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

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use LiveChat\Models\Message\SWIFT_MessageManager;

/**
 * Import Table: MessageData
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_MessageData extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'MessageData');

        if (!$this->TableExists(TABLE_PREFIX . 'messagedata')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Message:Message', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Message:MessageManager', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Message:MessageSurvey', APP_LIVECHAT);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "messagedata");
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_count = 0;

        $_oldMessageIDList = $_messageDataContainer = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "messagedata ORDER BY messagedataid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_messageDataContainer[$this->DatabaseImport->Record['messagedataid']] = $this->DatabaseImport->Record;

            $_oldMessageIDList[] = $this->DatabaseImport->Record['messageid'];
        }

        $_newMessageIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('message', $_oldMessageIDList);

        foreach ($_messageDataContainer as $_messageDataID => $_messageData) {
            $_count++;

            if (!isset($_newMessageIDList[$_messageData['messageid']])) {
                $this->GetImportManager()->AddToLog('Failed to Import Message Data due to non existent message: ' . htmlspecialchars($_messageData['messageid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_messageID = (int)($_newMessageIDList[$_messageData['messageid']]);

            $_contentType = SWIFT_MessageManager::CONTENT_CLIENT;

            // Is it a staff message?
            if ($_messageData['contenttype'] == '5') {
                $_contentType = SWIFT_MessageManager::CONTENT_STAFF;
            }

            $this->GetImportManager()->AddToLog('Importing Message Data for Message: ' . $_messageID, SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'messagedata',
                array('messageid' => $_messageID, 'contenttype' => $_contentType, 'contents' => $_messageData['contents']), 'INSERT');
            $_messageDataID = $this->Database->InsertID();
        }

        SWIFT_MessageManager::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "messagedata");
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

        return 500;
    }
}

?>
