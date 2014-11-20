<?php
/**
 * @package OaiPmhRepository
 * @subpackage Libraries
 * @copyright Copyright 2009-2014 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Abstract class containing functions for tasks common to all OAI-PMH
 * responses.
 *
 * @package OaiPmhRepository
 * @subpackage Libraries
 */
class OaiPmhRepository_OaiXmlGeneratorAbstract
{
    const XML_SCHEMA_NAMESPACE_URI = 'http://www.w3.org/2001/XMLSchema-instance';

    // =========================
    // General OAI-PMH constants
    // =========================
    
    const OAI_PMH_NAMESPACE_URI    = 'http://www.openarchives.org/OAI/2.0/';
    const OAI_PMH_SCHEMA_URI       = 'http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd';
    const OAI_PMH_PROTOCOL_VERSION = '2.0';
    
    // =========================
    // Error codes
    // =========================
    
    const OAI_ERR_BAD_ARGUMENT              = 'badArgument';
    const OAI_ERR_BAD_RESUMPTION_TOKEN      = 'badResumptionToken';
    const OAI_ERR_BAD_VERB                  = 'badVerb';
    const OAI_ERR_CANNOT_DISSEMINATE_FORMAT = 'cannotDisseminateFormat';
    const OAI_ERR_ID_DOES_NOT_EXIST         = 'idDoesNotExist';
    const OAI_ERR_NO_RECORDS_MATCH          = 'noRecordsMatch';
    const OAI_ERR_NO_METADATA_FORMATS       = 'noMetadataFormats';
    const OAI_ERR_NO_SET_HIERARCHY          = 'noSetHierarchy';
    
    // =========================
    // Date/time constants
    // =========================
    
    /**
     * Flags if an error has occurred during the response.
     * @var bool
     */
    protected $error;

    /**
     * The XML document being generated.
     * @var DOMDocument
     */
    protected $document;
    
    /**
     * Throws an OAI-PMH error on the given response.
     *
     * @param string $error OAI-PMH error code.
     * @param string $message Optional human-readable error message.
     */
    public function throwError($error, $message = null)
    {
        $this->error = true;
        $errorElement = $this->document->createElement('error', $message);
        $this->document->documentElement->appendChild($errorElement);
        $errorElement->setAttribute('code', $error);
    }
    
    /**
     * Get the DOMDocument for this generator.
     *
     * @return DOMDocument
     */
    public function getDocument()
    {
        return $this->document;
    }
}
