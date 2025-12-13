<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mpin extends Model
{
    protected $fillable = [
        'user_id',
        'mpin',
        'is_active',
        'last_used_at',
    ];

    protected $hidden = [
        'mpin',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
