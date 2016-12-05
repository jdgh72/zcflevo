<?php
require_once(__DIR__.'/eboekhouden.php');

class LedenAdmin extends CMSModule
{
	function GetName() { return 'LedenAdmin';}
	function GetVersion() {return '0.2';}
	function HasAdmin() {return false; }
	function IsPluginModule() { return true;}

	function SetParameters() 
	{
		$this->RegisterModulePlugin();
		$this->RestrictUnknownParams();
		
		$this->CreateParameter('userid','','UserId of FrontEndUser');
		$this->SetParameterType('userid', CLEAN_NONE);
	}	
	
	
}

?>
