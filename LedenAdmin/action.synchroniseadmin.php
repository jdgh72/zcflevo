<?php
if (!isset($gCms)) exit;

$feusers =& $this->GetModuleInstance('FrontEndUsers');
$cmsmailer =& $this->GetModuleInstance('CMSMailer');
$db = &$gCms->GetDb();

$boekhoudConnection = openConnection($db);
$ledenadminsync = new synchroniseledenadmin($feusers, $cmsmailer);

$userlist = $feusers->GetUsersInGroup('Leden');
foreach ($userlist as $FeuUser) {
	$username = $FeuUser['username'];
	
	$lid = $boekhoudConnection->getUserData($username);

	if (!empty($lid['naam'])) {
		$ledenadminsync->syncAdres($FeuUser['id'], $lid);
		$ledenadminsync->syncStatus($FeuUser['id'], $lid); 
	}
}

closeConnection($boekhoudConnection);





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
