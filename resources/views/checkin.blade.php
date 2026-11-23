
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Visitor Check In | Sengked.com</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="/assets/css/global.css">

    <link rel="stylesheet" href="/assets/css/user/device-frame.css">

    <link rel="stylesheet" href="/assets/css/user/darkmode-user.css">

    <link rel="stylesheet" href="/assets/css/user/checkin.css">

    @include('partials.head-assets')

</head>

<body>

<div id="deviceFrame">

    <div class="page-toolbar">

        <button class="theme-toggle-user" type="button" title="Ganti tema">
            <i class="theme-toggle-icon bi bi-moon-stars-fill"></i>
        </button>

    </div>

    <section class="checkin-page">

        <div class="container">

            <a href="{{ route('home') }}" class="back-button">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>

            <div class="page-header">

                <h1>Register Check In</h1>

                <p>
                    Untuk visitor yang baru pertama kali berkunjung.
                    Lengkapi seluruh data sebelum memasuki
                    ruangan Data Center.
                </p>

            </div>

            <div class="checkin-card">

                <form id="checkinForm"
                      action="{{ route('visitor.store') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      data-lookup-url="{{ route('checkin.lookup') }}"
                      autocomplete="off">

                    @csrf

                    @error('blacklist')
                        <div class="alert alert-danger mb-3">
                            {{ $message }}
                        </div>
                    @enderror

                    @error('id_kartu')
                        <div class="alert alert-danger mb-3">
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- NOMOR HP -->

                    <div class="field-group">

                        <label class="form-label">Nomor HP</label>

                        <input
                            type="tel"
                            class="form-control"
                            id="visitorPhone"
                            name="no_hp"
                            placeholder="08xxxxxxxxxx"
                            maxlength="15"
                            value="{{ old('no_hp') }}"
                            required>

                        <div class="invalid-feedback">
                            Nomor HP tidak valid.
                        </div>

                    </div>

                    <!-- DATA KUNJUNGAN SEBELUMNYA -->

                    <div class="alert alert-success d-none align-items-start mb-3"
                         id="previousDataAlert">

                        <i class="bi bi-clock-history me-2 mt-1"></i>

                        <div>
                            Ditemukan data Anda dari kunjungan sebelumnya. Nama, email,
                            perusahaan, lantai, dan nomor rak sudah kami isikan otomatis —
                            silakan periksa kembali, lalu isi Tujuan Kunjungan dan ambil
                            foto selfie baru untuk hari ini.
                        </div>

                    </div>

                    <!-- NAMA -->

                    <div class="field-group">

                        <label class="form-label">Nama Lengkap</label>

                        <input
                            type="text"
                            class="form-control"
                            id="visitorName"
                            name="nama"
                            placeholder="Masukkan nama lengkap"
                            maxlength="100"
                            value="{{ old('nama') }}"
                            required>

                        <div class="invalid-feedback">
                            Nama minimal 3 karakter.
                        </div>

                    </div>

                    <!-- EMAIL -->

                    <div class="field-group">

                        <label class="form-label">Email</label>

                        <input
                            type="email"
                            class="form-control"
                            id="visitorEmail"
                            name="email"
                            placeholder="contoh@email.com"
                            maxlength="100"
                            value="{{ old('email') }}"
                            required>

                        <div class="invalid-feedback">
                            Email tidak valid.
                        </div>

                    </div>

                    <!-- PERUSAHAAN -->

                    <div class="field-group">

                        <label class="form-label">Perusahaan</label>

                        <input
                            type="text"
                            class="form-control"
                            id="visitorCompany"
                            name="perusahaan"
                            placeholder="Masukkan nama perusahaan"
                            maxlength="100"
                            value="{{ old('perusahaan') }}"
                            required>

                        <div class="invalid-feedback">
                            Nama perusahaan wajib diisi.
                        </div>

                    </div>

                    <!-- TUJUAN -->

                    <div class="field-group">

                        <label class="form-label">Tujuan Kunjungan</label>

                        <select
                            class="form-select"
                            id="visitorPurpose"
                            name="tujuan"
                            required>

                            <option value="">-- Pilih Tujuan --</option>

                            <option value="Maintenance" {{ old('tujuan') == 'Maintenance' ? 'selected' : '' }}>
                                Maintenance
                            </option>

                            <option value="Masuk Perangkat" {{ old('tujuan') == 'Masuk Perangkat' ? 'selected' : '' }}>
                                Masuk Perangkat
                            </option>

                            <option value="Keluar Perangkat" {{ old('tujuan') == 'Keluar Perangkat' ? 'selected' : '' }}>
                                Keluar Perangkat
                            </option>

                            <option value="Visit" {{ old('tujuan') == 'Visit' ? 'selected' : '' }}>
                                Visit
                            </option>

                        </select>

                        <div class="invalid-feedback">
                            Silakan pilih tujuan kunjungan.
                        </div>

                    </div>

                    <!-- LANTAI -->

                    <div class="field-group">

                        <label class="form-label">Lantai</label>

                        <select
                            class="form-select"
                            id="visitorLantai"
                            name="lantai"
                            required>

                            <option value="">-- Pilih Lantai --</option>

                            <option value="1" {{ old('lantai') == '1' ? 'selected' : '' }}>Lantai 1</option>
                            <option value="2" {{ old('lantai') == '2' ? 'selected' : '' }}>Lantai 2</option>
                            <option value="3" {{ old('lantai') == '3' ? 'selected' : '' }}>Lantai 3</option>

                        </select>

                        <div class="invalid-feedback">
                            Silakan pilih lantai.
                        </div>

                    </div>

                    <!-- NOMOR RAK -->

                    <div class="field-group">

                        <label class="form-label">Nomor Rak</label>

                        <input
                            type="text"
                            class="form-control"
                            id="visitorRak"
                            name="nomor_rak"
                            inputmode="numeric"
                            placeholder="Contoh: 12"
                            maxlength="6"
                            value="{{ old('nomor_rak') }}"
                            required>

                        <small class="text-muted mt-1 d-block">
                            Masukkan nomor rak (angka saja) yang akan dikunjungi di Data Center.
                        </small>

                        <div class="invalid-feedback">
                            Nomor rak wajib diisi dan harus berupa angka.
                        </div>

                    </div>

                    <!-- ID KARTU VISITOR -->

                    <div class="field-group">

                        <label class="form-label">ID Kartu Visitor</label>

                        <input
                            type="text"
                            class="form-control"
                            id="visitorIdKartu"
                            name="id_kartu"
                            inputmode="numeric"
                            pattern="[1-9][0-9]*"
                            placeholder="Masukkan ID Kartu Visitor"
                            value="{{ old('id_kartu') }}"
                            required>

                        <small class="text-muted mt-1 d-block">
                            Masukkan nomor ID Kartu Visitor berupa angka positif.
                            Gunakan ID Kartu yang sama saat login pada kunjungan berikutnya.
                        </small>

                        <div class="invalid-feedback">
                            ID Kartu Visitor wajib diisi dengan angka bulat positif.
                        </div>

                    </div>

                    <!-- FOTO SELFIE -->

                    <div class="field-group">

                        <label class="form-label">Foto Selfie</label>

                        <div class="photo-preview" id="photoPreview">

                            <video
                                id="cameraStream"
                                class="preview-image d-none"
                                autoplay
                                playsinline
                                muted></video>

                            <img
                                id="previewImage"
                                src=""
                                alt="Preview Selfie"
                                class="preview-image d-none">

                            <div id="photoPlaceholder">
                                <i class="bi bi-person-bounding-box"></i>
                                <p>Belum ada foto</p>
                            </div>

                        </div>

                        <canvas id="cameraCanvas" class="d-none"></canvas>

                        <div class="previous-photo-hint d-none" id="previousPhotoHint">

                            <img id="previousPhotoImg"
                                 src=""
                                 alt="Foto check-in sebelumnya">

                            <span>
                                Foto check-in sebelumnya. Tetap ambil foto baru untuk hari ini.
                            </span>

                        </div>

                        <input
                            type="file"
                            id="selfieInput"
                            name="foto"
                            accept="image/*"
                            hidden>

                        <div class="d-flex gap-2 mt-3">

                            <button
                                type="button"
                                class="btn btn-outline-primary w-100"
                                id="cameraButton">

                                <i class="bi bi-camera-fill"></i>
                                Aktifkan Kamera

                            </button>

                            <button
                                type="button"
                                class="btn btn-primary w-100 d-none"
                                id="captureButton">

                                <i class="bi bi-camera"></i>
                                Jepret

                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-secondary w-100 d-none"
                                id="retakeButton">

                                <i class="bi bi-arrow-counterclockwise"></i>
                                Ambil Ulang

                            </button>

                        </div>

                        <p class="text-danger small mt-2 d-none" id="cameraError"></p>

                    </div>

                    <!-- INFORMASI -->

                    <div class="alert alert-info d-flex align-items-start">

                        <i class="bi bi-info-circle-fill me-2 mt-1"></i>

                        <div>
                            Pastikan seluruh data telah benar.
                            Data yang sudah dikirim akan masuk ke sistem
                            administrator Data Center dan digunakan sebagai
                            riwayat kunjungan.

                            <strong>
                                Simpan ID Kartu Visitor yang telah Anda masukkan.
                            </strong>

                            ID Kartu tersebut akan digunakan bersama Nomor HP
                            untuk login pada kunjungan berikutnya.
                        </div>

                    </div>

                    <!-- BUTTON -->

                    <div class="row g-3">

                        <div class="col-md-6">

                            <button
                                type="reset"
                                class="btn btn-outline-secondary w-100"
                                id="resetButton">

                                <i class="bi bi-arrow-clockwise"></i>
                                Reset

                            </button>

                        </div>

                        <div class="col-md-6">

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                                id="submitButton">

                                <i class="bi bi-box-arrow-in-right"></i>

                                <span>Check In</span>

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </section>

    @include('partials.footer')

    <!-- MODAL KONFIRMASI -->

    <div class="modal fade" id="confirmModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Konfirmasi Check In
                    </h5>

                </div>

                <div class="modal-body">
                    Apakah seluruh data yang Anda isi sudah benar?
                </div>

                <div class="modal-footer">

                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button
                        class="btn btn-primary"
                        id="confirmSubmit">
                        Ya, Check In
                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- MODAL SUCCESS -->

    <div class="modal fade" id="successModal" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content text-center p-4">

                <div class="mb-3">

                    <i class="bi bi-check-circle-fill text-success"
                       style="font-size:70px;"></i>

                </div>

                <h3>Check In Berhasil</h3>

                <p>Data visitor berhasil disimpan.</p>

                <button
                    class="btn btn-primary"
                    onclick="window.location='/'">
                    Kembali ke Beranda
                </button>

            </div>

        </div>

    </div>

    <script src="/assets/js/darkmode.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/assets/js/device-frame.js"></script>

    <script src="/assets/js/checkin.js"></script>

</div>

</body>

</html>
