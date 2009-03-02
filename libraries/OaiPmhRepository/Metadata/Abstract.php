<?php

require_once('OaiPmhRepository/OaiIdentifier.php');
require_once('OaiPmhRepository/UtcDateTime.php');

abstract class OaiPmhRepository_Metadata_Abstract
{
    public function __construct($response, $element, $itemId)
    {
        $itemTable = new ItemTable('Item', get_db());
        $select = $itemTable->getSelect();
        $itemTable->filterByRange($select, $itemId);
        $item = $itemTable->fetchObject($select);

        if(!isset($item->id))
        {
            // remove the response element
            $element->parentNode->removeChild($element);
            OaiPmhRepository_Error::throwError($response, OAI_ERR_ID_DOES_NOT_EXIST);
            return;
        }
        
        $header = new DOMElement('header');
        $element->appendChild($header);
        $this->generateHeader($header, $item);

        $metadata = new DOMElement('metadata');
        $element->appendChild($metadata);
        $this->generateMetadata($metadata, $item);
    }
    
    protected function generateHeader($headerElement, $item)
    {
        /* without access to the root document, we can directly use the
         * DOMElement constructor.  Each element cannot have children appended
         * to it util it is part of a document.
         */
        $identifier = new DOMElement('identifier',
            OaiPmhRepository_OaiIdentifier::itemToOaiId($item->id));
        $headerElement->appendChild($identifier);
        
        // still yet to figure how to extract the added/modified times from DB
        $datestamp = new DOMElement('datestamp', 
            OaiPmhRepository_UtcDateTime::dbTimeToUtc($item->modified));
        $headerElement->appendChild($datestamp);
    }
    
    abstract function generateMetadata($metadataElement, $item);
}
