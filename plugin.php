<?php 
/**
 * Main plugin script
 *
 * Main script for the plugin, sets up hooks and filters to the Omeka API.
 *
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
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
    set_option('oaipmh_repository_list_limit', 50);
    set_option('oaipmh_repository_expiration_time', 10);
    
    $db = get_db();
    
    /* Table: Stores currently active resumptionTokens
       
       id: primary key (also the value of the token)
       verb: Verb of original request
       metadata_prefix: metadataPrefix of original request
       from: Optional from argument of original request
       until: Optional until argument of original request
       set: Optional set argument of original request
       expiration: Datestamp after which token is expired
    */
    $sql = "
    CREATE TABLE IF NOT EXISTS `{$db->prefix}oai_pmh_repository_tokens` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `verb` ENUM('ListIdentifiers', 'ListRecords', 'ListSets') COLLATE utf8_unicode_ci NOT NULL,
        `metadata_prefix` TEXT COLLATE utf8_unicode_ci NOT NULL,
        `cursor` INT(10) UNSIGNED NOT NULL,
        `from` DATETIME DEFAULT NULL,
        `until` DATETIME DEFAULT NULL,
        `set` INT(10) UNSIGNED DEFAULT NULL,
        `expiration` DATETIME NOT NULL,
        PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db->query($sql);
}

/**
 * config_form callback
 */
function oaipmh_repository_config_form()
{
    $repoName = get_option('oaipmh_repository_name');
    $namespaceID = get_option('oaipmh_repository_namespace_id');
    $listLimit = get_option('oaipmh_repository_list_limit');
    $expirationTime = get_option('oaipmh_repository_expiration_time');
    include('config_form.php');
}

/**
 * config callback
 */ 
function oaipmh_repository_config()
{
    set_option('oaipmh_repository_name', $_POST['oaipmh_repository_name']);
    set_option('oaipmh_repository_namespace_id', $_POST['oaipmh_repository_namespace_id']);
    set_option('oaipmh_repository_list_limit', $_POST['oaipmh_repository_list_limit']);
    set_option('oaipmh_repository_expiration_time', $_POST['oaipmh_repository_expiration_time']);
}

/**
 * uninstall callback
 */
function oaipmh_repository_uninstall()
{
    delete_option('oaipmh_repository_plugin_version');
    delete_option('oaipmh_repository_name');
    delete_option('oaipmh_repository_namespace_id');
    delete_option('oaipmh_repository_record_limit');
    delete_option('oaipmh_repository_expiration_time');
    
    $db = get_db();
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}oai_pmh_repository_tokens`;";
    $db->query($sql);
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
