<?php
/**
 * @package OaiPmhRepository
 * @subpackage Libraries
 * @author John Flatness
 * @copyright Copyright 2012 John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */


/**
 * Class containing static functions for date tasks.
 *
 * @package OaiPmhRepository
 * @subpackage Libraries
 */
class OaiPmhRepository_Date {
    
    /**
     * PHP date() format string to produce the required date format.
     * Must be used with gmdate() to conform to spec.
     */
    const OAI_DATE_FORMAT = 'Y-m-d\TH:i:s\Z';
    const DB_DATE_FORMAT  = 'Y-m-d H:i:s';
    
    /**
     * Converts the given Unix timestamp to OAI-PMH's specified ISO 8601 format.
     *
     * @param int $timestamp Unix timestamp
     * @return string Time in ISO 8601 format
     */
    static function unixToUtc($timestamp)
    {
        return gmdate(self::OAI_DATE_FORMAT, $timestamp);
    }
    
    /**
     * Converts the given Unix timestamp to the Omeka DB's datetime format.
     *
     * @param int $timestamp Unix timestamp
     * @return string Time in Omeka DB format
     */
    static function unixToDb($timestamp)
    {
       return date(self::DB_DATE_FORMAT, $timestamp);
    }

    /**
     * Converts the given time string to OAI-PMH's specified ISO 8601 format.
     * Used to convert the timestamps output from the Omeka database.
     *
     * @param string $databaseTime Database time string
     * @return string Time in ISO 8601 format
     * @uses unixToUtc()
     */
    static function dbToUtc($databaseTime)
    {
        return self::unixToUtc(strtotime($databaseTime));
    }
    
    /**
     * Converts the given time string to the Omeka database's format.
     *
     * @param string $databaseTime Database time string
     * @return string Time in Omeka DB format
     * @uses unixToDb()
     */
    static function utcToDb($utcDateTime)
    {
       return self::unixToDb(strtotime($utcDateTime));
    }
}
