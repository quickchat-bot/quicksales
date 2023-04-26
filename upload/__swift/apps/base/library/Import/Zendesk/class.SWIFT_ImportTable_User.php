<?php

namespace Base\Library\Import\Zendesk;

use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use DOMDocument;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;

/**
 * Import Zendesk XML: User
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_User extends SWIFT_ImportTable
{
    var $dwk_zendesk_url = false;
    var $dwk_user_array = array();

    /**
     * Constructor
     *
     * @author Nicolás Ibarra Sabogal
     * @param SWIFT_ImportManager_Zendesk $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'User');

        $this->dwk_zendesk_url = $_SWIFT_ImportManagerObject->dwk_getZendeskUrl();

        $this->dwk_user_array = $this->dwk_getZendeskInformation();
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

//        if ($this->GetOffset() == 0)
//        {
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "users");
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "useremails");
//        }

        $_count = 0;

        $dwk_control = $this->GetOffset() + $this->GetItemsPerPass();

        if ($dwk_control > $this->GetTotal()) {
            $dwk_control = $this->GetTotal();
        }

        $_userContainer = $_userIDList = array();

        for ($index = $this->GetOffset(); $index < $dwk_control; $index++) {
            $_count++;

            $_userContainer[$this->dwk_user_array[$index]['id']] = $this->dwk_user_array[$index];
            $_userContainer[$this->dwk_user_array[$index]['id']]['emails'][] = $this->dwk_user_array[$index]['user_email'];
            $_userIDList[] = $this->dwk_user_array[$index]['id'];
        }


        foreach ($_userContainer as $_userID => $_user) {
//            Zendesk doesn't export the email of the users that are flagged as is-active = false, so I should put them as inactive
            if ($_user['enabled'] == "false") {
                $_user['isvalidated'] = 0;
            } else {
                $_user['isvalidated'] = 1;
            }

//            All the imported user are going to be on the Registered UserGroup
            $_userGroupID = 2;

//            // Try to fetch the user organization
            $_userOrganizationID = $this->ImportManager->GetImportRegistry()->GetKey('userorganization', $_user['userorganization']);
            if ($_userOrganizationID == false) {
                $_userOrganizationID = 0;
            }

            $this->GetImportManager()->AddToLog('Importing User: ' . text_to_html_entities($_user['fullname']), SWIFT_ImportManager::LOG_SUCCESS);

            $_userRole = SWIFT_User::ROLE_USER;

            $_userPassword = '';
            $_isLegacyPassword = false;
            if (!empty($_user['userpasswordtxt'])) {
                $_userPassword = SWIFT_User::GetComputedPassword($_user['userpasswordtxt']);
                $_isLegacyPassword = false;
            } else {
                $_userPassword = $_user['userpassword'];
                $_isLegacyPassword = true;
            }

            $_slaPlanID = $_slaPlanExpiry = 0;
            if ($_user['slaplanid'] != '0') {
                $_slaPlanID = $this->ImportManager->GetImportRegistry()->GetKey('slaplan', $_user['slaplanid']);

                $_slaPlanExpiry = (int)($_user['slaexpiry']);
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'users',
                array('usergroupid' => $_userGroupID, 'userrole' => $_userRole, 'userorganizationid' => $_userOrganizationID, 'salutation' => SWIFT_User::SALUTATION_NONE,
                    'fullname' => $_user['fullname'], 'phone' => $_user['phone'], 'userpassword' => $_userPassword, 'islegacypassword' => (int)($_isLegacyPassword),
                    'dateline' => $_user['dateline'], 'lastvisit' => $_user['lastvisit'], 'lastactivity' => $_user['lastvisit'], 'isvalidated' => $_user['isvalidated'],
                    'slaplanid' => $_slaPlanID, 'slaexpirytimeline' => $_slaPlanExpiry), 'INSERT');
            $_dwk_userID = $this->Database->InsertID();

            foreach ($_user['emails'] as $_emailAddress) {
                $this->Database->AutoExecute(TABLE_PREFIX . 'useremails',
                    array('linktype' => SWIFT_UserEmail::LINKTYPE_USER, 'linktypeid' => $_dwk_userID, 'email' => $_emailAddress), 'INSERT');
            }

            $this->ImportManager->GetImportRegistry()->UpdateKey('user', $_user['id'], $_dwk_userID, true);
        }

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

        $_countContainer['totalitems'] = count($this->dwk_user_array);

        return $_countContainer['totalitems'];

    }

    /**
     * Retrieve the total number of records in the zendesk xml
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

        return 100;
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

        $dwk_users_xml->load($dwk_temp_url . 'users.xml');
        $dwk_users = $dwk_users_xml->getElementsByTagName("user");

        $dwk_count = 0;
        $dwk_temp_array = array();

        foreach ($dwk_users as $dwk_user) {

//            Role = 0: End User
            if ($dwk_user->getElementsByTagName("roles")->item(0)->nodeValue == 0) {

                $dwk_temp_array[$dwk_count]['id'] = $dwk_user->getElementsByTagName("id")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['enabled'] = $dwk_user->getElementsByTagName("is-active")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['fullname'] = $dwk_user->getElementsByTagName("name")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['userpasswordtxt'] = "password";
                $dwk_temp_array[$dwk_count]['user_email'] = $dwk_user->getElementsByTagName("email")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['userorganization'] = $dwk_user->getElementsByTagName("organization-id")->item(0)->nodeValue;
                $dwk_temp_array[$dwk_count]['slaplanid'] = 0;
                $dwk_temp_array[$dwk_count]['phone'] = 0;
                $dwk_temp_array[$dwk_count]['dateline'] = DATENOW;
                $dwk_temp_array[$dwk_count]['lastvisit'] = DATENOW;

                $dwk_count++;
            }
        }

        return $dwk_temp_array;
    }
}

?>
