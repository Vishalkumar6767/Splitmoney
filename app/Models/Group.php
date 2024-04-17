<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'group_name',
        'description',
        'created_by',

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
        return [];
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_user');
    }




    // public function creator()
    // {
    //     return $this->belongsTo(User::class, 'created_by');
    // }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($group) {
    //         // If the user is authenticated, set the created_by attribute to the current user's ID
    //         if (auth()->check()) {
    //             $group->created_by = auth()->id();
    //         }
    //     });
    // }
}
