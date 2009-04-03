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
    <?php echo __v()->formText('oaipmh_repository_name', $repoName, null);?>
    <p class="explanation">Name for this OAI-PMH repository.</p>
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
<div class="field">
    <label for="oaipmh_repository_list_limit">List response limit</label>
    <?php echo __v()->formText('oaipmh_repository_list_limit', $listLimit, null);?>
    <p class="explanation">Number of individual items that can be returned in a
    response at once.  Larger values will increase memory usage but reduce the
    number of database queries and HTTP requests.  Smaller values will reduce
    memory usage but increase the number of DB queries and requests.</p>
</div>
<div class="field">
    <label for="oaipmh_repository_expiration_time">List expiration time</label>
    <?php echo __v()->formText('oaipmh_repository_expiration_time', $expirationTime, null);?>
    <p class="explanation">Amount of time in minutes a resumptionToken is valid for.
    The specification suggests a number in the tens of minutes.</p>
</div>
