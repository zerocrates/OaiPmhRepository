<?php

require_once('Abstract.php');

class OaiPmhRepository_Metadata_OaiDc extends OaiPmhRepository_Metadata_Abstract
{
    const OAI_DC_NAMESPACE_URI = 'http://www.openarchives.org/OAI/2.0/oai_dc';
    const DC_NAMESPACE_URI = 'http://purl.org/dc/elements/1.1';

    const XML_SCHEMA_URI = 'http://www.w3.org/2001/XMLSchema-instance';
    const OAI_DC_SCHEMA_URI = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';

    public function generateMetadata($metadataElement, $item) 
    {
        $oai_dc = $metadataElement->ownerDocument->createElementNS(
            self::OAI_DC_NAMESPACE_URI, 'oai_dc:dc');
        $metadataElement->appendChild($oai_dc);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $oai_dc->setAttribute('xmlns:dc', self::DC_NAMESPACE_URI);
        $oai_dc->setAttribute('xmlns:xsi', self::XML_SCHEMA_URI);
        $oai_dc->setAttribute('xsi:schemaLocation', self::OAI_DC_SCHEMA_URI);

        $dcElementNames = array('contributor', 'coverage', 'creator', 'date',
                                'description', 'format', 'identifier', 
                                'language', 'publisher', 'relation', 'rights',
                                'source', 'subject', 'title', 'type');

        /* Must create elements using createElement to make DOM allow a
         * top-level xmlns declaration instead of wasteful and non-
         * compliant per-node declarations.
         */
        foreach($dcElementNames as $elementName)
        {   
            $upperName = Inflector::camelize($elementName);
            $dcElements = $item->getElementTextsByElementNameAndSetName(
                $upperName, 'Dublin Core');
            foreach($dcElements as $elementText) {
                $dcElement = $metadataElement->ownerDocument->createElement(
                    'dc:'.$elementName, $elementText->text);
                $oai_dc->appendChild($dcElement);
            }
        }
    }
}
