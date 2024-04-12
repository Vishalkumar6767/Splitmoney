<?php

use App\Http\Controllers\InviteGroupMemberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;


Route::resource('/user', 'App\Http\Controllers\UserController')->except(['create', 'edit']);


Route::post('/signup', [AuthController::class, 'signup']);
Route::get('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/login', [AuthController::class, 'loginUser']);
Route::get('/login', [AuthController::class, 'loginUser']);
Route::post('/logout', [AuthController::class, 'logout']);



Route::get('/group', [GroupController::class, 'index']);
Route::post('/group', [GroupController::class, 'store']);
Route::put('/group/{id}', [GroupController::class, 'update']);
Route::delete('/group/{id}', [GroupController::class, 'destroy']);

Route::post('/invite-group-member', [InviteGroupMemberController::class, 'store']);
Route::get('/invite-group-member', [InviteGroupMemberController::class, 'index']);

Route::post('/add-group-member', [GroupMemberController::class, 'store']);
Route::get('/group-member', [GroupMemberController::class, 'index']);
