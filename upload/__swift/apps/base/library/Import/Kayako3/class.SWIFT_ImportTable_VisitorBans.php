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

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Table: VisitorBans
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_VisitorBans extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'VisitorBans');

        if (!$this->TableExists(TABLE_PREFIX . 'visitorbans')) {
            $this->SetByPass(true);
        }
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorbans");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "visitorbans ORDER BY visitorbanid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_staffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);

            $this->GetImportManager()->AddToLog('Importing Visitor Ban: ' . htmlspecialchars($this->DatabaseImport->Record['ipaddress']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'visitorbans',
                array('ipaddress' => $this->DatabaseImport->Record['ipaddress'], 'dateline' => $this->DatabaseImport->Record['dateline'], 'staffid' => $_staffID), 'INSERT');
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "visitorbans");
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
