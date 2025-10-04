<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nis',
        'nama',
        'kelas',
        'jurusan',
        'alamat',
        'telepon',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function magang(): HasMany
    {
        return $this->hasMany(Magang::class);
    }
}
