<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Box;
use App\Models\Counterparty;
use App\Models\Dogovor;
use App\Models\Entries;
use App\Models\Merchandise;
use App\Models\Operation;
use App\Models\Organization;
use App\Models\Salary;
use App\Models\Service;
use App\Models\Smena;
use App\Models\Subapplication;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PDF;

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
        $OrganizationsList = Organization::select('id','name', 'inn', 'active')->where('counterparty_id', $request->id)->get();
        return $OrganizationsList;
    }
public function update_organization(Request $request){
    $user = User::where('email', $request->uemail)->first();
    $update = Organization::where('id',$request->id)->update([
        'name' => $request->name,
        'inn' => $request->inn,
        'user_id' => $user->id,
    ]);
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
    public function service_price(Request $request)
    {   $data=[];
        $service_app = Service::where('category_id', '=', $request->category_id)->get();
        foreach($service_app as $ser){
          $price = $this->price($ser->id,$request->organization_id);
          $val =['value'=>$ser->id,'label'=>$ser->name,'rate'=>$ser->rate,'price'=>$price];
          array_push($data,$val);
        }
        return $data;
    }
    public function price($service_id,$organization_id){
        $counter = Organization::where('id',$organization_id)->first();
        $dogovor = Dogovor::where('counterparty_id',$counter->counterparty_id)->where('service_id',$service_id)->first();
        if($dogovor !=null){
           return $dogovor->price;
        }else{
            $price = Service::where('id',$service_id)->first();
             return $price->price;
        }
       
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
        try {
            $valdate = Validator::make(
                $request->all(),
                [
                    'organization_id' => 'required|numeric',
                    'subject_id' => 'required|numeric',
                    'anotation' => 'max:250',
                    'razbivka' => 'required'
                ]
            );
            if ($valdate->fails()) {
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
            $datas = $request->service_data;
            foreach ($datas as $data) {
                if ($data['service_id'] > 0) {
                    $subaplication = Subapplication::create([
                        'application_id' => $application->id,
                        'organization_id' => $request->organization_id,
                        'service_id' => $data['service_id'],
                        'service_num' => $data['num'],
                        'rate' => $data['rate'],
                        'description' => $data['description'],
                        'user_id' => $user->id,
                    ]);
                }
            }
            if($request->razbivka==1){
                $rate = Service::where('id','=',8)->first();
                $subaplication = Subapplication::create([
                    'application_id' => $application->id,
                    'organization_id' => $request->organization_id,
                    'service_id' => 8,
                    'service_num' =>1,
                    'rate' =>$rate->rate,
                    'description' =>'',
                    'user_id' => $user->id,
                ]);
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
        try {
            $valdate = Validator::make(
                $request->all(),
                [
                    'id' => 'required|numeric',
                    'organization_id' => 'required|numeric',
                    'anotation' => 'max:250',
                    'razbivka' => 'required'
                ]
            );
            if ($valdate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! формат данных не соответствует инструкции',
                    'errors' => $valdate->errors()
                ], 401);
            }
            $application = Application::where('id', $request->id)->first();
            $application->description = $request->anotation;
            $application->razbivka = $request->razbivka;
            $application->update_user_id = $user->id;
            $application->save();
            $delete = Subapplication::where('application_id', $request->id)->delete();
            $datas = $request->service_data;
            foreach ($datas as $data) {
                if ($data['service_id'] > 0) {
                    $subaplication = Subapplication::create([
                        'application_id' => $request->id,
                        'organization_id' => $request->organization_id,
                        'service_id' => $data['service_id'],
                        'service_num' => $data['num'],
                        'rate' => $data['rate'],
                        'description' => $data['description'],
                        'user_id' => $user->id,
                    ]);
                }
            }
            if($request->razbivka==1){
                $rate = Service::where('id','=',8)->first();
                $subaplication = Subapplication::create([
                    'application_id' => $application->id,
                    'organization_id' => $request->organization_id,
                    'service_id' => 8,
                    'service_num' =>1,
                    'rate' =>$rate->rate,
                    'description' =>'',
                    'user_id' => $user->id,
                ]);
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

    public function aplications(Request $request)
    {

        $application = DB::table('applications')->leftJoin('subjects', 'applications.subject_id', '=', 'subjects.id')
            ->leftJoin('organizations', 'applications.organization_id', '=', 'organizations.id')->leftJoin('counterparties', 'organizations.counterparty_id', '=', 'counterparties.id')
            ->leftJoin('users', 'applications.create_user_id', 'users.id')->leftJoin('users as managers', 'applications.update_user_id', 'managers.id')->leftJoin('statuses', 'applications.status_id', '=', 'statuses.id')
            ->select('applications.id as id', DB::RAW('CONCAT(organizations.name, "-" ,counterparties.name)as organization'), 'subjects.name as subject', 'applications.razbivka as razbivka', 'users.name as name', 'applications.created_at as created_at', 'applications.description as description', 'applications.status_id as status_id', 'statuses.name as status', 'applications.organization_id as organization_id','managers.name as manager','applications.updated_at as updated_at')
            ->whereIn('applications.status_id', $request->status_id)->orderBy('id', 'desc')->paginate(20);
        //
        $cur_page = $application->currentPage();
        $last_page = $application->lastPage();
        $dd = $application->items();
        $dada = [];
        foreach ($dd as $d) {
            $box = Box::where('application_id', $d->id)->count();
            $operation = Operation::where('application_id', $d->id)->sum('num');
            $subApp = DB::table('subapplications')->leftJoin('services', 'subapplications.service_id', '=', 'services.id')->leftJoin('valutas', 'services.valuta_id', '=', 'valutas.id')
                ->where('subapplications.application_id', '=', $d->id)
                ->select(DB::raw('GROUP_CONCAT(services.name,":", subapplications.service_num,"x",subapplications.rate,valutas.symbol SEPARATOR " | ") as gr'))->get();
            $val = [
                'id' => $d->id,
                'organization' => $d->organization,
                'subject' => $d->subject,
                'services' => $subApp[0]->gr,
                'name' => $d->name,
                'created_at' => $d->created_at,
                'updated_at' => $d->updated_at,
                'razbivka' => $d->razbivka,
                'description' => $d->description,
                'status_id' => $d->status_id,
                'status' => $d->status,
                'box' => $box,
                'operation' => $operation,
                'manager' => $d->manager,
            ];
            array_push($dada, $val);
        }
        $page['cur_page'] = $cur_page;
        $page['last_page'] = $last_page;
        $page['data'] = $dada;
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
        try {
            $valdate = Validator::make(
                $request->all(),
                [
                    'id' => 'required|numeric',
                    'status_id' => 'required|numeric',
                ]
            );
            if ($valdate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось обновить! формат данных не соответствует инструкции',
                    'errors' => $valdate->errors()
                ], 401);
            }
            $application = Application::where('id', $request->id)->first();
            $application->status_id = $request->status_id;
            $application->update_user_id = $user->id;
            $application->save();
             if($request->status_id==2){
                 $count['articles'] = DB::table('operations')->where('application_id','=',$request->id)->sum('num');
                 $count['boxes'] = DB::table('boxes')->where('application_id','=',$request->id)->count('id');
                 $subaplication['article_num'] =Subapplication::where('application_id','=',$request->id)->update(['article_num'=>$count['articles']]);
                 $subaplication['service_total'] =Subapplication::where('application_id','=',$request->id)->update(['service_total'=>DB::raw('article_num*service_num')]);
                 $subaplication['boxes'] =Subapplication::where('application_id','=',$request->id)->where('service_id','=',8)->update(['service_total'=>$count['boxes']]);
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
    public function appSub(Request $request)
    {
        $application = DB::table('applications')->leftJoin('organizations', 'applications.organization_id', '=', 'organizations.id')->leftJoin('subjects', 'applications.subject_id', 'subjects.id')
            ->leftJoin('counterparties', 'organizations.counterparty_id', '=', 'counterparties.id')->where('applications.id', '=', $request->id)->select(
                DB::raw('concat(organizations.name, "-",counterparties.name) as organization'),
                'subjects.name as subject',
                'applications.description as anotation',
                'applications.razbivka as razbivka',
                'applications.organization_id'
            )->get();
        $subaplication = Subapplication::where('application_id', $request->id)->select('service_id', 'service_num as num', 'rate', 'description')->get();
        $data['application'] = $application;
        $data['subaplication'] = $subaplication;
        return $data;
    }
    public function exelRazbivka(Request $request)
    {
        $data = DB::table('operations')->where('operations.application_id', '=', $request->application_id)->leftJoin('boxes','operations.box_id','=','boxes.id')
        ->leftJoin('articles','operations.article_id','=','articles.id')
        ->selectRaw('(CASE WHEN boxes.name is null THEN 0 ELSE boxes.name  END)  as box,articles.code as barcode, articles.name as article, (CASE WHEN articles.size is null THEN 0 ELSE articles.size END) as size, sum(operations.num) as quantity')
            ->groupBy('boxes.name','articles.code','articles.name','articles.size')->get();
        return $data;
    }
    //SHK_BOX_PDF_GENERATEs

    public function shkBox(Request $request)
    {
        $application = $request->application_id;
        $operations = '';
        $data = '';
        $title = DB::table('applications')->where('applications.id', '=', $application)->leftJoin('organizations', 'applications.organization_id', '=', 'organizations.id')
            ->select('organizations.name as name', 'organizations.counterparty_id as owner', 'applications.id as id')->first();
        if (isset($request->box_id)) {
            $operations = DB::table('operations')->where('operations.application_id', '=', $application)->where('operations.box_id', '=', $request->box_id)->selectRaw('sum(operations.num) as num , articles.name as name,articles.size as size,operations.box_id as box_id')
                ->leftJoin('articles', 'operations.article_id', '=', 'articles.id')
                ->groupBy('operations.article_id', 'articles.name', 'articles.size', 'operations.box_id')->get();
            $data = DB::table('boxes')->where('boxes.id', '=', $request->box_id)->leftJoin('users', 'boxes.user_id', '=', 'users.id')->select('boxes.name as box_name', 'boxes.created_at as created_at', 'users.name as user_name', 'boxes.id as id')->get();
        } else {


            $operations = DB::table('operations')->where('operations.application_id', '=', $application)->selectRaw('sum(operations.num) as num , articles.name as name,articles.size as size,operations.box_id as box_id')
                ->leftJoin('articles', 'operations.article_id', '=', 'articles.id')
                ->groupBy('operations.article_id', 'articles.name', 'articles.size', 'operations.box_id')->get();
            $data = DB::table('boxes')->where('boxes.application_id', '=', $application)->leftJoin('users', 'boxes.user_id', '=', 'users.id')->select('boxes.name as box_name', 'boxes.created_at as created_at', 'users.name as user_name', 'boxes.id as id')->get();
        }
        $customPaper = array(0, 0, 212.59, 340.15);
        $pdf = PDF::setOption(['defaultFont' => 'dejavu sans'])->loadView('shk_box', compact('title', 'data', 'operations'))->setPaper($customPaper, 'landscape');
        return $pdf->download($application . '_shk_box.pdf');
    }

    public function service_list(Request $request)
    {
        $service_list = Service::where('category_id','<>',4)->select('id as value', 'name as label','rate', 'price')->get();
        return $service_list;
    }
    public function merchandis_create(Request $request){
        $user = User::where('email',$request->email)->first();
        $merchandise = Merchandise::create([
                'organization_id'=>$request->organization_id,
                'service_id'=>$request->service_id,
                'service_count'=>$request->service_count,
                'price'=>$request->price,
                'rate'=>$request->rate,
                'accrued'=>$request->accrued,
                'user_id'=>$user->id,
        ]);
        $entries = Entries::create([
            'organization_id' => $request->organization_id,
            'service_id' => $request->service_id,
            'service_price' => $request->price,
            'service_count' => $request->service_count,
            'total_sum' => $request->price*$request->service_count,
            'public_date' => date('Y-m-d'),
            'user_id' => $user->id,
        ]);
       return $this-> merchandis_row();
    }
    public function merchandis_row(){
        $merchandis = DB::table('merchandises')->leftJoin('users','merchandises.user_id','=','users.id')->leftJoin('organizations','merchandises.organization_id','=','organizations.id')->leftJoin('services','merchandises.service_id','=','services.id')
        ->take(100)->select('merchandises.created_at as created_at','merchandises.id as id','organizations.name as organization','services.name as service','merchandises.service_count as service_count',
        'merchandises.accrued as accrued', 'merchandises.salary_id as salary_id','users.name as user')->orderBy('merchandises.id','DESC')->get();
        return $merchandis;
    }
    public function smena(){
        $user = User::where('oklad','>',0)->where('level','>',0)->orderBy('name')->select('id','name','email','grafik','oklad',DB::raw('100 as stavka'))->get();
      return $user;
    }
    public function smena_insert(Request $request){
        $user = User::where('email', $request->email)->first();
       
        $personals = $request->personals;
     
        foreach($personals as $personal){
            $id=$personal[0];
            $oklad = $personal[1];
            $stavka = $personal[2];
        $accrued =($oklad * $stavka)/100;
            $create_smena = Smena::create([
                'personal_id'=>$id,
                'oklad'=>$oklad,
                'percent'=>$stavka,
                'acrued'=>$accrued,
                'user_id'=>$user->id
            ]);
            $b = Salary::where('personal_id', $id)->orderBy('id', 'DESC')->select('balance')->first();
            $balance = 0;
            if ($b != null) {
                $balance = $b->balance;
            }
            $balance = $accrued + $balance;
            $create_salary = Salary::create([
                'personal_id' =>$id,
                'accrued' =>$accrued,
                'balance' => $balance,
                'description' => 'Оклад:'.$stavka.'%',
                'user_id' =>$user->id,
            ]);
        }
      return  $this->smena_top();
       
    }
    public function smena_top(){
        $data = DB::table('smenas')->leftJoin('users as personals','smenas.personal_id','=','personals.id')->leftJoin('users','smenas.user_id','=','users.id')->take(100)->orderBy('smenas.id','desc')
        ->select('smenas.id as id','smenas.created_at as created_at','personals.name as personal','personals.email as email','personals.grafik as grafik','smenas.oklad as oklad','smenas.percent as stavka','smenas.acrued as acrued','users.name as user')->get();
        return $data;
    }

    public function operations_application(Request $request){
        $app=Application::where('id',$request->id)->first();
        $data['status']=$app->status_id;
        $data['operations'] = DB::table('operations')->where('operations.application_id','=',$request->id)->leftJoin('boxes','operations.box_id','boxes.id')->leftJoin('articles','operations.article_id','articles.id')->leftJoin('users','operations.user_id','users.id')
        ->select('operations.id as id','users.name as personal','boxes.name as box','articles.name as article','articles.size as size', 'articles.code as barcode', 'operations.num as quantity')->get();
    return $data;
    }

    public function update_operation_num(Request $request){
        $operation = Operation::where('id',$request->id)->update(['num'=>$request->num]);
    }
}
