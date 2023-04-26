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
 * Used by Mail_mimeDecode to perform "encoded-word" decoding
 *
 * @author Ryan M Lederman
 */
class SWIFT_MailMIMEDecodePolicy extends SWIFT_Library
{
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
     * Converts str to the required encoding based on the current encoding,
     * which is specified in fromEncoding.
     *
     * @author Ryan M. Lederman
     * @param string $_string The String to Convert
     * @param string $_fromEncoding The Encoding to Convert From
     * @return bool "true" on Success, "false" otherwise
     */
    public function ConvertEncoding($_string /* Input string */, $_fromEncoding /* e.g. "KOI8-R" */)
    {
        // Do conversion to native encoding:
        // 1. If the desk is UTF-8, always do it.
        // 2. If the desk is not UTF-8, only do it if pr_conversion is enabled.
        // 3. If the encodings are not already the same.

        $_nativeEncoding = strtoupper($this->Language->Get('charset'));

        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-1754 Korean characters are not displaying correctly in Staff CP
         *
         * Comments: mbstring functions do not handle the 'ks_c_5601-1987' & 'ks_c_5601-1989' charsets. used in various versions of Outlook to send Korean characters.
         */
        if (in_array(mb_strtolower($_fromEncoding), array('ks_c_5601-1987', 'ks_c_5601-1989'))) {
            $_fromEncoding = 'UHC';
        }

        if ('UTF-8' == $_nativeEncoding || 1 == $this->Settings->Get('pr_conversion'))
        {
            if ($_nativeEncoding != strtoupper($_fromEncoding))
            {
                if (in_array(USE_ICONV, [true]) && extension_loaded('iconv')) {
                    /*
                     * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
                     *
                     * SWIFT-4734 Support for charset encoding iso-8859-8-i by parser
                     *
                     * Comments: As of now iconv() not supporting iso-8859-8-i so we are handling this.
                     */
                    if (mb_strtolower($_fromEncoding) == 'iso-8859-8-i') {

                        $_fromEncoding = 'iso-8859-8';
                    }

                    // iconv (for charsets not supported by mbstring)
                    $_string = iconv($_fromEncoding , $_nativeEncoding. '//TRANSLIT//IGNORE', $_string);
                } else {
                    // mbstring (default)
                    $_string = @mb_convert_encoding($_string, $_nativeEncoding, $_fromEncoding);
                }
            }
        }

        return $_string;
    }
}

?>
