<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Box;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogisticController extends Controller
{
    
    public function boxesStatus(Request $request){
        $data=[];
        $status = Status::whereIn('id',[1,7])->select('name')->get();
        $box = DB::table('boxes')->where('boxes.organization_id','=',$request->id)->leftJoin('statuses','boxes.status_id','=','statuses.id')->select('boxes.id as id','boxes.name as box','statuses.name as status')->get();
        $data['statuses']=$status;
        $data['boxes']=$box;
        return $data;
    }
    public function upBoxStatus(Request $request){
        

        $status=Status::where('name',$request->status)->first();
        $box=Box::where('id',$request->id)->first();
        $box->status_id=$status->id;
        $box->save();
        return $request->id;
    }
    
}
