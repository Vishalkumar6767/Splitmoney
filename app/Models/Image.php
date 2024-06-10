<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
    
class Image extends Model
{
    use HasFactory;
    protected $fillable =[
        'type',
        'group_id',
        'url'

    ];
    
    public function imageable()
    {
        return $this->morphTO(__FUNCTION__,'imageable_type','imageable_id');
    }
}
