<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $fillable =[
       
        'group_id',
        'payer_user_id',
        'amount',
        'description',
        'date',

    ];

}
