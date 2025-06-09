<?php

namespace App\Http\Controllers;

use App\Models\CompleteLesson;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LessonController extends Controller
{
    public function create(Request $request){
        try {
            $credentials = $request->validate([
                'name'=>'required',
                'set_id'=>'required|exists:sets,id',
                'contents'=>'required|array',
                'contents.*.type'=>'required|in:learn,quiz',
                'contents.*.content'=>'required|string',
                'contents.*.options'=>'nullable|array',
                'contents.*.options.*.option_text'=>'required|string',
                'contents.*.options.*.is_correct'=>'boolean',
            ]);

            $lastOrder = Lesson::where('set_id',$credentials['set_id'])->max('order');
            $create = Lesson::create([
                'name'=>$credentials['name'],
                'order'=>$lastOrder?$lastOrder+1:1,
                'set_id'=>$credentials['set_id']
            ]);

            foreach ($credentials['contents'] as $content) {
                $lastOrderr = LessonContent::where('lesson_id',$create->id)->max('order');
                $contentCreate = LessonContent::create([
                    'lesson_id'=>$create->id,
                    'type'=>$content['type'],
                    'content'=>$content['content'],
                    'order'=>$lastOrderr?$lastOrderr+1:1
                ]);

                if ($content['type']=== 'quiz') {
                    foreach ($content['options'] as $option) {
                        Option::create([
                            'lesson_content_id'=>$contentCreate->id,
                            'option_text'=>$option['option_text'],
                            'is_correct'=>$option['is_correct'],
                        ]);
                    }
                }
            }

            return response()->json([
                "status"=> "success",
                "message"=> "Lesson create successfully",
                "data"=>[
                    'name'=>$create->name,
                    'order'=>$create->order,
                    'id'=>$create->id,
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

    public function delete(Request $request,$id){
        $lesson = Lesson::where('id',$id)->first();
        if (!$lesson) {
            return response()->json([
                "status"=> "not_found",
                "message"=> "Resource not found"
            ],404);
        }
        $lesson->delete();
        return response()->json([
            "status"=> "success",
            "message"=> "Lesson successfully deleted"

        ],200);
    }


    public function checkAnswer(Request $request,$lesson_id,$content_id){
        try {
            $credentials = $request->validate([
                'option_id'=>'required|exists:options,id'
            ]);
             $lesson = Lesson::where('id',$lesson_id)->first();
                if (!$lesson) {
                    return response()->json([
                        "status"=> "not_found",
                        "message"=> "Resource not found"
                    ],404);
                }

                $content = LessonContent::where('id',$content_id)->first();
                if (!$content) {
                    return response()->json([
                        "status"=> "not_found",
                        "message"=> "Resource not found"
                    ],404);
                }

                if ($content->type !== 'quiz') {
                    return response()->json([
                        "status"=> "error",
                        "message"=> "Only for quiz content"
                    ],400);
                }

                $option = $content->options()->where('id',$credentials['option_id'])->first();
                return response()->json([
                    "status"=> "success",
                    "message"=> "Check answer success",
                    "data"=>[
                        "questions"=>$content->content,
                        "user_answer"=>$option->option_text,
                        "is_correct"=>$option->is_correct?true:false
                    ]
                    ],200);

        } catch (ValidationException $th) {
             return response()->json([
                'status'=>'error',
                'message'=>"Invalid field(s) in request",
                "errors"=>$th->errors()
            ],402);
        }
    }

    public function completeLesson(Request $request,$id){
        $lesson = Lesson::where('id',$id)->first();
        if (!$lesson) {
            return response()->json([
                "status"=> "not_found",
                "message"=> "Resource not found"
            ],404);
        }
        CompleteLesson::create([
            'user_id'=>Auth::id(),
            'lesson_id'=>$lesson->id
        ]);
        return response()->json([
            "status"=> "success",
            "message"=> "Lesson successfully completed"
        ],200);
    }

    
}
