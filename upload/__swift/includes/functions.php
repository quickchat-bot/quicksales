<?php

require_once ('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_THIRDPARTYDIRECTORY . '/Mail/EmailAddressValidator.php');

use Base\Library\HTML\SWIFT_HTML;
use Base\Library\HTML\SWIFT_HTMLPurifier;

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
* Secure Complete Installation file path
*
* @author Mahesh Salaria
* @param string $_filePath File Path
* @return mixed $_filePath on Success, "false" otherwise
*/
function SecureFilePath($_filePath = '')
{
    if (empty($_filePath))
    {
        return false;
    }

    $_filePath = str_ireplace(SWIFT_BASEPATH, '.', $_filePath);

    return $_filePath;
}

/**
 * Return value for array based on map VariableArray($_array, '[test][test2]')
 *
 * @author Varun Shoor
 * @param array $arr
 * @param string $string
 * @return mixed
 */
function VariableArray($arr, $string)
{
    $arr_matches = array();

    preg_match_all('/\[([^\]]*)\]/', $string, $arr_matches, PREG_PATTERN_ORDER);

    $return = $arr;

    if (count($arr_matches) > 1) {
        /**
         * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-5111 Ticket subject containing symbols [], # aren't supported in KQL.
         */
        foreach ($arr_matches[1] as $dimension) {
            $dimension = str_replace(['{{', '}}'], ['[', ']'], $dimension);
            $return = $return[$dimension];
        }
    }

    return $return;
}

function kc_mime_content_type($filename)
{
    $mime_types = getMimeTypes();

    /*
     * BUG FIX - Varun Shoor
     *
     * SWIFT-1711 PHP error when trying to load attachments via QuickSupport Mobile API
     *
     * Comments: Fix provided by Drew
     */

    $ext = strtolower(substr(strrchr($filename, '.'), 1));

    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);

        return $mimetype;
    }

    return 'application/octet-stream';
}

/**
 * Throw a file not found error and end execution
 *
 * @author Varun Shoor
 * @param string $_errorMessage
 * @param bool $_sendHeader (OPTIONAL)
 * @return bool "true" on Success, "false" otherwise
 */
function FileNotFound($_errorMessage, $_sendHeader = true)
{
    $_SWIFT = SWIFT::GetInstance();

    if (SWIFT_INTERFACE == 'console') {
        echo $_errorMessage;
        exit;
    }

    if ($_sendHeader) {
        header('HTTP/1.0 404 Not Found');
        header('Status: 404 Not Found');
    }

    $_SWIFT->Template->Assign('_errorMessage', $_errorMessage);
    $_SWIFT->Template->Render('error_404', SWIFT_TemplateEngine::TYPE_FILE);

    exit;

    return true;
}

/**
 * Throw an exception and exit
 *
 * @author Mansi Wason
 * @param string $_errorMessage
 * @return bool "true" on Success, "false" otherwise
 */
function ExceptionHandle($_errorMessage)
{
    $_SWIFT = SWIFT::GetInstance();

    if (SWIFT_INTERFACE == 'console') {
        echo $_errorMessage;
        exit;
    }

    $_SWIFT->Template->Assign('_errorMessage', $_errorMessage);
    $_SWIFT->Template->Render('exception', SWIFT_TemplateEngine::TYPE_FILE);

    return true;
}

/**
 * Decode UTF8 String
 *
 * @author Varun Shoor
 * @param string $str UTF8 Escaped Data
 * @return string Return UTF8 Decoded Data
 */
function utf8_urldecode($str)
{
    $str = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($str));
    return html_entity_decode($str, null, 'UTF-8');
}

/**
 * Check to see if its a valid HEX color
 *
 * @author Varun Shoor
 * @param string $_colorValue The Color
 * @return bool "true" on Success, "false" otherwise
 */
function IsValidColor($_colorValue)
{
    if (substr($_colorValue, 0, 1) == '#' && strlen($_colorValue) <= 7) {
        return true;
    }

    return false;
}

/**
 * Mulit-byte Unserialize
 *
 * UTF-8 will screw up a serialized string
 *
 * @access private
 * @param string $string
 * @return array
 */
function mb_unserialize($string)
{
    $_unserializeResult = '';

    if (is_serialized($string)) {
        $_unserializeResult = unserialize($string);
        if ($_unserializeResult === false) {
            echo 'UNSERIALIZE FAILED: ' . htmlspecialchars($string);
        }
    }

    return $_unserializeResult;
}

/**
 * Check value to find if it was serialized.
 *
 * If $data is not an string, then returned value will always be false.
 * Serialized data is always a string.
 *
 * @since Wordpress 2.0.5
 *
 * @param string $data   Value to check to see if was serialized.
 *
 * @return bool False if not serialized and true if it was.
 */
function is_serialized($data)
{
    // if it isn't a string, it isn't serialized
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' == $data) {
        return true;
    }
    $length = strlen($data);
    if ($length < 4) {
        return false;
    }
    if (':' !== $data[1]) {
        return false;
    }
    $lastc = $data[$length - 1];
    if (';' !== $lastc && '}' !== $lastc) {
        return false;
    }
    $token = $data[0];
    switch ($token) {
        case 's' :
            if ('"' !== $data[$length - 2]) {
                return false;
            }
        case 'a' :
        case 'O' :
            return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
        case 'b' :
        case 'i' :
        case 'd' :
            return (bool) preg_match("/^{$token}:[0-9.E-]+;\$/", $data);
    }

    return false;
}

/**
 * Return number of symbols in a string
 *
 * @author Varun Shoor
 * @param string $_haystack
 * @return int The Symbol Count
 */
function GetSymbolCount($_haystack)
{
    return strlen(preg_replace("/[0-9A-Za-z]/", '', $_haystack));
}

/**
 * Return a sanitized note color
 *
 * @author Varun Shoor
 * @param mixed $_noteColor The Note Color
 * @return int "1" on Success, "0" otherwise
 */
function GetSanitizedNoteColor($_noteColor)
{
    $_noteColor = (int) ($_noteColor);
    if ($_noteColor > 5 || $_noteColor < 1) {
        $_noteColor = 1;
    }

    return $_noteColor;
}

/**
 * Converts BR to Newlines
 *
 * @author Varun Shoor
 * @param string $_contents The Contents to Process
 * @return string The Processed Contents
 */
function br2nl($_contents)
{
    return preg_replace('#<br\s*?/?>#i', "\n", $_contents);
}

/**
 * Retrieve timestamp from the appropriate date field value
 *
 * @author Varun Shoor
 * @param string $_fieldName The Field Name
 * @return mixed The UNIX Timestamp
 */
function GetDateFieldTimestamp($_fieldName)
{
    if (!isset($_POST[$_fieldName]) || trim($_POST[$_fieldName]) == '') {
        return 0;
    }

    $_calendarTimeStamp = GetCalendarDateline($_POST[$_fieldName]);
    if (!$_calendarTimeStamp) {
        return 0;
    }

    /* BUG FIX - Bishwanath Jha
     *
     * SWIFT-4189: Issue with Time Zone
     *
     * Comments: Using gmdate() in place of date() for retrieval as date is in GMT
     */

    // 12 Hour
    if (isset($_POST[$_fieldName]) && isset($_POST[$_fieldName . '_hour']) && isset($_POST[$_fieldName . '_minute']) && isset($_POST[$_fieldName . '_meridian'])) {
        $_finalHour = $_POST[$_fieldName . '_hour'];

        /*
         * BUG FIX - Mahesh Salaria
         *
         * SWIFT-1729: When entering in a billing time/date that has a time of 12 am or pm the saved date ends up being the following day.
         * SWIFT-1601: If we set the due time to 12:30PM using Release tab, help desk reset the due time to 12: 30AM for next day.
         *
         * Comments: Added check for hours in PM less than 12.
         */
        if ($_POST[$_fieldName . '_meridian'] == 'pm' && $_POST[$_fieldName . '_hour'] < 12) {
            $_finalHour = $_POST[$_fieldName . '_hour'] + 12;
        } else if ($_POST[$_fieldName . '_meridian'] == 'am' && $_POST[$_fieldName . '_hour'] == 12) {
            $_finalHour = 0;
        }

        return mktime($_finalHour, $_POST[$_fieldName . '_minute'], 0, gmdate('n', $_calendarTimeStamp), gmdate('j', $_calendarTimeStamp), gmdate('Y', $_calendarTimeStamp));

        // 24 Hour
    } else if (isset($_POST[$_fieldName]) && isset($_POST[$_fieldName . '_hour']) && isset($_POST[$_fieldName . '_minute'])) {
        return mktime($_POST[$_fieldName . '_hour'], $_POST[$_fieldName . '_minute'], 0, gmdate('n', $_calendarTimeStamp), gmdate('j', $_calendarTimeStamp), gmdate('Y', $_calendarTimeStamp));
    }

    return $_calendarTimeStamp;
}

/**
 * Strip Tags and restricted allowed attributes
 *
 * @author php.net
 * @param string $string The String to Parse
 * @param string $allowtags The Tags to Allow
 * @param mixed $allowattributes The attributes to allow
 * @return string The Processed String
 */
function strip_tags_attributes($string, $allowtags=NULL, $allowattributes=NULL)
{
    $string = strip_tags($string, $allowtags);
    if (!is_null($allowattributes)) {
        if (!is_array($allowattributes))
            $allowattributes = explode(",", $allowattributes);
        if (is_array($allowattributes))
            $allowattributes = implode(")(?<!", $allowattributes);
        if (strlen($allowattributes) > 0)
            $allowattributes = "(?<!" . $allowattributes . ")";
        $string = preg_replace_callback("/<[^>]*>/i", function ($matches) use ($allowattributes) {
            return preg_replace("/ [^ =]*" . $allowattributes . "=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);
        }, $string);
    }
    return $string;
}

/**
 * Strips the JS
 *
 * @author php.net
 * @param string $filter The String to Parse
 * @return string The Processed String
 * @throws SWIFT_Exception
 */
function strip_javascript($filter)
{
    $_SWIFT = SWIFT::GetInstance();
    $_SWIFT_HTMLPurifierObject = $_SWIFT->HTMLPurifier;
    if (!$_SWIFT->HTMLPurifier instanceof SWIFT_HTMLPurifier) {
        $_SWIFT_HTMLPurifierObject = new SWIFT_HTMLPurifier();
        $_SWIFT->SetClass('HTMLPurifier', $_SWIFT_HTMLPurifierObject);
    }
    $filter =  $_SWIFT_HTMLPurifierObject->Purify($filter);

    return $filter;
}

/**
 * Process the Calendar format date into UNIX Timestamp according to setting
 *
 * @author Varun Shoor
 * @author Utsav Handa
 *
 * @param string $_calendarDate The Calendar Date
 * @return mixed The Processed UNIX Timestamp
 */
function GetCalendarDateline($_calendarDate)
{
    if (empty($_calendarDate)) {
        return $_calendarDate;
    }

    $_SWIFT = SWIFT::GetInstance();

    if ($_SWIFT->Settings->Get('dt_caltype') == 'eu') {
        $_calendarDate = str_replace('/', '-', $_calendarDate);
    }

    /**
     * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
     *
     * SWIFT-4931 The Resolution Due time gets reset on Staff reply.
     */
    // Adding timezone for strtotime to ignore system timezone setting during conversion
    return strtotime($_calendarDate . ' GMT');
}

/**
 * Process the Display Icon
 *
 * @author Varun Shoor
 * @param string $_displayIcon The Display Icon
 * @return string The Processed Display Icon
 */
function ProcessDisplayIcon($_displayIcon)
{
    return str_replace('{$themepath}', SWIFT::Get('themepathimages'), $_displayIcon);
}

/**
 * Reverse of strchr, returns the data from start of haystack to needle, Does not include the needle
 *
 * @author Ryan M. Lederman
 * @param string $_hayStack The Hay Stack
 * @param string $_needle The Needle
 * @return mixed The Processed String
 */
function reversestrchr($_hayStack, $_needle)
{
    if (!$_position = mb_strpos(mb_strtoupper($_hayStack), mb_strtoupper($_needle))) {
        return false;
    } else {
        return mb_substr($_hayStack, 0, $_position);
    }
}

/**
 * Strips script tags from a string (and only script tags)
 *
 * @author Ryan M. Lederman
 * @param string $_htmlCode The HTML Code
 * @return string The Processed String
 */
function StripScriptTags($_htmlCode)
{
    // Strips "empty" script tags (e.g. <script type="text/javascript" src="foo"/>)
    $_htmlCode = preg_replace('@<script[^<]*?/>@si', '', $_htmlCode);

    // Strips "full" script tags (e.g. <script type="text/javascript">foo</script>)
    $_htmlCode = preg_replace('@<script(?:.*?)>.*?</script(?:[\s]*?)>@si', '', $_htmlCode);

    /*
     * BUG FIX - Varun Shoor
     *
     * SWIFT-2309 Extra code is showing if subject contains closing script tag.
     *
     */
    // Strips just closing tags
    $_htmlCode = preg_replace('@</script(?:[\s]*?)>@si', '', $_htmlCode);

    // remove javascript event handlers
    $_htmlCode = preg_replace('@\bon\w+\s*=\s*[^>&\s]+(?=.*(>|&))@si', '', $_htmlCode);

    return $_htmlCode;
}

/**
 * Retrieve the max upload size in bytes
 *
 * @author Varun Shoor
 * @return mixed Max Upload Size in Bytes
 */
function GetPHPMaxUploadSize()
{
    $_uploadSize = ini_get('upload_max_filesize');

    if (is_numeric($_uploadSize)) {
        return $_uploadSize;
    }

    $_uploadSizeType1 = strtoupper(substr($_uploadSize, -1));
    $_uploadSizeType2 = strtoupper(substr($_uploadSize, -2));
    $_uploadSizeChunk1 = (int) (substr($_uploadSize, 0, strlen($_uploadSize) - 1));
    $_uploadSizeChunk2 = (int) (substr($_uploadSize, 0, strlen($_uploadSize) - 2));
    if ($_uploadSizeType1 == 'G') {
        return $_uploadSizeChunk1 * 1024 * 1024 * 1024;
    } else if ($_uploadSizeType2 == 'GB') {
        return $_uploadSizeChunk2 * 1024 * 1024 * 1024;
    } else if ($_uploadSizeType1 == 'M') {
        return $_uploadSizeChunk1 * 1024 * 1024;
    } else if ($_uploadSizeType2 == 'MB') {
        return $_uploadSizeChunk2 * 1024 * 1024;
    } else if ($_uploadSizeType1 == 'K') {
        return $_uploadSizeChunk1 * 1024;
    } else if ($_uploadSizeType2 == 'KB') {
        return $_uploadSizeChunk2 * 1024;
    } else if ($_uploadSizeType1 == 'B') {
        return $_uploadSizeChunk1;
    }

    return false;
}

/**
 * Manipulate brightness of color. 0.5 brightens by 50%, -0.5 darkens by 50%
 *
 * @author http://lab.pxwebdesign.com.au/?p=14
 * @param string $hex The Hex Color
 * @param float $percent The Manipulation Percent
 * @return string The Hex Color
 */
function ColorBrightness($hex, $percent)
{
    // Work out if hash given
    $hash = '';
    if (stristr($hex, '#')) {
        $hex = str_replace('#', '', $hex);
        $hash = '#';
    }
    /// HEX TO RGB
    $rgb = array(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    //// CALCULATE
    for ($i = 0; $i < 3; $i++) {
        // See if brighter or darker
        if ($percent > 0) {
            // Lighter
            $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1 - $percent));
        } else {
            // Darker
            $positivePercent = $percent - ($percent * 2);
            $rgb[$i] = round($rgb[$i] * $positivePercent) + round(0 * (1 - $positivePercent));
        }
        // In case rounding up causes us to go to 256
        if ($rgb[$i] > 255) {
            $rgb[$i] = 255;
        }
    }
    //// RBG to Hex
    $hex = '';
    for ($i = 0; $i < 3; $i++) {
        // Convert the decimal digit to hex
        $hexDigit = dechex($rgb[$i]);
        // Add a leading zero if necessary
        if (strlen($hexDigit) == 1) {
            $hexDigit = "0" . $hexDigit;
        }
        // Append to the hex string
        $hex .= $hexDigit;
    }
    return $hash . $hex;
}

/**
 * Highlights a given code with line numbers
 *
 * @author Varun Shoor
 * @param string $_rawCode The Raw Code
 * @return string The Processed Code HTML
 */
function HighlightCode($_rawCode)
{
    $_rawCode = trim(preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_rawCode));

    $_htmlData = '<ol class="highlight_source">';
//    $_highlightedCode = highlight_string($_rawCode, true);
//    $_highlightedCode = preg_replace("#(\r\n|\r|\n)#s", SWIFT_CRLF, $_highlightedCode);
    $_highlightedCode = $_rawCode;

    $_highlightedCodeContainer = explode(SWIFT_CRLF, $_highlightedCode);
    foreach ($_highlightedCodeContainer as $_key => $_val) {
        $_htmlData .= '<li>' . htmlspecialchars($_val) . '</li>';
    }

    $_htmlData .= '</ol>' . SWIFT_CRLF;

    return $_htmlData;
}

/**
 * Generate a unique mask for tickets, chats, knowledgebase
 *
 * @author   Varun Shoor
 * @author   Utsav Handa
 *
 * @param bool $_checkStorage  (OPTIONAL)
 *
 * @return string The Unique mask
 */
function GenerateUniqueMask($_checkStorage = true)
{
    $_SWIFT = SWIFT::GetInstance();

    // In version 2, random 3-letters would occasionally create offensive words (DIK, FUK, etc); stopwords prevents
    // mersenne twister is auto-seeded as of php 4.2.0; codebase requires 5.1.2
    do {
        do {
            $_prefix = strtoupper(chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)));
        } while (in_array($_prefix, SWIFT::Get('stopwords')));

        $_uniqueMaskID = $_prefix . '-' . mt_rand(100, 999) . '-' . mt_rand(10000, 99999);

        $_maskCheck = $_checkStorage ? $_SWIFT->Database->QueryFetch("SELECT ticketmaskid FROM " . TABLE_PREFIX . "tickets WHERE ticketmaskid = '" . $_SWIFT->Database->Escape($_uniqueMaskID) . "'") : array();

    } while (!empty($_maskCheck["ticketmaskid"]));

    return $_uniqueMaskID;
}

/**
 * Autoamtically convert URLs to links..
 * @author Varun Shoor
 * @param string $_text The Text to Process
 * @param bool $_customLinkClass (OPTIONAL) The Custom Link Class
 * @return string The Processed String
 */
function AutoLink($_text, $_customLinkClass = false)
{
    $_customClassHTML = '';
    if ($_customLinkClass) {
        $_customClassHTML = ' class="' . $_customLinkClass . '"';
    }

    // pad it with a space so we can match things at the start of the 1st line.
    $_returnResult = ' ' . $_text;

    // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
    // xxxx can only be alpha characters.
    // yyyy is anything up to the first space, newline, comma, double quote or <
    $_returnResult = preg_replace("#([\t\r\n ])([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="\2://\3" target="_blank"' . $_customClassHTML . '>\2://\3</a>', $_returnResult);

    // matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
    // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
    // zzzz is optional.. will contain everything up to the first space, newline,
    // comma, double quote or <.
    $_returnResult = preg_replace("#([\t\r\n ])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="http://\2.\3" target="_blank"' . $_customClassHTML . '>\2.\3</a>', $_returnResult);

    // matches an email@domain type address at the start of a line, or after a space.
    // Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
    $_returnResult = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\"" . $_customClassHTML . ">\\2@\\3</a>", $_returnResult);

    // Remove our padding..
    $_returnResult = substr($_returnResult, 1);

    return $_returnResult;
}

/**
 *
 *  Creates an RFC 4122 compliant v4 UUID as defined in sec 4.1.2, returned as a string
 *
 *  This function generates an RFC compliant UUID under the UUID version 4 standard ruleset defined in section 4.1.2
 *  of RFC 4122.  This function was released to the public on May 9 2006 by David Holmes of CFD software, as found
 *  in the PHP manual notes to uniqid.  (Added $nodash)
 *
 *  @author    David Holmes
 *  @since     0.4
 *  @version   1
 *  @access    public
 *
 *  @param     boolean     $nodash      whether or not to include hyphens in the result value
 *
 *  @return    string
 *
 *  @todo      pull into a more appropriate location (after finding it)
 *
 */
function GenerateUUID($nodash = false)
{
    $formatString = $nodash ? '%04x%04x%04x%03x4%04x%04x%04x%04x' : '%04x%04x-%04x-%03x4-%04x-%04x%04x%04x';

    return sprintf($formatString, mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
            mt_rand(0, 65535), // 16 bits for "time_mid"
            mt_rand(0, 4095), // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
            // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
            // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
            // 8 bits for "clk_seq_low"
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
    );
}

/**
 * Formats a data size
 * Expects bytes, returns bytes/KiB/MiB/GiB
 * @author Ryan M. Lederman
 * @param int $_size The Size to Format
 * @param bool $_displayBytes Whether to Display Result as Bytes
 * @param int $_decimal The Decimal Pointer Location
 * @return mixed The formatted size
 */
function FormattedSize($_size, $_displayBytes = false, $_decimal = 2)
{
    $_SWIFT = SWIFT::GetInstance();

    // Assumes input is bytes!
    if ($_size < 1024 && $_displayBytes == true) {
        // Bytes
        return number_format($_size, $_decimal) . " " . $_SWIFT->Language->Get('bytes');
    } else {
        if ($_size < 1024 && $_displayBytes == false) {
            // KiB
            return number_format($_size / 1024, $_decimal) . " " . $_SWIFT->Language->Get('kb');
        } else if ($_size >= 1024 && $_size < 1048576) {
            // KiB
            return number_format($_size / 1024, $_decimal) . " " . $_SWIFT->Language->Get('kb');
        } else if ($_size >= 1048576 && $_size < 1073741824) {
            // MiB
            return number_format($_size / 1048576, $_decimal) . " " . $_SWIFT->Language->Get('mb');
        } else if ($_size >= 1073741824) {
            // GiB
            return number_format($_size / 1073741824, $_decimal) . " " . $_SWIFT->Language->Get('gb');
        }
    }

    return false;
}

/**
 * Decodes a string
 *
 * @author Varun Shoor
 * @param string $_incomingData The Incoming Data
 * @return string The Processed Data
 */
function DecodeUTF8($_incomingData)
{
    $_SWIFT = SWIFT::GetInstance();

    $_convertedData = '';

    // We need to make the conversion to the native code page here
    $_defaultCodePage = strtoupper($_SWIFT->Language->Get('charset'));

    if ($_defaultCodePage != 'UTF-8') {
        // Incoming data is UTF-8, so convert to the codepage of the help desk.
        if (extension_loaded('mbstring') && function_exists('mb_convert_encoding')) {
            $_convertedData = mb_convert_encoding($_incomingData, $_defaultCodePage, 'UTF-8');
        } else {
            // mbstring isn't loaded or is the wrong version.  We could attempt utf8_decode...
            if ($_defaultCodePage == 'ISO-8859-1' && function_exists('utf8_decode')) {
                $_convertedData = utf8_decode($_incomingData);
            }
        }
    } else {
        return $_incomingData;
    }

    return $_convertedData;
}

/**
 * Sends a No Cache Header
 *
 * @author Varun Shoor
 * @return bool "true" on Success, "false" otherwise
 */
function HeaderNoCache()
{
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    return true;
}

/**
 * Sends a Cache Header, Caches for next 1 hour
 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html
 *
 * @author Varun Shoor
 * @return bool "true" on Success, "false" otherwise
 */
function HeaderCache()
{
    @header("Expires: " . gmdate("D, d M Y H:i:s", time() + (3600 * 24)) . " GMT");
    @header("Cache-Control: max-age=3600, must-revalidate");  // must-revalidate is required because we want browser to *strictly* follow our rules
}

/**
 * Adds a trailing slash to the string if none exists
 *
 * @author Varun Shoor
 * @param string $_string The String to Process
 * @return string The Processed String
 */
function AddTrailingSlash($_string)
{
    if (empty($_string)) {
        return '';
    } else if (substr($_string, -1) != '/') {
        $_string .= '/';
    }

    return $_string;
}

/**
 * Removes a trailing slash to the string if none exists
 *
 * @author Varun Shoor
 * @param string $_string The String to Process
 * @return string The Processed String
 */
function RemoveTrailingSlash($_string)
{
    if (empty($_string)) {
        return '';
    } else if (substr($_string, -1) == '/') {
        $_string = substr($_string, 0, strlen($_string) - 1);
    }

    return $_string;
}

/**
 * Shortcut to if
 *
 * @author Varun Shoor
 * @param string|bool $_expression The Expression to Parse
 * @param mixed $_returnOnTrue The Value to Return if Expression is True
 * @param mixed $_returnOnFalse The Value to Return if Expression is False
 * @return mixed $_returnOnTrue if _expression is true or returns _returnOnFalse
 */
function IIF($_expression, $_returnOnTrue = '', $_returnOnFalse = '')
{
    return ($_expression ? $_returnOnTrue : $_returnOnFalse);
}

/**
 * Build the Array into a IN() processable value
 *
 * @author Varun Shoor
 * @param array $_dataContainer The Data Container
 * @return string The Processed Value
 */
function BuildIN($_dataContainer, $_toInteger = false)
{
    $_inText = '';

    if (!_is_array($_dataContainer)) {
        return "'0'";
    }

    foreach ($_dataContainer as $_key => $_val) {
        if ($_toInteger) {
            $_inText .= addslashes($_val) . ",";
        } else {
            $_inText .= "'" . addslashes($_val) . "',";
        }
    }

    if (!empty($_inText)) {
        return substr($_inText, 0, -1);
    } else {
        return "'0'";
    }
}

/**
 * Build a Unique Hash
 *
 * @author Varun Shoor
 * @return string The Hash
 */
function BuildHash()
{
    return BuildHashBlock() . BuildHashBlock() . BuildHashBlock() . BuildHashBlock();
}

/**
 * Build a Unique Hash Block
 *
 * @author John Haugeland
 * @return string The Hash Block
 */
function BuildHashBlock()
{
    $Ch1to3 = mt_rand(0, 36 * 36 * 36) - 1;        // largest alphanum power that'll fit in the minimum guaranteed 16-bit range for mt_randmax()
    $Ch4to5 = mt_rand(0, 36 * 36) - 1;
    $Ch6to8 = hexdec(substr(uniqid(), -6)) % (36 * 36 * 36);  // only want the bottom two characters of entropy, but clip a large range to keep from much influencing probability

    return str_pad(base_convert($Ch1to3, 10, 36), 3, '0', STR_PAD_LEFT) . str_pad(base_convert($Ch4to5, 10, 36), 2, '0', STR_PAD_LEFT) . str_pad(base_convert($Ch6to8, 10, 36), 3, '0', STR_PAD_LEFT);
}

/**
 * Builds a random number after seeding the random number generator
 *
 * @author Varun Shoor
 * @return int The Unique Random Number
 */
function BuildRandom($_min, $_max)
{
    list($_usec, $_sec) = explode(' ', microtime());

    // Seed
    mt_srand((float) $_sec + ((float) $_usec * 100000));

    return mt_rand($_min, $_max);
}

/**
 * Converts a RGB Color to Hex
 *
 * @author Varun Shoor
 * @param mixed $_red The Red Color Value (0-255)
 * @param mixed $_green The Green Color Value (0-255)
 * @param mixed $_blue The Blue Color Value (0-255)
 * @return string The Hexadecimal Value
 */
function RGBToHex($_red, $_green, $_blue)
{
    $_red = (int) ($_red);
    $_green = (int) ($_green);
    $_blue = (int) ($_blue);

    return sprintf("%02X%02X%02X", $_red, $_green, $_blue);
}

/**
 * Returns "" if specified string is empty
 *
 * @author Varun Shoor
 * @param string $_string The String to Check
 * @return string $_string on Success, '' otherwise
 */
function ReturnNone($_string)
{
    if (trim($_string) == '' || empty($_string)) {
        return '';
    } else {
        return $_string;
    }
}

/**
 * Function converts a hex color to RGB one
 *
 * @author Varun Shoor
 * @param string $_hexCode The Hexadecimal Color Code (Can Contain # prefix)
 * @return mixed array(red, green, blue) on Success, 'false' otherwise
 */
function HexToRGB($_hexCode)
{
    if (trim($_hexCode) == "") {
        return array('red' => '0', 'green' => '0', 'blue' => '0');
    }

    $_hexCode = str_replace('#', '', $_hexCode);

    $_red = hexdec(substr($_hexCode, 0, 2));
    $_green = hexdec(substr($_hexCode, 2, 2));
    $_blue = hexdec(substr($_hexCode, 4, 2));

    $_returnContainer = array();
    $_returnContainer['red'] = $_red;
    $_returnContainer['green'] = $_green;
    $_returnContainer['blue'] = $_blue;

    return $_returnContainer;
}

/**
 * Cleans up variable, removes slashes etc (Everything except for alphabets, numbers and _ - :)
 *
 * @author Varun Shoor
 * @param string $_string The String to Process
 * @param bool $_allowPeriod Whether to allow period
 * @return string The Processed String
 */
function CleanDomainName($_string, $_allowPeriod = true)
{
    if ($_allowPeriod == false) {
        return trim(preg_replace("/[^a-zA-Z0-9\-]/", "", $_string));
    }

    return trim(preg_replace("/[^a-zA-Z0-9\-.]/", "", $_string));
}

/**
 * Cleans up variable, removes slashes etc (Everything except for alphabets, numbers and _ - :)
 *
 * @author Varun Shoor
 * @param string $_string The String to Process
 * @return string The Processed String
 */
function Clean($_string, $_allowSpace = false)
{
    if ($_allowSpace) {
        return trim(preg_replace("/[^a-zA-Z0-9\-\_:,\s]/", "", $_string));
    }

    return trim(preg_replace("/[^a-zA-Z0-9\-\_:,]/", "", $_string));
}

/**
 * Removes Quotes from Quoted String
 *
 * @author Andriy Lesyuk
 * @param string $_string The String to Process
 * @return string The Processed String
 */
function CleanQuotes($_string)
{
    if (preg_match('/^(["\'])(.*)\1$/', $_string, $_matches)) {
        return $_matches[2];
    }

    return $_string;
}

/**
 * Cleans up variable, removes slashes etc (Everything except for alphabets, numbers and _ - :)
 *
 * @author Mansi Wason
 * @param string $_string The String to Process
 * @return string The Processed String
 */
function CleanURL($_string)
{
    $StringConverter = new SWIFT_StringConverter();

    return empty($_string)
	    ? ''
	    : mb_strtolower(trim(preg_replace("/[^a-zA-Z0-9\-\_\s]/", "", $StringConverter->ConvertAccented($_string))));
}

/**
 * Cleans up variable, removes everything except numbers
 *
 * @author Varun Shoor
 * @param string $_string The String to Process
 * @return string The Processed String
 */
function CleanInt($_string)
{
    return trim(preg_replace("/[^0-9]/", "", $_string));
}

/**
 * Cleans up tag variable, removes slashes etc (Everything except for alphabets, numbers and _ - + :)
 *
 * @author Varun Shoor
 * @param string $_string The String to Process
 * @return string The Processed String
 */
function CleanTag($_string, $_extraChars = '')
{
    /**
     * BUG FIX - Ravi Sharma
     *
     * SWIFT-1930 Email field strips off apostrophe (') from email address when a user registers from Client Support Center
     *
     * Comments: None
     */
    /**
     * BUG FIX - Ankit Saini
     *
     * SWIFT-5199 superscript characters are stripped from tags
     *
     * Comments: None
     */
    return trim(preg_replace("/[^\p{L}A-Z0-9\+\-\_:\.@" . $_extraChars . " ]/u", "", $_string));
}

/**
 * Cleans up email (Everything except for alphabets, numbers and ' _ - + :)
 *
 * @author Ravi Sharma
 * @param string $_string The String to Process
 * @return string The Processed String
 */
function CleanEmail($_string)
{
    /**
     * BUG FIX - Ravi Sharma <ravi.shamra@opencart.com.vn>
     *
     * SWIFT-832 Tags do not accept accented characters.
     *
     * Comments:\p{L} replace all non-alpha characters with UTF8 support.
     */
    return trim(preg_replace("/[^\p{L}A-Z0-9\+\-\_:\.@!#$%&'*\=?^_`{|}~]/u", "", $_string));
}

/**
 * Check to see if the email address is valid
 *
 * @author Varun Shoor
 * @param string $_emailAddress The Email Address
 * @return bool "true" on Success, "false" otherwise
 */
function IsEmailValid($_emailAddress)
{
    if (!is_string($_emailAddress)) {
        return false;
    }

    // Emails shouldn't contain spaces
    if (preg_match('/ /', $_emailAddress)) {
        return false;
    }

    /**
     * BUG FIX: IW-151 HostedTrial Setup Failure: Email contains + sign
     * SWIFT-3375: HostedTrial Setup Failure: Email contains + sign
     * Comments: Php mail validator is not RFC2822 compliant, see https://bugs.php.net/bug.php?id=43402
     */

    return EmailAddressValidator::is_email($_emailAddress);
}


/**
 * Extended Array Check (Combine Count() with is_array())
 *
 * @author Varun Shoor
 * @param mixed $_containerArray The Container Array to Check On
 * @return bool "true" on Success, "false" otherwise
 */
function _is_array($_containerArray)
{
    if (!is_array($_containerArray) || !count($_containerArray)) {
        return false;
    } else {
        return true;
    }

    return false;
}

/**
 * Retrieve the Processed Micro Time
 *
 * @author Varun Shoor
 * @return float The Micro Time
 */
function GetMicroTime()
{
    list($_usec, $_sec) = explode(" ", microtime());

    return ((float) $_usec + (float) $_sec);
}

/**
 * Strips the Trailing Slash (www.domain.com/ to www.domain.com)
 *
 * @author Varun Shoor
 * @param string $_URL The URL to Strip
 * @return string The Processed URL
 */
function StripTrailingSlash($_URL)
{
    if (substr($_URL, -1, 1) == "/") {
        return substr($_URL, 0, strlen($_URL) - 1);
    }

    return $_URL;
}

/**
 * Strip X number of chars and add ... at end
 *
 * @author Varun Shoor
 * @param string $_string The String to Process
 * @param int $_length The Length for the cut off
 * @return string The Processed String
 */
function StripName($_string, $_length)
{
    $_SWIFT = SWIFT::GetInstance();

    /**
     * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
     *
     * SWIFT-3498 "Setting the value '0' for 'Article Preview Character Limit' under Admin CP > Knowledgebase > Settings, displays three eclipses under Article preview".
     */
    if ($_length <= 0) {
        $_string = '';
    } else {
        $enc = $_SWIFT->Language->Get('charset');
        if (empty($enc)) {
            $enc = 'UTF-8';
        }
        if (mb_strlen($_string, $enc) > $_length) {
            $_string = mb_substr($_string, 0, $_length, $enc) . '...';
        }
    }

    return $_string;
}

/**
 * Check an IP against the network list
 * Possible Range Styles:
 * 202.1.192.0-202.1.192.255: a range of IPs
 * 200.36.161.0/24: a range of IP by using net masking
 * 200.36.161/24: a shorten syntax similar to the above.
 *
 * @author Varun Shoor
 * @param string $_network The Network Range
 * @param string $_ipAddress The IP Address
 * @return bool "true" on Success, "false" otherwise
 */
function NetMatch($_network, $_ipAddress)
{
    $_network = trim($_network);
    $_ipAddress = trim($_ipAddress);

    // Is it an IPv6 address? If yes, we look for an exact match
    if (strstr($_ipAddress, '::') || strstr($_network, '::')) {
        // Remove mask information from IPv6 addresses and match
        return (preg_replace('/\/\d+$/', '', $_network) == preg_replace('/\/\d+$/', '', $_ipAddress));
    }

    $_hasRange = strpos($_network, '-');
    if ($_hasRange === false) {
        if (!strpos($_network, '/')) {
            $_network .= '/30';
        }

        $_ipNetworkContainer = explode('/', $_network);

        if (!preg_match('@\d*\.\d*\.\d*\.\d*@', $_ipNetworkContainer[0], $_matches)) {
            $_ipNetworkContainer[0] .= '.0'; // Alternate form 194.1.4/24
        }

        $_ipNetworkLong = ip2long($_ipNetworkContainer[0]);
        $_ipNetworkLongSec = ip2long($_ipNetworkContainer[1]);

        $_ipNetworkMask = long2ip($_ipNetworkLongSec) == $_ipNetworkContainer[1] ? $_ipNetworkLongSec : (0xffffffff << (32 - (int) $_ipNetworkContainer[1]));
        $_ipAddressLong = ip2long($_ipAddress);

        return ($_ipAddressLong & $_ipNetworkMask) == ($_ipNetworkLong & $_ipNetworkMask);
    } else {
        $_fromIPLong = ip2long(trim(substr($_network, 0, $_hasRange)));
        $_toIPLong = ip2long(trim(substr($_network, $_hasRange + 1)));

        $_ipAddressLong = ip2long($_ipAddress);

        return ($_ipAddressLong >= $_fromIPLong and $_ipAddressLong <= $_toIPLong);
    }

    return false;
}

/**
 * Builds a SQL Search Statement
 *
 * @author Varun Shoor
 * @param string $_fieldName The Field Name
 * @param string $_searchQuery The Search Query
 * @param bool $_noParity Whether there should be parity in data
 * @param bool $_useOR (OPTIONAL) Whether to use OR
 * @return mixed "true" / "string" on Success, "false" otherwise
 * @throws SWIFT_Exception If the Class is not Loaded
 */
function BuildSQLSearch($_fieldName, $_searchQuery, $_noParity = false, $_useOR = true)
{
    $_SWIFT = SWIFT::GetInstance();

    // Sanitize the text
    if ($_noParity) {
        $_stopData = array("#\s+#s", "#(\r\n|\r|\n)#s");
        $_replaceWithData = array(" ", " ");
    } else {
        $_stopData = array("#\s+#s", "#(\r\n|\r|\n)#s", "@[~`#$^&*()=+[\]{}|<>,;]@", "#[\\\\]#"); // 4 backslashes because PHP removes 2
        $_replaceWithData = array(" ", " ", "", "\\\\\\\\"); // For "LIKE" queries, backslashes must be double-escaped.
    }

    $_query = preg_replace($_stopData, $_replaceWithData, $_searchQuery);

    $_matches = $_strictMatches = array();
    if (preg_match_all('/["|\'](.*)["|\']/iU', $_query, $_matches)) {
        foreach ($_matches[1] as $_strictMatch) {
            if (trim($_strictMatch) == '') {
                continue;
            }

            $_strictMatches[] = $_strictMatch;
        }

        $_query = preg_replace('/["|\'](.*)["|\']/iU', '', $_query);
    }

    // Split the query into words using spaces
    $_wordsContainer = explode(" ", $_query);
    if (!count($_wordsContainer)) {
        $_wordsContainer = array($_searchQuery);
    }

    $_sqlContainer = array();
    foreach ($_wordsContainer as $_key => $_val) {
        if (trim($_val) == '') {
            continue;
        }

        $_prefix = substr($_val, 0, 1);
        $_suffixData = substr($_val, 1);
        if ($_prefix == '-') {
            $_sqlContainer[] = $_SWIFT->Database->Escape($_fieldName) . " NOT LIKE '%" . $_SWIFT->Database->Escape($_suffixData) . "%'";
        } else if ($_prefix == '+') {
            $_sqlContainer[] = $_SWIFT->Database->Escape($_fieldName) . " LIKE '%" . $_SWIFT->Database->Escape($_suffixData) . "%'";
        } else {
            $_sqlContainer[] = $_SWIFT->Database->Escape($_fieldName) . " LIKE '%" . $_SWIFT->Database->Escape($_val) . "%'";
        }
    }

    foreach ($_strictMatches as $_strictMatch) {
        $_sqlContainer[] = $_SWIFT->Database->Escape($_fieldName) . " LIKE '%" . $_SWIFT->Database->Escape($_strictMatch) . "%'";
    }

    if (!count($_sqlContainer)) {
        return false;
    }

    if ($_useOR) {
        return implode(' OR ', $_sqlContainer);
    }

    return implode(' AND ', $_sqlContainer);
}

/**
 * @author Mansi Wason <mansi.wason@opencart.com.vn>
 * @return string
 */
function GenerateID()
{
    return Random(mt_rand(10, 30)) . RandomID() . Random(mt_rand(10, 30));
}

/**
 * Generate a more truly "random" alpha-numeric string.
 *
 * @author Mansi Wason <mansi.wason@opencart.com.vn>
 * @param  int $_length
 * @return string
 * @throws Exception
 */
function Random($_length = 16)
{
    if (function_exists('openssl_random_pseudo_bytes')) {
        $_bytes = openssl_random_pseudo_bytes($_length * 2);

        if ($_bytes === false) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return substr(str_replace(['/', '+', '='], '', base64_encode($_bytes)), 0, $_length);
    }

    return QuickRandom($_length);
}
/**
 * Generate a "random" alpha-numeric string.
 *
 * Should not be considered sufficient for cryptography, etc.
 *
 * @author Mansi Wason <mansi.wason@opencart.com.vn>
 * @param  int $_length
 * @return string
 */
function QuickRandom($_length = 16)
{
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    return substr(str_shuffle(str_repeat($pool, 5)), 0, $_length);
}

/**
 * @author Mansi Wason <mansi.wason@opencart.com.vn>
 * @return string
 */
function RandomID()
{
    return sha1(uniqid(true) . Random(25) . microtime(true));
}

/**
 * Check the lock file
 *
 * @author Parminder Singh
 * @param string $_lockFile Lock file
 * @param int $_lockFileExpiry Lock file expiry time
 * @param bool $_deleteLockFile (OPTIONAL) Whether to delete Lock File
 * @return bool "true" on Success, "false" otherwise
 */
function CheckOrCreateLockFile($_lockFile, $_lockFileExpiry, $_deleteLockFile = false)
{

    $_lockFilePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_LOG_DIRECTORY . '/' . $_lockFile;

    // This is an argument provided to delete the lock file before processing or Check to see if the lock file is expired.
    if ($_deleteLockFile || HasLockFileExpired($_lockFile, $_lockFileExpiry)) {
        if (!ClearLockFile($_lockFile)) {
            return false;
        }
    }

    // If the lock file exists, some other instance of cron is still running, so we just bail.
    if (file_exists($_lockFilePath)) {
        return false;
    }

    // Open up the lock file and write the current time in it
    $_filePointer = fopen($_lockFilePath, "w+");
    if ($_filePointer) {
        fwrite($_filePointer, DATENOW);
        fclose($_filePointer);
    }

    return true;
}

/**
 * Clear lock file
 *
 * @param string $_lockFile Lock file
 * @author Parminder Singh
 * @return bool "true" on Success, "false" otherwise
 */
function ClearLockFile($_lockFile)
{
    $_lockFilePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_LOG_DIRECTORY . '/' . $_lockFile;

    if (file_exists($_lockFilePath) && !unlink($_lockFilePath)) {
        return false;
    }

    return true;
}

/**
 * Checks if the lock file has expired
 *
 * @param string $_lockFile Lock file
 * @param int $_lockFileExpiry Lock file expiry time
 * @author Parminder Singh
 * @return bool "true" on Success, "false" otherwise
 */
function HasLockFileExpired($_lockFile, $_lockFileExpiry)
{
    $_returnStatus = false;
    $_lockFilePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_LOG_DIRECTORY . '/' . $_lockFile;

    if (file_exists($_lockFilePath)) {
        $_filePointer = fopen($_lockFilePath, "r");

        if ($_filePointer !== false) {
            clearstatcache();
            $_fileBytes = filesize($_lockFilePath);
            $_fileData = fread($_filePointer, $_fileBytes);

            if ($_fileData !== false) {
                $_timeElapsed = DATENOW - (int) ($_fileData);

                if ($_timeElapsed >= $_lockFileExpiry) {
                    $_returnStatus = true;
                }
            }

            fclose($_filePointer);
        }
    }

    return $_returnStatus;
}


/**
 * Calculates the Business date from the time-period
 *
 * @param string $_days Days to process for calculation
 * @param int   $_dateTime Datetime formatted data
 *
 * @original Internet
 * @author Utsav Handa
 *
 * @return int "datetime" on success
 */
function calculateBusinessDay($_days, $_dateTime=null) {

    // Prepare the calculation information
    $_dateTime = (is_null($_dateTime)) ? time() : $_dateTime;
    $_day = 0;
    $_direction = $_days == 0 ? 0 : (int) ($_days/abs($_days));
    $_day_value = (60 * 60 * 24);

    while($_day !== $_days) {
        $_dateTime += $_direction * $_day_value;

        $_day_w = date("w", $_dateTime);
        if ($_day_w > 0 && $_day_w < 6) {
            $_day += $_direction * 1;
        }
    }

    return $_dateTime;
}

/**
 * Check if an IP belongs to QuickSupport servers
 *
 * @author Ravinder Singh
 * @param string $_ip
 * @param bool $_useCache
 * @return bool "true" on Success, "false" otherwise
 */
function IsQuickSupportIP($_ip, $_useCache = true)
{
    $_ipCache = array();
    $_cacheFile = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/ip.cache';
    if (file_exists($_cacheFile))
    {
        $_ipCache = json_decode(file_get_contents($_cacheFile), true);
    }

    if (!is_array($_ipCache))
    {
        $_ipCache = array();
    }

    if ($_useCache && isset($_ipCache[$_ip]))
    {
        return $_ipCache[$_ip];
    }

    $_validationURL = 'https://my.opencart.com.vn/Backend/Verify/IsQuickSupportIP';

    $_curlHandle = curl_init();
    curl_setopt($_curlHandle, CURLOPT_URL, $_validationURL);
    curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($_curlHandle, CURLOPT_TIMEOUT, 100);
    curl_setopt($_curlHandle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($_curlHandle, CURLOPT_POST, true);
    curl_setopt($_curlHandle, CURLOPT_POSTFIELDS, 'ip=' . urlencode($_ip));
    $_response = curl_exec($_curlHandle);

    $_returnResult = false;

    if (trim($_response) == '1')
    {
        $_returnResult = true;
    }

    $_ipCache[$_ip] = $_returnResult;

    // Update the cache?
    if ($_useCache)
    {
        file_put_contents($_cacheFile, json_encode($_ipCache));
    }

    return $_returnResult;
}

/**
 * Retrieves Client's IP from X_FORWARDED_FOR content
 *
 * @author Utsav Handa
 *
 * @param string $forwardedForAddresses
 *
 * @return string
 */
function GetClientIPFromXForwardedFor($forwardedForAddresses)
{
    // Some proxies typically list the whole chain of IP addresses through which the client has reached us.
    // e.g. client_ip, proxy_ip1, proxy_ip2, etc.
    $ipAddressList = explode(',', $forwardedForAddresses);

    return trim(array_shift($ipAddressList));
}

/**
 * Return the array with the invisible separation.
 *
 * @source : http://en.wikipedia.org/wiki/Zero-width_space
 *
 * @author Mansi Wason
 *
 * @param string $str
 *
 * @return string
 */
function wordwrapWithZeroWidthSpace($str)
{
    /* Bug Fix : Saloni Dhall
     * SWIFT-3842 : Knowledgebase Category title rendering issue with special characters.
     */
    return wordwrap($str, $width = 75, $break = "&#8203;", $cut = true);
}

/**
 * Sort the array as per the dateline column
 *
 * @param array      $_staffContainer The Staff Container Array
 * @param string     $_col            The Dateline
 * @param int|string $_dir            Sort in ascending order
 */
function array_sort_by_column(&$_staffContainer, $_col, $_dir = SORT_ASC) {
    $sort_col = array();

    foreach ($_staffContainer as $key=> $row) {
        $sort_col[$key] = $row[$_col];
    }

    array_multisort($sort_col, $_dir, $_staffContainer);
}

/**
 * Return the number of days in a month for a given year and calendar
 * Source : http://php.net/manual/en/function.cal-days-in-month.php
 *
 * @author Madhur Tandon
 *
 * @return integer timestamp
 */
if (!defined('CAL_GREGORIAN')) {
    define('CAL_GREGORIAN', 1);
}

if (!function_exists('cal_days_in_month')) {
    function cal_days_in_month($_calendar, $_month, $_year)
    {
        unset($_calendar);

        return date('t', mktime(0, 0, 0, $_month, 1, $_year));
    }
}

/**
 * Replace last occurrence of a string
 *
 * Ravi Sharma <ravi.sharma@opencart.com.vn>
 *
 * @param String $search
 * @param String $replace
 * @param String $str
 *
 * @return String $str
 */
function str_replace_last($search, $replace, $str)
{
    if (($pos = strrpos($str, $search)) !== false) {
        $search_length = strlen($search);
        $str           = substr_replace($str, $replace, $pos, $search_length);
    }

    return $str;
}

/**
 * Return the bytes
 *
 * Ravi Sharma <ravi.sharma@opencart.com.vn>
 *
 * @param string $val
 *
 * @return int $val
 */
function return_bytes($val)
{
    // Return 0 if $val is empty
    if (empty($val)) {
        return 0;
    }

    $last = strtolower($val[strlen(trim($val)) - 1]);

    $val = (int) $val;

    switch ($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

/**
 * Get extension from content type
 *
 * @author Verem Dugeri <verem.dugeri@crossover.com>
 *
 * @param string $_contentType
 *
 * @return string fileExtension
 */
function GetExtensionFromContentType($_contentType) {
    if (strtolower($_contentType) == "image/jpg") {
        $_contentType = 'image/jpeg';
    }
    return array_search($_contentType, getMimeTypes(), true);
}

/**
 * @return array
 */
function getMimeTypes(): array
{
    $_mimetypes = [
        'txt'  => 'text/plain',
        'html' => 'text/html',
        'htm'  => 'text/html',
        'php'  => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'swf'  => 'application/x-shockwave-flash',
        'flv'  => 'video/x-flv',
        // images
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpe'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif'  => 'image/tiff',
        'svg'  => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip'  => 'application/zip',
        'rar'  => 'application/x-rar-compressed',
        'exe'  => 'application/x-msdownload',
        'msi'  => 'application/x-msdownload',
        'cab'  => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3'  => 'audio/mpeg',
        'qt'   => 'video/quicktime',
        'mov'  => 'video/quicktime',
        // adobe
        'pdf'  => 'application/pdf',
        'psd'  => 'image/vnd.adobe.photoshop',
        'ai'   => 'application/postscript',
        'eps'  => 'application/postscript',
        'ps'   => 'application/postscript',
        // ms office
        'doc'  => 'application/msword',
        'rtf'  => 'application/rtf',
        'xls'  => 'application/vnd.ms-excel',
        'ppt'  => 'application/vnd.ms-powerpoint',
        // open office
        'odt'  => 'application/vnd.oasis.opendocument.text',
        'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
    ];

    return $_mimetypes;
}

/**
 * Strips Tags Leaving Safe HTML Tags.
 *
 * @author Arotimi Busayo
 *
 * @param string $content
 * @param string $extraTags
 * @return string
 */
function StripTags($content, $extraTags = '') {
    $content = preg_replace('/(<br\s*\/>)/', '<br>', $content);
    $_allowableTags = '<html><body><b><br><em><hr><i><li><ol><p><span><table><tr><td><ul><strong><small>' . $extraTags;
    return strip_tags($content, $_allowableTags);
}

function removeTags($html, $stripAllHTMLTags = 0, $_canReturnEmpty = true)
{
    $html = preg_replace('/(<br\s*\/>)/', '<br>', $html);
    $allowedTags = '<table><tr><td><thead><tbody><div><img><br><p><a><strong><b><i><em><blockquote><code><hr><h1><h2><h3><h4><h5><h6><label><ul><ol><li><span><sub><sup>';

    /**
     * Bug KAYAKOC-2410 - XSS vulnerability in Support Center
     * @author Banjo Paul <banjo.paul@aurea.com>
     */
    /**
     * Bug KAYAKOC-6254 - Unable to insert videos in Ticket replies
     * Allow iframe for Youtube videos
     * @author Werner Garcia
     */
    $allowedTags .= '<video><source><iframe>';

    $decoded_html = html_entity_decode($html);
    /**
     * Bug KAYAKOC-2411 - XSS vulnerability in Support Center
     * Strip encoded javascript code in href and src attributes of tags
     * @author Werner Garcia
     */
    $decoded_html = strip_javascript($decoded_html);
    $decoded_html = preg_replace("/(href|src)(\s+)?=(\s+)?(['\"])(\s+)?javascript(\s+)?[^\w.](.*)?\\4/i", '', $decoded_html);

    /**
     * Bug KAYAKOC-6254 - Unable to insert videos in Ticket replies
     * Allow only Youtube URLs in src attribute of iframe tags
     * @author Werner Garcia
     */
    $decoded_html = preg_replace_callback("/(<iframe .*)src(\s+)?=(\s+)?(['\"])([^'\"]*)\\4.*<\/iframe>/i", function($matches) {
        $url = parse_url($matches[5], PHP_URL_HOST);
        if (substr($url, -11) !== 'youtube.com' && substr($url, -8) !== 'youtu.be') {
            // remove tag
            return '';
        } else {
            // valid Youtube URL
            return $matches[0];
        }
    }, $decoded_html);

    /**
     * Bugfix KAYAKOC-6655 - QuickSupport should allow the rendering
     * of mathematical representations including "<" or ">" symbols.
     * @author Banjo Mofesola Paul
     *
     * spacing out <> in somewhat mathematical notations
     */
    $decoded_html = preg_replace("/([<>])([0-9])/", "$1 $2", $decoded_html);
    $decoded_html = preg_replace("/([0-9])([<>])/", "$1 $2", $decoded_html);

    if ($stripAllHTMLTags) {
        $clean_html = strip_tags($decoded_html);
    } else {
        $clean_html = strip_tags($decoded_html, $allowedTags);
    }

    if (empty($clean_html) && !$_canReturnEmpty) {
        $clean_html = '-- EMPTY HTML --';
    }
    return $clean_html;
}

/**
 * Converts UTF8 into Latin.
 *
 * @param string $value
 *
 * @return mixed
 */
function transliterate($value)
{
    return \URLify::transliterate($value);
}

/**
 * Strips non-alphanumeric characters.
 *
 * @param string     $value
 * @param bool|false $urldecode
 * @param bool|false $convertSpacesTo
 * @param array      $allowedCharacters
 *
 * @return string
 */
function alphanum($value, $urldecode = false, $convertSpacesTo = false, $allowedCharacters = [])
{
    if ($urldecode) {
        $value = urldecode($value);
    }
    if ($convertSpacesTo) {
        $value               = str_replace(' ', $convertSpacesTo, $value);
        $allowedCharacters[] = $convertSpacesTo;
    }
    $delimiter = '~';
//    if (false && in_array($delimiter, $allowedCharacters)) {
//        $delimiter = '#';
//    }
    if (!empty($allowedCharacters)) {
        $regex = $delimiter.'[^0-9a-z'.preg_quote(implode('', $allowedCharacters)).']+'.$delimiter.'i';
    } else {
        $regex = $delimiter.'[^0-9a-z]+'.$delimiter.'i';
    }
    return trim(preg_replace($regex, '', $value));
}

///////////////// BEGIN NAMESPACES CODE //////////////////

function get_swift_namespaces() {
    return ['troubleshooter', 'news', 'archiver', 'knowledgebase', 'tickets', 'base', 'livechat', 'parser'];
}

function prepend_app_namespace($_appName, $_appSetupClassName) {
    foreach (get_swift_namespaces() as $_app) {
        if ($_appName === $_app) {
            return sprintf('\\%s\\%s', ucwords(Clean($_appName)), $_appSetupClassName);
        }
    }

    return $_appSetupClassName;
}

function prepend_model_namespace($_appName, $_modelClassName, $_modelFilePath) {
    foreach (get_swift_namespaces() as $_app) {
        if ($_appName === $_app) {
            $_model = preg_replace('/^.*\/models\/([^\/]*)\/.*$/', '\1', $_modelFilePath);

            return sprintf('\\%s\\Models\\%s\\%s', ucwords($_appName), $_model, $_modelClassName);
        }
    }

    return $_modelClassName;
}

/**
 * Prepends the namespace to a library name
 *
 * @param array  $_lib array with class information
 * @param string $_libraryName
 * @param string $_libraryClassName
 * @param string $_dir
 * @param string $_appName
 * @return string
 */
function prepend_library_namespace(array $_lib, $_libraryName, $_libraryClassName, $_dir = 'Library', $_appName = '') {
    if (count($_lib) === 1) {
        $_classes = get_declared_classes();
        foreach ($_classes as $i => $_class) {
            // if the class is already loaded, return it
            if (false !== strpos($_class, '\\' . $_libraryClassName)) {
                return $_class;
            }
        }

        // not found, return default
        return $_libraryClassName;
    }

    // if the library group name is provided, use it
    foreach (get_swift_namespaces() as $_app) {
        if (stripos($_libraryName, $_app) === 0 || $_appName === $_app) {
            return sprintf('\\%s\\%s\\%s\\%s', ucwords(Clean($_app)), $_dir, $_lib[0], $_libraryClassName);
        }
    }

    return $_libraryClassName;
}

function prepend_controller_namespace($_appName, $_interfaceName, $_controllerClassName) {
    foreach (get_swift_namespaces() as $_app) {
        if ($_appName === $_app) {
            return sprintf('\\%s\\%s\\%s', ucwords(Clean($_appName)),
                ucwords($_interfaceName), $_controllerClassName);
        }
    }

    return $_controllerClassName;
}

function prepend_view_namespace($_appName, $_interfaceName, $_viewClassName) {
    foreach (get_swift_namespaces() as $_app) {
        if ($_appName === $_app) {
            return sprintf('\\%s\\%s\\%s', ucwords(Clean($_appName)),
                ucwords($_interfaceName), $_viewClassName);
        }
    }

    return $_viewClassName;
}

function get_short_class($object) {
    try {
        $reflect = new ReflectionClass($object);

        return $reflect->getShortName();
    } catch (ReflectionException $e) {
    }

    return $object;
}

/**
 * Turn all the URLs into clickable links
 * This is using the linkify functionality https://gist.github.com/jasny/2000705
 *
 * @param string $value
 * @param string[] $protocols
 * @param array $attributes
 * @return null|string
 *
 */
function ConvertTextUrlsToLinks($value, $protocols = array('http', 'mail'), array $attributes = array()) {
    $oldValue = $value;
	// Link attributes
	$attr = '';
	foreach ($attributes as $key => $val) {
		$attr .= ' ' . $key . '="' . htmlentities($val) . '"';
	}

	$links = array();

	// Extract existing links and tags
    $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { return '<' . array_push($links, $match[1]) . '>'; }, $value);

    //Required to handle large inline images
    if (preg_last_error() !== PREG_NO_ERROR) {
        return $oldValue;
    }

	// Extract text links for each protocol
	foreach ((array)$protocols as $protocol) {
		switch ($protocol) {
			case 'http':
			case 'https':
				$value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) {
					if ($match[1])
						$protocol = $match[1];
					$link = $match[2] ?: $match[3];
					$prefix = !empty($match[1]) ? $match[1] . '://' : '';
					$attrPrefix = !empty($attr) ? $attr . ' ' : '';
					return '<' . array_push($links, "<a {$attrPrefix}href=\"$protocol://$link\">{$prefix}{$link}</a>") . '>';
				}, $value);
				break;
			case 'mail':
				$value = preg_replace_callback('~([A-Za-z0-9_\-\+\.]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) {
					$attrPrefix = !empty($attr) ? $attr . ' ' : '';
					return '<' . array_push($links, "<a {$attrPrefix}href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>';
				}, $value);
				break;
			case 'twitter':
				$value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) {
					return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1] . "\">{$match[0]}</a>") . '>';
				}, $value);
				break;
			default:
				$value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) {
					return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>';
				}, $value);
				break;
		}
	}

	// Insert all link
	return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
}

/**
 * Convert date/time format between `date()` and `strftime()`
 *
 * Timezone conversion is done for Unix. Windows users must exchange %z and %Z.
 *
 * Unsupported date formats : S, n, t, L, B, G, u, e, I, P, Z, c, r
 * Unsupported strftime formats : %U, %W, %C, %g, %r, %R, %T, %X, %c, %D, %F, %x
 *
 * @author Werner Garcia based on code from https://github.com/mcaskill
 *
 * @example Convert `%A, %B %e, %Y, %l:%M %P` to `l, F j, Y, g:i a`, and vice versa for "Saturday, March 10, 2001, 5:16 pm"
 * @link http://php.net/manual/en/function.strftime.php#96424
 *
 * @param string $format The format to parse.
 * @param string $syntax The format's syntax. Either 'strf' for `strtime()` or 'date' for `date()`.
 * @return bool|string Returns a string formatted according $syntax using the given $format or `false`.
 */
function date_format_to($format, $syntax)
{
    // http://php.net/manual/en/function.strftime.php
    $strf_syntax = [
        0 => '%O', // extra modifier, ie add suffix: str_replace('%O', date('S', $timestamp), $format);
        1 => '%d', // Two-digit day of the month (with leading zeros)	01 to 31
        2 => '%a', // An abbreviated textual representation of the day	Sun through Sat
        3 => '%e', // Day of the month, with a space preceding single digits. Not implemented as described on Windows. See below for more information.	1 to 31
        4 => '%A', // A full textual representation of the day	Sunday through Saturday
        5 => '%u', // ISO-8601 numeric representation of the day of the week	1 (for Monday) through 7 (for Sunday)
        6 => '%w', // Numeric representation of the day of the week	0 (for Sunday) through 6 (for Saturday)
        7 => '%j', // Day of the year, 3 digits with leading zeros	001 to 366
        8 => '%V', // ISO-8601:1988 week number of the given year, starting with the first week of the year with at least 4 weekdays, with Monday being the start of the week	01 through 53 (where 53 accounts for an overlapping week)
        9 => '%B', // Full month name, based on the locale	January through December
        10 => '%m', // Two digit representation of the month	01 (for January) through 12 (for December)
        11 => '%b', // Abbreviated month name, based on the locale	Jan through Dec
        12 => '%-m', // stripping leading zeros from months in the short formats
        13 => '%G', // The full four-digit version of %g	Example: 2008 for the week of January 3, 2009
        14 => '%Y', // Four digit representation for the year	Example: 2038
        15 => '%y', // Two digit representation of the year	Example: 09 for 2009, 79 for 1979
        16 => '%P', // lower-case 'am' or 'pm' based on the given time	Example: am for 00:31, pm for 22:23
        17 => '%p', // UPPER-CASE 'AM' or 'PM' based on the given time	Example: AM for 00:31, PM for 22:23
        18 => '%l', // Hour in 12-hour format, with a space preceding single digits	1 through 12
        19 => '%I', // Two digit representation of the hour in 12-hour format	01 through 12
        20 => '%H', // Two digit representation of the hour in 24-hour format	00 through 23
        21 => '%M', // Two digit representation of the minute	00 through 59
        22 => '%S', // Two digit representation of the second	00 through 59
        23 => '%z', // The time zone offset. Not implemented as described on Windows. See below for more information.	Example: -0500 for US Eastern Time
        24 => '%Z', // The time zone abbreviation. Not implemented as described on Windows. See below for more information.	Example: EST for Eastern Time
        25 => '%s', // Unix Epoch Time timestamp (same as the time() function)	Example: 305815200 for September 10, 1979 08:40:00 AM
    ];

    // http://php.net/manual/en/function.date.php
    $date_syntax = [
        0 => 'S', // English ordinal suffix for the day of the month, 2 characters
        1 => 'd', // Two-digit day of the month (with leading zeros)    01 to 31
        2 => 'D', // An abbreviated textual representation of the day   Sun through Sat
        3 => 'j', // Day of the month, with a space preceding single digits. Not implemented as described on Windows. See below for more information.   1 to 31
        4 => 'l', // A full textual representation of the day   Sunday through Saturday
        5 => 'N', // ISO-8601 numeric representation of the day of the week     1 (for Monday) through 7 (for Sunday)
        6 => 'w', // Numeric representation of the day of the week      0 (for Sunday) through 6 (for Saturday)
        7 => 'z', // Day of the year, 3 digits with leading zeros       001 to 366
        8 => 'W', // ISO-8601:1988 week number of the given year, starting with the first week of the year with at least 4 weekdays, with Monday being the start of the week    01 through 53 (where 53 accounts for an overlapping week)
        9 => 'F', // Full month name, based on the locale       January through December
        10 => 'm', // Two digit representation of the month     01 (for January) through 12 (for December)
        11 => 'M', // Abbreviated month name, based on the locale       Jan through Dec
        12 => 'n', // Numeric representation of a month, without leading zeros	1 through 12
        13 => 'o', // The full four-digit version of %g Example: 2008 for the week of January 3, 2009
        14 => 'Y', // Four digit representation for the year    Example: 2038
        15 => 'y', // Two digit representation of the year      Example: 09 for 2009, 79 for 1979
        16 => 'a', // lower-case 'am' or 'pm' based on the given time   Example: am for 00:31, pm for 22:23
        17 => 'A', // UPPER-CASE 'AM' or 'PM' based on the given time   Example: AM for 00:31, PM for 22:23
        18 => 'g', // Hour in 12-hour format, with a space preceding single digits      1 through 12
        19 => 'h', // Two digit representation of the hour in 12-hour format    01 through 12
        20 => 'H', // Two digit representation of the hour in 24-hour format    00 through 23
        21 => 'i', // Two digit representation of the minute    00 through 59
        22 => 's', // Two digit representation of the second    00 through 59
        23 => 'O', // The time zone offset. Not implemented as described on Windows. See below for more information. Example: -0500 for US Eastern Time
        24 => 'T', // The time zone abbreviation. Not implemented as described on Windows. See below for more information. Example: EST for Eastern Time
        25 => 'U', // Unix Epoch Time timestamp (same as the time() function)   Example: 305815200 for September 10, 1979 08:40:00 AM
    ];

    // http://userguide.icu-project.org/formatparse/datetime
    $intl_syntax = [
        0 => '', // extra modifier, ie add suffix: str_replace('%O', date('S', $timestamp), $format);
        1 => 'dd', // Two-digit day of the month (with leading zeros)    01 to 31
        2 => 'E', // An abbreviated textual representation of the day   Sun through Sat
        3 => 'd', // Day of the month, with a space preceding single digits. Not implemented as described on Windows. See below for more information.   1 to 31
        4 => 'EEEE', // A full textual representation of the day   Sunday through Saturday
        5 => 'e', // ISO-8601 numeric representation of the day of the week     1 (for Monday) through 7 (for Sunday)
        6 => 'e', // Numeric representation of the day of the week      0 (for Sunday) through 6 (for Saturday)
        7 => 'D', // Day of the year, 3 digits with leading zeros       001 to 366
        8 => 'w', // ISO-8601:1988 week number of the given year, starting with the first week of the year with at least 4 weekdays, with Monday being the start of the week    01 through 53 (where 53 accounts for an overlapping week)
        9 => 'MMMM', // Full month name, based on the locale    January through December
        10 => 'MM', // Two digit representation of the month     01 (for January) through 12 (for December)
        11 => 'MMM', // Abbreviated month name, based on the locale       Jan through Dec
        12 => 'M', // stripping leading zeros from months in the short formats
        13 => 'y', // The full four-digit version of %g Example: 2008 for the week of January 3, 2009
        14 => 'y', // Four digit representation for the year    Example: 2038
        15 => 'yy', // Two digit representation of the year      Example: 09 for 2009, 79 for 1979
        16 => 'a', // lower-case 'am' or 'pm' based on the given time   Example: am for 00:31, pm for 22:23
        17 => 'a', // UPPER-CASE 'AM' or 'PM' based on the given time   Example: AM for 00:31, PM for 22:23
        18 => 'h', // Hour in 12-hour format, with a space preceding single digits      1 through 12
        19 => 'hh', // Two digit representation of the hour in 12-hour format   01 through 12
        20 => 'HH', // Two digit representation of the hour in 24-hour format    00 through 23
        21 => 'mm', // Two digit representation of the minute   00 through 59
        22 => 'ss', // Two digit representation of the second    00 through 59
        23 => 'x', // The time zone offset. Not implemented as described on Windows. See below for more information. Example: -0500 for US Eastern Time
        24 => 'O', // The time zone abbreviation. Not implemented as described on Windows. See below for more information. Example: EST for Eastern Time
        25 => 'A', // Unix Epoch Time timestamp (same as the time() function)   Example: 305815200 for September 10, 1979 08:40:00 AM
    ];

    switch ($syntax) {
        case 'intl':
            $from = $strf_syntax;
            $to = $intl_syntax;
            break;

        case 'date':
            $from = $strf_syntax;
            $to = $date_syntax;
            break;

        case 'strf':
            $from = $date_syntax;
            $to = $strf_syntax;
            break;

        default:
            return false;
    }

    $pattern = array_map(
        function ($s) {
            return '/(?<!\\\\|\%)' . $s . '/';
        },
        $from
    );

    return preg_replace($pattern, $to, $format);
}

/**
 * Equivalent to `date_format_to( $format, 'date' )`
 *
 * @param string $strf_format A `strftime()` date/time format
 * @return string
 */
function strftime_format_to_date_format($strf_format)
{
    return date_format_to($strf_format, 'date');
}

/**
 * Equivalent to `date_format_to( $format, 'intl' )`
 *
 * @param string $strf_format A `strftime()` date/time format
 * @return string
 */
function strftime_format_to_intl_format($strf_format)
{
    return date_format_to($strf_format, 'intl');
}

/**
 * Equivalent to `convert_datetime_format_to( $format, 'strf' )`
 *
 * @param string $date_format A `date()` date/time format
 * @return string
 */
function date_format_to_strftime_format($date_format)
{
    return date_format_to($date_format, 'strf');
}

/**
 * Debug to chrome js console
 *
 * @param string $var
 * @param string|null $tags
 */
function jsdebug($var, $tags = null) {
    if (constant('SWIFT_DEBUG')) {
        PhpConsole\Connector::getInstance()->getDebugDispatcher()->dispatchDebug($var, $tags, 1);
    }
}

/**
 * @param string $str
 * @return string
 */
function text_to_html_entities($str, $stripAllHTMLTags = 0, $_canReturnEmpty = true, $convertSingleQuotes = false) {
    // Prevent HTML injection
    $str = removeTags(html_entity_decode($str), $stripAllHTMLTags, $_canReturnEmpty);

    while (SWIFT_HTML::DetectHTMLEntities(html_entity_decode($str))) {
        $str = html_entity_decode($str);
    }
    if ($convertSingleQuotes) {
       $str = str_replace('"', "'", $str);
    }
    return html_entity_decode($str);
}

/***
 * @param string $msg
 * @param bool $canExit
 */
function log_error_and_exit($msg = '', $canExit = true)
{
    if (constant('SWIFT_DEBUG') === true) {
        $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $dbt[1]['function'] ?? '';
        $str = 'SWIFT: ';
        if (!empty($msg)) {
            $str .= $msg . ' - ';
        }
        $str .= 'Hard exiting at ' . $caller;
        error_log($str);
    }

    if ($canExit) {
        exit;
    }
}

// strptime polyfill
if (!function_exists('strptime'))
{
    /**
     * @param string $date
     * @param string $format
     * @return boolean
     */
    function strptime($date, $format)
    {
        windows_strptime($date, $format);
    }
}

/**
 * @param string $date
 * @param string $format
 * @return array|false
 */
function windows_strptime($date, $format)
{
    $masks = array(
        '%d' => '(?P<d>[0-9]{2})',
        '%m' => '(?P<m>[0-9]{2})',
        '%Y' => '(?P<Y>[0-9]{4})',
        '%H' => '(?P<H>[0-9]{2})',
        '%M' => '(?P<M>[0-9]{2})',
        '%S' => '(?P<S>[0-9]{2})'
    );

    $rexep = "#" . strtr(preg_quote($format), $masks) . "#";
    if (!preg_match($rexep, $date, $out))
        return false;

    $ret = array(
        "tm_sec" => (int) $out['S'],
        "tm_min" => (int) $out['M'],
        "tm_hour" => (int) $out['H'],
        "tm_mday" => (int) $out['d'],
        "tm_mon" => $out['m'] ? $out['m'] - 1 : 0,
        "tm_year" => $out['Y'] > 1900 ? $out['Y'] - 1900 : 0,
    );
    return $ret;
}

/**
 * @param string $_selector
 * @param string $_autofocus
 * @param string $_updatectrl
 * @return string
 * @throws SWIFT_Exception
 * @author Werner Garcia
 */
function GetTinyMceCode(string $_selector, string $_autofocus = '', string $_updatectrl = ''): string
{
    $_SWIFT        = SWIFT::GetInstance();
    $_LanguageLoad = 'en';

    If ($_SWIFT->Language->GetLanguageCode() === 'sv') {
        $_LanguageLoad = 'sv_SE';
    }

    If ($_SWIFT->Language->GetLanguageCode() === 'ru') {
        $_LanguageLoad = 'ru-ru';
    }

    If ($_SWIFT->Language->GetLanguageCode() === 'fr') {
        $_LanguageLoad = 'fr_FR';
    }

    If ($_SWIFT->Language->GetLanguageCode() === 'pt') {
        $_LanguageLoad = 'pt_PT';
    }

    $_extrajs = '';
    if (!empty($_updatectrl)) {
        $_extrajs = '$("#' . $_updatectrl . '").val(tinymce.activeEditor.getContent());';
    }

    $product_url = rtrim($_SWIFT->Settings->Get('general_producturl'), '/');

    return '
                $( ".mce-floatpanel" ).remove();
                $( ".mce-tooltip" ).remove();
                tinymce.init({
                    script_url : "' . $product_url . '/__swift/apps/base/javascript/__global/thirdparty/TinyMCE/tinymce.min.js",
                    selector: "' . $_selector . '",
                    auto_focus: "' . $_autofocus . '",
                    paste_data_images: true,
                    image_title: true,
                    automatic_uploads: true,
                    file_picker_types: "image",
                    file_picker_callback: function(cb, value, meta) {
                        var input = document.createElement("input");
                        input.setAttribute("type", "file");
                        input.setAttribute("accept", "image/*");
                        input.onchange = function() {
                            var file = this.files[0];
                            var reader = new FileReader();
                            reader.onload = function () {
                                var id = "blobid" + (new Date()).getTime();
                                var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                                var base64 = reader.result.split(",")[1];
                                var blobInfo = blobCache.create(id, file, base64);
                                blobCache.add(blobInfo);
                                cb(blobInfo.blobUri(), { title: file.name });
                            };
                            reader.readAsDataURL(file);
                        };
                        input.click();
                    },
                    language: "' . $_LanguageLoad . '",
                    force_br_newlines : false,
                    entity_encoding : "raw",
                    relative_urls : false,
                    apply_source_formatting : false,
                    remove_script_host : false,
                    fix_list_elements: true,
                    convert_urls : true,
                    force_p_newlines : false,
                    remove_linebreaks : true,
                    remove_trailing_nbsp : false,
                    browser_spellcheck : true,
                    gecko_spellcheck: true,
                    verify_html : false,
                    theme: "modern",
                    plugins: [
                        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
                        "searchreplace wordcount visualblocks visualchars code fullscreen",
                        "insertdatetime media nonbreaking save table directionality",
                        "template paste textcolor codesample"
                    ],
                    toolbar1: "undo redo | styleselect | bold italic underline | fontsizeselect fontselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image print preview media | forecolor backcolor ",
                    image_advtab: true,
                    menubar: "file edit insert view format tools",
                    setup: function (editor) {
                        editor.on("change", function () {
                            ' . $_extrajs . '
                            editor.save();
                        });
                    }
                });
        ';
}
