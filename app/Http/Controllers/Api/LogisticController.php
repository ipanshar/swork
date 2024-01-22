<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Baggage;
use App\Models\Box;
use App\Models\Dogovor;
use App\Models\Entries;
use App\Models\Organization;
use App\Models\Service;
use App\Models\Status;
use App\Models\Transport;
use App\Models\User;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogisticController extends Controller
{

    public function boxesStatus(Request $request)
    {
        $data = [];
        $status = Status::whereIn('id', [1, 7])->select('name')->get();
        $box = DB::table('boxes')->where('boxes.organization_id', '=', $request->id)->leftJoin('statuses', 'boxes.status_id', '=', 'statuses.id')->select('boxes.id as id', 'boxes.name as box', 'boxes.application_id as application_id', 'statuses.name as status', 'boxes.cell as cell')->get();
        $data['statuses'] = $status;
        $data['boxes'] = $box;
        return $data;
    }
    public function upBoxStatus(Request $request)
    {


        $status = Status::where('name', $request->status)->first();
        $box = Box::whereIn('id', $request->id)->update(['status_id' => $status->id]);
        $boxes = DB::table('boxes')->where('boxes.organization_id', '=', $request->organization_id)->leftJoin('statuses', 'boxes.status_id', '=', 'statuses.id')->select('boxes.id as id', 'boxes.name as box', 'statuses.name as status', 'boxes.cell as cell')->get();
        return $boxes;
    }
    public function upBoxCell(Request $request)
    {

        $box = Box::whereIn('id', $request->id)->update(['cell' => $request->cell]);
        $boxes = DB::table('boxes')->where('boxes.organization_id', '=', $request->organization_id)->leftJoin('statuses', 'boxes.status_id', '=', 'statuses.id')->select('boxes.id as id', 'boxes.name as box', 'statuses.name as status', 'boxes.cell as cell')->get();
        return $boxes;
    }


    public function razbivkaBoxes(Request $request)
    {

        $data = DB::table('boxes')->Join('operations', 'boxes.id', '=', 'operations.box_id')->leftJoin('articles', 'operations.article_id', '=', 'articles.id')->leftJoin('statuses', 'boxes.status_id', '=', 'statuses.id')->where('boxes.organization_id', $request->id)
            ->groupBy('application_id', 'box', 'code', 'article', 'size', 'status')->selectRaw('max(operations.id) as id,boxes.application_id as application_id, boxes.name as box, articles.code as code, articles.name as article, articles.size as size, sum(operations.num) as num, statuses.name as status')->get();
        return $data;
    }
    public function service_transport(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $createTr = Transport::create([
            'organization_id' => $request->organization_id,
            'service_id' => $request->service_id,
            'service_count' => $request->service_count,
            'accrued' => $request->accrued,
            'user_id' => $user->id,
        ]);
        $entries = $this->transport_entries();
        return $this->service_transport_row();
    }
    public function service_transport_row()
    {
        $transports = DB::table('transports')->leftJoin('users', 'transports.user_id', '=', 'users.id')->leftJoin('organizations', 'transports.organization_id', '=', 'organizations.id')->leftJoin('services', 'transports.service_id', '=', 'services.id')
            ->take(100)->select(
                'transports.created_at as created_at',
                'transports.id as id',
                'organizations.name as organization',
                'services.name as service',
                'transports.service_count as service_count',
                'transports.accrued as accrued',
                'transports.salary_id as salary_id',
                'users.name as user'
            )->orderBy('transports.id', 'DESC')->get();
        return $transports;
    }

    public function transport_entries()
    {
        $transport = Transport::where('status_id', 1)->get();
        foreach ($transport as $tr) {
            $service_id = 0;
            if ($tr->service_id == 10) {
                $service_id = 19;
            }
            if ($tr->service_id == 12) {
                $service_id = 20;
            }
            if ($tr->service_id == 13) {
                $service_id = 21;
            }
            if ($tr->service_id == 15) {
                $service_id = 18;
            }
            if ($tr->service_id == 16) {
                $service_id = 17;
            }
            if ($tr->service_id == 14) {
                if ($tr->service_count > 3) {
                    $service_id = 5;
                } else {
                    $service_id = 4;
                }
            }
            if ($service_id > 0) {
                $price = $this->price($service_id, $tr->organization_id);
                $this->insert_entries($tr->organization_id, $service_id, $price, $tr->service_count, $price * $tr->service_count, $tr->user_id);
                $up = Transport::where('id', $tr->id)->update(['status_id' => 2]);
            }
        }
    }

    public function insert_entries($organization_id, $service_id, $service_price, $service_count, $total_sum, $user_id)
    {
        $entries = Entries::create([
            'organization_id' => $organization_id,
            'service_id' => $service_id,
            'service_price' => $service_price,
            'service_count' => $service_count,
            'total_sum' => $total_sum,
            'public_date' => date('Y-m-d'),
            'user_id' => $user_id,
        ]);
    }
    public function price($service_id, $organization_id)
    {
        $counter = Organization::where('id', $organization_id)->first();
        $dogovor = Dogovor::where('counterparty_id', $counter->counterparty_id)->where('service_id', $service_id)->first();
        if ($dogovor != null) {
            return $dogovor->price;
        } else {
            $price = Service::where('id', $service_id)->first();
            return $price->price;
        }
    }
    function labelBaggageNew()
    {
        $baggage = Baggage::where('organization_id',null)->select('id as value','name as label')->get();
        return $baggage;
    }
    public function labelBaggage(Request $request)
    {

        if ($request->quantity > 0) {
            $user = User::where('email', $request->email)->first();
            for ($i = 1; $i <= $request->quantity; $i++) {
                $baggage = Baggage::create([
                    'name' => '',
                    'user_id' => $user->id
                ]);
                $baggage->name = 'Bag_' . (10000 + $baggage->id);
                $baggage->save();
            }
            return $this->labelBaggageNew();
        }else{
            return $this->labelBaggageNew();
        }
    }
    public function TieBaggage(Request $request){
        $user = User::where('email', $request->email)->first();
        $baggage = Baggage::wherein('id',$request->bag_id)->update([
            'organization_id'=>$request->organization_id,
            'cell'=>$request->cell,
            'description'=>$request->description,
            'user_id'=>$user->id
        ]);
    }
    public function AllBaggage(){
        $baggage = DB::table('baggage')->where('baggage.organization_id','>',0)->leftJoin('organizations','baggage.organization_id','=','organizations.id')
        ->select('baggage.id as id','baggage.name as name','organizations.name as organization', 'baggage.cell as cell', 'baggage.description as description')->get();
        return $baggage;
    }
    public function UpAllBaggage(Request $request){
        $user = User::where('email', $request->email)->first();
        $baggage = Baggage::wherein('id',$request->bag_id)->update([
            'cell'=>$request->cell,
            'description'=>$request->description,
            'user_id'=>$user->id
        ]);
    }
    public function DeleteBaggage(Request $request){
        $baggage = Baggage::wherein('id',$request->bag_id)->delete();
    }
}
