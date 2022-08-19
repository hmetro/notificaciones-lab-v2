<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Requester;
use Illuminate\Support\Facades\Storage;
use App\Models\Ordenes;

class OrdenesController extends Controller
{
    public function extraer()
    {
        try {
            $requester = new Requester();

            $ordenes = $requester->fetchOrdenes();

            if ($ordenes["success"]) {
                $baseDir = '1ordenes' . DIRECTORY_SEPARATOR;
                $cont = 0;

                foreach ($ordenes["data"] as $orden) {
                    $exists = Ordenes::checkFile($orden);
                    if (!$exists) {
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
            dd($th);
            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        }
    }

    public function filtrar()
    {
        try {
            $ordenes = Ordenes::getOrders();
            $requester = new Requester();
            $validando = '2validando' . DIRECTORY_SEPARATOR;
            $exepciones = '1ordenes' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $exepcionesValidando = '2validando' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $cont = 0;

            if (count($ordenes) != 0) {
                for ($i = 0; $i < 3; $i++) {
                    $orden = $ordenes[$i];
                    $ordenStorage = new Ordenes($orden, true);
                    $results = $requester->getOrderResults($ordenStorage);

                    if (!$results["success"] && $results["message"] == "Sin resultados") {
                        Storage::disk('local')->move($orden, $exepciones . $ordenStorage->getFileName());
                    } else {
                        $ordenStorage->addResults($results);
                        if ($ordenStorage->applyRules()) {
                            $saved = Storage::disk('local')->put($validando . $ordenStorage->getFileName(), $ordenStorage->toJson());
                            if ($saved) {
                                Storage::disk('local')->delete($orden);
                                $cont++;
                            }
                        } else {
                            $saved = Storage::disk('local')->put($exepcionesValidando . $ordenStorage->getFileName(), $ordenStorage->toJson());
                            if ($saved) {
                                Storage::disk('local')->delete($orden);
                                $cont++;
                            }
                        }
                    }
                }

                return response()->json([
                    'success'   => true,
                    'message'   => $cont . ' nuevas ordenes filtradas. :D'
                ], 200);
            } else {
                return response()->json([
                    'success'   => true,
                    'message'   => 'No hay ordenes por validar'
                ], 200);
            }
        } catch (\Throwable $th) {
            dd($th);
            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        }
    }

    public function validar()
    {
    }

    public function enviar()
    {
    }
}
