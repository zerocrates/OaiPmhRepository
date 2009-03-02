<?php
/**
 * @package OaiPmhRepository
 * @subpackage Controllers
 * @author John Flatness <jflatnes@vt.edu>
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
 */
class OaiPmhRepository_RequestController extends Omeka_Controller_Action
{
    private $response;
    private $query;

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
            default: 
                $this->response->throwError('badVerb', 'Invalid or no verb specified.');
        }
        $this->view->response = $this->response;
    }

    private function checkArguments($numArgs, $requiredArgs = array())
    {
        foreach($requiredArgs as $arg)
        {
            if(!isset($this->query[$arg]))
                $this->response->throwError('badArgument', "Missing argument '$arg'");
        }
        if(count($this->query) != $numArgs + 1)
            $this->response->throwError('badArgument', "Specified verb takes $numArgs arguments.");
    }
}
?>
