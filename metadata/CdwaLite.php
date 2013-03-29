<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author John Flatness
 * @copyright Copyright 2009 John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */


/**
 * Class implmenting metadata output CDWA Lite.
 *
 * @link http://www.getty.edu/research/conducting_research/standards/cdwa/cdwalite.html
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
class OaiPmhRepository_Metadata_CdwaLite extends OaiPmhRepository_Metadata_Abstract
{
    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'cdwalite';    
    
    /** XML namespace for output format */
    const METADATA_NAMESPACE = 'http://www.getty.edu/CDWA/CDWALite';
    
    /** XML schema for output format */
    const METADATA_SCHEMA = 'http://www.getty.edu/CDWA/CDWALite/CDWALite-xsd-public-v1-1.xsd';
    
    /**
     * Appends CDWALite metadata. 
     *
     * Appends a metadata element, an child element with the required format,
     * and further children for each of the Dublin Core fields present in the
     * item.
     */
    public function appendMetadata($metadataElement) 
    {
        $cdwaliteWrap = $this->document->createElementNS(
            self::METADATA_NAMESPACE, 'cdwalite:cdwaliteWrap');
        $metadataElement->appendChild($cdwaliteWrap);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $cdwaliteWrap->setAttribute('xmlns:cdwalite', self::METADATA_NAMESPACE);
        $cdwaliteWrap->setAttribute('xmlns:xsi', self::XML_SCHEMA_NAMESPACE_URI);
        $cdwaliteWrap->setAttribute('xsi:schemaLocation', self::METADATA_NAMESPACE
            .' '.self::METADATA_SCHEMA);
            
        $cdwalite = $this->appendNewElement($cdwaliteWrap, 'cdwalite:cdwalite');
        
        /* ====================
         * DESCRIPTIVE METADATA
         * ====================
         */

        $descriptive = $this->appendNewElement($cdwalite, 'cdwalite:descriptiveMetadata');
        
        /* Type => objectWorkTypeWrap->objectWorkType 
         * Required.  Fill with 'Unknown' if omitted.
         */
        $types = $this->item->getElementTexts('Dublin Core','Type');
        $objectWorkTypeWrap = $this->appendNewElement($descriptive, 'cdwalite:objectWorkTypeWrap');
        //print_r($objectWorkTypeWrap);
        if(count($types) == 0) $types[] = 'Unknown'; 
        foreach($types as $type)
        {  
            $this->appendNewElement($objectWorkTypeWrap, 'cdwalite:objectWorkTypeWrap', ($type == 'Unknown')? $type: $type->text );

        }
        
        /* Title => titleWrap->titleSet->title
         * Required.  Fill with 'Unknown' if omitted.
         */        
        $titles = $this->item->getElementTexts('Dublin Core','Title');
        $titleWrap = $this->appendNewElement($descriptive, 'cdwalite:titleWrap');
        if(count($types) == 0) $types[] = 'Unknown';
        foreach($titles as $title)
        {
            $titleSet = $this->appendNewElement($titleWrap, 'cdwalite:titleSet');
            $this->appendNewElement($titleSet, 'cdwalite:title', $title->text);
        }

        /* Creator => displayCreator
         * Required.  Fill with 'Unknown' if omitted.
         * Non-repeatable, implode for inclusion of many creators.
         */
        $creators = $this->item->getElementTexts('Dublin Core','Creator');

        $creatorTexts = array();
        foreach($creators as $creator) $creatorTexts[] = $creator->text;
        if (count($creatorTexts) == 0) $creatorTexts[] = 'Unknown';
        
        $creatorText = implode(', ', $creatorTexts);
        $this->appendNewElement($descriptive, 'cdwalite:displayCreator', $creatorText);
        
        /* Creator => indexingCreatorWrap->indexingCreatorSet->nameCreatorSet->nameCreator
         * Required.  Fill with 'Unknown' if omitted.
         * Also include roleCreator, fill with 'Unknown', required.
         */
        $indexingCreatorWrap = $this->appendNewElement($descriptive, 'cdwalite:indexingCreatorWrap');
        foreach($creatorTexts as $creator) 
        {
            $indexingCreatorSet = $this->appendNewElement($indexingCreatorWrap, 'cdwalite:indexingCreatorSet');
            $nameCreatorSet = $this->appendNewElement($indexingCreatorSet, 'cdwalite:nameCreatorSet');
            $this->appendNewElement($nameCreatorSet, 'cdwalite:nameCreator', $creator);
            $this->appendNewElement($indexingCreatorSet, 'cdwalite:roleCreator', 'Unknown');
        }
        
        /* displayMaterialsTech
         * Required.  No corresponding metadata, fill with 'not applicable'.
         */
        $this->appendNewElement($descriptive, 'cdwalite:displayMaterialsTech', 'not applicable');
        
        /* Date => displayCreationDate
         * Required. Fill with 'Unknown' if omitted.
         * Non-repeatable, include only first date.
         */
        $dates = $this->item->getElementTexts('Dublin Core','Date');
        $dateText = count($dates) > 0 ? $dates[0]->text : 'Unknown';
        $this->appendNewElement($descriptive, 'cdwalite:displayCreationDate', $dateText);
        
        /* Date => indexingDatesWrap->indexingDatesSet
         * Map to both earliest and latest date
         * Required.  Fill with 'Unknown' if omitted.
         */
        $indexingDatesWrap = $this->appendNewElement($descriptive, 'cdwalite:indexingDatesWrap');   
        foreach($dates as $date)
        {
            $indexingDatesSet = $this->appendNewElement($indexingDatesWrap, 'cdwalite:indexingDatesSet');
            $this->appendNewElement($indexingDatesSet, 'cdwalite:earliestDate', $date->text);
            $this->appendNewElement($indexingDatesSet, 'cdwalite:latestDate', $date->text);
        }
        
        /* locationWrap->locationSet->locationName
         * Required. No corresponding metadata, fill with 'location unknown'.
         */
        $locationWrap = $this->appendNewElement($descriptive, 'cdwalite:locationWrap');
        $locationSet = $this->appendNewElement($locationWrap, 'cdwalite:locationSet');
        $this->appendNewElement($locationSet, 'cdwalite:locationName', 'location unknown');

        /* Subject => classWrap->classification
         * Not required.
         */
        $subjects = $this->item->getElementTexts('Dublin Core','Subject');
        $classWrap = $this->appendNewElement($descriptive, 'cdwalite:classWrap');
        foreach($subjects as $subject)
        {
            $this->appendNewElement($classWrap, 'cdwalite:classification', $subject->text);
        }
        
        /* Description => descriptiveNoteWrap->descriptiveNoteSet->descriptiveNote
         * Not required.
         */
        $descriptions = $this->item->getElementTexts('Dublin Core','Description');
        if(count($descriptions) > 0)
        {
            $descriptiveNoteWrap = $this->appendNewElement($descriptive, 'cdwalite:descriptiveNoteWrap');
            foreach($descriptions as $description)
            {
                $descriptiveNoteSet = $this->appendNewElement($descriptiveNoteWrap, 'cdwalite:descriptiveNoteSet');
                $this->appendNewElement($descriptiveNoteSet, 'cdwalite:descriptiveNote', $description->text);
            }
        }
        
        /* =======================
         * ADMINISTRATIVE METADATA
         * =======================
         */
         
        $administrative = $this->appendNewElement($cdwalite, 'cdwalite:administrativeMetadata');
        
        /* Rights => rightsWork
         * Not required.
         */
        $rights = $this->item->getElementTexts('Dublin Core','Rights');
        foreach($rights as $right)
        {
            $this->appendNewElement($administrative, 'cdwalite:rightsWork', $right->text);
        }
        
        /* id => recordWrap->recordID
         * 'item' => recordWrap-recordType
         * Required.
         */     
        $recordWrap = $this->appendNewElement($administrative, 'cdwalite:recordWrap');
        $this->appendNewElement($recordWrap, 'cdwalite:recordID', $this->item->id);
        $this->appendNewElement($recordWrap, 'cdwalite:recordType', 'item');
        $recordInfoWrap = $this->appendNewElement($recordWrap, 'cdwalite:recordInfoWrap');
        $recordInfoID = $this->appendNewElement($recordInfoWrap, 'cdwalite:recordInfoID', OaiPmhRepository_OaiIdentifier::itemToOaiId($this->item->id));
        $recordInfoID->setAttribute('cdwalite:type', 'oai');
        
        /* file link => resourceWrap->resourceSet->linkResource
         * Not required.
         */
        if(get_option('oaipmh_repository_expose_files')) {
            $files = $this->item->getFiles();
            if(count($files) > 0) {
                $resourceWrap = $this->appendNewElement($administrative, 'cdwalite:resourceWrap');
                foreach($files as $file) 
                {
                    $resourceSet = $this->appendNewElement($resourceWrap, 'cdwalite:resourceSet');
                    $this->appendNewElement($resourceSet, 
                        'cdwalite:linkResource',$file->getWebPath('original'));
                }
            }
        }
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
        return self::METADATA_SCHEMA;
    }
    
    /**
     * Returns the XML namespace for the output format.
     *
     * @return string XML namespace URI
     */
    public function getMetadataNamespace()
    {
        return self::METADATA_NAMESPACE;
    }
}
