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
        dd($results);
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
        for ($i=0; $i < 2; $i++) { 
            foreach ($reglas as $key => $aplicar) {
                foreach ($aplicar as $regla) {
                    if($regla->ene == $ene){
                        if($key == "dataClinica"){

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
                                    array_push($this->dataEnvio, [
                                        "idRegla" => $regla->id,
                                    ]+json_decode($regla->add_json, true));
                                }
                            }
                        }
                    }
                }
            }
            $ene = false;
        }
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
