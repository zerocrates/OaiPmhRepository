<?php
/**
 * Config form include
 *
 * Included in the configuration page for the plugin to change settings.
 *
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009-2014 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
?>

<fieldset id="fieldset-oaipmhrepository"><legend><?php echo __('OAI-PMH Repository'); ?></legend>
<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('oaipmh_repository_base_url',
            __('Repository base url')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __('Base URL for this OAI-PMH repository.');
            echo ' ' . __('Default is "oai-pmh-repository/request" (always available), but it can be "oai/request" or simply "oai-pmh".'); ?>
        </p>
        <p class="explanation">
            <?php echo __('Currently, harvesters can access metadata from this url: %s.', sprintf('<a href="%s">%s</a>', OAI_PMH_BASE_URL, OAI_PMH_BASE_URL)); ?></p>
        </p>
        <?php echo $view->formText('oaipmh_repository_base_url', get_option('oaipmh_repository_base_url')); ?>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('oaipmh_repository_name',
            __('Repository name')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __('Name for this OAI-PMH repository.'); ?>
        </p>
        <?php echo $view->formText('oaipmh_repository_name', get_option('oaipmh_repository_name')); ?>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('oaipmh_repository_namespace_id',
            __('Namespace identifier')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __('This will be used to form globally unique IDs for the exposed metadata items.');
            echo ' ' . __('This value is required to be a domain name you have registered.');
            echo ' ' . __('Using other values will generate invalid identifiers.'); ?>
        </p>
        <?php echo $view->formText('oaipmh_repository_namespace_id', get_option('oaipmh_repository_namespace_id')); ?>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('oaipmh_repository_expose_files',
            _('Expose files')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __('Whether the plugin should include identifiers for the files associated with items.');
            echo __('This provides harvesters with direct access to files.'); ?>
        </p>
        <?php echo $view->formCheckbox('oaipmh_repository_expose_files', true,
            array('checked' => (boolean) get_option('oaipmh_repository_expose_files'))); ?>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('oaipmh_repository_expose_empty_collections',
            __('Expose empty collections')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __('Whether the plugin should expose empty public collections.'); ?>
        </p>
        <?php echo $view->formCheckbox('oaipmh_repository_expose_empty_collections', true,
            array('checked' => (boolean) get_option('oaipmh_repository_expose_empty_collections'))); ?>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('oaipmh_repository_expose_item_type',
            __('Expose item type')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __('Whether the plugin should expose the item type as Dublin Core Type.'); ?>
        </p>
        <?php echo $view->formCheckbox('oaipmh_repository_expose_item_type', true,
            array('checked' => (boolean) get_option('oaipmh_repository_expose_item_type'))); ?>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <?php echo $view->formLabel('oaipmh_repository_add_human_stylesheet',
            __('Human display')); ?>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            <?php echo __('If checked, a stylesheet will be added to the output, so humans will be able to browse the repository through a themable %sBootstrap%s responsive interface.',
                    '<a href="http://getbootstrap.com/">', '</a>'); ?>
        </p>
        <?php echo $view->formCheckbox('oaipmh_repository_add_human_stylesheet', true,
            array('checked' => (boolean) get_option('oaipmh_repository_add_human_stylesheet'))); ?>
    </div>
</div>
</fieldset>
