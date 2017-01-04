<?php
if (!isset($gCms)) exit;

$feusers =& $this->GetModuleInstance('FrontEndUsers');
$cmsmailer =& $this->GetModuleInstance('CMSMailer');
$db = &$gCms->GetDb();

$boekhoudConnection = openConnection($db);

$userlist = $feusers->GetUsersInGroup('Leden');
foreach ($userlist as $FeuUser) {
	$username = $FeuUser['username'];
	
	$lid = $boekhoudConnection->getUserData($username);

	if (!empty($lid['naam'])) {
		syncAdmin($FeuUser['id'], $lid, $feusers, $cmsmailer); 
	} 
}

closeConnection($boekhoudConnection);


function syncAdmin($feuId, $lid, $feusers, $cmsmailer) {
	$userProp = $feusers->GetUserProperties($feuId);
	$verschillen = array();
	
	if (getUserProperty($userProp, "address1") != $lid['adres']) {
		$feuData = getUserProperty($userProp, "address1");
		$feusers->setUserPropertyFull("address1",$lid['adres'],$feuId); 
		$verschillen[] = "Verschil in Adres, wordt aangepast van $feuData naar ".$lid['adres'];
	}

	if (getUserProperty($userProp, "address2") != "") {
		$feuData = getUserProperty($userProp, "address2");
		$feusers->setUserPropertyFull("address2","",$feuId);
		$verschillen[] = "Adresregel 2 komt te vervallen";
	}

	if (getUserProperty($userProp, "zipcode") != $lid['postcode']) {
		$feuData = getUserProperty($userProp, "zipcode");
		$feusers->setUserPropertyFull("zipcode",$lid['postcode'],$feuId);
		$verschillen[] = "Verschil in Postcode, wordt aangepast van $feuData naar ".$lid['postcode'];
	}
	
	if (getUserProperty($userProp, "residence") != $lid['woonplaats']) {
		$feuData = getUserProperty($userProp, "residence");
		$feusers->setUserPropertyFull("residence",$lid['woonplaats'],$feuId);
		$verschillen[] = "Verschil in Woonplaats, wordt aangepast van $feuData naar ".$lid['woonplaats'];
	}

	if (getUserProperty($userProp, "telhome") != $lid['telefoon']) {
		$feuData = getUserProperty($userProp, "telhome");
		$feusers->setUserPropertyFull("telhome",$lid['telefoon'],$feuId);
		$verschillen[] = "Verschil in Telefoonnr, wordt aangepast van $feuData naar ".$lid['telefoon'];
	}

	if (getUserProperty($userProp, "telmobile") != $lid['mobiel']) {
		$feuData = getUserProperty($userProp, "telmobile");
		$feusers->setUserPropertyFull("telmobile",$lid['mobiel'],$feuId);
		$verschillen[] = "Verschil in Mobielnr, wordt aangepast van $feuData naar ".$lid['mobiel'];
	}

	if (getUserProperty($userProp, "email") != $lid['email']) {
		$feuData = getUserProperty($userProp, "email");
		$feuEmail = $feuData;
		$feusers->setUserPropertyFull("email",$lid['email'],$feuId);
		$verschillen[] = "Verschil in email, wordt aangepast van $feuData naar ".$lid['email'];
	}
	
	if (!empty($verschillen)) {
		
		$mailContent = "Beste " . $lid['naam']. ", \n\n";

		$mailContent .= "Bij het synchroniseren van ledenadministratie zijn verschillen geconstateerd, "; 
        $mailContent .= "het betreft de volgende verschillen: \n\n";
		foreach ($verschillen as $verschil) {
			$mailContent .= $verschil . "\n";
		}
		$mailContent .= "\nJe kan je gegevens controleren op de ledenpagina van zcflevo.nl.";
		$mailContent .= "Mochten er dingen niet kloppen geef dit dan door aan de secretaris.";

		$cmsmailer->reset();
        
        /*
		if (filter_var($lid['email'], FILTER_VALIDATE_EMAIL)) {
			$cmsmailer->AddAddress($lid['email'], $lid['naam']);
		} else {
			$cmsmailer->AddAddress('webmaster@zcflevo.nl');
		}
		
		if (isset($feuEmail)) {
			if (filter_var($feuEmail, FILTER_VALIDATE_EMAIL)) {
				$cmsmailer->AddCC($feuEmail);
			}
		} */
        $cmsmailer->AddAddress("johannes.hulshof@xs4all.nl", "Johannes XS4all");
        $cmsmailer->AddAddress("webmaster@zcflevo.nl","Webmaster ZCFlevo");
		// $cmsmailer->AddBCC('webmaster@zcflevo.nl');
        
		$cmsmailer->AddReplyTo('secretaris@zcflevo.nl');
		$cmsmailer->SetSubject('Wijzigingen in administratie');
		$cmsmailer->SetBody($mailContent);
		$cmsmailer->IsHTML(false);
		$cmsmailer->Send();
    }
}

function getUserProperty($userProp, $title) {
	foreach( $userProp as $oneprop )
	{
		if( $oneprop['title'] == $title )
		{
			return $oneprop['data'];
		}
	}
	return "";
}


function openConnection($db) {
	$query = 'SELECT id, url, username, security_code1, security_code2
            FROM '.cms_db_prefix().'module_ledenadmin_soapproperties
           WHERE id = "eboekhouden"';
	
	$result = $db->Execute($query) or die('FATAL SQL ERROR: '.$db->ErrorMsg().'<br/>QUERY: '.$db->sql);
	$row=$result->FetchRow();
	
	$connection = new eboekhouden_connection($row['url'], $row['username'], $row['security_code1'], $row['security_code2']);
        return $connection;
}

function closeConnection($connection) {
	$connection->closeSession();
}

?>
