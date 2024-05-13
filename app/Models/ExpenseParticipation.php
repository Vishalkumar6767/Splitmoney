<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseParticipation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'expense_id',
        'owned_amount'
    ];
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
