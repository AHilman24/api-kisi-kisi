<?php

namespace App\Http\Controllers;

use App\Models\CompleteLesson;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function create(Request $request){
        try {
            $credentials = $request->validate([
                'name'=>'required',
                'description'=>'nullable',
                'slug'=>'required|unique:courses',
            ]);

            $create = Course::create([
                'name'=>$credentials['name'],
                'description'=>$credentials['description'],
                'slug'=>$credentials['slug'],
                'is_published'=>false
            ]);

            return response()->json([
                "status"=> "success",
                "message"=> "Course successfully added",
                "data"=>[
                    "name"=> $create->name,
                    "description"=> $create->description,
                    "slug"=> $create->slug,
                    "updated_at"=> $create->updated_at,
                    "created_at"=> $create->created_at,
                    "id"=> $create->id,
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

    public function update(Request $request,$slug){
        try {
            $credentials = $request->validate([
                'name'=>'required',
                'description'=>'nullable',
                'is_published'=>'nullable|boolean',
            ]);

            $course = Course::where('slug',$slug)->first();
            if (!$course) {
                return response()->json([
                    "status"=> "not_found",
                    "message"=> "Resource not found"
                ],404);
            }

            $course->update([
                'name'=>$credentials['name'],
                'description'=>$credentials['description'],
                'is_published'=>$credentials['is_published']
            ]);

            return response()->json([
                "status"=> "success",
                "message"=> "Course successfully added",
                "data"=>[
                    "name"=> $course->name,
                    "description"=> $course->description,
                    "slug"=> $course->slug,
                    "is_published"=> $course->is_published,
                    "updated_at"=> $course->updated_at,
                    "created_at"=> $course->created_at,
                    "id"=> $course->id,
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

    public function delete(Request $request,$slug){
        $course = Course::where('slug',$slug)->first();
        if (!$course) {
            return response()->json([
                "status"=> "not_found",
                "message"=> "Resource not found"
            ],404);
        }

        $course->delete([

        ]);

        return response()->json([
            "status"=> "success",
            "message"=> "Course successfully deleted"
        ],201);

    }

    public function getCourse(Request $request){
        $course = Course::where('is_published',true)->get();
        return response()->json([
            "status"=> "success",
            "message"=> "Courses retrieved successfully",
            "data"=>$course->map(function($item){
                return[
                    "id"=> $item->id,
                    "name"=> $item->name,
                    "description"=> $item->description,
                    "slug"=> $item->slug,
                    "is_published"=> $item->is_published,
                    "updated_at"=> $item->updated_at,
                    "created_at"=> $item->created_at,
                ];
            })
        ],200);
    }


    public function detail(Request $request,$slug){
        $course = Course::where('slug',$slug)->first();
        return response()->json([
            "status"=> "success",
            "message"=> "Course details retrieved successfully",
            "data"=>[
                "id"=> $course->id,
                "name"=> $course->name,
                "description"=> $course->description,
                "slug"=> $course->slug,
                "is_published"=> $course->is_published,
                "updated_at"=> $course->updated_at,
                "created_at"=> $course->created_at,
                "sets"=>$course->sets()->orderBy('order','asc')->get()->map(function($item){
                    return[
                        'id'=>$item->id,
                        'name'=>$item->name,
                        'order'=>$item->order,
                        'lesson'=>$item->lessons()->orderBy('order','asc')->get()->map(function($item){
                            return[
                                'id'=>$item->id,
                                'name'=>$item->name,
                                'order'=>$item->order,
                                'content'=>$item->contents()->orderBy('order','asc')->get()->map(function($item){
                                    return[
                                        'id'=>$item->id,
                                        'type'=>$item->type,
                                        'content'=>$item->content,
                                        'order'=>$item->order,
                                        'option'=>$item->options()->get()->map(function($item){
                                            return[
                                                'id'=>$item->id,
                                                'option_text'=>$item->option_text
                                            ];
                                        })
                                    ];

                                })
                            ];
                        })
                    ];
                })
            ]
        ],200);
    }

    public function register(Request $request,$slug){

        $course = Course::where('slug',$slug)->first();
        if (Enrollment::where('course_id',$course->id)->exists()) {
            return response()->json([
                "status"=> "error",
                "message"=> "The user is already registered for this course"
            ],400);
        }
            if (!$course) {
                return response()->json([
                    "status"=> "not_found",
                    "message"=> "Resource not found"
                ],404);
            }

            Enrollment::create([
                'user_id'=>Auth::id(),
                'course_id'=>$course->id
            ]);

            return response()->json([
                "status"=> "success",
                "message"=> "User registered successful"
            ],200);
    }

    public function progress(Request $request){
        $course = Enrollment::where('user_id',Auth::id())->with('courses')->get();
        $progress = $course->map(function($item){
            $courses = $item->courses;
            $complete = CompleteLesson::where('user_id',Auth::id())
            ->whereHas('lesson',function($q) use ($courses){
                $q->where('course_id',$courses->id);
            })
            ->with('lesson')->get();
            return[
                "courses"=>[
                    "id"=> $item->courses->id,
                    "name"=> $item->courses->name,
                    "description"=> $item->courses->description,
                    "slug"=> $item->courses->slug,
                    "is_published"=> $item->courses->is_published,
                    "updated_at"=> $item->courses->updated_at,
                    "created_at"=> $item->courses->created_at,
                ],
                'complete_lesson'=>$complete->map(function($item){
                    return[
                            'id'=>$item->lesson->id,
                            'name'=>$item->lesson->name,
                            'order'=>$item->lesson->order
                        ];
                })
            ];
        });

        return response()->json([
            "status"=> "success",
            "message"=> "User progress retrieved successfully",
            "data"=>[
                "progress"=>$progress
            ]
        ],200);
    }
}
