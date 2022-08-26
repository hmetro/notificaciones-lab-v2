<?php

namespace App;

use SoapClient;
use SoapFault;
use App\Models\Ordenes;

class Requester
{

    //PROPERTIES

    public string $token = "NO_TOKEN";

    public array $soapConfig = array(
        'soap_version' => SOAP_1_1,
        'exceptions' => true,
        'trace' => 1,
        'cache_wsdl' => WSDL_CACHE_NONE,
    );

    public string $soapsDir = "NO_DIR";

    public bool $onRetry = false;

    public ?SoapClient $client;

    //CONSTRUCTOR

    public function __construct(){
        $this->soapsDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SoapEndPoints' . DIRECTORY_SEPARATOR;
        $this->client = new SoapClient($this->soapsDir . 'zdk.ws.wSessions.wsdl.xml');
    }
    
    //FUNCTIONS

    public function fetchPDF(Ordenes $orden){
        try {
            $this->login();

            $reportClient = new SoapClient($this->soapsDir . 'wso.ws.wReports.wsdl.xml', $this->soapConfig);

            $preview = $reportClient->Preview(array(
                "pstrSessionKey" => $this->token,
                // "pstrSampleID" => "0018847841",
                // "pstrRegisterDate" => "2022-08-17",
                "pstrSampleID" => $orden->sc,
                "pstrRegisterDate" => $orden->fechaExamen,
                "pstrFormatDescription" => 'METROPOLITANO',
                "pstrPrintTarget" => 'Destino por defecto',
            ));

            $this->logout();

            if (isset($preview->PreviewResult) or $preview->PreviewResult == '0') {

                if ($preview->PreviewResult == '0') {
                    return array('success' => false, 'message' => "No existe el documento solicitado");
                } else {
                    return array(
                        'success' => true,
                        'data' => $preview->PreviewResult,
                    );
                }

            }

            return array('success' => false, 'message' => "No existe el documento solicitado");

        } catch (\Throwable $th) {
            dd($th);
            return array('success' => false, 'message' => $th->getMessage());
        }
    }

    public function fetchOrdenes(){
        try {

            //Login para Token
            $this->login();

            //Nuevo cliente wOrders y traigo resultados
            $ordenesClient = new SoapClient($this->soapsDir . 'wso.ws.wOrders.xml', $this->soapConfig);
            $list = $ordenesClient->GetList(array(
                'pstrSessionKey' => $this->token,
                // 'pstrRegisterDateFrom' => date('Y-m-d',strtotime("-1 days")),
                // 'pstrRegisterDateTo' => date('Y-m-d',strtotime("-1 days")),
                'pstrRegisterDateFrom' => date('Y-m-d'),
                'pstrRegisterDateTo' => date('Y-m-d'),
            ));
            
            //Logout para Token
            $this->logout();

            //Formateo JSON
            $xml = simplexml_load_string($list->GetListResult->any);
            $json = json_encode($xml);
            $array = json_decode($json, true);

            //Retorno datos
            if(isset($array["DefaultDataSet"]["SQL"])){
                return array('success' => true, 'data' => $array["DefaultDataSet"]["SQL"]);
            }else{
                return array('success' => true, 'data' => []);
            }

        } catch (\Throwable $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    public function getOrderResults(Ordenes $orden, $tryOtherAuth = false, $reRes = -1){
        try {

            //Login para Token
            if($tryOtherAuth){
                $this->login("CWMETRO", "CWM3TR0");
                $this->onRetry = true;
            }else{
                $this->login();
            }

            //Nuevo cliente wOrders y traigo resultados
            $resultsClient = new SoapClient($this->soapsDir . 'wso.ws.wResults.xml', $this->soapConfig);
            $query = array(
                'pstrSessionKey' => $this->token,
                'pstrSampleID' => $orden->sc,
                'pstrRegisterDate' => $orden->fechaExamen,
            );

            $results = $reRes == -1 || $reRes == 0 ? $resultsClient->GetResults($query) : null;
            $microResults = $reRes == -1 || $reRes == 1 ? $resultsClient->GetMicroResults($query) : null;

            //Logout para Token
            $this->logout();

            if(!isset($results->GetResultsResult) && !isset($microResults->GetMicroResultsResult)){
                return array('success' => false, 'message' => "Sin resultados");
            }else{
                $return = array('success' => true, 'data' => [
                    'results' => isset($results->GetResultsResult) ? $results->GetResultsResult->Orders->LISOrder->LabTests->LISLabTest : false,
                    'microResults' => isset($microResults->GetMicroResultsResult) ? $microResults->GetMicroResultsResult->Orders->LISOrder->MicSpecs->LISMicSpec : false,
                ]);

                //Retorno datos
                return $return;
            }
            
        } catch (\Throwable $e) {
            if($e->faultstring == "SOAP-ERROR: Encoding: Violation of encoding rules"){
                return $this->onRetry == true ? array('success' => false, 'message' => "Sin resultados") : $this->getOrderResults($orden, true);
            }else{
                dd($e);
                return array('success' => false, 'message' => $e->getMessage());
            }
        }
    }

    public function login($user = "CONSULTA", $pass = "CONSULTA1"){
        try {

            // Login
            $Login = $this->client->Login(array(
                "pstrUserName" => $user,
                "pstrPassword" => $pass,
            ));

            // Guardo Token
            $this->token = $Login->LoginResult;

        } catch (SoapFault $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    public function logout(){
        try {

            //Logout
            $this->client->Logout(array(
                "pstrSessionKey" => $this->token,
            ));

            //Regreso a valor inicial en Token
            $this->token = "NO_TOKEN";

        } catch (SoapFault $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
}
