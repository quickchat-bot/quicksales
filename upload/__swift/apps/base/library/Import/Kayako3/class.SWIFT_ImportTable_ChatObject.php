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

namespace Base\Library\Import\QuickSupport3;

use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: ChatObject
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_ChatObject extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'ChatObject');

        if (!$this->TableExists(TABLE_PREFIX . 'chatobjects')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadLibrary('Chat:ChatManager', APP_LIVECHAT);

        SWIFT_Loader::LoadModel('Chat:Chat', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Chat:ChatTextData', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Chat:ChatHits', APP_LIVECHAT);
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Varun Shoor
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatobjects");
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_count = 0;

        $_chatObjectContainer = $_userIDList = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "chatobjects ORDER BY chatobjectid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_chatObjectContainer[$this->DatabaseImport->Record['chatobjectid']] = $this->DatabaseImport->Record;

            if ($this->DatabaseImport->Record['userid'] != '0' && !in_array($this->DatabaseImport->Record['userid'], $_userIDList)) {
                $_userIDList[] = $this->DatabaseImport->Record['userid'];
            }
        }

        $_newUserIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('user', $_userIDList);

        foreach ($_chatObjectContainer as $_chatObjectID => $_chatObject) {
            $_count++;

            $_newDepartmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $_chatObject['departmentid']);
            if (!$_newDepartmentID) {
                $this->GetImportManager()->AddToLog('Failed to Import Chat due to non existent department: ' . htmlspecialchars($_chatObject['chatobjectid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_newUserID = false;
            if ($_chatObject['userid'] != '0' && isset($_newUserIDList[$_chatObject['userid']])) {
                $_newUserID = $_newUserIDList[$_chatObject['userid']];
            }

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_chatObject['staffid']);

            // Old Constants
            // define("CHAT_INCOMING", 1);
            // define("CHAT_INCHAT", 2);
            // define("CHAT_ENDED", 3);
            // define("CHAT_NOANSWER", 4);
            // define("CHAT_TIMEOUT", 5);

            $_chatStatus = SWIFT_Chat::CHAT_TIMEOUT;

            // We treat all incoming and "in chat" chat objects as ended
            if ($_chatObject['chatstatus'] == '1') {
                $_chatStatus = SWIFT_Chat::CHAT_ENDED;
            } elseif ($_chatObject['chatstatus'] == '2') {
                $_chatStatus = SWIFT_Chat::CHAT_ENDED;
            } elseif ($_chatObject['chatstatus'] == '3') {
                $_chatStatus = SWIFT_Chat::CHAT_ENDED;
            } elseif ($_chatObject['chatstatus'] == '4') {
                $_chatStatus = SWIFT_Chat::CHAT_NOANSWER;
            } elseif ($_chatObject['chatstatus'] == '5') {
                $_chatStatus = SWIFT_Chat::CHAT_TIMEOUT;
            }

            $_transferFromID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_chatObject['transferfromid']);
            $_transferToID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_chatObject['transfertoid']);

            // Old Transfer Constants
            // define("TRANSFER_PENDING", 1);
            // define("TRANSFER_ACCEPTED", 2);

            $_transferStatus = '0';

            if ($_chatObject['transferstatus'] == '1') {
                $_transferStatus = SWIFT_Chat::TRANSFER_PENDING;
            } elseif ($_chatObject['transferstatus'] == '2') {
                $_transferStatus = SWIFT_Chat::TRANSFER_ACCEPTED;
            }

            // Old Chat Type Constants
            // define("CHAT_STAFF", 6);
            // define("CHAT_CLIENT", 7);

            $_chatType = SWIFT_Chat::CHATTYPE_CLIENT;

            if ($_chatObject['chattype'] == '7') {
                $_chatType = SWIFT_Chat::CHATTYPE_CLIENT;
            } elseif ($_chatObject['chattype'] == '6') {
                $_chatType = SWIFT_Chat::CHATTYPE_STAFF;
            }

            $_waitTime = $_chatObject['roundrobintimeline'] - $_chatObject['dateline'];
            if ($_waitTime < 0) {
                $_waitTime = 0;
            }

            $this->GetImportManager()->AddToLog('Importing Chat Object: ' . htmlspecialchars($_chatObject['chatobjectid']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'chatobjects',
                array('chatobjectmaskid' => SWIFT_Chat::GetChatObjectMaskID(), 'visitorsessionid' => '', 'chatsessionid' => '', 'dateline' => $_chatObject['dateline'],
                    'lastpostactivity' => $_chatObject['lastpostactivity'], 'userpostactivity' => $_chatObject['userpostactivity'],
                    'staffpostactivity' => $_chatObject['staffpostactivity'], 'userid' => $_newUserID, 'userfullname' => $_chatObject['userfullname'],
                    'useremail' => $_chatObject['useremail'], 'subject' => $this->Language->Get('na'), 'staffid' => $_newStaffID, 'staffname' => $_chatObject['staffname'],
                    'chatstatus' => $_chatStatus, 'transferfromid' => $_transferFromID, 'transferstatus' => (int)($_transferStatus), 'transfertoid' => $_transferToID,
                    'transfertimeline' => $_chatObject['transfertimeline'], 'roundrobintimeline' => $_chatObject['roundrobintimeline'], 'roundrobinhits' => $_chatObject['roundrobinhits'],
                    'departmentid' => $_newDepartmentID, 'departmenttitle' => $_chatObject['departmenttitle'], 'chattype' => $_chatType, 'ipaddress' => $_chatObject['ipaddress'],
                    'isproactive' => $_chatObject['isproactive'], 'waittime' => $_waitTime, 'tgroupid' => '0', 'creatorstaffid' => '0', 'isphone' => '0', 'phonenumber' => '',
                    'callstatus' => '0', 'isindexed' => '0', 'chatskillid' => '0'), 'INSERT');
            $_chatObjectID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('chatobject', $_chatObject['chatobjectid'], $_chatObjectID, true);
        }

        SWIFT_Chat::RebuildCache();

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Varun Shoor
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "chatobjects");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Varun Shoor
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 1000;
    }
}

?>
