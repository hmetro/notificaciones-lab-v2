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

    
    public function getFileName(){
        return "sc_" . $this->sc . "_" . $this->fechaExamen . ".json";
    }

    public function toJson(){
        return json_encode(get_object_vars($this));
    }

    public static function checkFile($fromServer){
        return Storage::disk('local')->exists('1ordenes'. DIRECTORY_SEPARATOR . "sc_" . $fromServer["SampleID"] . "_" . $fromServer["RegisterDate"] . ".json");
    }
    
    public function __construct($fromServer){
        $this->numeroHistoriaClinica = $fromServer["PatientID1"];
        $this->nombresPaciente = $fromServer["FirstName"];
        $this->apellidosPaciente = $fromServer["LastName"];
        $this->sc = $fromServer["SampleID"];
        $this->fechaExamen = $fromServer["RegisterDate"];
        $this->horaExamen = $fromServer["RegisterHour"];
        $this->doctor = $fromServer["Doctor"] ?? "";
        $this->servicio = $fromServer["Service"];
        $this->origen = $fromServer["Origin"];
        $this->motivo = $fromServer["D_112"];
        $this->especialidad = $fromServer["D_117"] ?? "";
        $this->validacionClinica = -1;
        $this->validacionMicro = -1;
        $this->reglasFiltros = [];
        $this->dataEnvio = [];
        $this->logsEnvio = [];
        $this->dataClinica = [];
        $this->dataMicro = [];
    }
}
