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

namespace LiveChat\Winapp;

use Base\Models\Staff\SWIFT_StaffProfileImage;
use Controller_winapp;
use SWIFT;
use SWIFT_Exception;

/**
 * The Profile (Avatar & Status) Management Controller
 *
 * @author Varun Shoor
 */
class Controller_Profile extends Controller_winapp
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('livesupport');

        $this->Load->Library('Chat:ChatEventWinapp', [], true, false, APP_LIVECHAT);
    }

    /**
     * Update the Staff Status Message
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Status()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($_POST['msg'])) {
            $this->_DispatchError(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT->Staff->UpdateStatusMessage(urldecode($_POST['msg']));

        $this->_DispatchConfirmation();

        return true;
    }

    /**
     * Update the Staff Avatar
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Avatar()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($_POST['data']) || !isset($_POST['type']) || empty($_POST['type']) || empty($_POST['data']) || ($_POST['type'] != 'png' && $_POST['type'] != 'jpg' && $_POST['type'] != 'jpeg' && $_POST['type'] != 'gif')) {
            $this->_DispatchError(SWIFT_INVALIDDATA);

            return false;
        }

        SWIFT_StaffProfileImage::DeleteOnStaff(array($_SWIFT->Staff->GetStaffID()), SWIFT_StaffProfileImage::TYPE_PRIVATE);

        SWIFT_StaffProfileImage::Create($_SWIFT->Staff->GetStaffID(), SWIFT_StaffProfileImage::TYPE_PRIVATE, $_POST['type'], $_POST['data']);

        $this->_DispatchConfirmation();

        return true;
    }
}
