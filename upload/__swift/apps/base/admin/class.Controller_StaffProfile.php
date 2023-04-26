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

namespace Base\Admin;

use Controller_admin;
use SWIFT_DataID;
use SWIFT_Exception;
use Base\Library\ProfileImage\SWIFT_ProfileImage;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffProfileImage;

/**
 * The Staff Controller
 *
 * @author Varun Shoor
 */
class Controller_StaffProfile extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Staff:StaffPasswordPolicy', [], true, false, 'base');

        $this->Language->Load('staff_preferences');
    }


    /**
     * Retrieve the Profile Image
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProfileImage($_staffID = 0)
    {
        HeaderNoCache();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_staffID)) {
            return false;
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4364 Security Issue.
         *
         * Comments: Suppressing the exceptions with try statement.
         */
        try {
            $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID));
            $_SWIFT_StaffProfileImageObject = SWIFT_StaffProfileImage::RetrieveOnStaff($_SWIFT_StaffObject->GetStaffID());
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if (!$_SWIFT_StaffProfileImageObject instanceof SWIFT_StaffProfileImage || !$_SWIFT_StaffProfileImageObject->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT_StaffProfileImageObject->Output();

        return true;
    }

    /**
     * Display the Avatar
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param string $_emailAddressHash (OPTIONAL) The Email Address Hash
     * @param int $_preferredWidth (OPTIONAL) The Preferred Width
     * @param bool $_hasGravatarFallback (OPTIONAL) Whether to fallback to Gravatar
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DisplayAvatar($_staffID, $_emailAddressHash = '', $_preferredWidth = 60, $_hasGravatarFallback = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT_ProfileImage::OutputOnStaffID($_staffID, $_emailAddressHash, $_preferredWidth, $_hasGravatarFallback);

        return true;
    }

}

?>
