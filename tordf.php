<?php
require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' , 'afd');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


$db->EXECUTE("set names 'utf8'"); 

// publications
if (0)
{
	$sql = 'SELECT DISTINCT `PUB_PUB_AUTHOR`,
`PUB_PUB_YEAR`,
`PUB_PUB_TITLE`,
`PUB_PUB_PAGES`,
`PUB_PUB_PARENT_JOURNAL_TITLE`,
`PUB_PUB_PARENT_ARTICLE_TITLE`,
`PUBLICATION_GUID`,
`PARENT_PUBLICATION_GUID`,
`PUB_PUB_TYPE`
FROM afd WHERE PUBLICATION_GUID IS NOT NULL;';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	
	while (!$result->EOF) 
	{
		//print_r($result->fields);exit();
		
		// triples
		
		$triples = array();
		
		$s = '<https://biodiversity.org.au/afd/publication/' . $result->fields['PUBLICATION_GUID'] . '>';
		
		if ($result->fields['PUB_PUB_AUTHOR'] != '')
		{
			$triples[] = $s . ' <http://schema.org/author> ' . '"' . addcslashes($result->fields['PUB_PUB_AUTHOR'], '"') . '" .';
		}
		
		if ($result->fields['PUB_PUB_YEAR'] != '')
		{
			$triples[] = $s . ' <http://schema.org/datePublished> ' . '"' . addcslashes($result->fields['PUB_PUB_YEAR'], '"') . '" .';
		}
		
		if ($result->fields['PUB_PUB_TITLE'] .= '')
		{
			$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($result->fields['PUB_PUB_TITLE'], '"') . '" .';
		}
		
		if ($result->fields['PUB_PUB_PAGES'] != '')
		{
			$triples[] = $s . ' <http://schema.org/pagination> ' . '"' . addcslashes($result->fields['PUB_PUB_PAGES'], '"') . '" .';
		}
		
		if ($result->fields['PUB_PUB_PARENT_JOURNAL_TITLE'] != '')
		{
			$triples[] = $s . ' <http://prismstandard.org/namespaces/basic/2.1/publicationName> ' . '"' . addcslashes($result->fields['PUB_PUB_PARENT_JOURNAL_TITLE'], '"') . '" .';
		}
		
		if ($result->fields['PARENT_PUBLICATION_GUID'] != '')
		{
			$triples[] = $s . ' <http://scheme/isPartOf> <https://biodiversity.org.au/afd/publication/' . $result->fields['PARENT_PUBLICATION_GUID'] . '> .';
		}		
		
		if ($result->fields['PUB_PUB_TYPE'] != '')
		{
			$type = '<http://schema.org/CreativeWork>';
			
			switch ($result->fields['PUB_PUB_TYPE'])
			{
				case 'Article in Journal':
					$type = '<http://schema.org/ScholarlyArticle>';
					break;
					
				default:
					break;
			}
			$triples[] = $s . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ' . $type . ' .';
		}
		
 

		// print_r($triples);
		
		echo join("\n", $triples);
		
		$result->MoveNext();
	
	}

	}

	// taxa
	if (0)
	{
		$sql = 'SELECT * FROM afd WHERE PARENT_TAXON_GUID IS NOT NULL;';
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	
		while (!$result->EOF) 
		{
	
			$triples = array();
		
			$s = '<https://bie.ala.org.au/species/urn:lsid:biodiversity.org.au:afd.taxon:' . $result->fields['TAXON_GUID'] . '>';
		
			// there are multiple available types for taxa
			$triples[] = $s . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://rs.tdwg.org/ontology/voc/TaxonConcept#TaxonConcept> . ';
			$triples[] = $s . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://rs.tdwg.org/dwc/terms/Taxon> . ';
			
			// identifiers

			// name string
			if ($result->fields['SCIENTIFIC_NAME'] != '')
			{
				$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($result->fields['SCIENTIFIC_NAME'], '"') . '" . ';

				$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonConcept#nameString> ' . '"' . addcslashes($result->fields['SCIENTIFIC_NAME'], '"') . '" . ';
				$triples[] = $s . ' <http://rs.tdwg.org/dwc/terms/scientificName> ' . '"' . addcslashes($result->fields['SCIENTIFIC_NAME'], '"') . '" . ';
			}

			/*
			// link to name, we have to make up a GUID as this seems to be broken in AFD
			if ($result->fields['NAME_GUID'] != '')
			{
				$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonConcept#hasName> ' . '<urn:lsid:biodiversity.org.au:afd.name:' . $result->fields['NAME_GUID'] . '> . ';
				$triples[] = $s . ' <http://rs.tdwg.org/dwc/terms/scientificNameID> ' . '<urn:lsid:biodiversity.org.au:afd.name:' . $result->fields['NAME_GUID'] . '> . ';
			}
			*/
			
			// rank (should be property of name not taxon)
			if ($result->fields['RANK'] != '')
			{
				$triples[] = $s . ' <http://rs.tdwg.org/dwc/terms/taxonRank> ' . '"' . addcslashes($result->fields['RANK'], '"') . '" . ';
			}
						
			// parent-child
			if ($result->fields['PARENT_TAXON_GUID'] != '')
			{
				// rdfs
				$triples[] = $s . ' <http://www.w3.org/2000/01/rdf-schema#subClassOf> <https://bie.ala.org.au/species/urn:lsid:biodiversity.org.au:afd.taxon:' . $result->fields['PARENT_TAXON_GUID'] . '> . ';
				// darwin core
				$triples[] = $s . ' <http://rs.tdwg.org/dwc/terms/parentNameUsageID> <https://bie.ala.org.au/species/urn:lsid:biodiversity.org.au:afd.taxon:' . $result->fields['PARENT_TAXON_GUID'] . '> . ';
			}
			
			
			

			//print_r($triples);
			
			echo join("\n", $triples);

			$result->MoveNext();
	
		}

	}


// names
	if (0)
	{
		$sql = 'SELECT * FROM afd WHERE NAME_GUID IS NOT NULL;';
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	
		while (!$result->EOF) 
		{
	
			$triples = array();
		
			// AFD doesn't have a resolver for names, so we make this up
			$s = '<urn:lsid:biodiversity.org.au:afd.name:' . $result->fields['NAME_GUID'] . '>';
		
			$triples[] = $s . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://rs.tdwg.org/ontology/voc/TaxonName#TaxonName> . ';
			
			
			
			// identifiers

			
			// name, may be scientific or common
			if ($result->fields['NAME_TYPE'] == 'Common Name')
			{
				$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($result->fields['NAMES_VARIOUS'], '"') . '" . ';			
			
			}
			else
			{
				$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($result->fields['SCIENTIFIC_NAME'], '"') . '" . ';			
			
				$name_parts = array();
			
				if ($result->fields['FAMILY'] != '')
				{
					if ($result->fields['RANK'] == 'Family')
					{
						$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#uninomial> ' . '"' . addcslashes($result->fields['GENUS'], '"') . '" . ';
						$name_parts[] = $result->fields['FAMILY'];
					}				
				}			
						
				if ($result->fields['GENUS'] != '')
				{
					if ($result->fields['RANK'] == 'Genus')
					{
						$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#uninomial> ' . '"' . addcslashes($result->fields['GENUS'], '"') . '" . ';
					}
					else
					{
						$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#genusPart> ' . '"' . addcslashes($result->fields['GENUS'], '"') . '" . ';
					}
					$name_parts[] = $result->fields['GENUS'];
				}						
			
				if ($result->fields['SUBGENUS'] != '')
				{
					$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#infragenericEpithet> ' . '"' . addcslashes($result->fields['SUBGENUS'], '"') . '" . ';
					$name_parts[] = '(' . $result->fields['SUBGENUS'] . ')';
				}			
			
				if ($result->fields['SPECIES'] != '')
				{
					$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#specificEpithet> ' . '"' . addcslashes($result->fields['SPECIES'], '"') . '" . ';
					$name_parts[] = $result->fields['SPECIES'];
				}

				if ($result->fields['SUBSPECIES'] != '')
				{
					$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#infraspecificEpithet> ' . '"' . addcslashes($result->fields['SUBGENUS'], '"') . '" . ';
					$name_parts[] = $result->fields['SUBSPECIES'];
				}			
			
				$nameComplete = join(' ', $name_parts);
				if ($nameComplete == '')
				{
					$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> ' . '"' . addcslashes($result->fields['SCIENTIFIC_NAME'], '"') . '" . ';						
				}
				else
				{
					$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> ' . '"' . $nameComplete . '" . ';						
				}
					
			}
					
			if (($result->fields['AUTHOR'] != '') && ($result->fields['YEAR'] != ''))
			{
				$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#authorship> ' . '"' . $result->fields['AUTHOR'] . ', ' . $result->fields['YEAR'] . '" . ';										
			}
						
			
			// publication (if we have a publication we always have a GUID)
			if ($result->fields['PUBLICATION_GUID'] != '')
			{
				$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> ' . '<https://biodiversity.org.au/afd/publication/' . $result->fields['PUBLICATION_GUID'] . '> . ';
			}

			/*
			// link to name, we have to make up a GUID as this seems to be broken in AFD
			if ($result->fields['NAME_GUID'] != '')
			{
				$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/hasName> ' . '<urn:lsid:biodiversity.org.au:afd.name:' . $result->fields['NAME_GUID'] . '> . ';
				$triples[] = $s . ' <http://rs.tdwg.org/dwc/terms/scientificNameID> ' . '<urn:lsid:biodiversity.org.au:afd.name:' . $result->fields['NAME_GUID'] . '> . ';
			}
			*/
			
			// rank 
			if ($result->fields['RANK'] != '')
			{
				$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#rankString> ' . '"' . addcslashes($result->fields['RANK'], '"') . '" . ';
			}
						
					
			

			//print_r($triples);
			
			echo join("\n", $triples) . "\n\n";

			$result->MoveNext();
	
		}

	}
	
	// name to taxa link, AFD accepted taxa don't have publications (AFD is considered to be the publication)
	if (1)
	{
		$sql = 'SELECT * FROM afd WHERE PARENT_TAXON_GUID IS NULL';
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	
		while (!$result->EOF) 
		{
	
			$triples = array();
		
			$s = '<https://bie.ala.org.au/species/urn:lsid:biodiversity.org.au:afd.taxon:' . $result->fields['TAXON_GUID'] . '>';

			// link to name, we have to make up a GUID as this seems to be broken in AFD
			if ($result->fields['NAME_GUID'] != '')
			{
				$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonConcept#hasName> ' . '<urn:lsid:biodiversity.org.au:afd.name:' . $result->fields['NAME_GUID'] . '> . ';
				$triples[] = $s . ' <http://rs.tdwg.org/dwc/terms/scientificNameID> ' . '<urn:lsid:biodiversity.org.au:afd.name:' . $result->fields['NAME_GUID'] . '> . ';
			}

			//print_r($triples);
			
			echo join("\n", $triples) . "\n";

			$result->MoveNext();
	
		}

	}	

