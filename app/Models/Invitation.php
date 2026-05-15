<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;
    protected $fillable = [
        'occasion',
        'room',
        'date',
        'time',
        'end_time',
        'user_id',
        'image',
        "template_id",
        'host_contact',
        'host_name',
        'studio_location',
        'turning',
        'honoree_name',
        'location_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
