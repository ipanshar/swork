<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Box;
use App\Models\Operation;
use App\Models\Salary;
use App\Models\Subapplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CabinetController extends Controller
{
   public function mySalary()
   {
      return [1, 2, 3];
   }

   public function myProfile(Request $request)
   {
      $user = User::where('email', $request->email)->first();
      return response()->json([
         'name' => $user->name,
         'email' => $user->email,
         'level' => $user->level . ' - й уровень.',
         'created_at' => $user->created_at,
      ]);
   }
   public function myWork(Request $request)
   {
      $user = User::where('email', $request->email)->first();

      $operations = Operation::orderByDesc('update_at')->where('user_id', $user->id)->where('created_at', '>', $request->GetCreated)->where('created_at', '<', $request->EndCreated . ' 23:59:59')->groupBy('application_id')->select('application_id', DB::raw('sum(num) as num_me,date(max(updated_at)) update_at, max(salary_id) as salary_id'))->get();

      // leftJoin('subapplications','operations.application_id','=','subapplications.application_id')
      // ->leftJoin('services','subapplications.service_id')->seletRow()->get();
      $data = [];
      foreach ($operations as $oper) {
         $app = DB::table('applications')->leftJoin('organizations', 'applications.organization_id', '=', 'organizations.id')->where('applications.id', '=', $oper->application_id)->select('organizations.name  as name')->first();

         $subapplications = DB::table('subapplications')->where('subapplications.application_id', '=', $oper->application_id)->leftJoin('services', 'subapplications.service_id', '=', 'services.id')->where('services.category_id', '=', 2)
            ->select('services.name as service', 'subapplications.rate as rate', 'subapplications.service_num as service_num')->get();
         foreach ($subapplications as $sub) {
            $d = array('organization' => $app->name, 'application_id' => $oper->application_id, 'service' => $sub->service, 'rate' => $sub->rate, 'num_me' => $oper->num_me * $sub->service_num, 'sum' => ($oper->num_me * $sub->service_num) * $sub->rate, 'update_at' => $oper->update_at, 'salary_id' => $oper->salary_id);
            array_push($data, $d);
         }
         $box = Box::where('application_id', $oper->application_id)->where('user_id', $user->id)->select(DB::raw('count(id) as num_me, avg(rate) as rate'))->first();
         if ($box->num_me > 0) {
            $d = array('organization' => $app->name, 'application_id' => $oper->application_id, 'service' => 'Формирование короба', 'rate' => $box->rate, 'num_me' => $box->num_me, 'sum' => ($box->num_me * 1) * $box->rate, 'update_at' => $oper->update_at, 'salary_id' => $oper->salary_id);
            array_push($data, $d);
         }
      }
      return $data;
   }

   public function my_salary_data(Request $request){
      $user = User::where('email', $request->email)->first();
      $work_sum = 0;
      $operations = DB::table('operations')->where('operations.user_id', $user->id)->leftJoin('applications', 'operations.application_id', '=', 'applications.id')->where('applications.status_id', 1)->select('operations.application_id as application_id', 'operations.num as num')->get();
      foreach ($operations as $operation) {
         $subapplications = Subapplication::where('application_id', $operation->application_id)->where('service_id', '<>', 8)->select(DB::raw('sum(service_num * rate) as rate_sum'))->first();
         $work_sum = $work_sum + ($operation->num * $subapplications->rate_sum);
      }
      $box_sum = DB::table('boxes')->where('boxes.user_id', $user->id)->where('boxes.salary_id', 0)->leftJoin('applications', 'boxes.application_id', '=', 'applications.id')->where('applications.status_id', 1)->sum('boxes.rate');
      $balance = Salary::where('personal_id', $user->id)->orderBy('id', 'DESC')->first();
      $data['accrued'] = $work_sum + $box_sum;
      $data['balance'] = $balance->balance;
      $salaries = DB::table('salaries')->leftJoin('users', 'salaries.user_id', '=', 'users.id')->where('salaries.personal_id', $user->id)
         ->select('salaries.id as id', 'salaries.created_at as created_at', 'salaries.accrued as accrued', 'salaries.held as held', 'salaries.paid as paid', 'salaries.balance as balance', 'salaries.description as description', 'users.name as user')
         ->orderBy('id', 'DESC')->take(100)->get();
      $history = [];
      foreach ($salaries as $sl) {
         $oper = '';
         $nn = 0;
         $ss = 0;
         if ($sl->accrued > 0) {
            $nn = 1;
            $oper = 'Начислено';
            $ss = $sl->accrued;
         }
         if ($sl->held > 0) {
            $nn = 2;
            $oper = 'Удержано';
            $ss = $sl->held;
         }
         if ($sl->paid > 0) {
            $nn = 3;
            $oper = 'Оплата';
            $ss = $sl->paid;
         }
         $dd = array('id' => $sl->id, 'nn' => $nn, 'name' => $oper, 'sum' => $ss, 'balance' => $sl->balance, 'description' => $sl->description, 'date' => $sl->created_at, 'user' => $sl->user);
         array_push($history, $dd);
      }
      $data['history'] = $history;
      return $data;
   }
}
