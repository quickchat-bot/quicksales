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

namespace Base\Library\HTML;

use BeautifulHTMLTicketPosts;
use SWIFT_Library;
use tidy;

require_once('.' . DIRECTORY_SEPARATOR . SWIFT_BASEDIRECTORY . DIRECTORY_SEPARATOR . SWIFT_THIRDPARTYDIRECTORY . DIRECTORY_SEPARATOR . 'BeautifulHTMLTicketPosts' . DIRECTORY_SEPARATOR . 'BeautifulHTMLTicketPosts.php');

/**
 * HTML Library
 *
 * @property object $Tidy
 * @author Utsav Handa
 */
class SWIFT_HTML extends SWIFT_Library
{
    private $Tidy = false;
    private $TidyConfig = array(
        'show-body-only' => false, 'clean' => false, 'char-encoding' => 'utf8', 'add-xml-decl' => true, 'add-xml-space' => false, 'output-html' => false, 'output-xml' => false, 'output-xhtml' => true,
        'numeric-entities' => false, 'ascii-chars' => false, 'doctype' => 'strict', 'bare' => false, 'fix-uri' => true, 'indent' => false, 'indent-spaces' => 4, 'tab-size' => 4, 'wrap-attributes' => false,
        'wrap' => 0, 'indent-attributes' => false, 'join-classes' => false, 'join-styles' => false, 'enclose-block-text' => false, 'fix-bad-comments' => true, 'fix-backslash' => true, 'replace-color' => false,
        'wrap-asp' => false, 'wrap-jste' => false, 'wrap-php' => false, 'write-back' => true, 'drop-proprietary-attributes' => false, 'hide-comments' => false, 'hide-endtags' => false, 'show-errors' => false,
        'literal-attributes' => true, 'drop-empty-paras' => true, 'enclose-text' => false, 'quote-ampersand' => true, 'quote-marks' => false, 'quote-nbsp' => true, 'vertical-space' => false, 'force-output' => true,
        'wrap-script-literals' => false, 'tidy-mark' => false, 'merge-divs' => false, 'repeated-attributes' => 'keep-last', 'break-before-br' => false, 'input-xml' => false, 'show-warnings' => false,
        'wrap-sections' => false
    );


    /**
     * Constructor
     *
     * @author Utsav Handa
     */
    public function __construct()
    {
        parent::__construct();

        // Tidy
        if (class_exists('tidy')) {
            $className = 'tidy';
            $this->Tidy = new $className();
        }
    }

    /**
     * @author Utsav Handa
     *
     * @param string $_contents
     * @param string $_encoding
     * @param array $_config
     *
     * @return string
     */
    public function Tidy($_contents, $_encoding = 'UTF8', $_config = array())
    {
        if (method_exists($this->Tidy, 'repairString')) {
            $_contents = $this->Tidy->repairString($_contents, IIF(!_is_array($_config), $this->TidyConfig, $_config), $_encoding);
        }

        return $_contents;
    }

    /**
     * Prepares the HTML contents for markup validation, including unclosed tags
     *
     * @author Utsav Handa
     *
     * @param string $_contents
     * @param bool $_isHTML
     *
     * @return string
     */
    public function Beautify($_contents, $_isHTML)
    {
        // Tidy
        if (class_exists('tidy')) {
            // Tidy pretty prints the output. Conserving original line-breaks
            $_breakLineReplacement = '__NEWLINE' . GenerateUUID() . '__';

            return str_replace($_breakLineReplacement, SWIFT_CRLF, preg_replace("/" . PHP_EOL . "+/", '', $this->Tidy(str_replace(array("\r\n", "\n\r", "\n", "\r"), $_breakLineReplacement, $_contents))));
        }

        // Tidy - Custom
        $_contents = $this->TidyCustom($_contents, $_isHTML);

        return $_contents;
    }

    /**
     * Repair HTML content using a custom strategy
     * @author Utsav Handa
     *
     * @param string $_contents
     * @param bool $_isHTML
     *
     * @return string
     */
    public function TidyCustom($_contents, $_isHTML)
    {
        // ThirdParty library
        return BeautifulHTMLTicketPosts::Beautify($_contents, $_isHTML);
    }

    /**
     * Detects content-type by matching against any HTML/PHP tags stripping
     *
     * @author Utsav Handa
     *
     * @param string $_contents
     *
     * @return bool
     */
    public static function DetectHTMLContent($_contents)
    {
        return preg_match("/<(\s+)?(a|abbr|acronym|address|applet|area|base|basefont|big|blockquote|body|br|b|button|caption|center|cite|code|col|dfn|div|dl|dt|dd|em|font|form|frameset|frame|fieldset|h1|h2|h3|h4|h5|h6|head|hr|html|img|input|isindex|i|kbd|link|li|label|map|menu|meta|ol|option|optgroup|object|param|pre|p|q| samp|script|select|small|span|strikeout|strong|style|sub|sup|table|td|textarea|th|tbody|thead|tfoot|title|tr|tt|ul|u|var)([\s]+(.*)|[\s]*\/)?(\s+)?>/i", html_entity_decode($_contents));
    }

    /**
     * @param string $_contents
     * @return bool
     */
    public static function DetectHTMLEntities($_contents)
    {
        return strcmp(trim(html_entity_decode($_contents)), trim($_contents)) !== 0;
    }

    /**
     * Inserts HTML line breaks before all newlines in a string per HTML markup
     *
     * @author Utsav Handa
     *
     * @param string $_contents
     * @param bool $_isHTML
     *
     * @return string
     */
    public static function HTMLBreaklines($_contents, $_isHTML)
    {
        if ($_isHTML) {
            // Pure HTML content?
            if (trim(strip_tags(preg_replace("/<([a-z]+)>.*?<\/\\1>/is", "", $_contents))) == '') {
                return $_contents;
            }

            // Remove breaklines within HTML tags
            $_contents = preg_replace_callback('/(<([^>]*)>)/', function ($match) {
                return str_replace(array("\r\n", "\n\r", "\n", "\r"), '', $match[0]);
            }, $_contents);

            // Avoid additional breaklines with newline
            $_replacedContents = preg_replace("/(\<(BR|br)\s*\/{0,1}\s*\>(\r\n|\n\r|\n|\r){1,})|((\r\n||\n\r|\n\r){1,}\<(BR|br)\s*\/{0,1}\s*\>)/", '<br />', $_contents);
            if (preg_last_error() == PREG_NO_ERROR) {
                $_contents = $_replacedContents;
            }
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-3688 HTML tags are not being rendered, even if defined under ticket settings
         *
         * Comments: Handling <a <br> href="example.com"> problem.
         */
        $_contents = self::RemoveNewlineSpacesfromAttrs($_contents);

        return $_isHTML ? $_contents : nl2br($_contents);
    }

    /**
     * Removing new line spaces from attributes
     *
     * @author Ravi Sharma <ravi.sharma@opencart.com.vn>
     *
     * @param string $_content
     *
     * @return string well formed html
     */
    public static function RemoveNewlineSpacesfromAttrs($_content)
    {
        return preg_replace_callback('/<([^>]*)>/s', function ($match) {
            return str_replace("\n", '', $match[0]);
        }, $_content);
    }

    /**
     * Detect body and br tags from content
     *
     * @author Ravi Sharma <ravi.sharma@opencart.com.vn>
     *
     * @param string $_content
     *
     * @return bool
     */
    public static function DetectBodyAndBR($_content)
    {
        return preg_match("/<(\s+)?(br|body|)([\s]+(.*)|[\s]*\/)?(\s+)?>/i", $_content);
    }
}
