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
 * The String Highlighter Class
 *
 * @author Varun Shoor
 */
class SWIFT_StringHighlighter extends SWIFT_Library
{
    /**
     * Perform a simple text replace
     * This should be used when the string does not contain HTML
     * (off by default)
     */
    const HIGHLIGHT_SIMPLE = 1;

    /**
     * Only match whole words in the string
     * (off by default)
     */
    const HIGHLIGHT_WHOLEWD = 2;

    /**
     * Case sensitive matching
     * (off by default)
     */
    const HIGHLIGHT_CASESENS = 4;

    /**
     * Overwrite links if matched
     * This should be used when the replacement string is a link
     * (off by default)
     */
    const HIGHLIGHT_STRIPLINKS = 8;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Highlight a string in text without corrupting HTML tags
     *
     * @author Aidan Lister <aidan@php.net>
     * @param string $_hayStack Haystack - The text to search
     * @param array|string $_needle Needle - The string to highlight
     * @param bool|int $_optionsContainer Bitwise set of options
     * @param array|string $_highlightString Replacement string
     * @return string with needle highlighted
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Highlight($_hayStack, $_needle, $_optionsContainer = null, $_highlightString = null)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Default highlighting
        if ($_highlightString === null)
        {
            $_highlightString = '<strong>\1</strong>';
        }

        // Select pattern to use
        if ($_optionsContainer & self::HIGHLIGHT_SIMPLE)
        {
            $_defaultPattern = '#(%s)#';
            $_stripLinksPattern = '#(%s)#';
        } else {
            $_defaultPattern = '#(?!<.*?)(%s)(?![^<>]*?>)#';
            $_stripLinksPattern = '#<a\s(?:.*?)>(%s)</a>#';
        }

        // Case sensitivity
        if (!($_optionsContainer & self::HIGHLIGHT_CASESENS)) {
            $_defaultPattern .= 'i';
            $_stripLinksPattern .= 'i';
        }

        $_needle = (array)$_needle;
        foreach ($_needle as $_needleString) {
            $_needleString = preg_quote($_needleString);

            // Escape needle with optional whole word check
            if ($_optionsContainer & self::HIGHLIGHT_WHOLEWD) {
                $_needleString = '\b' . $_needleString . '\b';
            }

            /*
             * BUG FIX Ravinder Singh
             *
             * SWIFT-2888 Error logged on usage of # in Unified Search
             *
             * Comments: The pattern is #regex#, so escape any '#' characters in regex
             */
            $_needleString = str_replace('#', '\#', $_needleString);

            // Strip links
            if ($_optionsContainer & self::HIGHLIGHT_STRIPLINKS) {
                $_stripLinksRegExp = sprintf($_stripLinksPattern, $_needleString);
                $_hayStack = preg_replace($_stripLinksRegExp, '\1', $_hayStack);
            }

            $_regularExpression = sprintf($_defaultPattern, $_needleString);

            $_hayStack = preg_replace($_regularExpression, $_highlightString, $_hayStack);
        }

        return $_hayStack;
    }

    /**
     * Retrieve Highlighted Text according to different ranges
     *
     * @author Varun Shoor
     * @param string $_hayStack The Hay Stack to Search In
     * @param string $_needle The Search Needle
     * @param int $_range The Max Range of Text
     * @return mixed "_resultContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetHighlightedRange($_hayStack, string $_needle, int $_range)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_offset = 0;
        $_hayStackLength = strlen($_hayStack);
        $_needleLength = strlen($_needle);

        $_resultContainer = array();

        $_maxCount = 3;

//        echo 'Haystack: ' . $_hayStack . SWIFT_CRLF . 'Needle: ' . $_needle . SWIFT_CRLF;

        $_recordCount = 1;
        do {
            if ($_offset > $_hayStackLength)
            {
                break;
            }

            $_position = stripos($_hayStack, $_needle, $_offset);
//            echo 'POSITION: ' . $_position . SWIFT_CRLF . 'OFFSET: ' . $_offset . SWIFT_CRLF;
            if (!$_position)
            {
                break;
            }

            $_offset += $_position + strlen($_needle);

            if (strlen($_hayStack) <= $_range) {
                $_resultString = substr($_hayStack, ($_position - $_range), ($_range + $_needleLength));
            } else {
                $_resultString = substr($_hayStack, ($_position - $_range), ($_range + $_needleLength)) . substr($_hayStack, ($_position + $_needleLength), $_range);
            }

            $_resultContainer[] = $this->Highlight(htmlspecialchars($_resultString), $_needle, self::HIGHLIGHT_SIMPLE, '<span class="searchighlightcode">\1</span>');

            if ($_recordCount >= $_maxCount) {
                break;
            }

            $_recordCount++;
        } while ($_position);

        return $_resultContainer;
    }
}
?>
