<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Models\Valuta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function Termwind\ask;

class AdminController extends Controller
{
    public function users()
    {
        $users = DB::table('users')->select('id','name', 'email', 'level','grafik','oklad','bonus','created_at')->get();
        return $users;
    }

    public function uplevel(Request $request)
    {
        $user=User::where('email',$request->email)->first();
        if($user->level>3){
             $update = User::where('id',$request->id)->update([
            'level'=>$request->level,
            'grafik'=>$request->grafik,
            'oklad'=>$request->oklad,
            'bonus'=>$request->bonus,
        ]);
        return $this->users();
        }
    }

    public function createService(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 4) {
            return response()->json([
                'status' => false,
                'message' =>'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateService = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:150',
                    'rate' => 'required|numeric',
                    'price' => 'required|numeric',
                    'category_id' => 'required|numeric',
                    'valuta_id' => 'required|numeric'
                ]
            );
            if ($valdateService->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! формат данных не соответствует инструкции',
                    'errors' => $valdateService->errors()
                ], 401);
            }
            $service = Service::create([
                'name' => $request->name,
                'rate' => $request->rate,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'valuta_id'=>$request->valuta_id,
                'user_id' => $user->id,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Добавлен новый сервис'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function updateService(Request $request){
        
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 4) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateService = Validator::make(
                $request->all(),
                [   
                    'id'=>'required|numeric',
                    'name' => 'required|max:150',
                    'rate' => 'required|numeric',
                    'price' => 'required|numeric',
                    'category_id' => 'required|numeric',
                    'valuta_id' => 'required|numeric'
                ]
            );
            if ($valdateService->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! формат данных не соответствует инструкции',
                    'errors' => $valdateService->errors()
                ], 401);
            }
            $service= Service::where('id',$request->id)->first();
            $service->name = $request->name;
            $service->rate = $request->rate;
            $service->price = $request->price;
            $service->category_id = $request->category_id;
            $service->valuta_id = $request->valuta_id;
            $service->user_id =  $user->id;
            $service->save();
            return response()->json([
                'status' => true,
                'message' => 'Сервис обновлен'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function services(Request $request){
        $services = DB::table('services')->where('services.category_id',$request->category_id)->leftJoin('valutas','services.valuta_id','=','valutas.id')->select('services.id','services.name','services.rate','services.price','services.valuta_id','valutas.code',)->orderBy('services.name')->get();
        return $services;
    }

    public function CategoryList(){
        $CategoryList = Category::select('id as value','name as label')->orderBy('name')->get();
        return $CategoryList;
    }

    public function valuta(){
        $valuta = Valuta::select('id as value', 'code as label')->get();
        return $valuta;
    }
    
    
}
