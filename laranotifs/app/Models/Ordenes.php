<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use App\Requester;

class Ordenes
{
    public string $numeroHistoriaClinica;
    public string $nombresPaciente;
    public string $apellidosPaciente;
    public string $sc;
    public string $fechaExamen;
    public string $horaExamen;
    public string $doctor;
    public string $servicio;
    public string $origen;
    public string $motivo;
    public string $especialidad;
    public int $validacionClinica;
    public int $validacionMicro;
    public int $revalidationCount;
    public array $reglasFiltros;
    public array $dataEnvio;
    public array $logsEnvio;
    public array $emails;
    public array $dataClinica;
    public array $dataMicro;

    public function addResults($results){
        try {
            if($results["data"]["results"] != false && !isset($results["data"]["results"]->TestID)){
                foreach ($results["data"]["results"] as $result) {
                    
                    array_push($this->dataClinica, [
                        "testId" => $result->TestID,
                        "testStatus" => $result->TestStatus,
                        "nombreTest" => $result->TestName,
                        "lastVerified" => "",
                    ]);
                    $this->validacionClinica = 0;
                }
            }else if(isset($results["data"]["results"]->TestID)){
                array_push($this->dataClinica, [
                    "testId" => $results["data"]["results"]->TestID,
                    "testStatus" => $results["data"]["results"]->TestStatus,
                    "nombreTest" => $results["data"]["results"]->TestName,
                    "lastVerified" => "",
                ]);
                $this->validacionClinica = 0;
            }
    
            if($results["data"]["microResults"] != false && !isset($results["data"]["microResults"]->SpecimenName)){
                $listaMicro = $results["data"]["microResults"];
                foreach ($listaMicro as $result) {
                    $res = $result->MicTests->LISLabTest;
                    if(isset($res->TestID)){
                        array_push($this->dataMicro, [
                            "testId" => $res->TestID,
                            "testStatus" => $res->TestStatus,
                            "nombreTest" => $res->TestName,
                            "lastVerified" => "",
                        ]);
                        $this->validacionMicro = 0;
                    }else{
                        foreach ($res as $r) {
                            array_push($this->dataMicro, [
                                "testId" => $r->TestID,
                                "testStatus" => $r->TestStatus,
                                "nombreTest" => $r->TestName,
                                "lastVerified" => "",
                            ]);
                            $this->validacionMicro = 0;
                        }
                    }
                }
            }else if (isset($results["data"]["microResults"]->SpecimenName)){
                $listaMicro = $results["data"]["microResults"]->MicTests->LISLabTest;
                if(isset($listaMicro->TestID)){
                    array_push($this->dataMicro, [
                        "testId" => $listaMicro->TestID,
                        "testStatus" => $listaMicro->TestStatus,
                        "nombreTest" => $listaMicro->TestName,
                        "lastVerified" => "",
                    ]);
                    $this->validacionMicro = 0;
                }else{
                    foreach ($listaMicro as $result) {
                        array_push($this->dataMicro, [
                            "testId" => $result->TestID,
                            "testStatus" => $result->TestStatus,
                            "nombreTest" => $result->TestName,
                            "lastVerified" => "",
                        ]);
                        $this->validacionMicro = 0;
                    }
                }
            }
            return true;
        } catch (\Throwable $th) {
            dd($th);
            return false;
        }
    }

    public function applyRules(){
        $reglas = [
            "servicio" => Reglas::whereNotNull("x_servicio")->orderBy("ene", "desc")->get(),
            "origen" => Reglas::whereNotNull("x_origen")->orderBy("ene", "desc")->get(),
            "motivo" => Reglas::whereNotNull("x_motivo")->orderBy("ene", "desc")->get(),
            "especialidad" => Reglas::whereNotNull("x_especialidad")->orderBy("ene", "desc")->get(),
            "doctor" => Reglas::whereNotNull("x_medico")->orderBy("ene", "desc")->get(),
            "dataClinica" => Reglas::whereNotNull("x_idprueba")->orderBy("ene", "desc")->get(),
        ];

        $dic = [
            "servicio" => "x_servicio",
            "origen" => "x_origen",
            "motivo" => "x_motivo",
            "especialidad" => "x_especialidad",
            "doctor" => "x_medico",
            "dataClinica" => "x_idprueba",
        ];

        $ene = true;
        $addmails = true;
        for ($i=0; $i < 2; $i++) { 
            foreach ($reglas as $key => $aplicar) {
                foreach ($aplicar as $regla) {
                    if($regla->ene == $ene){
                        if($key == "dataClinica"){
                            $dbProp = $dic[$key];
                            foreach (array_merge($this->dataClinica, $this->dataMicro) as $result) {
                                if($result["testId"] == $regla->$dbProp){
                                    array_push($this->reglasFiltros, [
                                        "idRegla" => $regla->id,
                                        "nombreRegla" => $regla->nombre,
                                        "tipoRegla" => $regla->ene ? "E" : "NE",
                                        "aplicada" => date("Y-m-d e h:i:s"),
                                    ]);
    
                                    if($regla->add_json != null){
                                        $add = json_decode($regla->add_json, true);
                                        array_push($this->dataEnvio, [
                                            "idRegla" => $regla->id,
                                            "tipoRegla" => $regla->ene ? "E" : "NE",
                                        ]+$add);

                                        if($add["direccion"] != 0 && $add["direccion"] != 1){
                                            array_push($this->emails, $add["direccion"]);
                                        }else if($add["direccion"] == 0 && $regla->ene == false){
                                            $addmails = false;
                                        }
                                    }
                                }
                            }
                        }else{
                            $dbProp = $dic[$key];
                            if($this->$key == $regla->$dbProp){
                                array_push($this->reglasFiltros, [
                                    "idRegla" => $regla->id,
                                    "nombreRegla" => $regla->nombre,
                                    "tipoRegla" => $regla->ene ? "E" : "NE",
                                    "aplicada" => date("Y-m-d e h:i:s"),
                                ]);

                                if($regla->add_json != null){
                                    $add = json_decode($regla->add_json, true);
                                    array_push($this->dataEnvio, [
                                        "idRegla" => $regla->id,
                                        "tipoRegla" => $regla->ene ? "E" : "NE",
                                    ]+$add);
                                    
                                    if($add["direccion"] != 0 && $add["direccion"] != 1){
                                        array_push($this->emails, $add["direccion"]);
                                    }else if($add["direccion"] == 0 && $regla->ene == false){
                                        $addmails = false;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $ene = false;
        }
        if(count($this->reglasFiltros) == 0){
            $addmails = false;
        }
        if($addmails){
            //añadir correos usuario
            array_push($this->emails, "germariova@gmail.com");
        }

        return !(count($this->reglasFiltros) == 0);
    }

    public function isValid(){
        $validC = true;

        if($this->validacionClinica == 0){
            foreach ($this->dataClinica as $res) {
                $res->lastVerified = date("Y-m-d e h:i:s");
                if($res->testStatus < 4 ){
                    $validC = false;
                }
            }

            if($validC){
                $this->validacionClinica = $this->validacionMicro == -1 || $this->validacionMicro == 1 ? 2 : 1;
            }
        }

        $validM = true;

        if($this->validacionMicro == 0){
            foreach ($this->dataMicro as $res) {
                $res->lastVerified = date("Y-m-d e h:i:s");
                if($res->testStatus < 4 ){
                    $validM = false;
                }
            }
            if($validM){
                $this->validacionMicro = $this->validacionClinica == -1 || $this->validacionClinica == 1 ? 2 : 1;
            }
        }

        return $validC && $validM;
    }

    public function getFileName(){
        return "sc_" . $this->sc . "_" . $this->fechaExamen . ".json";
    }

    public function toJson(){
        return json_encode(get_object_vars($this));
    }

    public static function reValidate($file = ''){
        try {
            if($file != ''){
                $ordenes = $file;
            }else{
                $ordenes = Ordenes::getToRevalidateOrders();
            }

            $numOrdenes = count($ordenes);
            $requester = new Requester();
            $validando = '2validando' . DIRECTORY_SEPARATOR;
            $exepcionesValidando = '2validando' . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR;
            $cont = 0;
            $contErr = 0;

            if ($numOrdenes != 0) {
                $lim = $numOrdenes < 5 ? $numOrdenes : 5;
                for ($i = 0; $i < $lim; $i++) {
                    $orden = $ordenes[$i];
                    $ordenStorage = new Ordenes($orden, true);
                    if($ordenStorage->validacionClinica == 0 && $ordenStorage->validacionMicro == 0){
                        $results = $requester->getOrderResults($ordenStorage);
                    }else{
                        $results = $requester->getOrderResults($ordenStorage, false, $ordenStorage->validacionClinica == 0 ? 0 : 1);
                    }

                    if (!$results["success"] && $results["message"] == "Sin resultados") {
                        Storage::disk('local')->move($orden, $exepcionesValidando . $ordenStorage->getFileName());
                        $contErr++;
                    } else {
                        $ordenStorage->dataClinica = [];
                        $ordenStorage->dataMicro = [];
                        if ($ordenStorage->addResults($results)) {
                            $ordenStorage->revalidationCount++;
                            $saved = Storage::disk('local')->put($validando . $ordenStorage->getFileName(), $ordenStorage->toJson());
                            if ($saved) {
                                if(Storage::disk('local')->delete($orden)){
                                    $cont++;
                                }
                            }
                        }
                    }
                }

                return array(
                    'errValidando' => $contErr,
                    'validando' => $cont,
                );
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function sendNotification($pdf, $to){
        $file = base64_encode(file_get_contents($pdf));
        $adjunto = array();

        $adjunto[] = array(
            'Name' => 'resultado_' . $this->sc . '.pdf',
            'ContentType' => 'application/pdf',
            'Content' => $file,
        );

        $content = "Estimado(a).- <br/>". $this->nombresPaciente . " " . $this->apellidosPaciente ." está disponible un nuevo resultado de Laboratorio. <br/> Fecha de Examen: " . $this->fechaExamen;

        $body = array(
            "TextBody" => "Resultado de Laboratorio - Metrovirtual",
            'From' => 'Metrovirtual metrovirtual@hospitalmetropolitano.org',
            'To' => $to,
            'Subject' => "Nuevo resultado de Laboratorio",
            'HtmlBody' => view('mail.template', compact("content"))->render(),
            'Attachments' => $adjunto,
            'Tag' => 'NRLPPCR',
            'Bcc' => 'mchangcnt@gmail.com',
            'TrackLinks' => 'HtmlAndText',
            'TrackOpens' => true,
        );

        $encoded = json_encode($body);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.trx.icommarketing.com/email");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($encoded),
            'X-Postmark-Server-Token: 75032b22-cf9b-4fd7-8eb4-e7446c8b118b',
        ));

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $resultobj = curl_error($ch);
            array_push($this->logsEnvio, array(
                "email" => $to,
                "log" => $resultobj,
                "request" => $ch,
                "success" => false
            ));
            return false;
        }
        
        curl_close($ch);
        $resultobj = json_decode($result);
        array_push($this->logsEnvio, array(
            "email" => $to,
            "log" => $resultobj,
            "request" => $ch,
            "success" => true
        ));

        return true;
    }

    public static function checkFile($fromServer){
        return Storage::disk('local')->exists('0ordenesdia'. DIRECTORY_SEPARATOR . "sc_" . $fromServer["SampleID"] . "_" . $fromServer["RegisterDate"] . ".json");
    }

    public static function checkOrder($folder, $sc, $fecha){
        return Storage::disk('local')->exists($folder. DIRECTORY_SEPARATOR . "sc_" . $sc . "_" . $fecha . ".json");
    }

    public static function getDayOrders(){
        return Storage::disk('local')->files('0ordenesdia');
    }

    public static function getOrders($errors = false){
        $extra = $errors ? DIRECTORY_SEPARATOR . "errores" : "";
        return Storage::disk('local')->files('1ordenes' . $extra);
    }

    public static function getValidOrders($errors = false){
        $extra = $errors ? DIRECTORY_SEPARATOR . "errores" : "";
        return Storage::disk('local')->files('2validando' . $extra);
    }

    public static function getToRevalidateOrders(){
        return Storage::disk('local')->files('2validando' . DIRECTORY_SEPARATOR . 'xrevalidar');
    }

    public static function getOrdersToSend($errors = false){
        $extra = $errors ? DIRECTORY_SEPARATOR . "errores" : "";
        return Storage::disk('local')->files('3porenviar' . $extra);
    }

    public static function getSendedOrders($errors = false){
        $extra = $errors ? DIRECTORY_SEPARATOR . "errores" : "";
        return Storage::disk('local')->files('4enviadas' . $extra);
    }
    
    public function __construct($data, $fromFile = false){
        if($fromFile){
            $file = Storage::disk('local')->get($data);
            $decoded = json_decode($file);
            foreach($decoded as $key => $value){
                $this->{$key} = $value;
            }
        }else{
            $this->numeroHistoriaClinica = $data["PatientID1"];
            $this->nombresPaciente = $data["FirstName"];
            $this->apellidosPaciente = $data["LastName"];
            $this->sc = $data["SampleID"];
            $this->fechaExamen = $data["RegisterDate"];
            $this->horaExamen = $data["RegisterHour"];
            $this->doctor = $data["Doctor"] ?? "";
            $this->servicio = $data["Service"] ?? "";
            $this->origen = $data["Origin"];
            $this->motivo = $data["D_112"] ?? "";
            $this->especialidad = $data["D_117"] ?? "";
            $this->validacionClinica = -1;
            $this->validacionMicro = -1;
            $this->revalidationCount = 0;
            $this->reglasFiltros = [];
            $this->dataEnvio = [];
            $this->logsEnvio = [];
            $this->emails = [];
            $this->dataClinica = [];
            $this->dataMicro = [];
        }
    }
}
