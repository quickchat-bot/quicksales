<?php

namespace Base\Library\Import\Zendesk;

use Base\Models\User\SWIFT_UserOrganization;
use DOMDocument;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Zendesk XML: UserGroup
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_UserOrganization extends SWIFT_ImportTable
{
    var $dwk_zendesk_url = false;
    var $dwk_userorganization_array = array();

    /**
     * Constructor
     *
     * @author Nicolás Ibarra Sabogal
     * @param SWIFT_ImportManager_Zendesk $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'UserOrganization');

        $this->dwk_zendesk_url = $_SWIFT_ImportManagerObject->dwk_getZendeskUrl();

        $this->dwk_userorganization_array = $this->dwk_getZendeskInformation();
    }

    /**
     * Import the data based on offset in the zendesk xml
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

        $dwk_control = $this->GetOffset() + $this->GetItemsPerPass();

        if ($dwk_control > $this->GetTotal()) {
            $dwk_control = $this->GetTotal();
        }

        for ($index = $this->GetOffset(); $index < $dwk_control; $index++) {
            $_count++;

            $_titleSuffix = '';
            $_existingUserOrganizationTitle = $this->ImportManager->GetImportRegistry()->GetKey('organizationname', mb_strtolower(trim($this->dwk_userorganization_array[$index]['title'])));

            // A record with same title exists?
            if ($_existingUserOrganizationTitle != false) {
                $_titleSuffix .= ' (Import)';
            }

            $this->GetImportManager()->AddToLog('Importing User Organization: ' . htmlspecialchars($this->dwk_userorganization_array[$index]['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $_groupType = SWIFT_UserOrganization::TYPE_RESTRICTED;

            $this->Database->AutoExecute(TABLE_PREFIX . 'userorganizations', array('organizationname' => $this->dwk_userorganization_array[$index]['title'] . $_titleSuffix, 'organizationtype' => $_groupType, 'dateline' => DATENOW), 'INSERT');
            $_userOrganizationID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('userorganization', $this->dwk_userorganization_array[$index]['id'], $_userOrganizationID);
        }

//        SWIFT_UserGroup::RebuildCache();

        return $_count;
    }

    /**
     * Retrieve the total number of records in the zendesk xml
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

        $_countContainer['totalitems'] = count($this->dwk_userorganization_array);

        return $_countContainer['totalitems'];
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

    /**
     * Load the Zendesk XML into an array
     *
     * @author Nicolás Ibarra Sabogal
     * @return array All the Zendesk information that into the XML
     */
    public function dwk_getZendeskInformation()
    {

        $dwk_temp_url = $this->dwk_zendesk_url;
        $dwk_usergroups_xml = new DOMDocument();

        $dwk_usergroups_xml->load($dwk_temp_url . 'organizations.xml');
        $dwk_usergroups = $dwk_usergroups_xml->getElementsByTagName("organization");

        $dwk_count = 0;
        $dwk_temp_array = array();

        foreach ($dwk_usergroups as $dwk_usergroup) {

            $dwk_temp_array[$dwk_count]['id'] = $dwk_usergroup->getElementsByTagName("id")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]['title'] = $dwk_usergroup->getElementsByTagName("name")->item(0)->nodeValue;

            $dwk_count++;
        }

        return $dwk_temp_array;
    }
}

?>
