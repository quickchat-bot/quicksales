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

namespace Base\Intranet;

use Base\Library\ProfileImage\SWIFT_ProfileImage;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffProfileImage;
use Controller_intranet;
use SWIFT_DataID;
use SWIFT_Exception;

/**
 * The Staff Controller
 *
 * @author Varun Shoor
 */
class Controller_StaffProfile extends Controller_intranet
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 5;

    /**
     * Constructor
     *
     * @author Varun Shoor
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
    public function GetProfileImage($_staffID)
    {
        HeaderNoCache();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_staffID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staffID));
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT_StaffProfileImageObject = SWIFT_StaffProfileImage::RetrieveOnStaff($_SWIFT_StaffObject->GetStaffID());
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

            return false;
        }

        SWIFT_ProfileImage::OutputOnStaffID($_staffID, $_emailAddressHash, $_preferredWidth, $_hasGravatarFallback);

        return true;
    }

}

?>
