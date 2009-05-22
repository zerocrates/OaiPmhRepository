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
        /*$dcElementNames = array( 
                                 'publisher', 'contributor',
                                 'format', 'identifier', 'source', 'language',
                                 'relation', 'coverage', 'rights' );
                                 */
        /* ====================
         * DESCRIPTIVE METADATA
         * ====================
         */

        $descriptive = OaiPmhRepository_XmlUtilities::appendNewElement($cdwalite, 'cdwalite:descriptiveMetadata');
        
        /* Type => objectWorkTypeWrap->objectWorkType 
         * Required.  Fill with 'Unknown' if omitted.
         */
        $types = $this->item->getElementTextsByElementNameAndSetName('Type', 'Dublin Core');
        $objectWorkTypeWrap = OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:objectWorkTypeWrap');
        if(count($types) == 0) $types[] = 'Unknown';
        foreach($types as $type)
        {
            OaiPmhRepository_XmlUtilities::appendNewElement($objectWorkTypeWrap, 'cdwalite:objectWorkType', $type->text);
        }      
        
        /* Subject => classificationWrap->classification
         * Not required.
         */
        $subjects = $this->item->getElementTextsByElementNameAndSetName('Subject', 'Dublin Core');
        $classificationWrap = OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:classificationWrap');
        foreach($subjects as $subject)
        {
            OaiPmhRepository_XmlUtilities::appendNewElement($classificationWrap, 'cdwalite:classification', $subject->text);
        }
        
        /* Title => titleWrap->titleSet->title
         * Required.  Fill with 'Unknown' if omitted.
         */        
        $titles = $this->item->getElementTextsByElementNameAndSetName('Title', 'Dublin Core');
        $titleWrap = OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:titleWrap');
        if(count($types) == 0) $types[] = 'Unknown';
        foreach($titles as $title)
        {
            $titleSet = OaiPmhRepository_XmlUtilities::appendNewElement($titleWrap, 'cdwalite:titleSet');
            OaiPmhRepository_XmlUtilities::appendNewElement($titleSet, 'cdwalite:title', $title->text);
        }
        
        /* Creator => displayCreator
         * Required.  Fill with 'Unknown' if omitted.
         * Non-repeatable, implode for inclusion of many creators.
         */
        $creators = $this->item->getElementTextsByElementNameAndSetName('Creator', 'Dublin Core');
        foreach($creators as $creator) $creatorTexts[] = $creator->text;
        $creatorText = count($creators) >= 1 ? implode(',', $creatorTexts) : 'Unknown';
        OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:displayCreator', $creatorText);
        
        /* Creator => indexingCreatorWrap->indexingCreatorSet->nameCreatorSet->nameCreator
         * Required.  Fill with 'Unknown' if omitted.
         * Also include roleCreator, fill with 'Unknown', required.
         */
        $indexingCreatorWrap = OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:indexingCreatorWrap');
        if(count($creators) == 0) $creators[] = 'Unknown';       
        foreach($creators as $creator) 
        {
            $indexingCreatorSet = OaiPmhRepository_XmlUtilities::appendNewElement($indexingCreatorWrap, 'cdwalite:indexingCreatorSet');
            $nameCreatorSet = OaiPmhRepository_XmlUtilities::appendNewElement($indexingCreatorSet, 'cdwalite:nameCreatorSet');
            OaiPmhRepository_XmlUtilities::appendNewElement($nameCreatorSet, 'cdwalite:nameCreator', $creator->text);
            OaiPmhRepository_XmlUtilities::appendNewElement($indexingCreatorSet, 'cdwalite:roleCreator', 'Unknown');
        }
        
        /* displayMaterialsTech
         * Required.  No corresponding metadata, fill with 'not applicable'.
         */
        OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:displayMaterialsTech', 'not applicable');
        
        /* Date => displayCreationDate
         * Required. Fill with 'Unknown' if omitted.
         * Non-repeatable, include only first date.
         */
        $dates = $this->item->getElementTextsByElementNameAndSetName('Date', 'Dublin Core');
        $dateText = count($dates) > 0 ? $dates[0]->text : 'Unknown';
        OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:displayCreationDate', $dateText);
        
        /* Date => indexingDatesWrap->indexingDatesSet
         * Map to both earliest and latest date
         * Required.  Fill with 'Unknown' if omitted.
         */
        $indexingDatesWrap = OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:indexingDatesWrap');   
        foreach($dates as $date)
        {
            $indexingDatesSet = OaiPmhRepository_XmlUtilities::appendNewElement($indexingDatesWrap, 'cdwalite:indexingDatesSet');
            OaiPmhRepository_XmlUtilities::appendNewElement($indexingDatesSet, 'cdwalite:earliestDate', $date->text);
            OaiPmhRepository_XmlUtilities::appendNewElement($indexingDatesSet, 'cdwalite:latestDate', $date->text);
        }
        
        /* locationWrap->locationSet->locationName
         * Required. No corresponding metadata, fill with 'location unknown'.
         */
        $locationWrap = OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:locationWrap');
        $locationSet = OaiPmhRepository_XmlUtilities::appendNewElement($locationWrap, 'cdwalite:locationSet');
        OaiPmhRepository_XmlUtilities::appendNewElement($locationSet, 'cdwalite:locationName', 'location unknown');
        
        /* Description => descriptiveNoteWrap->descriptiveNoteSet->descriptiveNote
         * Not required.
         */
        $descriptions = $this->item->getElementTextsByElementNameAndSetName('Description', 'Dublin Core');
        if(count($descriptions) > 0)
        {
            $descriptiveNoteWrap = OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:descriptiveNoteWrap');
            foreach($descriptions as $description)
            {
                $descriptiveNoteSet = OaiPmhRepository_XmlUtilities::appendNewElement($descriptiveNoteWrap, 'cdwalite:descriptiveNoteSet');
                OaiPmhRepository_XmlUtilities::appendNewElement($descriptiveNoteSet, 'cdwalite:descriptiveNote', $description->text);
            }
        }
        
        /* =======================
         * ADMINISTRATIVE METADATA
         * =======================
         */
         
        $administrative = OaiPmhRepository_XmlUtilities::appendNewElement($cdwalite, 'cdwalite:administrativeMetadata');
        
        /* Rights => rightsWork
         * Not required.
         */
        $rights = $this->item->getElementTextsByElementNameAndSetName('Rights', 'Dublin Core');
        foreach($rights as $right)
        {
            OaiPmhRepository_XmlUtilities::appendNewElement($administrative, 'cdwalite:rightsWork', $right->text);
        }
        
        /* id => recordWrap->recordID
         * 'item' => recordWrap-recordType
         * Required.
         */     
        $recordWrap = OaiPmhRepository_XmlUtilities::appendNewElement($descriptive, 'cdwalite:recordWrap');
        OaiPmhRepository_XmlUtilities::appendNewElement($recordWrap, 'cdwalite:recordID', $this->item->id);
        OaiPmhRepository_XmlUtilities::appendNewElement($recordWrap, 'cdwalite:recordType', 'item');
        $recordMetadataWrap = OaiPmhRepository_XmlUtilities::appendNewElement($recordWrap, 'cdwalite:recordMetadataWrap');
        $recordInfoID = OaiPmhRepository_XmlUtilities::appendNewElement($recordMetadataWrap, 'cdwalite:recordInfoID', OaiPmhRepository_OaiIdentifier::itemToOaiId($this->item->id));
        $recordInfoID->setAttribute('type', 'oai');
        
        /* file link => resourceWrap->resourceSet->linkResource
         * Not required.
         */
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