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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\Attachment;

use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use SWIFT_MIME_Exception;
use SWIFT_MIMEList;

/**
 * The Attachment Renderer Class
 *
 * @author Varun Shoor
 */
class SWIFT_AttachmentRenderer extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieve the Parsed Attachment Container on Link
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param array $_linkTypeIDList The Link Type ID List
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnLink($_linkType, $_linkTypeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!SWIFT_Attachment::IsValidLinkType($_linkType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_attachmentContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "attachments WHERE linktype = '" . (int)($_linkType) . "' AND linktypeid IN (" . BuildIN($_linkTypeIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_mimeDataContainer = array();
            try {
                $_fileExtension = mb_strtolower(substr($_SWIFT->Database->Record['filename'], (strrpos($_SWIFT->Database->Record['filename'], '.') + 1)));

                $_MIMEListObject = new SWIFT_MIMEList();
                $_mimeDataContainer = $_MIMEListObject->Get($_fileExtension);
            } catch (SWIFT_MIME_Exception $_SWIFT_MIME_ExceptionObject) {
                // Do nothing
            }

            $_attachmentIcon = 'icon_file.gif';
            if (isset($_mimeDataContainer[1])) {
                $_attachmentIcon = $_mimeDataContainer[1];
            }

            $_attachmentContainer[$_SWIFT->Database->Record['attachmentid']] = $_SWIFT->Database->Record;
            $_attachmentContainer[$_SWIFT->Database->Record['attachmentid']]['icon'] = $_attachmentIcon;
            $_attachmentContainer[$_SWIFT->Database->Record['attachmentid']]['name'] = htmlspecialchars($_SWIFT->Database->Record['filename']);
            $_attachmentContainer[$_SWIFT->Database->Record['attachmentid']]['size'] = FormattedSize($_SWIFT->Database->Record['filesize']);
        }

        return $_attachmentContainer;
    }

    /**
     * Render the Checkbox List
     *
     * @author Varun Shoor
     * @param mixed $_linkType The Link Type
     * @param array $_linkTypeIDList The Link Type ID List
     * @return string
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RenderCheckbox($_linkType, $_linkTypeIDList, $_checkBoxName)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_attachmentContainer = self::RetrieveOnLink($_linkType, $_linkTypeIDList);

        $_renderHTML = '';

        /*
         * New Feature - Bishwanath Jha
         *
         * SWIFT-1982: "Select / Deselect all" in attachments list when forwarding a ticket
         *
         * Comments: Adding here additional checkbox to Select/DeSelect All
         */
        if (_is_array($_attachmentContainer) && count($_attachmentContainer) > 1) {
            $_renderHTML .= '<span><input id="' . $_checkBoxName . '" type="checkbox" checked="checked" class="attachmentparent-checkbox" name="allselect" autocomplete="OFF"/>&nbsp;' .
                '<label for="' . $_checkBoxName . '">All</label></span>&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        foreach ($_attachmentContainer as $_attachmentID => $_attachment) {
            $_renderHTML .= '<span><input id="' . $_checkBoxName . '_' . $_attachmentID . '" type="checkbox" name="' . $_checkBoxName . '[]" checked="checked"  class="attachmentchild-checkbox" value="' . $_attachmentID . '" /> ' .
                '<label for="' . $_checkBoxName . '_' . $_attachmentID . '"><img src="' . SWIFT::Get('themepathimages') . $_attachment['icon'] . '" align="absmiddle" border="0" /> ' . $_attachment['name'] . ' (' . $_attachment['size'] . ')</label></span>&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        return $_renderHTML;
    }
}

?>
