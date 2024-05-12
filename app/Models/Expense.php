<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = [

        'group_id',
        'payer_user_id',
        'amount',
        'type',
        'description',
        'date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'payer_user_id', 'id');
    }
    public function userExpenses()
    {
        return $this->hasMany(ExpenseParticipation::class,'expense_id','id');
    }
}
