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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace LiveChat\Models\Chat;

use LiveChat\Library\Chat\SWIFT_Chat_Exception;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Chat Childs Management Class. Chat childs are the additional users in a chat other than the initiator (staff/user)
 *
 * @author Varun Shoor
 */
class SWIFT_ChatChild extends SWIFT_Model
{
    const TABLE_NAME = 'chatchilds';
    const PRIMARY_KEY = 'chatchildid';

    const TABLE_STRUCTURE = "chatchildid I PRIMARY AUTO NOTNULL,
                                chatobjectid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                isinvite I2 DEFAULT '0' NOTNULL,
                                isobserver I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'chatobjectid, staffid';
    const INDEX_2 = 'staffid';
    const INDEX_3 = 'chatobjectid, isinvite';


    private $_chatChild = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_chatChildID The Chat Child ID to load data from
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function __construct($_chatChildID)
    {
        parent::__construct();

        if (!$this->LoadData($_chatChildID)) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }
    }

    /**
     * Load the Chat Child Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|string $_chatChildID The Chat Child ID to load data from
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_chatChildID)
    {
        $_chatChild = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "chatchilds WHERE chatchildid = '" . $this->Database->Escape($_chatChildID) . "'");
        if (!isset($_chatChild['chatchildid']) || empty($_chatChild['chatchildid'])) {
            return false;
        }

        $this->_chatChild = $_chatChild;

        return true;
    }

    /**
     * Deletes all the Chat Childs for the current chat object
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatchilds WHERE chatchildid = '" . $this->Database->Escape($this->GetChatChildID()) . "'");

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Retrieve the Chat Child ID
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws \LiveChat\Models\Chat\SWIFT_Chat_Exception If the Class is not Loaded
     */
    public function GetChatChildID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_chatChild['chatchildid'];
    }

    /**
     * Inserts a new Chat Child and Associate it with an active Chat Object
     *
     * @author Varun Shoor
     * @param SWIFT_Chat $_SWIFT_ChatObject The Live Chat Object
     * @param int $_staffID The Staff ID
     * @param bool $_isInvite (OPTIONAL) Whether this is a pending invitation to chat
     * @param bool $_isObserver (OPTIONAL) Whether this is an observer
     * @return mixed "chatChildID" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Insert(SWIFT_Chat $_SWIFT_ChatObject, $_staffID, $_isInvite = false, $_isObserver = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_ChatObject instanceof SWIFT_Chat || !$_SWIFT_ChatObject->GetIsClassLoaded()) {
            throw new SWIFT_Chat_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_staffID)) {
            throw new SWIFT_Chat_Exception(SWIFT_INVALIDDATA);
        }

        // Check for an existing chat child entry..
        $_chatChildContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "chatchilds WHERE chatobjectid = '" . ($_SWIFT_ChatObject->GetChatObjectID()) . "' AND staffid = '" . $_staffID . "'");
        if (isset($_chatChildContainer['chatchildid']) && !empty($_chatChildContainer['chatchildid'])) {
            // Previously it was an invite and now it isnt? make him a part of this chat! Or if we are inviting a staff over...
            if (($_chatChildContainer['isinvite'] == '1' && $_isInvite == false) || ($_chatChildContainer['isinvite'] == '0' && $_isInvite == true)) {
                $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chatchilds', array('isinvite' => (int)($_isInvite)), 'UPDATE', "chatchildid = '" . (int)($_chatChildContainer['chatchildid']) . "'");
            }

            return $_chatChildContainer['chatchildid'];
        }


        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'chatchilds', array('chatobjectid' => $_SWIFT_ChatObject->GetChatObjectID(), 'staffid' => $_staffID,
            'dateline' => DATENOW, 'isinvite' => (int)($_isInvite), 'isobserver' => (int)($_isObserver)), 'INSERT');
        return $_SWIFT->Database->Insert_ID();
    }
}
