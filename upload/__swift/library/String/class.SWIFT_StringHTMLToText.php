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
 * Convert HTML to Text
 *
 * @author Varun Shoor
 */
class SWIFT_StringHTMLToText extends SWIFT_Library
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
     * Convert HTML text to Plain Text
     *
     * @author Varun Shoor
     * @param string $_htmlContents The HTML Contents
     * @param bool $_wordWrap Whether or not to enable word-wrapping (default = 70 columns).
     * @param int $_wordwrapLength The Wordwrap Length
     * @return string The converted string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Convert($_htmlContents, $_wordWrap = true, $_wordwrapLength = 70)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /* Bug FIX : Saloni Dhall
         *
         * SWIFT-4369 : HTML2Text Library - unknown property 'width' handling
         *
         * Comments : Passing the argument width through object constructor, setting directly properties does not work.
         */
        $_WidthContainer = array();

        if (!$_wordWrap) {
            $_WidthContainer['width'] = 0; // Disables word-wrapping
        } else {
            $_WidthContainer['width'] = $_wordwrapLength;
        }

        $_HTMLToTextObject = new Html2Text\Html2Text($_htmlContents, $_WidthContainer);

        return $_HTMLToTextObject->getText();
    }
}
?>