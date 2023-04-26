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

namespace LiveChat\Library\Chat;

use Base\Library\SearchEngine\SWIFT_SearchEngine;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use LiveChat\Models\Chat\SWIFT_ChatTextData;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Chat Management Library
 *
 * @author Varun Shoor
 */
class SWIFT_ChatManager extends SWIFT_Library
{
    /**
     * Index the pending chat objects
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IndexPending()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_chatObjectIDList = $_chatDataContainer = array();
        $this->Database->QueryLimit("SELECT chatobjectid FROM " . TABLE_PREFIX . "chatobjects WHERE isindexed = '0'
            AND (chatstatus = '" . SWIFT_Chat::CHAT_ENDED . "' OR chatstatus = '" . SWIFT_Chat::CHAT_TIMEOUT . "')", 10);
        while ($this->Database->NextRecord()) {
            $_chatObjectIDList[] = $this->Database->Record['chatobjectid'];
        }

        if (!count($_chatObjectIDList)) {
            return true;
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatdata WHERE chatobjectid IN (" . BuildIN($_chatObjectIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_chatDataContainer[$this->Database->Record['chatobjectid']] = $this->Database->Record['contents'];
        }

        foreach ($_chatDataContainer as $_chatObjectID => $_rawContents) {
            $_chatText = '';

            $_chatDataArray = mb_unserialize($_rawContents);
            if (_is_array($_chatDataArray)) {
                foreach ($_chatDataArray as $_key => $_val) {
                    if ($_val['actiontype'] != SWIFT_ChatQueue::CHATACTION_SYSTEM &&
                        $_val['actiontype'] != SWIFT_ChatQueue::CHATACTION_SYSTEMMESSAGE &&
                        $_val['actiontype'] != SWIFT_ChatQueue::CHATACTION_MESSAGE) {
                        unset($_chatDataArray[$_key]);

                        continue;
                    }

                    if ($_val['base64'] == '1') {
                        $_chatText .= base64_decode($_val['message']) . SWIFT_CRLF;
                    } else {
                        $_chatText .= $_val['message'] . SWIFT_CRLF;
                    }
                }
            }

            // Create Chat Text Data for Quick Search
            SWIFT_ChatTextData::Create($_chatObjectID, $_chatText);

            // Search engine indexing
            $eng = new SWIFT_SearchEngine();
            $eng->Insert($_chatObjectID, 0, SWIFT_SearchEngine::TYPE_CHAT, $_chatText);
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'chatobjects', array('isindexed' => '1'), 'UPDATE', "chatobjectid IN (" . BuildIN($_chatObjectIDList) . ")");

        return true;
    }
}
