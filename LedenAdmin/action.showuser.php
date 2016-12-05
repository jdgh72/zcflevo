<?php

if (!isset($gCms)) exit;
$db = &$gCms->GetDb();

if (!empty($params['userid'])) {
	$query = 'SELECT id, url, username, security_code1, security_code2
	            FROM '.cms_db_prefix().'module_ledenadmin_soapproperties
	           WHERE id = "eboekhouden"';
	$userid = $params['userid'];
	
	$result = $db->Execute($query) or die('FATAL SQL ERROR: '.$db->ErrorMsg().'<br/>QUERY: '.$db->sql);
	$row=$result->FetchRow();
	
	$connection = new eboekhouden_connection($row['url'], $row['username'], $row['security_code1'], $row['security_code2']);
	$lid = $connection->getUserData($userid);
	$connection->closeSession();
}

// Display the populated template
$smarty->assign('lid',$lid);
echo $this->ProcessTemplate('showuser.tpl');

?>