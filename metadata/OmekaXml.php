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
class OaiPmhRepository_Metadata_OmekaXml extends OaiPmhRepository_Metadata_Abstract
{
    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'omeka-xml';

    /**
     * Appends Dublin Core metadata.
     *
     * Appends a metadata element, an child element with the required format,
     * and further children for each of the Dublin Core fields present in the
     * item.
     */
    public function appendMetadata($metadataElement)
    {
  
        $omekaXml = new Output_ItemOmekaXml($this->item, 'item');

        $node = $omekaXml->getDoc()->documentElement;
        $node = $this->document->importNode($node, true);

        $metadataElement->appendChild($node);
    }

    /**
     * Returns the OAI-PMH metadata prefix for the output format.
     *
     * @return string Metadata prefix
     */
    public function getMetadataPrefix()
    {
        return self::METADATA_PREFIX;
    }

    /**
     * Returns the XML schema for the output format.
     *
     * @return string XML schema URI
     */
    public function getMetadataSchema()
    {
        return Omeka_Output_OmekaXml_AbstractOmekaXml::XMLNS_SCHEMALOCATION;
    }

    /**
     * Returns the XML namespace for the output format.
     *
     * @return string XML namespace URI
     */
    public function getMetadataNamespace()
    {
        return Omeka_Output_OmekaXml_AbstractOmekaXml::XMLNS;
    }
}

