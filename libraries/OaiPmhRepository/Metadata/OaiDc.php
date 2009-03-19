<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author John Flatness, Yu-Hsun Lin
 */

require_once('Abstract.php');

/**
 * Class implmenting metadata output for the required oai_dc metadata format.
 * oai_dc is output of the 15 unqualified Dublin Core fields.
 *
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
class OaiPmhRepository_Metadata_OaiDc extends OaiPmhRepository_Metadata_Abstract
{
    const METADATA_PREFIX = 'oai_dc';    
    
    const OAI_DC_NAMESPACE_URI = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    const DC_NAMESPACE_URI = 'http://purl.org/dc/elements/1.1/';

    const XML_SCHEMA_URI = 'http://www.w3.org/2001/XMLSchema-instance';
    const OAI_DC_SCHEMA_URI = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    
    /**
     * Appends Dublin Core metadata. 
     *
     * Appends a metadata element, an child element with the required format,
     * and further children for each of the Dublin Core fields present in the
     * item.
     */
    public function appendMetadata() 
    {
        $metadataElement = $this->document->createElement('metadata');
        $this->parentElement->appendChild($metadataElement);   
        
        $oai_dc = $this->document->createElementNS(
            self::OAI_DC_NAMESPACE_URI, 'oai_dc:dc');
        $metadataElement->appendChild($oai_dc);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $oai_dc->setAttribute('xmlns:dc', self::DC_NAMESPACE_URI);
        $oai_dc->setAttribute('xmlns:xsi', self::XML_SCHEMA_URI);
        $oai_dc->setAttribute('xsi:schemaLocation', self::DC_NAMESPACE_URI.' '.
            self::OAI_DC_SCHEMA_URI);

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
            $dcElements = $this->item->getElementTextsByElementNameAndSetName(
                $upperName, 'Dublin Core');
            foreach($dcElements as $elementText) {
                $dcElement = $this->document->createElement('dc:'.$elementName);
                // Use a TextNode, causes escaping of input text
                $text = $this->document->createTextNode($elementText->text);
                $dcElement->appendChild($text);
                $oai_dc->appendChild($dcElement);
            }
        }
    }
    
    /**
     * Appends a metadataFormat element to the document. 
     *
     * Declares the metadataPrefix, schema URI, and namespace for the oai_dc
     * metadata format.
     */    
    public function declareMetadataFormat()
    {
        $elements = array( 'metadataPrefix'    => self::METADATA_PREFIX,
                           'schema'            => self::OAI_DC_SCHEMA_URI,
                           'metadataNamespace' => self::DC_NAMESPACE_URI );
        OaiPmhRepository_XmlUtilities::createElementWithChildren(
            $this->parentElement, 'metadataFormat', $elements);
    }
}
