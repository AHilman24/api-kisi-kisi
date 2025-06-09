<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonContent extends Model
{
    protected $guarded = [];

    public function options(){
        return $this->hasMany(Option::class,'lesson_content_id');
    }
}
