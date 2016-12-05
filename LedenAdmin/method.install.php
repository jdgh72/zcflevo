<?php
if (!isset($gCms)) exit;


$db =& $gCms->GetDb();

// mysql-specific, but ignored by other database
$taboptarray = array( 'mysql' => 'TYPE=MyISAM' );
$dict = NewDataDictionary( $db );

// table schema description
$flds = 'id C(32) KEY,
		url C(80),
		username C(80),
		security_code1 C(80),
		security_code2 C(80),
		';
			
// create it. This should do error checking, but I'm lazy.
$sqlarray = $dict->CreateTableSQL( cms_db_prefix().'module_ledenadmin_soapproperties',
				   $flds, 
				   $taboptarray);
$dict->ExecuteSQLArray($sqlarray);


?>
