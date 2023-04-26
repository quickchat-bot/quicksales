<?php

namespace Base\Library\Import\Zendesk;

use Base\Models\Staff\SWIFT_Staff;
use DOMDocument;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Zendesk XML: Staff
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Staff extends SWIFT_ImportTable
{
    var $dwk_zendesk_url = false;
    var $dwk_staff_array = array();

    /**
     * Constructor
     *
     * @author Nicolás Ibarra Sabogal
     * @param SWIFT_ImportManager_Zendesk $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'Staff');

        $this->dwk_zendesk_url = $_SWIFT_ImportManagerObject->dwk_getZendeskUrl();

        $this->dwk_staff_array = $this->dwk_getZendeskInformation();
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

        $_staffUsernameList = $_staffContainer = $_staffIDList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY staffid ASC");
        while ($this->Database->NextRecord()) {
            $_staffUsernameList[] = $this->Database->Record['username'];

            $_staffContainer[$this->Database->Record['staffid']] = $this->Database->Record;
            $_staffIDList[] = $this->Database->Record['staffid'];
        }

        // Cache the existing items
        if ($this->GetOffset() == 0) {
            $_existingStaffContainer = array();
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY staffid ASC");
            while ($this->Database->NextRecord()) {
                $_existingStaffContainer[$this->Database->Record['staffid']] = $this->Database->Record;
            }

            foreach ($_existingStaffContainer as $_eStaffContainer) {
                $this->ImportManager->GetImportRegistry()->UpdateKey('stafffullname', mb_strtolower(trim($_eStaffContainer['fullname'])), '1');
            }
        }


        $_count = 0;

        $dwk_control = $this->GetOffset() + $this->GetItemsPerPass();

        if ($dwk_control > $this->GetTotal()) {
            $dwk_control = $this->GetTotal();
        }

        for ($index = $this->GetOffset(); $index < $dwk_control; $index++) {
            $_count++;

//            Zendesk doesn't export the email of the users that are flagged as is-active = false, so I should put them as inactive
            if ($this->dwk_staff_array[$index]['enabled'] == "false") {
                $_isenabled = 0;
            } else {
                $_isenabled = 1;
            }

            $_titleSuffix = '';
            $_existingStaffFullName = $this->ImportManager->GetImportRegistry()->GetKey('stafffullname', mb_strtolower(trim($this->dwk_staff_array[$index]['fullname'])));

            // A record with same title exists?
            if ($_existingStaffFullName != false) {
                $_titleSuffix .= ' (Import)';
            }

            // Try to fetch the staff group
            $_staffGroupID = $this->dwk_staff_array[$index]['staffgroupid'];
            if ($_staffGroupID == false) {
//                Staff by default
                $_staffGroupID = 2;
            }

            $this->GetImportManager()->AddToLog('Importing Staff: ' . text_to_html_entities($this->dwk_staff_array[$index]['fullname']), SWIFT_ImportManager::LOG_SUCCESS);

            if (in_array($this->dwk_staff_array[$index]['username'], $_staffUsernameList)) {
                $this->dwk_staff_array[$index]['username'] .= '_imp' . substr(BuildHash(), 0, 4);

                $this->GetImportManager()->AddToLog('Imported "' . text_to_html_entities($this->dwk_staff_array[$index]['fullname']) . '" Username as "' . htmlspecialchars($this->dwk_staff_array[$index]['username']) . '" due to conflict with an existing staff username', SWIFT_ImportManager::LOG_WARNING);
            }

            $_staffFirstName = $_staffLastName = '';
            if (strpos($this->dwk_staff_array[$index]['fullname'], ' ')) {
                $_staffNameContainer = explode(' ', $this->dwk_staff_array[$index]['fullname']);
                $_staffFirstName = $_staffNameContainer[0];
                unset($_staffNameContainer[0]);

                $_staffLastName = implode(' ', $_staffNameContainer);
            } else {
                $_staffFirstName = $this->dwk_staff_array[$index]['fullname'];
            }

            if ($this->dwk_staff_array[$index]['timezonephp'] == '99') {
                $this->DatabaseImport->Record['timezonephp'] = 'GMT';
            }

            $_staffPassword = SWIFT_Staff::GetComputedPassword($this->dwk_staff_array[$index]['password']);

            $this->Database->AutoExecute(TABLE_PREFIX . 'staff',
                array('firstname' => $_staffFirstName, 'lastname' => $_staffLastName . $_titleSuffix, 'fullname' => $this->dwk_staff_array[$index]['fullname'] . $_titleSuffix,
                    'username' => $this->dwk_staff_array[$index]['username'], 'staffpassword' => $_staffPassword, 'islegacypassword' => '1',
                    'staffgroupid' => $_staffGroupID, 'email' => $this->dwk_staff_array[$index]['email'], 'mobilenumber' => $this->dwk_staff_array[$index]['mobilenumber'],
                    'groupassigns' => $this->dwk_staff_array[$index]['groupassigns'], 'timezonephp' => $this->dwk_staff_array[$index]['timezonephp'],
                    'lastvisit' => $this->dwk_staff_array[$index]['lastvisit'], 'lastvisit2' => $this->dwk_staff_array[$index]['lastvisit2'],
                    'lastactivity' => $this->dwk_staff_array[$index]['lastactivity'], 'isenabled' => $_isenabled), 'INSERT');
            $_staffID = $this->Database->InsertID();

//            Add the staff signature
            $this->Database->AutoExecute(TABLE_PREFIX . 'signatures', array('dateline' => DATENOW, 'staffid' => $_staffID, 'signature' => ReturnNone($this->dwk_staff_array[$index]['signature'])), 'INSERT');

            $this->ImportManager->GetImportRegistry()->UpdateKey('staff', $this->dwk_staff_array[$index]['id'], $_staffID);
        }

        SWIFT_Staff::RebuildCache();

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

        $_countContainer['totalitems'] = count($this->dwk_staff_array);

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
        $dwk_staffs_xml = new DOMDocument();

        $dwk_staffs_xml->load($dwk_temp_url . 'users.xml');
        $dwk_staffs = $dwk_staffs_xml->getElementsByTagName("user");

        $dwk_count = 0;
        $dwk_temp_array = array();

        foreach ($dwk_staffs as $dwk_staff) {

//            Role = 2: Administrator
//            Role = 4: Staff
            if ($dwk_staff->getElementsByTagName("roles")->item(0)->nodeValue == 2 || $dwk_staff->getElementsByTagName("roles")->item(0)->nodeValue == 4) {

                $dwk_temp_array[$dwk_count]['id'] = $dwk_staff->getElementsByTagName("id")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['enabled'] = $dwk_staff->getElementsByTagName("is-active")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['fullname'] = $dwk_staff->getElementsByTagName("name")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['password'] = "password";
                $dwk_temp_array[$dwk_count]['username'] = $dwk_staff->getElementsByTagName("email")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['email'] = $dwk_staff->getElementsByTagName("email")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['mobilenumber'] = 0;
                $dwk_temp_array[$dwk_count]['lastactivity'] = DATENOW;
                $dwk_temp_array[$dwk_count]['lastvisit'] = DATENOW;
                $dwk_temp_array[$dwk_count]['lastvisit2'] = DATENOW;
                $dwk_temp_array[$dwk_count]['timezonephp'] = 99;
                $dwk_temp_array[$dwk_count]['groupassigns'] = 0;
                $dwk_temp_array[$dwk_count]['signature'] = '';

                if ($dwk_staff->getElementsByTagName("roles")->item(0)->nodeValue == 2) {
//                       Administrator
                    $dwk_temp_array[$dwk_count]['staffgroupid'] = 1;
                } else {
//                       Staff
                    $dwk_temp_array[$dwk_count]['staffgroupid'] = 2;
                }
                $dwk_count++;
            }
        }

        return $dwk_temp_array;
    }
}

?>
