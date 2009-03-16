<?php
/**
 * @package OaiPmhRepository
 * @subpackage Controllers
 * @author John Flatness, Yu-Hsun Lin
 */

/**
 * Request page controller
 * 
 * The controller for the outward-facing segment of the repository plugin.  It 
 * processes queries, and produces the response in XML format.
 *
 * @package OaiPmhRepository
 * @subpackage Controllers
 * @uses OaiPmhRepository_ResponseGenerator
 * @uses OaiPmhRepository_Error
 */
class OaiPmhRepository_RequestController extends Omeka_Controller_Action
{
    private $response;
    private $query;
    
    /**
     * Parses and verifies POST/GET variables.
     *
     * @uses checkArguments
     */
    public function indexAction()
    {
        $this->response = new OaiPmhRepository_ResponseGenerator();
    
        switch($_SERVER['REQUEST_METHOD'])
        {
            case 'GET': $this->query = &$_GET; break;
            case 'POST': $this->query = &$_POST; break;
            default: die('Error determining request.');
        }
        switch($this->query['verb'])
        {
            case 'Identify': 
                $this->checkArguments(0);
                $this->response->identify(); 
                break;
            case 'GetRecord': 
                $requiredArguments = array('identifier', 'metadataPrefix');
                $this->checkArguments(2, $requiredArguments);
                $this->response->getRecord($this->query['identifier'], $this->query['metadataPrefix']);
                break;
             case 'ListRecords': 
                $requiredArguments = array('metadataPrefix');
                $this->checkArguments(1, $requiredArguments);
                $this->response->listRecords($this->query['metadataPrefix']);
                break;
            case 'ListIdentifiers': 
                $requiredArguments = array('metadataPrefix');
                $this->checkArguments(1, $requiredArguments);
                $this->response->listIdentifiers($this->query['metadataPrefix']);
                break;
            case 'ListSets':
                //will change in the future, but we currently don't support sets
                //should map to Omeka collections
                $this->response->listSets();
                break;
            case 'ListMetadataFormats':
                $this->response->listMetadataFormats($this->query['identifier']);
                break;
            default:
                OaiPmhRepository_Error::throwError($this->response, OAI_ERR_BAD_VERB);
        }
        $this->view->response = $this->response;
    }
    
    /**
     * Checks the argument list from the POST/GET query.
     *
     * Checks if there are the required number of arguments, and the required
     * argument types. 
     *
     * @todo Extend to optional arguments, repeated arguments.
     * @param int numArgs Number of required arguments.
     * @param array requiredArgs Array of required argument names.
     * @return bool True if arguments verify, false otherwise.
     */
    private function checkArguments($numArgs, $requiredArgs = array())
    {
        foreach($requiredArgs as $arg)
        {
            if(!isset($this->query[$arg]))
                OaiPmhRepository_Error::throwError($this->response, OAI_ERR_BAD_ARGUMENT, "Missing argument $arg.");
        }
        if(count($this->query) != $numArgs + 1)
            OaiPmhRepository_Error::throwError($this->response, OAI_ERR_BAD_ARGUMENT, "Specified verb takes $numArgs arguments.");
    }
}
?>
