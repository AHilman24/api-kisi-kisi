<?php

namespace App\Http\Controllers;

use App\Models\Administrator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request){
        try {
            $credentials = $request->validate([
                'full_name'=>'required',
                'username'=>'required|min:3|unique:users|regex:/^[a-zA-z0-9_.]+$/',
                'password'=>'required|min:6'
            ]);

            $create = User::create([
                'full_name'=>$credentials['full_name'],
                'username'=>$credentials['username'],
                'password'=>bcrypt($credentials['password']),
            ]);

            $token = $create->createToken('accessToken')->plainTextToken;
            return response()->json([
                "status"=> "success",
                "message"=> "User registration successful",
                "data"=>[
                    "full_name"=> $create->full_name,
                    "username"=> $create->username,
                    "created_at"=>$create->created_at,
                    "updated_at"=>$create->updated_at,
                    "id"=>$create->id,
                    "token"=>$token,
                    "role"=> "user"
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
    public function login(Request $request){
        try {
            $credentials = $request->validate([
                'username'=>'required',
                'password'=>'required'
            ]);

            $user = User::where('username',$credentials['username'])->first();
            $admin = Administrator::where('username',$credentials['username'])->first();
            if ($user && password_verify($credentials['password'],$user->password)) {
                # code...
                $token = $user->createToken('accessToken')->plainTextToken;
                return response()->json([
                    "status"=> "success",
                    "message"=> "User login successful",
                    "data"=>[
                        "full_name"=> $user->full_name,
                        "username"=> $user->username,
                        "created_at"=>$user->created_at,
                        "updated_at"=>$user->updated_at,
                        "id"=>$user->id,
                        "token"=>$token,
                        "role"=> "user"
                    ]
                ],201);
            }
            else if ($admin && password_verify($credentials['password'],$admin->password)) {
                # code...
                $token = $admin->createToken('accessToken')->plainTextToken;
                return response()->json([
                    "status"=> "success",
                    "message"=> "User login successful",
                    "data"=>[
                        "username"=> $admin->username,
                        "created_at"=>$admin->created_at,
                        "updated_at"=>$admin->updated_at,
                        "id"=>$admin->id,
                        "token"=>$token,
                        "role"=> "admin"
                    ]
                ],201);
            }
            else{
                return response()->json([
                    "status"=> "authentication_failed",
                    "message"=> "The username or password you entered is incorrect"
                ],400);
            }

        } catch (ValidationException $th) {
            return response()->json([
                'status'=>'error',
                'message'=>"Invalid field(s) in request",
                "errors"=>$th->errors()
            ],402);
        }
    }

    public function logout(Request $request){
        $request->user()->Tokens()->delete();
        return response()->json([
            "status"=> "success",
            "message"=> "Logout successful"
        ],200);
    }
}
