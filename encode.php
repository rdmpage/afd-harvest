<?php

// Convert files to UTF-8 encoding

$basedir = dirname(__FILE__) . '/data';

$files = scandir($basedir);

// debugging
//$files=array('AMBASSIDAE.csv');

foreach ($files as $filename)
{
	if (preg_match('/\.csv$/', $filename))
	{	
		$source = $basedir . '/' . $filename;
		$destination = $basedir . '/' . $filename . '.new';
	
		$command = "iconv -f iso-8859-1 -t utf-8 '$source' > '$destination'";
		echo $command . "\n";
		system($command, $retval);
		
		echo $retval . "\n";
		
		if ($retval != 0)
		{
			exit();
		}
		
		rename($destination, $source);
	}
}
		
?>
