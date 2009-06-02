<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author John Flatness
 * @copyright Copyright 2009 John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once('Abstract.php');
require_once HELPERS;

/**
 * Class implmenting MODS metadata output format.
 *
 * @link http://www.loc.gov/standards/mods/
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 * @todo Complete crosswalk from Dublin Core
 */
class OaiPmhRepository_Metadata_Mods extends OaiPmhRepository_Metadata_Abstract
{
    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'mods';    
    
    /** XML namespace for output format */
    const METADATA_NAMESPACE = 'http://www.loc.gov/mods/v3';
    
    /** XML schema for output format */
    const METADATA_SCHEMA = 'http://www.loc.gov/standards/mods/v3/mods-3-3.xsd';
    
    /**
     * Appends MODS metadata. 
     *
     * Appends a metadata element, an child element with the required format,
     * and further children for each of the Dublin Core fields present in the
     * item.
     */
    public function appendMetadata() 
    {
        $metadataElement = $this->document->createElement('metadata');
        $this->parentElement->appendChild($metadataElement);   
        
        $mods = $this->document->createElementNS(
            self::METADATA_NAMESPACE, 'mods');
        $metadataElement->appendChild($mods);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        //$mods->setAttribute('xmlns:cdwalite', self::METADATA_NAMESPACE);
        $mods->setAttribute('xmlns:xsi', self::XML_SCHEMA_NAMESPACE_URI);
        $mods->setAttribute('xsi:schemaLocation', self::METADATA_NAMESPACE
            .' '.self::METADATA_SCHEMA);
            
        /* According to the crosswalk at
         * http://www.loc.gov/standards/mods/dcsimple-mods.html
         */
        
        $titles = $this->item->getElementTextsByElementNameAndSetName('Title', 'Dublin Core');
        foreach($titles as $title)
        {
            $titleInfo = $this->appendNewElement($mods, 'titleInfo');
            $this->appendNewElement($titleInfo, 'title', $title->text);
        }
        
        $creators = $this->item->getElementTextsByElementNameAndSetName('Creator', 'Dublin Core');
        foreach($creators as $creator)
        {
            $name = $this->appendNewElement($mods, 'name');
            $this->appendNewElement($name, 'namePart', $creator->text);
            $role = $this->appendNewElement($name, 'role');
            $roleTerm = $this->appendNewElement($role, 'roleTerm', 'creator');
            $roleTerm->setAttribute('type', 'text');
        }
        
        $contributors = $this->item->getElementTextsByElementNameAndSetName('Contributor', 'Dublin Core');
        foreach($contributors as $contributor)
        {
            $name = $this->appendNewElement($mods, 'name');
            $this->appendNewElement($name, 'namePart', $contributor->text);
            $role = $this->appendNewElement($name, 'role');
            $roleTerm = $this->appendNewElement($role, 'roleTerm', 'contributor');
            $roleTerm->setAttribute('type', 'text');
        }
        
        $subjects = $this->item->getElementTextsByElementNameAndSetName('Subject', 'Dublin Core');
        foreach($subjects as $subject)
        {
            $subjectTag = $this->appendNewElement($mods, 'subject');
            $this->appendNewElement($subjectTag, 'topic', $subject->text);
        }
        
        $descriptions = $this->item->getElementTextsByElementNameAndSetName('Description', 'Dublin Core');
        foreach($descriptions as $description)
        {
            $this->appendNewElement($mods, 'note', $description->text);
        }
        
        $formats = $this->item->getElementTextsByElementNameAndSetName('Format', 'Dublin Core');
        foreach($formats as $format)
        {
            $physicalDescription = $this->appendNewElement($mods, 'physicalDescription');
            $this->appendNewElement($physicalDescription, 'form', $format->text);
        }
        
        $languages = $this->item->getElementTextsByElementNameAndSetName('Language', 'Dublin Core');
        foreach($languages as $language)
        {
            $this->appendNewElement($mods, 'language', $language->text);
        }
        
        $rights = $this->item->getElementTextsByElementNameAndSetName('Rights', 'Dublin Core');
        foreach($rights as $right)
        {
            $this->appendNewElement($mods, 'accessCondition', $right->text);
        }
        
        $publishers = $this->item->getElementTextsByElementNameAndSetName('Publisher', 'Dublin Core');
        $dates = $this->item->getElementTextsByElementNameAndSetName('Date', 'Dublin Core');
        
        // Empty originInfo sections are illegal
        if(count($publishers) + count($dates) > 0) 
        {
            $originInfo = $this->appendNewElement($mods, 'originInfo');
        
            foreach($publishers as $publisher)
            {
                $this->appendNewElement($originInfo, 'publisher', $publisher->text);
            }

            foreach($dates as $date)
            {
                $this->appendNewElement($originInfo, 'dateOther', $date->text);
            }
        }
        
        $recordInfo = $this->appendNewElement($mods, 'recordInfo');
        $this->appendNewElement($recordInfo, 'recordIdentifier', $item->id);
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