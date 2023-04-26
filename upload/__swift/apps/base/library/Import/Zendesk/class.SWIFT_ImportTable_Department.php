<?php

namespace Base\Library\Import\Zendesk;

use Base\Models\Department\SWIFT_Department;
use DOMDocument;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Zendesk XML: Department
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Department extends SWIFT_ImportTable
{

    var $dwk_zendesk_url = false;
    var $dwk_department_array = array();

    /**
     * Constructor
     *
     * @author Nicolás Ibarra Sabogal
     * @param SWIFT_ImportManager_Zendesk $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'Department');

        $this->dwk_zendesk_url = $_SWIFT_ImportManagerObject->dwk_getZendeskUrl();

        $this->dwk_department_array = $this->dwk_getZendeskInformation();
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
            $_existingDepartmentContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "departments ORDER BY departmentid ASC");
            while ($this->Database->NextRecord()) {
                $_existingDepartmentContainer[$this->Database->Record['departmentid']] = $this->Database->Record;
            }

            foreach ($_existingDepartmentContainer as $_departmentContainer) {
                $this->ImportManager->GetImportRegistry()->UpdateKey('departmenttitle', mb_strtolower(trim($_departmentContainer['title'])), '1');
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
            $_existingDepartmentTitle = $this->ImportManager->GetImportRegistry()->GetKey('departmenttitle', mb_strtolower(trim($this->dwk_department_array[$index]['title'])));

            // A record with same title exists?
            if ($_existingDepartmentTitle != false) {
                $_titleSuffix .= ' (Import)';
            }

            if ($this->dwk_department_array[$index]['departmentapp'] == 'livesupport') {
                $this->dwk_department_array[$index]['departmentapp'] = 'livechat';
            }

            $this->GetImportManager()->AddToLog('Importing Department: ' . text_to_html_entities($this->dwk_department_array[$index]['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'departments',
                array('title' => $this->dwk_department_array[$index]['title'] . $_titleSuffix, 'departmenttype' => $this->dwk_department_array[$index]['departmenttype'],
                    'departmentapp' => $this->dwk_department_array[$index]['departmentapp'], 'displayorder' => $this->dwk_department_array[$index]['displayorder']), 'INSERT');
            $_departmentID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('department', $this->dwk_department_array[$index]['id'], $_departmentID);
        }

        SWIFT_Department::RebuildCache();

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

        $_countContainer['totalitems'] = count($this->dwk_department_array);

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
        $dwk_users_xml = new DOMDocument();

        $dwk_users_xml->load($dwk_temp_url . 'groups.xml');
        $dwk_users = $dwk_users_xml->getElementsByTagName("group");

        $dwk_count = 0;
        $dwk_temp_array = array();

        foreach ($dwk_users as $dwk_user) {

            $dwk_temp_array[$dwk_count]['id'] = $dwk_user->getElementsByTagName("id")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]['title'] = $dwk_user->getElementsByTagName("name")->item(0)->nodeValue;
            $dwk_temp_array[$dwk_count]['departmenttype'] = 'public';
            $dwk_temp_array[$dwk_count]['departmentapp'] = 'tickets';
            $dwk_temp_array[$dwk_count]['displayorder'] = $dwk_count + 1;

            $dwk_count++;
        }

        return $dwk_temp_array;
    }
}

?>
