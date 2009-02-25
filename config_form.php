<?php 
/**
 * Config form include
 *
 * Included in the configuration page for the plugin to change settings.
 * @package OaiPmhRepository
 * @author John Flatness <jflatnes@vt.edu>
 */
?>
<div class="field">
    <label for="oaipmh_repository_name">Repository name</label>
    <?php echo __v()->formText('oaipmh_repository_name', $repoName, null);?>
    <p class="explanation">Name for this OAI-PMH repository.  May default or
    set to the Omeka installation's name in the future.</p>
</div>
<div class="field">
    <label for="oaipmh_repository_namespace_id">Namespace identifier</label>
    <?php echo __v()->formText('oaipmh_repository_namespace_id', $namespaceID, null);?>
    <p class="explanation">The oai-identifier specification requires
    repositories to specify a namespace identifier.  This will be used to form
    globally unique IDs for the exposed metadata items.  This value is required
    to be a domain name you have registered.  Using other values will generate
    invalid identifiers.</p>
</div>
