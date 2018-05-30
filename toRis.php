<?php

// Dump references as RIS

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

$result = $db->Execute('SET max_heap_table_size = 1024 * 1024 * 1024');
$result = $db->Execute('SET tmp_table_size = 1024 * 1024 * 1024');

$done = false;

while (!$done)
{
	$sql = 'SELECT * FROM `bibliography` ';
	
	//$sql .= ' WHERE PUB_PARENT_JOURNAL_TITLE = "Memoirs of the Queensland Museum"';
	//$sql .= ' WHERE PUB_PARENT_JOURNAL_TITLE = "Bijdragen tot de Dierkunde"';
	$sql .= ' WHERE PUB_PARENT_JOURNAL_TITLE = "Annals And Magazine of Natural History"';
	
	$sql .= ' AND PUB_YEAR LIKE "196%"';
	
	$sql .= ' ORDER BY PUB_YEAR';
	$sql .= ' LIMIT ' . $page . ' OFFSET ' . $offset;
	
	//echo $sql . "\n";

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF && ($result->NumRows() > 0)) 
	{		
		$reference = new stdclass;
		
		$reference->publisher_id = $result->fields['PUBLICATION_GUID'];
		
		$notes = array();
		
		$type = $result->fields['PUB_TYPE'];
		switch ($type)
		{
			case 'Article in Journal':
				$reference->genre = 'article';				
				break;
				
			default:
				$reference->genre = 'generic';
				break;
		}
		
		if ($result->fields['PUB_AUTHOR'] != '')
		{
			$notes[] = $result->fields['PUB_AUTHOR'];
			
			$authorstring = $result->fields['PUB_AUTHOR'];
			
			$authorstring = preg_replace('/\.,\s+/', '.|', $authorstring);
			$authorstring = preg_replace('/ & /', '|', $authorstring);
			$authorstring = preg_replace('/([A-Z])\.([A-Z])/', '$1. $2', $authorstring);
			$authorstring = preg_replace('/([A-Z])\.([A-Z])/', '$1. $2', $authorstring);
			
			$reference->authors = explode("|", $authorstring);			
		}
		
		
		$reference->year = $result->fields['PUB_YEAR'];	
		
		$notes[] = $reference->year;
		
		$reference->title = strip_tags($result->fields['PUB_TITLE']);
		
		$notes[] = $reference->title;
		
		if ($reference->genre == 'article')
		{
			$reference->journal = $result->fields['PUB_PARENT_JOURNAL_TITLE'];
			$notes[] = $reference->journal;

			// volume
			$matched = false;
		
			if (!$matched)
			{
				//echo $result->fields['PUB_FORMATTED'] . "\n";
				
				if (preg_match('/<em>' . $reference->journal . '<\/em><\/a> <strong>(?<volume>\d+)<\/strong>(\((?<issue>.*)\))?:/', $result->fields['PUB_FORMATTED'] , $m))
				{
					$reference->volume = $m['volume'];
					
					if ($m['issue'] != '')
					{
						$reference->issue = $m['issue'];
					}
					$matched = true;
				}
			}
			
			// series info
			// </em></a> 13 <strong>9</strong>:
			if (!$matched)
			{
				//echo $result->fields['PUB_FORMATTED'] . "\n";
				
				if (preg_match('/<em>' . $reference->journal . '<\/em><\/a>\s+(?<series>\d+)\s+<strong>(?<volume>\d+)<\/strong>(\((?<issue>.*)\))?:/', $result->fields['PUB_FORMATTED'] , $m))
				{
					$reference->series = $m['series'];
					$reference->volume = $m['volume'];
					
					if ($m['issue'] != '')
					{
						$reference->issue = $m['issue'];
					}
					$matched = true;
				}
			}
			
		}	
		
		if (isset($reference->series))	
		{
			$notes[] = $reference->series;
		}
		if (isset($reference->volume))	
		{
			$notes[] = $reference->volume;
		}
		if (isset($reference->issue))	
		{
			$notes[] = $reference->issue;
		}
		
		// pages
		
		$notes[] = $result->fields['PUB_PAGES'];
		
		$matched = false;
		
		if (!$matched)
		{
			if (preg_match('/^(?<spage>\d+)-(?<epage>\d+)$/', $result->fields['PUB_PAGES'], $m))
			{
				$reference->spage = $m['spage'];
				$reference->epage = $m['epage'];
				$matched = true;
			}
		
		}
		
		// 241-251, pl. 5, figs. 1-4
		if (!$matched)
		{
			if (preg_match('/^(?<spage>\d+)-(?<epage>\d+),?\s+/', $result->fields['PUB_PAGES'], $m))
			{
				$reference->spage = $m['spage'];
				$reference->epage = $m['epage'];
				$matched = true;
			}
		
		}
		
		
		$reference->notes = join(' ', $notes);
		
		print_r($reference);

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

?>