<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once('Abstract.php');
require_once HELPERS;

/**
 * Class implmenting metadata output CDWA Lite.
 *
 * @link http://www.getty.edu/research/conducting_research/standards/cdwa/cdwalite.html
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
class OaiPmhRepository_Metadata_CdwaLite extends OaiPmhRepository_Metadata_Abstract
{
    /* These three variables must be set to the correct values, with these exact
       variable names, in any metadata mapping classes.  The abstract class uses
       these to build the list of available formats. These are variables, not
       constants because of limitations on their access from parent classes. */
    public $metadataPrefix = 'cdwalite';    
    protected $metadataNamespaceUri = 'http://www.getty.edu/CDWA/CDWALite';
    protected $metadataSchemaUri = 'http://www.getty.edu/CDWA/CDWALite/CDWALite-xsd-public-v1-1.xsd';
    
    /**
     * Appends CDWALite metadata. 
     *
     * Appends a metadata element, an child element with the required format,
     * and further children for each of the Dublin Core fields present in the
     * item.
     */
    public function appendMetadata() 
    {
        $metadataElement = $this->document->createElement('metadata');
        $this->parentElement->appendChild($metadataElement);   
        
        $cdwaliteWrap = $this->document->createElementNS(
            $this->metadataNamespaceUri, 'cdwalite:cdwaliteWrap');
        $metadataElement->appendChild($cdwaliteWrap);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $cdwaliteWrap->setAttribute('xmlns:cdwalite', $this->metadataNamespaceUri);
        $cdwaliteWrap->setAttribute('xmlns:xsi', XML_SCHEMA_NAMESPACE_URI);
        $cdwaliteWrap->setAttribute('xsi:schemaLocation', $this->metadataNamespaceUri.' '.
            $this->metadataSchemaUri);
            
        $cdwalite = OaiPmhRepository_XmlUtilities::appendNewElement($cdwaliteWrap, 'cdwalite:cdwalite');
        /* Each of the 16 unqualified Dublin Core elements, in the order
         * specified by the oai_dc XML schema
         */
        $dcElementNames = array( 'title', 'creator', 'subject', 'description',
                                 'publisher', 'contributor', 'date', 'type',
                                 'format', 'identifier', 'source', 'language',
                                 'relation', 'coverage', 'rights' );

        $descriptive = OaiPmhRepository_XmlUtilities::appendNewElement($cdwalite, 'cdwalite:descriptiveMetadata');
        
        $titles = $this->item->getElementTextsByElementNameAndSetName('Title', 'Dublin Core');
        $titleWrap = OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:titleWrap');
        foreach($titles as $title)
        {
            $titleSet = OaiPmhRepository_XmlUtilities::appendNewElement($titleWrap, 'cdwalite:titleSet');
            OaiPmhRepository_XmlUtilities::appendNewElement($titleSet, 'cdwalite:title', $title->text);
        }
        
        //displayCreator is non-repeatable
        $creators = $this->item->getElementTextsByElementNameAndSetName('Creator', 'Dublin Core');
        if(count($creators) >= 1)
        {
            OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:displayCreator', $creators[0]->text);
        }
        
        $administrative = OaiPmhRepository_XmlUtilities::appendNewElement($cdwalite, 'cdwalite:administrativeMetadata');
        
        // Also append an identifier for each file
        if(get_option('oaipmh_repository_expose_files')) {
            $files = $this->item->getFiles();
            if(count($files) > 0) {
                $resourceWrap = OaiPmhRepository_XmlUtilities::appendNewElement($administrative, 'cdwalite:resourceWrap');
                foreach($files as $file) 
                {
                    $resourceSet = OaiPmhRepository_XmlUtilities::appendNewElement($resourceWrap, 'cdwalite:resourceSet');
                    OaiPmhRepository_XmlUtilities::appendNewElement($resourceSet, 
                        'cdwalite:linkResource', $file->getWebPath('archive'));
                }
            }
        }
    }
}
