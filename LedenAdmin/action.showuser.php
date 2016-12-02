<?php

if (!isset($gCms)) exit;

if (!empty($params['userid'])) {
	$userid = $params['userid'];
	
	$connection = new eboekhouden_connection("johanneszcflevo", "23bb4b6b8962c7916aeab057a17f58f5","A4806969-DA8F-484A-83F4-14076589A3F3");
	$lid = $connection->getUserData($userid);
	$connection->closeSession();
}

// Display the populated template
$smarty->assign('lid',$lid);
echo $this->ProcessTemplate('showuser.tpl');

?>