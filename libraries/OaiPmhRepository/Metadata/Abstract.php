<?php

include_once('OaiPmhRepository/OaiIdentifier.php');

abstract class OaiPmhRepository_Metadata_Abstract
{
    private $item;
    private $parentElement;

    public function __construct(&$element, $itemId)
    {
        $this->item = new Item(get_db());
        $this->item->id = $itemId;
        $this->parentElement =& $element;
        
        $this->appendHeader();
    }
    
    protected function appendHeader()
    {
        /* without access to the root document, we must directly use the
         * DOMElement constructor.  Each element cannot have children appended
         * to it util it is part of a document.
         */
        $header = new DOMElement('header');
        $this->parentElement->appendChild($header);
        
        $identifier = new DOMElement('identifier',
            OaiPmhRepository_OaiIdentifier::itemToOaiId($this->item->id));
        $header->appendChild($identifier);
        
        // still yet to figure how to extract the added/modified times from DB
        $datestamp = new DOMElement('datestamp', 'unimplemented');
        $header->appendChild($datestamp);
    }
    
    abstract function appendMetadata();
}
