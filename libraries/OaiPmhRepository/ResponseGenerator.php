<?php
/**
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once('Error.php');
require_once('OaiIdentifier.php');
require_once('UtcDateTime.php');
require_once('XmlUtilities.php');
require_once('Metadata/OaiDc.php');
require_once('OaiPmhRepositoryToken.php');

// Namespace URIs for XML response document
define('OAI_PMH_NAMESPACE_URI', 'http://www.openarchives.org/OAI/2.0/');

// XML Schema URIs for XML response document 
define('OAI_PMH_SCHEMA_URI', 'http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');

// Version of OAI-PMH protocol the repository plugin complies with.
define('OAI_PMH_PROTOCOL_VERSION', '2.0');

/**
 * OaiPmhRepository_ResponseGenerator generates the XML responses to OAI-PMH
 * requests recieved by the repository.  The DOM extension is used to generate
 * all the XML output on-the-fly.
 *
 * @package OaiPmhRepository
 */
class OaiPmhRepository_ResponseGenerator
{
    public $responseDoc;
    public $error;
    private $request;
    private $query;

    /**
     * Constructor
     *
     * Creates the DomDocument object, and adds XML elements common to all
     * OAI-PMH responses.  Dispatches control to appropriate verb, if any.
     *
     * @param array $query HTTP POST/GET query key-value pair array.
     * @uses dispatchRequest()
     */
    public function __construct($query)
    {
        $this->error = false;
        $this->query = $query;
        $this->responseDoc = new DomDocument('1.0', 'UTF-8');
        
        //formatOutput makes DOM output "pretty" XML.  Good for debugging, but
        //adds some overhead, especially on large outputs.
        $this->responseDoc->formatOutput = true;
        $this->responseDoc->xmlStandalone = true;
        
        $root = $this->responseDoc->createElementNS(OAI_PMH_NAMESPACE_URI,
            'OAI-PMH');
        $this->responseDoc->appendChild($root);
    
        $root->setAttributeNS(XML_SCHEMA_NAMESPACE_URI, 'xsi:schemaLocation',
            OAI_PMH_NAMESPACE_URI.' '.OAI_PMH_SCHEMA_URI);
    
        $responseDate = $this->responseDoc->createElement('responseDate', 
            OaiPmhRepository_UtcDateTime::unixToUtc(time()));
        $root->appendChild($responseDate);

        $this->request = $this->responseDoc->createElement('request', BASE_URL);

        $root->appendChild($this->request);
        
        $this->dispatchRequest();
    }
    
    /**
     * Parses the HTTP query and dispatches to the correct verb handler.
     *
     * Checks arguments for each verb type, and sets XML request tag.
     *
     * @uses checkArguments()
     */
    private function dispatchRequest()
    {
        $requiredArgs = array();
        $optionalArgs = array();
        
        $verb = $this->query['verb'];
        $resumptionToken = $this->query['resumptionToken'];
        
        if($resumptionToken)
            $requiredArgs = array('resumptionToken');
        else
            switch($verb)
            {
                case 'Identify':
                    break;
                case 'GetRecord':
                    $requiredArgs = array('identifier', 'metadataPrefix');
                    break;
                case 'ListRecords':
                    $requiredArgs = array('metadataPrefix');
                    $optionalArgs = array('from', 'until', 'set');
                    break;
                case 'ListIdentifiers':
                    $requiredArgs = array('metadataPrefix');
                    $optionalArgs = array('from', 'until', 'set');
                    break;                
                case 'ListSets':
                    break;
                case 'ListMetadataFormats':
                    $optionalArgs = array('identifier');
                    break;
                default:
                    OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_VERB);
            }
        
        $this->checkArguments($requiredArgs, $optionalArgs);
        
        if(!$this->error) {
            foreach($this->query as $key => $value)
                $this->request->setAttribute($key, $value);
                
            if($resumptionToken)
                $this->resumeListRequest($resumptionToken);
            else {
                /* This Inflector use means verb-implementing functions must be
                   the lowerCamelCased version of the verb name. */
                $functionName = Inflector::variablize($this->query['verb']);
                $this->$functionName();
            }
        }
    }
    
    /**
     * Checks the argument list from the POST/GET query.
     *
     * Checks if the required arguments are present, and no invalid extra
     * arguments are present.  All valid arguments must be in either the
     * required or optional array.
     *
     * @param array requiredArgs Array of required argument names.
     * @param array optionalArgs Array of optional, but valid argument names.
     */
    private function checkArguments($requiredArgs = array(), $optionalArgs = array())
    {
        $requiredArgs[] = 'verb';
        
        /* Checks (essentially), if there are more arguments in the query string
           than in PHP's returned array, if so there were duplicate arguments,
           which is not allowed. */
        if($_SERVER['QUERY_STRING'] != urldecode(http_build_query($this->query)))
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Duplicate arguments in request.");
        
        $keys = array_keys($this->query);
        
        foreach(array_diff($requiredArgs, $keys) as $arg)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Missing required argument $arg.");
        foreach(array_diff($keys, $requiredArgs, $optionalArgs) as $arg)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Unknown argument $arg.");
        
        $fromGran = OaiPmhRepository_UtcDateTime::getGranularity($this->query['from']);
        $untilGran = OaiPmhRepository_UtcDateTime::getGranularity($this->query['until']);
        
        /* These tests, while they do catch the date errors they are written for,
           vastly overtest the same things several times. Not a big issue, but
           could easily be improved */ 
        if(isset($this->query['from']) && !$fromGran)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Invalid date/time argument.");
        if(isset($this->query['until']) && !$untilGran)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Invalid date/time argument.");
        if(isset($this->query['from']) && isset($this->query['until']) && $fromGran != $untilGran)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Date/time arguments of differing granularity.");
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
           response to validate */
        $elements = array( 
            'repositoryName'    => get_option('oaipmh_repository_name'),
            'baseURL'           => OAI_PMH_BASE_URL,
            'protocolVersion'   => OAI_PMH_PROTOCOL_VERSION,
            'adminEmail'        => get_option('administrator_email'),
            'earliestDatestamp' => OaiPmhRepository_UtcDateTime::unixToUtc(0),
            'deletedRecord'     => 'no',
            'granularity'       => OaiPmhRepository_UtcDateTime::OAI_GRANULARITY_STRING);
        $identify = OaiPmhRepository_XmlUtilities::createElementWithChildren(
            $this->responseDoc->documentElement, 'Identify', $elements);
        
        $description = $this->responseDoc->createElement('description');
        $identify->appendChild($description);
        
        OaiPmhRepository_OaiIdentifier::describeIdentifier($description);
    }
    
    /**
     * Responds to the GetRecord verb.
     *
     * Outputs the header and metadata in the specified format for the specified
     * identifier.
     *
     */
    private function getRecord()
    {
        $identifier = $this->query['identifier'];
        $metadataPrefix = $this->query['metadataPrefix'];
        
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
     * @uses listResponse()
     */
    private function listRecords()
    {
        $metadataPrefix = $this->query['metadataPrefix'];
        $set = $this->query['set'];
        $from = $this->query['from'];
        $until = $this->query['until'];
        
        if($from)
            $fromDate = OaiPmhRepository_UtcDateTime::utcToDb($from);
        if($until)
            $untilDate = OaiPmhRepository_UtcDateTime::utcToDb($until);
        
        $this->listResponse('ListRecords', $metadataPrefix, 0, $set, $from, $until);
    }
    
    /**
     * Responds to the ListIdentifiers verb.
     *
     * Outputs headers for all of the items in the database in the specified
     * metadata format.
     *
     * @uses listResponse()
     */
    private function listIdentifiers()
    {
        $metadataPrefix = $this->query['metadataPrefix'];
        $set = $this->query['set'];
        $from = $this->query['from'];
        $until = $this->query['until'];
        
        if($from)
            $fromDate = OaiPmhRepository_UtcDateTime::utcToDb($from);
        if($until)
            $untilDate = OaiPmhRepository_UtcDateTime::utcToDb($until);
        
        $this->listResponse('ListIdentifiers', $metadataPrefix, 0, $set, $fromDate, $untilDate);
    }
    
    /**
     * Responds to the two main List verbs, includes resumption and limiting.
     *
     * @param string $verb OAI-PMH verb for the request
     * @param string $metadataPrefix Metadata prefix
     * @param int $cursor Offset in response to begin output at
     * @param mixed $set Optional set argument
     * @param string $from Optional from date argument
     * @param string $until Optional until date argument
     * @uses createResumptionToken()
     */
    private function listResponse($verb, $metadataPrefix, $cursor, $set, $from, $until) {
        $listLimit = get_option('oaipmh_repository_list_limit');
        
        if($metadataPrefix != 'oai_dc')
            OaiPmhRepository_Error::throwError($this, OAI_ERR_CANNOT_DISSEMINATE_FORMAT);
        
        else {
            $itemTable = get_db()->getTable('Item');
            $select = $itemTable->getSelect();
            $itemTable->filterByPublic($select, true);
            if($set)
                $itemTable->filterByCollection($select, $set);
            if($from) {
                $select->where('modified >= CAST(? AS DATETIME) OR added >= CAST(? AS DATETIME)', $from);
                }
            if($until) {
                $select->where('modified <= CAST(? AS DATETIME) OR added <= CAST(? AS DATETIME)', $until);
                }
            
            // Total number of rows that would be returned
            $rows = $select->query()->rowCount();
            // This limit call will form the basis of the flow control
            $select->limit($listLimit, $cursor);
            
            $items = $itemTable->fetchObjects($select);  
            
            if(count($items) == 0)
                OaiPmhRepository_Error::throwError($this, OAI_ERR_NO_RECORDS_MATCH,
                    'No records match the given criteria');

            else {
                if($verb == 'ListIdentifiers')
                    $method = 'appendHeader';
                else if($verb == 'ListRecords')
                    $method = 'appendRecord';
                
                $verbElement = $this->responseDoc->createElement($verb);
                $this->responseDoc->documentElement->appendChild($verbElement);
                foreach($items as $item) {
                    $record = new OaiPmhRepository_Metadata_OaiDc($item, $verbElement);
                    $record->$method();
                    // Drop Item from memory explicitly
                    release_object($this->item);
                }
                if($rows > ($cursor + $listLimit)) {
                    $token = $this->createResumptionToken($verb, $metadataPrefix, $cursor + $listLimit, $from, $until, $set);

                    $tokenElement = $this->responseDoc->createElement('resumptionToken', $token->id);
                    $tokenElement->setAttribute('expirationDate', OaiPmhRepository_UtcDateTime::dbToUtc($token->expiration));
                    $tokenElement->setAttribute('completeListSize', $rows);
                    $tokenElement->setAttribute('cursor', $cursor);
                    $verbElement->appendChild($tokenElement);
                }
                else if($cursor != 0) {
                    $tokenElement = $this->responseDoc->createElement('resumptionToken');
                    $verbElement->appendChild($tokenElement);
                }
            }
        }
    }
    
    /**
     * Responds to the ListMetadataFormats verb.
     *
     * Outputs records for all of the items in the database in the specified
     * metadata format.
     *
     * @todo extend for additional metadata formats
     */
    private function listMetadataFormats()
    {
        $identifier = $this->query['identifier'];
        /* Items are not used for lookup, simply checks for an invalid id */
        if($identifier) {
            $itemId = OaiPmhRepository_OaiIdentifier::oaiIdToItem($identifier);
        
            if(!$itemId) {
                OaiPmhRepository_Error::throwError($this, OAI_ERR_ID_DOES_NOT_EXIST);
                return;
            }
        }
        if(!$this->error) {
            $listMetadataFormats = $this->responseDoc->createElement('ListMetadataFormats');
            $this->responseDoc->documentElement->appendChild($listMetadataFormats);
            $format = new OaiPmhRepository_Metadata_OaiDc(null, $listMetadataFormats);
            $format->declareMetadataFormat();
        }
    }

    /**
     * Responds to the ListSets verb.
     *
     * Outputs setSpec and setName for all OAI-PMH sets (Omeka collections).
     *
     * @todo replace with Zend_Db_Select to allow use of limit or pageLimit
     */
    private function listSets()
    {
        $collections = get_db()->getTable('Collection')->findAll();
        
        if(count($collections) == 0)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_NO_SET_HIERARCHY);
            
        $listSets = $this->responseDoc->createElement('ListSets');     

        if(!$this->error) {
            $this->responseDoc->documentElement->appendChild($listSets); 
            foreach ($collections as $collection) {
                $elements = array( 'setSpec' => $collection->id,
                                   'setName' => $collection->name );
                OaiPmhRepository_XmlUtilities::createElementWithChildren(
                    $listSets, 'set', $elements);
            }
        }
    }
    
    /**
     * Stores a new resumption token record in the database
     *
     * @param string $verb OAI-PMH verb for the request
     * @param string $metadataPrefix Metadata prefix
     * @param int $cursor Offset in response to begin output at
     * @param mixed $set Optional set argument
     * @param string $from Optional from date argument
     * @param string $until Optional until date argument
     * @return OaiPmhRepositoryToken Token model object
     */
    private function createResumptionToken($verb, $metadataPrefix, $cursor, $set, $from, $until)
    {
        $tokenTable = get_db()->getTable('OaiPmhRepositoryToken');
        
        $resumptionToken = new OaiPmhRepositoryToken();
        $resumptionToken->verb = $verb;
        $resumptionToken->metadata_prefix = $metadataPrefix;
        $resumptionToken->cursor = $cursor;
        if($set)
            $resumptionToken->set = $set;
        if($from)
            $resumptionToken->from = $from;
        if($until)
            $resumptionToken->until = $until;
        $resumptionToken->expiration = OaiPmhRepository_UtcDateTime::unixToDb(time() + (get_option('oaipmh_repository_expiration_time')*60));
        $resumptionToken->save();
        
        return $resumptionToken;
    }
    
    /**
     * Returns the next incomplete list response based on the given resumption
     * token.
     *
     * @param string $token Resumption token
     * @uses listResponse()
     */
    private function resumeListRequest($token)
    {
        $tokenTable = new OaiPmhRepositoryTokenTable(get_db(), 'OaiPmhRepositoryToken');
        $tokenTable->purgeExpiredTokens();
        //$tokenObject = get_db()->getTable('OaiPmhRepositoryToken')->find($token);
        $tokenObject = $tokenTable->find($token);
        
        if(!$tokenObject || ($tokenObject->verb != $this->query['verb']))
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_RESUMPTION_TOKEN);
        else
            $this->listResponse($tokenObject->verb,
                                $tokenObject->metadata_prefix,
                                $tokenObject->cursor,
                                $tokenObject->set,
                                $tokenObject->from,
                                $tokenObject->until);
    }
    
    /**
     * Outputs the XML response as a string
     *
     * Called once processing is complete to return the XML to the client.
     *
     * @return string the response XML
     */
    public function __toString()
    {
        return $this->responseDoc->saveXML();
    }
}
?>
