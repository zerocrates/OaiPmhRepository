# OAI-PMH Repository #
## A plugin for Omeka ##

This plugin implements an Open Archives Initiative Protocol for Metadata
Harvesting ([OAI-PMH][1]) repository for Omeka, allowing Omeka items to be
harvested by OAI-PMH harvesters. The plugin implements version 2.0 of the
protocol.

### Version History ###

*2.1*

* New RDF metadata format
* New `oai_pmh_repository_metadata_formats` filter to allow other plugins to add and modify metadata formats
* Localization support (contributed by [jajm](https://github.com/jajm))
* New option to exclude empty collections from ListSets (contributed by [Daniel-KM](https://github.com/Daniel-KM))
* New option to expose item type as Dublin Core Type value (contributed by [Daniel-KM](https://github.com/Daniel-KM))
* More accurate "earliest datestamp" calculation (contributed by [Daniel-KM](https://github.com/Daniel-KM))
* Fixed "expose files" flag check for METS and omeka-xml outputs (contributed by [Daniel-KM](https://github.com/Daniel-KM))
* Additional miscellaneous cleanup (significant portions contributed by [Daniel-KM](https://github.com/Daniel-KM))

*2.0*

* Initial support for Omeka 2.0 and up
* File exposure support for METS

 [1]: https://www.openarchives.org/OAI/openarchivesprotocol.html
