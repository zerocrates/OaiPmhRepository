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
    public function indexAction()
    {
	$response = new OaiPmhRepository_ResponseGenerator();
	
	switch($_SERVER['REQUEST_METHOD'])
	{
	    case 'GET': $query = &$_GET; break;
	    case 'POST': $query = &$_POST; break;
	    default: die('Error determining request.');
	}
	switch($query['verb'])
	{
	    case 'Identify': $response->identify(); break;
	    default: $response->throwError('badVerb', 'Invalid or no verb specified.');
	}
	$this->view->response = $response;
    }
}
?>
