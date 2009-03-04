<?php
/**
 * @package OaiPmhRepository
 * @author John Flatness <jflatnes@vt.edu>
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

    public static function oaiIdToItem($oaiId) {
        $scheme = strtok($oaiId, ':');
        $namespaceId = strtok(':');
        $localId = strtok(':');
        if( $scheme != 'oai' || 
            $namespaceId != get_option('oaipmh_repository_namespace_id') ||
            $localId < 0) {
           return NULL;
        }
        return $localId;
    }

    public static function itemToOaiId($item) {
        return 'oai:'.get_option('oaipmh_repository_namespace_id').':'.$item;
    }

}
?>
