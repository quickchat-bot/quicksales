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

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: CannedResponseData
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CannedResponseData extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'CannedResponseData');

        if (!$this->TableExists(TABLE_PREFIX . 'cannedresponsedata')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Canned:CannedCategory', APP_LIVECHAT);
        SWIFT_Loader::LoadModel('Canned:CannedResponse', APP_LIVECHAT);
        SWIFT_Loader::LoadLibrary('Canned:CannedManager', APP_LIVECHAT);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "cannedresponsedata");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT cannedresponses.*, cannedresponsedata.* FROM " . TABLE_PREFIX . "cannedresponsedata AS cannedresponsedata
            LEFT JOIN " . TABLE_PREFIX . "cannedresponses AS cannedresponses ON (cannedresponses.cannedresponseid = cannedresponsedata.cannedresponseid)
            ORDER BY cannedresponsedata.cannedresponsedataid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newCannedResponseID = $this->ImportManager->GetImportRegistry()->GetKey('cannedresponse', $this->DatabaseImport->Record['cannedresponseid']);
            if (!$_newCannedResponseID) {
                $this->GetImportManager()->AddToLog('Failed to Import Canned Response Data due to non existent parent record: ' . htmlspecialchars($this->DatabaseImport->Record['cannedresponseid']), SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_responseContents = '';

            $_updateCannedResponseRecord = false;
            $_urlData = $_imageData = '';
            if ($this->DatabaseImport->Record['responsetype'] == 'url') {
                $_updateCannedResponseRecord = true;

                $_urlData = $this->DatabaseImport->Record['contents'];
            } elseif ($this->DatabaseImport->Record['responsetype'] == 'image') {
                $_updateCannedResponseRecord = true;

                $_imageData = $this->DatabaseImport->Record['contents'];
            } else {
                $_responseContents = $this->DatabaseImport->Record['contents'];
            }

            if ($_updateCannedResponseRecord) {
                $this->Database->AutoExecute(TABLE_PREFIX . 'cannedresponses',
                    array('urldata' => $_urlData, 'imagedata' => $_imageData), 'UPDATE', "cannedresponseid = '" . $_newCannedResponseID . "'");
            }

            $this->GetImportManager()->AddToLog('Importing Canned Response Data: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'cannedresponsedata',
                array('cannedresponseid' => $_newCannedResponseID, 'contents' => $_responseContents), 'INSERT');
        }

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "cannedresponsedata");
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
