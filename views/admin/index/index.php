<?php
/**
 * Admin page view
 *
 * Provides the administrator-only view for the plugin. 
 *
 * @package OaiPmhRepository
 * @subpackage Views
 * @author John Flatness <jflatnes@vt.edu>
 */
$head = array('body_class' => 'oaipmh-repository primary',
              'title'      => 'OAI-PMH Repository');
head($head);
?>

<h1><?php echo $head['title'];?></h1>

<div id="primary">

<?php echo flash(); ?>

   <h2>Hello, World!</h2>
</div>

<?php foot(); ?>
