<?php
/**
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright John Flatness, Center for History and New Media, 2013-2014
 * @package OaiPmhRepository
 */

define('OAI_PMH_BASE_URL',
    WEB_ROOT . '/'
    . (($baseUrl = get_option('oaipmh_repository_base_url'))
        ? $baseUrl
        : 'oai-pmh-repository/request'));
define('OAI_PMH_REPOSITORY_PLUGIN_DIRECTORY', dirname(__FILE__));
define('OAI_PMH_REPOSITORY_METADATA_DIRECTORY', OAI_PMH_REPOSITORY_PLUGIN_DIRECTORY . '/metadata');

/**
 * OaiPmhRepository plugin class
 *
 * @package OaiPmhRepository
 */
class OaiPmhRepositoryPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'install',
        'uninstall',
        'config_form',
        'config',
        'define_routes',
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'admin_dashboard_panels',
    );

    /**
     * @var array Options and their default values.
     */
    protected $_options = array(
        'oaipmh_repository_base_url' => 'oai-pmh-repository/request',
        'oaipmh_repository_name',
        'oaipmh_repository_namespace_id',
        'oaipmh_repository_expose_files' => 1,
        'oaipmh_repository_expose_empty_collections' => 1,
        'oaipmh_repository_expose_item_type' => 0,
    );

    /**
     * OaiPmhRepository install hook.
     */
    public function hookInstall()
    {
        $this->_options['oaipmh_repository_name'] = get_option('site_title');
        $this->_options['oaipmh_repository_namespace_id'] = $this->_getServerName();
        $this->_installOptions();

        $db = get_db();
        /* Table: Stores currently active resumptionTokens

           id: primary key (also the value of the token)
           verb: Verb of original request
           metadata_prefix: metadataPrefix of original request
           cursor: Position of cursor within result set
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
            PRIMARY KEY  (`id`),
            INDEX(`expiration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ";
        $db->query($sql);
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        // Old options in config.ini.
        delete_option('oaipmh_repository_record_limit');
        delete_option('oaipmh_repository_expiration_time');
        $this->_uninstallOptions();

        $db = get_db();
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}oai_pmh_repository_tokens`;";
        $db->query($sql);
    }

    /**
     * Display the config form.
     */
    public function hookConfigForm($args)
    {
        $view = $args['view'];
        echo $view->partial(
            'plugins/oai-pmh-repository-config-form.php',
            array(
                'view' => $view,
            )
        );
    }

    /**
      * Handle the config form.
      */
    public function hookConfig($args)
    {
        $post = $args['post'];
        foreach ($post as $key => $value) {
            set_option($key, $value);
        }
    }

    /**
     * Define routes.
     *
     * @param Zend_Controller_Router_Rewrite $router
     */
    public function hookDefineRoutes($args)
    {
        if (is_admin_theme()) {
            return;
        }

        // If base url is not set, use the default module/controller/action.
        $route = get_option('oaipmh_repository_base_url');
        if (empty($route) || $route == 'oai-pmh-repository/request') {
            return;
        }

        $args['router']->addRoute('oai-pmh-repository', new Zend_Controller_Router_Route(
            $route,
            array(
                'module' => 'oai-pmh-repository',
                'controller' => 'request',
                'action' => 'index',
        )));
    }

    /**
     * Filter to add a dashboard panel.
     */
    public function filterAdminDashboardPanels($panels)
    {
        $html = '<h2>' . __('OAI-PMH Repository') . '</h2>';
        $html .= '<p>' . __('Harvester can access metadata from this site: %s.', sprintf('<a href="%s">%s</a>', OAI_PMH_BASE_URL, OAI_PMH_BASE_URL)) . '</p>';
        $panels[] = $html;

        return $panels;
    }

    private function _getServerName()
    {
        $name = preg_replace('/[^a-z0-9\-\.]/i', '', $_SERVER['SERVER_NAME']);
        if ($name == 'localhost') {
            $name = 'default.must.change';
        }

        return $name;
    }
}
