<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'nama',
        'perusahaan',
        'no_hp',
        'visitor_id',
        'id_kartu',
        'email',
        'tujuan',
        'lantai',
        'nomor_rak',
        'foto',
        'waktu_masuk',
        'waktu_keluar',
        'status'
    ];
    
    protected $casts = [
        'id_kartu' => 'integer',
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime'
    ];
}
