<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use App\Requester;
use Illuminate\Support\Facades\Storage;
use App\Models\Ordenes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use setasign\Fpdi\Fpdi;

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
                $contDia = 0;
                $contOrd = 0;

                foreach ($ordenes["data"] as $orden) {
                    $exists = Ordenes::checkFile($orden);
                    if (!$exists) {
                        $newOrden = new Ordenes($orden);
                        $name = $newOrden->getFileName();
                        
                        if(Storage::disk('local')->put($baseDir . $name, $newOrden->toJson())){
                            $contOrd++;
                        }
                        if(Storage::disk('local')->put($dayOrders . $name, json_encode(array(
                            'errors' => 0,
                            'where' => 'ordenes',
                            'path' => './1ordenes/' . $name
                        )))){
                            $contDia++;
                        }
                    }
                }

                return response()->json([
                    'success'   => true,
                    'message'   => 'Ordenes archivadas :D',
                    'data' => array(
                        'ordenesDia' => $contDia,
                        'ordenes' => $contOrd,
                    )
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
            $dayOrders = '0ordenesdia' . DIRECTORY_SEPARATOR;
            $validando = '2validando' . DIRECTORY_SEPARATOR;
            $exepciones = '1ordenes' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $exepcionesValidando = '2validando' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $exepcionesEnvio = '3porenviar' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $valids = 0;
            $excepFiltro = 0;
            $excepValid = 0;
            $excepEnvio = 0;

            if ($numOrdenes != 0) {
                $lim = $numOrdenes < 5 ? $numOrdenes : 5;
                for ($i = 0; $i < $lim; $i++) {
                    $orden = $ordenes[$i];
                    $ordenStorage = new Ordenes($orden, true);
                    $results = $requester->getOrderResults($ordenStorage);
                    $name = $ordenStorage->getFileName();

                    if (!$results["success"] && $results["message"] == "Sin resultados") {
                        Storage::disk('local')->move($orden, $exepciones . $name);
                        Storage::disk('local')->put($dayOrders . $name, json_encode(array(
                            'errors' => 1,
                            'where' => 'ordenes',
                            'path' => './1ordenes/errores/' . $name
                        )));
                        $excepFiltro++;
                    } else {
                        $ordenStorage->addResults($results);
                        if ($ordenStorage->applyRules()) {
                            if (count($ordenStorage->emails) > 0) {
                                $saved = Storage::disk('local')->put($validando . $name, $ordenStorage->toJson());
                                Storage::disk('local')->put($dayOrders . $name, json_encode(array(
                                    'errors' => 0,
                                    'where' => 'validando',
                                    'path' => './2validando/' . $name
                                )));
                                if ($saved) {
                                    Storage::disk('local')->delete($orden);
                                    $valids++;
                                }
                            }else{
                                $saved = Storage::disk('local')->put($exepcionesEnvio . $name, $ordenStorage->toJson());
                                Storage::disk('local')->put($dayOrders . $name, json_encode(array(
                                    'errors' => 1,
                                    'where' => 'porenviar',
                                    'path' => './3porenviar/errores/' . $name
                                )));
                                if ($saved) {
                                    Storage::disk('local')->delete($orden);
                                    $excepEnvio++;
                                }
                            }
                            
                        } else {
                            $saved = Storage::disk('local')->put($exepcionesValidando . $name, $ordenStorage->toJson());
                            Storage::disk('local')->put($dayOrders . $name, json_encode(array(
                                'errors' => 1,
                                'where' => 'validando',
                                'path' => './2validando/errores/' . $name
                            )));
                            if ($saved) {
                                Storage::disk('local')->delete($orden);
                                $excepValid++;
                            }
                        }
                    }
                }

                return response()->json([
                    'success'   => true,
                    'message'   => 'Ordenes filtradas. :D',
                    'data' => array(
                        'errOrdenes' => $excepFiltro,
                        'enValidar' => $valids,
                        'errValidando' => $excepValid,
                        'errEnvio' => $excepEnvio,
                    )
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

            $dayOrders = '0ordenesdia' . DIRECTORY_SEPARATOR;
            $revalidar = '2validando' . DIRECTORY_SEPARATOR . 'xrevalidar' . DIRECTORY_SEPARATOR;
            $porenviar = '3porenviar' . DIRECTORY_SEPARATOR;
            $contEnviar = 0;
            $contRevalidar = 0;

            if(count($ordenes) != 0 && !$revalid){
                foreach ($ordenes as $orden) {
                    $ordenStorage = new Ordenes($orden, true);
                    $name = $ordenStorage->getFileName();

                    if($ordenStorage->isValid()){
                        $saved = Storage::disk('local')->put($porenviar . $name, $ordenStorage->toJson());
                        Storage::disk('local')->put($dayOrders . $name, json_encode(array(
                            'errors' => 0,
                            'where' => 'porenviar',
                            'path' => './3porenviar/' . $name
                        )));
                        if ($saved) {
                            Storage::disk('local')->delete($orden);
                            $contEnviar++;
                        }
                    }else{
                        $saved = Storage::disk('local')->put($revalidar . $name, $ordenStorage->toJson());
                        Storage::disk('local')->put($dayOrders . $name, json_encode(array(
                            'errors' => 0,
                            'where' => 'revalidando',
                            'path' => './2validando/xrevalidar/' . $name
                        )));
                        if ($saved) {
                            Storage::disk('local')->delete($orden);
                            $contRevalidar++;
                        }
                    }
                }
                return response()->json([
                    'success'   => true,
                    'message'   => 'Ordenes validadas. :D',
                    'data' => array(
                        'porenviar' => $contEnviar,
                        'xrevalidar' => $contRevalidar,
                    )
                ], 200);
            }else{
                $dataRevalid = Ordenes::reValidate($file ? $ordenes : '');
                if($dataRevalid != null){
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Ordenes actualizadas para validar nuevamente. :D',
                        'data' => $dataRevalid
                    ], 200);
                } else {
                    return response()->json([
                        'success'   => true,
                        'message'   => 'No hay ordenes para validar o revalidar. :D',
                        'data' => 0
                    ], 200);
                }
                
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
            $dayOrders = '0ordenesdia' . DIRECTORY_SEPARATOR;
            $errorenviadas = '4enviadas' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $errorporenviar = '3porenviar' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $enviadas = '4enviadas' . DIRECTORY_SEPARATOR;
            $ocr = 'pcr' . DIRECTORY_SEPARATOR;
            $contEnviadas = 0;
            $contErrores = 0;
            $errXenviar = 0;

            if(count($ordenes) != 0){
                foreach ($ordenes as $orden) {
                    $ordenStorage = new Ordenes($orden, true);
                    $resultpdf = array(
                        'success' => false,
                    );

                    if($ordenStorage->isPCR()){
                        $firstPdf = $requester->fetchPDF($ordenStorage);
                        if($firstPdf["success"] == true){
                            $url = $firstPdf["data"];
                            $fileName = $ordenStorage->sc . '.pdf';
                            $qrName = $ordenStorage->sc . '.png';
                            Storage::disk('public')->put('pcr' . DIRECTORY_SEPARATOR . $fileName, file_get_contents($url));
                            $fullUrl = URL::to('/storage/pcr/' . $fileName);
                            $dir = public_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'pcr' . DIRECTORY_SEPARATOR . $fileName;
                            $qrCode = Builder::create()
                                ->writer(new PngWriter())
                                ->writerOptions([])
                                ->data($fullUrl)
                                ->encoding(new Encoding('UTF-8'))
                                ->logoPath(__DIR__ . DIRECTORY_SEPARATOR . 'hm.png')
                                ->build();
                            Storage::disk('public')->put('pcr' . DIRECTORY_SEPARATOR . 'qrs' . DIRECTORY_SEPARATOR . $qrName, $qrCode->getString());

                            $dirQr = public_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'pcr' . DIRECTORY_SEPARATOR . 'qrs' . DIRECTORY_SEPARATOR . $qrName;

                            $pdf = new Fpdi();
                            $staticIds = array();
                            $pageCount = $pdf->setSourceFile($dir);

                            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                                $staticIds[$pageNumber] = $pdf->importPage($pageNumber);
                            }
                
                            // get the page count of the uploaded file
                            $pageCount = $pdf->setSourceFile($dir);
                
                            // let's track the page number for the filler page
                            $fillerPageCount = 1;
                            // import the uploaded document page by page
                            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                
                                if ($fillerPageCount == 1) {
                                    // add the current filler page
                                    $pdf->AddPage();
                                    $pdf->useTemplate($staticIds[$fillerPageCount]);
                                    $pdf->Image($dirQr, 5, 237, 40, 40);
                                    // QR ACESS
                                    // $pdf->Image($qrAcess, 46, 237.1, 40, 39);
                                }
                
                                // update the filler page number or reset it
                                $fillerPageCount++;
                                if ($fillerPageCount > count($staticIds)) {
                                    $fillerPageCount = 1;
                                }
                            }
                
                            Storage::disk('public')->put('pcr' . DIRECTORY_SEPARATOR . $fileName, $pdf->Output('S'));
                            
                            $resultpdf = array(
                                'success' => true,
                                'data' => $fullUrl,
                            );
                        }else{
                            return response()->json([
                                'success'   => false,
                                'message'   => 'No hay el resultado de la orden :/'
                            ], 200);
                        }
                    }else{
                        $resultpdf = $requester->fetchPDF($ordenStorage);
                    }

                    $name = $ordenStorage->getFileName();

                    if($resultpdf["success"] == true){
                        $errores = false;
                        foreach ($ordenStorage->emails as $mail) {
                            if(!$ordenStorage->sendNotification($resultpdf["data"], $mail)){
                                $errores = true;
                            }
                        }
                        if(!$reenviar){
                            if($errores){
                                $saved = Storage::disk('local')->put($errorenviadas . $name, $ordenStorage->toJson());
                                Storage::disk('local')->put($dayOrders . $name, json_encode(array(
                                    'errors' => 1,
                                    'where' => 'enviadas',
                                    'path' => './4enviadas/errores/' . $name
                                )));
                                if ($saved) {
                                    Storage::disk('local')->delete($orden);
                                    $contErrores++;
                                }
                            }else{
                                $saved = Storage::disk('local')->put($enviadas . $name, $ordenStorage->toJson());
                                Storage::disk('local')->put($dayOrders . $name, json_encode(array(
                                    'errors' => 0,
                                    'where' => 'enviadas',
                                    'path' => './4enviadas/' . $name
                                )));
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
                        $saved = Storage::disk('local')->put($errorporenviar . $name, $ordenStorage->toJson());
                        Storage::disk('local')->put($dayOrders . $name, json_encode(array(
                            'errors' => 1,
                            'where' => 'porenviar',
                            'path' => './3porenviar/errores/' . $name
                        )));
                        if ($saved) {
                            Storage::disk('local')->delete($orden);
                            $errXenviar++;
                        }
                    }
                }
                return response()->json([
                    'success'   => true,
                    'message'   => 'Ordenes enviadas. :D',
                    'data' => array(
                        'enviadas' => $contEnviadas,
                        'errEnvio' => $contErrores,
                        'errXdnviar' => $errXenviar,
                    )
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

    public function fileInfo(Request $request){
        try {
            $sc = $request->input('sc');
            $fecha = $request->input('fecha');
            $folder = '0ordenesdia' .  DIRECTORY_SEPARATOR;
            
            if($sc != null && $fecha != null){
                $name = $folder. DIRECTORY_SEPARATOR . "sc_" . $sc . "_" . $fecha . ".json";

                if(Ordenes::checkOrder($folder, $sc, $fecha)){;
                    $ordenStorage = new Ordenes($name, true);
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Datos orden',
                        'data' => get_object_vars($ordenStorage)
                    ], 200);
                }else{
                    return response()->json([
                        'success'   => true,
                        'message'   => 'Orden no encontrada'
                    ], 404);
                }
            }else{
                return response()->json([
                    'success'   => true,
                    'message'   => 'Debes enviar el sc y la fecha'
                ], 400);
            }
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
