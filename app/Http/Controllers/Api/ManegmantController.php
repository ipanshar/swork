<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Counterparty;
use App\Models\Organization;
use App\Models\Service;
use App\Models\Subapplication;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ManegmantController extends Controller
{
    //--------Создание контагента
    public function createCounterparty(Request $request)
    {
        $user = User::where('email', $request->uemail)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateCounterparty = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:150',
                    'phone' => 'required|unique:counterparties,phone|max:25',
                    'phone2' => 'nullable|max:25',
                    'email' => 'nullable|email'
                ]
            );

            if ($valdateCounterparty->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! Возможные причины: номер телефона уже имеется в базе, формат данных не соответствует инструкции',
                    'errors' => $valdateCounterparty->errors()
                ], 401);
            }

            $counterparty = Counterparty::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'phone2' => $request->phone2,
                'email' => $request->email,
                'user_id' => $user->id
            ]);
            $code = 'EL' . str_pad($counterparty->id, 4, '0', STR_PAD_LEFT);
            $counterparty->code = $code;
            $counterparty->save();

            return response()->json([
                'status' => true,
                'message' =>  'Добавлен контрагент: ' . $code . ' - ' . $request->name,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //--------Изменить данные контагента
    public function updateCounterparty(Request $request)
    {
        $user = User::where('email', $request->uemail)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateCounterparty = Validator::make(
                $request->all(),
                [
                    'id' => 'required',
                    'name' => 'required|max:150',
                    'phone2' => 'nullable|max:25',
                    'email' => 'nullable|email'
                ]
            );

            if ($valdateCounterparty->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! Формат данных не соответствует инструкции',
                    'errors' => $valdateCounterparty->errors()
                ], 401);
            }

            $counterparty = Counterparty::where('id', $request->id)->first();
            if (!$counterparty == null) {
                $counterparty->name = $request->name;
                $counterparty->phone2 = $request->phone2;
                $counterparty->email = $request->email;
                $counterparty->user_id = $user->id;
                $counterparty->save();
                return response()->json([
                    'status' => true,
                    'message' =>  'Данные контрагента обновлены!',
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => 'Контрагент не найден!'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //--------Изменить основной номер телефона контагента
    public function updateCounterpartyPhone(Request $request)
    {
        $user = User::where('email', $request->uemail)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateCounterparty = Validator::make(
                $request->all(),
                [
                    'id' => 'required',
                    'phone' => 'required|unique:counterparties,phone|max:25',
                ]
            );

            if ($valdateCounterparty->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось изменить! Возможные причины: номер телефона уже имеется в базе, формат данных не соответствует инструкции',
                    'errors' => $valdateCounterparty->errors()
                ], 401);
            }

            $counterparty = Counterparty::where('id', $request->id)->first();
            if (!$counterparty == null) {
                $counterparty->phone = $request->phone;
                $counterparty->user_id = $user->id;
                $counterparty->save();
                return response()->json([
                    'status' => true,
                    'message' =>  'Основной номер телефона контрагента изменен!',
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => 'Контрагент не найден!'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    //------Выборка контрагентов
    public function counterparties()
    {

        $counterparties = Counterparty::select('id as value', DB::raw('CONCAT(code,"-", name)as label'))->get();

        return $counterparties;
    }
    //------Данные контрагента
    public function counterparty(Request $request)
    {

        $counterparty = Counterparty::where('id', $request->id)->select('code', 'name', 'phone', 'phone2', 'email', 'created_at')->get();

        return $counterparty;
    }
    //-------Добавляем организацию
    public function createOrganization(Request $request)
    {
        $user = User::where('email', $request->uemail)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateOrganization = Validator::make(
                $request->all(),
                [
                    'id' => 'required',
                    'name' => 'required|max:150',
                    'inn' => 'max:15',
                ]
            );
            if ($valdateOrganization->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! формат данных не соответствует инструкции',
                    'errors' => $valdateOrganization->errors()
                ], 401);
            }
            $organization = Organization::create([
                'counterparty_id' => $request->id,
                'name' => $request->name,
                'inn' => $request->inn,
                'user_id' => $user->id,
            ]);
            return response()->json([
                'status' => true,
                'message' =>  'Добавлена организация'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    //----Выборка организаций
    public function OrganizationsList(Request $request)
    {
        $OrganizationsList = Organization::select('name', 'inn', 'active')->where('counterparty_id', $request->id)->get();
        return $OrganizationsList;
    }

    public function createSubject(Request $request)
    {

        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateSubject = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:150|unique:subjects,name'
                ]
            );
            if ($valdateSubject->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! формат данных не соответствует инструкции',
                    'errors' => $valdateSubject->errors()
                ], 401);
            }
            $subject = Subject::create([
                'name' => $request->name,
                'user_id' => $user->id,
            ]);
            return response()->json([
                'status' => true,
                'message' =>  'Предмет добавлен'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function updateSubject(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateSubject = Validator::make(
                $request->all(),
                [
                    'id' => 'required|numeric',
                    'name' => 'required|max:150'
                ]
            );
            if ($valdateSubject->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! формат данных не соответствует инструкции',
                    'errors' => $valdateSubject->errors()
                ], 401);
            }
            $subject = Subject::where('id', $request->id)->first();
            $subject->name = $request->name;
            $subject->user_id = $user->id;
            $subject->save();
            return response()->json([
                'status' => true,
                'message' =>  'Предмет изменен'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function subjects()
    {
        $subject = Subject::select('id', 'name')->paginate(30);
        return $subject;
    }

    public function subjects_org(Request $request)
    {
        $subjects_org = DB::table('articles')->leftJoin('subjects', 'articles.subject_id', '=', 'subjects.id')->select('articles.subject_id as value', 'subjects.name as label')->groupBy('articles.subject_id', 'subjects.name')
            ->where('articles.organization_id', '=', $request->organization_id)->get();
        return $subjects_org;
    }
    public function service_app(Request $request)
    {
        $service_app = Service::where('category_id', '=', $request->category_id)->select('id as value', 'name as label', 'rate')->get();
        return $service_app;
    }

    public function create_application(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try{
            $valdate = Validator::make(
                $request->all(),
                [
                    'organization_id' => 'required|numeric',
                    'subject_id' => 'required|numeric',
                    'anotation' => 'max:250',
                    'razbivka' => 'required'
                ]
            );if ($valdate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! формат данных не соответствует инструкции',
                    'errors' => $valdate->errors()
                ], 401);
            }
            $application = Application::create([
                'status_id' => 1,
                'subject_id' => $request->subject_id,
                'organization_id' => $request->organization_id,
                'razbivka' => $request->razbivka,
                'description' => $request->anotation,
                'create_user_id' => $user->id,
            ]);
            $datas=$request->service_data;
            foreach($datas as $data){
                if($data['service_id']>0){
                    $subaplication = Subapplication::create([
                    'application_id'=>$request->id,
                    'organization_id'=>$request->organization_id,
                    'service_id'=>$data['service_id'],
                    'service_num'=>$data['num'],
                    'rate'=>$data['rate'],
                    'description'=>$data['description'],
                    'user_id'=>$user->id,
                ]);
                }
            }
            return response()->json([
                'status' => true,
                'message' =>  'Задача создана'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update_application(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try{
            $valdate = Validator::make(
                $request->all(),
                [
                    'id' => 'required|numeric',
                    'organization_id' => 'required|numeric',
                    'anotation' => 'max:250',
                    'razbivka' => 'required'
                ]
            );if ($valdate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! формат данных не соответствует инструкции',
                    'errors' => $valdate->errors()
                ], 401);
            }
            $application = Application::where('id',$request->id)->first(); 
            $application->description = $request->anotation;
            $application->razbivka = $request->razbivka;
            $application->update_user_id = $user->id;
            $application->save();
            $delete=Subapplication::where('application_id',$request->id)->delete();
            $datas=$request->service_data;
            foreach($datas as $data){
                if($data['service_id']>0){
                    $subaplication = Subapplication::create([
                    'application_id'=>$request->id,
                    'organization_id'=>$request->organization_id,
                    'service_id'=>$data['service_id'],
                    'service_num'=>$data['num'],
                    'rate'=>$data['rate'],
                    'description'=>$data['description'],
                    'user_id'=>$user->id,
                ]);
                }
            }
            return response()->json([
                'status' => true,
                'message' =>  'Задача обновлена'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function aplications(Request $request){
        
        $application = DB::table('applications')->leftJoin('subjects','applications.subject_id','=','subjects.id')
        ->leftJoin('organizations','applications.organization_id','=','organizations.id')->leftJoin('counterparties','organizations.counterparty_id','=','counterparties.id')
        ->leftJoin('users','applications.create_user_id','users.id')->leftJoin('statuses','applications.status_id','=','statuses.id')
        ->select('applications.id as id',DB::RAW('CONCAT(organizations.name, "-" ,counterparties.name)as organization'),'subjects.name as subject','applications.razbivka as razbivka','users.name as name','applications.created_at as created_at','applications.description as description','applications.status_id as status_id','statuses.name as status','applications.organization_id as organization_id' )
        ->whereIn('applications.status_id',$request->status_id)->orderBy('id','desc')->paginate(20);
       //
       $cur_page=$application->currentPage();
       $last_page=$application->lastPage();
       $dd=$application->items() ;
       $dada=[];
       foreach($dd as $d){
        $subApp= DB::table('subapplications')->leftJoin('services','subapplications.service_id','=','services.id')->leftJoin('valutas','services.valuta_id','=','valutas.id')
        ->where('subapplications.application_id','=',$d->id)
        ->select(DB::raw('GROUP_CONCAT(services.name,":", subapplications.service_num,"x",subapplications.rate,valutas.symbol SEPARATOR " | ") as gr'))->get();
        $val=['id'=>$d->id,
        'organization'=>$d->organization,
        'subject'=>$d->subject,
        'services'=> $subApp[0]->gr,
        'name'=>$d->name,
        'created_at'=>$d->created_at,
        'razbivka'=>$d->razbivka,
        'description'=>$d->description,
        'status_id'=>$d->status_id,
        'status'=>$d->status,
    ];
        array_push($dada, $val);
       }
       $page['cur_page']=$cur_page;
       $page['last_page']=$last_page;
       $page['data']=$dada;
        return $page;
    }

    public function update_app_status(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try{
            $valdate = Validator::make(
                $request->all(),
                [
                    'id' => 'required|numeric',
                    'status_id' => 'required|numeric',
                ]
            );if ($valdate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось обновить! формат данных не соответствует инструкции',
                    'errors' => $valdate->errors()
                ], 401);
            }
            $application = Application::where( 'id', $request->id)->first();
            $application->status_id= $request->status_id;
            $application->update_user_id = $user->id;
           $application->save();
            return response()->json([
                'status' => true,
                'message' =>  'Задача обновлена'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function appSub(Request $request){
        $application=DB::table('applications')->leftJoin('organizations','applications.organization_id','=','organizations.id')->leftJoin('subjects','applications.subject_id','subjects.id')
        ->leftJoin('counterparties','organizations.counterparty_id','=','counterparties.id')->where('applications.id','=',$request->id)->select(DB::raw('concat(organizations.name, "-",counterparties.name) as organization'),'subjects.name as subject',
        'applications.description as anotation','applications.razbivka as razbivka','applications.organization_id')->get();
        $subaplication=Subapplication::where('application_id',$request->id)->select('service_id','service_num as num','rate','description')->get();
        $data['application']=$application;
        $data['subaplication']=$subaplication;
        return $data;
    }
}
