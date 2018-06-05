<?php

// Publications to triples

require_once (dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');

require_once('php-json-ld/jsonld.php');

require_once (dirname(dirname(__FILE__)) . '/parse_authors.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' , 'afd');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


$db->EXECUTE("set names 'utf8'"); 

$sql = 'SELECT DISTINCT *
FROM bibliography
WHERE PARENT_PUBLICATION_GUID ="8823e1b7-c40a-4a59-a8f1-4c2cf0ad430a";';


$sql = 'SELECT DISTINCT *
FROM bibliography
WHERE PUBLICATION_GUID ="49cedf3d-3d83-478d-93d1-30c74527f168";';

$sql = 'SELECT DISTINCT *
FROM bibliography
WHERE PUBLICATION_GUID ="25d1f178-cadb-465a-9937-d75bbd17ca5c";';


/*
// book
$sql = 'SELECT DISTINCT *
FROM bibliography
WHERE PUBLICATION_GUID ="d9e0e56c-30f4-4391-b05d-038463908d8f";';

// chapter
$sql = 'SELECT DISTINCT *
FROM bibliography
WHERE PUBLICATION_GUID ="b60c708e-85bf-4543-9955-947bb5143e6a";';
*/

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$enhance_authors 		= true;
$enhance_metadata 		= true;
$enhance_identifiers	= true;


while (!$result->EOF) 
{
	// triples
	
	$triples = array();
	
	$subject_id = 'https://biodiversity.org.au/afd/publication/' . $result->fields['PUBLICATION_GUID'];
	
	$s = '<' . $subject_id . '>';
	
	if ($result->fields['PUB_AUTHOR'] != '')
	{
		if ($enhance_authors)
		{
			$p = parse_authors($result->fields['PUB_AUTHOR']);
			foreach ($p as $author)
			{
				$author_id = '<' . $subject_id . '#creator/' . $author->id . '>';
				
				
				$triples[] = $s . ' <http://schema.org/creator> ' .  $author_id . ' .';
				$triples[] = $author_id . ' <http://schema.org/name> ' . '"' . addcslashes($author->name, '"') . '"' . ' .';
				
				if (isset($author->givenName))
				{
					$triples[] = $author_id . ' <http://schema.org/givenName> ' . '"' . addcslashes($author->givenName, '"') . '"' . ' .';				
				}
				if (isset($author->familyName))
				{
					$triples[] = $author_id . ' <http://schema.org/familyName> ' . '"' . addcslashes($author->familyName, '"') . '"' . ' .';				
				}

				$triples[] = $author_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . ' <http://schema.org/' . $author->type . '>' . ' .';			
			}
		
		}
		else
		{
			$triples[] = $s . ' <http://schema.org/creator> ' . '"' . addcslashes($result->fields['PUB_AUTHOR'], '"') . '" .';
		}
	}
	
	if ($result->fields['PUB_YEAR'] != '')
	{
		$triples[] = $s . ' <http://schema.org/datePublished> ' . '"' . addcslashes($result->fields['PUB_YEAR'], '"') . '" .';
	}
	
	if ($result->fields['PUB_TITLE'] .= '')
	{
		$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes(strip_tags($result->fields['PUB_TITLE']), '"') . '" .';
	}
	
	if ($result->fields['PUB_PAGES'] != '')
	{
		$triples[] = $s . ' <http://schema.org/pagination> ' . '"' . addcslashes($result->fields['PUB_PAGES'], '"') . '" .';
	}
	
	/*
	if ($result->fields['PUB_PARENT_JOURNAL_TITLE'] != '')
	{
		$triples[] = $s . ' <http://prismstandard.org/namespaces/basic/2.1/publicationName> ' . '"' . addcslashes($result->fields['PUB_PARENT_JOURNAL_TITLE'], '"') . '" .';
	}
	*/
	
	if ($result->fields['PARENT_PUBLICATION_GUID'] != '')
	{
		$container_id = '<https://biodiversity.org.au/afd/publication/' . $result->fields['PARENT_PUBLICATION_GUID'] . '>';
		$triples[] = $s . ' <http://schema.org/isPartOf> ' . $container_id . ' .';

		// Journal
		if ($result->fields['PUB_PARENT_JOURNAL_TITLE'] != '')
		{
			$triples[] = $container_id . ' <http://schema.org/name> ' .  '"' . addcslashes($result->fields['PUB_PARENT_JOURNAL_TITLE'], '"') . '" .';
			$triples[] = $container_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/Periodical> .';
		}
		
		// Book
		if ($result->fields['PUB_PARENT_BOOK_TITLE'] != '')
		{
			$triples[] = $container_id . ' <http://schema.org/name> ' .  '"' . addcslashes($result->fields['PUB_PARENT_BOOK_TITLE'], '"') . '" .';
			$triples[] = $container_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/Book> .';
		}
		
	}	
	
	if ($result->fields['PUB_PUBLISHER'] != '')
	{
		$triples[] = $s . ' <http://schema.org/publisher> ' . '"' . addcslashes($result->fields['PUB_PUBLISHER'], '"') . '" .';
	}
	
	if ($enhance_metadata)
	{
		switch ($result->fields['PUB_TYPE'])
		{
			case 'Book':
				break;
				
			default:
				if ($result->fields['volume'] != '')
				{
					$triples[] = $s . ' <http://schema.org/volume> ' . '"' . addcslashes($result->fields['volume'], '"') . '" .';
				}
				if ($result->fields['issue'] != '')
				{
					$triples[] = $s . ' <http://schema.org/issueNumber> ' . '"' . addcslashes($result->fields['issue'], '"') . '" .';
				}
				if ($result->fields['spage'] != '')
				{
					$triples[] = $s . ' <http://schema.org/pageStart> ' . '"' . addcslashes($result->fields['spage'], '"') . '" .';
				}
				if ($result->fields['epage'] != '')
				{
					$triples[] = $s . ' <http://schema.org/pageEnd> ' . '"' . addcslashes($result->fields['epage'], '"') . '" .';
				}
				break;
		}		
	}
	
	if ($enhance_identifiers)
	{
		if ($result->fields['doi'] != '')
		{
			$identifier_id = '<' . $subject_id . '#doi' . '>';
		
			$triples[] = $s . ' <http://schema.org/identifier> ' . $identifier_id . '.';			
			$triples[] = $identifier_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://schema.org/PropertyValue> .';
			$triples[] = $identifier_id . ' <http://schema.org/propertyID> ' . '"doi"' . '.';
			$triples[] = $identifier_id . ' <http://schema.org/value> ' . '"' . addcslashes($result->fields['doi'], '"') . '"' . '.';
			
			$triples[] = $s . ' <http://schema.org/sameAs> ' . '<https://doi.org/' . $result->fields['doi'] . '> ' . '. ';
			
		}
		
		if ($result->fields['isbn'] != '')
		{
			$triples[] = $s . ' <http://schema.org/isbn> ' . '"' . addcslashes($result->fields['doi'], '"') . '"' . '.';
		}		
		
	}
	
	
	
	if ($result->fields['PUB_TYPE'] != '')
	{
		switch ($result->fields['PUB_TYPE'])
		{
			case 'Article in Journal':
				$type = '<http://schema.org/ScholarlyArticle>';
				break;
				
			case 'Book':
				$type = '<http://schema.org/Book>';
				break;
			
			case 'Chapter in Book':
				$type = '<http://schema.org/Chapter>';
				break;

			case 'Miscellaneous':
				$type = '<http://schema.org/CreativeWork>';
				break;
				
			case 'Section in Article':
				$type = '<http://schema.org/CreativeWork>';
				break;
				
			case 'This Work':
				$type = '<http://schema.org/CreativeWork>';
				break;
				
			case 'URL':
				$type = '<http://schema.org/WebSite>';
				break;
				
			default:
				$type = '<http://schema.org/CreativeWork>';
				break;
		}
		$triples[] = $s . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . $type . ' .';
	}
	
	// print_r($triples);
	
	
	$t = join("\n", $triples);
	
	//echo $t . "\n";
	
	$doc = jsonld_from_rdf($t, array('format' => 'application/nquads'));
	
	$context = (object)array(
		'@vocab' => 'http://schema.org/'
	);
	
	$compacted = jsonld_compact($doc, $context);

	echo json_encode($compacted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	
	echo "\n";
	
	
	//exit();
	
	$result->MoveNext();

}

?>

