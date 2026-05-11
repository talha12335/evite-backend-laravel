<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'severity',
        'title',
        'message',
        'meta',
        'detected_at',
        'is_resolved',
        'resolved_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_resolved' => 'boolean',
    ];
}
