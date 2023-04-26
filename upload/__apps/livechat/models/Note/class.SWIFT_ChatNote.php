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

namespace LiveChat\Models\Note;

use Base\Models\Staff\SWIFT_Staff;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT;
use LiveChat\Models\Note\SWIFT_Note_Exception;
use LiveChat\Models\Note\SWIFT_VisitorNoteManager;

/**
 * The Chat Note Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_ChatNote extends SWIFT_VisitorNoteManager
{
    /**
     * Create a new Chat Note
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The Chat Object
     * @param string $_noteContents The Note Contents
     * @param int $_noteColor The Note Color
     * @return mixed "SWIFT_ChatNote" Object on Success, "false" otherwise
     * @throws SWIFT_Note_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_SWIFT_ChatObject, $_noteContents, $_noteColor = 1,
                                  $_ = null, $__ = null, $___ = null)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Note_Exception(SWIFT_INVALIDDATA);

            return false;
        } else if (!$_SWIFT->Staff instanceof SWIFT_Staff || !$_SWIFT->Staff->GetIsClassLoaded()) {
            throw new SWIFT_Note_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_visitorNoteID = parent::Create(self::LINKTYPE_CHAT, $_SWIFT_ChatObject->GetChatObjectID(), $_SWIFT->Staff->GetStaffID(), $_SWIFT->Staff->GetProperty('fullname'), $_noteContents, $_noteColor);
        if (!$_visitorNoteID) {
            throw new SWIFT_Note_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        return new SWIFT_ChatNote($_visitorNoteID);
    }

    /**
     * Delete Visitor Notes based on Chat Object ID List
     *
     * @author Varun Shoor
     * @param array $_chatObjectIDList The Chat Object ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnChat($_chatObjectIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_chatObjectIDList)) {
            return false;
        }

        $_visitorNoteIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitornotes WHERE linktype = '" . self::LINKTYPE_CHAT . "' AND linktypevalue IN (" . BuildIN($_chatObjectIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_visitorNoteIDList[] = $_SWIFT->Database->Record['visitornoteid'];
        }

        if (!count($_visitorNoteIDList)) {
            return false;
        }

        self::DeleteList($_visitorNoteIDList);

        return true;
    }
}

