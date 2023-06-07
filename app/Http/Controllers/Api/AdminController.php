<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
   public function users(){
    $users = DB::table('users')->select('level as value', 'email as label')->get();
    return $users;
   }

   public function uplevel(Request $request){
    $level = DB::table('users')->where('email', $request->email)->update(['level'=>$request->newlevel]);
    if(is_null($level)){
        return response()->json([
            'status'=>false,
            'massage'=>'Не удалось обновить'
        ],200);
    }
    return response()->json([
        'status'=>true,
        'message'=>'Доступ успешно обновлено'
    ],200);
   }
}
