<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
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
    /* These three variables must be set to the correct values, with these exact
       variable names, in any metadata mapping classes.  The abstract class uses
       these to build the list of available formats. These are variables, not
       constants because of limitations on their access from parent classes. */
    public $metadataPrefix = 'oai_dc';    
    protected $metadataNamespaceUri = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    protected $metadataSchemaUri = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    
    const DC_NAMESPACE_URI = 'http://purl.org/dc/elements/1.1/';
    
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
            $this->metadataNamespaceUri, 'oai_dc:dc');
        $metadataElement->appendChild($oai_dc);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $oai_dc->setAttribute('xmlns:dc', self::DC_NAMESPACE_URI);
        $oai_dc->setAttribute('xmlns:xsi', XML_SCHEMA_NAMESPACE_URI);
        $oai_dc->setAttribute('xsi:schemaLocation', self::DC_NAMESPACE_URI.' '.
            $this->metadataSchemaUri);

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
}
