<?php

include_once('Abstract.php');

class OaiPmhRepository_Metadata_OaiDc extends OaiPmhRepository_Metadata_Abstract
{
    const OAI_DC_NAMESPACE_URI = 'http://www.openarchives.org/OAI/2.0/oai_dc';
    const DC_NAMEPSACE_URI = 'http://purl.org/dc/elements/1.1';

    const XML_SCHEMA_URI = 'http://www.w3.org/2001/XMLSchema-instance';
    const OAI_DC_SCHEMA_URI = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';

    public function generateMetadata($metadataElement) 
    {
        $oai_dc = new DOMElement('oai_dc:dc', '', self::OAI_DC_NAMESPACE_URI);
        $metadataElement->appendChild($oai_dc);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $oai_dc->setAttribute('xmlns:xsi', self::XML_SCHEMA_URI);
        $oai_dc->setAttribute('xsi:schemaLocation', self::OAI_DC_SCHEMA_URI);
    }
}
