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

namespace Base\Library\ProfileImage;

use Base\Models\Staff\SWIFT_StaffProfileImage;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserProfileImage;
use SWIFT;
use SWIFT_Exception;
use SWIFT_ImageResize;
use SWIFT_Library;

/**
 * The Profile Image Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_ProfileImage extends SWIFT_Library
{
    const TYPE_USER = 1;
    const TYPE_STAFF = 2;

    const CACHE_PREFIX = 'avatar_';

    const EXTENSION_PNG = 'png';
    const EXTENSION_JPEG = 'jpeg';
    const EXTENSION_GIF = 'gif';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param mixed $_imageType The Image Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function __construct($_imageType)
    {
        parent::__construct();

        if (!self::IsValidType($_imageType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * Check to see if its a valid type
     *
     * @author Varun Shoor
     * @param mixed $_imageType The Image Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_imageType)
    {
        if ($_imageType == self::TYPE_STAFF || $_imageType == self::TYPE_USER) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the Image from Gravatar
     *
     * @author Varun Shoor
     * @param string $_emailAddressHash The Email Address MD5 Hash
     * @return string The Gravatar Image Contents
     */
    public static function RetrieveFromGravatar($_emailAddressHash)
    {
        $_SWIFT = SWIFT::GetInstance();
        // $_defaultImageUrl = urlencode('http://localhost/SWIFT/trunk/__swift/themes/client/images/icon_defaultavatar.gif');
        $_gravatarImageContents = @file_get_contents('http://www.gravatar.com/avatar/' . $_emailAddressHash . '.png?s=100&d=404');
        if (!$_gravatarImageContents) {
            return '';
        }

        return $_gravatarImageContents;
    }

    /**
     * Attempt to retrieve the image from cache
     *
     * @author Varun Shoor
     * @param mixed $_imageType The Image Type
     * @param int $_creatorID The Creator ID
     * @param string $_emailAddress The Email Address
     * @param int $_preferredWidth The Image Width
     * @return mixed array(extension, file path) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveFromCache($_imageType, $_creatorID, $_emailAddress, $_preferredWidth)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_imageType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_imageFilename = self::CACHE_PREFIX . md5($_imageType . $_creatorID . $_emailAddress) . '_' . $_preferredWidth;

        foreach (array(self::EXTENSION_GIF, self::EXTENSION_JPEG, self::EXTENSION_PNG) as $_extension) {
            $_finalFilePath = './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . $_imageFilename . '.' . $_extension;
            if (file_exists($_finalFilePath)) {
                return array($_extension, $_finalFilePath);
            }
        }

        return false;
    }

    /**
     * Update the Image Cache
     *
     * @author Varun Shoor
     * @param string $_fileName The File Name
     * @param string $_fileContents The File Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateImageCache($_fileName, $_fileContents)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_fileName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        file_put_contents('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . $_fileName, $_fileContents);

        return true;
    }

    /**
     * Retrieve the Profile image from a Staff ID
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID
     * @param string $_emailAddressHash (OPTIONAL) The Replacement Email Address
     * @param int $_preferredWidth (OPTIONAL) The Preferred Width
     * @param bool $_hasGravatarFallback (OPTIONAL) Whether to fallback to Gravatar
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function OutputOnStaffID($_staffID, $_emailAddressHash = '', $_preferredWidth = 100, $_hasGravatarFallback = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        if (isset($_staffCache[$_staffID])) {
            $_emailAddressHash = md5($_staffCache[$_staffID]['email']);
        }

        // Do we have a cache for this?
        $_imageCacheContainer = self::RetrieveFromCache(self::TYPE_STAFF, $_staffID, $_emailAddressHash, $_preferredWidth);
        if (_is_array($_imageCacheContainer)) {
//            echo $_imageCacheContainer[1];
            $_SWIFT_ImageResizeObject = new SWIFT_ImageResize($_imageCacheContainer[1]);
            if ($_SWIFT_ImageResizeObject instanceof SWIFT_ImageResize && $_SWIFT_ImageResizeObject->GetIsClassLoaded()) {
                $_SWIFT_ImageResizeObject->Output();

                return true;
            }
        }

        // First attempt to retrieve the image from staff profile
        $_staffHasProfileImage = SWIFT_StaffProfileImage::StaffHasProfileImage($_staffID);
        if ($_staffHasProfileImage) {
            $_SWIFT_StaffProfileImageObject = SWIFT_StaffProfileImage::RetrieveOnStaff($_staffID);
            if ($_SWIFT_StaffProfileImageObject instanceof SWIFT_StaffProfileImage && $_SWIFT_StaffProfileImageObject->GetIsClassLoaded()) {
                // We have received the locally set profile image for this staff, attempt to get it according to our required size
                $_imageFilename = self::CACHE_PREFIX . md5(self::TYPE_STAFF . $_staffID . $_emailAddressHash) . '_' . $_preferredWidth . '.' . $_SWIFT_StaffProfileImageObject->GetProperty('extension');
                self::UpdateImageCache($_imageFilename, base64_decode($_SWIFT_StaffProfileImageObject->GetProperty('imagedata')));

                $_SWIFT_ImageResizeObject = new SWIFT_ImageResize('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . $_imageFilename);
                if ($_SWIFT_ImageResizeObject instanceof SWIFT_ImageResize && $_SWIFT_ImageResizeObject->GetIsClassLoaded()) {
                    $_SWIFT_ImageResizeObject->SetSize($_preferredWidth, $_preferredWidth);
                    $_SWIFT_ImageResizeObject->SetKeepProportions(true);

                    $_SWIFT_ImageResizeObject->Resize();

                    $_SWIFT_ImageResizeObject->Save('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . $_imageFilename, true);

                    return true;
                }
            }
        }

        // If none exists, then check gravatar
        $imageProfileExtension = 'png';
        $_gravatarImageContents = false;
        if ($_hasGravatarFallback) {
            $_gravatarImageContents = self::RetrieveFromGravatar($_emailAddressHash);
        }

        if (empty($_gravatarImageContents)) {
            $imageProfileExtension = 'gif';
            $_gravatarImageContents = file_get_contents('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_THEMES_DIRECTORY . '/__cp/images/icon_defaultavatar.gif');
        }

        $_imageFilename = self::CACHE_PREFIX . md5(self::TYPE_STAFF . $_staffID . $_emailAddressHash) . '_' . $_preferredWidth . '.' . $imageProfileExtension;
        self::UpdateImageCache($_imageFilename, $_gravatarImageContents);


        $_SWIFT_ImageResizeObject = new SWIFT_ImageResize('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . $_imageFilename);
        if ($_SWIFT_ImageResizeObject instanceof SWIFT_ImageResize && $_SWIFT_ImageResizeObject->GetIsClassLoaded()) {
            $_SWIFT_ImageResizeObject->SetSize($_preferredWidth, $_preferredWidth);
            $_SWIFT_ImageResizeObject->SetKeepProportions(true);

            $_SWIFT_ImageResizeObject->Resize();

            $_SWIFT_ImageResizeObject->Save('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . $_imageFilename, true);

            return true;
        }

        return true;
    }

    /**
     * Retrieve the Profile image from a User ID
     *
     * @author Varun Shoor
     * @param int $_userID The User ID
     * @param string $_emailAddressHash (OPTIONAL) The Replacement Email Address
     * @param int $_preferredWidth (OPTIONAL) The Preferred Width
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function OutputOnUserID($_userID, $_emailAddressHash = '', $_preferredWidth = 100)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!empty($_userID)) {
            $_userEmailList = SWIFT_UserEmail::RetrieveList($_userID);

            if (_is_array($_userEmailList)) {
                $_emailAddressHash = md5($_userEmailList[0]);
            }
        }

        // Do we have a cache for this?
        $_imageCacheContainer = self::RetrieveFromCache(self::TYPE_USER, $_userID, $_emailAddressHash, $_preferredWidth);
        if (_is_array($_imageCacheContainer)) {
            $_SWIFT_ImageResizeObject = new SWIFT_ImageResize($_imageCacheContainer[1]);
            if ($_SWIFT_ImageResizeObject instanceof SWIFT_ImageResize && $_SWIFT_ImageResizeObject->GetIsClassLoaded()) {
                $_SWIFT_ImageResizeObject->Output();

                return true;
            }
        }

        // First attempt to retrieve the image from user profile
        $_userHasProfileImage = SWIFT_UserProfileImage::UserHasProfileImage($_userID);
        if ($_userHasProfileImage) {
            $_SWIFT_UserProfileImageObject = SWIFT_UserProfileImage::RetrieveOnUser($_userID);
            if ($_SWIFT_UserProfileImageObject instanceof SWIFT_UserProfileImage && $_SWIFT_UserProfileImageObject->GetIsClassLoaded()) {
                // We have received the locally set profile image for this user, attempt to get it according to our required size
                $_imageFilename = self::CACHE_PREFIX . md5(self::TYPE_USER . $_userID . $_emailAddressHash) . '_' . $_preferredWidth . '.' . $_SWIFT_UserProfileImageObject->GetProperty('extension');
                self::UpdateImageCache($_imageFilename, base64_decode($_SWIFT_UserProfileImageObject->GetProperty('imagedata')));

                $_SWIFT_ImageResizeObject = new SWIFT_ImageResize('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . $_imageFilename);
                if ($_SWIFT_ImageResizeObject instanceof SWIFT_ImageResize && $_SWIFT_ImageResizeObject->GetIsClassLoaded()) {
                    $_SWIFT_ImageResizeObject->SetSize($_preferredWidth, $_preferredWidth);
                    $_SWIFT_ImageResizeObject->SetKeepProportions(true);

                    $_SWIFT_ImageResizeObject->Resize();

                    $_SWIFT_ImageResizeObject->Save('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . $_imageFilename, true);

                    return true;
                }
            }
        }

        // If none exists, then check gravatar
        $imageProfileExtension = 'png';
        $_gravatarImageContents = self::RetrieveFromGravatar($_emailAddressHash);

        if (empty($_gravatarImageContents)) {
            $imageProfileExtension = 'gif';
            $_gravatarImageContents = file_get_contents('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_THEMES_DIRECTORY . '/client/images/icon_defaultavatar.gif');
        }

        $_imageFilename = self::CACHE_PREFIX . md5(self::TYPE_USER . $_userID . $_emailAddressHash) . '_' . $_preferredWidth . '.' . $imageProfileExtension;
        self::UpdateImageCache($_imageFilename, $_gravatarImageContents);

        $_SWIFT_ImageResizeObject = new SWIFT_ImageResize('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . $_imageFilename);
        if ($_SWIFT_ImageResizeObject instanceof SWIFT_ImageResize && $_SWIFT_ImageResizeObject->GetIsClassLoaded()) {
            $_SWIFT_ImageResizeObject->SetSize($_preferredWidth, $_preferredWidth);
            $_SWIFT_ImageResizeObject->SetKeepProportions(true);

            $_SWIFT_ImageResizeObject->Resize();

            $_SWIFT_ImageResizeObject->Save('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/' . $_imageFilename, true);

            return true;
        }

        return true;
    }
}

?>
