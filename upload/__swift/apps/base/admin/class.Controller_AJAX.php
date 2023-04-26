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

namespace Base\Admin;

use Controller_admin;
use SWIFT_Exception;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;

/**
 * The AJAX Controller
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class Controller_AJAX extends Controller_admin
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
     * Dispatch Online Staff JSON
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function OnlineStaff()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->ProcessOnlineStaff();
        $_onlineStaffContainer = $this->UserInterface->GetOnlineStaffContainer();

        $_onlineStaffHTML = SWIFT_UserInterfaceControlPanel::RenderOnlineStaff();

        header('Content-Type: text/plain');

        echo '{' . SWIFT_CRLF;
        echo '"onlineusershtml": "' . addslashes($_onlineStaffHTML) . '",' . SWIFT_CRLF;
        echo '"onlineusersarray": {' . SWIFT_CRLF;

        $_index = 1;
        $_jsonStaffContainer = array();
        foreach ($_onlineStaffContainer as $_key => $_val) {
            $_jsonStaffContainer[] = '"' . $_val['staffid'] . '"' . ': {"fullname": "' . $_val['fullname'] . '"}';
            $_index++;
        }

        echo implode(', ', $_jsonStaffContainer);

        echo '} }';

        return true;
    }
}

?>
