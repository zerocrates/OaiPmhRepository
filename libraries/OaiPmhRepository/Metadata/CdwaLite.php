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
class OaiPmhRepository_Metadata_CdwaLite implements OaiPmhRepository_Metadata_FormatInterface
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
    public function appendMetadata($item, $metadataElement, $generator)
    {
        $document = $generator->getDocument();
        $cdwaliteWrap = $document->createElementNS(
            self::METADATA_NAMESPACE, 'cdwalite:cdwaliteWrap');
        $metadataElement->appendChild($cdwaliteWrap);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $cdwaliteWrap->setAttribute('xmlns:cdwalite', self::METADATA_NAMESPACE);
        $cdwaliteWrap->setAttribute('xmlns:xsi', OaiPmhRepository_XmlGeneratorAbstract::XML_SCHEMA_NAMESPACE_URI);
        $cdwaliteWrap->setAttribute('xsi:schemaLocation', self::METADATA_NAMESPACE
            .' '.self::METADATA_SCHEMA);
            
        $cdwalite = $generator->appendNewElement($cdwaliteWrap, 'cdwalite:cdwalite');
        
        /* ====================
         * DESCRIPTIVE METADATA
         * ====================
         */

        $descriptive = $generator->appendNewElement($cdwalite, 'cdwalite:descriptiveMetadata');
        
        /* Type => objectWorkTypeWrap->objectWorkType 
         * Required.  Fill with 'Unknown' if omitted.
         */
        $types = $item->getElementTexts('Dublin Core','Type');
        $objectWorkTypeWrap = $generator->appendNewElement($descriptive, 'cdwalite:objectWorkTypeWrap');
        //print_r($objectWorkTypeWrap);
        if(count($types) == 0) $types[] = 'Unknown'; 
        foreach($types as $type)
        {  
            $generator->appendNewElement($objectWorkTypeWrap, 'cdwalite:objectWorkTypeWrap', ($type == 'Unknown')? $type: $type->text );

        }
        
        /* Title => titleWrap->titleSet->title
         * Required.  Fill with 'Unknown' if omitted.
         */        
        $titles = $item->getElementTexts('Dublin Core','Title');
        $titleWrap = $generator->appendNewElement($descriptive, 'cdwalite:titleWrap');
        if(count($types) == 0) $types[] = 'Unknown';
        foreach($titles as $title)
        {
            $titleSet = $generator->appendNewElement($titleWrap, 'cdwalite:titleSet');
            $generator->appendNewElement($titleSet, 'cdwalite:title', $title->text);
        }

        /* Creator => displayCreator
         * Required.  Fill with 'Unknown' if omitted.
         * Non-repeatable, implode for inclusion of many creators.
         */
        $creators = $item->getElementTexts('Dublin Core','Creator');

        $creatorTexts = array();
        foreach($creators as $creator) $creatorTexts[] = $creator->text;
        if (count($creatorTexts) == 0) $creatorTexts[] = 'Unknown';
        
        $creatorText = implode(', ', $creatorTexts);
        $generator->appendNewElement($descriptive, 'cdwalite:displayCreator', $creatorText);
        
        /* Creator => indexingCreatorWrap->indexingCreatorSet->nameCreatorSet->nameCreator
         * Required.  Fill with 'Unknown' if omitted.
         * Also include roleCreator, fill with 'Unknown', required.
         */
        $indexingCreatorWrap = $generator->appendNewElement($descriptive, 'cdwalite:indexingCreatorWrap');
        foreach($creatorTexts as $creator) 
        {
            $indexingCreatorSet = $generator->appendNewElement($indexingCreatorWrap, 'cdwalite:indexingCreatorSet');
            $nameCreatorSet = $generator->appendNewElement($indexingCreatorSet, 'cdwalite:nameCreatorSet');
            $generator->appendNewElement($nameCreatorSet, 'cdwalite:nameCreator', $creator);
            $generator->appendNewElement($indexingCreatorSet, 'cdwalite:roleCreator', 'Unknown');
        }
        
        /* displayMaterialsTech
         * Required.  No corresponding metadata, fill with 'not applicable'.
         */
        $generator->appendNewElement($descriptive, 'cdwalite:displayMaterialsTech', 'not applicable');
        
        /* Date => displayCreationDate
         * Required. Fill with 'Unknown' if omitted.
         * Non-repeatable, include only first date.
         */
        $dates = $item->getElementTexts('Dublin Core','Date');
        $dateText = count($dates) > 0 ? $dates[0]->text : 'Unknown';
        $generator->appendNewElement($descriptive, 'cdwalite:displayCreationDate', $dateText);
        
        /* Date => indexingDatesWrap->indexingDatesSet
         * Map to both earliest and latest date
         * Required.  Fill with 'Unknown' if omitted.
         */
        $indexingDatesWrap = $generator->appendNewElement($descriptive, 'cdwalite:indexingDatesWrap');   
        foreach($dates as $date)
        {
            $indexingDatesSet = $generator->appendNewElement($indexingDatesWrap, 'cdwalite:indexingDatesSet');
            $generator->appendNewElement($indexingDatesSet, 'cdwalite:earliestDate', $date->text);
            $generator->appendNewElement($indexingDatesSet, 'cdwalite:latestDate', $date->text);
        }
        
        /* locationWrap->locationSet->locationName
         * Required. No corresponding metadata, fill with 'location unknown'.
         */
        $locationWrap = $generator->appendNewElement($descriptive, 'cdwalite:locationWrap');
        $locationSet = $generator->appendNewElement($locationWrap, 'cdwalite:locationSet');
        $generator->appendNewElement($locationSet, 'cdwalite:locationName', 'location unknown');

        /* Subject => classWrap->classification
         * Not required.
         */
        $subjects = $item->getElementTexts('Dublin Core','Subject');
        $classWrap = $generator->appendNewElement($descriptive, 'cdwalite:classWrap');
        foreach($subjects as $subject)
        {
            $generator->appendNewElement($classWrap, 'cdwalite:classification', $subject->text);
        }
        
        /* Description => descriptiveNoteWrap->descriptiveNoteSet->descriptiveNote
         * Not required.
         */
        $descriptions = $item->getElementTexts('Dublin Core','Description');
        if(count($descriptions) > 0)
        {
            $descriptiveNoteWrap = $generator->appendNewElement($descriptive, 'cdwalite:descriptiveNoteWrap');
            foreach($descriptions as $description)
            {
                $descriptiveNoteSet = $generator->appendNewElement($descriptiveNoteWrap, 'cdwalite:descriptiveNoteSet');
                $generator->appendNewElement($descriptiveNoteSet, 'cdwalite:descriptiveNote', $description->text);
            }
        }
        
        /* =======================
         * ADMINISTRATIVE METADATA
         * =======================
         */
         
        $administrative = $generator->appendNewElement($cdwalite, 'cdwalite:administrativeMetadata');
        
        /* Rights => rightsWork
         * Not required.
         */
        $rights = $item->getElementTexts('Dublin Core','Rights');
        foreach($rights as $right)
        {
            $generator->appendNewElement($administrative, 'cdwalite:rightsWork', $right->text);
        }
        
        /* id => recordWrap->recordID
         * 'item' => recordWrap-recordType
         * Required.
         */     
        $recordWrap = $generator->appendNewElement($administrative, 'cdwalite:recordWrap');
        $generator->appendNewElement($recordWrap, 'cdwalite:recordID', $item->id);
        $generator->appendNewElement($recordWrap, 'cdwalite:recordType', 'item');
        $recordInfoWrap = $generator->appendNewElement($recordWrap, 'cdwalite:recordInfoWrap');
        $recordInfoID = $generator->appendNewElement($recordInfoWrap, 'cdwalite:recordInfoID', OaiPmhRepository_OaiIdentifier::itemToOaiId($item->id));
        $recordInfoID->setAttribute('cdwalite:type', 'oai');
        
        /* file link => resourceWrap->resourceSet->linkResource
         * Not required.
         */
        if(get_option('oaipmh_repository_expose_files')) {
            $files = $item->getFiles();
            if(count($files) > 0) {
                $resourceWrap = $generator->appendNewElement($administrative, 'cdwalite:resourceWrap');
                foreach($files as $file) 
                {
                    $resourceSet = $generator->appendNewElement($resourceWrap, 'cdwalite:resourceSet');
                    $generator->appendNewElement($resourceSet, 
                        'cdwalite:linkResource',$file->getWebPath('original'));
                }
            }
        }
    }
}
