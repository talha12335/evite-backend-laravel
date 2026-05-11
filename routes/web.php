<?php

use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\GuestController;
use App\Http\Controllers\admin\InvitationController;
use App\Http\Controllers\admin\TemplateController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::middleware(['auth', 'isAdmin'])
    ->group(function ()
    {
Route::get('/dashboard', [AdminDashboardController::class, "index"])->name('dashboard');

Route::resource('admin_invitation', InvitationController::class);
Route::resource('admin_template', TemplateController::class);
Route::resource('admin_guest',GuestController::class);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    });

