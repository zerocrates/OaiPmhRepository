<?php 
/**
 * Main plugin script
 *
 * Main script for the plugin, sets up hooks and filters to the Omeka API.
 * @package OaiPmhRepository
 * @author John Flatness <jflatnes@vt.edu>
 */
define('OAIPMH_REPOSITORY_PLUGIN_VERSION', '0.0.1');

require_once('OaiPmhRepository/ResponseGenerator.php');

add_plugin_hook('install', 'oaipmh_repository_install');
add_plugin_hook('config_form', 'oaipmh_repository_config_form');
add_plugin_hook('config', 'oaipmh_repository_config');
add_plugin_hook('uninstall', 'oaipmh_repository_uninstall');
add_filter('admin_navigation_main', 'oaipmh_repository_admin_navigation_main');

/**
 * install callback
 */
function oaipmh_repository_install()
{
    set_option('oaipmh_repository_plugin_version', OAIPMH_REPOSITORY_PLUGIN_VERSION);
    set_option('oaipmh_repository_name', get_option('site_title'));
    set_option('oaipmh_repository_namespace_id', 'default.must.change');
}

/**
 * config_form callback
 */
function oaipmh_repository_config_form()
{
    $repoName = get_option('oaipmh_repository_name');
    $namespaceID = get_option('oaipmh_repository_namespace_id');
    include('config_form.php');
}

/**
 * config callback
 */ 
function oaipmh_repository_config()
{
    set_option('oaipmh_repository_name', $_POST['oaipmh_repository_name']);
    set_option('oaipmh_repository_namespace_id', $_POST['oaipmh_repository_namespace_id']);
}

/**
 * uninstall callback
 */
function oaipmh_repository_uninstall()
{
    delete_option('oaipmh_repository_plugin_version');
    delete_option('oaipmh_repository_name');
    delete_option('oaipmh_repository_namespace_id');
}

/**
 * admin_navigation_main filter
 * @param array $tabs array of admin navigation tabs
 */
function oaipmh_repository_admin_navigation_main($tabs)
{
    $tabs['OAI-PMH Repository'] = uri('oai-pmh-repository');
    return $tabs;
}
?>
