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

namespace Base\Library\Tag;

use Base\Library\Tag\SWIFT_Tag_Exception;
use SWIFT_Library;

/**
 * The Tag Cloud Renderer
 *
 * @author Varun Shoor
 */
class SWIFT_TagCloud extends SWIFT_Library
{
    private $_tagLinkURL = false;
    private $_tagLinkJavaScript = false;

    private $_tagContainer = array();

    // Core Constants
    const MIN_SIZE = '11';
    const MAX_SIZE = '18';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_tagContainer, $_tagLinkURL = false, $_tagLinkJavaScript = false)
    {
        parent::__construct();

        if (!$_tagLinkURL) {
            $_tagLinkURL = 'javascript: void(0);';
        }

        if (!$_tagLinkJavaScript) {
            $_tagLinkJavaScript = 'javascript: void(0);';
        }

        $this->_tagContainer = $_tagContainer;
        $this->_tagLinkURL = $_tagLinkURL;
        $this->_tagLinkJavaScript = $_tagLinkJavaScript;
    }

    /**
     * Render the Tag Cloud
     *
     * @author Varun Shoor
     * @return mixed "_renderHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Tag_Exception If the Class is not Loaded
     */
    public function Render()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Tag_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tagContainer = $this->_tagContainer;
        arsort($_tagContainer);

        if (!_is_array($_tagContainer)) {
            return '';
        }

        // largest and smallest array values
        $_maxQuantity = max(array_values($_tagContainer));
        $_minQuantity = min(array_values($_tagContainer));

        // find the range of values
        $_spread = $_maxQuantity - $_minQuantity;
        if ($_spread == 0) { // we don't want to divide by zero
            $_spread = 1;
        }

        // set the font-size increment
        $_step = (self::MAX_SIZE - self::MIN_SIZE) / ($_spread);

        $_renderHTML = '<div class="renavsection" id="itemoptionsnav"><div class="navsub"><div class="navtitle">' . $this->Language->Get('tagcloud') . '</div><div id="tagcloudcontainer" style="word-wrap: break-word; width: 170px;">';

        // loop through the tag array
        foreach ($_tagContainer as $_key => $_val) {
            // calculate font-size
            // find the $value in excess of $min_qty
            // multiply by the font-size increment ($size)
            // and add the $min_size set above
            $_fontSize = round(self::MIN_SIZE + (($_val - $_minQuantity) * $_step));

            $_renderHTML .= '<a href="' . sprintf($this->_tagLinkURL, mb_strtolower($_key)) . '" onclick="' . sprintf($this->_tagLinkJavaScript, mb_strtolower($_key)) . '" style="font-size: ' . $_fontSize . 'px" title="#' . (int)($_val) . ' ' . addslashes($_key) . '">' . htmlspecialchars($_key) . ' (' . (int)($_val) . ')' . '</a> ';
        }

        $_renderHTML .= '</div></div></div>';

        return $_renderHTML;
    }
}

?>
