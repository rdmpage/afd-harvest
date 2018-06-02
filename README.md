# afd-harvest
Harvest Australian Faunal Directory

## Links

TAXON_GUID is ALA URL, e.g http://bie.ala.org.au/species/urn:lsid:biodiversity.org.au:afd.taxon:db447bf9-87eb-44de-a438-ce58c1fcd2e2 where **db447bf9-87eb-44de-a438-ce58c1fcd2e2** is the GUID. AFD UUIDs do not seem to be stable, ALA has history of replacement, see e.g. https://bie.ala.org.au/ws/species/urn:lsid:biodiversity.org.au:afd.taxon:db447bf9-87eb-44de-a438-ce58c1fcd2e2.json

### Publication links

Append the PUBLICATION GUID, e.g. a535cc67-8cd1-406d-b723-5cf61b25c5e4 to https://biodiversity.org.au/afd/publication/, e.g. https://biodiversity.org.au/afd/publication/a535cc67-8cd1-406d-b723-5cf61b25c5e4 .

## Format Definition

Field name | Description
-- | --
CAVS_CODE | The CAVS code if available.
CAAB_CODE | The CAAB code if available.
NAMES_VARIOUS | This field holds either the species group name or the common name.
SCIENTIFIC_NAME | The full scientific name and authority.
FAMILY | The family scientific name.
GENUS | The genus scientific name.
SUBGENUS | The subgenus scientific name.
SPECIES | The species scientific name.
SUBSPECIES | The subspecies scientific name.
NAME_TYPE | The classification of this name:
. | Valid
. | A valid scientific name.
. | Common
. | A common name.
. | Synonym
. | Taxonomically an accepted available name, but in this instance, for convenience, including the categories of names listed below.
. | Miscellaneous Literature Name
. | A literature synonym.
NAME_SUBTYPE | Further classification on the name type:
. | Valid name
. | Valid names are not broken down into subtypes.
. | Common name
. | Subtypes are either "Preferred" or "General".
. | Synonym
. | Subtypes can be "synonym", "nomen nudum", "replacement name", "invalid name", "original spelling", "subsequent misspelling", "emendation", "nomen dubium", "objective synonym", "subjective synonym", "junior homonym", "nomem oblitum" or "nomen protectum".
. | Miscellaneous Literature Name
. | Miscellaneous literature names are not broken down into subtypes.
RANK | The rank of this taxon.
QUALIFICATION | Qualification or comments for the taxon.
AUTHOR | The authority author name.
YEAR | The authority year.
ORIG_COMBINATION | Whether this is an original combination, either 'Y', 'N' or empty when not applicable.
NAME_GUID | The GUID of the name.
NAME_LAST_UPDATE | The time at which this name was last updated.
TAXON_GUID | The GUID of the AFD taxonomic concept with which this name is associated.
TAXON_LAST_UPDATE | The time at which this taxonomic concept was last updated.
TAXON_PARENT_GUID | The GUID of the parent AFD taxonomic concept.

Fields relating to the primary reference

Field name | Description
-- | --
CONCEPT_GUID | The GUID of taxonomic concept of the primary reference.
. | For valid names this will be the same as the TAXON_GUID. The publication fields will be empty, as the publication is this directory itself.
PUB_AUTHOR | The author of the publication.
PUB_YEAR | The year of the publication.
PUB_TITLE | The title of the publication.
PUB_PAGES | The pages referenced.
PUB_PARENT_BOOK_TITLE | The title of the book in which the chapter occurs, if applicable.
PUB_PARENT_JOURNAL_TITLE | The title of the journal in which the article occurs, if applicable.
PUB_PARENT_ARTICLE_TITLE | The title of the article in which the section occurs, if applicable.
PUB_PUBLICATION_DATE | The publication date.
PUB_PUBLISHER | The publisher.
PUB_FORMATTED | The formatted version of this publication.
PUB_QUALIFICATION | Qualification and comments about this publication.
PUB_TYPE | Type of publication reference:
. | Book
. | A book
. | Chapter in a Book
. | A chapter within a book
. | Article in Journal
. | An article within a journal
. | Section in an Article
. | A section within a article in a journal
. | URL
. | A website URL
. | This Work
. | A volume of the AFD
. | Miscellaneous
A miscellaneous publication
PUBLICATION_GUID | The GUID for this publication record.
PUBLICATION_LAST_UPDATE | The timestamp of the last update to this publication.
PARENT_PUBLICATION_GUID | The GUID for the publication containing this publication (if any).

## Gotchas

The data files are not UTF-8 encoded so we need to convert them, see encode.php.

## SPARQL queries

### Subtree

http://localhost:32773/test/sparql

```
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX dwc: <http://rs.tdwg.org/dwc/terms/>
SELECT ?root_name ?parent_name ?child_name  WHERE
{   
VALUES ?root_name {"HYDROPTILIDAE"}
?root dwc:scientificName ?root_name .
?child rdfs:subClassOf+ ?root .
?child rdfs:subClassOf ?parent .
?child dwc:scientificName ?child_name .
?parent dwc:scientificName ?parent_name .
}
```

![image](https://rawgit.com/rdmpage/afd-harvest/master/HYDROPTILIDAE.png) 

### Publications for names in subtree

```
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX dwc: <http://rs.tdwg.org/dwc/terms/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX dwc: <http://rs.tdwg.org/dwc/terms/>
SELECT DISTINCT ?author ?year  ?title ?journal ?pages WHERE
{   
VALUES ?root_name {"HYDROPTILIDAE"}
?root dwc:scientificName ?root_name .
?child rdfs:subClassOf+ ?root .
?child rdfs:subClassOf ?parent .
?child dwc:scientificName ?child_name .
?parent dwc:scientificName ?parent_name .
   ?child <http://rs.tdwg.org/ontology/voc/TaxonConcept#hasName> ?name .
 ?name <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> ?pub .
  ?pub <http://schema.org/author> ?author .
  ?pub <http://schema.org/datePublished> ?year .
  ?pub <http://schema.org/name> ?title .
   OPTIONAL {
    ?pub <http://prismstandard.org/namespaces/basic/2.1/publicationName> ?journal .
  }
  OPTIONAL {
    ?pub <http://schema.org/pagination> ?pages .
  }


}
```

### Names per year

```
SELECT ?year (STR(COUNT(?year)) as ?c) 
WHERE {   
  ?name <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> ?publication .
  ?publication <http://schema.org/datePublished> ?year .
}
group by ?year
order by desc(?year)
```

### To clear all data

Make sure to change SPARQL endpoint to:
http://localhost:32773/test/update

```
DELETE  {
  ?s ?p ?o 
} 
WHERE {}
```

## Publication counts

```
SELECT PUB_PARENT_JOURNAL_TITLE, COUNT(PUBLICATION_GUID) AS c FROM bibliography WHERE PUB_PARENT_JOURNAL_TITLE IS NOT NULL GROUP BY PUB_PARENT_JOURNAL_TITLE ORDER BY c DESC;
```

Top 20 journals

Journal | Count
---|---:
Annals and Magazine of Natural History | 2043
Zootaxa | 1600
Proceedings of the Linnean Society of New South Wales | 1597
Proceedings of the Zoological Society of London | 1222
Transactions of the Royal Society of South Australia | 919
Australian Journal of Zoology | 887
Records of the Australian Museum | 756
Journal of the Australian Entomological Society | 741
Memoirs of the Queensland Museum | 691
Zoologischer Anzeiger | 543
Records of the Western Australian Museum | 528
Annales de la Société Entomologique de France | 514
Transactions of the Entomological Society of London | 451
Journal of Natural History | 444
Proceedings of the United States National Museum | 435
Proceedings of the Biological Society of Washington | 379
Entomologist's Monthly Magazine | 376
Records of the South Australian Museum (Adelaide) | 372
Invertebrate Taxonomy | 361
The Emu | 359

## Create views for a publication


```
CREATE VIEW`Copeia` AS SELECT * FROM `bibliography` WHERE `bibliography`.`PUB_PARENT_JOURNAL_TITLE` = "Copeia";
```

To generate view SQL for top 40 publications:

```
SELECT CONCAT('CREATE VIEW`', PUB_PARENT_JOURNAL_TITLE, '` AS SELECT * FROM `bibliography` WHERE `bibliography`.`PUB_PARENT_JOURNAL_TITLE` = "',PUB_PARENT_JOURNAL_TITLE,'";'), COUNT(PUBLICATION_GUID) AS c FROM bibliography WHERE PUB_PARENT_JOURNAL_TITLE IS NOT NULL GROUP BY PUB_PARENT_JOURNAL_TITLE ORDER BY c DESC LIMIT 40;
```
