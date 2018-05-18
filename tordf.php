<?php
require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' , 'afd');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


$db->EXECUTE("set names 'utf8'"); 


// publications, taxa, names, names2taxa
$mode = 'names_as_annotations';

// publications, schema.org
if ($mode == 'publications')
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
			$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes(strip_tags($result->fields['PUB_PUB_TITLE']), '"') . '" .';
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
			$triples[] = $s . ' <http://schema/isPartOf> <https://biodiversity.org.au/afd/publication/' . $result->fields['PARENT_PUBLICATION_GUID'] . '> .';
		}		
		
		if ($result->fields['PUB_PUB_TYPE'] != '')
		{
			switch ($result->fields['PUB_PUB_TYPE'])
			{
				case 'Article in Journal':
					$type = '<http://schema.org/ScholarlyArticle>';
					break;
					
				case 'Book':
					$type = '<http://schema.org/Book>';
					break;
				
				case 'Chapter in a Book':
					$type = '<http://schema.org/Chapter>';
					break;

				case 'Miscellaneous':
					$type = '<http://schema.org/CreativeWork>';
					break;
					
				case 'Section in Article':
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
		
		echo join("\n", $triples) . "\n";
		
		$result->MoveNext();
	
	}
}

// taxa (parent-child as rdfs:subClassOf )
if ($mode == 'taxa')
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

// question is, do we treat names as names, or names + publication as an annotation (~ usage)?


// names (as TDWG LSID names)
if ($mode == 'names')
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
			// common
			$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($result->fields['NAMES_VARIOUS'], '"') . '" . ';			
		
		}
		else
		{
			// scientific 
			$triples[] = $s . ' <http://schema.org/name> ' . '"' . addcslashes($result->fields['SCIENTIFIC_NAME'], '"') . '" . ';			
		
			$name_parts = array();
		
			if ($result->fields['FAMILY'] != '')
			{
				if ($result->fields['RANK'] == 'Family')
				{
					$triples[] = $s . ' <http://rs.tdwg.org/ontology/voc/TaxonName#uninomial> ' . '"' . addcslashes($result->fields['FAMILY'], '"') . '" . ';
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
			
			
			// status, nomenclature and taxonomy
			if ($result->fields['NAME_TYPE'] != '')
			{
				$taxonomic_status = '';
				$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/available'; // by default
			
				$name_subtype = $result->fields['NAME_SUBTYPE'];
			
				switch ($result->fields['NAME_TYPE'])
				{
					case 'Synonym':
						$taxonomic_status = 'http://rs.gbif.org/vocabulary/gbif/taxonomicStatus/synonym';
					
						switch ($name_subtype)
						{
							case 'synonym':
								break;
								
							case 'junior homonym':
								$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/invalidum';									
								// http://purl.obolibrary.org/obo/NOMEN_0000289 -- homonym 
								break;
								
							case 'invalid name':
								$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/invalidum';
								// http://purl.obolibrary.org/obo/NOMEN_0000272 -- invalid
								break;
								
							case 'nomen nudum':
								$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/nudum';
								break;

							case 'replacement name':
								$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/novum';
								break;

							case 'original spelling':
								$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/orthographia';
								break;
								
							case 'subsequent misspelling':
								$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/orthographia';
								break;
								
							case 'emendation':
								$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/correctum';
								break;
								
							case 'nomen dubium':
								$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/dubimum';
								break;
								
							case 'objective synonym':
								$taxonomic_status = 'http://rs.gbif.org/vocabulary/gbif/taxonomicStatus/homotypicSynonym';
								break;

							case 'subjective synonym':
								$taxonomic_status = 'http://rs.gbif.org/vocabulary/gbif/taxonomicStatus/heterotypicSynonym';
								break;
								
							case 'nomem oblitum':
								$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/oblitum';
								break;
								
							case 'nomen protectum':
								$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/protectum';
								break;
						
							default:
								break;
						}						
						break;
						
					case 'Valid Name':
						$taxonomic_status = 'http://rs.gbif.org/vocabulary/gbif/taxonomicStatus/accepted';
						break;
						
					case 'Generic Combination':
						$taxonomic_status = 'http://rs.gbif.org/vocabulary/gbif/taxonomicStatus/synonym';
						$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/combinatio';
						break;
						
					default:
						break;
				}
				
				if ($taxonomic_status != '')
				{
					$triples[] = $s . ' <http://rs.tdwg.org/dwc/terms/taxonomicStatus> <' .  $taxonomic_status . '> . ';					
				}
				
				if ($nomenclatural_status != '')
				{
					$triples[] = $s . ' <http://rs.tdwg.org/dwc/terms/nomenclaturalStatus> <' .  $nomenclatural_status . '> . ';
				}			
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
if ($mode == 'names2taxa')
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

if ($mode == 'names_as_annotations')
{
	$sql = 'SELECT * FROM afd WHERE PUBLICATION_GUID IS NOT NULL';
	
	$sql .= ' AND TAXON_GUID="cd3c80cc-f09c-4c7c-8fdd-60ddeb0ca90d"';
	//$sql .= ' AND NAME_TYPE <> "Common Name"';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$triples = array();
	
		// AFD doesn't have a resolver for names, so we make this up
		$s = 
	
		// annotation
		
		$annotation_id = '<urn:lsid:biodiversity.org.au:afd.name:' . $result->fields['NAME_GUID'] . '#annotation>';
		$triples[] = $annotation_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.w3.org/ns/oa#Annotation> . ';
		
		// body
		// scientific name
		
		// body_id
		$body_id = str_replace('#annotation>', '>', $annotation_id);

		$triples[] = $annotation_id . ' <http://www.w3.org/ns/oa#hasBody> ' . $body_id . ' .';

		// name-specific stuff
		
		$nomenclatural_status = '';
		
		// name, may be scientific or common
		if ($result->fields['NAME_TYPE'] == 'Common Name')
		{
			// common
			$triples[] = $body_id . ' <http://schema.org/name> ' . '"' . addcslashes($result->fields['NAMES_VARIOUS'], '"') . '" . ';					
			$triples[] = $body_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#value>  ' . '"' . addcslashes($result->fields['NAMES_VARIOUS'], '"') . '" . ';					
		}
		else
		{
			// scientific 
			$triples[] = $body_id . ' <http://schema.org/name> ' . '"' . addcslashes($result->fields['SCIENTIFIC_NAME'], '"') . '" . ';			
			$triples[] = $body_id . ' <http://www.w3.org/1999/02/22-rdf-syntax-ns#value> ' . '"' . addcslashes($result->fields['SCIENTIFIC_NAME'], '"') . '" . ';			
		
			$name_parts = array();
		
			if ($result->fields['FAMILY'] != '')
			{
				if ($result->fields['RANK'] == 'Family')
				{
					$triples[] = $body_id . ' <http://rs.tdwg.org/ontology/voc/TaxonName#uninomial> ' . '"' . addcslashes($result->fields['FAMILY'], '"') . '" . ';
					$name_parts[] = $result->fields['FAMILY'];
				}				
			}			
					
			if ($result->fields['GENUS'] != '')
			{
				if ($result->fields['RANK'] == 'Genus')
				{
					$triples[] = $body_id . ' <http://rs.tdwg.org/ontology/voc/TaxonName#uninomial> ' . '"' . addcslashes($result->fields['GENUS'], '"') . '" . ';
				}
				else
				{
					$triples[] = $body_id . ' <http://rs.tdwg.org/ontology/voc/TaxonName#genusPart> ' . '"' . addcslashes($result->fields['GENUS'], '"') . '" . ';
				}
				$name_parts[] = $result->fields['GENUS'];
			}						
		
			if ($result->fields['SUBGENUS'] != '')
			{
				$triples[] = $body_id . ' <http://rs.tdwg.org/ontology/voc/TaxonName#infragenericEpithet> ' . '"' . addcslashes($result->fields['SUBGENUS'], '"') . '" . ';
				$name_parts[] = '(' . $result->fields['SUBGENUS'] . ')';
			}			
		
			if ($result->fields['SPECIES'] != '')
			{
				$triples[] = $body_id . ' <http://rs.tdwg.org/ontology/voc/TaxonName#specificEpithet> ' . '"' . addcslashes($result->fields['SPECIES'], '"') . '" . ';
				$name_parts[] = $result->fields['SPECIES'];
			}

			if ($result->fields['SUBSPECIES'] != '')
			{
				$triples[] = $body_id . ' <http://rs.tdwg.org/ontology/voc/TaxonName#infraspecificEpithet> ' . '"' . addcslashes($result->fields['SUBGENUS'], '"') . '" . ';
				$name_parts[] = $result->fields['SUBSPECIES'];
			}			
		
			$nameComplete = join(' ', $name_parts);
			if ($nameComplete == '')
			{
				$triples[] = $body_id . ' <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> ' . '"' . addcslashes($result->fields['SCIENTIFIC_NAME'], '"') . '" . ';						
			}
			else
			{
				$triples[] = $body_id . ' <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> ' . '"' . $nameComplete . '" . ';						
			}
			
			// name status
			$name_subtype = $result->fields['NAME_SUBTYPE'];
		
			switch ($name_subtype)
			{
					
				case 'junior homonym':
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/invalidum';									
					// http://purl.obolibrary.org/obo/NOMEN_0000289 -- homonym 
					break;
					
				case 'invalid name':
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/invalidum';
					// http://purl.obolibrary.org/obo/NOMEN_0000272 -- invalid
					break;
					
				case 'nomen nudum':
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/nudum';
					break;

				case 'replacement name':
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/novum';
					break;

				case 'original spelling':
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/orthographia';
					break;
					
				case 'subsequent misspelling':
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/orthographia';
					break;
					
				case 'emendation':
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/correctum';
					break;
					
				case 'nomen dubium':
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/dubimum';
					break;
					
				case 'objective synonym':
					$taxonomic_status = 'http://rs.gbif.org/vocabulary/gbif/taxonomicStatus/homotypicSynonym';
					break;

				case 'subjective synonym':
					$taxonomic_status = 'http://rs.gbif.org/vocabulary/gbif/taxonomicStatus/heterotypicSynonym';
					break;
					
				case 'nomem oblitum':
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/oblitum';
					break;
					
				case 'nomen protectum':
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/protectum';
					break;
			
				case 'synonym':
				default:
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/available'; 
					break;
			}
			
			// defer output to later as we want to check for combinations
			//$triples[] = $body_id . ' <http://rs.tdwg.org/dwc/terms/nomenclaturalStatus> ' . '<' . $nomenclatural_status . '> . ';						
								
		}

		// target is a reference, either URI, or URI with source/selector
		// target id
		$target_id = '<https://biodiversity.org.au/afd/publication/' . $result->fields['PUBLICATION_GUID'] . '>';
		
		$triples[] = $annotation_id . ' <http://www.w3.org/ns/oa#hasTarget> ' . $target_id . ' .';
		
		
		// classify the type of reference
		// decide where we put this
		// also take into account hypothes.is annotations and where user puts
		// equivalent information (e.g., "tags")
		
		$reference_type = 'http://rs.gbif.org/vocabulary/gbif/referenceType/publication';
		
		if ($result->fields['NAME_TYPE'] != '')
		{
			switch ($result->fields['NAME_TYPE'])
			{
				case 'Generic Combination':
					$reference_type = 'http://rs.gbif.org/vocabulary/gbif/referenceType/combination';
					$nomenclatural_status = 'http://rs.gbif.org/vocabulary/gbif/nomenclatural_status/combinatio';
					break;
									
				default:
					break;
			}			
		}	
		
		if ($result->fields['ORIG_COMBINATION'] == 'Y')
		{
			$reference_type = 'http://rs.gbif.org/vocabulary/gbif/referenceType/original';
		}
		
		$triples[] = $target_id . ' <http://purl.org/dc/terms/type> ' . '<' . $reference_type . '> . ';						
		
		if ($nomenclatural_status != '')
		{
			$triples[] = $body_id . ' <http://rs.tdwg.org/dwc/terms/nomenclaturalStatus> ' . '<' . $nomenclatural_status . '> . ';						
		}		
		
		//print_r($triples);
		
		echo join("\n", $triples) . "\n\n";

		$result->MoveNext();

	}

}