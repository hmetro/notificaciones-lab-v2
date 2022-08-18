<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Requester;
use Illuminate\Support\Facades\Storage;
use App\Models\Ordenes;

class OrdenesController extends Controller
{
    public function extraer(){
        try {
            $requester = new Requester();

            $ordenes = $requester->fetchOrdenes();

            if($ordenes["success"]){
                $baseDir = '1ordenes' . DIRECTORY_SEPARATOR;
                $cont = 0;

                foreach ($ordenes["data"] as $orden) {
                    $exists = Ordenes::checkFile($orden);
                    if(!$exists){
                        $cont++;
                        $newOrden = new Ordenes($orden);
                        Storage::disk('local')->put($baseDir . $newOrden->getFileName(), $newOrden->toJson());
                    }
                }

                return response()->json([
                    'success'   => true,
                    'message'   => $cont . ' nuevas ordenes archivadas. :D'
                ], 200);
            }

            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        }
    }

    public function filtrar(){
        try {
            $ordenes = Ordenes::getOrders();
            $requester = new Requester();
            $validando = '2validando' . DIRECTORY_SEPARATOR;
            $xrevalidar = '2validando' . DIRECTORY_SEPARATOR . 'xrevalidar' . DIRECTORY_SEPARATOR;

            foreach ($ordenes as $orden) {
                $ordenStorage = new Ordenes($orden, true);
                $results = $requester->getOrderResults($ordenStorage);

                if(!$results["success"]){
                    dd("sin resultados");
                    Storage::disk('local')->move($orden, $xrevalidar . $ordenStorage->getFileName());
                }else{
                    $ordenStorage->addResults($results);
                    $ordenStorage->applyRules();
                }
            }

            
        } catch (\Throwable $th) {
            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        }
    }

    public function validar(){
        
    }

    public function enviar(){
        
    }
}
