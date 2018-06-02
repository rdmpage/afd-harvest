<?php
require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' , 'afd');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


$db->EXECUTE("set names 'utf8'"); 


$page = 100;
$offset = 0;
$done = false;

while (!$done)
{
	$sql = 'SELECT * FROM afd WHERE NAME_TYPE="Valid name"';
	$sql .= ' LIMIT ' . $page . ' OFFSET ' . $offset;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF && ($result->NumRows() > 0)) 
	{		
		
	
	
		$name_parts = array();

		if ($result->fields['FAMILY'] != '')
		{
			if ($result->fields['RANK'] == 'Family')
			{
				$name_parts[] = $result->fields['FAMILY'];
			}				
		}			
			
		if ($result->fields['GENUS'] != '')
		{
			$name_parts[] = $result->fields['GENUS'];
		}						

		if ($result->fields['SUBGENUS'] != '')
		{
			$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#infragenericEpithet> ' . '"' . addcslashes($result->fields['SUBGENUS'], '"') . '" . ';
			$name_parts[] = '(' . $result->fields['SUBGENUS'] . ')';
		}			

		if ($result->fields['SPECIES'] != '')
		{
			$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#specificEpithet> ' . '"' . addcslashes($result->fields['SPECIES'], '"') . '" . ';
			$name_parts[] = $result->fields['SPECIES'];
		}

		if ($result->fields['SUBSPECIES'] != '')
		{
			$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#infraspecificEpithet> ' . '"' . addcslashes($result->fields['SUBGENUS'], '"') . '" . ';
			$name_parts[] = $result->fields['SUBSPECIES'];
		}			

		$nameComplete = join(' ', $name_parts);
		if ($nameComplete == '')
		{
			$nameComplete = $result->fields['NAMES_VARIOUS'];						
		}
	
		if (preg_match('/^[A-Z]+$/', $nameComplete))
		{
			$nameComplete = mb_convert_case($nameComplete, MB_CASE_TITLE);
		}
	
		if (0)
		{
		
			echo $result->fields['TAXON_GUID'];
			echo "\t";
			echo $nameComplete;
			echo "\n";
		}
		
		if (1)
		{
			if ($result->fields['RANK'] != '')
			{
				echo 'UPDATE afdtogbif SET rank="' . strtolower($result->fields['RANK']) . '" WHERE TAXON_GUID="' . $result->fields['TAXON_GUID'] . '";' . "\n";
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
		//if ($offset > 3000) { $done = true; }
	}
	
}
