<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Counterparty;
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
                    'phone2' => 'max:25',
                    'email' => 'email'
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
                    'phone2' => 'max:25',
                    'email' => 'email'
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

        $counterparties = Counterparty::select('id as value', DB::raw('CONCAT(code,"|", name)as label'))->get();

        return $counterparties;
    }
    //------Данные контрагента
    public function counterparty(Request $request)
    {

        $counterparty = Counterparty::where('id', $request->id)->select('code', 'name', 'phone', 'phone2', 'email', 'created_at')->get();

        return $counterparty;
    }
}
