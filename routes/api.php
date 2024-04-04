<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;

Route::resource('/user', 'App\Http\Controllers\UserController')->except(['create', 'edit']);
// Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
// Route::get('/verify-otp', [AuthController::class, 'verifyOtp']);


Route::post('/signup', [AuthController::class, 'signup']);
Route::get('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/login', [AuthController::class, 'loginUser']);
Route::get('/login', [AuthController::class, 'loginUser']);

Route::post('/group', [GroupController::class, 'store']);

