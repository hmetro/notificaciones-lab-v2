<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Requester;
use Illuminate\Support\Facades\Storage;
use App\Models\Ordenes;
use Illuminate\Http\Request;

class OrdenesController extends Controller
{
    public function extraer()
    {
        try {
            $requester = new Requester();

            $ordenes = $requester->fetchOrdenes();

            if ($ordenes["success"]) {
                $baseDir = '1ordenes' . DIRECTORY_SEPARATOR;
                $dayOrders = '0ordenesdia' . DIRECTORY_SEPARATOR;
                $cont = 0;

                foreach ($ordenes["data"] as $orden) {
                    $exists = Ordenes::checkFile($orden);
                    if (!$exists) {
                        $newOrden = new Ordenes($orden);
                        $name = $newOrden->getFileName();
                        $json = $newOrden->toJson();
                        Storage::disk('local')->put($baseDir . $name, $json);
                        Storage::disk('local')->put($dayOrders . $name, $json);
                        $cont++;
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

    public function filtrar(Request $request)
    {
        try {
            $sc = $request->input('sc');
            $fecha = $request->input('fecha');
            $folder = '1ordenes';
            
            if($sc != null && $fecha != null){
                if(Ordenes::checkOrder($folder, $sc, $fecha)){
                    $ordenes = array($folder. DIRECTORY_SEPARATOR . "sc_" . $sc . "_" . $fecha . ".json");
                }else{
                    $ordenes = array();
                }
            }else{
                $ordenes = Ordenes::getOrders();
            }

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
                            if (count($ordenStorage->emails) > 0) {
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
                    'message'   => 'No hay ordenes por filtrar'
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

    public function validar(Request $request)
    {
        try {
            $sc = $request->input('sc');
            $fecha = $request->input('fecha');
            $revalid = $request->input('revalidar');
            $folder = $revalid ? '2validando' .  DIRECTORY_SEPARATOR . 'xrevalidar' : '2validando';
            $file = false;
            
            if($sc != null && $fecha != null){
                if(Ordenes::checkOrder($folder, $sc, $fecha)){
                    $ordenes = array($folder. DIRECTORY_SEPARATOR . "sc_" . $sc . "_" . $fecha . ".json");
                    $file = true;
                }else{
                    $ordenes = array();
                }
            }else{
                $ordenes = Ordenes::getValidOrders();
            }

            $revalidar = '2validando' . DIRECTORY_SEPARATOR . 'xrevalidar' . DIRECTORY_SEPARATOR;
            $porenviar = '3porenviar' . DIRECTORY_SEPARATOR;
            $contEnviar = 0;
            $contRevalidar = 0;

            if(count($ordenes) != 0 && !$revalid){
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
                $contRevalidadas = Ordenes::reValidate($file ? $ordenes : '');
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

    public function enviar(Request $request)
    {
        try {
            $sc = $request->input('sc');
            $fecha = $request->input('fecha');
            $reenviar = $request->input('reenviar');
            $folder = $reenviar ? '4enviadas' : '3porenviar';
            
            if($sc != null && $fecha != null){
                if(Ordenes::checkOrder($folder, $sc, $fecha)){
                    $ordenes = array($folder. DIRECTORY_SEPARATOR . "sc_" . $sc . "_" . $fecha . ".json");
                }else{
                    $ordenes = array();
                }
            }else{
                $ordenes = Ordenes::getOrdersToSend();
            }

            $requester = new Requester();
            $errorenviadas = '4enviadas' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $errorporenviar = '3porenviar' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $enviadas = '4enviadas' . DIRECTORY_SEPARATOR;
            $contEnviadas = 0;
            $contErrores = 0;

            if(count($ordenes) != 0){
                foreach ($ordenes as $orden) {
                    $ordenStorage = new Ordenes($orden, true);
                    $resultpdf = $requester->fetchPDF($ordenStorage);
                    if($resultpdf["success"] == true){
                        $errores = false;
                        foreach ($ordenStorage->emails as $mail) {
                            if(!$ordenStorage->sendNotification($resultpdf["data"], $mail)){
                                $errores = true;
                            }
                        }
                        if(!$reenviar){
                            if($errores){
                                $saved = Storage::disk('local')->put($errorenviadas . $ordenStorage->getFileName(), $ordenStorage->toJson());
                                if ($saved) {
                                    Storage::disk('local')->delete($orden);
                                    $contErrores++;
                                }
                            }else{
                                $saved = Storage::disk('local')->put($enviadas . $ordenStorage->getFileName(), $ordenStorage->toJson());
                                if ($saved) {
                                    Storage::disk('local')->delete($orden);
                                    $contEnviadas++;
                                }
                            }
                        }else{
                            if($errores){
                                $contErrores++;
                            }else{
                                $contEnviadas++;
                            }
                        }
                    }else{
                        $saved = Storage::disk('local')->put($errorporenviar . $ordenStorage->getFileName(), $ordenStorage->toJson());
                        if ($saved) {
                            Storage::disk('local')->delete($orden);
                            $contErrores++;
                        }
                    }
                }
                return response()->json([
                    'success'   => true,
                    'message'   => $contEnviadas . ' ordenes enviadas y ' . $contErrores .' ordenes con error al enviar. :D'
                ], 200);
            }else{
                return response()->json([
                    'success'   => true,
                    'message'   => 'No hay ordenes por enviar :D'
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

    public function limpiar(){
        try {
            $ordenes = Ordenes::getDayOrders();
            $cont = 0;

            if(count($ordenes) != 0){
                foreach ($ordenes as $orden) {
                    Storage::disk('local')->delete($orden);
                    $cont++;
                }
            }

            return response()->json([
                'success'   => true,
                'message'   => $cont . ' ordenes limpiadas. :D'
            ], 200);

        } catch (\Throwable $th) {
            dd($th);
            return response()->json([
                'success'   => false,
                'message'   => 'Algo salió mal :/'
            ], 500);
        }
    }

    public function listar(Request $request){
        $tipoOrdenes = $request->input('tipo');
        $errores = $request->input('errores');
        $limit = $request->input('limit') ? $request->input('limit')*1 : 10;
        $withdata = $request->input('withdata');
        $page = $request->input('page') ? $request->input('page')*1 : 1;

        switch ($tipoOrdenes) {
            case 'deldia':
                $ordenes = Ordenes::getDayOrders();
                break;
            case 'ordenes':
                $ordenes = Ordenes::getOrders($errores);
                break;
            case 'validando':
                $ordenes = Ordenes::getValidOrders($errores);
                break;
            case 'revalidando':
                $ordenes = Ordenes::getToRevalidateOrders();
                break;
            case 'porenviar':
                $ordenes = Ordenes::getOrdersToSend($errores);
                break;
            case 'enviadas':
                $ordenes = Ordenes::getSendedOrders($errores);
                break;

            default:
                return response()->json([
                    'success'   => false,
                    'message'   => 'Usa query params tipo=[ordenes, validando, revalidando, porenviar, enviadas], opcional errores=[0,1], limit=[number, deffault=10], withdata=[0,1], page=[number, deffault=1]'
                ], 400);
                break;
        }

        $numOrdenes = count($ordenes);
        $lastPage = (int) ceil($numOrdenes/$limit);
        $data = array();
        $inic = $limit*($page-1) >= $numOrdenes ? 0 : $limit*($page-1);
        $fin = $limit*$page > $numOrdenes ? $numOrdenes : $limit*($page);
        if($inic == 0 && $fin == $numOrdenes && $limit < $numOrdenes){
            $inic = 0;
            $fin = 0;
        }

        // dd(array(
        //     'page' => $page,
        //     'limit' => $limit,
        //     'total' => $numOrdenes,
        //     'last_page' => $lastPage,
        //     'inic' => $inic,
        //     'fin' => $fin
        // ));

        for ($i=$inic; $i < $fin; $i++) {
            if($withdata){
                array_push($data, get_object_vars(new Ordenes($ordenes[$i], true)));
            }else{
                array_push($data, $ordenes[$i]);
            }
            
        }

        return response()->json([
            'success'   => true,
            'data'   => $data,
            'pagination' => array(
                'page' => $page,
                'limit' => $limit,
                'total' => $numOrdenes,
                'last_page' => $lastPage
            )
        ], 200);
    }
}
