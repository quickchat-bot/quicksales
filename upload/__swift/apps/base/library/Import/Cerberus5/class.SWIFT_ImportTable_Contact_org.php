<?php

namespace Base\Library\Import\Cerberus5;

use Base\Models\User\SWIFT_UserOrganization;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import table: Userorganization
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Contact_org extends SWIFT_ImportTable
{
    var $dwk_zendesk_url = false;
    var $dwk_userorganization_array = array();

    /**
     * Constructor
     *
     * @author Nicolás Ibarra Sabogal
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'Contact_org');

        if (!$this->TableExists('contact_org')) {
            $this->SetByPass(true);
        }
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Cache the existing items
        if ($this->GetOffset() == 0) {
            $_existingUserOrganizationContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "userorganizations ORDER BY userorganizationid ASC");
            while ($this->Database->NextRecord()) {
                $_existingUserOrganizationContainer[$this->Database->Record['userorganizationid']] = $this->Database->Record;
            }

            foreach ($_existingUserOrganizationContainer as $_userOrganizationContainer) {
                $this->ImportManager->GetImportRegistry()->UpdateKey('organizationname', mb_strtolower(trim($_userOrganizationContainer['organizationname'])), '1');
            }
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM contact_org ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_titleSuffix = '';
            $_existingDepartmentTitle = $this->ImportManager->GetImportRegistry()->GetKey('organizationname', mb_strtolower(trim($this->DatabaseImport->Record['name'])));

            // A record with same title exists?
            if ($_existingDepartmentTitle != false) {
                $_titleSuffix .= ' (Import)';
            }

            $this->GetImportManager()->AddToLog('Importing User Organization: ' . htmlspecialchars($this->DatabaseImport->Record['name']), SWIFT_ImportManager::LOG_SUCCESS);

            $_groupType = SWIFT_UserOrganization::TYPE_RESTRICTED;

            $this->Database->AutoExecute(TABLE_PREFIX . 'userorganizations',
                array('organizationname' => $this->DatabaseImport->Record['name'] . $_titleSuffix, 'organizationtype' => $_groupType,
                    'address' => $this->DatabaseImport->Record['street'], 'city' => $this->DatabaseImport->Record['city'], 'state' => $this->DatabaseImport->Record['province'],
                    'postalcode' => $this->DatabaseImport->Record['postal'], 'country' => $this->DatabaseImport->Record['country'], 'phone' => $this->DatabaseImport->Record['phone'],
                    'website' => $this->DatabaseImport->Record['website'], 'dateline' => $this->DatabaseImport->Record['created']), 'INSERT');
            $_userOrganizationID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('userorganization', $this->DatabaseImport->Record['id'], $_userOrganizationID);
        }

//        SWIFT_UserGroup::RebuildCache();

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM contact_org");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 20;
    }
}

?>
