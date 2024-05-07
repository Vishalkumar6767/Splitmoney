<?php

use App\Http\Controllers\InviteGroupMemberController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\UserController;

//User Routes
Route::resource('/users', UserController::class)->except(['create', 'edit']);
//Auth Routes
Route::post('/signup', [AuthController::class, 'signup']);
Route::get('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:api')->group(function () {
    //Group Routes
    Route::resource('groups', GroupController::class);
    // Invite Group member routes
    Route::post('/invite-group-member', [InviteGroupMemberController::class, 'store']);
    Route::get('/invite-group-member', [InviteGroupMemberController::class, 'index']);
    //Add members in Group routes
    Route::resource('group-members', GroupMemberController::class);
});
