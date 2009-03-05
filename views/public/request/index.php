<?php
/**
 * Request view
 *
 * The view for the outward-facing request page.  Simply outputs the XML
 * passed in by the controller.
 *
 * @package OaiPmhRepository
 * @subpackage Views
 * @author John Flatness, Yu-Hsun Lin
 */
header('Content-Type: text/xml');

echo $response;
?>
