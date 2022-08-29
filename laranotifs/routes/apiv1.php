<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiV1\ApiV1Controller;
use App\Http\Controllers\ApiV1\OrdenesController;
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

Route::get('extraer/ordenes', [OrdenesController::class, 'extraer']);

Route::get('filtrar/ordenes', [OrdenesController::class, 'filtrar']);

Route::get('validar/ordenes', [OrdenesController::class, 'validar']);

Route::get('enviar/ordenes', [OrdenesController::class, 'enviar']);

Route::get('limpiar/ordenes', [OrdenesController::class, 'limpiar']);

Route::get('listar/ordenes', [OrdenesController::class, 'listar']);

Route::get('buscar/ordenes', [OrdenesController::class, 'fileInfo']);

Route::get('status', [ApiV1Controller::class, 'isWorking']);