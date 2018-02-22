# afd-harvest
Harvest Australian Faunal Directory


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
 | Valid
 | A valid scientific name.
 | Common
 | A common name.
 | Synonym
 | Taxonomically an accepted available name, but in this instance, for convenience, including the categories of names listed below.
 | Miscellaneous Literature Name
 | A literature synonym.
NAME_SUBTYPE | Further classification on the name type:
 | Valid name
 | Valid names are not broken down into subtypes.
 | Common name
 | Subtypes are either "Preferred" or "General".
 | Synonym
 | Subtypes can be "synonym", "nomen nudum", "replacement name", "invalid name", "original spelling", "subsequent misspelling", "emendation", "nomen dubium", "objective synonym", "subjective synonym", "junior homonym", "nomem oblitum" or "nomen protectum".
 | Miscellaneous Literature Name
 | Miscellaneous literature names are not broken down into subtypes.
RANK | The rank of this taxon.
QUALIFICATION | Qualification or comments for the taxon.
AUTHOR | The authority author name.
YEAR | The authoriy year.
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
For valid names this will be the same as the TAXON_GUID. The publication fields will be empty, as the publication is this directory itself.
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
 | Book
 | A book
 | Chapter in a Book
 | A chapter within a book
 | Article in Journal
 | An article within a journal
 | Section in an Article
 | A section within a article in a journal
 | URL
 | A website URL
 | This Work
 | A volume of the AFD
 | Miscellaneous
A miscellaneous publication
PUBLICATION_GUID | The GUID for this publication record.
PUBLICATION_LAST_UPDATE | The timestamp of the last update to this publication.
PARENT_PUBLICATION_GUID | The GUID for the publication containing this publication (if any).
