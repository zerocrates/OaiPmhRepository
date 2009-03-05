<?php
/**
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 * @author John Flatness, Yu-Hsun Lin
 */

require_once('OaiPmhRepository/OaiIdentifier.php');
require_once('OaiPmhRepository/UtcDateTime.php');

/**
 * Abstract class on which all other metadata format handlers are based.
 * Includes logic for all metadata-independent record output.
 *
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
abstract class OaiPmhRepository_Metadata_Abstract
{
    /**
     * Item object for this record.
     */
    protected $item;
    
    /**
     * Parent DOMElement element for XML output.
     */
    protected $parentElement;
    
    /**
     * Owner DOMDocument of parent element.
     */
    protected $document;
    
    /**
     * Metadata_Abstract constructor
     *
     * Sets base class properties.
     *
     * @param Item item Item object whose metadata will be output.
     * @param DOMElement element Parent element for XML output.
     */
    public function __construct($item, DOMElement $element)
    {
        $this->item = $item;
        $this->parentElement = $element;
        $this->document = $element->ownerDocument;
    }
    
    /**
     * Appends the record to the XML response.
     *
     * Adds both the header and metadata elements as children of a record
     * element, which is appended to the document.
     *
     * @uses appendHeader
     * @uses appendMetadata
     */
    public function appendRecord()
    {
        $record = $this->document->createElement('record');
        $this->parentElement->appendChild($record);
        
        // Sets the parent of the next append functions
        $this->parentElement = $record;
        $this->appendHeader();
        $this->appendMetadata();
    }
    
    /**
     * Appends the record's header to the XML response.
     *
     * Adds the identifier, datestamp and setSpec to a header element, and
     * appends in to the document.  
     *
     * @uses appendHeader
     * @uses appendMetadata
     */
    public function appendHeader()
    {
        /* without access to the root document, we can directly use the
         * DOMElement constructor.  Each element cannot have children appended
         * to it util it is part of a document.
         */
         
        $header = $this->document->createElement('header');
        $this->parentElement->appendChild($header); 
         
        $identifier = $this->document->createElement('identifier',
            OaiPmhRepository_OaiIdentifier::itemToOaiId($this->item->id));
        $header->appendChild($identifier);
        
        // still yet to figure how to extract the added/modified times from DB
        $datestamp = $this->document->createElement('datestamp', 
            OaiPmhRepository_UtcDateTime::dbTimeToUtc($this->item->modified));
        $header->appendChild($datestamp);
    }
    
    abstract public function appendMetadata();
    
    abstract public function declareMetadataFormat();
}
?>
