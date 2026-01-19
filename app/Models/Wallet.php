<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'currency',
        'balance',
        'is_blocked',
        'block_reason',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_blocked' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }
}
