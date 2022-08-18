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

    public ?SoapClient $client;

    //CONSTRUCTOR

    public function __construct(){
        $this->soapsDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SoapEndPoints' . DIRECTORY_SEPARATOR;
        $this->client = new SoapClient($this->soapsDir . 'zdk.ws.wSessions.wsdl.xml');
    }
    
    //FUNCTIONS

    public function fetchOrdenes(){
        try {

            //Login para Token
            $this->login();

            //Nuevo cliente wOrders y traigo resultados
            $ordenesClient = new SoapClient($this->soapsDir . 'wso.ws.wOrders.xml', $this->soapConfig);
            $list = $ordenesClient->GetList(array(
                'pstrSessionKey' => $this->token,
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
            return array('success' => true, 'data' => $array["DefaultDataSet"]["SQL"]);

        } catch (\Throwable $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    public function getOrderResults(Ordenes $orden){
        try {

            //Login para Token
            $this->login();

            //Nuevo cliente wOrders y traigo resultados
            $resultsClient = new SoapClient($this->soapsDir . 'wso.ws.wResults.xml', $this->soapConfig);
            $query = array(
                'pstrSessionKey' => $this->token,
                'pstrSampleID' => $orden->sc,
                'pstrRegisterDate' => $orden->fechaExamen,
            );

            $results = $resultsClient->GetResults($query);
            $microResults = $resultsClient->GetMicroResults($query);

            dd($results, $microResults);

            //Logout para Token
            $this->logout();

            if(!isset($results->GetResultsResult) && !isset($microResults->GetMicroResultsResult)){
                return array('success' => false, 'message' => "Sin resultados");
            }else{
                dd($results);

                //Formateo JSON
                $xml = simplexml_load_string($results->GetResultsResult->any);
                $json = json_encode($xml);
                $array = json_decode($json, true);

                //Retorno datos
                return array('success' => true, 'data' => $array["DefaultDataSet"]["SQL"]);
            }
            
        } catch (\Throwable $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    public function login(){
        try {

            // Login
            $Login = $this->client->Login(array(
                "pstrUserName" => "CONSULTA",
                "pstrPassword" => "CONSULTA1",
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
