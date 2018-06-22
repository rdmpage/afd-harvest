<?php

// Fetch Zenodo record

require_once(dirname(__FILE__) . '/php-json-ld/jsonld.php');


$stack = array();

//----------------------------------------------------------------------------------------
function fetch_zenodo_json($id, &$jsonld)
{	
	global $stack;

	$url = "https://zenodo.org/api/records/" . $id;

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
		
		// image URL
		if (isset($obj->files[0]->links->self))
		{
			$jsonld->contentUrl = $obj->files[0]->links->self;
		}
		
		// image thumbnail
		if (isset($obj->links->thumb250))
		{
			$jsonld->thumbnailUrl = $obj->links->thumb250;
		}
		
		// parts
		if (isset($obj->metadata->related_identifiers))
		{
			foreach ($obj->metadata->related_identifiers as $related)
			{
				switch ($related->relation)
				{
					// [identifier] => http://zenodo.org/record/252172
					case 'hasPart':
						if (preg_match('/http:\/\/zenodo.org\/record\/(?<id>\d+)/', $related->identifier, $m))
						{
							$stack[] = $m['id'];
						}
						break;
						
					// already done in JSON-LD
					/*
					case 'cites':
						if (!isset($jsonld->cites))
						{
							$jsonld->cites = array();
						}
						
						if ($related->scheme == 'doi')
						{						
							$cited = new stdclass;
							$cited->{'@id'} = 'https://doi.org/' . $related->identifier;
						
							$jsonld->cites[] = $cited;
						}
						break;*/
				
					default:
						break;
				}
			}
			
		
		}
		
	}
}

//----------------------------------------------------------------------------------------
// Call API asking for JSON-LD which we convert to triples 
// Note that we make a second call to get the details of the image itself (sigh)
function fetch_zenodo($id)
{
	global $stack;
	
	$url = "https://zenodo.org/api/records/" . $id;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  CURLOPT_HTTPHEADER => array("Accept: application/ld+json")
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	if ($data != '')
	{
		//$obj = json_decode($data);
		//print_r($obj);
		
		//echo $data;
		
		// triples
		$jsonld = json_decode($data);
		
		// second call 
		fetch_zenodo_json($id, $jsonld);
				
				
		//print_r($jsonld);		
					 
		$triples = jsonld_to_rdf($jsonld, array('format' => 'application/nquads'));		
		
		echo $triples;
	}
}


$stack = array(252165); // work

$stack = array(251164,579281);

while (count($stack) > 0)
{
	$id = array_pop($stack);
	
	//echo "-- Fetching node $id...\n";
	
	fetch_zenodo($id);	
}

?>