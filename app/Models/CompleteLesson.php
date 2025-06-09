<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompleteLesson extends Model
{
    protected $guarded = [];
    protected $table = 'completed_lessons';

    public function lesson(){
        return $this->belongsTo(Lesson::class,'lesson_id');
    }
}
