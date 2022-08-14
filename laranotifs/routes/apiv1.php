<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiV1\ApiV1Controller;
use App\Http\Controllers\ApiV1\ReglasNegocioController;

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

Route::apiResource('reglas', ReglasNegocioController::class);

Route::get('extraer/ordenes', [ApiV1Controller::class, 'isWorking']);

Route::get('filtrar/ordenes', [ApiV1Controller::class, 'isWorking']);

Route::get('validar/ordenes', [ApiV1Controller::class, 'isWorking']);

Route::get('enviar/ordenes', [ApiV1Controller::class, 'isWorking']);