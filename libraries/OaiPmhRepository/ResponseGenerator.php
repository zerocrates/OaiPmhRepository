<?php
/**
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 */

require_once('Error.php');
require_once('OaiIdentifier.php');
require_once('UtcDateTime.php');
require_once('Metadata/OaiDc.php');

// Namespace URIs for XML response document
define('OAI_PMH_NAMESPACE_URI', 'http://www.openarchives.org/OAI/2.0/');
define('XML_SCHEMA_NAMESPACE_URI', 'http://www.w3.org/2001/XMLSchema-instance');

// XML Schema URIs for XML response document 
define('OAI_PMH_SCHEMA_URI', 'http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');

// Calculated base URL for the repository.
define('BASE_URL', 'http://'.$_SERVER['SERVER_NAME'].parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Version of OAI-PMH protocol the repository plugin complies with.
define('PROTOCOL_VERSION', '2.0');

/**
 * OaiPmhRepository_ResponseGenerator generates the XML responses to OAI-PMH
 * requests recieved by the repository.  The DOM extension is used to generate
 * all the XML output on-the-fly.
 *
 * @package OaiPmhRepository
 * @todo Refactor out code for setting arguments in request element
 */
class OaiPmhRepository_ResponseGenerator
{
    public $responseDoc;
    private $request;
    public $error;

    /**
     * Constructor
     *
     * Creates the DomDocument object, and adds XML elements common to all
     * OAI-PMH responses.
     */
    public function __construct()
    {
        $this->error = false;
        $this->responseDoc = new DomDocument('1.0', 'UTF-8');
        //formatOutput makes DOM output "pretty" XML.  Good for debugging, but
        //adds some overhead, especially on large outputs
        $this->responseDoc->formatOutput = true;
        $this->responseDoc->xmlStandalone = true;
        
        $root = $this->responseDoc->createElementNS(OAI_PMH_NAMESPACE_URI,
            'OAI-PMH');
        $this->responseDoc->appendChild($root);
    
        $root->setAttributeNS(XML_SCHEMA_NAMESPACE_URI, 'xsi:schemaLocation',
            OAI_PMH_NAMESPACE_URI.' '.OAI_PMH_SCHEMA_URI);
    
        $responseDate = $this->responseDoc->createElement('responseDate', 
            OaiPmhRepository_UtcDateTime::currentTime());
        $root->appendChild($responseDate);

        $this->request = $this->responseDoc->createElement('request', BASE_URL);
        $OAI_PMH_SCHEMA_URI = 'http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd';
        $root->appendChild($this->request);
    }
    
    /**
     * Responds to the Identify verb.
     *
     * Appends the Identify element for the repository to the response.
     */
    public function identify()
    {
        if($this->error)
            return;
        $this->request->setAttribute('verb', 'Identify');
        /* according to the schema, this order of elements is required for the
         * response to validate
         */
        $elements = array( 
            'repositoryName'    => get_option('oaipmh_repository_name'),
            'baseURL'           => BASE_URL,
            'protocolVersion'   => PROTOCOL_VERSION,
            'adminEmail'        => get_option('administrator_email'),
            'earliestDatestamp' => OaiPmhRepository_UtcDateTime::convertToUtcDateTime(0),
            'deletedRecord'     => 'no',
            'granularity'       => 'YYYY-MM-DDThh:mm:ssZ');
        $identify = $this->createElementWithChildren('Identify', $elements);
        
        $description = $this->responseDoc->createElement('description');
        $identify->appendChild($description);
        
        OaiPmhRepository_OaiIdentifier::describeIdentifier($description);
        
        $this->responseDoc->documentElement->appendChild($identify);
    }
    
    /**
     * Responds to the GetRecord verb.
     *
     * Outputs the header and metadata in the specified format for the specified
     * identifier.
     *
     * @param string identifier The oai-identifier for the desired record.
     * @param string metadataPrefix Code for the desired metadata format.
     */
    public function getRecord($identifier, $metadataPrefix)
    {
        $this->request->setAttribute('verb', 'GetRecord');
        if($identifier)
            $this->request->setAttribute('identifier', $identifier);
        if($metadataPrefix)
            $this->request->setAttribute('metadataPrefix', $metadataPrefix);
        
        $itemId = OaiPmhRepository_OaiIdentifier::oaiIdToItem($identifier);
        
        if(!$itemId) {
            OaiPmhRepository_Error::throwError($this, OAI_ERR_ID_DOES_NOT_EXIST);
            return;
        }
        
        $item = get_db()->getTable('Item')->find($itemId);

        if(!$item) {
            OaiPmhRepository_Error::throwError($this, OAI_ERR_ID_DOES_NOT_EXIST);
        }
        if($metadataPrefix != 'oai_dc') {
            OaiPmhRepository_Error::throwError($this, OAI_ERR_CANNOT_DISSEMINATE_FORMAT);
        }
        if(!$this->error) {
            $getRecord = $this->responseDoc->createElement('GetRecord');
            $this->responseDoc->documentElement->appendChild($getRecord);
            $record = new OaiPmhRepository_Metadata_OaiDc($item, $getRecord);
            $record->appendRecord();
        }
    }
    
    /**
     * Responds to the ListRecords verb.
     *
     * Outputs records for all of the items in the database in the specified
     * metadata format.
     *
     * @param string metadataPrefix Code for the desired metadata format.
     */
    public function listRecords($metadataPrefix)
    {
        $this->request->setAttribute('verb', 'ListRecords');
        if($metadataPrefix)
            $this->request->setAttribute('metadataPrefix', $metadataPrefix);
        
        if($metadataPrefix != 'oai_dc') {
            OaiPmhRepository_Error::throwError($this, OAI_ERR_CANNOT_DISSEMINATE_FORMAT);
        }
        
        $items = get_db()->getTable('Item')->findBy();
        
        if(!$this->error) {
            $listRecords = $this->responseDoc->createElement('ListRecords');
            $this->responseDoc->documentElement->appendChild($listRecords);
            foreach($items as $item) {
                $record = new OaiPmhRepository_Metadata_OaiDc($item, $listRecords);
                $record->appendRecord();
            }
        }
    }
    
    /**
     * Responds to the ListIdentifiers verb.
     *
     * Outputs headers for all of the items in the database in the specified
     * metadata format.
     *
     * @param string metadataPrefix Code for the desired metadata format.
     */
    public function listIdentifiers($metadataPrefix)
    {
        $this->request->setAttribute('verb', 'ListIdentifiers');
        if($metadataPrefix)
            $this->request->setAttribute('metadataPrefix', $metadataPrefix);
        
        if($metadataPrefix != 'oai_dc') {
            OaiPmhRepository_Error::throwError($this, OAI_ERR_CANNOT_DISSEMINATE_FORMAT);
        }
        
        $items = get_db()->getTable('Item')->findBy();
        
        if(!$this->error) {
            $listIdentifiers = $this->responseDoc->createElement('ListIdentifiers');
            $this->responseDoc->documentElement->appendChild($listIdentifiers);
            foreach($items as $item) {
                $record = new OaiPmhRepository_Metadata_OaiDc($item, $listIdentifiers);
                $record->appendHeader();
            }
        }
    }
    /**
     * Responds to the ListMetadataFormats verb.
     *
     * Outputs records for all of the items in the database in the specified
     * metadata format.
     *
     * @param string identifier OAI identifier for the desired record, if any.
     */
    public function listMetadataFormats($identifier = null)
    {
        $this->request->setAttribute('verb', 'ListMetadataFormats');
        if($identifier)
            $this->request->setAttribute('identifier', $identifier);
        if(!$this->error) {
            $listMetadataFormats = $this->responseDoc->createElement('ListMetadataFormats');
            $this->responseDoc->documentElement->appendChild($listMetadataFormats);
            $format = new OaiPmhRepository_Metadata_OaiDc(null, $listMetadataFormats);
            $format->declareMetadataFormat();
        }
    }
    /**
     * Creates a new XML element with the specified children
     *
     * Creates a parent element with the given name, with children with names
     * and values as given.  Adds the resulting element to the response
     * document.
     *
     * @param string name Name of the parent element.
     * @param array children Child names and values, as name => value. 
     */
    private function createElementWithChildren($name, $children)
    {
        $newElement = $this->responseDoc->createElement($name);
        foreach($children as $tag => $value)
        {
            $newElement->appendChild($this->responseDoc->createElement($tag, $value));
        }
        return $newElement;
    }

    /**
     * Outputs the XML response as a string
     *
     * Called once processing is complete to obtain the XML to return to the client.
     
     * @return string the response XML
     */
    public function __toString()
    {
        return $this->responseDoc->saveXML();
    }
}
?>
