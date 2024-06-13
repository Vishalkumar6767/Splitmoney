<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;
    protected $fillable=[
        'group_id',
        'expense_id',
        'payer_user_id',
        'payee_id',
        'amount', 
    ];
    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_user_id', 'id');
    }
    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id', 'id');
    }
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
    public function expenses()
    {
        return $this->belongsTo(Expense::class,'expense_id','id');
    }
}
