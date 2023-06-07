<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CabinetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
Route::post('/auth/logout', [AuthController::class,'logout']);
Route::post('/auth/newpassword', [AuthController::class,'newPassword']);
Route::post('/auth/logout-all-device', [AuthController::class,'deleteAllSessions']);
Route::post('/auth/create-recovery', [AuthController::class,'createRecoveryToken']);
Route::post('/auth/recovery-token', [AuthController::class,'recoveryToken']);
Route::post('/auth/level', [AuthController::class,'level'])->middleware('auth:sanctum');
Route::post('/cabinet/mysalary', [CabinetController::class,'mySalary'])->middleware('auth:sanctum');
Route::post('/cabinet/myprofile', [CabinetController::class,'myProfile'])->middleware('auth:sanctum');

Route::post('/admin/users',[AdminController::class,'users'])->middleware('auth:sanctum');
Route::post('/admin/uplevel',[AdminController::class,'uplevel'])->middleware('auth:sanctum');
