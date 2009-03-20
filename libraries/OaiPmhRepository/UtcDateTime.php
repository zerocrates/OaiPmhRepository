<?php
/**
 * @package OaiPmhRepository
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Contains utility functions for dealing with datestamps, converting to and
 * from OAI-PMH's required UTCdatetime format.
 *
 * @package OaiPmhRepository
 */
class OaiPmhRepository_UtcDateTime
{
    /**
     * PHP date() format string to produce the required date format.
     * Must be used with gmdate() to conform to spec.
     */
    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';
    
    /**
     * Returns the current time in OAI-PMH's specified ISO 8601 format.
     *
     * @return string Current time in ISO 8601 format
     */
    static function currentTime()
    {
        return gmdate(self::DATE_FORMAT);
    }
    
    /**
     * Converts the given Unix timestamp to OAI-PMH's specified ISO 8601 format.
     *
     * @param int $timestamp Unix timestamp
     * @return string Time in ISO 8601 format
     */
    static function convertToUtcDateTime($timestamp)
    {
        return gmdate(self::DATE_FORMAT, $timestamp);
    }

    /**
     * Converts the given time string to OAI-PMH's specified ISO 8601 format.
     * Used to convert the timestamps output from the Omeka database.
     *
     * @param string $databaseTime Database time string
     * @return string Time in ISO 8601 format
     * @uses convertToUtcDateTime
     */
    static function dbTimeToUtc($databaseTime)
    {
        return self::convertToUtcDateTime(strtotime($databaseTime));
    }
}
?>
