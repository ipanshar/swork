<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Entries;
use App\Models\Invoice;
use App\Models\Subapplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Svg\Tag\Rect;

class AccountingController extends Controller
{
    public function add_entries(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'organization_id' => 'required',
                    'service_id' => 'required',
                    'service_price' => 'required',
                    'service_count' => 'required',
                    'public_date' => 'required',

                ]
            );
            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! не заполнены обьязательные поля',
                    'errors' => $validate->errors()
                ], 401);
            }
            $entries = Entries::create([
                'organization_id' => $request->organization_id,
                'subject_id' => $request->subject_id,
                'subject_count' => $request->subject_count,
                'service_id' => $request->service_id,
                'service_price' => $request->service_price,
                'service_count' => $request->service_count,
                'total_sum' => $request->service_count * $request->service_price,
                'coment' => $request->coment,
                'public_date' => $request->public_date,
                'user_id' => $user->id,
            ]);
            return response()->json([
                'status' => true,
                'message' =>  'Проводка добавлена'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function entries_list()
    {
        $data = DB::table('entries')->where('entries.invoice_id', '=', '0')
            ->leftJoin('organizations', 'entries.organization_id', '=', 'organizations.id')->leftJoin('counterparties', 'organizations.counterparty_id', '=', 'counterparties.id')
            ->leftJoin('subjects', 'entries.subject_id', '=', 'subjects.id')->leftJoin('services', 'entries.service_id', '=', 'services.id')
            ->select(
                'entries.id as id',
                DB::raw("CONCAT(counterparties.name,':',counterparties.code) as counterparty, CONCAT(subjects.name,'-',entries.subject_count) as subject"),
                'organizations.name as company',
                'services.name as service',
                'entries.service_count as service_count',
                'entries.service_price as service_price',
                'entries.total_sum as total_sum',
                'entries.coment as coment',
                'entries.public_date as public_date',
            )->orderBy('entries.id', 'desc')->get();
        return $data;
    }
    public function entries_update(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'id' => 'required',
                    'service_price' => 'required',
                    'service_count' => 'required',
                    'public_date' => 'required',

                ]
            );
            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Не удалось сохранить! не заполнены обьязательные поля',
                    'errors' => $validate->errors()
                ], 401);
            }
            $entries = Entries::where('id', $request->id)->first();
            $entries->service_count = $request->service_count;
            $entries->service_price = $request->service_price;
            $entries->total_sum = $request->service_count * $request->service_price;
            $entries->coment = $request->coment;
            $entries->public_date = $request->public_date;
            $entries->user_id = $user->id;
            $entries->save();
            return response()->json([
                'status' => true,
                'message' =>  'Проводка обновлена'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function entries_delete(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        $delete = Entries::where('id', $request->id)->delete();
    }
    public function app_list_end()
    {
        $application = DB::table('applications')->where('applications.status_id', '=', 2)->leftJoin('organizations', 'applications.organization_id', '=', 'organizations.id')
            ->leftJoin('counterparties', 'organizations.counterparty_id', '=', 'counterparties.id')->select('applications.id as value', DB::raw("CONCAT(applications.id,'-',organizations.name,'(',counterparties.name,')') as label"))->get();
        return $application;
    }
    public function app_reports(Request $request)
    {

        $reports = DB::table('applications')->where('applications.id', $request->id)->leftJoin('subapplications', 'applications.id', '=', 'subapplications.application_id')->leftJoin('services', 'subapplications.service_id', '=', 'services.id')
            ->leftJoin('subjects', 'applications.subject_id', '=', 'subjects.id')->where('subapplications.status_id', 1)->select(
                'subapplications.id as id',
                'subjects.name as subject',
                'applications.subject_id as subject_id',
                'subapplications.article_num as subject_count',
                'subapplications.service_id as service_id',
                'services.name as service',
                'services.price as service_price',
                'subapplications.service_total as service_count',
                'subapplications.description as coment',
                'applications.updated_at as public_date',
                'applications.organization_id as organization_id'
            )->get();
        return $reports;
    }
    public function app_status_end(Request $request)
    {
        $subapp['update'] = Subapplication::where('id', $request->subaplication_id)->first();
        $subapp['update']->status_id = 2;
        $subapp['update']->save();
        $subapp['count'] = Subapplication::where('application_id', $subapp['update']->application_id)->where('status_id', '=', 1)->first();
        if ($subapp['count'] === null) {
            $subapp['appUpdate'] = Application::where('id', $subapp['update']->application_id)->first();
            $subapp['appUpdate']->status_id = 4;
            $subapp['appUpdate']->save();
        }
    }
    public function counter_enties(Request $request)
    {
        $data = DB::table('entries')->where('entries.invoice_id', '=', '0')
            ->leftJoin('organizations', 'entries.organization_id', '=', 'organizations.id')->where('organizations.counterparty_id', $request->id)->leftJoin('counterparties', 'organizations.counterparty_id', '=', 'counterparties.id')
            ->leftJoin('subjects', 'entries.subject_id', '=', 'subjects.id')->leftJoin('services', 'entries.service_id', '=', 'services.id')
            ->select(
                'entries.id as id',
                DB::raw("CONCAT(counterparties.name,':',counterparties.code) as counterparty, CONCAT(subjects.name,'-',entries.subject_count) as subject"),
                'organizations.name as company',
                'services.name as service',
                'entries.service_count as service_count',
                'entries.service_price as service_price',
                'entries.total_sum as total_sum',
                'entries.coment as coment',
                'entries.public_date as public_date',
            )->orderBy('entries.id', 'desc')->get();
        return $data;
    }
    public function create_bill(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 3) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        $ids = $request->id;
        $invoice['accrual'] = Invoice::where('counterparty_id', $request->counterparty_id)->sum('accrual');
        $invoice['payment'] = Invoice::where('counterparty_id', $request->counterparty_id)->sum('payment');
        $invoice['balance'] =  $invoice['accrual'] - $invoice['payment'];
        $invoice['create'] = Invoice::create([
            'counterparty_id'=>$request->counterparty_id,
            'accrual' => $request->total_sum,
            'balance' => $invoice['balance'],
            'user_id' => $user->id,
        ]);
        foreach ($ids as $id){
            $entries=Entries::where('id',$id)->update(['invoice_id'=>$invoice['create']->id]);
        }
        return response()->json([
            'status' => true,
            'message' =>  'Проводка добавлена'
        ], 200);
    }
}
