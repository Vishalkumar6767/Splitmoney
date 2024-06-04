<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_no',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }
    public function owner()
    {
        return $this->belongsTo(Group::class, 'created_by');
    }
    public function group()
    {
        return $this->belongsToMany(Group::class, 'group_members');
    }
    public function expense()
    {
        return $this->hasMany(Expense::class);
    }
    public function expenseParticipation()
    {
        return $this->hasMany(ExpenseParticipation::class);
    }
    public function image()
    {
        return $this->hasOne(Image::class);
    }
    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = strtolower($email);
    }
    protected function Name(): Attribute
    {
        return Attribute::make(
            set: fn ($name) => strtolower($name),
            get: fn ($name)=>ucwords($name),
        );
    }
}
