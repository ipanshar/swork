<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CabinetController extends Controller
{
   public function mySalary(){
    return [1, 2, 3];
   }
   
   public function myProfile(Request $request){
      $user = User::where('email', $request->email)->first();
      return response()->json([
         'name'=>$user->name,
         'email'=>$user->email,
         'level'=>$user->level.' - й уровень.',
         'created_at'=>$user->created_at,
      ]);
   }
   
}
