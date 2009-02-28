<?php
/**
 * @package OaiPmhRepository
 * @author John Flatness <jflatnes@vt.edu>
 */

require_once('OaiIdentifier.php');
require_once('UtcDateTime.php');

/**
 * Namespace URI for the OAI-PMH protocol.
 */
define('OAI_PMH_NAMESPACE_URI', 'http://www.openarchives.org/OAI/2.0/');

define('OAI_IDENTIFIER_NAMESPACE_URI', 'http://www.openarchives.org/OAI/2.0/oai-identifier');
/**
 * Namespace URI for XML Schema instance elements.
 */
define('XML_SCHEMA_NAMESPACE_URI', 'http://www.w3.org/2001/XMLSchema-instance');
/**
 * Schema URI for the OAI-PMH protocol.
 */
define('OAI_PMH_SCHEMA_URI', 'http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');

define('OAI_IDENTIFIER_SCHEMA_URI', 'http://www.openarchives.org/OAI/2.0/oai-identifier.xsd');

/**
 * Calculated base URL for the repository.
 */
define('BASE_URL', 'http://'.$_SERVER['SERVER_NAME'].parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

/**
 * Version of OAI-PMH protocol the repository complies with.
 */
define('PROTOCOL_VERSION', '2.0');

/**
 * OaiPmhRepository_ResponseGenerator generates the XML responses to OAI-PMH
 * requests recieved by the repository.  The DOM extension is used to generate
 * all the XML output on-the-fly.
 *
 * @package OaiPmhRepository
 */
class OaiPmhRepository_ResponseGenerator
{
    private $responseDoc;
    private $request;

    /**
     * Default constructor
     *
     * Creates the DomDocument object, and adds XML elements common to all
     * OAI-PMH responses.
     */
    public function __construct()
    {
        $this->responseDoc = new DomDocument('1.0', 'UTF-8');
        //formatOutput makes DOM output "pretty" XML.  Good for debugging, but
        //adds some overhead, especially on large outputs
        $this->responseDoc->formatOutput = true;
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
     * Responds to the Identify verb
     *
     * Appends the Identify element for the repository to the response.
     */
    public function identify()
    {
        $this->request->setAttribute('verb', 'Identify');
        $identify = $this->responseDoc->createElement('Identify');
        $elements = array( 'repositoryName' => 
                                get_option('oaipmh_repository_name'),
                           'adminEmail' => get_option('administrator_email'),
                           'baseURL' => BASE_URL,
                           'protocolVersion' => PROTOCOL_VERSION,
                           'earliestDatestamp' => 'I need to fix this.',
                           'deletedRecord' => 'no',
                           'granularity' => 'YYYY-MM-DDThh:mm:ssZ');
        foreach($elements as $tag => $value)
        {
            $identify->appendChild($this->responseDoc->createElement($tag, $value));
        }
        $description = $this->responseDoc->createElement('description');
        $identify->appendChild($description);
        
        $oaiIdentifier = new DOMElement('oai-identifier');
        $description->appendChild($oaiIdentifier);
        //must set xmlns attribute manually to avoid DOM extension appending default: prefix to element name
        $oaiIdentifier->setAttribute('xmlns', OAI_IDENTIFIER_NAMESPACE_URI);
        $oaiIdentifier->setAttributeNS(XML_SCHEMA_NAMESPACE_URI,
                'xsi:schemaLocation',
                OAI_IDENTIFIER_NAMESPACE_URI.' '.OAI_IDENTIFIER_SCHEMA_URI);
        $elements = array( 'scheme' => 'oai',
                           'repositoryIdentifier' => get_option('oaipmh_repository_namespace_id'),
                           'delimiter' => ':',
                           'sampleIdentifier' => OaiPmhRepository_OaiIdentifier::itemtoOaiId(1));
        foreach($elements as $tag => $value)
        {
            $oaiIdentifier->appendChild($this->responseDoc->createElement($tag, $value));
        }

        $this->responseDoc->documentElement->appendChild($identify);
    }
    
    public function getRecord()
    {
        $this->request->setAttribute('verb', 'GetRecord');
    }

    /**
     * Adds an error element to the output
     *
     * Called to specify that an error has occured while processing a request.
     * @param string $code The OAI-PMH error code
     * @param string $text Human-readable error text
     */
    public function throwError($code, $text)
    {
        $error = $this->responseDoc->createElement('error', $text);
        $error->setAttribute('code', $code);
        $this->responseDoc->documentElement->appendChild($error);
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
