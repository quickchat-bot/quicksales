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

namespace Base\Client;

use Base\Library\ProfileImage\SWIFT_ProfileImage;
use Controller_client;
use SWIFT_Exception;

/**
 * The Staff related functions
 *
 * @author Varun Shoor
 */
class Controller_StaffProfile extends Controller_client
{
    /**
     * Display the Avatar
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param string $_emailAddressHash (OPTIONAL) The Email Address Hash
     * @param int $_preferredWidth (OPTIONAL) The Preferred Width
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DisplayAvatar($_staffID = 0, $_emailAddressHash = '', $_preferredWidth = 60)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        /* BUG FIX : Parminder Singh
         *
         * SWIFT-3238  Uncaught Exception: Invalid data provided in ./__swift/library/Image/class.SWIFT_ImageResize.php:371
         *
         * Comments : None
         */

        if (empty($_preferredWidth)) {
            $_preferredWidth = 60;
        }

        SWIFT_ProfileImage::OutputOnStaffID($_staffID, $_emailAddressHash, $_preferredWidth);

        return true;
    }
}

?>
