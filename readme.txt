Islandora book viewer.

Uses the archive.org bookviewer code

DEPLYMENT:

Deploy the module.

Ingest the archieveorg_bookCModel.xml as archiveorg:bookCModel.  This is in ./foxml/archiveorg_bookCModel.xml.

Change the defines in the ./plugins/BookReaderDemo/index.php file (first few lines) to point at your webapps.

RELATIONSHIPS:

Books should have the contentModel: archeiveorg:bookCModel

<fedora-model:hasModel rdf:resource="info:fedora/archiveorg:bookCModel"></fedora-model:hasModel>
  
Pages should be the members of a book object:
Pages should have the contentModel: archiveorg:pageCModel (an objet is not necessary)
Pages should have the relationship: isPageNumber

<fedora:isMemberOf rdf:resource="info:fedora/example:1"></fedora:isMemberOf>
    <pageNS:isPageNumber>1</pageNS:isPageNumber>
    <fedora-model:hasModel rdf:resource="info:fedora/archiveorg:pageCModel"></fedora-model:hasModel>
 