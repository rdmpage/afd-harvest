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



$filename = dirname(__FILE__) . '/fix-encoding/fix.csv';

$file_handle = fopen($filename, "r");

$count = 0;

while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
	
	if ($line != '')
	{
	
		$parts = explode(",", $line);
		
		if ($count > 0)
		{
	
			//print_r($parts);
			
			$sql = 'SELECT * FROM bibliography WHERE PUB_AUTHOR LIKE("%' . $parts[0] . '%")';
			
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

			while (!$result->EOF) 
			{		
				$pub_author = $result->fields['PUB_AUTHOR'];
				$pub_guid = $result->fields['PUBLICATION_GUID'];
				
				$old_author = $parts[0];
				$old_author = str_replace('?', '\?', $old_author);
				
				$pattern = '/' . $old_author . '/u';
				
				$pub_author = preg_replace($pattern, $parts[1], $pub_author);
				
				//echo $parts[0] . ' ' . $parts[1] . ' ' . $pub_author . "\n";
				
				$sql = 'UPDATE bibliography SET PUB_AUTHOR="' . $pub_author . '" WHERE PUBLICATION_GUID="' . $pub_guid . '";' ;
				
				echo $sql . "\n";
			
				$result->MoveNext();
			
			}
		}
		
		
		$count++;
		
		//if ($count > 1) exit();
	

	}
	

}		

?>