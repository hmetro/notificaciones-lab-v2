<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

class Ordenes
{
    public string $numeroHistoriaClinica;
    public string $nombresPaciente;
    public string $apellidosPaciente;
    public string $sc;
    public string $fechaExamen;
    public string $horaExamen;
    public ?string $doctor;
    public string $servicio;
    public string $origen;
    public string $motivo;
    public ?string $especialidad;
    public int $validacionClinica;
    public int $validacionMicro;
    public array $reglasFiltros;
    public array $dataEnvio;
    public array $logsEnvio;
    public array $dataClinica;
    public array $dataMicro;

    public function addResults($results){
        if($results["data"]["results"] != false){
            foreach ($results["data"]["results"] as $result) {
                array_push($this->dataClinica, [
                    "testId" => $result->TestID,
                    "testStatus" => $result->TestStatus,
                    "nombreTest" => $result->TestName,
                    "lastVerified" => "",
                ]);
                
            }
        }

        if($results["data"]["microResults"] != false){

        }
    }

    public function applyRules(){
        $reglas = Reglas::all();
        dd($reglas, $this);
    }

    public function getFileName(){
        return "sc_" . $this->sc . "_" . $this->fechaExamen . ".json";
    }

    public function toJson(){
        return json_encode(get_object_vars($this));
    }

    public static function checkFile($fromServer){
        return Storage::disk('local')->exists('1ordenes'. DIRECTORY_SEPARATOR . "sc_" . $fromServer["SampleID"] . "_" . $fromServer["RegisterDate"] . ".json");
    }

    public static function getOrders(){
        return Storage::disk('local')->files('1ordenes');
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
            $this->servicio = $data["Service"];
            $this->origen = $data["Origin"];
            $this->motivo = $data["D_112"];
            $this->especialidad = $data["D_117"] ?? "";
            $this->validacionClinica = -1;
            $this->validacionMicro = -1;
            $this->reglasFiltros = [];
            $this->dataEnvio = [];
            $this->logsEnvio = [];
            $this->dataClinica = [];
            $this->dataMicro = [];
        }
    }
}
