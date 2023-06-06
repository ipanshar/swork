<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'email' => $user->email,
                'level' => 1,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 200);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'email' => $user->email,
                'level' => $user->level,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    /**
     * Login The User
     * @param Request $request
     * @return User
     */

    public function newPassword(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required',
                    'newpassword' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Неверный текущий пароль.',
                ], 200);
            }

            $user = User::where('email', $request->email)->first();
            $user->password =  Hash::make($request->newpassword);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Пароль изменен.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $user->tokens()->where('id', $request->tokenId)->delete();
        return response()->json([
            'status' => true,
            'message' => 'Соединение разорвано.',
        ], 200);
    }

    public function deleteAllSessions(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $user->tokens()->delete();
    }
    public function createRecoveryToken(Request $request){
        if(User::where('email',$request->email)->count() > 0){
            $user = User::where('email',$request->email)->first();
            $user->recovery_token = random_int(100000, 999999);
            $user->save();
            $data = array(
                'name'		=> $user->name, 
                'site'	=> $request->site,
                'request'	=> 'recovery_password',
                'recovery_token' =>$user->recovery_token
            );
            Mail::to($request->email)->send(new SendMail($data));
            return response()->json([
                'status' => true,
                'message' =>  'На уазаный email, отправлен код для сброса пароля',
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Email не найден',
            ], 200);
        }
    }

    public function recoveryToken(Request $request){
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'recovery_token' => 'required',
                    'password' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = User::where('email', $request->email)->first();
            if ($user->recovery_token == $request->recovery_token and $request->recovery_token > 0) { 
                $user->password =  Hash::make($request->password);
            $user->recovery_token=0;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Пароль изменен.',
            ], 200);
                
            }else{
                 return response()->json([
                    'status' => false,
                    'message' => 'Неверный код.',
                ], 200);
            }
          
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function level(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        return response()->json([
            'level' => $user->level
        ], 200);
    }
}
