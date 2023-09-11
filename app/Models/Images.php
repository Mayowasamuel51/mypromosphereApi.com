<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
class Images extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function postItem(){
        return $this->belongsTo(Post::class);
    }
}
