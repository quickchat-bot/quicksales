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

namespace Base\Library\UserInterface;

use Base\Models\User\SWIFT_UserOrganization;
use SWIFT;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Library;

/**
 * The User Interface Management Object
 *
 * @method bool Header(...$arg)
 * @method bool Footer()
 * @method bool DisplayError(...$args)
 * @method bool ProcessOnlineStaff()
 * @method bool ProcessDialogs()
 * @method mixed GetFooterScript()
 * @method mixed GetOnlineStaffContainer()
 * @method bool Start(...$args)
 * @method SWIFT_UserInterfaceTab AddTab(...$args)
 * @method bool Hidden(...$args)
 * @method void End()
 * @method bool SetDialogOptions($_saveButton = true)
 * @method bool AddNavigationBox(...$args)
 *
 * @author Varun Shoor
 */
class SWIFT_UserInterface extends SWIFT_Library
{
    protected $_interfaceStarted = false;
    public $Toolbar;

    // Core Constants
    const FORM_SUFFIX = 'form';
    const MODE_EDIT = 1;
    const MODE_INSERT = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1');
        header('X-Content-Type-Options: nosniff');
    }

    /**
     * Check to see if the request was sent via AJAX
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function IsAjax()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if ((isset($_POST['isajax']) && $_POST['isajax'] == 1) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
            return true;
        }

        return false;
    }

    /**
     * Log an Error
     *
     * @author Varun Shoor
     * @param string|bool $_title The Title
     * @param string $_message The Message
     * @return bool "true" on Success, "false" otherwise
     */
    public function Error($_title, $_message, $_divID = '')
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        SWIFT::Error($_title, $_message);

        return true;
    }

    /**
     * Log an Alert
     *
     * @author Varun Shoor
     * @param string $_title The Title
     * @param string $_message The Message
     * @return bool "true" on Success, "false" otherwise
     */
    public function Alert($_title, $_message, $_divID = '')
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        SWIFT::Alert($_title, $_message);

        return true;
    }

    /**
     * Log Confirmation
     *
     * @author Varun Shoor
     * @param string|bool $_title The Title
     * @param string $_message The Message
     * @return bool "true" on Success, "false" otherwise
     */
    public function Info($_title, $_message, $_divID = '')
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        SWIFT::Info($_title, $_message);

        return true;
    }

    /**
     * Processes empty fields and tags them into SWIFT::ErrorField()
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function CheckFields()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldList = func_get_args();

        if (!_is_array($_fieldList)) {
            return false;
        }
        foreach ($_fieldList as $_key => $_val) {
            if (!isset($_POST[$_val]) || (isset($_POST[$_val]) && !is_array($_POST[$_val]) && trim($_POST[$_val]) == '') ||
                (isset($_POST[$_val]) && is_array($_POST[$_val]) && !count($_POST[$_val]))) {
                SWIFT::ErrorField($_val);
            }
        }

        return true;
    }

    /**
     * Retrieve values of Multi Input text/tag box
     *
     * @author Varun Shoor
     * @param string $_fieldName The Field Name
     * @return mixed "_fieldValues" (ARRAY) on Success, "false" otherwise
     */
    public static function GetMultipleInputValues($_fieldName, $_isCheckbox = false, $_isEmail = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_mainFieldName = 'taginput_' . $_fieldName;
        $_fieldContainerName = 'container' . $_mainFieldName;
        if ($_isCheckbox) {
            $_fieldContainerName = 'taginputcheck_' . $_fieldName;
        }


        if (!isset($_POST[$_mainFieldName])) {
            return false;
        }

        $_fieldValues = array();

        if (isset($_POST[$_fieldContainerName]) && _is_array($_POST[$_fieldContainerName])) {
            $_fieldValues = $_POST[$_fieldContainerName];
        }

        // add the single value to the end of the list
        if (isset($_POST[$_mainFieldName]) && !empty($_POST[$_mainFieldName]) && $_POST[$_mainFieldName] != $_SWIFT->Language->Get('starttypingtags')) {

            /*
             * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
             *
             * SWIFT-2416 Special characters are filtered out, while specifying email address (with special characters) in the 'To' field..
             */
            if ($_isEmail) {
                $_fieldValues[] = CleanEmail($_POST[$_mainFieldName]);
            } else {
                $_fieldValues[] = CleanTag($_POST[$_mainFieldName], SWIFT_UserOrganization::ALLOWED_CHARACTERS);
            }
        }

        return $_fieldValues;
    }

    /**
     * Retrieve the icon, if a new one is uploaded.. pass it through file manager and return the relevant new URL to it
     *
     * @author Varun Shoor
     * @param string $_fieldName The Field Name
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public static function GetIconURL($_fieldName)
    {
        // We always give priority to the uploaded file..
        $_uploadedFieldName = 'file_' . $_fieldName;
        if (isset($_FILES[$_uploadedFieldName]) && isset($_FILES[$_uploadedFieldName]['tmp_name']) && is_uploaded_file($_FILES[$_uploadedFieldName]['tmp_name'])) {
            $_fileID = SWIFT_FileManager::Create($_FILES[$_uploadedFieldName]['tmp_name'], $_FILES[$_uploadedFieldName]['name']);
            if (!empty($_fileID)) {
                $_SWIFT_FileManagerObject = new SWIFT_FileManager($_fileID);
                if ($_SWIFT_FileManagerObject->GetIsClassLoaded()) {
                    return $_SWIFT_FileManagerObject->GetURL();
                }
            }
        }

        if (!isset($_POST['url_' . $_fieldName])) {
            return '';
        }

        return $_POST['url_' . $_fieldName];
    }
}

?>
