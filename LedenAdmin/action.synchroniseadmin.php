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
		syncAdmin($FeuUser['id'], $lid, $feusers); 
	} else {
		echo "Lid: $username; geen data in e-boekhouden<br />";
		echo "===================================== <br />";
        }
}

closeConnection($boekhoudConnection);


function syncAdmin($feuId, $lid, $feusers) {
	$userProp = $feusers->GetUserProperties($feuId);
	$verschillen = array();
	
	if (getUserProperty($userProp, "address1") != $lid['adres']) {
		$feuData = getUserProperty($userProp, "address1");
		$verschillen[] = "Verschil in Adres (site / administratie); $feuData / ".$lid['adres'];
	}

	if (getUserProperty($userProp, "zipcode") != $lid['postcode']) {
		$feuData = getUserProperty($userProp, "zipcode");
		$verschillen[] = "Verschil in Postcode (site / administratie); $feuData / ".$lid['postcode'];
	}
	
	if (getUserProperty($userProp, "residence") != $lid['woonplaats']) {
		$feuData = getUserProperty($userProp, "residence");
		$verschillen[] = "Verschil in Woonplaats (site / administratie); $feuData / ".$lid['woonplaats'];
	}

	if (getUserProperty($userProp, "telhome") != $lid['telefoon']) {
		$feuData = getUserProperty($userProp, "telhome");
		$verschillen[] = "Verschil in Telefoonnr (site / administratie); $feuData / ".$lid['telefoon'];
	}

	if (getUserProperty($userProp, "telmobile") != $lid['mobiel']) {
		$feuData = getUserProperty($userProp, "telmobile");
		$verschillen[] = "Verschil in Mobiel (site / administratie); $feuData / ".$lid['mobiel'];
	}

	if (getUserProperty($userProp, "email") != $lid['email']) {
		$feuData = getUserProperty($userProp, "email");
		$verschillen[] = "Verschil in email (site / administratie); $feuData / ".$lid['email'];
	}
	
	if (!empty($verschillen)) {
		echo "Verschillen geconstateerd voor user" . $lid['naam']. "<br />";
		foreach ($verschillen as $verschil)
		{
			echo "$verschil <br />";
		}
		echo "===================================== <br />";
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
