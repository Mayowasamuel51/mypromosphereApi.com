<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Images;
class Post extends Model
{
    use HasFactory;
    protected $guarded = [];


    // include images , comments 

    public function images(){
        return $this->hasMany(Images::class);
    }
}
