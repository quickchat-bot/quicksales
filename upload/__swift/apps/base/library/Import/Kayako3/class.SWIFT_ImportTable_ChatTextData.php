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

namespace Base\Library\Import\Kayako3;

use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: ChatTextData
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_ChatTextData extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'ChatTextData');

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

        $_count = 0;

        $_chatDataContainer = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "chatdata ORDER BY chatdataid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;
        }

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "chatdata ORDER BY chatdataid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->Database->NextRecord()) {
            $_chatDataContainer[$this->Database->Record['chatdataid']] = $this->Database->Record;
        }

        foreach ($_chatDataContainer as $_chatDataID => $_chatData) {
            $this->GetImportManager()->AddToLog('Importing Chat Text Data: ' . $_chatDataID, SWIFT_ImportManager::LOG_SUCCESS);

            $_chatDataArray = mb_unserialize($_chatData['contents']);

            $_chatText = '';

            if (_is_array($_chatDataArray)) {
                foreach ($_chatDataArray as $_key => $_val) {
                    if (!isset($_val['base64'])) {
                        $_val['base64'] = '0';
                    }

                    if ($_val['actiontype'] != SWIFT_ChatQueue::CHATACTION_SYSTEM &&
                        $_val['actiontype'] != SWIFT_ChatQueue::CHATACTION_SYSTEMMESSAGE &&
                        $_val['actiontype'] != SWIFT_ChatQueue::CHATACTION_MESSAGE) {
                        unset($_chatDataArray[$_key]);

                        continue;
                    }

                    if ($_val['base64'] == '1') {
                        $_chatText .= html_entity_decode(base64_decode($_val['message']), ENT_COMPAT, 'UTF-8') . SWIFT_CRLF;
                    } else {
                        $_chatText .= html_entity_decode($_val['message'], ENT_COMPAT, 'UTF-8') . SWIFT_CRLF;
                    }
                }
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'chattextdata',
                array('chatobjectid' => (int)($_chatData['chatobjectid']), 'contents' => $_chatText), 'INSERT');
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
