<?php

// Match names

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' , 'gbif-backbone');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 



$filename = dirname(__FILE__) . '/taxa.tsv';
$filename = dirname(__FILE__) . '/notmatched.txt';

$file_handle = fopen($filename, "r");


while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
	
	if ($line != '')
	{
	
		$parts = explode("\t", $line);
	
		//print_r($parts);
	
	
		$sql = 'SELECT * FROM taxon WHERE canonicalName="' . $parts[1] . '"';
		// AND taxonomicStatus IN ("accepted", "synonym")';
		$sql .= ' AND taxonRank="' . $parts[2] . '"';		
	
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

		$n = $result->NumRows();
	
		switch ($n)
		{
			case 0:
				echo "-- No match for " . $parts[1] . "\n";
				break;
			
			case 1:
				echo 'REPLACE INTO afdtogbif(TAXON_GUID, name, gbif) VALUES ("' . $parts[0] . '","' . $parts[1] . '","' . $result->fields['taxonID'] . '");' . "\n";
				break;
			
			default:
				echo "-- Multiple matches for " . $parts[1] . "\n";
				break;
	
		}
	}
	

}		

?>