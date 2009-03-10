<?php
/**
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 */

define('XML_SCHEMA_NAMESPACE_URI', 'http://www.w3.org/2001/XMLSchema-instance');

/**
 * Globally-required or useful XML utility functions and defines.
 *
 * @package OaiPmhRepository
 */
class OaiPmhRepository_XmlUtilities
{
    /**
     * Creates a new XML element with the specified children
     *
     * Creates a parent element with the given name, with children with names
     * and values as given.  Adds the resulting element as a child of the given
     * element
     *
     * @param DomElement parent Existing parent of all the new nodes.
     * @param string name Name of the new parent element.
     * @param array children Child names and values, as name => value.
     * @return DomElement The new tree of elements.
     */
    static public function createElementWithChildren($parent, $name, $children)
    {
        $document = $parent->ownerDocument;
        $newElement = $document->createElement($name);
        foreach($children as $tag => $value)
        {
            $newElement->appendChild($document->createElement($tag, $value));
        }
        $parent->appendChild($newElement);
        return newElement;
    }
?>