<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author John Flatness
 * @copyright Copyright 2009 John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once HELPERS;

/**
 * Class implmenting MODS metadata output format.
 *
 * @link http://www.loc.gov/standards/mods/
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
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
     *
     * @link http://www.loc.gov/standards/mods/dcsimple-mods.html
     */
    public function appendMetadata($metadataElement) 
    {
        $mods = $this->document->createElementNS(
            self::METADATA_NAMESPACE, 'mods');
        $metadataElement->appendChild($mods);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $mods->setAttribute('xmlns:xsi', self::XML_SCHEMA_NAMESPACE_URI);
        $mods->setAttribute('xsi:schemaLocation', self::METADATA_NAMESPACE
            .' '.self::METADATA_SCHEMA);
            
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
            $languageElement = $this->appendNewElement($mods, 'language');
            $languageTerm = $this->appendNewElement($languageElement, 'languageTerm', $language->text);
            $languageTerm->setAttribute('type', 'text');
        }
        
        $rights = $this->item->getElementTextsByElementNameAndSetName('Rights', 'Dublin Core');
        foreach($rights as $right)
        {
            $this->appendNewElement($mods, 'accessCondition', $right->text);
        }

        $types = $this->item->getElementTextsByElementNameAndSetName('Type', 'Dublin Core');
        foreach ($types as $type)
        {
            $this->appendNewElement($mods, 'genre', $type->text);
        }


        $identifiers = $this->item->getElementTextsByElementNameAndSetName('Identifier', 'Dublin Core');
        foreach ($identifiers as $identifier)
        {
            $text = $identifier->text;
            $idElement = $this->appendNewElement($mods, 'identifier', $text);
            if ($this->_isUrl($text)) {
                $idElement->setAttribute('type', 'uri');
            } else {
                $idElement->setAttribute('type', 'local');
            }
        }

        $sources = $this->item->getElementTextsByElementNameAndSetName('Source', 'Dublin Core');
        foreach ($sources as $source)
        {
            $this->_addRelatedItem($mods, $source->text, true);
        }

        $relations = $this->item->getElementTextsByElementNameAndSetName('Relation', 'Dublin Core');
        foreach ($relations as $relation)
        {
            $this->_addRelatedItem($mods, $relation->text);
        }

        $location = $this->appendNewElement($mods, 'location');
        $url = $this->appendNewElement($location, 'url', abs_item_uri($this->item));
        $url->setAttribute('usage', 'primary display');

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
        $this->appendNewElement($recordInfo, 'recordIdentifier', $this->item->id);
    }

    /**
     * Add a relatedItem element.
     *
     * Checks the $text to see if it looks like a URL, and creates a
     * location subelement if so. Otherwise, a titleInfo is used.
     *
     * @param DomElement $mods
     * @param string $text
     * @param bool $original
     */
    private function _addRelatedItem($mods, $text, $original = false)
    {
        $relatedItem = $this->appendNewElement($mods, 'relatedItem');
        if ($this->_isUrl($text)) {
            $titleInfo = $this->appendNewElement($relatedItem, 'titleInfo');
            $this->appendNewElement($titleInfo, 'title', $text);
        } else {
            $location = $this->appendNewElement($relatedItem, 'location');
            $this->appendNewElement($location, 'url', $text);
        }
        if ($original) {
            $relatedItem->setAttribute('type', 'original');
        }
    }

    /**
     * Returns whether the given test is (looks like) a URL.
     *
     * @param string $text
     * @return bool
     */
    private function _isUrl($text)
    {
        return strncmp($text, 'http://', 7) || strncmp($text, 'https://', 8);
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
