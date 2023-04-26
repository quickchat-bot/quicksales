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

use LiveChat\Models\Canned\SWIFT_CannedResponse;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: CannedResponse
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_CannedResponse extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'CannedResponse');

        if (!$this->TableExists(TABLE_PREFIX . 'cannedresponses')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "cannedresponses");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "cannedresponses ORDER BY cannedresponseid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newCannedCategoryID = $this->ImportManager->GetImportRegistry()->GetKey('cannedcategory', $this->DatabaseImport->Record['cannedcategoryid']);
            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);

            $_responseType = SWIFT_CannedResponse::TYPE_NONE;
            if ($this->DatabaseImport->Record['responsetype'] == 'text') {
                $_responseType = SWIFT_CannedResponse::TYPE_MESSAGE;
            } elseif ($this->DatabaseImport->Record['responsetype'] == 'code') {
                $_responseType = SWIFT_CannedResponse::TYPE_CODE;
            }

            $this->GetImportManager()->AddToLog('Importing Canned Response: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'cannedresponses',
                array('cannedcategoryid' => $_newCannedCategoryID, 'staffid' => $_newStaffID, 'title' => $this->DatabaseImport->Record['title'],
                    'urldata' => '', 'imagedata' => '', 'responsetype' => $_responseType, 'dateline' => $this->DatabaseImport->Record['dateline']), 'INSERT');
            $_cannedResponseID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('cannedresponse', $this->DatabaseImport->Record['cannedresponseid'], $_cannedResponseID);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "cannedresponses");
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
