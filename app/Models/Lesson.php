<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $guarded = [];

    public function contents(){
        return $this->hasMany(LessonContent::class,'lesson_id');
    }
}
