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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Tickets\Client;

use Base\Library\CustomField\SWIFT_CustomFieldRendererClient;
use Base\Library\HTML\SWIFT_HTML;
use Base\Models\Template\SWIFT_TemplateGroup;
use Controller_client;
use SWIFT;
use SWIFT_App;
use Base\Library\Captcha\SWIFT_CaptchaManager;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use SWIFT_DataID;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use SWIFT_Session;
use Base\Models\Template\SWIFT_Template;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserConsent;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Widget\SWIFT_Widget;
use Tickets\Models\Priority\SWIFT_TicketPriority;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Type\SWIFT_TicketType;

/**
 * The Submit a Ticket Controller
 *
 * @author Varun Shoor
 * @property SWIFT_CustomFieldRendererClient $CustomFieldRendererClient
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 * @property SWIFT_TemplateGroup $TemplateGroup
 */
class Controller_Submit extends Controller_client {
    private $isSaas;
    private $internal_ut;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws \SWIFT_Exception
     */
    public function __construct() {
        parent::__construct();

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2528: Widget particular pages shows up using direct URIs irrespective of whether the widget's visibility is restricted.
         *
         * Comments: None
         */
        if (!SWIFT_App::IsInstalled(APP_TICKETS) || !SWIFT_Widget::IsWidgetVisible(APP_TICKETS, 'submitticket'))
        {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();
            $this->stopRendering(true);
            return;
        }

        $this->Load->Library('CustomField:CustomFieldRendererClient', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('tickets');

        SWIFT_Ticket::LoadLanguageTable();

        $this->isSaas = preg_match('/.+saas.+/', strtolower(SWIFT::Get('licensepackage')));
        $this->internal_ut = $this->Settings->Get('internal_ut') ?: false;
    }

    /**
     * Render the department list
     *
     * @author Varun Shoor
     * @param bool|int $_departmentID (OPTIONAL) The default department id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_departmentID = false) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentCache = $this->Cache->Get('departmentcache');

        // Process a department to be selected by default if specified
        if (isset($_POST['departmentid'])) {
            $_departmentID = $_POST['departmentid'];
        }

        if (!$_departmentID || !is_numeric($_departmentID) || !isset($_departmentCache[$_departmentID])) {
            $_departmentID = false;
        } else {
            $_departmentID = (int) ($_departmentID);
        }

        if (!$_departmentID) {
            $_departmentID = $this->TemplateGroup->GetProperty('departmentid');
        }

        $_selectedDepartmentID = $_departmentID;
        $_displayParentDepartmentID = $_selectedDepartmentID;

        $_departmentMapContainer = SWIFT_Department::GetDepartmentMap(APP_TICKETS, SWIFT_PUBLIC, false, SWIFT::Get('usergroupid'));

        foreach ($_departmentMapContainer as $_dID => $_departmentContainer) {
            $_departmentContainer_Ref = &$_departmentMapContainer[$_dID];

            $_departmentTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $_dID);
            if (empty($_departmentTitleLanguage)) {
                $_departmentContainer_Ref['title_language'] = text_to_html_entities($_departmentContainer['title']);
            } else {
                $_departmentContainer_Ref['title_language'] = text_to_html_entities($_departmentTitleLanguage);
            }

            foreach ($_departmentContainer['subdepartments'] as $_subDepartmentID => $_subDepartmentContainer) {
                // If sub department is selected then we need to ensure the parent department container is set to display: block
                if ($_selectedDepartmentID == $_subDepartmentID) {
                    $_displayParentDepartmentID = $_dID;
                }

                $_subDepartmentContainer_Ref = &$_departmentMapContainer[$_dID]['subdepartments'][$_subDepartmentID];

                $_departmentTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $_subDepartmentID);
                if (empty($_departmentTitleLanguage)) {
                    $_subDepartmentContainer_Ref['title_language'] = text_to_html_entities($_subDepartmentContainer['title']);
                } else {
                    $_subDepartmentContainer_Ref['title_language'] = text_to_html_entities($_departmentTitleLanguage);
                }
            }
        }

        $this->Template->Assign('_pageTitle', text_to_html_entities($this->Language->Get('selectdepartmenttitle')));

        $this->UserInterface->Header('submitticket');

        $this->Template->Assign('_departmentMap', $_departmentMapContainer);

        $this->Template->Assign('_selectedDepartmentID', $_selectedDepartmentID);
        $this->Template->Assign('_displayParentDepartmentID', $_displayParentDepartmentID);

        $this->Template->Render('submitticket_departments');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the ticket submission form
     *
     * @author Varun Shoor
     * @param bool|int $_departmentID (OPTIONAL) The department id
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderForm($_departmentID = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_departmentCache = $this->Cache->Get('departmentcache');

        if ((!$_departmentID || !is_numeric($_departmentID) || !isset($_departmentCache[$_departmentID])) && isset($_POST['departmentid'])) {
            $_departmentID = $_POST['departmentid'];
        } else {
            $_departmentID = (int) ($_departmentID);
        }

        $_userGroupDepartmentIDList = SWIFT_Department::GetDepartmentIDListOnUserGroup(SWIFT::Get('usergroupid'));

        // Check for valid department
        if (empty($_departmentID) || !isset($_departmentCache[$_departmentID]) ||
                $_departmentCache[$_departmentID]['departmenttype'] != SWIFT_PUBLIC ||
                $_departmentCache[$_departmentID]['departmentapp'] != APP_TICKETS ||
                !in_array($_departmentID, $_userGroupDepartmentIDList)) {
            $this->UserInterface->CheckFields('departmentid');

            $this->UserInterface->Error(true, $this->Language->Get('errstinvaliddepartmentid'));

            $this->Load->Index();

            return false;
        }
        // By default we dont prompt user for priority or type
        $this->Template->Assign('_promptTicketPriority', false);
        $this->Template->Assign('_promptTicketType', false);

        // Check the template group setting and move accordingly.. first we process the priorities
        if ($this->TemplateGroup->GetProperty('tickets_promptpriority') == '1') {
            $_finalTicketPriorityContainer = $this->_GetTicketPriorityContainer();
            if (count($_finalTicketPriorityContainer)) {
                $this->Template->Assign('_promptTicketPriority', true);
                $this->Template->Assign('_ticketPriorityContainer', $_finalTicketPriorityContainer);
            }
        }

        // Then we process the types
        if ($this->TemplateGroup->GetProperty('tickets_prompttype') == '1') {
            $_finalTicketTypeContainer = $this->_GetTicketTypeContainer();

            if (count($_finalTicketTypeContainer)) {
                $this->Template->Assign('_promptTicketType', true);
                $this->Template->Assign('_ticketTypeContainer', $_finalTicketTypeContainer);
            }
        }

        $this->Template->Assign('_selectedDepartmentContainer', $_departmentCache[$_departmentID]);
        $_selectedDepartmentTitle = text_to_html_entities($_departmentCache[$_departmentID]['title']);

        $_departmentTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $_departmentID);
        if (!empty($_departmentTitleLanguage)) {
            $_selectedDepartmentTitle = text_to_html_entities($_departmentTitleLanguage);
        }

        $this->Template->Assign('_selectedDepartmentTitle', $_selectedDepartmentTitle);

        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded())
        {
            $this->Template->Assign('_noContactDetails', '1');
        }

        // Process the POST/Default variables
        if (isset($_POST['ticketfullname'])) {
            $this->Template->Assign('_ticketFullName', text_to_html_entities($_POST['ticketfullname'],1, true, true));
        } else {
            $this->Template->Assign('_ticketFullName', '');
        }

        if (isset($_POST['ticketemail'])) {
            $this->Template->Assign('_ticketEmail', htmlspecialchars($_POST['ticketemail']));
        } else {
            $this->Template->Assign('_ticketEmail', '');
        }

        if (isset($_POST['ticketsubject'])) {
            $this->Template->Assign('_ticketSubject', htmlspecialchars($_POST['ticketsubject']));
        } else {
            $this->Template->Assign('_ticketSubject', '');
        }

        if (isset($_POST['ticketmessage'])) {
            $this->Template->Assign('_ticketMessage', htmlspecialchars($_POST['ticketmessage']));
        } else {
            $this->Template->Assign('_ticketMessage', '');
        }

        if (isset($_POST['tickettypeid'])) {
            $this->Template->Assign('_ticketTypeID', (int) ($_POST['tickettypeid']));
        } else {
            $this->Template->Assign('_ticketTypeID', (int) ($this->TemplateGroup->GetProperty('tickettypeid')));
        }

        if (isset($_POST['ticketpriorityid'])) {
            $this->Template->Assign('_ticketPriorityID', (int) ($_POST['ticketpriorityid']));
        } else {
            $this->Template->Assign('_ticketPriorityID', (int) ($this->TemplateGroup->GetProperty('priorityid')));
        }

        $this->Template->Assign('_departmentID', (int) ($_departmentID));

        // Custom Fields
        $this->CustomFieldRendererClient->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_INSERT,
                array(SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), 0, $_departmentID);

        // Captcha
        $this->Template->Assign('_canCaptcha', false);
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('t_ccaptcha') == '1') && !$_SWIFT->Session->IsLoggedIn())
        {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded())
            {
                $_captchaHTML = $_CaptchaObject->GetHTML();
                if ($_captchaHTML)
                {
                    $isRecaptcha = false;
                    if(SWIFT::GetInstance()->Settings->Get('security_captchatype') == SWIFT_CaptchaManager::TYPE_RECAPTCHA) {
                        $isRecaptcha = true;
                    }
                    $this->Template->Assign('_isRecaptcha', $isRecaptcha);
                    $this->Template->Assign('_canCaptcha', true);
                    $this->Template->Assign('_captchaHTML', $_captchaHTML);
                }
            }
        }

        // Ticket recipients - CC
        $this->Template->Assign('_ticketCC', (isset($_POST['ticketcc']) ? htmlspecialchars($_POST['ticketcc']) : ''));

        $_canIRS = false;
        if (SWIFT_App::IsInstalled(APP_KNOWLEDGEBASE) && $this->Settings->Get('t_canirs') == '1')
        {
            $_canIRS = true;
        }
        $this->Template->Assign('_canIRS', $_canIRS);

        // Now render the form
        $this->Template->Assign('_pageTitle', htmlspecialchars($this->Language->Get('yourticketdetailstitle')));

        $this->Template->Assign('_csrfhash', $_SWIFT->Session->GetProperty('csrfhash'));

        $this->UserInterface->Header('submitticket');

        $this->Template->Render('submitticket_form');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Submit the ticket and display the confirmation
     *
     * @author Varun Shoor
     *
     * @param int $_hasAttachments (OPTIONAL)
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Confirmation($_hasAttachments = 0) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_userGroupDepartmentIDList = SWIFT_Department::GetDepartmentIDListOnUserGroup(SWIFT::Get('usergroupid'));

        $_ticketPriorityContainer = $this->_GetTicketPriorityContainer();
        $_ticketTypeContainer = $this->_GetTicketTypeContainer();

        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-3620 CSRF in Ticket Creation Functionality
         *
         * Comment: None
         */
        if (!in_array('submitticket_form (Default)', SWIFT_Template::GetUpgradeRevertList(), true) && (!isset($_POST['_csrfhash']) || !SWIFT_Session::CheckCSRFHash($_POST['_csrfhash']))) {
            $this->UserInterface->Error(true, $this->Language->Get('msgcsrfhash'));

            $this->Load->RenderForm();

            return false;
        }

        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded())
        {
            $_POST['ticketfullname'] = text_to_html_entities($_SWIFT->User->GetProperty('fullname'), 1, true, true);
            $_userEmailList = $_SWIFT->User->GetEmailList();
            $_POST['ticketemail'] = $_userEmailList[0];
        }

        $showEmptyError = false;

        // Custom Field Check
        $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT,
            array(SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_POST['departmentid']);
        if ($_customFieldCheckResult[0] == false)
        {
            call_user_func_array(array('SWIFT', 'ErrorField'), $_customFieldCheckResult[1]);

            $showEmptyError = true;
        }

        // Check for empty fields..
        if (!isset($_POST['ticketfullname']) || !isset($_POST['ticketemail']) || !isset($_POST['ticketsubject']) || !isset($_POST['ticketmessage']) ||
                trim($_POST['ticketfullname']) == '' || trim($_POST['ticketemail']) == '' || trim($_POST['ticketsubject']) == '' ||
                trim($_POST['ticketmessage']) == '')
        {
            $this->UserInterface->CheckFields('ticketfullname', 'ticketemail', 'ticketsubject', 'ticketmessage');
            $showEmptyError = true;
        }

        if ($showEmptyError) {
            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));

            $this->Load->RenderForm();

            return false;
        }

        $_ticketFullName = text_to_html_entities(trim(removeTags($_POST['ticketfullname'])));
        if (empty($_ticketFullName) || $_ticketFullName === '-- EMPTY HTML --') {
            SWIFT::ErrorField('ticketfullname');

            $this->UserInterface->Error(true, $this->Language->Get('invalidname'));

            $this->Load->RenderForm();

            return false;
        }

        // Email validation
        /**
         * BUGFIX - Verem Dugeri <verem.dugeri@crossover.com>
         *
         * KAYAKOC-268 - Validate email format
         *
         * Comments - None
         */

        if (!IsEmailValid($_POST['ticketemail']) || !filter_var($_POST['ticketemail'], FILTER_VALIDATE_EMAIL)) {
            SWIFT::ErrorField('ticketemail');

            $this->UserInterface->Error(true, $this->Language->Get('invalidemail'));

            $this->Load->RenderForm();

            return false;
        }

        if (!isset($_POST['registrationconsent'])) {

            $this->UserInterface->Error(true, $this->Language->Get('st_regpolicyareement'));

            $this->Load->RenderForm();

            return false;

        }

        // Check for captcha
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('t_ccaptcha') == '1') && !$_SWIFT->Session->IsLoggedIn())
        {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded() && !$_CaptchaObject->IsValidCaptcha())
            {
                SWIFT::ErrorField('captcha');

                $this->UserInterface->Error(true, $this->Language->Get('errcaptchainvalid'));

                $this->Load->RenderForm();

                return false;
            }
        }

        //Moved department id check before other checks as we need a valid departmentid for custom field check
        if (!isset($_POST['departmentid']) || empty($_POST['departmentid']) || !isset($_departmentCache[$_POST['departmentid']]) ||
                !in_array($_POST['departmentid'], $_userGroupDepartmentIDList)) {
            $this->UserInterface->Error(true, $this->Language->Get('sterr_invaliddepartment'));

            $this->Load->RenderForm();

            return false;
        }

        // Check for valid attachments
        $_attachmentCheckResult = self::CheckForValidAttachments($_hasAttachments);
        if ($_attachmentCheckResult[0] == false && _is_array($_attachmentCheckResult[1])) {
            $this->UserInterface->Error(true, sprintf($this->Language->Get('invalidattachments'), implode(', ', $_attachmentCheckResult[1])));

            $this->Load->RenderForm();

            return false;
        }

        // Check for has attachment
        $_errorCode = IIF($_attachmentCheckResult === false, 1, 0);

        // Create default variables
        $_userID = false;
        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded()) {
            $_userID = $_SWIFT->User->GetUserID();
        } else {
            $_userID = SWIFT_Ticket::GetOrCreateUserID($_ticketFullName, $_POST['ticketemail'], $_SWIFT->TemplateGroup->GetProperty('regusergroupid'), $_SWIFT->TemplateGroup->GetProperty('languageid'), true);
        }

        if ($_userConsent = SWIFT_UserConsent::RetrieveConsent($_userID, SWIFT_UserConsent::CONSENT_REGISTRATION)) {
            (new SWIFT_UserConsent($_userConsent[SWIFT_UserConsent::PRIMARY_KEY]))
                ->update(SWIFT_UserConsent::CHANNEL_WEB, SWIFT_UserConsent::SOURCE_SUBMIT_TICKET, $this->Router->GetCurrentURL());
        } else {
            SWIFT_UserConsent::Create(
                $_userID,
                SWIFT_UserConsent::CONSENT_REGISTRATION,
                SWIFT_UserConsent::CHANNEL_WEB,
                SWIFT_UserConsent::SOURCE_SUBMIT_TICKET, $this->Router->GetCurrentURL());
        }

        $_ticketStatusID = $this->TemplateGroup->GetProperty('ticketstatusid');
        $_ticketPriorityID = $this->TemplateGroup->GetProperty('priorityid');
        $_ticketTypeID = $this->TemplateGroup->GetProperty('tickettypeid');

        // Now that all basic data has been checked for, check the core variables.
        if (isset($_POST['ticketpriorityid']) && (!isset($_ticketPriorityCache[$_POST['ticketpriorityid']]) ||
                !isset($_ticketPriorityContainer[$_POST['ticketpriorityid']]))) {
            $this->UserInterface->Error(true, $this->Language->Get('sterr_invalidpriority'));

            $this->Load->RenderForm();

            return false;
        }

        if (isset($_POST['tickettypeid']) && (!isset($_ticketTypeCache[$_POST['tickettypeid']]) ||
                !isset($_ticketTypeContainer[$_POST['tickettypeid']]))) {
            $this->UserInterface->Error(true, $this->Language->Get('sterr_invalidtype'));

            $this->Load->RenderForm();

            return false;
        }

        // Process incoming variables.. sanitization checks have been done above
        if (isset($_POST['ticketpriorityid'])) {
            $_ticketPriorityID = (int) ($_POST['ticketpriorityid']);
        }

        if (isset($_POST['tickettypeid'])) {
            $_ticketTypeID = (int) ($_POST['tickettypeid']);
        }

        $_departmentID = (int) ($_POST['departmentid']);
        $_ownerStaffID = 0;

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-623 "Should Send Ticket Autoresponder" is not available under User Group settings
         *
         * Comments: None
         */
        $_sendAutoResponder = true;
        if (SWIFT_User::GetPermission('perm_sendautoresponder') == '0') {
            $_sendAutoResponder = false;
        }

        /**
         * BUG FIX  - Verem Dugeri <verem.dugeri@crossover.com>
         * KAYAKO-3095 - XSS Security Vulnerability with HTML
         *
         * Comments - None
         */
        $_htmlSetting = $this->Settings->Get('t_chtml');
        $_ticketSubject = trim(removeTags($_POST['ticketsubject']));
        $_isHTML = SWIFT_HTML::DetectHTMLContent($_ticketSubject);
        $_ticketSubject = SWIFT_TicketPost::GetParsedContents($_ticketSubject, $_htmlSetting, $_isHTML);

        /**
         * BUG FIX - Werner Garcia
         * KAYAKOC-6832 - Raw HTML/XML in ticket content
         *
         * Comments - handle HTML correctly according to settings
         */
        $_isHTML = SWIFT_HTML::DetectHTMLContent($_POST['ticketmessage']);
        $_ticketMessage = SWIFT_TicketPost::GetParsedContents($_POST['ticketmessage'], $_htmlSetting, $_isHTML);

        $_ticketMessage .=  SWIFT_CRLF;

        if ($_SWIFT->Settings->GetBool('t_tinymceeditor') &&
            $_SWIFT->Settings->Get('t_chtml') === 'entities') {
            $_ticketMessage = html_entity_decode($_ticketMessage);
        }

        $_htmlError = false;

        if (empty($_ticketSubject)) {
            SWIFT::ErrorField('ticketsubject');
            $_htmlError = true;
        }

        if (empty($_ticketMessage)) {
            SWIFT::ErrorField('ticketmessage');
            $_htmlError = true;
        }

        if ($_htmlError) {
            $this->UserInterface->Error(true, $this->Language->Get('invalidhtmltags'));
            $this->Load->RenderForm();
            return false;
        }

        // By now we have checked for all incoming data.. time to create a new ticket
        $_SWIFT_TicketObject = SWIFT_Ticket::Create($_ticketSubject, $_ticketFullName, $_POST['ticketemail'], $_ticketMessage,
            $_ownerStaffID, $_departmentID, $_ticketStatusID, $_ticketPriorityID, $_ticketTypeID,
            $_userID, 0, SWIFT_Ticket::TYPE_DEFAULT, SWIFT_Ticket::CREATOR_USER, SWIFT_Ticket::CREATIONMODE_SUPPORTCENTER,
            '', 0, false, '', $_isHTML);

        // Process the attachments?
        if ($this->Settings->Get('t_cenattach') == '1') {
            $_SWIFT_TicketObject->ProcessPostAttachments();
        }

        $_SWIFT_TicketObject->SetTemplateGroup($_SWIFT->TemplateGroup->GetTemplateGroupID());

        // Update Custom Field Values
        $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT,
                array(SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_SWIFT_TicketObject->GetTicketID(),
                $_SWIFT_TicketObject->GetProperty('departmentid'));

        // Process CC recipients
        if ($this->Settings->Get('t_csccrecipients') == '1' && trim($_POST['ticketcc']) != '') {

            $_emailList = array();
            // Tokenize information for delimiters - single space/comma
            $_emailAddressToken = strtok($_POST['ticketcc'], ', ');

            while ($_emailAddressToken !== false) {
                if (IsEmailValid($_emailAddressToken)) {
                    $_emailList[] = $_emailAddressToken;
                }
                $_emailAddressToken = strtok(', ');
            }

            if (_is_array($_emailList)) {
                SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_CC, $_emailList);
            }
        }

        // Dispatch autoresponder post ticket creation
        if (SWIFT_User::GetPermission('perm_sendautoresponder') == '1') {
            $_SWIFT_TicketObject->DispatchAutoresponder();
        }

        if (constant('SWIFT_INTERFACE') !== 'tests') {
            @header('location: ' . SWIFT::Get('basename') . '/Tickets/Submit/ConfirmationMessage/' . $_SWIFT_TicketObject->GetTicketDisplayID() . '/' . $_SWIFT_TicketObject->GetProperty('tickethash') . '/' . $_errorCode);
        }

        return true;
    }

    /**
     * Display the confirmation
     *
     * @author Varun Shoor
     *
     * @param string $_ticketID
     * @param string $_ticketHash
     * @param int    $_errorCode (OPTIONAL)
     *
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ConfirmationMessage($_ticketID, $_ticketHash, $_errorCode = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_userGroupDepartmentIDList = SWIFT_Department::GetDepartmentIDListOnUserGroup(SWIFT::Get('usergroupid'));

        $_ticketPriorityContainer = $this->_GetTicketPriorityContainer();
        $_ticketTypeContainer = $this->_GetTicketTypeContainer();

        $_SWIFT_TicketObject = $this->_GetTicketObjectOnHash($_ticketID, $_ticketHash);
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        /**
         * ---------------------------------------------
         * BEGIN CONFIRMATION MESSAGE CODE
         * ---------------------------------------------
         */

        $_departmentID = $_SWIFT_TicketObject->GetProperty('departmentid');

        // Assign template variables
        $_selectedDepartmentTitle = text_to_html_entities($_departmentCache[$_departmentID]['title']);

        $_departmentTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT, $_departmentID);
        if (!empty($_departmentTitleLanguage)) {
            $_selectedDepartmentTitle = text_to_html_entities($_departmentTitleLanguage);
        }

        $_ticketMessageContents = '';
        $_SWIFT_TicketPostObject = $_SWIFT_TicketObject->GetFirstPostObject();
        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketMessageContents = $_SWIFT_TicketPostObject->GetDisplayContents();

        /**
         * BIG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4917 Cross site scripting flaw in Kayako Case.
         */
        $this->Template->Assign('_selectedDepartmentTitle', $_selectedDepartmentTitle);
        $this->Template->Assign('_ticketFullName', text_to_html_entities($this->Input->SanitizeForXSS($_SWIFT_TicketObject->GetProperty('fullname')), 1, true, true));
        $this->Template->Assign('_ticketEmail', htmlspecialchars($_SWIFT_TicketObject->GetProperty('email')));
        $this->Template->Assign('_ticketSubject', $this->Input->SanitizeForXSS($this->Emoji->decode($_SWIFT_TicketObject->GetProperty('subject')), false, true));
        $this->Template->Assign('_ticketMessage', $_ticketMessageContents);
        $this->Template->Assign('_ticketDisplayID', $_SWIFT_TicketObject->GetTicketDisplayID());

        $this->Template->Assign('_ticketPriorityTitle', '');

        /**
         * BUG FIX - Pankaj Garg
         *
         * SWIFT-2288: Help desk displays wrong error message when user tries to upload a file above the maximum allowed size
         */
        if ($_errorCode == 1) {
            $this->UserInterface->Error(true, $this->Language->Get('st_attachmentwarning'));
        }

        $_ticketPriorityID = $_SWIFT_TicketObject->GetProperty('priorityid');

        if ($_ticketPriorityID) {
            $_ticketPriorityTitle = htmlspecialchars($_ticketPriorityCache[$_ticketPriorityID]['title']);
            $_ticketPriorityTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETPRIORITY, $_ticketPriorityID);
            if (!empty($_ticketPriorityTitleLanguage)) {
                $_ticketPriorityTitle = htmlspecialchars($_ticketPriorityTitleLanguage);
            }
            $this->Template->Assign('_ticketPriorityTitle', $_ticketPriorityTitle);
        }

        $_ticketTypeID = $_SWIFT_TicketObject->GetProperty('tickettypeid');

        $this->Template->Assign('_ticketTypeTitle', '');
        if ($_ticketTypeID) {
            $_ticketTypeTitle = htmlspecialchars($_ticketTypeCache[$_ticketTypeID]['title']);
            $_ticketTypeTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETTYPE, $_ticketTypeID);
            if (!empty($_ticketTypeTitleLanguage)) {
                $_ticketTypeTitle = htmlspecialchars($_ticketTypeTitleLanguage);
            }
            $this->Template->Assign('_ticketTypeTitle', $_ticketTypeTitle);
        }

        $_recipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($_SWIFT_TicketObject);
        if (isset($_recipientContainer[SWIFT_TicketRecipient::TYPE_CC])) {
            $this->Template->Assign('_CCEmailList', $_recipientContainer[SWIFT_TicketRecipient::TYPE_CC]);
        }

        // Display the confirmation
        $this->Template->Assign('_pageTitle', htmlspecialchars($this->Language->Get('ticketsubmittedtitle')));
        $this->Template->Assign('_robotsNoIndex', true);

        $this->UserInterface->Header('submitticket');

        $this->Template->Render('submitticket_confirmation');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Retrieve the ticket priority container linked with the current user group
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetTicketPriorityContainer() {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalTicketPriorityContainer = array();

        $_ticketPriorityContainer = SWIFT_TicketPriority::RetrieveOnUserGroup(SWIFT::Get('usergroupid'));
        if (_is_array($_ticketPriorityContainer)) {
            foreach ($_ticketPriorityContainer as $_ticketPriorityID => $_priorityContainer) {
                if ($_priorityContainer['type'] == SWIFT_PUBLIC) {
                    $_finalTicketPriorityContainer[$_ticketPriorityID] = $_priorityContainer;

                    if ((isset($_POST['ticketpriorityid']) && $_POST['ticketpriorityid'] == $_ticketPriorityID) ||
                            (!isset($_POST['ticketpriorityid']) && $_SWIFT->TemplateGroup->GetProperty('priorityid') == $_ticketPriorityID))
                    {
                        $_finalTicketPriorityContainer[$_ticketPriorityID]['selected'] = true;
                    }

                    $_priorityTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETPRIORITY, $_ticketPriorityID);
                    if (!empty($_priorityTitleLanguage)) {
                        $_finalTicketPriorityContainer[$_ticketPriorityID]['title'] = $_priorityTitleLanguage;
                    }
                }
            }
        }

        return $_finalTicketPriorityContainer;
    }

    /**
     * Retrieve the ticket type container linked to the current user group
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetTicketTypeContainer() {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_parentDepartmentID = 0;
        $_departmentCache = $this->Cache->Get('departmentcache');
        if (isset($_POST['departmentid']) && isset($_departmentCache[$_POST['departmentid']]) && !empty($_departmentCache[$_POST['departmentid']]['parentdepartmentid'])) {
            $_parentDepartmentID = (int) ($_departmentCache[$_POST['departmentid']]['parentdepartmentid']);
        }

        $_finalTicketTypeContainer = array();

        $_ticketTypeContainer = SWIFT_TicketType::RetrieveOnUserGroup(SWIFT::Get('usergroupid'));
        if (_is_array($_ticketTypeContainer)) {
            foreach ($_ticketTypeContainer as $_ticketTypeID => $_typeContainer) {
                if ($_typeContainer['type'] == SWIFT_PUBLIC && ($_typeContainer['departmentid'] == '0' ||
                                (isset($_POST['departmentid']) && $_typeContainer['departmentid'] == $_POST['departmentid']) || $_typeContainer['departmentid'] == $_parentDepartmentID)) {
                    $_finalTicketTypeContainer[$_ticketTypeID] = $_typeContainer;

                    if ((isset($_POST['tickettypeid']) && $_POST['tickettypeid'] == $_ticketTypeID) ||
                            (!isset($_POST['tickettypeid']) && $_SWIFT->TemplateGroup->GetProperty('tickettypeid') == $_ticketTypeID))
                    {
                        $_finalTicketTypeContainer[$_ticketTypeID]['selected'] = true;
                    }

                    $_typeTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETTYPE, $_ticketTypeID);
                    if (!empty($_typeTitleLanguage)) {
                        $_finalTicketTypeContainer[$_ticketTypeID]['title'] = $_typeTitleLanguage;
                    }
                }
            }
        }

        return $_finalTicketTypeContainer;
    }

    /**
     * Retrieve the Ticket Object
     *
     * @author Varun Shoor
     * @param mixed $_ticketID The Ticket ID
     * @param string $_ticketHash The Ticket Hash
     * @return SWIFT_Ticket|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _GetTicketObjectOnHash($_ticketID, $_ticketHash)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID))
        {
            return false;
        }

        $_SWIFT_TicketObject = false;

        $_finalTicketID = false;
        if (is_numeric($_ticketID))
        {
            $_finalTicketID = $_ticketID;
        } else {
            $_finalTicketID = SWIFT_Ticket::GetTicketIDFromMask($_ticketID);
        }

        if (!empty($_finalTicketID))
        {
            try
            {
                $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($_finalTicketID));
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                return false;
            }
        }

        if ($_SWIFT_TicketObject instanceof SWIFT_Ticket && $_SWIFT_TicketObject->GetIsClassLoaded() && $_SWIFT_TicketObject->GetProperty('tickethash') == $_ticketHash && trim($_ticketHash) != '')
        {
            return $_SWIFT_TicketObject;
        }

        return false;
    }

    /**
     * Checks for valid input for attachments
     *
     * @author Varun Shoor
     *
     * @param int $_hasAttachments (OPTIONAL)
     *
     * @return array|bool
     * @throws SWIFT_Exception
     */
    public static function CheckForValidAttachments($_hasAttachments = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        /**
         * BUG FIX - Pankaj Garg
         *
         * SWIFT-2288: Help desk displays wrong error message when user tries to upload a file above the maximum allowed size
         *
         * Comments: Check client side hasattachment variable.
         */
        if ($_hasAttachments == 1 && isset($_FILES['ticketattachments']['tmp_name'])) {
            foreach ($_FILES['ticketattachments']['tmp_name'] as $_tempFileName) {
                if (empty($_tempFileName)) {
                    return false;
                }
            }
        }

        // If its coming from support center and we cant find anything then return true
        if (!isset($_FILES['ticketattachments']) || !_is_array($_FILES['ticketattachments']) || !_is_array($_FILES['ticketattachments']['name'])) {
            return true;
        }

        $_fileTypeCache    = $_SWIFT->Cache->Get('filetypecache');
        $_fileTypeCacheMap = array();

        // Do we need to sanitize the data?
        if ($_SWIFT->Settings->Get('tickets_resattachments') == '0') {
            return true;
        }

        // Sanitize the data.. do we need to sanitize the data?
        foreach ($_fileTypeCache as $_ticketFileTypeID => $_ticketFileTypeContainer) {
            $_fileTypeCacheMap[mb_strtolower($_ticketFileTypeContainer['extension'])] = $_ticketFileTypeContainer;
        }

        $_resultArray = array();
        $_result      = true;

        // Check the attachments
        foreach ($_FILES['ticketattachments']['name'] as $_fileIndex => $_fileName) {
            $_fileExtension = mb_strtolower(substr($_fileName, (strrpos($_fileName, '.') + 1)));

            // Extension isnt added in the list? || Check whether we can accept it from support center? || Invalid File Size?
            if (!isset($_fileTypeCacheMap[$_fileExtension]) ||
                $_fileTypeCacheMap[$_fileExtension]['acceptsupportcenter'] == '0' ||
                ($_fileTypeCacheMap[$_fileExtension]['maxsize'] != '0' && ($_FILES['ticketattachments']['size'][$_fileIndex] / 1024) >= $_fileTypeCacheMap[$_fileExtension]['maxsize'])
            ) {
                $_result        = false;
                $_resultArray[] = $_fileName;
            }
        }

        return array($_result, $_resultArray);
    }
}
