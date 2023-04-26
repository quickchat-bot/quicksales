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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The CSS Parser Class
 *
 * @author Varun Shoor
 */
class SWIFT_StringCSSParser extends SWIFT_Library
{
    static protected $_blockedElements = array('a', 'div', 'p', '@font-face', 'b', 'e', 'i', 'body', 'fieldset', 'form', 'img', 'iframe', 'option', 'optgroup', 'select', 'input', 'pre', 'span', 'table', 'tbody', 'td', 'tr', 'u', 'li', 'ul');

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
     * Sanitizes the CSS <style> tags
     *
     * @author Varun Shoor
     * @param string $_text
     * @return string Processed Text
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SanitizeCSS($_text)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_returnText = preg_replace_callback('/<style[^>]*>(.*)<\/style>/msiU', 'SWIFT_StringCSSParser::StartProcess', $_text);

        return $_returnText;
    }

    /**
     * Start the processing
     *
     * @author Varun Shoor
     * @param array $_matches
     * @return string Processed Text
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function StartProcess($_matches)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_matches)) {
            return '';
        }

        $_text = $_matches[1];

        // Strip Comments
        $_finalText = preg_replace('%/\*(?:(?!\*/).)*\*/%sUmi', '', str_replace(array('<!--', '-->'), array('', ''), $_text));

        // Strip Preceding and Proceeding spaces
        $_finalText = preg_replace("#(\r\n|\r|\n)#s", '', $_finalText);
        $_finalText = preg_replace("/[\s]{2,}/s", '', $_finalText);
        $_finalText = preg_replace("/[\t]{1,}/s", ' ', $_finalText);
        $_finalText = str_replace('}', '}' . SWIFT_CRLF, $_finalText);

        // Now start the processing
        $_chunks = explode(SWIFT_CRLF, $_finalText);

        $_returnText = '';
        foreach ($_chunks as $_chunkLine) {
            $_chunkLine = trim($_chunkLine);
            if (preg_match('/^(.*)(\s+)?\{(.*)\}$/Umsi', $_chunkLine, $_matches)) {
                $_cssChunks = array();
                if (strstr($_matches[1], ',')) {
                    $_cssChunks = explode(',', $_matches[1]);
                } else {
                    $_cssChunks = array($_matches[1]);
                }

                $_finalChunks = array();
                foreach ($_cssChunks as $_cssChunk) {
                    $_cssCheckChunk = trim(mb_strtolower($_cssChunk));

                    if (strstr($_cssCheckChunk, ':')) {
                        $_cssCheckChunk = substr($_cssCheckChunk, 0, strpos($_cssCheckChunk, ':'));
                    }

                    if (in_array($_cssCheckChunk, self::$_blockedElements)) {
                        continue;
                    }

                    $_finalChunks[] = trim($_cssChunk);
                }

                if (count($_finalChunks)) {
                    $_returnText .= implode(', ', $_finalChunks) . ' { ' . $_matches[3] . ' }' . SWIFT_CRLF;
                }

            } else {
                $_returnText .= $_chunkLine . SWIFT_CRLF;
            }
        }

        return SWIFT_CRLF . '<style>' . SWIFT_CRLF . $_returnText . SWIFT_CRLF . '</style>' . SWIFT_CRLF;
    }
}
?>