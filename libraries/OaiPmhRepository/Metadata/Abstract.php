<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once('OaiPmhRepository/OaiXmlGeneratorAbstract.php');
require_once('OaiPmhRepository/OaiIdentifier.php');

/**
 * Abstract class on which all other metadata format handlers are based.
 * Includes logic for all metadata-independent record output.
 *
 * @todo Migration to PHP 5.3 will allow the abstract getter functions to be
 *       static, as they should be.
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
abstract class OaiPmhRepository_Metadata_Abstract extends OaiPmhRepository_OaiXmlGeneratorAbstract
{   
    /**
     * Item object for this record.
     * @var Item
     */
    protected $item;

    /**
     * Document to append to.
     * @var DOMDocument
     */
    protected $document;
    
    /**
     * Metadata_Abstract constructor
     *
     * Sets base class properties.
     *
     * @param Item item Item object whose metadata will be output.
     * @param DOMDocument $document
     */
    public function __construct($item = null, $document = null)
    {
        if($item) {
            $this->item = $item;
        }
        if($document) {
            $this->document = $document;
        }
    }
    
    /**
     * Appends the record to the XML response.
     *
     * Adds both the header and metadata elements as children of a record
     * element, which is appended to the document.
     *
     * @uses appendHeader
     * @uses appendMetadata
     * @param DOMElement $parentElement
     */
    public function appendRecord($parentElement)
    {
        $record = $this->document->createElement('record');
        $parentElement->appendChild($record);
        $this->appendHeader($record);
        
        $metadata = $this->document->createElement('metadata');
        $record->appendChild($metadata);
        $this->appendMetadata($metadata);
    }
    
    /**
     * Appends the record's header to the XML response.
     *
     * Adds the identifier, datestamp and setSpec to a header element, and
     * appends in to the document.
     *
     * @param DOMElement $parentElement
     */
    public function appendHeader($parentElement)
    {
        $headerData['identifier'] = 
            OaiPmhRepository_OaiIdentifier::itemToOaiId($this->item->id);
        $headerData['datestamp'] = OaiPmhRepository_Date::dbToUtc($this->item->modified);
        
        $collectionId = $this->item->collection_id;
        if ($collectionId)
            $headerData['setSpec'] = $collectionId;
        
        $this->createElementWithChildren($parentElement, 'header', $headerData);
    }
    
    /**
     * Appends a metadataFormat element to the document. 
     *
     * Declares the metadataPrefix, schema URI, and namespace for the oai_dc
     * metadata format.
     *
     * @param DOMElement $parentElement
     */    
    public function declareMetadataFormat($parentElement)
    {
        $elements = array( 'metadataPrefix'    => $this->getMetadataPrefix(),
                           'schema'            => $this->getMetadataSchema(),
                           'metadataNamespace' => $this->getMetadataNamespace() );
        $this->createElementWithChildren($parentElement, 'metadataFormat', $elements);
    }
    
    /**
     * Returns the OAI-PMH metadata prefix for the output format.
     *
     * @return string Metadata prefix
     */
    abstract public function getMetadataPrefix();
    
    /**
     * Returns the XML schema for the output format.
     *
     * @return string XML schema URI
     */
    abstract public function getMetadataSchema();
    
    /**
     * Returns the XML namespace for the output format.
     *
     * @return string XML namespace URI
     */
    abstract public function getMetadataNamespace();
    
    /**
     * Appends the metadata for one Omeka item to the XML document.
     *
     * @param DOMElement $parentElement
     */
    abstract public function appendMetadata($parentElement);
}
