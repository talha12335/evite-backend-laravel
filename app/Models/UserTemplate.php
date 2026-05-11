<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTemplate extends Model
{
    use HasFactory;
    Protected $fillable = [
        'user_id',
        'image',
        'text1_color',
        'text2_color',
        'text3_color'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
