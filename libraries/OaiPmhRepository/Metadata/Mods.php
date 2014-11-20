<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author John Flatness
 * @copyright Copyright 2009 John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Class implmenting MODS metadata output format.
 *
 * @link http://www.loc.gov/standards/mods/
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
class OaiPmhRepository_Metadata_Mods implements OaiPmhRepository_Metadata_FormatInterface
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
    public function appendMetadata($item, $metadataElement, $generator)
    {
        $document = $generator->getDocument();
        $mods = $document->createElementNS(
            self::METADATA_NAMESPACE, 'mods');
        $metadataElement->appendChild($mods);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $mods->setAttribute('xmlns:xsi', OaiPmhRepository_XmlGeneratorAbstract::XML_SCHEMA_NAMESPACE_URI);
        $mods->setAttribute('xsi:schemaLocation', self::METADATA_NAMESPACE
            .' '.self::METADATA_SCHEMA);
            
        $titles = $item->getElementTexts( 'Dublin Core','Title');
        foreach($titles as $title)
        {
            $titleInfo = $generator->appendNewElement($mods, 'titleInfo');
            $generator->appendNewElement($titleInfo, 'title', $title->text);
        }
        
        $creators = $item->getElementTexts('Dublin Core','Creator');
        foreach($creators as $creator)
        {
            $name = $generator->appendNewElement($mods, 'name');
            $generator->appendNewElement($name, 'namePart', $creator->text);
            $role = $generator->appendNewElement($name, 'role');
            $roleTerm = $generator->appendNewElement($role, 'roleTerm', 'creator');
            $roleTerm->setAttribute('type', 'text');
        }
        
        $contributors = $item->getElementTexts('Dublin Core','Contributor');
        foreach($contributors as $contributor)
        {
            $name = $generator->appendNewElement($mods, 'name');
            $generator->appendNewElement($name, 'namePart', $contributor->text);
            $role = $generator->appendNewElement($name, 'role');
            $roleTerm = $generator->appendNewElement($role, 'roleTerm', 'contributor');
            $roleTerm->setAttribute('type', 'text');
        }
        
        $subjects = $item->getElementTexts('Dublin Core','Subject');
        foreach($subjects as $subject)
        {
            $subjectTag = $generator->appendNewElement($mods, 'subject');
            $generator->appendNewElement($subjectTag, 'topic', $subject->text);
        }
        
        $descriptions = $item->getElementTexts('Dublin Core','Description');
        foreach($descriptions as $description)
        {
            $generator->appendNewElement($mods, 'note', $description->text);
        }
        
        $formats = $item->getElementTexts('Dublin Core','Format');
        foreach($formats as $format)
        {
            $physicalDescription = $generator->appendNewElement($mods, 'physicalDescription');
            $generator->appendNewElement($physicalDescription, 'form', $format->text);
        }
        
        $languages = $item->getElementTexts('Dublin Core','Language');
        foreach($languages as $language)
        {
            $languageElement = $generator->appendNewElement($mods, 'language');
            $languageTerm = $generator->appendNewElement($languageElement, 'languageTerm', $language->text);
            $languageTerm->setAttribute('type', 'text');
        }
        
        $rights = $item->getElementTexts('Dublin Core','Rights');
        foreach($rights as $right)
        {
            $generator->appendNewElement($mods, 'accessCondition', $right->text);
        }

        $types = $item->getElementTexts('Dublin Core','Type');
        foreach ($types as $type)
        {
            $generator->appendNewElement($mods, 'genre', $type->text);
        }


        $identifiers = $item->getElementTexts( 'Dublin Core','Identifier');
        foreach ($identifiers as $identifier)
        {
            $text = $identifier->text;
            $idElement = $generator->appendNewElement($mods, 'identifier', $text);
            if ($this->_isUrl($text)) {
                $idElement->setAttribute('type', 'uri');
            } else {
                $idElement->setAttribute('type', 'local');
            }
        }

        $sources = $item->getElementTexts('Dublin Core','Source');
        foreach ($sources as $source)
        {
            $this->_addRelatedItem($mods, $generator, $source->text, true);
        }

        $relations = $item->getElementTexts('Dublin Core','Relation');
        foreach ($relations as $relation)
        {
            $this->_addRelatedItem($mods, $generator, $relation->text);
        }

        $location = $generator->appendNewElement($mods, 'location');
        $url = $generator->appendNewElement($location, 'url', record_url($item,'show',true));
        $url->setAttribute('usage', 'primary display');

        $publishers = $item->getElementTexts('Dublin Core','Publisher');
        $dates = $item->getElementTexts('Dublin Core','Date');

        // Empty originInfo sections are illegal
        if(count($publishers) + count($dates) > 0) 
        {
            $originInfo = $generator->appendNewElement($mods, 'originInfo');
        
            foreach($publishers as $publisher)
            {
                $generator->appendNewElement($originInfo, 'publisher', $publisher->text);
            }

            foreach($dates as $date)
            {
                $generator->appendNewElement($originInfo, 'dateOther', $date->text);
            }
        }
        
        $recordInfo = $generator->appendNewElement($mods, 'recordInfo');
        $generator->appendNewElement($recordInfo, 'recordIdentifier', $item->id);
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
    private function _addRelatedItem($mods, $generator, $text, $original = false)
    {
        $relatedItem = $generator->appendNewElement($mods, 'relatedItem');
        if ($this->_isUrl($text)) {
            $titleInfo = $generator->appendNewElement($relatedItem, 'titleInfo');
            $generator->appendNewElement($titleInfo, 'title', $text);
        } else {
            $location = $generator->appendNewElement($relatedItem, 'location');
            $generator->appendNewElement($location, 'url', $text);
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
}
