<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;
    protected $fillable = [
        'invitation_id',
        'guestEmail'
    ];


    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
