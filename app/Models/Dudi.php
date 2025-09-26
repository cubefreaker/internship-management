<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dudi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dudi';

    protected $fillable = [
        'user_id',
        'nama_perusahaan',
        'alamat',
        'telepon',
        'email',
        'penanggung_jawab',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function magang()
    {
        return $this->hasMany(Magang::class);
    }

    public function getTotalSiswaMagangAttribute()
    {
        return $this->magang()->where('status', 'aktif')->count();
    }
}
