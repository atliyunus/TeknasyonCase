<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\MockingIOSController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\MockingGoogleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);
Route::middleware('auth:api')->group( function () {
    Route::post('/device', [DeviceController::class,'insert']);
    Route::post('/purchase', [PurchaseController::class,'insert']);
    Route::post('/check_subscription', [PurchaseController::class,'check_subscription']);
});
Route::post('/GoogleReceipt', [MockingGoogleController::class,'GoogleReceipt']);
Route::post('/IOSReceipt',    [MockingIOSController::class,'IOSReceipt']);

