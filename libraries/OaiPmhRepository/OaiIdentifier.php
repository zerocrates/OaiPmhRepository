<?php
/**
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 */

/**
 * Utility class for dealing with OAI identifiers
 *
 * OaiPmhRepository_OaiIdentifier represents an instance of a unique identifier
 * for the repository conforming to the oai-identifier recommendation.  The class
 * can parse the local ID out of a given identifier string, or create a new
 * identifier by specifing the local ID of the item.
 *
 * @package OaiPmhRepository
 */
class OaiPmhRepository_OaiIdentifier {

    const NAMESPACE_ID = get_option('oaipmh_repository_namespace_id');
    
    /**
     * Converts the given OAI identifier to an Omeka item ID.
     *
     * @param string $oaiId OAI identifier.
     * @return string Omeka item ID.
     */
    public static function oaiIdToItem($oaiId) {
        $scheme = strtok($oaiId, ':');
        $namespaceId = strtok(':');
        $localId = strtok(':');
        if( $scheme != 'oai' || 
            $namespaceId != NAMESPACE_ID ||
            $localId < 0) {
           return NULL;
        }
        return $localId;
    }
    
    /**
     * Converts the given Omeka item ID to a OAI identifier.
     *
     * @param mixed $itemId Omeka item ID.
     * @return string OAI identifier.
     */
    public static function itemToOaiId($itemId) {
        return 'oai:'.NAMESPACE_ID.':'.$itemId;
    }

}
?>
