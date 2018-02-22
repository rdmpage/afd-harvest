<?php

$stack = array();


//----------------------------------------------------------------------------------------
function fetch_info($key)
{
	$children = array();
	
	$url = "https://biodiversity.org.au/afd/taxa/$key/checklist-subtaxa.json";

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
		
		foreach ($obj as $item)
		{
			$has_children = false;
			if (isset($item->children))
			{
				if (count($item->children)) 
				{
					$has_children = true;
				}
			}
			
			if ($has_children)
			{
				foreach ($item->children as $child)
				{
					$children[] = $child->metadata->nameKey;
				}
			}
			else
			{
				$children[] = $item->metadata->nameKey;
			}
			
		}
	}
	
	//print_r($children);
	
	return $children;
			
}	

//----------------------------------------------------------------------------------------
// https://stackoverflow.com/a/38130611
function fetch_csv($key)
{
	global $stack;
	
	$ok = false;

	$url = "https://biodiversity.org.au/afd/taxa/$key/names/csv/$key.csv";

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLINFO_CONTENT_TYPE => "application/csv",
	  CURLOPT_BINARYTRANSFER => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);

	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	if (preg_match('/DOCTYPE html PUBLIC/', $data))
	{
		echo "$key has too many records\n";
		
		$children = fetch_info($key);
		foreach ($children as $child)
		{
			$stack[] = $child;
		}
	}
	else
	{
		echo "$key.csv\n";
		$ok = true;
		file_put_contents($key . '.csv', $data);
	}
	
	return $ok;
}


//$list = fetch_info('ANTHOZOA;Anthozoa');
//$stack = fetch_info('CNIDARIA');

$stack = array(
// ANIMALIA
'ACANTHOCEPHALA',
'PORIFERA',
'TARDIGRADA',
'CNIDARIA',
'CTENOPHORA',
'DICYEMIDA',
'PLATYHELMINTHES',
'XENACOELOMORPHA',
'NEMERTEA',
'GASTROTRICHA',
'ROTIFERA',
'CHAETOGNATHA',
'KINORHYNCHA',
'GNATHOSTOMULIDA',
'LORICIFERA',
'NEMATODA',
'NEMATOMORPHA',
'BRYOZOA',
'BRACHIOPODA',
'MOLLUSCA',
'PHORONIDA',
'PRIAPULIDA',
'SIPUNCULA',
'ECHIURA',
'KAMPTOZOA',
'ANNELIDA',
'ONYCHOPHORA',
'ARTHROPODA',
'ECHINODERMATA',
'HEMICHORDATA',
'CHORDATA',
// PROTISTA
'OPISTHOKONTA',
'HAPLOSPORIDIA',
'SARCOMASTIGOPHORA',
'PARAMYXEA',
'DINOFLAGELLATA',
'EUGLENOZOA',
'PARABASALIA',
'PREAXOSTYLA',
'FORNICATA',
'MICROSPORIDIA',
'APICOMPLEXA',
'AMOEBOZOA',
'RETARIA',
'HETEROLOBOSEA',
'CERCOZOA',
'CILIOPHORA'
);

while (count($stack) > 0)
{
	$node = array_pop($stack);

	echo "Fetching node $node...\n";
	
	fetch_csv($node);
}

?>