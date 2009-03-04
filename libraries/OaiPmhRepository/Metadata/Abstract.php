<?php

require_once('OaiPmhRepository/OaiIdentifier.php');
require_once('OaiPmhRepository/UtcDateTime.php');

abstract class OaiPmhRepository_Metadata_Abstract
{
    protected $item;
    protected $parentElement;
    protected $document;
    
    public function __construct($item, $element)
    {
        $this->item = $item;
        $this->parentElement = $element;
        $this->document = $element->ownerDocument;
        /*
        $record = $this->document->createElement('record');
        $element->appendChild($record);

        $header = $this->document->createElement('header');
        $record->appendChild($header);
        $this->generateHeader($header);

        $metadata = $this->document->createElement('metadata');
        $record->appendChild($metadata);
        $this->generateMetadata($metadata);
        */
    }
    
    /*protected function getItem()
    {
        return $this->item;
    }
    
    protected function getParentElement()
    {
        return $this->parentElement
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
    
    //abstract function declareMetadatFormat($parentElement);
}
?>
