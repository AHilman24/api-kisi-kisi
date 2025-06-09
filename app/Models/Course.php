<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $guarded = [];

    public function sets(){
        return $this->hasMany(Set::class,'course_id');
    }
    public function lessons(){
        return $this->hasMany(Lesson::class);
    }
}
