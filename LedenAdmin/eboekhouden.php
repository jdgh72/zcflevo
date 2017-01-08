<?php

class eboekhouden_connection {
	private $security_code_1;
	private $security_code_2;
	private $username;
	private $sessionID;
	private $client;
	
	public function __construct($url,$uname, $sec1, $sec2) 
	{
		$this->username = $uname;
		$this->security_code_1 = $sec1;
		$this->security_code_2 = $sec2;
		$this->client = new SoapClient($url);
		$this->openSession();	
	}
	
	private function openSession()
	{
		$openSessionParam = array('Username'=>$this->username
		                         ,'SecurityCode1'=>$this->security_code_1
		                         ,'SecurityCode2'=>$this->security_code_2);
		$result = $this->client->OpenSession($openSessionParam);
		$this->sessionID = $result->OpenSessionResult->SessionID;
	}
	
	public function getUserData($userid) 
	{
		$relatieFilter = array("ID"=>0,"Code"=>$userid);
		$getRelatiesParam = array("SessionID"=>$this->sessionID
		                        ,"SecurityCode2"=>$this->security_code_2
		                        ,"cFilter"=>$relatieFilter);
		
		$result = $this->client->GetRelaties($getRelatiesParam);
		$relatie = $result->GetRelatiesResult->Relaties->cRelatie;
		
		$lid['naam'] = $relatie->Bedrijf;
		$lid['geslacht'] = $relatie->Geslacht;
		$lid['adres'] = $relatie->Adres;
		$lid['postcode'] = $relatie->Postcode;
		$lid['woonplaats'] = $relatie->Plaats;
		$lid['land'] = $relatie->Land;
		$lid['telefoon'] = $relatie->Telefoon;
		$lid['mobiel'] = $relatie->GSM;
		$lid['email'] = $relatie->Email;
		$lid['geboortedatum'] = $relatie->Def1;
		$lid['lid_sinds'] = $relatie->Def2;
        $lid['IBAN'] = $relatie->IBAN;
		 
		return $lid;
	}
	
	public function closeSession() 
	{
		$closeSessionParam = array('SessionID'=>$this->sessionID);
		$result = $this->client->CloseSession($closeSessionParam);
	}
}
