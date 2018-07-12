<?php

// Fix author name encoding issues

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' , 'afd');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

			
// One of the Köhler names had a \x03 embedded in the string
$sql = 'SELECT  * FROM bibliography WHERE PUB_AUTHOR LIKE("Kö%hler%")';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{		
	$pub_author = $result->fields['PUB_AUTHOR'];
	
	$pub_guid = $result->fields['PUBLICATION_GUID'];
	
	echo "$pub_author $pub_guid\n";
	
	if (preg_match('/\x03/', $pub_author))
	{
		echo 'xxxxx' . "\n";
		
		
	}
	
	
	//$sql = 'UPDATE bibliography SET PUB_AUTHOR="' . $pub_author . '" WHERE PUBLICATION_GUID="' . $pub_guid . '";' ;
	
	//echo $sql . "\n";

	$result->MoveNext();

}


?>
