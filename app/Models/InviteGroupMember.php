<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class InviteGroupMember extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'group_id',
        'email',
        'token'

    ];
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
