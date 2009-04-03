<?php
/**
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Model class for resumption token table.
 *
 * @package OaiPmhRepository
 * @subpackage Models
 */
class OaiPmhRepositoryTokenTable extends Omeka_Db_Table
{
    /**
     * Deletes the rows for expired tokens from the table.
     */
    public function purgeExpiredTokens()
    {
        /* This really should just use $this->_name, but that property only
           seems to be set sporadically, particularly for plugin tables.  For
           now, the table name is hardcoded. */
        $db = get_db();
        $db->delete("{$db->prefix}oai_pmh_repository_tokens", 'expiration <= NOW()');
    }
}
