<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CabinetController;
use App\Http\Controllers\Api\ManegmantController;
use App\Http\Controllers\Api\TaskController;
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
Route::post('/admin/createservice',[AdminController::class,'createService'])->middleware('auth:sanctum');
Route::post('/admin/services',[AdminController::class,'services'])->middleware('auth:sanctum');
Route::post('/admin/categorylist',[AdminController::class,'CategoryList'])->middleware('auth:sanctum');
Route::post('/admin/valuta',[AdminController::class,'valuta'])->middleware('auth:sanctum');
Route::post('/admin/updateservice',[AdminController::class,'updateService'])->middleware('auth:sanctum');

Route::post('/managment/create-counterparty',[ManegmantController::class,'createCounterparty'])->middleware('auth:sanctum');
Route::post('/managment/update-counterparty',[ManegmantController::class,'updateCounterparty'])->middleware('auth:sanctum');
Route::post('/managment/update-counterparty-phone',[ManegmantController::class,'updateCounterpartyPhone'])->middleware('auth:sanctum');
Route::post('/managment/counterparties',[ManegmantController::class,'counterparties'])->middleware('auth:sanctum');
Route::post('/managment/counterparty',[ManegmantController::class,'counterparty'])->middleware('auth:sanctum');
Route::post('/managment/createorganization',[ManegmantController::class,'createOrganization'])->middleware('auth:sanctum');
Route::post('/managment/organizationslist',[ManegmantController::class,'OrganizationsList'])->middleware('auth:sanctum');
Route::post('/managment/createsubject',[ManegmantController::class,'createSubject'])->middleware('auth:sanctum');
Route::post('/managment/updatesubject',[ManegmantController::class,'updateSubject'])->middleware('auth:sanctum');
Route::post('/managment/subjects',[ManegmantController::class,'subjects'])->middleware('auth:sanctum');
Route::post('/managment/subjects_org',[ManegmantController::class,'subjects_org'])->middleware('auth:sanctum');
Route::post('/managment/service_app',[ManegmantController::class,'service_app'])->middleware('auth:sanctum');
Route::post('/managment/create_application',[ManegmantController::class,'create_application'])->middleware('auth:sanctum');
Route::post('/managment/aplications',[ManegmantController::class,'aplications'])->middleware('auth:sanctum');
Route::post('/managment/update_app_status',[ManegmantController::class,'update_app_status'])->middleware('auth:sanctum');
Route::post('/managment/app_sub',[ManegmantController::class,'appSub'])->middleware('auth:sanctum');
Route::post('/managment/update_application',[ManegmantController::class,'update_application'])->middleware('auth:sanctum');
Route::post('/managment/exelrazbivka',[ManegmantController::class,'exelRazbivka'])->middleware('auth:sanctum');


Route::post('/task/createarticle',[TaskController::class,'createArticle'])->middleware('auth:sanctum');
Route::post('/task/updateArticle',[TaskController::class,'updateArticle'])->middleware('auth:sanctum');
Route::post('/task/articles',[TaskController::class,'articles'])->middleware('auth:sanctum');
Route::post('/task/orglist',[TaskController::class,'orgList'])->middleware('auth:sanctum');
Route::post('/task/article',[TaskController::class,'article'])->middleware('auth:sanctum');
Route::post('/task/sublist',[TaskController::class,'subList'])->middleware('auth:sanctum');
Route::post('/task/create_box',[TaskController::class,'create_box'])->middleware('auth:sanctum');
Route::post('/task/app_articles',[TaskController::class,'app_articles'])->middleware('auth:sanctum');
Route::post('/task/create_option',[TaskController::class,'create_option'])->middleware('auth:sanctum');
Route::post('/task/delete_option',[TaskController::class,'delete_option'])->middleware('auth:sanctum');
Route::post('/task/operations_box',[TaskController::class,'operations_box'])->middleware('auth:sanctum');
Route::post('/task/detal_box',[TaskController::class,'detal_box'])->middleware('auth:sanctum');
Route::post('/task/delete_box',[TaskController::class,'delete_box'])->middleware('auth:sanctum');
