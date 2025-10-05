<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Logbook extends Model
{
    use HasFactory;

    protected $table = 'logbook';

    protected $fillable = [
        'magang_id',
        'tanggal',
        'kegiatan',
        'kendala',
        'file',
        'status_verifikasi',
        'catatan_guru',
        'catatan_dudi',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function magang(): BelongsTo
    {
        return $this->belongsTo(Magang::class);
    }

    public function siswa()
    {
        return $this->hasOneThrough(User::class, Magang::class, 'id', 'id', 'magang_id', 'siswa_id');
    }

    public function dudi()
    {
        return $this->hasOneThrough(Dudi::class, Magang::class, 'id', 'id', 'magang_id', 'dudi_id');
    }
}