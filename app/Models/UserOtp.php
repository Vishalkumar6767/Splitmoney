<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Http\JsonResponse;

use Illuminate\Foundation\Auth\UserOtp as Authenticatable;

class UserOtp extends Model
{
    use HasFactory;
    protected $fillable = [

        'otp',
        'phone_no',
        'type',
        'verified_at',
        'expire_at',
    ];
}
