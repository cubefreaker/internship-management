<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSettings extends Model
{
    use HasFactory;

    protected $table = 'school_settings';

    protected $fillable = [
        'logo_url',
        'nama_sekolah',
        'alamat',
        'telepon',
        'email',
        'website',
        'kepala_sekolah',
        'npsn',
    ];
}