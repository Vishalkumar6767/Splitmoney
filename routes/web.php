<?php

use App\Http\Controllers\InviteGroupMemberController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('invite-group-member', [InviteGroupMemberController::class, 'index']);
