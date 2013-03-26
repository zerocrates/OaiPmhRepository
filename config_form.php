<?php 
/**
 * Config form include
 *
 * Included in the configuration page for the plugin to change settings.
 *
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
?>

<div class="field">
    <label for="oaipmh_repository_name">Repository name</label>
    <?php echo get_view()->formText('oaipmh_repository_name', $repoName);?>
    <p class="explanation">Name for this OAI-PMH repository.</p>
</div>
<div class="field">
    <label for="oaipmh_repository_namespace_id">Namespace identifier</label>
    <?php echo get_view()->formText('oaipmh_repository_namespace_id', $namespaceID);?>
    <p class="explanation">This will be used to form
    globally unique IDs for the exposed metadata items.  This value is required
    to be a domain name you have registered.  Using other values will generate
    invalid identifiers.</p>
</div>
<div class="field">
    <label for="oaipmh_repository_expose_files">Expose files</label>
    <?php echo get_view()->formCheckbox('oaipmh_repository_expose_files', $exposeFiles, null, 
        array('checked' => '1', 'unChecked' => '0'));?>
    <p class="explanation">Whether the plugin should include identifiers for the
    files associated with items.  This provides harvesters with direct access to
    files.</p>
</div>
