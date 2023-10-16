<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Box;
use App\Models\Cashbox;
use App\Models\Counterparty;
use App\Models\Dogovor;
use App\Models\Entries;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Merchandise;
use App\Models\Operation;
use App\Models\Organization;
use App\Models\Salary;
use App\Models\Service;
use App\Models\Subapplication;
use App\Models\Transport;
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
            $total_sum = $request->service_count * $request->service_price;
            if ($request->service_id === 9) {
                $total_sum = 0 - $total_sum;
            }
            $entries = Entries::create([
                'organization_id' => $request->organization_id,
                'subject_id' => $request->subject_id,
                'subject_count' => $request->subject_count,
                'service_id' => $request->service_id,
                'service_price' => $request->service_price,
                'service_count' => $request->service_count,
                'total_sum' => $total_sum,
                'coment' => $request->coment,
                'public_date' => $request->public_date,
                'user_id' => $user->id,
            ]);
            if ($request->service_id === 6) {
                $org = Organization::where('id', $request->organization_id)->first();
                $cashbox = $this->insert_cashbox($org->counterparty_id, 0, 0, 0, 8, 0, $total_sum, $request->coment, $user->id);
            }
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
    public function app_reports(Request $request)
    {

        $reports = DB::table('applications')->where('applications.status_id', '=', 2)->leftJoin('organizations', 'applications.organization_id', '=', 'organizations.id')->leftJoin('subapplications', 'applications.id', '=', 'subapplications.application_id')->leftJoin('services', 'subapplications.service_id', '=', 'services.id')
            ->leftJoin('subjects', 'applications.subject_id', '=', 'subjects.id')->where('subapplications.status_id', 1)->leftJoin('counterparties', 'organizations.counterparty_id', '=', 'counterparties.id')->select(
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
                'applications.organization_id as organization_id',
                DB::raw("CONCAT(applications.id,'-',organizations.name,'(',counterparties.name,')') as organizations")
            )->get();
            $data=[];
            foreach($reports as $re){
                $price = $this->price($re->service_id,$re->organization_id); 
                $val= [
                    'id'=>$re->id,
                    'subject'=>$re->subject,
                    'subject_id'=>$re->subject_id,
                    'subject_count'=>$re->subject_count,
                    'service_id'=>$re->service_id,
                    'service'=>$re->service,
                    'service_price'=>$price,
                    'service_count'=>$re->service_count,
                    'coment'=>$re->coment,
                    'public_date'=>$re->public_date,
                    'organization_id'=>$re->organization_id, 
                    'organizations'=>$re->organizations
                ];
                array_push($data,$val);
            }
        return $data;
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
            'counterparty_id' => $request->counterparty_id,
            'accrual' => $request->total_sum,
            'balance' => $invoice['balance'],
            'user_id' => $user->id,
        ]);
        foreach ($ids as $id) {
            $entries = Entries::where('id', $id)->update(['invoice_id' => $invoice['create']->id]);
        }
        return response()->json([
            'status' => true,
            'message' =>  'Проводка добавлена',
            'id' => $invoice['create']->id,
        ], 200);
    }
    public function invoice_view(Request $request)
    {
        $invoice = DB::table('invoices')->where('invoices.id', $request->id)->leftJoin('counterparties', 'invoices.counterparty_id', '=', 'counterparties.id')
            ->selectRaw('invoices.accrual as accrual,invoices.payment as payment,invoices.balance as balance, invoices.created_at as created_at,CONCAT(counterparties.code, " - ", counterparties.name) as  counterparty')->first();
        $data['accrual'] = $invoice->accrual;
        $data['payment'] = $invoice->payment;
        $data['balance'] = $invoice->balance;
        $data['created_at'] = $invoice->created_at;
        $data['counterparty'] = $invoice->counterparty;
        $data['organizations'] = DB::table('entries')->where('entries.invoice_id', $request->id)->leftJoin('organizations', 'entries.organization_id', '=', 'organizations.id')
            ->groupBy('organizations.id','organizations.name','organizations.inn')->select('organizations.id as organization_id',  DB::raw('CONCAT(organizations.name, CASE WHEN organizations.inn > 0 then CONCAT(" - ИНН:", organizations.inn) ELSE "" END)as org_name'))->get();
        $data['entries'] = DB::table('entries')->where('entries.invoice_id', $request->id)
            ->leftJoin('organizations', 'entries.organization_id', '=', 'organizations.id')
            ->leftJoin('subjects', 'entries.subject_id', '=', 'subjects.id')->leftJoin('services', 'entries.service_id', '=', 'services.id')
            ->select(
                'entries.id as id',
                DB::raw(" CONCAT(subjects.name,'-',entries.subject_count) as subject"),
                'organizations.id as organization_id',
                'services.name as service',
                'entries.service_count as service_count',
                'entries.service_price as service_price',
                'entries.total_sum as total_sum',
                'entries.coment as coment',
                'entries.public_date as public_date',
            )->orderBy('entries.id', 'desc')->get();
        return $data;
    }
    public function coun_journal(Request $request)
    {
        $accrual = Invoice::where('counterparty_id', $request->id)->sum('accrual');
        $payment = Invoice::where('counterparty_id', $request->id)->sum('payment');
        $data['balance'] = $accrual - $payment;
        $data['rows'] = DB::table('invoices')->where('invoices.counterparty_id', $request->id)->leftJoin('users', 'invoices.user_id', '=', 'users.id')
            ->selectRaw('invoices.created_at as public_date,invoices.id as id,
        CASE WHEN invoices.accrual = 0 AND invoices.payment=0 THEN "Аннулирован" WHEN invoices.accrual > 0 THEN "Счет на оплату" WHEN invoices.payment > 0 THEN "Принят платеж" ELSE "" END as description,
        invoices.accrual as accrual,invoices.payment as payment,users.name as user')->orderBy('invoices.id', 'desc')->get();
        return $data;
    }
    public function delete_invoice(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $Invoice = Invoice::where('id', $request->id)->update(['accrual' => 0, 'payment' => 0, 'balance' => 0, 'user_id' => $user->id]);
        $entries = Entries::where('invoice_id', $request->id)->update(['invoice_id' => 0]);
    }
    public function counterparties()
    {  $counterparties =  Counterparty::get();
        $data=[];
        foreach($counterparties as $counter){
            $accrual = Invoice::where('counterparty_id', $counter->id)->sum('accrual');
            $payment = Invoice::where('counterparty_id', $counter->id)->sum('payment');
            $val=['value'=>$counter->id,'label'=>$counter->code.'-'.$counter->name,'accrual'=>$accrual,'payment'=>$payment];
            array_push($data,$val);
        }
        return $data;
    }
    public function items()
    {
        $items = Item::select('id', 'name', 'key', 'group')->get();
        return $items;
    }
    public function add_item(Request $request)
    {
        $item = Item::create(['name' => $request->name, 'key' => $request->key, 'group' => $request->group]);
        return $this->items();
    }
    public function group_items(Request $request)
    {
        $items = Item::where('group', $request->group)->select('id', 'name', 'key', 'group')->get();
        return $items;
    }
    public function accept_payment(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $invoice['accrual'] = Invoice::where('counterparty_id', $request->counterparty_id)->sum('accrual');
        $invoice['payment'] = Invoice::where('counterparty_id', $request->counterparty_id)->sum('payment');
        $invoice['balance'] =  $invoice['accrual'] - $invoice['payment'];
        $invoice_id = Invoice::create([
            'counterparty_id' => $request->counterparty_id,
            'payment' => $request->payment,
            'balance' => $invoice['balance'] - $request->payment,
            'user_id' => $user->id,
        ]);
        $cashbox = $this->insert_cashbox($request->counterparty_id, $invoice_id->id, 0, 0, $request->item, $request->payment, 0, $request->description, $user->id);
        if ($cashbox > 0) {
            return response()->json([
                'status' => true,
                'message' =>  'Кассовый ордер добавлен',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Кассовый ордер не добавлен'
            ], 401);
        }
    }
    public function pay_expense(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $cashbox = $this->insert_cashbox($request->counterparty_id, 0, 0, 0, $request->item, 0, $request->payment, $request->description, $user->id);
        if ($cashbox > 0) {
            return response()->json([
                'status' => true,
                'message' =>  'Расходный кассовый ордер добавлен',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Расходный кассовый ордер не добавлен'
            ], 401);
        }
    }
    public function insert_cashbox($counterparty_id, $invoice_id, $salary_id, $personal_id, $item_id, $incoming, $expense, $description, $user_id)
    {
        $cashbox = Cashbox::create([
            'counterparty_id' => $counterparty_id,
            'invoice_id' => $invoice_id,
            'salary_id' => $salary_id,
            'personal_id' => $personal_id,
            'item_id' => $item_id,
            'incoming' =>  $incoming,
            'expense' => $expense,
            'description' => $description,
            'user_id' => $user_id,
        ]);
        if($item_id!=9){
            $this->bonus($incoming, $expense, $user_id);
        }
        
        return $cashbox->id;
    }
    public function bonus($accrued, $held, $user_id)
    {
        $users = User::where('bonus', '>', 0)->get();
        foreach ($users as $user) {
            $a = ($accrued * $user->bonus) / 100;
            $h = ($held * $user->bonus) / 100;
            $this->salary_insert($user->id, $a, $h, 0, '', $user_id, 1);
        }
    }
    public function personal()
    {
        $personal = User::select('id as value', DB::raw('CONCAT(name,"-",email) as label'), 'oklad as oklad')->get();
        return $personal;
    }

    public function salary_insert($personal_id, $accrued, $held, $paid, $description, $user_id, $partner)
    {
        $b = Salary::where('personal_id', $personal_id)->orderBy('id', 'DESC')->select('balance')->first();
        $balance = 0;
        if ($b != null) {
            $balance = $b->balance;
        }
        $balance = $accrued + $balance - $held - $paid;
        $create = Salary::create([
            'personal_id' => $personal_id,
            'accrued' => $accrued,
            'held' => $held,
            'paid' => $paid,
            'balance' => $balance,
            'description' => $description,
            'user_id' => $user_id,
            'partner' => $partner,
        ]);
        if ($paid > 0) {
            $cashbox = $this->insert_cashbox(0, 0, $create->id, $personal_id, 2, 0, $paid, $description, $user_id);
        }
        return $create->id;
    }
    public function add_salary(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user->level > 3) {
            $personal_id = $request->personal_id;
            $accrued = $request->accrued;
            $held = $request->held;
            $paid = $request->paid;
            $description = $request->description;
            $user_id = $user->id;
            $partner = 0;
            $salary_id = $this->salary_insert($personal_id, $accrued, $held, $paid, $description, $user_id, $partner);
            return $salary_id;
        }
        return 0;
    }
    //
    public function cashbox_top()
    {
        $cashbox['incoming'] = Cashbox::sum('incoming');
        $cashbox['expense'] = Cashbox::sum('expense');

        $cashbox['cashboxes'] = DB::table('cashboxes')->leftJoin('items', 'cashboxes.item_id', '=', 'items.id')->leftJoin('users as personal', 'cashboxes.personal_id', '=', 'personal.id')
            ->leftJoin('users', 'cashboxes.user_id', '=', 'users.id')->leftJoin('counterparties', 'cashboxes.counterparty_id', '=', 'counterparties.id')->orderBy('cashboxes.id', 'DESC')
            ->select('cashboxes.id as id', 'cashboxes.created_at', DB::raw('CASE WHEN cashboxes.personal_id>0 THEN personal.name ELSE counterparties.name END as agent'), 'items.name as item', 'cashboxes.incoming as incoming', 'cashboxes.expense as expense', 'cashboxes.description as description', 'users.name as user')->take(100)->get();
        return $cashbox;
    }
    //
    public function salary_top()
    { $salary['accrued'] = Salary::sum('accrued');
     $salary['held'] = Salary::sum('held');
    $salary['paid'] = Salary::sum('paid');
        $salary['salaries'] = DB::table('salaries')->leftJoin('users as personal', 'salaries.personal_id', '=', 'personal.id')->leftJoin('users', 'salaries.user_id', '=', 'users.id')->where('salaries.partner', 0)->orderBy('salaries.id', 'DESC')
            ->select('salaries.id as id', 'salaries.created_at as created_at', 'personal.name as personal', 'salaries.accrued', 'salaries.held as held', 'salaries.paid as paid', 'salaries.balance as balance', 'users.name as user', 'salaries.description as description',)->take(100)->get();
       
            return $salary;
    }
    public function salary_calculation(Request $request)
    {
        $accounting = User::where('email', $request->email)->first();
        if ($accounting->level < 4) {
            return response()->json([
                'status' => false,
                'message' => 'У вас не достаточно прав'
            ], 401);
        }

        $operations = DB::table('operations')->where('operations.work_sum', 0)->leftJoin('applications', 'operations.application_id', '=', 'applications.id')->where('applications.status_id', 4)->select('operations.id as id', 'operations.application_id as application_id', 'operations.num as num')->get();
        foreach ($operations as $operation) {
            $work_sum = 0;
            $subapplications = Subapplication::where('application_id', $operation->application_id)->where('service_id', '<>', 8)->select(DB::raw('sum(service_num * rate) as rate_sum'))->get();
            foreach ($subapplications as $subapplication) {
                $work_sum = $work_sum + ($operation->num * $subapplication->rate_sum);
            }
            $operation_up = Operation::where('id', $operation->id)->update(['work_sum' => $work_sum]);
        }
        $users =  User::get();
        foreach ($users as $user) {
            $work_sum = Operation::where('user_id', $user->id)->where('work_sum', '>', 0)->where('salary_id', 0)->sum('work_sum');
            $box_sum = DB::table('boxes')->where('boxes.user_id', $user->id)->where('boxes.salary_id', 0)->leftJoin('applications', 'boxes.application_id', '=', 'applications.id')->where('applications.status_id', 4)->sum('boxes.rate');
            $accrued = $box_sum + $work_sum;
            if ($accrued > 0) {
                $salary_id = $this->salary_insert($user->id, $accrued, 0, 0, 'Выработка', $accounting->id, 0);
                $work_salary = Operation::where('user_id', $user->id)->where('work_sum', '>', 0)->where('salary_id', 0)->update(['salary_id' => $salary_id]);
                $box_salary = DB::table('boxes')->where('boxes.user_id', $user->id)->where('boxes.salary_id', 0)->leftJoin('applications', 'boxes.application_id', '=', 'applications.id')->where('applications.status_id', 4)
                    ->update(['boxes.salary_id' => $salary_id]);
            }
            $application = Application::where('salary_id', 0)->where('status_id', 4)->where('update_user_id', $user->id)->get();
            $subappTotalSum = 0;
            foreach ($application as $app) {
                $subappTotal = Subapplication::where('application_id', $app->id)->select(DB::raw('service_total * rate as total'))->get();
                foreach ($subappTotal as $s) {
                    $subappTotalSum = $subappTotalSum + $s->total;
                }
            }
            if ($subappTotalSum > 0) {
                $accruedManager = ($subappTotalSum * 30) / 100;
                $salary_id_manager = $this->salary_insert($user->id, $accruedManager, 0, 0, 'Бонус менеджера ' . $subappTotalSum . ' * 30%', $accounting->id, 0);
                $applicationUp =  Application::where('salary_id', 0)->where('status_id', 4)->where('update_user_id', $user->id)->update(['salary_id' => $salary_id_manager]);
            }
            $transport = Transport::where('user_id', $user->id,)->where('salary_id', 0)->sum('accrued');
            if ($transport > 0) {
                $transport_salary_id = $this->salary_insert($user->id, $transport, 0, 0, 'Транспортные услуги', $accounting->id, 0);
                $transportUp = Transport::where('user_id', $user->id,)->where('salary_id', 0)->update(['salary_id' => $transport_salary_id]);
            }
            $merchand = Merchandise::where('user_id', $user->id,)->where('salary_id', 0)->sum('accrued');
            if ($merchand > 0) {
                $merchand_salary_id = $this->salary_insert($user->id, $merchand, 0, 0, 'Бонус с продаж', $accounting->id, 0);
                $merchandUp = Merchandise::where('user_id', $user->id,)->where('salary_id', 0)->update(['salary_id' => $merchand_salary_id]);
            }
        }

        return response()->json([
            'status' => true,
            'message' =>  'Просчет завершен',
        ], 200);
    }

    public function personal_list()
    {
        $data = DB::table('salaries')->leftJoin('users', 'salaries.personal_id', '=', 'users.id')->groupBy('salaries.personal_id', 'users.name')->selectRaw('salaries.personal_id as id, users.name as name, sum(salaries.accrued) as accrued,sum(salaries.held) as held, sum(salaries.paid) as paid')->get();
        return $data;
    }
    public function personal_list_id(Request $request)
    {
        if ($request->personal_id) {
            $personal = User::where('id', $request->personal_id)->first();
        }
        if ($request->email) {
            $personal = User::where('email', $request->email)->first();
        }
        $data['personal'] = $personal->name . ' - ' . $personal->email;
        $data['rows'] = DB::table('salaries')->leftJoin('users', 'salaries.user_id', '=', 'users.id')->where('salaries.personal_id', $personal->id)->select('salaries.id as id', 'salaries.created_at as created_at', 'salaries.accrued as accrued', 'salaries.held as held', 'salaries.paid as paid', 'salaries.balance as balance', 'salaries.description as description', 'users.name as user')->orderBy('id', 'DESC')->get();
        return $data;
    }

    public function servise_agr()
    {
        $data = Service::whereIn('category_id', [1, 2, 6])->select('id as value', 'name as label', 'price as price')->get();
        return $data;
    }
    public function new_agr(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $agr = Dogovor::where('counterparty_id', $request->counterparty_id)->where('service_id', $request->service_id)->first();
        if ($agr != null) {
            $agr->price = $request->price;
            $agr->user_id = $user->id;
            $agr->save();
        } else {
            $new = Dogovor::create([
                'counterparty_id' => $request->counterparty_id,
                'service_id' => $request->service_id,
                'price' => $request->price,
                'user_id' => $user->id,
            ]);
        }
        return $this->agr_row($request->counterparty_id);
    }
    public function agr_row($counterparty_id)
    {
        $data = DB::table('dogovors')->where('dogovors.counterparty_id', $counterparty_id)->leftJoin('users', 'dogovors.user_id', '=', 'users.id')->leftJoin('services', 'dogovors.service_id', '=', 'services.id')
            ->select('dogovors.id as id', 'services.name as service', 'dogovors.updated_at as updated_at', 'dogovors.price as price', 'users.name as user')->get();
        return $data;
    }
    public function agr_delete(Request $request)
    { $user = User::where('email', $request->email)->first();
        if($user->level>3){
             $agr = Dogovor::where('id', $request->id)->delete();
        }
        return $this->agr_row($request->counterparty_id);
    }
    public function agr_row_request(Request $request)
    {
        return $this->agr_row($request->counterparty_id);
    }
    public function service_price(Request $request)
    {
        $service_list = Service::where('category_id','<>',4)->get();
        $data=[];
        foreach($service_list as $service){
            $price = $this->price($service->id,$request->organization_id);
            $val= [
                'value'=>$service->id,
                'label'=>$service->name,
                'rate'=>$service->rate,
                'price'=>$price,
            ];
            array_push($data,$val);
        }
        return $data;
    }
    public function servise_actual_price()
    {
        $data = DB::table('categories')->whereIn('categories.id', [1, 2, 6])->leftJoin('services','categories.id','=','services.category_id')->select('services.id as id', 'categories.name as category','services.name as service','services.price as price')
        ->orderBy('categories.name')->get();
        return $data;
    }
}
