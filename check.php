<?php

// Check files

date_default_timezone_set('UTC');

//----------------------------------------------------------------------------------------
// http://stackoverflow.com/a/5996888/9684
function translate_quoted($string) {
  $search  = array("\\t", "\\n", "\\r");
  $replace = array( "\t",  "\n",  "\r");
  return str_replace($search, $replace, $string);
}

$basedir = dirname(__FILE__) . '/data';

$files = scandir($basedir);

// debugging
//$files=array('TRICHOPTERA.csv');

foreach ($files as $filename)
{
	if (preg_match('/\.csv$/', $filename))
	{	
		$csv_filename = $basedir . '/' . $filename;
		
		
		$file = fopen($csv_filename, "r"); 

	   $line = fgets($file);	
	   
	   if (preg_match('/<html/', $line))
	   {
	  	 echo $line . "\n";
	  	 
	  	 rename($csv_filename, $basedir . '/broken/' . $filename);
	   }
	   
	   //echo $line . "\n";
	   
	   fclose($file);	

	}
}
		
?>
