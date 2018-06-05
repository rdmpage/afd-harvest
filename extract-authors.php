<?php

// Extract authors from references

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');



//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' , 'afd');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$page = 100;

$result = $db->Execute('SET max_heap_table_size = 1024 * 1024 * 1024');
$result = $db->Execute('SET tmp_table_size = 1024 * 1024 * 1024');


$offset = 0;
$done = false;

while (!$done)
{
	$sql = 'SELECT * FROM `bibliography` ';
	
	//$sql .= ' WHERE PUBLICATION_GUID="2f9f095c-5ae1-450f-97b5-ddf120ad501d"';
	$sql .= ' WHERE PUBLICATION_GUID="3023f70f-f6c8-4eb1-a8bf-a576393963bc"';
	
	$sql .= ' LIMIT ' . $page . ' OFFSET ' . $offset;

	//echo $sql . "\n";

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF && ($result->NumRows() > 0)) 
	{			
		if ($result->fields['PUB_AUTHOR'] != '')
		{
			$notes[] = $result->fields['PUB_AUTHOR'];
		
			$authorstring = $result->fields['PUB_AUTHOR'];
			
			echo "-- $authorstring\n";
			
			$matched = false;
			$authors = array();
			
			// More complex
			if (!$matched)
			{
			
			 	// ignore Jr (it's a hassle)
				$authorstring = preg_replace('/\.\s+Jr\.?/', '.', $authorstring);
				
				// remove et al.
				$authorstring = preg_replace('/,?\s+et al./', '', $authorstring);
				
				// trim authors before ' in ' because we want authors of work, not name
				$authorstring = preg_replace('/^(.*)\s+in\s+/', '', $authorstring);		
		
		
				$authorstring = preg_replace('/\.\s*,\s+([^&])/', '.|$1', $authorstring);
				
				$authorstring = preg_replace('/,? & /', '|', $authorstring);
				$authorstring = preg_replace('/([A-Z])\.([A-Z])/', '$1. $2', $authorstring);
				$authorstring = preg_replace('/([A-Z])\.([A-Z])/', '$1. $2', $authorstring);
				
				
		
		
				echo $authorstring . "\n";
			
				$authors = explode("|", $authorstring);	
			}
			
			//print_r($authors);		
			
			foreach ($authors as $author)
			{
				$keys = array();
				$values = array();
				
				$keys[] = 'PUBLICATION_GUID';
				$values[] = '"' . $result->fields['PUBLICATION_GUID'] . '"';
								
				$parts = preg_split('/,\s*/u', $author);
				if (count($parts) == 2)
				{
					//print_r($parts);
					
					//echo $parts[1] . ' ' . $parts[0] . "\n";
					
					$keys[] = 'name';
					$values[] = '"' . addcslashes($parts[1] . ' ' . $parts[0], '"') . '"';
					
					$keys[] = 'givenName';
					$values[] = '"' . addcslashes($parts[1], '"') . '"';

					$keys[] = 'familyName';
					$values[] = '"' . addcslashes($parts[0], '"') . '"';
					
				}
				else
				{
					$keys[] = 'name';
					$values[] = '"' . addcslashes($author, '"') . '"';		
					
					// try alternative parsing	
					if (preg_match('/^(?<familyName>[A-Z]\w+)\s+(?<givenName>[A-Z]\.(\s+[A-Z]\.)*)$/u', $author, $m))
					{
						$keys[] = 'givenName';
						$values[] = '"' . addcslashes($m['givenName'], '"') . '"';

						$keys[] = 'familyName';
						$values[] = '"' . addcslashes($m['familyName'], '"') . '"';

					}
					
				}
				
				//print_r($keys);
				//print_r($values);
				
				echo 'INSERT INTO authors(' . join(',', $keys) . ') VALUES (' . join(',', $values) . ');' . "\n";
			
			}
		}

	
	
		$result->MoveNext();
	}

	if ($result->NumRows() < $page)
	{
		$done = true;
	}
	else
	{
		$offset += $page;
	
		//if ($offset > 200) { $done = true; }
	}
}	

?>