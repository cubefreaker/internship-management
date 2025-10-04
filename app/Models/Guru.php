<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'nama',
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
