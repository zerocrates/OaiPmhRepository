<?php
/**
 * Admin page view
 *
 * Provides the administrator-only view for the plugin. 
 *
 * @package OaiPmhRepository
 * @subpackage Views
 * @author John Flatness, Yu-Hsun Lin
 */

define('OAI_PMH_URI', WEB_ROOT.'/oai-pmh-repository/request');

$head = array('body_class' => 'oaipmh-repository primary',
              'title'      => 'OAI-PMH Repository');
head($head);
?>

<h1><?php echo $head['title'];?></h1>

<div id="primary">

<?php echo flash(); ?>

   <p>This Omeka installation is now exposing the metadata for its items as a
      repository conforming to the Open Archives Initiative Protocol for
      Metadata Harvesting.
   </p>
   <p>   
      The respository is located at 
      <a href="<?php echo OAI_PMH_URI ?>"><?php echo OAI_PMH_URI ?></a>.
   </p>
</div>

<?php foot(); ?>
