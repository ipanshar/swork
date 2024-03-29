<?php

use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CabinetController;
use App\Http\Controllers\Api\LogisticController;
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
Route::post('/cabinet/mywork',[CabinetController::class,'myWork'])->middleware('auth:sanctum');
Route::post('/cabinet/my_salary_data',[CabinetController::class,'my_salary_data'])->middleware('auth:sanctum');

Route::post('/admin/users',[AdminController::class,'users'])->middleware('auth:sanctum');
Route::post('/admin/uplevel',[AdminController::class,'uplevel'])->middleware('auth:sanctum');
Route::post('/admin/createservice',[AdminController::class,'createService'])->middleware('auth:sanctum');
Route::post('/admin/services',[AdminController::class,'services'])->middleware('auth:sanctum');
Route::post('/admin/categorylist',[AdminController::class,'CategoryList'])->middleware('auth:sanctum');
Route::post('/admin/valuta',[AdminController::class,'valuta'])->middleware('auth:sanctum');
Route::post('/admin/updateservice',[AdminController::class,'updateService'])->middleware('auth:sanctum');
Route::post('/admin/cash_add',[AdminController::class,'Cash_add'])->middleware('auth:sanctum');
Route::post('/admin/cash_up',[AdminController::class,'Cash_up'])->middleware('auth:sanctum');
Route::post('/admin/cash',[AdminController::class,'Cash_up'])->middleware('auth:sanctum');

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
Route::post('/managment/service_price',[ManegmantController::class,'service_price'])->middleware('auth:sanctum');
Route::post('/managment/service_list',[ManegmantController::class,'service_list'])->middleware('auth:sanctum');
Route::post('/managment/create_application',[ManegmantController::class,'create_application'])->middleware('auth:sanctum');
Route::post('/managment/aplications',[ManegmantController::class,'aplications'])->middleware('auth:sanctum');
Route::post('/managment/update_app_status',[ManegmantController::class,'update_app_status'])->middleware('auth:sanctum');
Route::post('/managment/app_sub',[ManegmantController::class,'appSub'])->middleware('auth:sanctum');
Route::post('/managment/update_application',[ManegmantController::class,'update_application'])->middleware('auth:sanctum');
Route::post('/managment/exelrazbivka',[ManegmantController::class,'exelRazbivka'])->middleware('auth:sanctum');
Route::post('/managment/merchandis_create',[ManegmantController::class,'merchandis_create'])->middleware('auth:sanctum');
Route::post('/managment/merchandis_row',[ManegmantController::class,'merchandis_row'])->middleware('auth:sanctum');
Route::post('/managment/smena',[ManegmantController::class,'smena'])->middleware('auth:sanctum');
Route::post('/managment/smena_insert',[ManegmantController::class,'smena_insert'])->middleware('auth:sanctum');
Route::post('/managment/smena_top',[ManegmantController::class,'smena_top'])->middleware('auth:sanctum');
Route::post('/managment/update_organization',[ManegmantController::class,'update_organization'])->middleware('auth:sanctum');
Route::post('/managment/operations_application',[ManegmantController::class,'operations_application'])->middleware('auth:sanctum');
Route::post('/managment/update_operation_num',[ManegmantController::class,'update_operation_num'])->middleware('auth:sanctum');
Route::post('/managment/print_shk_box',[ManegmantController::class,'printShkBox'])->middleware('auth:sanctum');
Route::get('/managment/debetcredit',[ManegmantController::class,'DebetCredit']);


Route::post('/task/createarticle',[TaskController::class,'createArticle'])->middleware('auth:sanctum');
Route::post('/task/updateArticle',[TaskController::class,'updateArticle'])->middleware('auth:sanctum');
Route::post('/task/articles',[TaskController::class,'articles'])->middleware('auth:sanctum');
Route::post('/task/orglist',[TaskController::class,'orgList'])->middleware('auth:sanctum');
Route::get('/task/orglistget',[TaskController::class,'orgList']);
Route::post('/task/article',[TaskController::class,'article'])->middleware('auth:sanctum');
Route::post('/task/delete_article',[TaskController::class,'delete_article'])->middleware('auth:sanctum');
Route::post('/task/sublist',[TaskController::class,'subList'])->middleware('auth:sanctum');
Route::post('/task/create_box',[TaskController::class,'create_box'])->middleware('auth:sanctum');
Route::post('/task/app_articles',[TaskController::class,'app_articles'])->middleware('auth:sanctum');
Route::post('/task/create_option',[TaskController::class,'create_option'])->middleware('auth:sanctum');
Route::post('/task/delete_option',[TaskController::class,'delete_option'])->middleware('auth:sanctum');
Route::post('/task/operations_box',[TaskController::class,'operations_box'])->middleware('auth:sanctum');
Route::post('/task/detal_box',[TaskController::class,'detal_box'])->middleware('auth:sanctum');
Route::post('/task/delete_box',[TaskController::class,'delete_box'])->middleware('auth:sanctum');
Route::post('/task/wb_import_articles',[TaskController::class,'wb_import_articles'])->middleware('auth:sanctum');
Route::post('/task/subjects_country',[TaskController::class,'subjects_country'])->middleware('auth:sanctum');
Route::post('/task/articles_all__request',[TaskController::class,'articles_all__request'])->middleware('auth:sanctum');




Route::post('/logistic/boxes_status',[LogisticController::class,'boxesStatus'])->middleware('auth:sanctum');
Route::post('/logistic/up_box_status',[LogisticController::class,'upBoxStatus'])->middleware('auth:sanctum');
Route::post('/logistic/up_box_cell',[LogisticController::class,'upBoxCell'])->middleware('auth:sanctum');
Route::post('/logistic/razbivka_boxes',[LogisticController::class,'razbivkaBoxes'])->middleware('auth:sanctum');
Route::post('/logistic/service_transport',[LogisticController::class,'service_transport'])->middleware('auth:sanctum');
Route::post('/logistic/service_transport_row',[LogisticController::class,'service_transport_row'])->middleware('auth:sanctum');
Route::post('/logistic/labelbaggage',[LogisticController::class,'labelBaggage'])->middleware('auth:sanctum');
Route::post('/logistic/tiebaggage',[LogisticController::class,'TieBaggage'])->middleware('auth:sanctum');
Route::post('/logistic/allbaggage',[LogisticController::class,'AllBaggage'])->middleware('auth:sanctum');
Route::post('/logistic/upallbaggage',[LogisticController::class,'UpAllBaggage'])->middleware('auth:sanctum');
Route::post('/logistic/deletebaggage',[LogisticController::class,'DeleteBaggage'])->middleware('auth:sanctum');



Route::post('/accounting/add_entries',[AccountingController::class,'add_entries'])->middleware('auth:sanctum');
Route::post('/accounting/entries_list',[AccountingController::class,'entries_list'])->middleware('auth:sanctum');
Route::post('/accounting/entries_update',[AccountingController::class,'entries_update'])->middleware('auth:sanctum');
Route::post('/accounting/entries_delete',[AccountingController::class,'entries_delete'])->middleware('auth:sanctum');
Route::post('/accounting/app_list_end',[AccountingController::class,'app_list_end'])->middleware('auth:sanctum');
Route::post('/accounting/app_reports',[AccountingController::class,'app_reports'])->middleware('auth:sanctum');
Route::post('/accounting/app_status_end',[AccountingController::class,'app_status_end'])->middleware('auth:sanctum');
Route::post('/accounting/counter_enties',[AccountingController::class,'counter_enties'])->middleware('auth:sanctum');
Route::post('/accounting/create_bill',[AccountingController::class,'create_bill'])->middleware('auth:sanctum');
Route::post('/accounting/invoice_view',[AccountingController::class,'invoice_view'])->middleware('auth:sanctum');
Route::post('/accounting/check_view',[AccountingController::class,'check_view'])->middleware('auth:sanctum');
Route::post('/accounting/coun_journal',[AccountingController::class,'coun_journal'])->middleware('auth:sanctum');
Route::post('/accounting/delete_invoice',[AccountingController::class,'delete_invoice'])->middleware('auth:sanctum');
Route::post('/accounting/counterparties',[AccountingController::class,'counterparties'])->middleware('auth:sanctum');
Route::post('/accounting/items',[AccountingController::class,'items'])->middleware('auth:sanctum');
Route::post('/accounting/add_item',[AccountingController::class,'add_item'])->middleware('auth:sanctum');
Route::post('/accounting/group_items',[AccountingController::class,'group_items'])->middleware('auth:sanctum');
Route::post('/accounting/accept_payment',[AccountingController::class,'accept_payment'])->middleware('auth:sanctum');
Route::post('/accounting/personal',[AccountingController::class,'personal'])->middleware('auth:sanctum');
Route::post('/accounting/pay_expense',[AccountingController::class,'pay_expense'])->middleware('auth:sanctum');
Route::post('/accounting/locale_transfer',[AccountingController::class,'locale_transfer'])->middleware('auth:sanctum');
Route::post('/accounting/add_salary',[AccountingController::class,'add_salary'])->middleware('auth:sanctum');
Route::post('/accounting/cashbox_top',[AccountingController::class,'cashbox_top'])->middleware('auth:sanctum');
Route::post('/accounting/salary_top',[AccountingController::class,'salary_top'])->middleware('auth:sanctum');
Route::post('/accounting/salary_calculation',[AccountingController::class,'salary_calculation'])->middleware('auth:sanctum');
Route::post('/accounting/personal_list',[AccountingController::class,'personal_list'])->middleware('auth:sanctum');
Route::post('/accounting/personal_list_id',[AccountingController::class,'personal_list_id'])->middleware('auth:sanctum');
Route::post('/accounting/servise_agr',[AccountingController::class,'servise_agr'])->middleware('auth:sanctum');
Route::post('/accounting/new_agr',[AccountingController::class,'new_agr'])->middleware('auth:sanctum');
Route::post('/accounting/agr_row_request',[AccountingController::class,'agr_row_request'])->middleware('auth:sanctum');
Route::post('/accounting/agr_delete',[AccountingController::class,'agr_delete'])->middleware('auth:sanctum');
Route::post('/accounting/service_price',[AccountingController::class,'service_price'])->middleware('auth:sanctum');
Route::post('/accounting/servise_actual_price',[AccountingController::class,'servise_actual_price']);
Route::post('/accounting/cash_select',[AccountingController::class,'Cash_select']);
Route::post('/accounting/totall_report',[AccountingController::class,'totall_report'])->middleware('auth:sanctum');
Route::post('/accounting/passivies',[AccountingController::class,'passivies'])->middleware('auth:sanctum');
Route::post('/accounting/passive_add',[AccountingController::class,'passive_add'])->middleware('auth:sanctum');
Route::post('/accounting/passive_delete',[AccountingController::class,'passive_delete'])->middleware('auth:sanctum');
Route::post('/accounting/credit_invoice',[AccountingController::class,'credit_invoice'])->middleware('auth:sanctum');


