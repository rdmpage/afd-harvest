<?php

require_once (dirname(dirname(__FILE__)) . '/adodb5/adodb.inc.php');
require_once('php-json-ld/jsonld.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' , 'afd');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


$db->EXECUTE("set names 'utf8'"); 



	
$page = 1000;
$offset = 0;

$done = false;

while (!$done)
{
	$sql = 'SELECT * FROM afd WHERE NAME_GUID IS NOT NULL AND PUBLICATION_GUID IS NOT NULL';

	$sql .= ' LIMIT ' . $page . ' OFFSET ' . $offset;
	
	
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
			
			/*
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
			*/
			
				
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
					
				
		// link to taxon
		$taxon = '<https://bie.ala.org.au/species/urn:lsid:biodiversity.org.au:afd.taxon:' . $result->fields['TAXON_GUID'] . '>';

		// link to name, we have to make up a GUID as this seems to be broken in AFD
		if ($result->fields['NAME_GUID'] != '')
		{
			$triples[] = $taxon . ' <http://rs.tdwg.org/ontology/voc/TaxonConcept#hasName> ' . $s . ' . ';
			$triples[] = $taxon . ' <http://rs.tdwg.org/dwc/terms/scientificNameID> ' . $s . ' . ';
		}
		
		
		
		// link to literature
		
		

		//print_r($triples);
		
		$t = join("\n", $triples) . "\n\n";
		
		if (1)
		{
			echo $t . "\n";
		}
		else
		{
	
			$doc = jsonld_from_rdf($t, array('format' => 'application/nquads'));
	
			$context = (object)array(
				'@vocab' => 'http://schema.org/',
				'tcommon' => 'http://rs.tdwg.org/ontology/voc/Common#',
				'tc' => 'http://rs.tdwg.org/ontology/voc/TaxonConcept#',
				'tn' => 'http://rs.tdwg.org/ontology/voc/TaxonName#',				
				'dwc' => 'http://rs.tdwg.org/dwc/terms/'
			);
	
			$compacted = jsonld_compact($doc, $context);

			echo json_encode($compacted, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	
			echo "\n";
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

