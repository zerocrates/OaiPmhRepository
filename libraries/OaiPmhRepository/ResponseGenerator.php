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
require_once('XmlGeneratorAbstract.php');
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
class OaiPmhRepository_ResponseGenerator extends OaiPmhRepository_XmlGeneratorAbstract
{
    public $document;
    public $error;
    private $request;
    private $query;
    private $metadataFormats;

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
        $this->document = new DomDocument('1.0', 'UTF-8');
        
        //formatOutput makes DOM output "pretty" XML.  Good for debugging, but
        //adds some overhead, especially on large outputs.
        $this->document->formatOutput = true;
        $this->document->xmlStandalone = true;
        
        $root = $this->document->createElementNS(OAI_PMH_NAMESPACE_URI,
            'OAI-PMH');
        $this->document->appendChild($root);
    
        $root->setAttributeNS(parent::XML_SCHEMA_NAMESPACE_URI, 'xsi:schemaLocation',
            OAI_PMH_NAMESPACE_URI.' '.OAI_PMH_SCHEMA_URI);
    
        $responseDate = $this->document->createElement('responseDate', 
            OaiPmhRepository_UtcDateTime::unixToUtc(time()));
        $root->appendChild($responseDate);

        $this->request = $this->document->createElement('request',
            OAI_PMH_BASE_URL);

        $root->appendChild($this->request);
        
        $this->metadataFormats = $this->getFormats();
        
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
                $this->resumeListResponse($resumptionToken);
            /* ListRecords and ListIdentifiers use a common code base and share
               all possible arguments, and are handled by one function. */
            else if($verb == 'ListRecords' || $verb == 'ListIdentifiers')
                $this->initListResponse();
            else {
                /* This Inflector use means verb-implementing functions must be
                   the lowerCamelCased version of the verb name. */
                $functionName = Inflector::variablize($verb);
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
        if($_SERVER['REQUEST_METHOD'] == 'GET' && (urldecode($_SERVER['QUERY_STRING']) != urldecode(http_build_query($this->query))))
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Duplicate arguments in request.");
        
        $keys = array_keys($this->query);
        
        foreach(array_diff($requiredArgs, $keys) as $arg)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Missing required argument $arg.");
        foreach(array_diff($keys, $requiredArgs, $optionalArgs) as $arg)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Unknown argument $arg.");
                
        $from = $this->query['from'];
        $until = $this->query['until'];
        
        $fromGran = OaiPmhRepository_UtcDateTime::getGranularity($from);
        $untilGran = OaiPmhRepository_UtcDateTime::getGranularity($until);
        
        if($from && !$fromGran)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Invalid date/time argument.");
        if($until && !$untilGran)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Invalid date/time argument.");
        if($from && $until && $fromGran != $untilGran)
            OaiPmhRepository_Error::throwError($this, OAI_ERR_BAD_ARGUMENT,
                "Date/time arguments of differing granularity.");
                
        $metadataPrefix = $this->query['metadataPrefix'];
        
        if($metadataPrefix && !array_key_exists($metadataPrefix, $this->metadataFormats))
            OaiPmhRepository_Error::throwError($this, OAI_ERR_CANNOT_DISSEMINATE_FORMAT);
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
        $identify = $this->createElementWithChildren(
            $this->document->documentElement, 'Identify', $elements);
        
        $description = $this->document->createElement('description');
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

        if(!$this->error) {
            $getRecord = $this->document->createElement('GetRecord');
            $this->document->documentElement->appendChild($getRecord);
            $record = new $this->metadataFormats[$metadataPrefix]($item, $getRecord);
            $record->appendRecord();
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
            $listMetadataFormats = $this->document->createElement('ListMetadataFormats');
            $this->document->documentElement->appendChild($listMetadataFormats);
            foreach($this->metadataFormats as $format) {
                $formatObject = new $format(null, $listMetadataFormats);
                $formatObject->declareMetadataFormat();
            }
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
            
        $listSets = $this->document->createElement('ListSets');     

        if(!$this->error) {
            $this->document->documentElement->appendChild($listSets); 
            foreach ($collections as $collection) {
                $elements = array( 'setSpec' => $collection->id,
                                   'setName' => $collection->name );
                $this->createElementWithChildren($listSets, 'set', $elements);
            }
        }
    }
    
    /**
     * Responds to the ListIdentifiers and ListRecords verbs.
     *
     * Only called for the initial request in the case of multiple incomplete
     * list responses
     *
     * @uses listResponse()
     */
    private function initListResponse()
    {
        $from = $this->query['from'];
        $until = $this->query['until'];
        
        if($from)
            $fromDate = OaiPmhRepository_UtcDateTime::utcToDb($from);
        if($until)
            $untilDate = OaiPmhRepository_UtcDateTime::utcToDb($until);
        
        $this->listResponse($this->query['verb'], 
                            $this->query['metadataPrefix'],
                            0,
                            $this->query['set'],
                            $fromDate,
                            $untilDate);
    }
    
    /**
     * Returns the next incomplete list response based on the given resumption
     * token.
     *
     * @param string $token Resumption token
     * @uses listResponse()
     */
    private function resumeListResponse($token)
    {
        $tokenTable = get_db()->getTable('OaiPmhRepositoryToken');
        $tokenTable->purgeExpiredTokens();
        
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
        
        $itemTable = get_db()->getTable('Item');
        $select = $itemTable->getSelect();
        $itemTable->filterByPublic($select, true);
        if($set)
            $itemTable->filterByCollection($select, $set);
        if($from) {
            $select->joinLeft(array('er' => "{$db->prefix}entities_relations"),
                        'i.id = er.relation_id AND er.type = "Item"', array());
            $select->where('er.time >= ? OR i.added >= ?', $from);
            $select->group('i.id');
        }
        if($until) {
            $select->joinLeft(array('er' => "{$db->prefix}entities_relations"),
                        'i.id = er.relation_id AND er.type = "Item"');
            $select->where('er.time <= ? OR i.added <= ?', $until);
            $select->group('i.id');
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
            
            $verbElement = $this->document->createElement($verb);
            $this->document->documentElement->appendChild($verbElement);
            foreach($items as $item) {
                $record = new $this->metadataFormats[$metadataPrefix]($item, $verbElement);
                $record->$method();
                // Drop Item from memory explicitly
                release_object($this->item);
            }
            if($rows > ($cursor + $listLimit)) {
                $token = $this->createResumptionToken($verb,
                                                      $metadataPrefix,
                                                      $cursor + $listLimit,
                                                      $from,
                                                      $until,
                                                      $set);

                $tokenElement = $this->document->createElement('resumptionToken', $token->id);
                $tokenElement->setAttribute('expirationDate',
                    OaiPmhRepository_UtcDateTime::dbToUtc($token->expiration));
                $tokenElement->setAttribute('completeListSize', $rows);
                $tokenElement->setAttribute('cursor', $cursor);
                $verbElement->appendChild($tokenElement);
            }
            else if($cursor != 0) {
                $tokenElement = $this->document->createElement('resumptionToken');
                $verbElement->appendChild($tokenElement);
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
        $resumptionToken->expiration = OaiPmhRepository_UtcDateTime::unixToDb(
            time() + (get_option('oaipmh_repository_expiration_time') * 60 ) );
        $resumptionToken->save();
        
        return $resumptionToken;
    }
    
    
    /**
     * Builds an array of entries for all included metadata mapping classes.
     * Derived heavily from OaipmhHarvester's getMaps().
     *
     * @return array An array, with metadataPrefix => class.
     */
    private function getFormats()
    {
        $dir = new DirectoryIterator(OAI_PMH_REPOSITORY_METADATA_DIRECTORY);
        $metadataFormats = array();
        foreach ($dir as $dirEntry) {
            if ($dirEntry->isFile() && !$dirEntry->isDot()) {
                $filename = $dirEntry->getFilename();
                $pathname = $dirEntry->getPathname();
                // Check for all PHP files, ignore the abstract class
                if(preg_match('/^(.+)\.php$/', $filename, $match) && $match[1] != 'Abstract') {
                    require_once($pathname);
                    $class = "OaiPmhRepository_Metadata_${match[1]}";
                    $object = new $class(null, null);
                    $metadataFormats[$object->getMetadataPrefix()] = $class;
                }
            }
        }
        return $metadataFormats;
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
        return $this->document->saveXML();
    }
}
