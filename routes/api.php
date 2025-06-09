<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SetController;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');


Route::middleware(['auth:sanctum'])->group(function(){
    Route::middleware('admin')->group(function(){
        Route::post('/course',[CourseController::class,'create']);
        Route::put('/course/:{slug}',[CourseController::class,'update']);
        Route::delete('/course/:{slug}',[CourseController::class,'delete']);

        Route::post('/course/:{slug}/sets',[SetController::class,'create']);
        Route::delete('/course/:{slug}/sets/:{id}',[SetController::class,'delete']);

        Route::post('/lessons',[LessonController::class,'create']);
        Route::delete('/lessons/:{id}',[LessonController::class,'delete']);
        Route::put('/lessons/:{id}/complete',[LessonController::class,'completeLesson']);
        Route::post('/lessons/:{id}/contents/:{content_id}/check',[LessonController::class,'checkAnswer']);

    });
    Route::get('/course',[CourseController::class,'getCourse']);
    Route::get('/course/:{slug}',[CourseController::class,'detail']);
    Route::post('/courses/:{slug}/register',[CourseController::class,'register']);
    Route::get('/users/progress',[CourseController::class,'progress']);
});


Route::fallback(function(){
    return response()->json([
        "status"=> "not_found",
        "message"=> "Resource not found"

    ],404);
});
