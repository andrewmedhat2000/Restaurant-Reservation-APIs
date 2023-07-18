<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReservationController;
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



Route::group(['namespace' => 'Api'], function () {
        Route::post('/checkAvailability', [ReservationController::class, 'checkAvailability']);
        Route::post('/create_reservation', [ReservationController::class, 'create_reservation']);
        Route::get('/menu', [ReservationController::class, 'menu_list']);
        Route::post('/create_order', [ReservationController::class, 'create_order']);
        Route::get('/checkout', [ReservationController::class, 'checkout']);


});

