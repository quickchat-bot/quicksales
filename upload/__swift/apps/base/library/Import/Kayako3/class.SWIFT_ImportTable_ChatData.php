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

namespace Base\Library\Import\QuickSupport3;

use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: ChatData
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_ChatData extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'ChatData');

        if (!$this->TableExists(TABLE_PREFIX . 'chatdata')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadLibrary('Chat:ChatManager', APP_LIVECHAT);

        SWIFT_Loader::LoadModel('Chat:Chat', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Chat:ChatTextData', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Chat:ChatHits', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Chat:ChatQueue', APP_LIVECHAT);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "chatdata");
        }

        $_count = 0;

        $_oldChatObjectIDList = $_chatDataContainer = $_chatObjectContainer = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "chatdata ORDER BY chatdataid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_chatDataContainer[$this->DatabaseImport->Record['chatdataid']] = $this->DatabaseImport->Record;

            $_oldChatObjectIDList[] = $this->DatabaseImport->Record['chatobjectid'];
        }

        $_newChatObjectIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('chatobject', $_oldChatObjectIDList);
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatobjects WHERE chatobjectid IN (" . BuildIN($_newChatObjectIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_chatObjectContainer[$this->Database->Record['chatobjectid']] = $this->Database->Record;
        }

        foreach ($_chatDataContainer as $_chatDataID => $_chatData) {
            $_count++;

            if (!isset($_newChatObjectIDList[$_chatData['chatobjectid']]) || !isset($_chatObjectContainer[$_newChatObjectIDList[$_chatData['chatobjectid']]])) {
                $this->GetImportManager()->AddToLog('Failed to Import Chat Data due to non existent chat objectid: ' . htmlspecialchars($_chatData['chatobjectid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_chatObjectID = (int)($_newChatObjectIDList[$_chatData['chatobjectid']]);
            $_chatObject = $_chatObjectContainer[$_chatObjectID];

            $this->GetImportManager()->AddToLog('Importing Chat Data: ' . $_chatDataID, SWIFT_ImportManager::LOG_SUCCESS);

            $_existingChatContainer = mb_unserialize($_chatData['contents']);

            $_chatContainer = array();
            $_index = 0;
            if (_is_array($_existingChatContainer)) {
                foreach ($_existingChatContainer as $_chatEntry) {
                    $_messageType = SWIFT_ChatQueue::MESSAGE_CLIENT;
                    $_submitType = SWIFT_ChatQueue::SUBMIT_CLIENT;

                    if ($_chatEntry['type'] == 'client') {
                        $_messageType = SWIFT_ChatQueue::MESSAGE_STAFF;
                        $_submitType = SWIFT_ChatQueue::SUBMIT_CLIENT;
                    } elseif ($_chatEntry['type'] == 'staff') {
                        $_messageType = SWIFT_ChatQueue::MESSAGE_CLIENT;
                        $_submitType = SWIFT_ChatQueue::SUBMIT_STAFF;
                    }

                    if (!isset($_chatEntry['base64'])) {
                        $_chatEntry['base64'] = '0';
                    }

                    $_finalMessage = $_chatEntry['message'];
                    if ($_chatEntry['base64'] == '1') {
                        $_finalMessage = base64_encode(html_entity_decode(base64_decode($_chatEntry['message']), ENT_COMPAT, 'UTF-8'));
                    } elseif ($_chatEntry['base64'] == '0') {
                        $_finalMessage = html_entity_decode($_chatEntry['message'], ENT_COMPAT, 'UTF-8');
                    }

                    $_newChatEntry = array();
                    $_newChatEntry['type'] = $_messageType;
                    $_newChatEntry['name'] = $_chatEntry['name'];
                    $_newChatEntry['message'] = $_finalMessage;
                    $_newChatEntry['base64'] = $_chatEntry['base64'];
                    $_newChatEntry['submittype'] = $_submitType;
                    $_newChatEntry['actiontype'] = SWIFT_ChatQueue::CHATACTION_MESSAGE;
                    $_newChatEntry['dateline'] = $_chatObject['dateline'];

                    $_chatContainer[$_index] = $_newChatEntry;

                    $_index++;
                }
            }

            $_finalContents = serialize($_chatContainer);

            $this->Database->AutoExecute(TABLE_PREFIX . 'chatdata',
                array('chatobjectid' => $_chatObjectID, 'contents' => $_finalContents), 'INSERT');
            $_chatDataID = $this->Database->InsertID();
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "chatdata");
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
