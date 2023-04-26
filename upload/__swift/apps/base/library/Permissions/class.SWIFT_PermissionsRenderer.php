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

namespace Base\Library\Permissions;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Library\Staff\SWIFT_StaffPermissionContainer;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Base\Library\User\SWIFT_UserPermissionContainer;

/**
 * The Permissions Renderer Class
 *
 * @author Varun Shoor
 */
class SWIFT_PermissionsRenderer extends SWIFT_Library
{
    // Core Constants
    const PERMISSIONS_STAFF = 'staff';
    const PERMISSIONS_ADMIN = 'admin';
    const PERMISSIONS_USER = 'user';

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
     * Render the Permissions HTML
     *
     * @author Varun Shoor
     * @param mixed $_type The Permission Type
     * @param array $_permissionValueContainer The Permission Value (from database) Container
     * @param SWIFT_UserInterfaceTab $_TabObject (OPTIONAL) The Tab Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderPermissionsHTML(SWIFT_UserInterfaceControlPanel $_SWIFT_UserInterfaceObject, $_type = self::PERMISSIONS_STAFF, $_permissionValueContainer, SWIFT_UserInterfaceTab $_TabObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Create a temporary tab object if none is provided (for copy from menu items)
        $_hasTab = true;
        if (!$_TabObject) {
            $_SWIFT_UserInterfaceObject->Start(get_short_class($this), '/Base/Home/Index', SWIFT_UserInterface::MODE_INSERT, false);
            $_TabObject = $_SWIFT_UserInterfaceObject->AddTab(SWIFT_PRODUCT, 'permissions');

            $_hasTab = false;
        }

        if (isset($_COOKIE['jqCookieJar_' . $_type . 'permissions'])) {
            $_cookiePermissionContainer = @json_decode($_COOKIE['jqCookieJar_' . $_type . 'permissions'], true);
        }

        $_permissionContainer = array();
        if ($_type == self::PERMISSIONS_STAFF) {
            $_permissionContainer = SWIFT_StaffPermissionContainer::GetStaff();

            $_permissionContainer = array_merge($_permissionContainer, SWIFT_App::GetPermissionContainer('staff'));
        } elseif ($_type == self::PERMISSIONS_ADMIN) {
            $_permissionContainer = SWIFT_StaffPermissionContainer::GetAdmin();
        } elseif ($_type == self::PERMISSIONS_USER) {
            $_permissionContainer = SWIFT_UserPermissionContainer::GetDefault();

            $_permissionContainer = array_merge($_permissionContainer, SWIFT_App::GetPermissionContainer('user'));
        }

        $_permissionExtension = '';
        if ($_type == self::PERMISSIONS_ADMIN) {
            $_permissionExtension = 'admin';
        } elseif ($_type == self::PERMISSIONS_STAFF) {
            $_permissionExtension = 'staff';
        } elseif ($_type == self::PERMISSIONS_USER) {
            $_permissionExtension = 'user';
        }

        foreach ($_permissionContainer as $_key => $_val) {
            if (SWIFT_App::IsInstalled($_key)) {
                $_cookiePermission = false;
                if (isset($_cookiePermissionContainer['perm' . $_permissionExtension . '_' . $_key]) && $_cookiePermissionContainer['perm' . $_permissionExtension . '_' . $_key] == true) {
                    $_cookiePermission = true;
                }

                $_appTitle = $this->Language->Get('app_' . $_key);
                if (!$_appTitle) {
                    $_appTitle = ucfirst($_key);
                }

                $_tabHTML = '';

                $_tabHTML .= '<table width="100%" border="0" cellspacing="1" cellpadding="4">';
                $_tabHTML .= '<tr class="settabletitlerowmain2"><td class="settabletitlerowmain2" align="left" colspan="2">';
                $_tabHTML .= '<span style="float: left; margin-right: 8px;"><a href="javascript: void(0);" onclick="javascript: TogglePermissionDivUI(\'' . addslashes($_key) . '\', \'' . $_permissionExtension . '\');"><img src="' . SWIFT::Get('themepath') . 'images/' . IIF($_cookiePermission, 'icon_minus', 'icon_plus') . '.gif" align="absmiddle" border="0" id="imgplus' . $_permissionExtension . '_' . addslashes($_key) . '" /></a></span><span style="float: left;"><a href="javascript: void(0);" onclick="javascript: TogglePermissionDivUI(\'' . addslashes($_key) . '\', \'' . $_permissionExtension . '\');"><!--<img src="' . SWIFT::Get('themepath') . IIF($_cookiePermission, 'images/icon_doublearrowsdown.gif', 'images/icon_doublearrows.gif') . '" id="imgperm' . $_permissionExtension . '_' . $_key . '" align="absmiddle" border="0" /> -->' . $_appTitle . ' <font color=\'#8BB467\'>(' . count($_val) . ')</font></a></span>';
                $_tabHTML .= '</td></tr>';
                $_tabHTML .= '</table>';

                $_tabHTML .= '<div id="perm' . $_permissionExtension . '_' . $_key . '" style="display: ' . IIF($_cookiePermission, 'block', 'none') . ';">';
                $_tabHTML .= '<table width="100%" border="0" cellspacing="1" cellpadding="4">';

                $_TabObject->RowHTML($_tabHTML);

                if (_is_array($_val)) {
                    foreach ($_val as $_permissionKey => $_permissionVal) {
                        if (is_array($_permissionVal)) {
                            $_permissionGroupHTMLContainer = self::GetPermissionGroupRowHTML($_permissionValueContainer, $_permissionVal);

                            if (isset($_permissionGroupHTMLContainer[0]) && isset($_permissionGroupHTMLContainer[1])) {
                                $_TabObject->DefaultDescriptionRow($_permissionGroupHTMLContainer[0], '', $_permissionGroupHTMLContainer[1]);
                            }
                        } else {
                            if (isset($_permissionValueContainer[$_permissionVal]) && $_permissionValueContainer[$_permissionVal] == 1) {
                                $_permissionResult = true;
                            } elseif (!isset($_permissionValueContainer[$_permissionVal]) || $_permissionValueContainer[$_permissionVal] == '') {
                                $_permissionResult = true;
                            } else {
                                $_permissionResult = false;
                            }

                            $_permissionTitle = $this->Language->Get($_permissionVal);
                            if (!$_permissionTitle) {
                                $_permissionTitle = $_permissionVal;
                            }

                            $_TabObject->YesNo('perm[' . $_permissionVal . ']', $_permissionTitle, '', $_permissionResult);
                        }
                    }
                }

                $_TabObject->RowHTML('</table></div>');
            }
        }

        if (!$_hasTab) {
            echo $_TabObject->GetOutput();
        }

        return true;
    }

    /**
     * Returns the Default Value for the field based on the value in $_POST
     *
     * @author Varun Shoor
     * @param array $_permissionContainer The Permission Container
     * @param string $_fieldName The Field name
     * @return bool "true" on Success, "false" otherwise
     */
    protected static function GetFieldPostStatus($_permissionContainer, $_fieldName)
    {
        if (isset($_permissionContainer[$_fieldName]) && $_permissionContainer[$_fieldName] == '1') {
            return true;
        } elseif (!isset($_permissionContainer[$_fieldName])) {
            return true;
        } else {
            return false;
        }

        return false;
    }

    /**
     * Print the Permission Group Row (View/Insert/Update/Delete)
     *
     * @author Varun Shoor
     * @param array $_permissionContainer The Permission Container
     * @param array $_permission The Specific Permission to Check
     * @return array (Title, Contents)
     */
    protected static function GetPermissionGroupRowHTML($_permissionContainer, $_permission)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_viewValue = $_insertValue = $_updateValue = $_deleteValue = false;

        $_data = '';

        if (isset($_permission[1][SWIFT_VIEW])) {
            $_viewValue = self::GetFieldPostStatus($_permissionContainer, $_permission[1][SWIFT_VIEW]);
            $_data .= '<label for="v' . $_permission[1][SWIFT_VIEW] . '">' . '<input type="checkbox" name="perm[' . $_permission[1][SWIFT_VIEW] . ']" id="v' . $_permission[1][SWIFT_VIEW] . '" value="1"' . IIF($_viewValue, ' checked') . ' /> ' . $_SWIFT->Language->Get('view') . '</label> ' . SWIFT_CRLF;
        }

        if (isset($_permission[1][SWIFT_MANAGE])) {
            $_viewValue = self::GetFieldPostStatus($_permissionContainer, $_permission[1][SWIFT_MANAGE]);
            $_data .= '<label for="v' . $_permission[1][SWIFT_MANAGE] . '">' . '<input type="checkbox" name="perm[' . $_permission[1][SWIFT_MANAGE] . ']" id="v' . $_permission[1][SWIFT_MANAGE] . '" value="1"' . IIF($_viewValue, ' checked') . ' /> ' . $_SWIFT->Language->Get('manage') . '</label> ' . SWIFT_CRLF;
        }

        if (isset($_permission[1][SWIFT_INSERT])) {
            $_insertValue = self::GetFieldPostStatus($_permissionContainer, $_permission[1][SWIFT_INSERT]);
            $_data .= '<label for="i' . $_permission[1][SWIFT_INSERT] . '">' . '<input type="checkbox" name="perm[' . $_permission[1][SWIFT_INSERT] . ']" id="i' . $_permission[1][SWIFT_INSERT] . '" value="1"' . IIF($_insertValue, ' checked') . ' /> ' . $_SWIFT->Language->Get('insert') . '</label> ' . SWIFT_CRLF;
        }

        if (isset($_permission[1][SWIFT_UPDATE])) {
            $_updateValue = self::GetFieldPostStatus($_permissionContainer, $_permission[1][SWIFT_UPDATE]);
            $_data .= '<label for="u' . $_permission[1][SWIFT_UPDATE] . '">' . '<input type="checkbox" name="perm[' . $_permission[1][SWIFT_UPDATE] . ']" id="u' . $_permission[1][SWIFT_UPDATE] . '" value="1"' . IIF($_updateValue, ' checked') . ' /> ' . $_SWIFT->Language->Get('update') . '</label> ' . SWIFT_CRLF;
        }

        if (isset($_permission[1][SWIFT_DELETE])) {
            $_deleteValue = self::GetFieldPostStatus($_permissionContainer, $_permission[1][SWIFT_DELETE]);
            $_data .= '<label for="d' . $_permission[1][SWIFT_DELETE] . '">' . '<input type="checkbox" name="perm[' . $_permission[1][SWIFT_DELETE] . ']" id="d' . $_permission[1][SWIFT_DELETE] . '" value="1"' . IIF($_deleteValue, ' checked') . ' /> ' . $_SWIFT->Language->Get('delete') . '</label> ' . SWIFT_CRLF;
        }

        if (isset($_permission[1][SWIFT_IMPORT])) {
            $_viewValue = self::GetFieldPostStatus($_permissionContainer, $_permission[1][SWIFT_IMPORT]);
            $_data .= '<label for="v' . $_permission[1][SWIFT_IMPORT] . '">' . '<input type="checkbox" name="perm[' . $_permission[1][SWIFT_IMPORT] . ']" id="v' . $_permission[1][SWIFT_IMPORT] . '" value="1"' . IIF($_viewValue, ' checked') . ' /> ' . $_SWIFT->Language->Get('import') . '</label> ' . SWIFT_CRLF;
        }

        if (isset($_permission[1][SWIFT_EXPORT])) {
            $_viewValue = self::GetFieldPostStatus($_permissionContainer, $_permission[1][SWIFT_EXPORT]);
            $_data .= '<label for="v' . $_permission[1][SWIFT_EXPORT] . '">' . '<input type="checkbox" name="perm[' . $_permission[1][SWIFT_EXPORT] . ']" id="v' . $_permission[1][SWIFT_EXPORT] . '" value="1"' . IIF($_viewValue, ' checked') . ' /> ' . $_SWIFT->Language->Get('export') . '</label> ' . SWIFT_CRLF;
        }

        $_infoContainer = $_SWIFT->Language->Get($_permission[0]);
        if (!$_infoContainer) {
            $_infoContainer = $_permission[0];
        }

        return array($_infoContainer, $_data);
    }
}

?>
