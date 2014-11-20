<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Class implmenting metadata output for the required oai_dc metadata format.
 * oai_dc is output of the 15 unqualified Dublin Core fields.
 *
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
class OaiPmhRepository_Metadata_OaiDc implements OaiPmhRepository_Metadata_FormatInterface
{
    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'oai_dc';
    
    /** XML namespace for output format */
    const METADATA_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    
    /** XML schema for output format */
    const METADATA_SCHEMA = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    
    /** XML namespace for unqualified Dublin Core */
    const DC_NAMESPACE_URI = 'http://purl.org/dc/elements/1.1/';
    
    /**
     * Appends Dublin Core metadata. 
     *
     * Appends a metadata element, an child element with the required format,
     * and further children for each of the Dublin Core fields present in the
     * item.
     */
    public function appendMetadata($item, $metadataElement, $generator)
    {
        $document = $generator->getDocument();
        $oai_dc = $document->createElementNS(
            self::METADATA_NAMESPACE, 'oai_dc:dc');
        $metadataElement->appendChild($oai_dc);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $oai_dc->setAttribute('xmlns:dc', self::DC_NAMESPACE_URI);
        $oai_dc->setAttribute('xmlns:xsi', OaiPmhRepository_XmlGeneratorAbstract::XML_SCHEMA_NAMESPACE_URI);
        $oai_dc->setAttribute('xsi:schemaLocation', self::METADATA_NAMESPACE.' '.
            self::METADATA_SCHEMA);

        /* Each of the 16 unqualified Dublin Core elements, in the order
         * specified by the oai_dc XML schema
         */
        $dcElementNames = array( 'title', 'creator', 'subject', 'description',
                                 'publisher', 'contributor', 'date', 'type',
                                 'format', 'identifier', 'source', 'language',
                                 'relation', 'coverage', 'rights' );

        /* Must create elements using createElement to make DOM allow a
         * top-level xmlns declaration instead of wasteful and non-
         * compliant per-node declarations.
         */
        foreach($dcElementNames as $elementName)
        {   
            $upperName = Inflector::camelize($elementName);
            $dcElements = $item->getElementTexts(
                'Dublin Core',$upperName );
            foreach($dcElements as $elementText)
            {
                $generator->appendNewElement($oai_dc, 
                    'dc:'.$elementName, $elementText->text);
            }
            // Append the browse URI to all results
            if($elementName == 'identifier') 
            {
                $generator->appendNewElement($oai_dc, 
                    'dc:identifier', record_url($item,'show',true));
                
                // Also append an identifier for each file
                if(get_option('oaipmh_repository_expose_files')) {
                    $files = $item->getFiles();
                    foreach($files as $file) 
                    {
                        $generator->appendNewElement($oai_dc, 
                            'dc:identifier', $file->getWebPath('original'));
                    }
                }
            }
        }
    }
}
