<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Article;
use App\Models\Box;
use App\Models\Operation;
use App\Models\Service;
use App\Models\Subapplication;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class TaskController extends Controller
{
    public function createArticle(Request $request)
    {

        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 1) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateArticle = Validator::make(
                $request->all(),
                [
                    'code' => 'nullable|string',
                    'name' => 'required|max:150',
                    'size' => 'nullable|string',
                    'subject_id' => 'required|numeric',
                    'organization_id' => 'required|numeric',
                ]
            );
            if ($valdateArticle->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ошибка! данные не соответствуют регламенту',
                    'errors' => $valdateArticle->errors()
                ], 401);
            }
            $article = Article::create([
                'code' => $request->code,
                'name' => $request->name,
                'size' => $request->size,
                'subject_id' => $request->subject_id,
                'organization_id' => $request->organization_id,
                'user_id' => $user->id,
            ]);
            return response()->json([
                'status' => true,
                'message' =>  'Артикул добавлен'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function updateArticle(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 1) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateArticle = Validator::make(
                $request->all(),
                [
                    'id' => 'required|numeric',
                    'subject_id' => 'required|numeric',
                    'code' => 'nullable|string',
                    'name' => 'max:150',
                    'size' => 'nullable|string',
                ]
            );
            if ($valdateArticle->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ошибка! данные не соответствуют регламенту',
                    'errors' => $valdateArticle->errors()
                ], 401);
            }
            $article = Article::where('id', $request->id)->first();
            $article->code = $request->code;
            $article->name = $request->name;
            $article->size = $request->size;
            $article->subject_id = $request->subject_id;
            $article->user_id = $user->id;
            $article->save();
            return response()->json([
                'status' => true,
                'message' =>  'Артикул изменен'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function articles(Request $request)
    {
        $article = DB::table('articles')->leftJoin('subjects', 'articles.subject_id', '=', 'subjects.id')->where('articles.organization_id', $request->orgID)->select('articles.id as id', 'articles.name as name', 'articles.code as code', 'articles.size as size', 'subjects.name as subject', 'articles.subject_id as subject_id')->paginate(30);
        return $article;
    }
    public function article(Request $request)
    {
        $article = Article::where('id', $request->id)->select('id', 'subject_id', 'code', 'name', 'size')->get();
        return $article;
    }


    public function orgList()
    {
        $orglist = DB::table('organizations')->leftJoin('counterparties', 'organizations.counterparty_id', '=', 'counterparties.id')
            ->select('organizations.id as value', DB::raw('CONCAT(organizations.name," - (", counterparties.name,")")as label'))->get();
        return $orglist;
    }
    public function subList()
    {
        $subList = Subject::select('id as value', 'name as label')->get();
        return $subList;
    }

    public function create_box(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 1) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateBox = Validator::make(
                $request->all(),
                [
                    'application_id' => 'required|numeric',
                ]
            );
            if ($valdateBox->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ошибка! данные не соответствуют регламенту',
                    'errors' => $valdateBox->errors()
                ], 401);
            }
            $rate = Service::where('id', 8)->first();
            $app = Application::where('id',$request->application_id)->first();
            $box = Box::create([
                'application_id' => $request->application_id,
                'rate' => $rate->rate,
                'user_id' => $user->id,
                'organization_id'=>$app->organization_id,
            ]);
            $box->name = 'BOX_' . (10000 + $box->id);
            $box->save();
            return response()->json([
                'status' => true,
                'box' =>  $box->name,
                'box_id' =>  $box->id
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function app_articles(Request $request)
    {
        // $articles = DB::table('applications')->where('applications.id', '=', $request->id)
        //     ->leftJoin('articles', 'applications.organization_id', '=', 'articles.organization_id', 'applications.subject_id', '=', 'articles.subject_id')
        //     ->select('articles.id as value', DB::raw('concat( articles.name,"-", COALESCE(articles.size,""),"- ", COALESCE(articles.code,"") )as label'))->get();
            $applications= Application::where('id',$request->id)->first();
           $articles= Article::where('organization_id',$applications->organization_id)->where('subject_id',$applications->subject_id)
           ->select('id as value',DB::raw('CONCAT(name, CASE WHEN size <> "" AND code <> "" THEN  CONCAT("(",size,") - ",code) WHEN size <> "" THEN  CONCAT("(",size,")") WHEN code <> "" THEN CONCAT(" - ",code) ELSE "" END) as label'))->get();
        return $articles;
    }
    ///////////////////////////////////////////////////////////////////
    public function create_option(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user == null or $user->level < 1) {
            return response()->json([
                'status' => false,
                'message' => 'На совершение операции, у вас не достаточно полномочий'
            ], 401);
        }
        try {
            $valdateBox = Validator::make(
                $request->all(),
                [
                    'box_id' => 'required|numeric',
                    'application_id' => 'required|numeric',
                    'article_id' => 'required|numeric',
                    'num' => 'required|numeric',
                ]
            );
            if ($valdateBox->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ошибка! данные не соответствуют регламенту',
                    'errors' => $valdateBox->errors()
                ], 401);
            }

            $operation = Operation::create([
                'box_id' => $request->box_id,
                'application_id' => $request->application_id,
                'article_id' => $request->article_id,
                'num' => $request->num,
                'user_id' => $user->id,
            ]);
            return response()->json([
                'status' => true,
                'message' =>  'Запись добавлена'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    ///////////////////////////////
    public function delete_option(Request $request)
    {   $oper=Operation::where('id', "=", $request->id)->first();
        $appl=Application::where('id',$oper->application_id)->first();
    if($appl->status_id==1){
        $delete = Operation::where('id', "=", $request->id)->delete();
        return  response()->json([
            'status' => true,
            'message' =>  'Запись удалена'
        ], 200);
    } 
    return  response()->json([
        'status' => false,
        'message' =>  'Не удалось удалить'
    ], 401);
    }
    public function operations_box(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $operations_box = DB::table('operations')->leftJoin('articles', 'operations.article_id', '=', 'articles.id')->where('operations.user_id', "=", $user->id)->where('operations.box_id', "=", $request->box_id)->where('operations.application_id', $request->id)
            ->select('operations.id as operation_id', DB::raw('concat( articles.name,"-", COALESCE(articles.size,""),"- ", COALESCE(articles.code,"") )as article_name'), 'operations.num')->get();
        return $operations_box;
    }

    public function detal_box(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $box = Db::table('boxes')->where('boxes.application_id','=', $request->application_id)->leftJoin('users','boxes.user_id','=','users.id')->select('boxes.id as id','boxes.name as name','users.name as user')->get();
        $operations_box = DB::table('operations')->leftJoin('articles', 'operations.article_id', '=', 'articles.id')->where('operations.user_id', "=", $user->id)->where('operations.application_id', $request->application_id)
            ->select('operations.box_id as box_id', 'operations.id as operation_id', DB::raw('concat( articles.name," sz:", COALESCE(articles.size,"")," шк:", COALESCE(articles.code,"") )as article_name'), 'operations.num')->get();
        $num_all = Operation::where('application_id', $request->application_id)->select(DB::raw('sum(num) as num_all'))->first();
        $num_me = Operation::where('application_id', $request->application_id)->where('user_id', $user->id)->select(DB::raw('sum(num) as num_me'))->first();
        $box_all = Box::where('application_id', $request->application_id)->select(DB::raw('count(id) as box_all, MAX(rate) as rate'))->first();
        $box_me = Box::where('application_id', $request->application_id)->where('user_id', $user->id)->select(DB::raw('count(id) as box_me'))->first();
        $sub = Subapplication::where('application_id', $request->application_id)->select(DB::raw('SUM(service_num * rate) as rate'))->first();
        $info['num_all'] = $num_all->num_all;
        $info['num_me'] = $num_me->num_me;
        $info['box_all'] = $box_all->box_all;
        $info['box_me'] = $box_me->box_me;
        $info['box_sum_rate'] = ($box_all->rate * $box_me->box_me);
        $info['sub_sum_rate'] = ($sub->rate * $num_me->num_me);
        $info['sum_rate_all'] = ($sub->rate * $num_me->num_me) + ($box_all->rate * $box_me->box_me);
        $data['info'] =  $info;
        if (count($box) > 0) {
            $data['box_data'] = $box;
        } else {
            $data['box_data'] = [json_decode('{"id":"0","name":"Без короба"}', true)];
        }
        $data['operations_box'] = $operations_box;
        return $data;
    }
    public function delete_box(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            $box = Box::where('id', $request->box_id)->first();
            $appl=Application::where('id',$box->application_id)->first();
            if($appl->status_id==1){
            if ($box->user_id == $user->id) {
                Box::where('id', $request->box_id)->delete();
                Operation::where('box_id', $request->box_id)->delete();
                return  response()->json([
                    'status' => true,
                    'message' =>  'Запись удалена'
                ], 200);
            }
        }
            return  response()->json([
                'status' => false,
                'message' =>  'Не удалось удалить, у вас отстутвуют права'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

}
