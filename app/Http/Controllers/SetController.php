<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SetController extends Controller
{
    public function create(Request $request,$course){
        try {
            $credentials = $request->validate([
                'name'=>'required',
            ]);

            $course = Course::where('slug',$course)->first();
            if (!$course) {
                return response()->json([
                    'status'=>'not found',
                    'message'=>'not found'
                ],404);
            }
            $latest = Set::where('course_id',$course->id)->max('order');
            $create = Set::create([
                'name'=>$credentials['name'],
                'course_id'=>$course->id,
                'order'=>$latest?$latest+1:1
            ]);

            return response()->json([
                "status"=> "success",
                "message"=> "Set successfully added",
                "data"=>[
                    "name"=>$create->name,
                    "order"=>$create->order,
                    'id'=>$create->id
                ]
                ],201);

        } catch (ValidationException $th) {
            return response()->json([
                'status'=>'error',
                'message'=>"Invalid field(s) in request",
                "errors"=>$th->errors()
            ],402);
        }
    }

    public function delete(Request $request,$course,$id){
        $course = Course::where('slug',$course)->first();
            if (!$course) {
                return response()->json([
                    'status'=>'not found',
                    'message'=>'not found'
                ],404);
            }

            $set = $course->sets()->where('id',$id)->first();
            $set->delete();
            return response()->json([
                "status"=> "success",
                "message"=> "Set successfully deleted"
            ],200);
    }
}
