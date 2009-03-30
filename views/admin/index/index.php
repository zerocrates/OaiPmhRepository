<?php
/**
 * Admin page view
 *
 * Provides the administrator-only view for the plugin. 
 *
 * @package OaiPmhRepository
 * @subpackage Views
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

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
      <a href="<?php echo OAI_PMH_BASE_URL ?>"><?php echo OAI_PMH_BASE_URL ?></a>.
   </p>
</div>

<?php foot(); ?>
