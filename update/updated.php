<?php

// Get updated AFD taxon GUID



//----------------------------------------------------------------------------------------
function get_redirect($url)
{	
	$redirect = '';
	
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,  0); 
	curl_setopt ($ch, CURLOPT_HEADER,		  1);  
	
	// timeout (seconds)
	curl_setopt ($ch, CURLOPT_TIMEOUT, 240);

			
	$curl_result = curl_exec ($ch); 
	
	if (curl_errno ($ch) != 0 )
	{
		echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
	}
	else
	{
		$info = curl_getinfo($ch);
		
		//print_r($info);		
		 
		$header = substr($curl_result, 0, $info['header_size']);
				
		$http_code = $info['http_code'];
		
		if ($http_code == 303)
		{
			$redirect = $info['redirect_url'];
		}
		
		if ($http_code == 302)
		{
			$redirect = $info['redirect_url'];
		}
		
	}
	
	$redirect = preg_replace('/;jsessionid=.*$/', '', $redirect);
	
	
	return $redirect;
}


//----------------------------------------------------------------------------------------
function get($url)
{
	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLINFO_CONTENT_TYPE => "application/csv",
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);

	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}


//----------------------------------------------------------------------------------------
// https://stackoverflow.com/a/38130611
function fetch_csv($key)
{
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

	echo "$key.csv\n";
	file_put_contents(dirname(__FILE__) . '/csv/' . $key . '.csv', $data);
}

//----------------------------------------------------------------------------------------

// Record taxa we need to delete
$sql_filename = 'delete.sql';
$handle = fopen($sql_filename, 'a');

// Taxa to potentially update
$guids = array(
'cbf10ca7-c527-4f12-b86e-93344cf091f3',
'e5b1bf67-462c-418b-be44-faddfa84e2b0'
);

foreach ($guids as $guid)
{
	$url = 'https://biodiversity.org.au/afd/taxa/' . $guid;
	
	$sql = 'DELETE FROM afd WHERE TAXON_GUID="' . $guid . '";';
	
	fwrite($handle, $sql . "\n");

	$redirect = get_redirect($url);

	if ($redirect != '')
	{
		echo $redirect . "\n";
	
		$html = get($redirect);
	
		if (preg_match('/<a href="(?<key>.*)\/names"/Uu', $html, $m))
		{
			fetch_csv($m['key']);
		}
	}
}

fclose($handle);



?>
