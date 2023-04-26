<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridField;
use Base\Library\UserInterface\SWIFT_UserInterfaceGridMassAction;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\Department\SWIFT_Department;
use SWIFT;
use SWIFT_App;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use Parser\Models\EmailQueue\SWIFT_EmailQueueMailbox;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType;
use SWIFT_Exception;
use SWIFT_View;

/**
 * The Email Queue View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_EmailQueue extends SWIFT_View
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Render the Email Queue Form
     *
     * @author Varun Shoor
     *
     * @param int    $_mode                   The Render Mode
     * @param SWIFT_EmailQueue $_SWIFT_EmailQueueObject The Parser\Models\EmailQueue\SWIFT_EmailQueue Object Pointer (Only for EDIT Mode)
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render($_mode, SWIFT_EmailQueue $_SWIFT_EmailQueueObject = null, $_postStep = 1)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Cache->Queue('departmentcache', 'statuscache', 'tickettypecache', 'prioritycache');
        $this->Cache->LoadQueue();

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_statusCache = $this->Cache->Get('statuscache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_priorityCache = $this->Cache->Get('prioritycache');

        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Start(get_short_class($this), '/Parser/EmailQueue/EditSubmit/' . $_SWIFT_EmailQueueObject->GetEmailQueueID(),
                SWIFT_UserInterface::MODE_EDIT, false);
        } else {
            if ($_postStep == 1) {
                $this->UserInterface->Start(get_short_class($this), '/Parser/EmailQueue/InsertStep', SWIFT_UserInterface::MODE_INSERT, false);
            } else {
                $this->UserInterface->Start(get_short_class($this), '/Parser/EmailQueue/InsertSubmit', SWIFT_UserInterface::MODE_INSERT, false);
            }
        }

        $_emailQueueAddress = '';
        $_emailQueueType = SWIFT_EmailQueueType::TYPE_TICKETS;
        $_emailQueueFetchType = SWIFT_EmailQueue::FETCH_PIPE;
        $_emailQueueTemplateGroupID = false;
        $_emailQueueIsEnabled = true;

        $_emailQueueCustomFromName = $_emailQueueCustomFromEmail = '';

        $_emailQueueTicketAutoresponder = true;
        $_emailQueueRegistrationRequired = false;

        $_emailQueuePrefix = '';
        $_emailQueueSignature = '';

        $_emailQueueHost = '';

        $_emailQueuePort = '';

        $_emailQueueUsername = '';
        $_emailQueuePassword = '';

        $_emailQueueForceQueue = true;

        $_emailQueueLeaveCopyOnServer = false;
        $_emailQueueUseQueueSMTP = false;

        $_emailQueueDepartmentID = $_emailQueueTicketStatusID = $_emailQueueTicketTypeID = $_emailQueueTicketPriorityID = false;

        $_emailQueueAuthType = $_emailQueueClientId = $_emailQueueClientSecret = $_emailQueueAuthEndpoint = '';
        $_emailQueueTokenEndpoint = $_emailQueueAuthScopes = $_emailQueueAccessToken = $_emailQueueRefreshToken = '';
        $_emailQueueTokenExpiry = 0;
        $_emailQueueSMTPPort = $_emailQueueSMTPHost = '';
        
        $_emailQueueSMTPType = SWIFT_EmailQueueMailbox::SMTP_NONSSL;

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');

            $this->UserInterface->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Parser/EmailQueue/Delete/' .
                $_SWIFT_EmailQueueObject->GetEmailQueueID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parseremailqueue'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

            $_emailQueueAddress = $_SWIFT_EmailQueueObject->GetProperty('email');
            $_emailQueueType = $_SWIFT_EmailQueueObject->GetProperty('type');
            $_emailQueueFetchType = $_SWIFT_EmailQueueObject->GetProperty('fetchtype');
            $_emailQueueTemplateGroupID = (int)($_SWIFT_EmailQueueObject->GetProperty('tgroupid'));
            $_emailQueueIsEnabled = (int)($_SWIFT_EmailQueueObject->GetProperty('isenabled'));

            $_emailQueueCustomFromName = $_SWIFT_EmailQueueObject->GetProperty('customfromname');
            $_emailQueueCustomFromEmail = $_SWIFT_EmailQueueObject->GetProperty('customfromemail');

            $_emailQueuePrefix = $_SWIFT_EmailQueueObject->GetProperty('prefix');

            $_emailQueueTicketAutoresponder = (int)($_SWIFT_EmailQueueObject->GetProperty('ticketautoresponder'));
            $_emailQueueRegistrationRequired = (int)($_SWIFT_EmailQueueObject->GetProperty('registrationrequired'));

            $_emailQueueSignature = $_SWIFT_EmailQueueObject->GetSignature();

            $_emailQueueHost = $_SWIFT_EmailQueueObject->GetProperty('host');
            $_emailQueuePort = (int)($_SWIFT_EmailQueueObject->GetProperty('port'));
            $_emailQueueAuthType = $_SWIFT_EmailQueueObject->GetProperty('authtype');
            $_emailQueueUsername = $_SWIFT_EmailQueueObject->GetProperty('username');
            $_emailQueuePassword = $_SWIFT_EmailQueueObject->GetProperty('userpassword');
            $_emailQueueClientId = $_SWIFT_EmailQueueObject->GetProperty('clientid');
            $_emailQueueClientSecret = $_SWIFT_EmailQueueObject->GetProperty('clientsecret');
            $_emailQueueAuthEndpoint = $_SWIFT_EmailQueueObject->GetProperty('authendpoint');
            $_emailQueueTokenEndpoint = $_SWIFT_EmailQueueObject->GetProperty('tokenendpoint');
            $_emailQueueAuthScopes = $_SWIFT_EmailQueueObject->GetProperty('authscopes');
            $_emailQueueAccessToken = $_SWIFT_EmailQueueObject->GetProperty('accesstoken');
            $_emailQueueRefreshToken = $_SWIFT_EmailQueueObject->GetProperty('refreshtoken');
            $_emailQueueTokenExpiry = $_SWIFT_EmailQueueObject->GetProperty('tokenexpiry');

            $_emailQueueLeaveCopyOnServer = (int)($_SWIFT_EmailQueueObject->GetProperty('leavecopyonserver'));
            $_emailQueueUseQueueSMTP = (int)($_SWIFT_EmailQueueObject->GetProperty('usequeuesmtp'));
            $_emailQueueSMTPHost = $_SWIFT_EmailQueueObject->GetProperty('smtphost');
            $_emailQueueSMTPPort = $_SWIFT_EmailQueueObject->GetProperty('smtpport');
            $_emailQueueUseSMTPType = (int)($_SWIFT_EmailQueueObject->GetProperty('smtptype'));
            $_emailQueueForceQueue = (int)($_SWIFT_EmailQueueObject->GetProperty('forcequeue'));

            $_emailQueueDepartmentID = (int)($_SWIFT_EmailQueueObject->GetProperty('departmentid'));
            $_emailQueueTicketStatusID = (int)($_SWIFT_EmailQueueObject->GetProperty('ticketstatusid'));
            $_emailQueueTicketTypeID = (int)($_SWIFT_EmailQueueObject->GetProperty('tickettypeid'));
            $_emailQueueTicketPriorityID = (int)($_SWIFT_EmailQueueObject->GetProperty('priorityid'));

            $_emailQueueSMTPType = $_SWIFT_EmailQueueObject->GetProperty('smtptype');

        } else {
            if ($_postStep == 1) {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('next'), 'fa-chevron-circle-right ');
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('verifyconnection'), 'icon_verifyconnection.png',
                    'VerifyParserConnection(\'' . addslashes(htmlspecialchars($this->Language->Get('verifyconnection'))) . '\');',
                    SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
            } else {
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('insert'), 'fa-plus-circle');
                $this->UserInterface->Toolbar->AddButton($this->Language->Get('back'), 'fa-chevron-circle-left', '/Parser/EmailQueue/Insert',
                    SWIFT_UserInterfaceToolbar::LINK_FORM);
            }
            $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parseremailqueue'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
        }


        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        if ($_postStep == 1) {
            $_GeneralTabObject->Title($this->Language->Get('emailgeneralfields'), 'doublearrows.gif');

            $_GeneralTabObject->Text('email', $this->Language->Get('emailaddress'), $this->Language->Get('desc_emailaddress'), $_emailQueueAddress);

            $_optionsContainer = array();

            $_index = 0;

            if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
                foreach (array(SWIFT_EmailQueueType::TYPE_TICKETS, SWIFT_EmailQueueType::TYPE_BACKEND) as
                         $_key => $_val) {
                    if (SWIFT_App::IsInstalled($_val)) {
                        $_optionsContainer[$_index]['title'] = $this->Language->Get('app_' . $_val);
                        $_optionsContainer[$_index]['value'] = $_val;
                        if ($_emailQueueType == $_val) {
                            $_optionsContainer[$_index]['selected'] = true;
                        }

                        $_index++;
                    }
                }
                $_GeneralTabObject->Select('type', $this->Language->Get('emailtype'), $this->Language->Get('desc_emailtype'), $_optionsContainer);
            } else {
                $_GeneralTabObject->DefaultDescriptionRow($this->Language->Get('emailtype'), $this->Language->Get('desc_emailtype'),
                    $this->Language->Get('app_' . $_emailQueueType));
                $this->UserInterface->Hidden('type', $_emailQueueType);
            }


            $_optionsContainer = array();
            $_index = 0;

            foreach (array(SWIFT_EmailQueue::FETCH_PIPE, SWIFT_EmailQueue::FETCH_POP3, SWIFT_EmailQueue::FETCH_POP3SSL,
                         SWIFT_EmailQueue::FETCH_POP3TLS, SWIFT_EmailQueue::FETCH_IMAP, SWIFT_EmailQueue::FETCH_IMAPSSL,
                         SWIFT_EmailQueue::FETCH_IMAPTLS) as $_key => $_val) {
                $_optionsContainer[$_index]['title'] = $this->Language->Get('fetch' . $_val);
                $_optionsContainer[$_index]['value'] = $_val;
                if ($_val == $_emailQueueFetchType) {
                    $_optionsContainer[$_index]['selected'] = true;
                }
                $_index++;
            }

            $_GeneralTabObject->Select('fetchtype', $this->Language->Get('emailfetchtype'), $this->Language->Get('desc_emailfetchtype'),
                $_optionsContainer, "javascript: SwitchParserFields(true);");

            $_optionsContainer = array();
            $_index = 0;
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups ORDER BY title ASC");
            while ($this->Database->NextRecord()) {
                $_optionsContainer[$_index]['title'] = htmlspecialchars($this->Database->Record['title']);
                $_optionsContainer[$_index]['value'] = (int)($this->Database->Record['tgroupid']);

                if ($_emailQueueTemplateGroupID == false || $_emailQueueTemplateGroupID == $this->Database->Record['tgroupid']) {
                    $_optionsContainer[$_index]['selected'] = true;

                    $_emailQueueTemplateGroupID = (int)($this->Database->Record['tgroupid']);
                }

                $_index++;
            }
            $_GeneralTabObject->Select('templategroupid', $this->Language->Get('templategroup'), $this->Language->Get('desc_templategroup'),
                $_optionsContainer);

            $_GeneralTabObject->YesNo('isenabled', $this->Language->Get('queueisenabled'), $this->Language->Get('desc_queueisenabled'),
                $_emailQueueIsEnabled);

            if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                $_TabObject = $this->UserInterface->AddTab($this->Language->Get('tabpop3imap'), 'icon_settings2.gif', 'pop3settings');
                $_TabObject->LoadToolbar();

                $_TabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
                $_TabObject->Toolbar->AddButton($this->Language->Get('verifyconnection'), 'icon_verifyconnection.png', 'VerifyParserConnection(\'' .
                    addslashes(htmlspecialchars($this->Language->Get('verifyconnection'))) . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
                $_TabObject->Toolbar->AddButton($this->Language->Get('delete'), 'fa-trash', '/Parser/EmailQueue/Delete/' .
                    $_SWIFT_EmailQueueObject->GetEmailQueueID(), SWIFT_UserInterfaceToolbar::LINK_CONFIRM, '', '', false);
                $_TabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parseremailqueue'),
                    SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);
            } else {
                $_TabObject = $_GeneralTabObject;
            }

            $_TabObject->Title($this->Language->Get('emailimapfields'), 'doublearrows.gif');

            $_TabObject->Text('host', $this->Language->Get('emailhost'), $this->Language->Get('desc_emailhost'), $_emailQueueHost);
            $_TabObject->Number("port", $this->Language->Get('emailport'), $this->Language->Get('desc_emailport'), $_emailQueuePort);

            $_authOptionsContainer = array(
                0 => array(
                    'title' => $this->Language->Get('authtype_basic'),
                    'value' => 'basic',
                    'selected' => ($_emailQueueAuthType == 'basic' ? true : false)
                ),
                1 => array(
                    'title' => $this->Language->Get('authtype_oauth'),
                    'value' => 'oauth',
                    'selected' => ($_emailQueueAuthType == 'oauth' ? true : false)
                ),                
            );

            $_TabObject->Select('authtype', $this->Language->Get('authtype'), $this->Language->Get('desc_authtype'), $_authOptionsContainer, 'changeEmailQueueAuthType()');

            $_TabObject->Text('username', $this->Language->Get('emailusername'), $this->Language->Get('desc_emailusername'), $_emailQueueUsername);
            $_TabObject->Text('userpassword', $this->Language->Get('emailpassword'), $this->Language->Get('desc_emailpassword'), $_emailQueuePassword, 'password', '20', 0, '', 'swifttext');

            $_TabObject->Text('authclientid', $this->Language->Get('authclientid'), $this->Language->Get('desc_authclientid'), $_emailQueueClientId);
            $_TabObject->Text('authclientsecret', $this->Language->Get('authclientsecret'), $this->Language->Get('desc_authclientsecret'), $_emailQueueClientSecret);
            $_TabObject->Text('authendpoint', $this->Language->Get('authendpoint'), $this->Language->Get('desc_authendpoint'), $_emailQueueAuthEndpoint);
            $_TabObject->Text('tokenendpoint', $this->Language->Get('tokenendpoint'), $this->Language->Get('desc_tokenendpoint'), $_emailQueueTokenEndpoint);
            $_TabObject->Text('authscope', $this->Language->Get('authscope'), $this->Language->Get('desc_authscope'), $_emailQueueAuthScopes);
            $_TabObject->Hidden('accesstoken', $_emailQueueAccessToken);
            $_TabObject->Hidden('refreshtoken', $_emailQueueRefreshToken);
            $_TabObject->Hidden('tokenexpiry', $_emailQueueTokenExpiry);

            $_TabObject->YesNo('forcequeue', $this->Language->Get('forcequeue'), $this->Language->Get('desc_forcequeue'),
                $_emailQueueForceQueue);

            $_TabObject->YesNo('leavecopyonserver', $this->Language->Get('leavecopyonserver'), $this->Language->Get('desc_leavecopyonserver'),
                $_emailQueueLeaveCopyOnServer);
            $_TabObject->YesNo('usequeuesmtp', $this->Language->Get('usequeuesmtp'), $this->Language->Get('desc_usequeuesmtp'),
                $_emailQueueUseQueueSMTP, 'toggleUseQueueSMTP()');

            $_TabObject->Text('smtphost', $this->Language->Get('smtphost'), $this->Language->Get('desc_smtphost'), $_emailQueueSMTPHost);
            $_TabObject->Text('smtpport', $this->Language->Get('smtpport'), $this->Language->Get('desc_smtpport'), $_emailQueueSMTPPort);
            
            $_optionsContainer = array();

            $_index = 0;

            foreach (array(SWIFT_EmailQueueMailbox::SMTP_NONSSL, SWIFT_EmailQueueMailbox::SMTP_TLS, SWIFT_EmailQueueMailbox::SMTP_SSL,
                         SWIFT_EmailQueueMailbox::SMTP_SSLV2, SWIFT_EmailQueueMailbox::SMTP_SSLV3) as $_key => $_val) {
                $_optionsContainer[$_index]['title'] = $this->Language->Get($_val);
                $_optionsContainer[$_index]['value'] = $_val;

                if ($_emailQueueSMTPType == $_val) {
                    $_optionsContainer[$_index]['selected'] = true;
                }

                $_index++;
            }

            $_TabObject->Select('smtptype', $this->Language->Get('smtptype'), $this->Language->Get('desc_smtptype'), $_optionsContainer);

            $_TabObject->AppendHTML('<script>changeEmailQueueAuthType(); toggleUseQueueSMTP();</script>');
            /*
             * ###############################################
             * END GENERAL TAB
             * ###############################################
             */


            /*
             * ###############################################
             * BEGIN SETTINGS TAB
             * ###############################################
             */

            $_SettingsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabsettings'), 'icon_settings2.gif', 'settings');
            $_SettingsTabObject->Title($this->Language->Get('queueoverrides'), 'doublearrows.gif');

            $_SettingsTabObject->Text('customfromname', $this->Language->Get('queuefromname'), $this->Language->Get('desc_queuefromname'),
                $_emailQueueCustomFromName);
            $_SettingsTabObject->Text('customfromemail', $this->Language->Get('queuefromemail'), $this->Language->Get('desc_queuefromemail'),
                $_emailQueueCustomFromEmail);


            $_SettingsTabObject->Title($this->Language->Get('queuesettings'), 'doublearrows.gif');
            $_SettingsTabObject->Text('prefix', $this->Language->Get('queueprefix'), $this->Language->Get('desc_queueprefix'), $_emailQueuePrefix);

            $_SettingsTabObject->Textarea('signature', $this->Language->Get('queuesignature'), $this->Language->Get('desc_queuesignature'),
                $_emailQueueSignature, '50');


            /*
             * ###############################################
             * END SETTINGS TAB
             * ###############################################
             */
        }

        if (($_mode == SWIFT_UserInterface::MODE_EDIT && $_emailQueueType == SWIFT_EmailQueueType::TYPE_TICKETS) ||
            ($_mode == SWIFT_UserInterface::MODE_INSERT && $_postStep == 2 && $_POST['type'] == SWIFT_EmailQueueType::TYPE_TICKETS)) {
            if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
                $_TabObject = $this->UserInterface->AddTab($this->Language->Get('tabticketsettings'), 'icon_settings2.gif', 'tabticketsettings');
            } else {
                $_TabObject = $_GeneralTabObject;
            }

            $_TabObject->Title($this->Language->Get('ticketfields'), 'doublearrows.gif');

            // Departments
            if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
                $_defaultDepartmentContainer = $this->Database->QueryFetch("SELECT departmentid FROM " . TABLE_PREFIX .
                    "departments WHERE departmentapp = '" . APP_TICKETS . "' ORDER BY title ASC");
                $_emailQueueDepartmentID = $_defaultDepartmentContainer['departmentid'];
            }
            $_optionsContainer = array();
            $_index = 0;

            $_optionsContainer = SWIFT_Department::GetDepartmentMapOptions($_emailQueueDepartmentID, APP_TICKETS);

            $_TabObject->Select('departmentid', $this->Language->Get('queuedepartment'), $this->Language->Get('desc_queuedepartment'),
                $_optionsContainer, 'javascript: UpdateTicketStatusDiv(this, \'ticketstatusid\', false, false); UpdateTicketTypeDiv(this, \'tickettypeid\', false, false);');

            // Type
            $_optionsContainer = array();
            $_index = 0;

            if (_is_array($_ticketTypeCache)) {
                foreach ($_ticketTypeCache as $_key => $_val) {
                    if ($_val['departmentid'] == '0' || $_val['departmentid'] == $_emailQueueDepartmentID) {
                        $_optionsContainer[$_index]['title'] = $_val['title'];
                        $_optionsContainer[$_index]['value'] = $_val['tickettypeid'];

                        if (($_emailQueueTicketTypeID == false && $_index == 0) || $_emailQueueTicketTypeID == $_val['tickettypeid']) {
                            $_optionsContainer[$_index]['selected'] = true;
                        }

                        $_index++;
                    }
                }
            }
            $_TabObject->Select('tickettypeid', $this->Language->Get('queuetickettype'), $this->Language->Get('desc_queuetickettype'),
                $_optionsContainer, '', 'tickettypeid_container');

            // Status
            $_optionsContainer = array();
            $_index = 0;

            if (_is_array($_statusCache)) {
                foreach ($_statusCache as $_key => $_val) {
                    if ($_val['departmentid'] == '0' || $_val['departmentid'] == $_emailQueueDepartmentID) {
                        $_optionsContainer[$_index]['title'] = $_val['title'];
                        $_optionsContainer[$_index]['value'] = $_val['ticketstatusid'];

                        if (($_emailQueueTicketStatusID == false && $_index == 0) || $_emailQueueTicketStatusID == $_val['ticketstatusid']) {
                            $_optionsContainer[$_index]['selected'] = true;
                        }

                        $_index++;
                    }
                }
            }
            $_TabObject->Select('ticketstatusid', $this->Language->Get('queueticketstatus'), $this->Language->Get('desc_queueticketstatus'),
                $_optionsContainer, '', 'ticketstatusid_container');

            // Priority
            $_optionsContainer = array();
            $_index = 0;
            if (_is_array($_priorityCache)) {
                foreach ($_priorityCache as $_key => $_val) {
                    $_optionsContainer[$_index]['title'] = $_val['title'];
                    $_optionsContainer[$_index]['value'] = $_val['priorityid'];

                    if (($_emailQueueTicketPriorityID == false && $_index == 0) || $_emailQueueTicketPriorityID == $_val['priorityid']) {
                        $_optionsContainer[$_index]['selected'] = true;
                    }

                    $_index++;
                }
            }

            $_TabObject->Select('ticketpriorityid', $this->Language->Get('queuepriority'), $this->Language->Get('desc_queuepriority'),
                $_optionsContainer);

            $_TabObject->YesNo('registrationrequired', $this->Language->Get('registrationrequired'),
                $this->Language->Get('desc_registrationrequired'), $_emailQueueRegistrationRequired);
            $_TabObject->YesNo('ticketautoresponder', $this->Language->Get('issueautoresponder'),
                $this->Language->Get('desc_issueautoresponder'), $_emailQueueTicketAutoresponder);
        }

        if ($_mode == SWIFT_UserInterface::MODE_INSERT && $_postStep == 2) {
            $this->UserInterface->Hidden('email', $_POST['email']);
            $this->UserInterface->Hidden('type', $_POST['type']);
            $this->UserInterface->Hidden('fetchtype', $_POST['fetchtype']);

            if (isset($_POST['host']) && isset($_POST['port'])) {
                $this->UserInterface->Hidden('host', $_POST['host']);
                $this->UserInterface->Hidden('port', $_POST['port']);
            }

            if (isset($_POST['authtype'])) {
                $this->UserInterface->Hidden('authtype', $_POST['authtype']);
                $this->UserInterface->Hidden('username', $_POST['username']);
                if($_POST['authtype'] == 'basic') {
                    $this->UserInterface->Hidden('userpassword', $_POST['userpassword']);    
                } else if ($_POST['authtype'] == 'oauth') {
                    $this->UserInterface->Hidden('authclientid', $_POST['authclientid']);
                    $this->UserInterface->Hidden('authclientsecret', $_POST['authclientsecret']);    
                    $this->UserInterface->Hidden('authendpoint', $_POST['authendpoint']);    
                    $this->UserInterface->Hidden('tokenendpoint', $_POST['tokenendpoint']);    
                    $this->UserInterface->Hidden('authscope', $_POST['authscope']);    
                }
            }

            if (isset($_POST['forcequeue']) && isset($_POST['leavecopyonserver']) && isset($_POST['usequeuesmtp']) && isset($_POST['smtptype'])) {
                $this->UserInterface->Hidden('forcequeue', $_POST['forcequeue']);
                $this->UserInterface->Hidden('leavecopyonserver', $_POST['leavecopyonserver']);
                $this->UserInterface->Hidden('usequeuesmtp', $_POST['usequeuesmtp']);
                $this->UserInterface->Hidden('smtptype', $_POST['smtptype']);
            }

            $this->UserInterface->Hidden('customfromname', $_POST['customfromname']);
            $this->UserInterface->Hidden('customfromemail', $_POST['customfromemail']);
            $this->UserInterface->Hidden('signature', $_POST['signature']);
            $this->UserInterface->Hidden('templategroupid', $_POST['templategroupid']);
            $this->UserInterface->Hidden('prefix', $_POST['prefix']);
            $this->UserInterface->Hidden('isenabled', $_POST['isenabled']);
        }

        $_GeneralTabObject->PrependHTML('<script type="text/javascript">QueueFunction(function(){ SwitchParserFields(false); });</script>');

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Parser Queue Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderGrid()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('UserInterface:UserInterfaceGrid', array('emailqueuegrid'), true, false, 'base');

        if ($this->UserInterfaceGrid->GetMode() == SWIFT_UserInterfaceGrid::MODE_SEARCH) {
            $this->UserInterfaceGrid->SetSearchQuery('SELECT * FROM ' . TABLE_PREFIX . 'emailqueues AS emailqueues WHERE (' .
                $this->UserInterfaceGrid->BuildSQLSearch('email', true) . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('type') .
                ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('host') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('username') .
                ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('customfromname') . ') OR (' .
                $this->UserInterfaceGrid->BuildSQLSearch('customfromemail') . ')', 'SELECT COUNT(*) AS totalitems FROM ' . TABLE_PREFIX .
                'emailqueues WHERE (' . $this->UserInterfaceGrid->BuildSQLSearch('email', true) . ') OR (' .
                $this->UserInterfaceGrid->BuildSQLSearch('type') . ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('host') .
                ') OR (' . $this->UserInterfaceGrid->BuildSQLSearch('username') . ') OR (' .
                $this->UserInterfaceGrid->BuildSQLSearch('customfromname') . ') OR (' .
                $this->UserInterfaceGrid->BuildSQLSearch('customfromemail') . ')');
        }

        $this->UserInterfaceGrid->SetQuery('SELECT * FROM ' . TABLE_PREFIX . 'emailqueues AS emailqueues', 'SELECT COUNT(*) AS totalitems FROM ' .
            TABLE_PREFIX . 'emailqueues');

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('emailqueueid', 'emailqueueid', SWIFT_UserInterfaceGridField::TYPE_ID));

        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('icon', '&nbsp;', SWIFT_UserInterfaceGridField::TYPE_CUSTOM, 16,
            SWIFT_UserInterfaceGridField::ALIGN_CENTER));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('emailqueues.email', $this->Language->Get('emailaddress'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 0, SWIFT_UserInterfaceGridField::ALIGN_LEFT, SWIFT_UserInterfaceGridField::SORT_ASC), true);
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('emailqueues.fetchtype', $this->Language->Get('emailfetchtype'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 120, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('emailqueues.departmentid', $this->Language->Get('queuedepartment'),
            SWIFT_UserInterfaceGridField::TYPE_DB, 180, SWIFT_UserInterfaceGridField::ALIGN_LEFT));
        $this->UserInterfaceGrid->AddField(new SWIFT_UserInterfaceGridField('emailqueues.registrationrequired',
            $this->Language->Get('registrationrequired'), SWIFT_UserInterfaceGridField::TYPE_DB, 200,
            SWIFT_UserInterfaceGridField::ALIGN_LEFT));

        $this->UserInterfaceGrid->SetRenderCallback(array($this, 'GridRender'));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('delete'), 'fa-trash',
            array('Parser\Admin\Controller_EmailQueue', 'DeleteList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('enable'), 'fa-check-circle',
            array('Parser\Admin\Controller_EmailQueue', 'EnableList'), $this->Language->Get('actionconfirm')));
        $this->UserInterfaceGrid->AddMassAction(new SWIFT_UserInterfaceGridMassAction($this->Language->Get('disable'), 'fa-minus-circle',
            array('Parser\Admin\Controller_EmailQueue', 'DisableList'), $this->Language->Get('actionconfirm')));

        $this->UserInterfaceGrid->SetNewLinkViewport(SWIFT::Get('basename') . '/Parser/EmailQueue/Insert');

        $this->UserInterfaceGrid->Render();

        $this->UserInterfaceGrid->Display();

        return true;
    }

    /**
     * The Grid Rendering Function
     *
     * @author Varun Shoor
     *
     * @param array $_fieldContainer The Field Record Value Container
     *
     * @return array
     */
    public static function GridRender($_fieldContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');

        $_emailQueueIcon = 'fa-envelope';
        if ($_fieldContainer['isenabled'] == '0') {
            $_emailQueueIcon = 'fa-check-circle';
        }

        $_fieldContainer['icon'] = '<i class="fa ' . $_emailQueueIcon . '" aria-hidden="true"></i>';

        $_fieldContainer['emailqueues.email'] = '<a href="' . SWIFT::Get('basename') . '/Parser/EmailQueue/Edit/' . (int)($_fieldContainer['emailqueueid']) . '" viewport="1" title="' . $_SWIFT->Language->Get('edit') . '">' . htmlspecialchars($_fieldContainer['email']) . '</a>';

        $_fieldContainer['emailqueues.emailqueueid'] = (int)($_fieldContainer['emailqueueid']);
        $_fieldContainer['emailqueues.fetchtype'] = $_SWIFT->Language->Get('fetch' . $_fieldContainer['fetchtype']);

        if (isset($_departmentCache[$_fieldContainer['departmentid']])) {
            $_fieldContainer['emailqueues.departmentid'] = text_to_html_entities($_departmentCache[$_fieldContainer['departmentid']]['title']);
        } else {
            $_fieldContainer['emailqueues.departmentid'] = $_SWIFT->Language->Get('na');
        }

        $_fieldContainer['emailqueues.registrationrequired'] = IIF($_fieldContainer['registrationrequired'] == '1', $_SWIFT->Language->Get('yes'),
            $_SWIFT->Language->Get('no'));

        return $_fieldContainer;
    }

    /**
     * Render the Verify Connection Dialog
     *
     * @author Varun Shoor
     *
     * @param array $_connectionContainer The Connection Container
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderVerifyConnection($_connectionContainer, $_script = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        $this->UserInterface->Start(get_short_class($this) . 'verifycon', '/Parser/EmailQueue/VerifyConnection', SWIFT_UserInterface::MODE_EDIT, true);
        $this->UserInterface->SetDialogOptions(false);

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('parseremailqueue'),
            SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        /*
         * ###############################################
         * BEGIN GENERAL TAB
         * ###############################################
         */
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('tabgeneral'), 'icon_form.gif', 'general', true);

        foreach ($_connectionContainer as $_key => $_val) {
            $_columnContainer = array();
            $_customClass = '';
            if ($_val[1]) {
                $_statusImage = 'fa-check-circle';
            } else {
                $_statusImage = 'fa-minus-circle';
                $_customClass = 'errorrow';
            }

            $_columnContainer[0]['value'] = $_val[0];
            $_columnContainer[0]['align'] = 'left';

            $_columnContainer[1]['value'] = '<i class="fa ' . $_statusImage . ' " aria-hidden="true"></i>';
            $_columnContainer[1]['align'] = 'center';
            $_columnContainer[1]['width'] = '16';

            $_GeneralTabObject->Row($_columnContainer, $_customClass);
        }

        /*
         * ###############################################
         * END GENERAL TAB
         * ###############################################
         */

         if($_script != '') {
             $this->UserInterface->AppendHTML($_script);
         }

        $this->UserInterface->End();

        return true;
    }
}

?>
