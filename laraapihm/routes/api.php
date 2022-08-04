<?php

use Illuminate\Http\Request;
use Viewflex\Zoap\ZoapController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PedidoLaboratorioController;
use App\Http\Controllers\API\SoapController;

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

Route::get('test', function(){
    return "API Working";
});

Route::middleware(['xml'])->group(function () {
    
    Route::apiResource('pedido-lab', PedidoLaboratorioController::class);

});

Route::get('zoap/{key}/server', [
    'as' => 'zoap.server.wsdl',
    'uses' => 'Viewflex\Zoap\ZoapController@server',
]);


Route::post('zoap/{key}/server', [
    'as' => 'zoap.server',
    'uses' => 'Viewflex\Zoap\ZoapController@server',
]);
