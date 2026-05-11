<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function show($id){
        $guest_list = Guest::where('invitation_id',$id)->get();
        foreach ($guest_list as $guest) {
            $guest->guestEmail = json_decode($guest->guestEmail);
        }        
        return view("admin.guest.guestList",compact('guest_list'));
    }
}
