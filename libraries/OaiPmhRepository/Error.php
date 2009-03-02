<?php
define(OAI_ERR_BAD_ARGUMENT              , 'badArgument');
define(OAI_ERR_BAD_RESUMPTION_TOKEN      , 'badResumptionToken');
define(OAI_ERR_BAD_VERB                  , 'badVerb');
define(OAI_ERR_CANNOT_DISSEMINATE_FORMAT , 'cannotDisseminateFormat');
define(OAI_ERR_ID_DOES_NOT_EXIST         , 'idDoesNotExist');
define(OAI_ERR_NO_RECORDS_MATCH          , 'noRecordsMatch');
define(OAI_ERR_NO_METADATA_FORMATS       , 'noMetadataFormats');
define(OAI_ERR_NO_SET_HIERARCHY          , 'noSetHierarchy');

class OaiPmhRepository_Error {

    static public function throwError($response, $error, $message = NULL)
    {
        $response->error = true;
        $errorElement = $response->responseDoc->createElement('error', $message);
        $response->responseDoc->documentElement->appendChild($errorElement);
        $errorElement->setAttribute('code', $error);
    }
}
