<?php
/**
 * Request view
 *
 * The view for the outward-facing request page.  Simply outputs the XML
 * passed in by the controller.
 *
 * @package OaiPmhRepository
 * @subpackage Views
 * @author John Flatness <jflatnes@vt.edu>
 */
header('Content-Type: text/xml');

echo $response;
?>
