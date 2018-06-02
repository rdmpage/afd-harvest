<?php

// Dump references as RIS

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');


//----------------------------------------------------------------------------------------
function openurl($reference)
{
	$doi = '';
	
	if (isset($reference->title)
		&& isset($reference->journal)
		&& isset($reference->volume)
		&& isset($reference->spage)
	)
	{
		$openurl = '';
		if (isset($reference->authors))
		{
			foreach ($reference->authors as $author)
			{
				$openurl .= '&au=' . urlencode($author);
			}	
		}
		$openurl .= '&atitle=' . urlencode($reference->title);
		$openurl .= '&title=' . urlencode($reference->journal);
		
		if (isset($reference->issn))
		{
			$openurl .= '&issn=' . $reference->issn;
		}
		
		if (isset($reference->series))
		{
			$openurl .= '&series=' . $reference->series;
		}
		
		if (isset($reference->volume))
		{
			$openurl .= '&volume=' . $reference->volume;
		}
	
		if (isset($reference->issue))
		{
			$openurl .= '&issue=' . $reference->issue;
		}		
	
		if (isset($reference->spage))
		{
			$openurl .= '&spage=' . $reference->spage;
		}
		
		if (isset($reference->epage))
		{
			$openurl .= '&epage=' . $reference->epage;
		}
		
		$openurl .= '&date=' . $reference->year;
		
		$url = 'http://www.crossref.org/openurl?' . $openurl . '&pid=rdmpage@gmail.com&redirect=false';
		
		//echo $url . "\n";
		
		$opts = array(
		  CURLOPT_URL =>$url,
		  CURLOPT_FOLLOWLOCATION => TRUE,
		  CURLOPT_RETURNTRANSFER => TRUE
		);
	
		$ch = curl_init();
		curl_setopt_array($ch, $opts);
		$xml = curl_exec($ch);
		$info = curl_getinfo($ch); 
		curl_close($ch);
				
		//echo $xml;
		
		if ($xml != '')
		{
			$xml = str_replace(' version="2.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.crossref.org/qrschema/2.0 http://www.crossref.org/schema/queryResultSchema/crossref_query_output2.0.xsd"', '', $xml);
			$xml = str_replace('xmlns="http://www.crossref.org/qrschema/2.0"', '', $xml);
			
			//echo $xml;
		
			$dom= new DOMDocument;
			$dom->loadXML($xml);
			$xpath = new DOMXPath($dom);
			$xpath_query = '//doi[@type="journal_article"]';
			$nodeCollection = $xpath->query ($xpath_query);
		
			foreach($nodeCollection as $node)
			{
				$doi = $node->firstChild->nodeValue;
			}
			
			//print_r($reference);
		
		}
	}

	return $doi;	
}

//----------------------------------------------------------------------------------------
function find_doi($string)
{
	$doi = '';
	
	$url = 'https://mesquite-tongue.glitch.me/search?q=' . urlencode($string);
	
	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	if ($data != '')
	{
		$obj = json_decode($data);
		
		//print_r($obj);
		
		if (count($obj) == 1)
		{
			if ($obj[0]->match)
			{
				$doi = $obj[0]->id;
			}
		}
		
	}
	
	return $doi;
			
}	

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


$journals = array(
"Annals of the Entomological Society of America",
"Australian Journal of Marine and Freshwater Research",
"Canadian Entomologist",
"Canadian Journal of Zoology",
"Copeia",
"Hydrobiologia",
"International Journal for Parasitology",
"International Journal of Acarology",
"Journal of Parasitology",
"Molecular Phylogenetics and Evolution",
"New Zealand Journal of Zoology",
"Parasitology",
"Smithsonian Contributions to Zoology",
"Systematic Entomology",
"ZooKeys",
"Zoologica Scripta",
"Zoological Journal of the Linnean Society"
);


$journals = array(
"Euro"
);

$doi_lookup = true;
//$doi_lookup = false;


foreach ($journals as $journal)
{
	$offset = 0;
	$done = false;
	
	while (!$done)
	{
		$sql = 'SELECT * FROM `bibliography` ';

		//$sql .= ' WHERE PUB_PARENT_JOURNAL_TITLE = "' . $journal . '"';

		$sql .= ' WHERE PUB_PARENT_JOURNAL_TITLE LIKE "' . $journal . '%"';
	
		//$sql .= ' AND PUB_YEAR LIKE "20%"';
		
		//$sql .= ' WHERE PUB_PAGES IS NOT NULL';

		if ($doi_lookup)
		{
			$sql .= ' AND doi IS NULL';
		}
		else
		{
			$sql .= ' AND spage IS NULL';
		}
	
		$sql .= ' ORDER BY PUB_YEAR DESC';
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
				
				// Fudge journal names for DOI lookup if needed
				/*
				if ($reference->journal == 'Australian Wildlife Research')
				{
					$reference->journal == 'Wildlife Research';
				}
				*/
				
				/*
				if ($reference->journal == 'Proceedings of the Malacological Society of London')
				{
					$reference->journal == 'Journal of Molluscan Studies';
				}
				*/
				
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
				
					if (preg_match('/<em>' . $reference->journal . '<\/em><\/a>\s+\(?(?<series>\d+)\)?\s+<strong>(?<volume>\d+)<\/strong>(\((?<issue>.*)\))?:/', $result->fields['PUB_FORMATTED'] , $m))
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
			
				// </em></a> Suppl. <strong>4</strong>
				if (!$matched)
				{
					//echo $result->fields['PUB_FORMATTED'] . "\n";
				
					if (preg_match('/<em>' . $reference->journal . '<\/em><\/a>\s+\Suppl.\s+<strong>(?<volume>\d+)<\/strong>:/', $result->fields['PUB_FORMATTED'] , $m))
					{					
						$reference->volume = $m['volume'];
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
				if (preg_match('/^(pp.\s+)?(?<spage>\d+)\s*-\s*(?<epage>\d+)$/', $result->fields['PUB_PAGES'], $m))
				{
					$reference->spage = $m['spage'];
					$reference->epage = $m['epage'];
					$matched = true;
				}
		
			}
		
			// 241-251, pl. 5, figs. 1-4
			if (!$matched)
			{
				if (preg_match('/^(?<spage>\d+)-(?<epage>\d+)[,|;]?\s+/', $result->fields['PUB_PAGES'], $m))
				{
					$reference->spage = $m['spage'];
					$reference->epage = $m['epage'];
					$matched = true;
				}
		
			}
			
			if (!$matched)
			{
				if (preg_match('/^(?<spage>\d+)$/', $result->fields['PUB_PAGES'], $m))
				{
					$reference->spage = $m['spage'];
					$matched = true;
				}
		
			}

			if (!$matched)
			{
				if (preg_match('/^(?<spage>\d+),\s+/', $result->fields['PUB_PAGES'], $m))
				{
					$reference->spage = $m['spage'];
					$matched = true;
				}
		
			}

			if (!$matched)
			{
				if (preg_match('/^(?<spage>\d+)\s+pp./', $result->fields['PUB_PAGES'], $m))
				{
					$reference->spage = $m['spage'];
					$matched = true;
				}
		
			}
			
		
		
			$reference->notes = join(' ', $notes);
			
			//print_r($reference);
		
		
			// Optional lookup DOI
			if ($doi_lookup)
			{
		
				// Use search API, assumes CrossRef metadata has title, which can be false especially for CSIRO
				$doi = '';
				if (1)
				{
					$doi = find_doi($reference->notes);
					if ($doi != '')
					{
						$reference->doi = strtolower($doi);
					}
				}
		
				if ($doi == '')
				{
					$doi = openurl($reference);
					if ($doi != '')
					{
						$reference->doi = strtolower($doi);
					}			
				}
			
				if (0)
				{	
					print_r($reference);
				}
			}		
		
			// update
			if (1)
			{
				$keys = array('series', 'volume', 'issue', 'doi', 'spage', 'epage');
				$values = array();
		
				foreach ($keys as $k)
				{
					if (isset($reference->{$k}))
					{
						$values[] = $k . '=' . '"' . $reference->{$k} . '"';
					}
				}
		
				if (count($values) > 0)
				{
					echo  'UPDATE bibliography SET ' . join(', ', $values) . ' WHERE PUBLICATION_GUID="' . $reference->publisher_id . '";' . "\n";
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
}

?>