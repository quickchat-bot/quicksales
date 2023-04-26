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

use SWIFT;
use SWIFT_App;
use SWIFT_Database;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;

/**
 * The QuickSupport Version 3 Import Manager
 *
 * @author Varun Shoor
 */
class SWIFT_ImportManager_QuickSupport3 extends SWIFT_ImportManager
{
    public $DatabaseImport = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct('QuickSupport3', 'QuickSupport SupportSuite/eSupport/LiveResponse v3');
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

        if ($this->ImportRegistry->GetKey('options', 'importonlytr') != '1') {
            $_tableList[] = 'StaffGroup';

            $_tableList[] = 'Staff';
            $_tableList[] = 'Signature';

            $_tableList[] = 'Department';
            $_tableList[] = 'GroupAssign';
            $_tableList[] = 'StaffAssign';

            $_tableList[] = 'UserGroup';

            $_tableList[] = 'CustomFieldGroup';
            $_tableList[] = 'CustomField';
            $_tableList[] = 'CustomFieldOption';
            $_tableList[] = 'CustomFieldDepartmentLink';

            if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
                $_tableList[] = 'VisitorBans';
            }

            if (SWIFT_App::IsInstalled(APP_TICKETS)) {
                $_tableList[] = 'TicketStatus';
                $_tableList[] = 'TicketPriority';
                $_tableList[] = 'SLASchedule';
                $_tableList[] = 'SLAPlan';
                $_tableList[] = 'EscalationRule';
            }

            $_tableList[] = 'TemplateGroup';

            if (SWIFT_App::IsInstalled(APP_PARSER)) {
                $_tableList[] = 'ParserBan';
                $_tableList[] = 'Breakline';
                $_tableList[] = 'EmailQueue';
                $_tableList[] = 'QueueSignature';
                $_tableList[] = 'CatchAllRule';
                $_tableList[] = 'ParserRule';
                $_tableList[] = 'ParserRuleCriteria';
                $_tableList[] = 'ParserRuleAction';
            }

            if (SWIFT_App::IsInstalled(APP_NEWS)) {
                $_tableList[] = 'NewsSubscriber';
                $_tableList[] = 'News';
                $_tableList[] = 'NewsData';
            }

            $_tableList[] = 'User';

            if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
                $_tableList[] = 'CannedCategory';
                $_tableList[] = 'CannedCategoryRebuild';
                $_tableList[] = 'CannedResponse';
                $_tableList[] = 'CannedResponseData';
                $_tableList[] = 'Message';
                $_tableList[] = 'MessageData';
                $_tableList[] = 'ChatObject';
                $_tableList[] = 'ChatData';
                $_tableList[] = 'ChatTextData';
                $_tableList[] = 'ChatHit';
            }

            if (SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE)) {
                $_tableList[] = 'KBCategory';
                $_tableList[] = 'KBCategoryRebuild';
                $_tableList[] = 'KBArticle';
                $_tableList[] = 'KBArticleData';
                $_tableList[] = 'KBArticleLink';

                $_tableList[] = 'DownloadCategory';
                $_tableList[] = 'DownloadCategoryRebuild';
                $_tableList[] = 'DownloadItem';
                $_tableList[] = 'DownloadDesc';
            }

            if (SWIFT_App::IsInstalled(APP_TICKETS)) {
                $_tableList[] = 'Ticket';
                $_tableList[] = 'TicketPost';
                $_tableList[] = 'TicketMessageID';
                $_tableList[] = 'TicketDraft';
                $_tableList[] = 'TicketMergeLog';
                $_tableList[] = 'TicketEmail';
                $_tableList[] = 'EscalationPath';
                $_tableList[] = 'TicketRecipient';
                $_tableList[] = 'AuditLog';
                $_tableList[] = 'TicketNote';
                $_tableList[] = 'TicketTimeTrack';
                $_tableList[] = 'TicketFollowUp';
                $_tableList[] = 'Attachment';
                $_tableList[] = 'AttachmentChunk';
                $_tableList[] = 'TicketLabel';
                $_tableList[] = 'TicketLabelLink';
                $_tableList[] = 'TicketRebuild';

                $_tableList[] = 'PredefinedCategory';
                $_tableList[] = 'PredefinedCategoryRebuild';
                $_tableList[] = 'PredefinedReply';
                $_tableList[] = 'PredefinedReplyData';

                $_tableList[] = 'TicketFilter';
                $_tableList[] = 'TicketFilterField';
            }

            $_tableList[] = 'CustomFieldLink';
            $_tableList[] = 'CustomFieldValue';

            $_tableList[] = 'Comment';
            $_tableList[] = 'CommentData';
        }

        if (SWIFT_App::IsInstalled(APP_TROUBLESHOOTER)) {
            $_tableList[] = 'TroubleshooterCategory';
            $_tableList[] = 'TroubleshooterStep';
            $_tableList[] = 'TroubleshooterLink';
        }

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

        $_TabObject->Text('dbhost', 'Database Host', '', '127.0.0.1');
        $_TabObject->Text('dbname', 'Database Name', '', 'swift');
        $_TabObject->Text('dbusername', 'Database Username', '', 'root');
        $_TabObject->Password('dbpassword', 'Database Password', '', '');
        $_TabObject->Number('dbport', 'Database Port', '', '3306');
        $_TabObject->Text('dbsocket', 'Database Socket', '', '');

        $_TabObject->Title('Beta Options', 'icon_doublearrows.gif');
        $_TabObject->YesNo('importonlytr', 'Import only Troubleshooter', 'Use this option to Import ONLY the troubleshooter data. This option is to be used by users who started using the Beta whilst the Troubleshooter app was unavailable.', false);
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
            $_SWIFT_DatabaseObject = new SWIFT_Database(true, $_POST['dbhost'], $_POST['dbport'], $_POST['dbname'], $_POST['dbusername'], $_POST['dbpassword'], $_POST['dbsocket']);
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
        $this->ImportRegistry->UpdateKey('database', 'dbsocket', $_POST['dbsocket']);

        $this->ImportRegistry->UpdateKey('options', 'importonlytr', $_POST['importonlytr']);

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
    public function UpdateForm($_databaseHost, $_databaseName, $_databasePort, $_dbUsername, $_dbPassword, $_databaseSocket)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->ImportRegistry->UpdateKey('database', 'dbhost', $_databaseHost);
        $this->ImportRegistry->UpdateKey('database', 'dbport', $_databasePort);
        $this->ImportRegistry->UpdateKey('database', 'dbname', $_databaseName);
        $this->ImportRegistry->UpdateKey('database', 'dbusername', $_dbUsername);
        $this->ImportRegistry->UpdateKey('database', 'dbpassword', $_dbPassword);
        $this->ImportRegistry->UpdateKey('database', 'dbsocket', $_databaseSocket);

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
        $_dbSocket = $this->ImportRegistry->GetKey('database', 'dbsocket');

        try {
            $_SWIFT_DatabaseObject = new SWIFT_Database(true, $_dbHost, $_dbPort, $_dbName, $_dbUser, $_dbPassword, $_dbSocket);
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            SWIFT::Error('Database Connection Failed', 'Unable to connect to database using the provided details.');

            return false;
        }

        if (!$_SWIFT_DatabaseObject instanceof SWIFT_Database || !$_SWIFT_DatabaseObject->IsConnected()) {
            SWIFT::Error('Database Connection Failed', 'Unable to connect to database using the provided details.');

            return false;
        }

        $this->DatabaseImport = $_SWIFT_DatabaseObject;

        $_versionContainer = $this->DatabaseImport->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "settings WHERE vkey = 'version'");
        if (!isset($_versionContainer['data']) || empty($_versionContainer['data']) || version_compare($_versionContainer['data'], '3.60.00') == -1) {
            SWIFT::Error('Version Check Failed', 'Unable to import as minimum product version required is: 3.60.00, please upgrade your existing version 3 helpdesk to continue the import process.');

            return false;
        }
        /*
                for ($index = 0; $index < 10000; $index++) {
                    $this->DatabaseImport->AutoExecute(TABLE_PREFIX . 'users', array('usergroupid' => '2', 'fullname' => 'User: ' . $index, 'userpassword' => md5($index),
                        'userpasswordtxt' => $index, 'dateline' => DATENOW, 'enabled' => '1'), 'INSERT');
                }
        */
        return true;
    }
}

?>
