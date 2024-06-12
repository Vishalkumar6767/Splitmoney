<?php

use App\Http\Controllers\InviteGroupMemberController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\GroupStatisticsController;
use App\Http\Controllers\InviteGroupController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\UserController;


//User Routes

//Auth Routes
Route::post('/signup', [AuthController::class, 'signup']);
Route::get('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:api')->group(function () {
    Route::post('/upload',[UserController::class, 'upload']);
    Route::get('/me', [AuthController::class, 'show']);
    //show users list and update user
    Route::resource('/users', UserController::class)->except(['create', 'store', 'edit', 'destroy']);
    //Group Routes
    Route::resource('groups', GroupController::class);
    // Invite Group member routes
    Route::post('/invite-group-member', [InviteGroupMemberController::class, 'store']);
    Route::get('/invite-group-member', [InviteGroupMemberController::class, 'index']);
    //Add members in Group routes
    Route::resource('group-members', GroupMemberController::class);
    //Expense routes and User Expense.
    Route::resource('expenses', ExpenseController::class);
    //group-statistics route
    Route::resource('group-statistics', GroupStatisticsController::class);
    //add -settlement
    Route::resource('settlements', SettlementController::class);
    
});
// Add invited members in group members
Route::resource('invite-group', InviteGroupController::class);

