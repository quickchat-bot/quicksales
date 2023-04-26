<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * Input library
 *
 * @author Anjali Sharma
 */
class SWIFT_Input extends SWIFT_Library
{
    private $_data = array();
    private $_dataCache = array();

    private $_config = array(
        'supportedglobals' => array('GET', 'POST', 'REQUEST', 'SERVER')
    );

    static $_sanitizationConfig = array(

        "notallowedstrings"   => array(
            'document.cookie' => '[removed]',
            'document.write'  => '[removed]',
            'document.domain'  => '[removed]',
            '.parentNode'     => '[removed]',
            '.innerHTML'      => '[removed]',
            'window.location' => '[removed]',
            '-moz-binding'    => '[removed]',
        ),

        "notallowedregex"     => array(
            'javascript\s*:',
            ';cript\s*:',
            'expression\s*(\(|&\#40;)', // CSS and IE
            'vbscript\s*:', // IE, surprise!
            'Redirect\s+302',
        ),

        "words"               => array(
            'javascript', ';cript', 'expression', 'vbscript', 'script', 'applet',
            'alert', 'document', 'write', 'cookie', 'window'
        ),

        "invisiblecharacters" => array('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'),

        "badtags"             => 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|marquee|object|plaintext|style|script|textarea|title|video|xml|xss',

    );


    /**
     *
     * Constructor
     *
     * @author Anjali Sharma
     * @param bool $_clearGlobals
     */
    public function __construct($_clearGlobals = false)
    {
        parent::__construct();
        $this->SetConfig('clearglobals', $_clearGlobals);
        $this->Init();
    }

    /**
     * Initialization
     *
     * @author Anjali Sharma
     * @return bool "true" always
     */
    private function Init()
    {

        foreach ($this->GetConfig('supportedglobals') as $_globalName) {
            switch ($_globalName) {
                case 'GET':
                    $this->_data[$_globalName] = $_GET;
                    break;
                case 'POST':
                    $this->_data[$_globalName] = $_POST;
                    break;
                case 'REQUEST':
                    $this->_data[$_globalName] = $_REQUEST;
                    break;
                case 'SERVER':
                    $this->_data[$_globalName] = $_SERVER;
                    break;
            }
        }

        if (($this->GetConfig('clearglobals'))) {
            $_GET = $_POST = $_REQUEST = array();
        }

        return true;
    }


    /**
     * Set configuration
     *
     * @author Anjali Sharma
     * @param string $_key
     * @param mixed  $_value
     * @return SWIFT_Input
     */
    function SetConfig($_key, $_value)
    {
        $this->_config[$_key] = $_value;
        return $this;
    }

    /**
     * Get configuration
     *
     * @author Anjali Sharma
     * @param string $_key
     * @return mixed
     */
    function GetConfig($_key)
    {
        return $this->_config[$_key];
    }

    /**
     * Retrieves the Get parameter
     *
     * @author Anjali Sharma
     * @param string $_parameterName Parameter Name
     * @param bool   $_sanitize      Indicator for parameter XSS sanitization
     * @param mixed  $_defaultValue  Value (default) to returned
     * @return SWIFT_Input
     */
    public function Get($_parameterName, $_sanitize = true, $_defaultValue = false)
    {
        return $this->GetParameter('GET', $_parameterName, $_sanitize, $_defaultValue);
    }

    /**
     * Retrieves the Post parameter
     *
     * @author Anjali Sharma
     * @param string $_parameterName Parameter Name
     * @param bool   $_sanitize      Indicator for parameter XSS sanitization
     * @param mixed  $_defaultValue  Value (default) to returned
     * @return string|int
     */
    public function Post($_parameterName, $_sanitize = true, $_defaultValue = false)
    {
        return $this->GetParameter('POST', $_parameterName, $_sanitize, $_defaultValue);
    }

    /**
     * Retrieves the Request parameter
     *
     * @author Anjali Sharma
     * @param string $_parameterName Parameter Name
     * @param bool   $_sanitize      Indicator for parameter XSS sanitization
     * @param mixed  $_defaultValue  Value (default) to returned
     * @return SWIFT_Input
     */
    public function Request($_parameterName, $_sanitize = true, $_defaultValue = false)
    {
        return $this->GetParameter('REQUEST', $_parameterName, $_sanitize, $_defaultValue);
    }

    /**
     * Retrieves the Server parameter
     *
     * @author Anjali Sharma
     * @param string $_parameterName Parameter Name
     * @param bool   $_sanitize      Indicator for parameter XSS sanitization
     * @param mixed  $_defaultValue  Value (default) to returned
     * @return SWIFT_Input
     */
    public function Server($_parameterName, $_sanitize = true, $_defaultValue = false)
    {
        return $this->GetParameter('SERVER', $_parameterName, $_sanitize, $_defaultValue);
    }

    /**
     * Retrieves the specified Parameter information from container
     *
     * @author Anjali Sharma
     * @param string $_globalName     Container name
     * @param string $_parameterName  Parameter name
     * @param bool   $_sanitization   Indicate XSS sanitization
     * @param mixed  $_defaultValue   Value (default) to be returned
     * @return SWIFT_Input|string
     */
    private function GetParameter($_globalName, $_parameterName, $_sanitization, $_defaultValue)
    {

        if (isset($this->_dataCache[$_globalName][$_parameterName])) {
            return $this->_dataCache[$_globalName][$_parameterName];
        }

        $_result = $_defaultValue;
        if (array_key_exists($_parameterName, $this->_data[$_globalName])) {
            $_result = $this->_data[$_globalName][$_parameterName];
            $_result = $_sanitization ? $this->SanitizeForXSS($_result) : $_result;

            // Cache
            $this->_dataCache[$_globalName][$_parameterName] = $_result;
        }

        return $_result;
    }


    /**
     * Sanitizes the data for XSS attacks filteration
     *
     * @author Anjali Sharma
     * @author Utsav Handa
     * @param string $_str     String to be sanitized
     * @param bool $_isImage Image indicator
     * @param bool $_isRawURLencoding Url encoding
     * @return string|bool XSS-sanitized string
     */
    public function SanitizeForXSS($_str, $_isImage = false, $_isRawURLencoding = false)
    {
        /**
         * Remove invisible charcters
         * (Every control character except newline, carriage return, and horizontal tab)
         */
        do {
            $_str = preg_replace(self::$_sanitizationConfig['invisiblecharacters'], '', $_str, -1, $_count);
        } while ($_count);

        /*
         * Validate URL entities
         */
        // Validate standard character entities
        // (Add a semicolon, if missing to enable the later conversion of entities to ASCII)
        /*
         * BUG FIX - Ravi Sharma
         *
         * SWIFT-3647 If URL is used in ticket contents, it gets changed while rendering in Staff CP. Symbol ';' is added to the URL.
         *
         * Comments: Added a "^" in regular expression to avoid non variable text containing
         */
        $_str = preg_replace('#(^&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', '\\1;\\2', $_str);

        // Validate UTF16 two byte encoding (x00)
        $_str = preg_replace('#(&\#x?)([0-9A-F]+);?#i', "\\1\\2;", $_str);

        /*
         * URL Decode
         */
        // Handles <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a> and specifically
        // avoid URL's decoding to alter original URL's. Extract URL's from content. Decode content and
        // replace back URL's.
        $_links = array();
        $_str = preg_replace_callback('/(ftp|https?):\/\/(\w+:?\w*@)?(\S+)(:[0-9]+)?(\/([\w#!:.?+=&%@!\/-])?)?/', function($_match) use (&$_links) { $_links[] = $_match[0]; return md5($_match[0]); }, $_str);

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4165 URL Encoding characters rendering issue in ticket posts.
         * SWIFT-4987 Random characters in URLs showing in KD
         * SWIFT-4993 Issue with Greek characters
         */
        if($_isRawURLencoding) {
            $_str = utf8_decode(rawurlencode($_str));
            $_str = rawurldecode(utf8_encode($_str));
        }
        else {
            $_str = rawurldecode($_str);
        }
        array_walk($_links, function($_link) use (&$_str) { $_str = str_replace(md5($_link), $_link, $_str); });
        unset($_links);

        /**
         * Convert character entities to ASCII
         */
        $_str = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", array($this, 'DecodeEntity'), $_str);

        /*
         *  Remove Invisible Characters again
         */
        do {
            $_str = preg_replace(self::$_sanitizationConfig['invisiblecharacters'], '', $_str, -1, $_count);
        } while ($_count);

        /*
         * Convert all tabs to spaces, preventing strings like this: ja    vascript
         */
        $_str = str_replace("\t", ' ', $_str);

        /*
         * Store converted string for later comparison
         */
        $_convertedStr = $_str;

        /*
         * Remove Strings that are never allowed
         */
        $_str = str_replace(array_keys(self::$_sanitizationConfig['notallowedstrings']), self::$_sanitizationConfig['notallowedstrings'], $_str);
        foreach (self::$_sanitizationConfig['notallowedregex'] as $regex) {
            $_str = preg_replace('#' . $regex . '#is', '[removed]', $_str);
        }

        /*
         * Compact any exploded words
         * (Correct words like:  j a v a s c r i p t : into their original state)
         */
        foreach (self::$_sanitizationConfig['words'] as $_word) {
            $_temp = '';

            for ($_len = 0, $_wordlen = strlen($_word); $_len < $_wordlen; $_len++) {
                $_temp .= substr($_word, $_len, 1) . "\s*";
            }

            // We only want to do this when it is followed by a non-word character
            // That way valid stuff like "dealer to" does not become "dealerto"
            $_str = preg_replace_callback('#(' . substr($_temp, 0, -3) . ')(\W)#is', function ($_matches) {
                return preg_replace('/\s+/s', '', $_matches[1]) . $_matches[2];
            }, $_str);
        }

        /*
         * Remove disallowed Javascript in links or img tags
         */
        do {
            $_original = $_str;

            if (preg_match("/<a/i", $_str)) {
                /**
                 * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>, Mansi Wason <mansi.wason@kayako.com>
                 *
                 * SWIFT-4500 Incomplete email addresses listed in the From field while replying to offline chat messages.
                 */
                $_str = preg_replace_callback('#<a[^a-z0-9\.>]+([^>]-*?)(?:>|$)#si', array($this, 'JSImageRemoval'), $_str);
            }

            if (preg_match("/script|object/i", $_str) OR preg_match("/xss/i", $_str)) {
                $_str = preg_replace("#<(/*)(script|xss|object)(.*?)\>#si", '[removed]', $_str);
            }
        } while ($_original != $_str);
        unset($_original);

        // Remove evil attributes such as onclick, onload, xmlns etc.
        $_str = $this->RemoveEvilAttributes($_str, $_isImage);

        /*
         * Sanitize bad scripting elements
         */
        // (Similar to above. Also looks for PHP and JavaScript commands that are disallowed. Instead of removing the
        //  code, it simply converts the parenthesis to entities rendering the code un-executable)
        $_str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $_str);

        /*
         * Additional precaution, in case something gets through the above filters
         */
        $_str = str_replace(array_keys(self::$_sanitizationConfig['notallowedstrings']), self::$_sanitizationConfig['notallowedstrings'], $_str);
        foreach (self::$_sanitizationConfig['notallowedregex'] as $_regex) {
            $_str = preg_replace('#' . $_regex . '#is', '[removed]', $_str);
        }

        /*
         * Images are Handled in a Special Way
         * - Essentially, we want to know that after all of the character
         * conversion is done whether any unwanted, likely XSS, code was found.
         * If not, we return TRUE, as the image is clean.
         * However, if the string post-conversion does not matched the
         * string post-removal of XSS, then it fails, as there was unwanted XSS
         * code found and removed/changed during processing.
         */

        if ($_isImage === true) {
            return ($_str == $_convertedStr);
        }

        return $_str;
    }

    // ------------------------------------------------------------------- //
    // ------------------------------------------------------------------- //

    /**
     * Javascript link sanitization
     *
     * @original CodeIgniter Security Library
     * @author Utsav Handa
     * @param array $_match
     * @return string "Sanitized String" on success, "false" on failure
     */
    private function JSLinkRemoval($_match)
    {
        return str_replace(
            $_match[1],
            preg_replace(
                '#href=.*?(alert\(|alert&\#40;|alert&lpar;|confirm\(|confirm&\#40;|confirm&lpar;|javascript\:|javascript&colon;|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
                '',
                $this->FilterAttributes(str_replace(array('<', '>'), '', $_match[1]))
            ),
            $_match[0]
        );
    }

    /**
     * Filter tag attributes for consistency and safety
     *
     * @original CodeIgniter Security Library
     * @author Utsav Handa
     * @param string $_str
     * @return string "Sanitized String" on success, "false" on failure
     */
    private function FilterAttributes($_str)
    {
        $_out = '';

        if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $_str, $_matches)) {
            foreach ($_matches[0] as $_match) {
                $_out .= preg_replace("#/\*.*?\*/#s", '', $_match);
            }
        }

        return $_out;
    }

    /**
     * Javascript image removal
     *
     * @original CodeIgniter Security Library
     * @author Utsav Handa
     * @param array $_match
     * @return string "Sanitized String" on success, "false" on failure
     */
    private function JSImageRemoval($_match)
    {
        return str_replace(
            $_match[1],
            preg_replace(
                '#src=.*?(alert\(|alert&\#40;|alert&lpar;|confirm\(|confirm&\#40;|confirm&lpar;|javascript\:|javascript&colon;|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss\s*,)#si',
                '',
                $this->FilterAttributes(str_replace(array('<', '>'), '', $_match[1]))
            ),
            $_match[0]
        );
    }

    /**
     * Remove Evil HTML Attributes (like evenhandlers)
     *
     * It removes the evil attribute and either:
     *    - Everything up until a space
     *        For example, everything between the pipes:
     *        <a |style=document.write('hello');alert('world');| class=link>
     *    - Everything inside the quotes
     *        For example, everything between the pipes:
     *        <a |style="document.write('hello'); alert('world');"| class="link">
     *
     * @original CodeIgniter Security Library
     * @author   Utsav Handa
     *
     * @param string $_str The string to check
     * @param bool   $_isImage
     *
     * @return string
     */
    private function RemoveEvilAttributes($_str, $_isImage = false)
    {
        // All javascript event handlers (e.g. onload, onclick, onmouseover), and xmlns
        $_evilAttributes = array('on\w{1,}', 'xmlns');

        if ($_isImage === true) {
            /*
             * Adobe Photoshop puts XML metadata into JFIF images,
             * including namespacing, so we have to allow this for images.
             */
            unset($_evilAttributes[array_search('xmlns', $_evilAttributes)]);
        }

        do {
            $_str = preg_replace("#<(/?[^><]+?)([^A-Za-z\-])(".implode('|', $_evilAttributes).")(\s*=\s*)([\"][^>]*?[\"]|[\'][^>]*?[\']|[^>]*?)([\s><])([><]*)#i", "<$1$6$7", $_str, -1, $_count);
        } while ($_count);

        return $_str;
    }

    /**
     * HTML Entities Decode
     *
     * The reason we are not using html_entity_decode() by itself is because
     * while it is not technically correct to leave out the semicolon
     * at the end of an entity most browsers will still interpret the entity
     * correctly.  html_entity_decode() does not convert entities without
     * semicolons, so we are left with our own little solution here. Bummer.
     *
     * @original CodeIgniter Security Library
     * @author   Utsav Handa
     *
     * @param string $_str
     * @param string $_charSet
     * @return string "Sanitized String" on success, "false" on failure
     */
    private function EntityDecode($_str, $_charSet = 'UTF-8')
    {
        if (stristr($_str, '&') === false) {
            return $_str;
        }

        $_str = @html_entity_decode($_str, ENT_COMPAT, $_charSet);
        $_str = preg_replace_callback('~&#x(0*[0-9a-f]{2,5})~i', function($_matches) { return chr(hexdec($_matches[1])); }, $_str);

        //return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $_str);
        return preg_replace_callback('~&#([0-9]{2,4})~', function($_matches) {return chr($_matches[1]);}, $_str);
    }

    /**
     * HTML Entity Decode Callback
     *
     * @author Utsav Handa
     *
     * @param array $_match
     * @return string
     */
    private function DecodeEntity($_match)
    {
        return $this->EntityDecode($_match[0], DB_CHARSET);
    }
}
