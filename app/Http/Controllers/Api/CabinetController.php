<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Box;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
public function myWork(Request $request){
   $user = User::where('email',$request->email)->first();
   
   $operations=Operation::orderByDesc('update_at')->where('user_id',$user->id)->where('created_at','>',$request->GetCreated)->where('created_at','<',$request->EndCreated.' 23:59:59')->groupBy('application_id')->select('application_id',DB::raw('sum(num) as num_me,date(max(updated_at)) update_at'))->get();
 
   // leftJoin('subapplications','operations.application_id','=','subapplications.application_id')
   // ->leftJoin('services','subapplications.service_id')->seletRow()->get();
   $data=[];
   foreach ($operations as $oper){
      $app=DB::table('applications')->leftJoin('organizations','applications.organization_id','=','organizations.id')->where('applications.id','=',$oper->application_id)->select('organizations.name  as name')->first();

      $subapplications= DB::table('subapplications')->where('subapplications.application_id','=', $oper->application_id)->leftJoin('services','subapplications.service_id','=','services.id')
      ->select('services.name as service','subapplications.rate as rate','subapplications.service_num as service_num')->get();
      foreach($subapplications as $sub){
         $d=array('organization'=>$app->name,'application_id'=>$oper->application_id,'service'=>$sub->service,'rate'=>$sub->rate,'num_me'=>$oper->num_me*$sub->service_num,'sum'=>($oper->num_me*$sub->service_num)*$sub->rate,'update_at'=>$oper->update_at);
      array_push($data,$d);
      }
      $box=Box::where('application_id',$oper->application_id)->where('user_id',$user->id)->select(DB::raw('count(id) as num_me, avg(rate) as rate'))->first();
      if ($box->num_me>0){
         $d=array('organization'=>$app->name,'application_id'=>$oper->application_id,'service'=>'Формирование короба','rate'=>$box->rate,'num_me'=>$box->num_me,'sum'=>($box->num_me*1)*$box->rate,'update_at'=>$oper->update_at);
         array_push($data,$d);
      }

   }
return $data;
}
}
