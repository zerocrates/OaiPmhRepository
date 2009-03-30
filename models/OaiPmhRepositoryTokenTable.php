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
        //$where = $this->getAdapter()->quoteInto('expiration <= ?', OaiPmhRepository_UtcDateTime::unixToDb(time()));
        //die($this);
        get_db()->delete($this->_name, 'expiration <= NOW()');
    }
}
