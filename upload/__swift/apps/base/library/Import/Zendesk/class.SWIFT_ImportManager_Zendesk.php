<?php

namespace Base\Library\Import\Zendesk;

use SWIFT;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;

/**
 * The Zendesk Import Manager
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportManager_Zendesk extends SWIFT_ImportManager
{
    private $dwk_zendeskUrlImport = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct('Zendesk', 'Zendesk');
    }

    public function dwk_getZendeskUrl()
    {
        return $this->dwk_zendeskUrlImport;
    }

    /**
     * Return the Import Tables
     *
     * @author Varun Shoor
     * @return array The Import Tables
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetImportTables()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableList = array();
        $_tableList[] = 'UserOrganization';
        $_tableList[] = 'User';
        $_tableList[] = 'Staff';
        $_tableList[] = 'Department';
        $_tableList[] = 'Ticket';

        return $_tableList;
    }

    /**
     * Render the Form
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceTab $_TabObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderForm(SWIFT_UserInterfaceTab $_TabObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::RenderForm($_TabObject);

        $_TabObject->Text('url', 'URL path to the xml file', 'E.g. www.yoursite.com/zendesk/', '');

        return true;
    }

    /**
     * Parse the form POST data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessForm()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::ProcessForm();

        $this->ImportRegistry->UpdateKey('zendeskurl', 'url', $_POST['url']);

        return true;
    }

    /**
     * Execute the code before importing starts
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ImportPre()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_dwk_zendeskUrl = $this->ImportRegistry->GetKey('zendeskurl', 'url');

//        Check if the tickets.xml exists on the server    TICKETS
        $dwk_filename = $_dwk_zendeskUrl . 'tickets.xml';

        $dwk_AgetHeaders = @get_headers($dwk_filename);
        if (preg_match("|200|", $dwk_AgetHeaders[0])) {
//            File Exists :)
        } else {
            SWIFT::Error('The file ' . $dwk_filename . ' doesn\'t exist', 'Unable to read the file using the provided url.');
            return false;
        }

//        Check if the organizations.xml exists on the server    USER GROUPS
        $dwk_filename = $_dwk_zendeskUrl . 'organizations.xml';

        $dwk_AgetHeaders = @get_headers($dwk_filename);
        if (preg_match("|200|", $dwk_AgetHeaders[0])) {
//            File Exists :)
        } else {
            SWIFT::Error('The file ' . $dwk_filename . ' doesn\'t exist', 'Unable to read the file using the provided url.');
            return false;
        }

//        Check if the users.xml exists on the server        USERS AND STAFFS
        $dwk_filename = $_dwk_zendeskUrl . 'users.xml';

        $dwk_AgetHeaders = @get_headers($dwk_filename);
        if (preg_match("|200|", $dwk_AgetHeaders[0])) {
//            File Exists :)
        } else {
            SWIFT::Error('The file ' . $dwk_filename . ' doesn\'t exist', 'Unable to read the file using the provided url.');
            return false;
        }

//        Check if the groups.xml exists on the server    DEPARTMENTS
        $dwk_filename = $_dwk_zendeskUrl . 'groups.xml';

        $dwk_AgetHeaders = @get_headers($dwk_filename);
        if (preg_match("|200|", $dwk_AgetHeaders[0])) {
//            File Exists :)
        } else {
            SWIFT::Error('The file ' . $dwk_filename . ' doesn\'t exist', 'Unable to read the file using the provided url.');
            return false;
        }

        $this->dwk_zendeskUrlImport = $_dwk_zendeskUrl;

        return true;
    }

}

?>
