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
//"Journal of the Malacological Society of Australia"
//"Molluscan Research"
//"Zoosystematics and Evolution"
//"Malacologia"
//"Conservation Genetics"
//'Austral Entomology'
//'Fragmenta Entomologica'
//'Aquatic Insects'
//'Entomologica Scandinavica'
//'Biology Letters'
//'Systematic Biology'
//'Ecological Entomology'
//'Florida Entomologist'
//'Environmental Biology of Fishes'
//'Journal of Crustacean Biology'
//'Subterranean Biology'
//'The Coleopterists Bulletin'
//'The Canadian Entomologist'
//'Pacific Science'
//'Journal of Fish Biology'
//'Journal of Arachnology'
//'Molecular Ecology'
//'Journal of Zoology, London'
//'Psyche (Cambridge)'
//'Tropical Zoology'
//'Transactions of the Royal Society of Tropical Medicine and Hygiene',
//'Annals of Tropical Medicine and Parasitology',
//'American Journal of Tropical Medicine and Hygiene'
//'Studies on Neotropical Fauna and Environment'
//'Journal of Medical Entomology',
//'Journal of the American Veterinary Medical Association',
//'Medical Journal of Australia',
//'Australian Journal for Experimental Biology and Medical Science',
//'British Medical Journal'
//'Science'
//'International Journal of Speleology'
//'Insect Systematics and Evolution',
//'Proceedings of the Zoological Society of London',
//'Mitteilungen aus dem Zoologischen Museum in Berlin'

//'Memoirs of the Queensland Museum - Nature'
//'Deep-Sea Research'
//'Nematologica'
//'Systematics and Biodiversity'
//'Memoirs of the Entomological Society of Canada'
//'Marine Mammal Science'
//'Marine Ecology Progress Series'
//'Conservation Biology'
//'Journal of Morphology'
//'bmc genomics'
//'Marine Genomics'
//'Journal of Phycology',
//'International Journal of Legal Medicine',
//'Journal of Molecular Evolution',
//'Molecular and Biochemical Parasitology',
//'Auk',
//'Science (Washington, D.C.)',
//'Biological Journal of the Linnean Society of London',
//'Genetical Research',
//'Molecular Biology and Evolution',
//'New Zealand Journal of Marine and Freshwater Research',
//'Experimental Parasitology'
//'Journal of Molecular Biology and Evolution',
//'Mitochondrial DNA Part A',
//'Proceedings of the National Academy of Sciences of the United States of America',
//'Proceedings of the Royal Society of London (B)',
//'BMC Evolutionary Biology',
//'Genome'
//'Genome Research'
//'Chromosoma (Berlin)',
//'New Zealand Journal of Science'
/*'Biodiversity and Conservation',
'Advances in Parasitology',
'Biological Invasions',
'Organisms, Diversity and Evolution',
'Agricultural and Forest Entomology',
'International Journal for Parasitology: Parasites and Wildlife'*/
//'Veterinary Parasitology'
//'Polskie Pismo Entomologiczne'
//'Oriental Insects',
//'Journal of Parasitology'
//'Bionomina'
//'Journal of the Royal Society of New Zealand'
//'Acta Parasitologica',
//'Parasitology International',
//'Marine Parasitology',
//'Annales de Parasitologie Humaine et Comparée'
/*'Journal of Zoological Systematics and Evolutionary Research',
'Cladistics',
'Marine Biology, Berlin',
'Polar Biology',
'Nature (London)',
'Journal of Animal Ecology',
'Australian Journal of Marine and Freshwater Research',
'Journal of Anatomy'*/
//'Journal of the Linnean Society of London, Zoology'
//'Acta Arachnologica'
//'American Museum Novitates'
//'Gene'
//'Systematic and Applied Acarology'
//'Journal of Biogeography',
//'General and Applied Entomology',
//'Folia Parasitologica',
//'Entomologica Americana',
//'Bulletin of the American Museum of Natural History',
//'Annales Zoologici, Warszawa'
//'Journal of Eukaryotic Microbiology',
//'Protist',
//'Behaviour'
//'Publications of the Seto Marine Biological Laboratory'
//'Peckhamia'
//'Australian Journal of Ecology'
'Journal of Experimental Marine Biology and Ecology'
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

		$sql .= ' WHERE PUB_PARENT_JOURNAL_TITLE = "' . $journal . '"';

		//$sql .= ' WHERE PUB_PARENT_JOURNAL_TITLE LIKE "' . $journal . '%"';
	
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
				
					if (preg_match('/<em>' . $reference->journal . '<\/em><\/a> <strong>(No. )?(?<volume>\d+(\.\d)?)<\/strong>(\((?<issue>.*)\))?:/', $result->fields['PUB_FORMATTED'] , $m))
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
				if (preg_match('/^(pp.\s+)?(?<spage>\d+)\s*-\s*(?<epage>\d+)\.?$/', $result->fields['PUB_PAGES'], $m))
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