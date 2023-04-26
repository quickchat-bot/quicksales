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
 * Import Table: ChatHit
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_ChatHit extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'ChatHit');

        if (!$this->TableExists(TABLE_PREFIX . 'chathits')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "chathits");
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_count = 0;

        $_oldChatObjectIDList = $_chatHitContainer = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "chathits ORDER BY chathitid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_chatHitContainer[$this->DatabaseImport->Record['chathitid']] = $this->DatabaseImport->Record;

            $_oldChatObjectIDList[] = $this->DatabaseImport->Record['chatobjectid'];
        }

        $_newChatObjectIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('chatobject', $_oldChatObjectIDList);

        foreach ($_chatHitContainer as $_chatHitID => $_chatHit) {
            $_count++;

            if (!isset($_newChatObjectIDList[$_chatHit['chatobjectid']])) {
                $this->GetImportManager()->AddToLog('Failed to Import Chat Hit due to non existent chat objectid: ' . htmlspecialchars($_chatHit['chatobjectid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_chatObjectID = (int)($_newChatObjectIDList[$_chatHit['chatobjectid']]);

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_chatHit['staffid']);

            $this->GetImportManager()->AddToLog('Importing Chat Hit: ' . $_chatHitID, SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'chathits',
                array('staffid' => $_newStaffID, 'chatobjectid' => $_chatObjectID, 'dateline' => $_chatHit['dateline'], 'fullname' => $_chatHit['fullname'],
                    'email' => $_chatHit['email'], 'isaccepted' => (int)($_chatHit['isaccepted'])), 'INSERT');
            $_chatHitID = $this->Database->InsertID();
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "chathits");
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
