<?php

/**
 *  *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Rajat Garg
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 *  */

namespace Base\Library\User;

use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserGroupAssign;
use Base\Models\User\SWIFT_UserMerged;
use Base\Models\User\SWIFT_UserProfileImage;
use Base\Models\User\SWIFT_UserProperty;
use Base\Models\User\SWIFT_UserSetting;
use Base\Models\User\SWIFT_UserVerifyHash;
use News\Models\Subscriber\SWIFT_NewsSubscriber;
use SWIFT_App;
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Library;
use SWIFT_Loader;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * The User Merging Manager
 *
 * @author Rajat Garg
 */
class SWIFT_UsersMerge extends SWIFT_Library
{

    /**
     * @author Rajat Garg
     *
     * @param int $_primaryUserID
     * @param array $_secondaryUserIDList
     *
     * @return bool
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function MergeUsers($_primaryUserID, $_secondaryUserIDList)
    {
        if (!_is_array($_secondaryUserIDList) || empty($_primaryUserID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        SWIFT_UserMerged::Insert($_primaryUserID, $_secondaryUserIDList);

        return self::UpdateUserRecords($_primaryUserID, $_secondaryUserIDList);
    }

    /**
     * Update User tables for processing Merge
     *
     * @author Mansi Wason <mansi.wason@kayako.com>
     *
     * @param int $_primaryUserID
     * @param array $_secondaryUserIDList
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    static private function UpdateUserRecords($_primaryUserID, $_secondaryUserIDList)
    {
        if (!_is_array($_secondaryUserIDList) || empty($_primaryUserID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        SWIFT_UserEmail::UpdateIsPrimary($_primaryUserID, true);

        self::UpdateUserOnMerge($_primaryUserID, $_secondaryUserIDList);

        foreach ($_secondaryUserIDList as $_secondaryUserID) {
            $_User = new SWIFT_User(new SWIFT_DataID($_secondaryUserID));

            if (!$_User instanceof SWIFT_User) {
                continue;
            }

            if (SWIFT_App::IsInstalled(APP_TICKETS)) {
                $_ticketIDList = SWIFT_Ticket::GetTicketIDListOnUser($_User);
                if (_is_array($_ticketIDList)) {
                    foreach ($_ticketIDList as $_ticketID) {
                        $_Ticket = SWIFT_Ticket::GetObjectOnID($_ticketID);

                        if ($_Ticket instanceof SWIFT_Ticket && SWIFT_App::IsInstalled(APP_TICKETS)) {
                            $_Ticket->UpdateUser($_primaryUserID);
                        }
                    }
                }
                //Merging will change the user for all the operations.
                SWIFT_TicketPost::UpdateUserIDOnMerge($_primaryUserID, $_secondaryUserIDList);
            }

            if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
                SWIFT_Call::UpdateUserIDOnMerge($_primaryUserID, $_secondaryUserIDList);
                SWIFT_Chat::UpdateUserIDOnMerge($_primaryUserID, $_secondaryUserIDList);
            }

            //Delete the news subscribers.
            SWIFT_NewsSubscriber::DeleteOnUserID($_secondaryUserID);

            //Emails
            SWIFT_UserEmail::UpdateIsPrimary($_secondaryUserID, false);
            SWIFT_UserEmail::UpdateUserID($_secondaryUserID, $_primaryUserID);

            //Deletion
            $_userIDList = array($_secondaryUserID);
            SWIFT_UserSetting::DeleteOnUser($_userIDList);
            SWIFT_UserGroupAssign::DeleteOnUser($_userIDList);
            SWIFT_User::DeleteList($_userIDList);
            SWIFT_UserProfileImage::DeleteList($_userIDList);
            SWIFT_UserProperty::DeleteOnUser($_userIDList);
            SWIFT_UserVerifyHash::DeleteOnUser($_userIDList);
        }

        return true;
    }

    /**
     * Update secondary user IDs with primary user ID
     *
     * @author Abhishek Mittal
     *
     * @param int $_primaryUserID
     * @param array $_secondaryUserIDList
     *
     * @return bool
     */
    public static function UpdateUserOnMerge($_primaryUserID, $_secondaryUserIDList)
    {
        if (!_is_array($_secondaryUserIDList)) {
            return false;
        }

        $_classContainer = self::RetrieveCacheList();

        foreach ($_classContainer['model'] as $_modelContainer) {

            list($_modelLoadName, $_modelName, $_modelFilePath, $_appName) = $_modelContainer;

            SWIFT_Loader::LoadModel($_modelLoadName, $_appName);

            call_user_func_array(array(prepend_model_namespace($_appName, 'SWIFT_' . $_modelName, $_modelFilePath), 'UpdateUserIDOnMerge'), array($_primaryUserID, $_secondaryUserIDList));
        }

        foreach ($_classContainer['lib'] as $_libContainer) {

            list($_libLoadName, $_libName, $_libFilePath, $_appName) = $_libContainer;

            SWIFT_Loader::LoadLibrary($_libLoadName, $_appName);

            call_user_func_array(array(prepend_library_namespace(explode(':', $_libLoadName), $_libName, 'SWIFT_' . $_libName, 'library', $_appName), 'UpdateUserIDOnMerge'), array($_primaryUserID, $_secondaryUserIDList));
        }

        return true;
    }

    /**
     * Retrieve the list of models/libs
     *
     * @author Abhishek Mittal
     *
     * @return array|bool
     */
    public static function RetrieveCacheList()
    {
        chdir(SWIFT_BASEPATH);

        $_returnContainer = array('lib' => array(), 'model' => array());

        $_appList = SWIFT_App::GetInstalledApps();

        foreach ($_appList as $_appName) {
            if (SWIFT_App::IsInstalled($_appName)) {

                $_App = SWIFT_App::Get($_appName);
                if (!$_App instanceof SWIFT_App || !$_App->GetIsClassLoaded()) {
                    continue;
                }
                $_returnContainer['model'] = array_merge($_returnContainer['model'], $_App->RetrieveFileList(SWIFT_App::FILETYPE_MODEL, 'UpdateUserIDOnMerge'));

                $_returnContainer['lib'] = array_merge($_returnContainer['lib'], $_App->RetrieveFileList(SWIFT_App::FILETYPE_LIBRARY, 'UpdateUserIDOnMerge'));
            } else {
                return false;
            }

            return $_returnContainer;
        }
    }
}
