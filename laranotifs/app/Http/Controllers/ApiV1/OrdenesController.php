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
            $numOrdenes = count($ordenes);
            $requester = new Requester();
            $validando = '2validando' . DIRECTORY_SEPARATOR;
            $exepciones = '1ordenes' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $exepcionesValidando = '2validando' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $exepcionesEnvio = '3porenviar' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $cont = 0;

            if ($numOrdenes != 0) {
                $lim = $numOrdenes < 5 ? $numOrdenes : 5;
                for ($i = 0; $i < $lim; $i++) {
                    $orden = $ordenes[$i];
                    $ordenStorage = new Ordenes($orden, true);
                    $results = $requester->getOrderResults($ordenStorage);

                    if (!$results["success"] && $results["message"] == "Sin resultados") {
                        Storage::disk('local')->move($orden, $exepciones . $ordenStorage->getFileName());
                    } else {
                        $ordenStorage->addResults($results);
                        if ($ordenStorage->applyRules()) {
                            $enviarPac = true;
                            $enviarDoc = false;

                            foreach (array_reverse($ordenStorage->dataEnvio) as $data) {
                                if($data["tipoRegla"] == "NE" && $data["direccion"] == 0){
                                    $enviarPac = false;
                                }
                                if($data["tipoRegla"] == "E" && $data["direccion"] != 0){
                                    $enviarDoc = true;
                                }
                            }
                            if ($enviarDoc || $enviarPac) {
                                $saved = Storage::disk('local')->put($validando . $ordenStorage->getFileName(), $ordenStorage->toJson());
                                if ($saved) {
                                    Storage::disk('local')->delete($orden);
                                    $cont++;
                                }
                            }else{
                                $saved = Storage::disk('local')->put($exepcionesEnvio . $ordenStorage->getFileName(), $ordenStorage->toJson());
                                if ($saved) {
                                    Storage::disk('local')->delete($orden);
                                    $cont++;
                                }
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
        try {
            $ordenes = Ordenes::getValidOrders();
            $revalidar = '2validando' . DIRECTORY_SEPARATOR . 'xrevalidar' . DIRECTORY_SEPARATOR;
            $porenviar = '3porenviar' . DIRECTORY_SEPARATOR;
            $contEnviar = 0;
            $contRevalidar = 0;

            if(count($ordenes) != 0){
                foreach ($ordenes as $orden) {
                    $ordenStorage = new Ordenes($orden, true);
                    if($ordenStorage->isValid()){
                        $saved = Storage::disk('local')->put($porenviar . $ordenStorage->getFileName(), $ordenStorage->toJson());
                        if ($saved) {
                            Storage::disk('local')->delete($orden);
                            $contEnviar++;
                        }
                    }else{
                        $saved = Storage::disk('local')->put($revalidar . $ordenStorage->getFileName(), $ordenStorage->toJson());
                        if ($saved) {
                            Storage::disk('local')->delete($orden);
                            $contRevalidar++;
                        }
                    }
                }
                return response()->json([
                    'success'   => true,
                    'message'   => $contEnviar . ' ordenes listas para enviar y ' . $contRevalidar .' ordenes enviadas a revalidar. :D'
                ], 200);
            }else{
                $contRevalidadas = Ordenes::reValidate();
                return response()->json([
                    'success'   => true,
                    'message'   => $contRevalidadas . ' ordenes listas para validar nuevamente. :D'
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

    public function enviar()
    {
        try {
            $ordenes = Ordenes::getOrdersToSend();
            $revalidar = '2validando' . DIRECTORY_SEPARATOR . 'xrevalidar' . DIRECTORY_SEPARATOR;
            $porenviar = '3porenviar' . DIRECTORY_SEPARATOR;
            $contEnviar = 0;
            $contRevalidar = 0;

            if(count($ordenes) != 0){
                foreach ($ordenes as $orden) {
                    $ordenStorage = new Ordenes($orden, true);
                    if($ordenStorage->isValid()){
                        $saved = Storage::disk('local')->put($porenviar . $ordenStorage->getFileName(), $ordenStorage->toJson());
                        if ($saved) {
                            Storage::disk('local')->delete($orden);
                            $contEnviar++;
                        }
                    }else{
                        $saved = Storage::disk('local')->put($revalidar . $ordenStorage->getFileName(), $ordenStorage->toJson());
                        if ($saved) {
                            Storage::disk('local')->delete($orden);
                            $contRevalidar++;
                        }
                    }
                }
                return response()->json([
                    'success'   => true,
                    'message'   => $contEnviar . ' ordenes listas para enviar y ' . $contRevalidar .' ordenes enviadas a revalidar. :D'
                ], 200);
            }else{
                $contRevalidadas = Ordenes::reValidate();
                return response()->json([
                    'success'   => true,
                    'message'   => $contRevalidadas . ' ordenes listas para validar nuevamente. :D'
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
}
