<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(){
        $new_invitation = Invitation::orderBy('id', 'desc')->limit(5)->get();
        return view('admin.index',compact('new_invitation'));
    }
    
}
