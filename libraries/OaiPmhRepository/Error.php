<?php
/**
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

// Each of the defined OAI-PMH error states
define('OAI_ERR_BAD_ARGUMENT'              , 'badArgument');
define('OAI_ERR_BAD_RESUMPTION_TOKEN'      , 'badResumptionToken');
define('OAI_ERR_BAD_VERB'                  , 'badVerb');
define('OAI_ERR_CANNOT_DISSEMINATE_FORMAT' , 'cannotDisseminateFormat');
define('OAI_ERR_ID_DOES_NOT_EXIST'         , 'idDoesNotExist');
define('OAI_ERR_NO_RECORDS_MATCH'          , 'noRecordsMatch');
define('OAI_ERR_NO_METADATA_FORMATS'       , 'noMetadataFormats');
define('OAI_ERR_NO_SET_HIERARCHY'          , 'noSetHierarchy');

/**
 * Class for throwing OAI-PMH error states.
 *
 * @package OaiPmhRepository
 */
class OaiPmhRepository_Error {

    /**
     * Throws an OAI-PMH error on the given response.
     * 
     * @param OaiPmhRepository_ResponseGenerator $response Response generator object.
     * @param string $error OAI-PMH error code.
     * @param string $message Optional human-readable error message.
     */
    static public function throwError($response, $error, $message = null)
    {
        $response->error = true;
        $errorElement = $response->document->createElement('error', $message);
        $response->document->documentElement->appendChild($errorElement);
        $errorElement->setAttribute('code', $error);
    }
}
