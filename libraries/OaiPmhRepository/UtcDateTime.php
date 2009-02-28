<?php

class OaiPmhRepository_UtcDateTime
{
    /**
     * PHP date() format string to produce the required date format.
     * Must be used with gmdate() to conform to spec.
     */
    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';
    /**
     * Returns the current time in OAI-PMH's specified ISO 8601 format.
     */
    static function currentTime()
    {
        return gmdate(self::DATE_FORMAT);
    }
    
    static function convertToUtcDateTime()
    {
        //unsure what's involved here yet, need to see if database stores local
        //time, utc, and in what format
        //may condense these to one function with optional timestamp param
    }
}
?>
