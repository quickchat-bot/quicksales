<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * The Date Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Date extends SWIFT_Library
{
    // Used by getCalendarDateFormats
    // RML
    const DATE_FORMAT_HTML_US_HOUR = 'M d Y g:i A';
    const DATE_FORMAT_HTML_EU_HOUR = 'd M Y H:i';
    const DATE_FORMAT_CAL_US_HOUR = '%b %d %Y %l:%M %p';
    const DATE_FORMAT_CAL_EU_HOUR = '%d %b %Y %H:%M';
    const DATE_FORMAT_HTML_US = 'M d Y';
    const DATE_FORMAT_HTML_EU = 'd M Y';
    const DATE_FORMAT_CAL_US = 'mm/dd/yy';
    const DATE_FORMAT_CAL_EU = 'dd/mm/yy';
    const DATE_FORMAT_HOUR_US = '12';
    const DATE_FORMAT_HOUR_EU = '24';
    const TIME_FORMAT_US = 'g:i A';
    const TIME_FORMAT_EU = 'H:i';

    const DATE_HOUR = 3600;
    const TYPE_TIME = 1;
    const TYPE_DATETIME = 2;
    const TYPE_DATE = 3;
    const TYPE_CUSTOM = 4;
    const TYPE_DATEFULL = 5;

    // Used globally
    // RML
    const DATE_ONE_DAY = 86400;
    const DATE_ONE_WEEK = 604800;

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Explode a Chunk and return the first part
     *
     * @author Varun Shoor
     * @param string $_chunk The Chunk to Explode
     * @return string The First Part of the Chunk
     */
    static private function ExplodeChunk($_chunk)
    {
        $_explodeResult = explode('.', $_chunk);

        return $_explodeResult[0];
    }

    /**
     * Returns a time line like 2d1h3m in colored format
     *
     * @author Varun Shoor
     * @param int $_difference The Number of Seconds
     * @param bool $_flipColors Whether to flip the color system
     * @param bool $_noColors Whether to use no colors at all
     * @return string
     */
    public static function ColorTime($_difference, $_flipColors = false, $_noColors = false)
    {
        $_SWIFT      = SWIFT::GetInstance();
        $_isNegative = false;

        $_colorSecond = $_colorMinute = $_colorHour = $_colorDay = '';
        if ($_flipColors == true && $_noColors == false) {
            $_colorSecond = 'textred';
            $_colorMinute = 'textorange';
            $_colorHour   = 'textblue';
            $_colorDay    = 'textgreen';
        } else if ($_flipColors == false && $_noColors == false) {
            $_colorSecond = 'textgreen';
            $_colorMinute = 'textblue';
            $_colorHour   = 'textorange';
            $_colorDay    = 'textred';
        }
        /**
         * BUG FIX - Nidhi Gupta <nidhi.gupta@opencart.com.vn>
         *
         * SWIFT-4764 Negative first response time should be correct and in human readable format in reports
         *
         * Comments : Allowing calculation for -ve values and setting flag at the end.
         */

        if ($_difference < 0) {
            $_difference = abs($_difference);
            $_isNegative = true;
        }
        $_hour    = self::ExplodeChunk(floor($_difference / 3600));
        $_minute  = self::ExplodeChunk(floor(($_difference % 3600) / 60));
        $_seconds = self::ExplodeChunk($_difference % 60);

        if ($_hour > 0) {
            if ($_hour >= 24) {
                $_day = floor($_hour/24);
                $_actualHour = $_hour - ($_day * 24);

                $_response = IIF($_noColors == false, '<div class=\'' . $_colorDay . '\'>') . sprintf($_SWIFT->Language->Get('vardate1'), $_day, $_actualHour, $_minute, $_seconds) . IIF($_noColors == false, '</div>');
            } else {
                $_response = IIF($_noColors == false, '<div class=\'' . $_colorHour . '\'>') . sprintf($_SWIFT->Language->Get('vardate2'), $_hour, $_minute, $_seconds) . IIF($_noColors == false, '</div>');
            }
        } else if ($_minute > 0 && $_hour <= 0) {
            $_response = IIF($_noColors == false, '<div class=\'' . $_colorMinute . '\'>') . sprintf($_SWIFT->Language->Get('vardate3'), $_minute, $_seconds) . IIF($_noColors == false, '</div>');
        } else {
            $_response = IIF($_noColors == false, '<div class=\'' . $_colorSecond . '\'>') . sprintf($_SWIFT->Language->Get('vardate4'), $_seconds) . IIF($_noColors == false, '</div>');
        }

        if ($_isNegative) {
            $_response = '-' . $_response;
        }

        return $_response;
    }

    /**
     * Returns an easy date    format
     *
     * @author Varun Shoor
     * @param int $_timeStamp
     * @return string|bool
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function EasyDate($_timeStamp)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_timeStamp)) {
            return '';
        }

//        echo 'Timestamp: ' . date('d M Y h:i:s A', $_timeStamp) . SWIFT_CRLF . 'Datenow: ' . date('d M Y h:i:s A', DATENOW) . SWIFT_CRLF;

        $_difference = DATENOW-$_timeStamp;

        if ($_difference <= 0) {
            return '';
        }

        $_year = floor($_difference/(86400*365));
        $_month = floor($_difference/(86400*30));
        $_day = floor($_difference/86400);
        $_hour = floor($_difference/3600);
        $_minute = floor($_difference/60);
        $_seconds = $_difference;

//        echo 'Year: ' . $_year . SWIFT_CRLF . 'Month: ' . $_month . SWIFT_CRLF . 'Day: ' . $_day . SWIFT_CRLF . 'Hour: ' . $_hour . SWIFT_CRLF . 'Minute: ' . $_minute . SWIFT_CRLF . 'Seconds: ' . $_seconds . SWIFT_CRLF;

        if ($_year > 0) {
            if ($_year == 1) {
                return $_SWIFT->Language->Get('edoneyear');
            } else {
                return sprintf($_SWIFT->Language->Get('edxyear'), $_year);
            }
        } else if ($_month > 0) {
            if ($_month == 1) {
                return $_SWIFT->Language->Get('edonemonth');
            } else {
                return sprintf($_SWIFT->Language->Get('edxmonth'), $_month);
            }
        } else if ($_day > 0) {
            if ($_day == 1) {
                return $_SWIFT->Language->Get('edoneday');
            } else {
                return sprintf($_SWIFT->Language->Get('edxday'), $_day);
            }
        } else if ($_hour > 0) {
            if ($_hour == 1) {
                return $_SWIFT->Language->Get('edonehour');
            } else {
                return sprintf($_SWIFT->Language->Get('edxhour'), $_hour);
            }
        } else if ($_minute > 0) {
            if ($_minute == 1) {
                return $_SWIFT->Language->Get('edoneminute');
            } else {
                return sprintf($_SWIFT->Language->Get('edxminute'), $_minute);
            }
        } else {
            if ($_seconds >= 30) {
                return sprintf($_SWIFT->Language->Get('edxseconds'), $_seconds);
            } else {
                return $_SWIFT->Language->Get('edjustnow');
            }
        }

        return true;
    }

    /**
     * Returns the time/date according to prespecified timezone
     *
     * @author Varun Shoor
     *
     * @param int         $_dateType       The Default Date Type (OPTIONAL)
     * @param bool|int    $_customTimeline (OPTIONAL)
     * @param bool|string $_customFormat   (OPTIONAL)
     * @param bool        $_isGMT          Tells if $_customTimeline is in GMT or in local time zone, only used when $_customTimeline is passed (OPTIONAL)
     *
     * @return string|bool The Processed Date
     */
    public static function Get($_dateType = self::TYPE_DATETIME, $_customTimeline = false, $_customFormat = false, $_isGMT = false, $ignoreDateOnly = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_dateType == self::TYPE_CUSTOM && empty($_customFormat))
        {
            return false;
        }

        // $_customTimeline must be in GMT!
        $_timeLine = ($_customTimeline === false) ? DATENOW : $_customTimeline;
        $_timeZone = SWIFT::Get('timezone');

        /*
         * BUG FIX - Pankaj Garg
         *
         * SWIFT-2299 - "Daylight Savings" does not work for EST Time Zone.
         *
         * Comments: Called New function IsDST to check the DST Enabled for that particular timeZone.
         */

        // If the option for daylight savings is turned off, and this date would normally have DST added,
        // we need to subtract an hour because date() automatically adjusts for DST.
        if (SWIFT::Get('daylightsavings') == false && (int) (self::IsDST($_timeLine, $_timeZone)) == 1)
        {
            $_timeLine -= self::DATE_HOUR;
        }

        if ($_dateType == self::TYPE_TIME)
        {
            $_dateFormat = $_SWIFT->Settings->Get('dt_timeformat');
        } else if ($_dateType == self::TYPE_DATE) {
            $_dateFormat = $_SWIFT->Settings->Get('dt_dateformat');
        } else if ($_dateType == self::TYPE_CUSTOM) {
            $_dateFormat = $_customFormat;
        } else if ($_dateType == self::TYPE_DATEFULL) {
            $_dateFormat = '%B %d';
        } else {
            $_dateFormat = $_SWIFT->Settings->Get('dt_datetimeformat');
        }

        // Fix - Bishwanath Jha - SWIFT-4189 : Issue with Time Zone - skipping transition calculation if its just date.
        $_hasTransition = IIF($_isGMT, (gmdate('His', $_timeLine) > 0), (date('His', $_timeLine) > 0));

        if ($_hasTransition || $ignoreDateOnly) {
            // This will return timeline according to the timezone and DST enabled.
            $_timeLine = self::GetTimestampForZone($_timeLine, $_timeZone);
        }

        // Now set the default timezone to GMT because the timeline is already with added offset.
        date_default_timezone_set('GMT');

        // Get the date adjusted to default timezone that is GMT as set above.
        /**
         * Bug Fix : Saloni Dhall <saloni.dhall@opencart.com.vn>
         *
         * SWIFT-4327 : Security issue (medium)
         *
         * Comments : Sanitizing date format sequence at the time of rendering.
         */
        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-3764: setlocale() statement (under config.php) does not support russian date format
         *
         * Comments: Windows locale returning content in windows-1251, converting it forcefully to utf-8 otherwise it will convert into garbage content
         */
        if (in_array(mb_strtolower(SWIFT_LOCALE), array('rus', 'russian'))) {
            $_returnValue = iconv('windows-1251', $_SWIFT->Language->Get('charset'), strftime($_SWIFT->Input->SanitizeForXSS($_dateFormat), $_timeLine));
        } else {
            $_returnValue = strftime($_SWIFT->Input->SanitizeForXSS($_dateFormat), $_timeLine);

            try {
                if (extension_loaded('intl')) {
                    $dateTime = new \DateTime('@' . $_timeLine, new \DateTimeZone($_timeZone));
                    $format = strftime_format_to_intl_format($_SWIFT->Input->SanitizeForXSS($_dateFormat));
                    $_returnValue = \IntlDateFormatter::formatObject(
                        $dateTime, // a DateTime object
                        $format,  // UCI standard formatted string
                        $_SWIFT->Language->GetLanguageCode()  // the locale
                    );
                }
            } catch (\Exception $ex) {
            }
        }

        // Set the SWIFT timezone as it was earlier.
        date_default_timezone_set($_timeZone);

        return $_returnValue;
    }

    /**
     * Return the Relevant Calendar Date Format
     *
     * @author Varun Shoor
     * @return string The Calendar Date Format
     */
    public static function GetCalendarDateFormat()
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_SWIFT->Settings->Get('dt_caltype') == 'us')
        {
            return 'm/d/Y';
        } else {
            return 'd/m/Y';
        }
    }

    /**
     * Returns the date format(s) to use when constructing a calendar control.
     *
     * Return Value:
     *   Array with the following keys:
     *       "html": This is the format to use in the actual HTML entity's value.
     *       "cal":  This is the format to pass to the calendar control.
     *       "hour": This is the clock format to pass to the calendar control
     *       "usformat": True if the setting is enabled to use US date/time formats
     *
     * @author Ryan M. Lederman
     * @param bool $_useHours Use Hours for Formatting
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public static function GetCalendarDateFormats($_useHours = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_returnValue = array();
        $_returnValue["usformat"] = ($_SWIFT->Settings->Get('dt_caltype') == 'us');

        if ($_useHours)
        {
            $_returnValue["html"] = ($_returnValue["usformat"] ? self::DATE_FORMAT_HTML_US_HOUR : self::DATE_FORMAT_HTML_EU_HOUR);
            $_returnValue["cal"]  = ($_returnValue["usformat"] ? self::DATE_FORMAT_CAL_US_HOUR : self::DATE_FORMAT_CAL_EU_HOUR);
        } else {
            $_returnValue["html"] = ($_returnValue["usformat"] ? self::DATE_FORMAT_HTML_US : self::DATE_FORMAT_HTML_EU);
            $_returnValue["cal"]  = ($_returnValue["usformat"] ? self::DATE_FORMAT_CAL_US : self::DATE_FORMAT_CAL_EU);
        }

        $_returnValue["hour"] = ($_returnValue["usformat"] ? self::DATE_FORMAT_HOUR_US : self::DATE_FORMAT_HOUR_EU);

        return $_returnValue;
    }

    /**
     * Converts Time to Seconds
     *
     * @author Andriy Lesyuk
     * @param string $_timeString
     * @return int|bool The Number of Seconds
     */
    public static function StringToSeconds($_timeString)
    {
        $_timeChunks = explode(':', $_timeString);

        if (count($_timeChunks) > 1) {
            $_timeSeconds = 0;

            if (isset($_timeChunks[2])) {
                $_timeSeconds = (int) ($_timeChunks[2]);
            }

            $_timeSeconds += (int) ($_timeChunks[0]) * 3600;
            $_timeSeconds += (int) ($_timeChunks[1]) * 60;

            return $_timeSeconds;
        }

        return false;
    }

    /**
     * Takes a UNIX epoch value and ensures it is the lowest value for that specific day available,
     * meaning 12 AM and zero seconds.
     *
     * @author Ryan M. Lederman
     * @param int $_dateLine The Date Line to Process
     * @return int The Processed Dateline
     */
    public static function FloorDate($_dateLine)
    {
        return mktime(0, 0, 0, date('m', $_dateLine), date('d', $_dateLine), date('Y', $_dateLine));
    }

    /**
     * Takes a UNIX epoch value and ensures it is the highest value for that specific day available,
     * meaning 11:59 PM and 59 seconds.
     *
     * @author Ryan M. Lederman
     * @param int $_dateLine The Date Line to Process
     * @return int The Processed Dateline
     */
    public static function CeilDate($_dateLine)
    {
        return mktime(23, 59, 59, date('m', $_dateLine), date('d', $_dateLine), date('Y', $_dateLine));
    }

    /**
     * Takes a UNIX epoch value and sets it to the lowest value available for the same month.
     * e.g. May 08 2008 2:05 PM -> May 01 2008 12:00 AM
     *
     * @author Ryan M. Lederman
     * @param int $_dateLine The Date Line to Process
     * @return int The Processed Dateline
     */
    public static function FirstOfTheMonth($_dateLine)
    {
        return mktime(0, 0, 0, date('m', $_dateLine), 1, date('Y', $_dateLine));
    }

    /**
     * Returns a string representation of the first day of the current month.
     *
     * @author Ryan M. Lederman
     * @param string $_dateFormat The Date Format
     * @return string The Processed Dateline
     */
    public static function StringFirstOfTheMonth($_dateFormat)
    {
        return gmdate($_dateFormat, self::FirstOfTheMonth(DATENOW));
    }

    /**
     * Returns UNIX Timestamp For a date
     *
     * @author   Amarjeet Kaur
     *
     * @param int $_month
     * @param int $_day
     * @param int $_year
     *
     * @return int UNIX Timestamp
     */
    public static function GenerateTimestamp($_month, $_day, $_year)
    {
        return mktime(0, 0, 0, $_month, $_day, $_year);
    }

    /**
     * Check Whether or not the date is in daylight saving time
     *
     * @author Pankaj Garg
     *
     * @param int    $_timeLine
     * @param string $_timeZone
     *
     * @return string|bool
     */
    public static function IsDST($_timeLine, $_timeZone)
    {
        if (!class_exists('DateTimeZone')) {
            return gmdate('I', $_timeLine);
        }

        $_DateTimeZone         = new DateTimeZone($_timeZone);
        $_transitionsContainer = $_DateTimeZone->getTransitions($_timeLine, $_timeLine);

        return $_transitionsContainer[0]['isdst'];
    }

    /**
     * Get TimeLine According to TimeZone and DST Enabled.
     *
     * @author Pankaj Garg
     *
     * @param int    $_GMTTimeline
     * @param string $_timeZone
     *
     * @return int
     */
    public static function GetTimestampForZone($_GMTTimeline, $_timeZone)
    {
        $_DateTimeZone         = new DateTimeZone($_timeZone);
        $_transitionsContainer = $_DateTimeZone->getTransitions($_GMTTimeline, $_GMTTimeline);

        return ($_GMTTimeline + $_transitionsContainer[0]['offset']);
    }
}
