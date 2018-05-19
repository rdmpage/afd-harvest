<?php

// Convert to SQL

date_default_timezone_set('UTC');

//----------------------------------------------------------------------------------------
// http://stackoverflow.com/a/5996888/9684
function translate_quoted($string) {
  $search  = array("\\t", "\\n", "\\r");
  $replace = array( "\t",  "\n",  "\r");
  return str_replace($search, $replace, $string);
}

$basedir = dirname(__FILE__) . '/bibliography';

$files = scandir($basedir);

// debugging
//$files=array('STRATIOMYIDAE-bibliography.csv');

foreach ($files as $filename)
{
	if (preg_match('/\.csv$/', $filename))
	{	
		$filename = $basedir . '/' . $filename;
	
		$row_count = 0;
	
		$file = @fopen($filename, "r") or die("couldn't open $filename");
			
		$file_handle = fopen($filename, "r");
		while (!feof($file_handle)) 
		{
			$row = fgetcsv(
				$file_handle, 
				0, 
				',',
				'"'
				);
				
			//print_r($row);
			
			if ($row_count == 0)
			{
				$column_keys = $row;
			}
			else
			{
				if (is_array($row))
				{
					$obj = new stdclass;
					
					$keys = array();
					$values = array();
					
					foreach ($row as $k => $v)
					{
						if ($v != '')
						{
							switch ($column_keys[$k])
							{
								case 'PUBLICATION_LAST_UPDATE':
									$v = str_replace('T', ' ', $v);
									$v = str_replace('+0000', '', $v);
									
									$v = date( 'Y-m-d H:i:s', strtotime($v));
									break;
									
								default:
									break;
							}
							
							$keys[] = $column_keys[$k];
							
							$values[] = '"' . addcslashes($v, '"\\') . '"';
						}
					}
				
					//print_r($obj);
					
					echo 'REPLACE INTO bibliography(' . join(',', $keys) . ') VALUES (' .  join(',', $values) . ');' . "\n";
					
					
				}
			}
			
			
			
			$row_count++;
		}

	}
}
		
?>
