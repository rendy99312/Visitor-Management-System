<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Visitor;
use App\Models\Blacklist;
use App\Models\Rak;

class VisitorController extends Controller
{
    public function checkin()
    {
        return view('checkin-select');
    }

    public function checkinRegister()
    {
        $raks = Rak::where('status', 'aktif')
            ->orderBy('nomor_rak')
            ->get();

        return view('checkin', compact('raks'));
    }

    public function checkinLogin()
    {
        return view('checkin-login');
    }

    /**
     * Memeriksa visitor berdasarkan nomor HP dan ID kartu.
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'no_hp' => 'required|string',
            'id_kartu' => 'nullable|integer|min:1',
        ]);

        $noHp = $this->normalizePhone((string) $request->no_hp);

        $query = Visitor::where('no_hp', $noHp);

        if ($request->filled('id_kartu')) {
            $query->where('id_kartu', (int) $request->id_kartu);
        }

        $visitor = $query
            ->orderByDesc('waktu_masuk')
            ->first();

        if (!$visitor) {
            return response()->json([
                'success' => false,
                'reason' => 'visitor_not_found',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nama' => $visitor->nama,
                'email' => $visitor->email,
                'perusahaan' => $visitor->perusahaan,
                'lantai' => $visitor->lantai,
                'nomor_rak' => $visitor->nomor_rak,
                'id_kartu' => $visitor->id_kartu,
                'foto' => $visitor->foto
                    ? asset('storage/' . $visitor->foto)
                    : null,
            ],
        ]);
    }

    /**
     * Registrasi visitor baru.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|min:3|max:100',
                'perusahaan' => 'required|string|max:150',
                'no_hp' => 'required|string|min:10|max:15',
                'email' => 'required|email',
                'tujuan' => 'required|string',
                'lantai' => 'required|integer|between:1,3',
                'nomor_rak' => 'required|integer',
                'id_kartu' => 'required|integer|min:1',
                'foto' => 'required|image|max:5120',
            ]);

            $noHpNormalized = $this->normalizePhone($request->no_hp);
            $idKartu = (int) $request->id_kartu;

            $blacklisted = Blacklist::where('no_hp', $noHpNormalized)
                ->orWhereRaw('LOWER(email) = ?', [
                    strtolower($request->email)
                ])
                ->first();

            if ($blacklisted) {
                return back()
                    ->withErrors([
                        'blacklist' => 'Maaf, Anda tidak dapat melakukan check-in karena masuk daftar blacklist.'
                    ])
                    ->withInput();
            }

            $this->acquireCardLock($idKartu);

            try {
                $visitor = DB::transaction(function () use (
                    $request,
                    $noHpNormalized,
                    $idKartu
                ) {
                    $this->ensureCardAvailable($idKartu);

                    $path = $request->file('foto')->store(
                        'visitors',
                        'public'
                    );

                    $visitorId = $this->visitorIdForPhone(
                        $noHpNormalized
                    );

                    return Visitor::create([
                        'nama' => $request->nama,
                        'perusahaan' => $request->perusahaan,
                        'no_hp' => $noHpNormalized,
                        'visitor_id' => $visitorId,
                        'id_kartu' => $idKartu,
                        'email' => $request->email,
                        'tujuan' => $request->tujuan,
                        'lantai' => $request->lantai,
                        'nomor_rak' => $request->nomor_rak,
                        'foto' => $path,
                        'status' => 'Masuk',
                        'waktu_masuk' => now(),
                    ]);
                });
            } finally {
                $this->releaseCardLock($idKartu);
            }

            return redirect()
                ->route('success')
                ->with('visitor_id', $visitor->visitor_id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\RuntimeException $e) {
            return back()
                ->withErrors([
                    'id_kartu' => $e->getMessage()
                ])
                ->withInput();

        } catch (\Exception $e) {
            report($e);

            return back()
                ->withErrors([
                    'error' => 'Terjadi kesalahan saat registrasi. Silakan coba kembali.'
                ])
                ->withInput();
        }
    }

    /**
     * Login visitor menggunakan nomor HP dan ID kartu.
     */
    
public function loginStore(Request $request)
{
    try {
        // 1. Validasi data yang dikirim
        $request->validate([
            'no_hp' => 'required|string|min:10|max:15',
            'id_kartu' => 'required|integer|min:1',
            'tujuan' => 'required|string',
            'lantai' => 'required|integer|between:1,3',
            'nomor_rak' => 'required|integer',
            'foto' => 'required|image|max:5120',
        ]);

        // 2. Normalisasi nomor HP dan ambil ID kartu baru
        $noHpNormalized = $this->normalizePhone($request->no_hp);
        $idKartu = (int) $request->id_kartu;

        // 3. Cari data visitor berdasarkan nomor HP saja
        // ID kartu boleh berbeda dari kunjungan sebelumnya
        $previous = Visitor::where('no_hp', $noHpNormalized)
            ->orderByDesc('waktu_masuk')
            ->first();

        // 4. Pastikan nomor HP sudah terdaftar
        if (!$previous) {
            return back()
                ->withErrors([
                    'no_hp' => 'Nomor HP belum terdaftar. Silakan gunakan menu Register.'
                ])
                ->withInput();
        }

        // 5. Periksa blacklist
        $blacklisted = Blacklist::where('no_hp', $noHpNormalized)
            ->orWhereRaw('LOWER(email) = ?', [
                strtolower($previous->email)
            ])
            ->first();

        if ($blacklisted) {
            return back()
                ->withErrors([
                    'blacklist' => 'Maaf, Anda tidak dapat melakukan check-in karena masuk daftar blacklist.'
                ])
                ->withInput();
        }

        // 6. Kunci penggunaan kartu
        $this->acquireCardLock($idKartu);

        try {
            // 7. Simpan kunjungan baru
            DB::transaction(function () use (
                $request,
                $previous,
                $noHpNormalized,
                $idKartu
            ) {
                // Pastikan kartu belum digunakan visitor lain
                $this->ensureCardAvailable($idKartu);

                // Simpan foto selfie
                $path = $request->file('foto')->store(
                    'visitors',
                    'public'
                );

                // Buat riwayat kunjungan baru
                Visitor::create([
                    'nama' => $previous->nama,
                    'perusahaan' => $previous->perusahaan,
                    'no_hp' => $noHpNormalized,
                    'visitor_id' => $previous->visitor_id,
                    'id_kartu' => $idKartu,
                    'email' => $previous->email,
                    'tujuan' => $request->tujuan,
                    'lantai' => $request->lantai,
                    'nomor_rak' => $request->nomor_rak,
                    'foto' => $path,
                    'status' => 'Masuk',
                    'waktu_masuk' => now(),
                ]);
            });

        } finally {
            // Lepaskan kunci kartu
            $this->releaseCardLock($idKartu);
        }

        // 8. Arahkan ke halaman sukses
        return redirect()->route('success');

    } catch (\Illuminate\Validation\ValidationException $e) {

        return back()
            ->withErrors($e->errors())
            ->withInput();

    } catch (\RuntimeException $e) {

        return back()
            ->withErrors([
                'id_kartu' => $e->getMessage()
            ])
            ->withInput();

    } catch (\Exception $e) {

        report($e);

        return back()
            ->withErrors([
                'error' => 'Terjadi kesalahan saat login. Silakan coba kembali.'
            ])
            ->withInput();
    }
}

    public function checkout()
    {
        return view('checkout');
    }

    /**
     * Mencari visitor yang masih aktif untuk checkout.
     */
    public function searchVisitor(Request $request)
    {
        $request->validate([
            'no_hp' => 'required|string',
        ]);

        $noHp = $this->normalizePhone((string) $request->no_hp);

        $visitor = Visitor::where('no_hp', $noHp)
            ->where('status', 'Masuk')
            ->orderByDesc('waktu_masuk')
            ->first();

        if (!$visitor) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor tidak ditemukan atau sudah melakukan checkout.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $visitor->id,
                'nama' => $visitor->nama,
                'no_hp' => $visitor->no_hp,
                'email' => $visitor->email,
                'perusahaan' => $visitor->perusahaan,
                'tujuan' => $visitor->tujuan,
                'lantai' => $visitor->lantai ?: '-',
                'nomor_rak' => $visitor->nomor_rak ?: '-',
                'id_kartu' => $visitor->id_kartu,
                'foto' => $visitor->foto
                    ? asset('storage/' . $visitor->foto)
                    : asset('assets/img/default-user.png'),
                'waktu_masuk' => $visitor->waktu_masuk
                    ? $visitor->waktu_masuk->format('d M Y - H:i') . ' WIB'
                    : '-',
            ],
        ]);
    }

    /**
     * Checkout membebaskan kartu untuk digunakan kembali.
     */
    public function processCheckout(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:visitors,id',
        ]);

        $visitor = Visitor::where('id', $request->id)
            ->where('status', 'Masuk')
            ->first();

        if (!$visitor) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor tidak ditemukan atau sudah checkout.',
            ], 404);
        }

        $visitor->update([
            'status' => 'Keluar',
            'waktu_keluar' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checkout berhasil.',
        ]);
    }

    /**
     * Memeriksa apakah kartu sedang digunakan.
     */
    private function ensureCardAvailable(int $idKartu): void
    {
        $isUsed = Visitor::where('id_kartu', $idKartu)
            ->where('status', 'Masuk')
            ->exists();

        if ($isUsed) {
            throw new \RuntimeException(
                'ID Kartu Visitor nomor ' . $idKartu . ' sedang digunakan. Silakan pilih kartu lain.'
            );
        }
    }

    /**
     * Mengunci penggunaan kartu selama proses check-in.
     */
    private function acquireCardLock(int $idKartu): void
    {
        $lockName = 'sengked_visitor_card_' . $idKartu;

        $result = DB::selectOne(
            'SELECT GET_LOCK(?, 10) AS acquired',
            [$lockName]
        );

        if (!$result || (int) $result->acquired !== 1) {
            throw new \RuntimeException(
                'Sistem sedang memproses penggunaan kartu ini. Silakan coba kembali.'
            );
        }
    }

    /**
     * Melepaskan kunci kartu.
     */
    private function releaseCardLock(int $idKartu): void
    {
        $lockName = 'sengked_visitor_card_' . $idKartu;

        DB::select(
            'SELECT RELEASE_LOCK(?)',
            [$lockName]
        );
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '62')) {
            $phone = '0' . substr($phone, 2);
        }

        return $phone;
    }

    /**
     * Mengambil ID Visitor lama berdasarkan nomor HP.
     */
    private function visitorIdForPhone(string $noHp): string
    {
        $existing = Visitor::where('no_hp', $noHp)
            ->whereNotNull('visitor_id')
            ->value('visitor_id');

        return $existing ?: $this->generateVisitorId();
    }

    /**
     * Membuat ID Visitor 5 digit yang belum pernah digunakan.
     */
    private function generateVisitorId(): string
    {
        do {
            $id = str_pad(
                (string) random_int(0, 99999),
                5,
                '0',
                STR_PAD_LEFT
            );
        } while (Visitor::where('visitor_id', $id)->exists());

        return $id;
    }
}
