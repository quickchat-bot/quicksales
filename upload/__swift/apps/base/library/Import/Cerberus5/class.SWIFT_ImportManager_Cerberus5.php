<?php

namespace Base\Library\Import\Cerberus5;

use SWIFT;
use SWIFT_Database;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;

/**
 * Cerberus 5 Import Manager
 *
 * @author Carlos Orozco
 */
class SWIFT_ImportManager_Cerberus5 extends SWIFT_ImportManager
{
    public $DatabaseImport = false;

    /**
     * Constructor
     *
     * @author Carlos Orozco
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct('Cerberus5', 'Cerberus Helpdesk 5');
    }

    /**
     * Return the Import Tables
     *
     * @author Carlos Orozco
     * @return array The Import Tables
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetImportTables()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tableList = array();
        $_tableList[] = 'Worker_Role'; // Staff Group
        $_tableList[] = 'Team'; // Department
        $_tableList[] = 'Worker'; // Staff
        $_tableList[] = 'Worker_to_team'; // Staff Assign

        $_tableList[] = 'Contact_org'; // Organizations
        $_tableList[] = 'Address'; // Users

        $_tableList[] = 'Kb_category'; // KB Category
        $_tableList[] = 'Kb_article'; // KB Article
        $_tableList[] = 'Kb_article_to_category'; // KB Links

        $_tableList[] = 'Pop3_account'; // Email Queu

        $_tableList[] = 'Ticket'; // Tickets (Post & Notes)


        return $_tableList;
    }

    /**
     * Render the Form
     *
     * @author Carlos Orozco
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

        $_TabObject->Text('dbhost', 'Database Host', '', '');
        $_TabObject->Text('dbname', 'Database Name', '', '');
        $_TabObject->Text('dbusername', 'Database Username', '', '');
        $_TabObject->Password('dbpassword', 'Database Password', '', '');
        $_TabObject->Number('dbport', 'Database Port', '', '3306');

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

        $_SWIFT_DatabaseObject = false;

        try {
            $_SWIFT_DatabaseObject = new SWIFT_Database(true, $_POST['dbhost'], $_POST['dbport'], $_POST['dbname'], $_POST['dbusername'], $_POST['dbpassword']);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            SWIFT::Error('Database Connection Failed', 'Unable to connect to database using the provided details.');

            return false;
        }

        if (!$_SWIFT_DatabaseObject instanceof SWIFT_Database || !$_SWIFT_DatabaseObject->IsConnected()) {
            SWIFT::Error('Database Connection Failed', 'Unable to connect to database using the provided details.');

            return false;
        }

        $this->ImportRegistry->UpdateKey('database', 'dbhost', $_POST['dbhost']);
        $this->ImportRegistry->UpdateKey('database', 'dbport', $_POST['dbport']);
        $this->ImportRegistry->UpdateKey('database', 'dbname', $_POST['dbname']);
        $this->ImportRegistry->UpdateKey('database', 'dbusername', $_POST['dbusername']);
        $this->ImportRegistry->UpdateKey('database', 'dbpassword', $_POST['dbpassword']);

        return true;
    }

    /**
     * Update the Form Properties
     *
     * @author Varun Shoor
     * @param string $_databaseHost The Database Host
     * @param string $_databaseName The DB Name
     * @param string $_databasePort The Database Port
     * @param string $_dbUsername The DB Username
     * @param string $_dbPassword The DB Password
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateForm($_databaseHost, $_databaseName, $_databasePort, $_dbUsername, $_dbPassword)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ImportRegistry->UpdateKey('database', 'dbhost', $_databaseHost);
        $this->ImportRegistry->UpdateKey('database', 'dbport', $_databasePort);
        $this->ImportRegistry->UpdateKey('database', 'dbname', $_databaseName);
        $this->ImportRegistry->UpdateKey('database', 'dbusername', $_dbUsername);
        $this->ImportRegistry->UpdateKey('database', 'dbpassword', $_dbPassword);

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

        $_SWIFT_DatabaseObject = false;
        $_dbHost = $this->ImportRegistry->GetKey('database', 'dbhost');
        $_dbPort = $this->ImportRegistry->GetKey('database', 'dbport');
        $_dbName = $this->ImportRegistry->GetKey('database', 'dbname');
        $_dbUser = $this->ImportRegistry->GetKey('database', 'dbusername');
        $_dbPassword = $this->ImportRegistry->GetKey('database', 'dbpassword');

        try {
            $_SWIFT_DatabaseObject = new SWIFT_Database(true, $_dbHost, $_dbPort, $_dbName, $_dbUser, $_dbPassword);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            SWIFT::Error('Database Connection Failed', 'Unable to connect to database using the provided details.');

            return false;
        }

        if (!$_SWIFT_DatabaseObject instanceof SWIFT_Database || !$_SWIFT_DatabaseObject->IsConnected()) {
            SWIFT::Error('Database Connection Failed', 'Unable to connect to database using the provided details.');

            return false;
        }

        $this->DatabaseImport = $_SWIFT_DatabaseObject;

        return true;
    }
}

?>
