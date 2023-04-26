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

namespace LiveChat\Visitor;

use Base\Library\Captcha\SWIFT_CaptchaManager;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\PolicyLink\SWIFT_PolicyLink;
use Base\Models\Rating\SWIFT_Rating;
use Base\Models\Rating\SWIFT_RatingResult;
use Base\Models\User\SWIFT_User;
use Controller_visitor;
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Chat\SWIFT_ChatQueue;
use LiveChat\Models\Chat\SWIFT_ChatVariable;
use LiveChat\Models\Message\SWIFT_Message;
use LiveChat\Models\Message\SWIFT_MessageSurvey;
use LiveChat\Models\Session\SWIFT_SessionManager;
use LiveChat\Models\Visitor\SWIFT_Visitor;
use LiveChat\Models\Visitor\SWIFT_VisitorData;
use SWIFT;
use SWIFT_App;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Hook;
use SWIFT_Interface;
use SWIFT_Loader;
use SWIFT_Session;

/**
 * The Chat Controller
 *
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = '', $_customAppName = '', $_appName = '')
 * @property Controller_Chat $Load
 * @author Varun Shoor
 */
class Controller_Chat extends Controller_visitor
{
    public $ExtendedCountryContainer;
    public $CustomFieldRendererClient;
    public $CustomFieldManager;
    public $TemplateGroup;
    public $ChatEventClient;
    private $isSaas;
    private $internal_ut;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * Bug Fix : Nidhi Gupta <nidhi.gupta@opencart.com.vn>
         *
         * SWIFT-4900 : UTM parameters for links to opencart.com.vn
         */
        $_requestURL = $_SERVER['SERVER_NAME'];

        $this->Language->Load('livechatclient');

        $this->Template->Assign('_randomNumber', BuildHash());
        $this->Template->Assign('_footerScript', '');
        $this->Template->Assign('_requestURL', $_requestURL);

        $this->isSaas = preg_match('/.+saas.+/', strtolower(SWIFT::Get('licensepackage')));
        $this->internal_ut = $this->Settings->Get('internal_ut') ?: false;
    }

    /**
     * Load the Chat Libraries
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected function _LoadChatLibraries()
    {
        $this->Load->Library('Chat:ChatEventClient', [], true, false, APP_LIVECHAT);

        $this->Load->Library('CustomField:CustomFieldRendererClient', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Template->Assign('_extendedPromptType', '');

        return true;
    }

    /**
     * Load the Chat Libraries
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected function _LoadMessageLibraries()
    {
        $this->Load->Library('CustomField:CustomFieldRendererClient', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        return true;
    }

    /**
     * The Default Index Controller
     *
     * @author Varun Shoor
     * @param array $_chatArguments The Chat Arguments
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Index($_chatArguments = array())
    {
        return $this->Start($_chatArguments);
    }

    /**
     * Start the Chat
     *
     * @author Varun Shoor
     * @param array $_chatArguments The Chat Arguments
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Request($_chatArguments = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $this->_LoadChatLibraries();

        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-1613:  Client's Operating System cannot be determined and is not sent to KD
         *
         * Comments: None
         */
        if (!is_array($_chatArguments)) {
            return false;
        }

        $_cookieSessionID = $this->Cookie->Get('sessionid' . SWIFT_Interface::INTERFACE_VISITOR);

        if (trim($_cookieSessionID) != '') {
            SWIFT_Session::Start($_SWIFT->Interface);
            $_chatArguments['_sessionID'] = $_cookieSessionID;
        } else if (!empty($_chatArguments['_sessionID'])) {
            SWIFT_Session::Insert($_SWIFT->Interface->GetInterface(), 0, $_chatArguments['_sessionID']);
            SWIFT_Session::Start($_SWIFT->Interface, $_chatArguments['_sessionID']);
        } else {
            $_chatArguments['_sessionID'] = SWIFT_Session::InsertAndStart(0);
        }

        if (isset($_chatArguments['_languageID']) && $_chatArguments['_languageID'] != $_SWIFT->Language->GetLanguageID()) {
            $_languageID = (int)($_chatArguments['_languageID']);
            $_languageCache = $this->Cache->Get('languagecache');
            if (!isset($_languageCache[$_languageID])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT->Cookie->Parse('client');
            $_SWIFT->Cookie->AddVariable('client', 'languageid', $_languageID);
            $_SWIFT->Cookie->Rebuild('client', true);

            if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded()) {
                $_SWIFT->User->UpdateLanguage($_languageID);
            }

            $_finalURLArgs = array();
            foreach ($_chatArguments as $_key => $_val) {
                $_finalURLArgs[] = $_key . '=' . $_val;
            }

            header('location: ' . SWIFT::Get('basename') . '/LiveChat/Chat/Request/' . implode('/', $_finalURLArgs));

            return false;
        }

        // Captcha
        $this->Template->Assign('_canCaptcha', false);

        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('livesupport_captcha') == '1') && !$this->_IsUserLoggedIn()) {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded()) {
                $_captchaHTML = $_CaptchaObject->GetHTML();
                if ($_captchaHTML) {
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

        if (empty($_chatArguments['_sessionID']))
        {
            return false;
        }

        if (!isset($_chatArguments['_departmentID'])) {
            $_chatArguments['_departmentID'] = 0;
        }

        if (!isset($_chatArguments['_filterDepartmentID'])) {
            $_chatArguments['_filterDepartmentID'] = '';
        }

        $this->UserInterface->ProcessDialogs();
        $this->Template->Assign('_footerScript', $this->UserInterface->GetFooterScript());

        // Cache all templates
        $this->Template->LoadCache(array('chatheader', 'chatfooter', 'chatdepartmentlist', 'chatlanding'));

        // Get Department Status
        $_chatArguments['_filterDepartmentIDList'] = array();
        $_chatArguments = $this->_ProcessArgumentContainer($_chatArguments);

        // Check to see if some staff is online
        $_staffStatus = false;
        $_isPhone = false;
        $_promptType = 'chat';

        if (isset($_chatArguments['_promptType']) && $_chatArguments['_promptType'] == 'call') {
            $_staffStatus = SWIFT_Visitor::GetStaffPhoneStatus($_chatArguments['_filterDepartmentIDList']);
            $_promptType = 'call';
            $_isPhone = true;
        } else {
            $_staffStatus = SWIFT_Visitor::GetStaffOnlineStatus($_chatArguments['_filterDepartmentIDList']);
        }

        $_departmentStatus = SWIFT_Chat::GetDepartmentStatus($_chatArguments['_filterDepartmentIDList'], $_isPhone, SWIFT::Get('usergroupid'));

        /* Bug Fix : Nidhi Gupta <nidhi.gupta@opencart.com.vn
         *
         * SWIFT-4543 : LiveChat window goes blank
         *
         * Comments : Removed array_column function as its not compatible with PHP version < 5.5
         */
        $_OnlineDepartmentContainer = $_OfflineDepartmentContainer = array();
        foreach ($_departmentStatus['online'] as $_container) {
            if (isset($_container['departmentid'])) {
                $_OnlineDepartmentContainer[] = $_container['departmentid'];
            }
        }

        foreach ($_departmentStatus['offline'] as $_container) {
            if (isset($_container['departmentid'])) {
                $_OfflineDepartmentContainer[] = $_container['departmentid'];
            }
        }

        $this->Template->Assign('_departmentStatusContainer', $_departmentStatus);
        $this->Template->Assign('_promptType', $_promptType);

        // Process Proactive Data
        /** Bug FIX : Saloni Dhall <saloni.dhall@opencart.com.vn>
         *
         * SWIFT-3400 : 'Default Department' setting for live chat under Template groups does not work
         */
        $_setDefaultDepartment = true;
        if (isset($_chatArguments['_proactive']) && $_chatArguments['_proactive'] != 0 && is_numeric($_chatArguments['_proactive'])) {
            $_setDefaultDepartment = false;
            $_SWIFT_SessionManagerObject = new SWIFT_SessionManager($_chatArguments['_sessionID']);
            if ($_SWIFT_SessionManagerObject instanceof SWIFT_SessionManager && $_SWIFT_SessionManagerObject->GetIsClassLoaded()) {
                $_SWIFT_SessionManagerObject->ProcessVisitorUpdateSession();
                if (isset($_SWIFT->Session) && $_SWIFT->Session->GetIsClassLoaded() && ($_chatArguments['_proactive'] == SWIFT_Visitor::PROACTIVE_ENGAGE || $_chatArguments['_proactive'] == SWIFT_Visitor::PROACTIVE_INLINE)) {
                    /** BUG FIX : Saloni Dhall <saloni.dhall@opencart.com.vn>
                     *
                     * SWIFT-918 : Custom Fields are being shown for the live chat department to which they are not linked
                     *
                     * Comments : The dept. should be selected in the dropdown which is being selected.
                     */
                    if ($_SWIFT->Session->GetProperty('departmentid') != '0') {
                        if (isset($_chatArguments['_departmentID']) && $_chatArguments['_departmentID'] != '0') {
                            $_defaultDepartmentID = $_chatArguments['_departmentID'];
                            $this->Template->Assign('_setDepartmentID', $_defaultDepartmentID);
                        } else {
                            $_defaultDepartmentID = $_SWIFT->Session->GetProperty('departmentid');
                            $this->Template->Assign('_setDepartmentID', 0);

                            //Check all the possible cases here
                            if (!$_departmentStatus['online'] || //If noone is online.
                                ($_departmentStatus['online'] && in_array($_defaultDepartmentID, $_OnlineDepartmentContainer)) //Default department is in online container
                            ) {
                                $this->Template->Assign('_setDepartmentID', $_defaultDepartmentID);
                            } else if ((!in_array($_defaultDepartmentID, $_OfflineDepartmentContainer) && !in_array($_defaultDepartmentID, $_OnlineDepartmentContainer)) || // Default dept. neither offline nor online (referring to filter department case)
                                ($_departmentStatus['offline'] && in_array($_defaultDepartmentID, $_OfflineDepartmentContainer)) //Default department is in offline container
                            ) {
                                foreach ($_departmentStatus['online'] as $_key => $_departmentContainer) {
                                    $_defaultDepartmentID = $_departmentContainer['departmentid'];
                                }

                                $this->Template->Assign('_setDepartmentID', $_defaultDepartmentID);
                            }
                        }

                        $_chatArguments['_departmentID'] = $_defaultDepartmentID;
                        $this->Template->Assign('_proactive', $_chatArguments['_proactive']);
                    } else {
                        $_setDefaultDepartment = true;
                    }
                }
                $_SWIFT_SessionManagerObject->UpdateSessionStatus(SWIFT_Visitor::PROACTIVE_NONE, SWIFT_Visitor::PROACTIVERESULT_ACCEPTED);
            }
        }

        if ($_setDefaultDepartment == true) {
            /** BUG FIX : Saloni Dhall <saloni.dhall@opencart.com.vn>
             *
             * SWIFT-918 : Custom Fields are being shown for the live chat department to which they are not linked
             *
             * Comments : The dept. should be selected in the dropdown which is being selected.
             */
            if (isset($_chatArguments['_departmentID']) && $_chatArguments['_departmentID'] != '0') {
                $_defaultDepartmentID = $_chatArguments['_departmentID'];
                $this->Template->Assign('_setDepartmentID', $_defaultDepartmentID);
            } else {
                $_templateGroupID = $_SWIFT->TemplateGroup->GetTemplateGroupID();
                $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');
                $_defaultDepartmentID = $_templateGroupCache[$_templateGroupID]['departmentid_livechat'];
                $this->Template->Assign('_setDepartmentID', 0);

                //Check all the possible cases here
                if (!$_departmentStatus['online'] || //If noone is online.
                    ($_departmentStatus['online'] && in_array($_defaultDepartmentID, $_OnlineDepartmentContainer)) //Default department is in online container
                ) {
                    $this->Template->Assign('_setDepartmentID', $_defaultDepartmentID);
                } else if ((!in_array($_defaultDepartmentID, $_OfflineDepartmentContainer) && !in_array($_defaultDepartmentID, $_OnlineDepartmentContainer)) || // Default dept. neither offline nor online (referring to filter department case)
                    ($_departmentStatus['offline'] && in_array($_defaultDepartmentID, $_OfflineDepartmentContainer)) //Default department is in offline container
                ) {
                    foreach ($_departmentStatus['online'] as $_key => $_departmentContainer) {
                        if ($_departmentContainer['issub'] == '1') {
                            continue;
                        }
                        $_defaultDepartmentID = $_departmentContainer['departmentid'];
                    }
                    $this->Template->Assign('_setDepartmentID', $_defaultDepartmentID);
                }
            }
            //$this->Template->Assign('_proactive', $_chatArguments['_proactive']);
            $_chatArguments['_departmentID'] = $_defaultDepartmentID;
        }

        $languageID = $this->Cookie->GetVariable('client', 'languageid');
        $_registrationPolicyURL = SWIFT_PolicyLink::RetrieveURL($languageID);
        $this->Template->Assign('_registrationPolicyURL', $_registrationPolicyURL);

        if ($_staffStatus == SWIFT_Session::STATUS_ONLINE) {
            $this->Template->Assign('_isStaffOnline', '1');
        } else {
            $this->Load->Message($_chatArguments);

            return false;
        }

        // Call country list?
        if ($_promptType == 'call') {
            $this->Load->Library('Misc:ExtendedCountryContainer');
            $_countryContainer = $this->ExtendedCountryContainer->Get();
            $_finalCountryContainer = array();
            $_firstMatchSelected = false;

            foreach ($_countryContainer as $_country) {
                $_ituCode = $_country[5];
                $_ituPrefixCode = '+' . $_SWIFT->Settings->Get('ls_activecountrycode');
                if (empty($_ituCode) || ($this->Settings->Get('ls_enableinternationalcalls') == '0' && $_ituCode != $_ituPrefixCode)) {
                    continue;
                }

                $_isSelected = false;
                if (!$_firstMatchSelected && $_ituCode == $_ituPrefixCode) {
                    $_isSelected = true;
                    $_firstMatchSelected = true;
                }

                $_finalCountryContainer[] = array('title' => $_country[0] . ' (' . $_country[5] . ')', 'value' => str_replace('+', '', $_country[5]), 'selected' => $_isSelected);
            }

            $this->Template->Assign('_countryList', $_finalCountryContainer);
        }

        if (isset($_chatArguments['_captchaError']) && $_chatArguments['_captchaError'])
        {
            $this->Template->Assign('_captchaError', true);
        }

        // Assign All Variables
        $this->_AssignVariables($_chatArguments);

        // Begin Hook: visitor_chat_request
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('visitor_chat_request')) ? eval($_hookCode) : false;
        // End Hook

        // Custom Fields
        $this->Template->Assign('_isLiveChat', true);
        $this->CustomFieldRendererClient->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE), 0, $_chatArguments['_departmentID']);

        // Load the Core Templates
        $this->Template->Render('chatlanding');

        return true;
    }

    /**
     * Process the variables in the argument container
     *
     * @author Varun Shoor
     * @param array $_argumentContainer The Argument Container
     * @return array "_argumentContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessArgumentContainer($_argumentContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        foreach (array('_filterDepartmentID') as $_key => $_val) {
            if (isset($_argumentContainer[$_val]) && !empty($_argumentContainer[$_val]) && !_is_array($_argumentContainer[$_val . 'List'])) {
                if (is_numeric($_argumentContainer[$_val])) {
                    $_argumentContainer[$_val . 'List'][] = (int)($_argumentContainer[$_val]);
                } else if (strstr($_argumentContainer[$_val], ',')) {
                    $_chunkContainer = explode(',', $_argumentContainer[$_val]);
                    if (_is_array($_chunkContainer)) {
                        foreach ($_chunkContainer as $_chunkKey => $_chunkVal) {
                            if (is_numeric($_chunkVal)) {
                                $_argumentContainer[$_val . 'List'][] = (int)($_chunkVal);
                            }
                        }
                    }
                }
            }
        }

        return $_argumentContainer;
    }

    /**
     * Processes the POST data into a Cookie
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _ProcessChatCookie()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_fullName = $_email = '';

        if (isset($_POST['fullname'])) {
            $_fullName = $_POST['fullname'];
        }

        if (isset($_POST['email'])) {
            $_email = $_POST['email'];
        }

        $this->Cookie->Parse('livechatdetails');
        $this->Cookie->AddVariable('livechatdetails', 'fullname', $_fullName);
        $this->Cookie->AddVariable('livechatdetails', 'email', $_email);
        $this->Cookie->Rebuild('livechatdetails', true);

        return true;
    }

    /**
     * Start an Inline Chat
     *
     * @author Varun Shoor
     * @param array $_chatArguments The Chat Arguments
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function StartInline($_chatArguments = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $this->_LoadChatLibraries();

        if (!isset($_chatArguments['_sessionID'])) {
            return false;
        } else if (empty($_chatArguments['_sessionID']) && $_SWIFT->Session instanceof SWIFT_Session && $_SWIFT->Session->GetIsClassLoaded()) {
            $_chatArguments['_sessionID'] = $_SWIFT->Session->GetSessionID();
        } else if (empty($_chatArguments['_sessionID'])) {
            $_chatArguments['_sessionID'] = SWIFT_Session::InsertAndStart(0);
        }

        if (empty($_chatArguments['_sessionID'])) {
            return false;
        }

        $_POST['fullname'] = $_REQUEST['fullname'] = $this->Language->Get('proactivevisitorname');
        $_POST['email'] = $_REQUEST['email'] = '';
        $_POST['sessionid'] = $_REQUEST['sessionid'] = $_chatArguments['_sessionID'];

        $_SWIFT_SessionManagerObject = new SWIFT_SessionManager($_chatArguments['_sessionID']);
        if ($_SWIFT_SessionManagerObject instanceof SWIFT_SessionManager && $_SWIFT_SessionManagerObject->GetIsClassLoaded()) {
            $_SWIFT_SessionManagerObject->ProcessVisitorUpdateSession();
        }

        // Calculate the filtered departments
        $_filterDepartmentIDList = array();

        $_chatArguments['filterdepartmentidlist'] = array();

        if (isset($_chatArguments['_filterDepartmentID']) && !empty($_chatArguments['_filterDepartmentID'])) {
            if (is_numeric($_chatArguments['_filterDepartmentID'])) {
                $_chatArguments['filterdepartmentidlist'][] = (int)($_chatArguments['_filterDepartmentID']);
            } else if (strstr($_chatArguments['_filterDepartmentID'], ',')) {
                $_chunkContainer = explode(',', $_chatArguments['_filterDepartmentID']);
                if (_is_array($_chunkContainer)) {
                    foreach ($_chunkContainer as $_chunkKey => $_chunkVal) {
                        if (is_numeric($_chunkVal)) {
                            $_chatArguments['filterdepartmentidlist'][] = (int)($_chunkVal);
                        }
                    }
                }
            }
        }

        // Priority is with Session Department ID
        if (isset($_SWIFT->Session) && $_SWIFT->Session->GetIsClassLoaded()) {
            $_sessionDepartmentID = $_SWIFT->Session->GetProperty('departmentid');

            if (!empty($_sessionDepartmentID)) {
                $_filterDepartmentIDList[] = (int)($_SWIFT->Session->GetProperty('departmentid'));
            }
        }

        $_incomingDepartmentID = false;
        // Otherwise some department ids from argument perhaps?
        if (isset($_chatArguments['filterdepartmentidlist']) && _is_array($_chatArguments['filterdepartmentidlist'])) {
            $_filterDepartmentIDList = array_merge($_filterDepartmentIDList, $_chatArguments['filterdepartmentidlist']);
            $_incomingDepartmentID = true;
        }

        $_departmentStatus = SWIFT_Chat::GetDepartmentStatus($_filterDepartmentIDList, false, SWIFT::Get('usergroupid'));

        // No online departments?
        if (!isset($_departmentStatus['online']) || !count($_departmentStatus['online'])) {
            $_chatArguments['_proactive'] = SWIFT_Visitor::PROACTIVE_INLINE;
            return $this->Load->Request($_chatArguments);
        }

        $_isDepartmentOnline = false;
        if (!count($_filterDepartmentIDList)) {
            $_filterDepartmentIDList[] = $_departmentStatus['online'][0]['departmentid'];
            $_isDepartmentOnline = $_departmentStatus['online'][0]['departmentid'];

        } else {
            // Itterate through online departments and make sure its set..
            foreach ($_departmentStatus['online'] as $_key => $_val) {
                if (in_array($_val['departmentid'], $_filterDepartmentIDList)) {
                    $_isDepartmentOnline = $_val['departmentid'];

                    break;
                }
            }

            // We couldnt find this department online so we set it to the first available department.. (ONLY IF INCOMING ARGUMENTS ARE NOT SET)
            if ($_isDepartmentOnline == false && !$_incomingDepartmentID) {
                $_filterDepartmentIDList[] = $_departmentStatus['online'][0]['departmentid'];
                $_isDepartmentOnline = $_departmentStatus['online'][0]['departmentid'];
            }
        }

        // This shouldnt happen but we have still added a check for it.. just in case..
        if (!count($_filterDepartmentIDList) || $_isDepartmentOnline == false) {
            return $this->Load->Message($_chatArguments);
        }

        $_POST['departmentid'] = $_REQUEST['departmentid'] = (int)($_isDepartmentOnline);
        $_POST['filterdepartmentid'] = $_chatArguments['_filterDepartmentID'];
        $_POST['subject'] = $_REQUEST['subject'] = '';

        if (isset($_chatArguments['_inline']) && $_chatArguments['_inline'] == '1') {
            $_chatArguments['inline'] = '1';
            $_chatArguments['_proactive'] = SWIFT_Visitor::PROACTIVE_INLINE;
        }

        // Begin Hook: visitor_chat_inline
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('visitor_chat_inline')) ? eval($_hookCode) : false;
        // End Hook

        $this->Load->Start($_chatArguments);

        return true;
    }

    /**
     * Start the Chat
     *
     * @author Varun Shoor
     * @param array $_chatArguments The Chat Arguments
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Start($_chatArguments = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $this->_LoadChatLibraries();

        $_departmentID = isset($_chatArguments['_departmentID']) ? $_chatArguments['_departmentID'] : $_POST['departmentid'];
        $_proActive = (isset($_chatArguments['_proactive']) && is_numeric($_chatArguments['_proactive'])) ? $_chatArguments['_proactive'] : 0;
        $_filterDepartmentID = isset($_chatArguments['_filterDepartmentID']) ? $_chatArguments['_filterDepartmentID'] : 0;

        $_chatRedirectArguments = array('_sessionID' => '',
            '_proactive' => $_proActive,
            '_departmentID' => $_departmentID,
            '_filterDepartmentID' => $_filterDepartmentID,
            '_fullName' => '',
            '_email' => '',
            '_promptType' => '');

        if (!isset($_POST['sessionid']) || !isset($_POST['fullname']) || !isset($_POST['email']) || !isset($_POST['departmentid']) || empty($_POST['sessionid']) || empty($_POST['fullname']) || empty($_POST['departmentid'])) {
            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));
            $this->Load->Request($_chatRedirectArguments);

            return false;
        }

        //captcha check
        SWIFT_Session::Start($this->Interface);
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('livesupport_captcha') == '1') && !$this->_IsUserLoggedIn()
            && (!isset($_chatArguments['inline']) || $_chatArguments['inline'] != '1')) {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded() && !$_CaptchaObject->IsValidCaptcha()) {
                $_chatRedirectArguments['_captchaError'] = true;
                $this->Load->Request($_chatRedirectArguments);
                return false;
            }
        }

        $_isPhone = false;
        $_phoneNumber = '';
        $_countryCode = '';
        $_finalPhoneNumber = '';
        $_displayPhoneNumber = '';

        if (!isset($_POST['prompttype'])) {
            $_POST['prompttype'] = 'chat';
        }

        $_chatRedirectArguments = array('_sessionID' => $_POST['sessionid'],
            '_proactive' => $_proActive,
            '_departmentID' => $_departmentID,
            '_filterDepartmentID' => $_POST['filterdepartmentid'],
            '_fullName' => $_POST['fullname'],
            '_email' => $_POST['email'],
            '_promptType' => $_POST['prompttype']);

        if (isset($_POST['prompttype']) && $_POST['prompttype'] == 'call' && isset($_POST['phonenumber']) && !empty($_POST['phonenumber']) && isset($_POST['countrycode']) && !empty($_POST['countrycode'])) {
            $_isPhone = true;
            $_phoneNumber = CleanInt($_POST['phonenumber']);
            $_countryCode = CleanInt($_POST['countrycode']);
            if ($this->Settings->Get('ls_enableinternationalcalls') == '0' && $_countryCode != $_SWIFT->Settings->Get('ls_activecountrycode')) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_finalPhoneNumber = SWIFT_Chat::GetProcessedPhoneNumber($_countryCode, $_phoneNumber);

            if (empty($_phoneNumber) || empty($_countryCode) || empty($_finalPhoneNumber)) {
                $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));
                $this->Load->Request($_chatRedirectArguments);
                return false;
            }

            $_displayPhoneNumber = $_countryCode . $_phoneNumber;

            $this->Template->Assign('_promptType', 'call');
        } else {
            $this->Template->Assign('_promptType', 'chat');
        }

        $_staffCache = $this->Cache->Get('staffcache');
        $_departmentCache = $this->Cache->Get('departmentcache');

        // Custom Field Check
        $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_departmentID);
        if ($_customFieldCheckResult[0] == false) {
            call_user_func_array(array('SWIFT', 'ErrorField'), $_customFieldCheckResult[1]);

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));
            $this->Load->Request($_chatRedirectArguments);

            return false;
        }

        // Cache all templates
        $this->Template->LoadCache(array('chatheader', 'chatfooter', 'chatcore'));

        $this->_AssignVariables(array_merge($_chatArguments, $_POST));
        $this->Template->Assign('_displayLanguageSelection', false);

        $_visitorSkillIDList = array();
        $_chatSkillID = false;
        $_isProactive = false;
        $_roundRobinStaffID = 0;

        if (isset($_REQUEST['proactivestaffid']) && trim($_REQUEST['proactivestaffid']) != "" && is_numeric($_REQUEST['proactivestaffid'])) {
            $_roundRobinStaffID = (int)($_REQUEST['proactivestaffid']);
            $_isProactive = true;
        } else {
            $_visitorSkillIDList = SWIFT_VisitorData::RetrieveOnVisitorSession($_POST['sessionid'], SWIFT_VisitorData::DATATYPE_SKILL);

            if (_is_array($_visitorSkillIDList)) {
                foreach ($_visitorSkillIDList as $_key => $_val) {
                    $_chatSkillID = $_val;

                    break;
                }
            }

            // Check routing mode and work accordingly
            if ($this->Settings->Get('ls_routingmode') != 'openqueue') {
                $_roundRobinStaffID = SWIFT_Chat::GetRoundRobinStaff((int)($_REQUEST['departmentid']), false, false, $_visitorSkillIDList, $_isPhone);
            }

            $_isProactive = false;
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-3170,SWIFT-4304 - Chat gets initiated while selecting Offline departments.
         *
         * Comments: Adding online department check.
         */
        if ((empty($_roundRobinStaffID) || empty($_REQUEST['fullname'])) && ($this->Settings->Get('ls_routingmode') == 'openqueue' && !SWIFT_Chat::IsDepartmentOnline($_REQUEST['departmentid']))) {
            // Seems like user selected a department which has no staff online? Show him the Leave a Message form
            $this->Load->Message(array_merge($_chatArguments, $_POST));

            return false;
        } else if ((isset($_staffCache[$_roundRobinStaffID]) && isset($_departmentCache[$_REQUEST['departmentid']])) || $this->Settings->Get('ls_routingmode') == 'openqueue') {
            /*
             * BUG FIX - Mahesh Salaria
             *
             * SWIFT-1825: Not able to view chat history due to [Notice]: Object of class SWIFT_User could not be converted to int (Chat/class.SWIFT_Chat.php:339)
             *
             * Comments: Funtion was returning User Object which was causing issue.
             */
            $_userID = 0;
            if (!empty($_REQUEST['email'])) {
                /*
                 * BUG FIX - Simaranjit Singh
                 *
                 * SWIFT-2864: User is shown as 'Guest' under Staff CP > Users > Manage Users though he is able to login and submit ticket
                 *
                 * Comments: Changed Guest usergroupid with Registered usergroupid linked with current template group
                 */
                $_SWIFT_UserObject = SWIFT_User::GetOrCreateUserID($_REQUEST['fullname'], $_REQUEST['email'], $this->TemplateGroup->GetProperty('regusergroupid'));
                $_userID = $_SWIFT_UserObject->GetUserID();
            }


            $this->_UpdateLastChatCookie();
            // Checking routingmode for appropriate chat initiation
            if ($this->Settings->Get('ls_routingmode') == 'openqueue') {
                $_SWIFT_ChatObject = SWIFT_Chat::Insert($_REQUEST['sessionid'], $_userID, $_REQUEST['fullname'], $_REQUEST['email'], $_REQUEST['subject'], 0, '', $_REQUEST['departmentid'],
                    $_departmentCache[$_REQUEST['departmentid']]['title'], SWIFT_Chat::CHATTYPE_CLIENT, SWIFT::Get('IP'), $_isProactive,
                    $_chatSkillID, false, $_SWIFT->TemplateGroup->GetTemplateGroupID(), $_isPhone, $_finalPhoneNumber);
            } else { // Roundrobin
                $_SWIFT_ChatObject = SWIFT_Chat::Insert($_REQUEST['sessionid'], $_userID, $_REQUEST['fullname'], $_REQUEST['email'], $_REQUEST['subject'],
                    $_roundRobinStaffID, $_staffCache[$_roundRobinStaffID]['fullname'], $_REQUEST['departmentid'],
                    $_departmentCache[$_REQUEST['departmentid']]['title'], SWIFT_Chat::CHATTYPE_CLIENT, SWIFT::Get('IP'), $_isProactive,
                    $_chatSkillID, false, $_SWIFT->TemplateGroup->GetTemplateGroupID(), $_isPhone, $_finalPhoneNumber);
            }

            if ($_SWIFT_ChatObject instanceof SWIFT_Chat && $_SWIFT_ChatObject->GetIsClassLoaded()) {
                // Checking routing mode for call initiation
                if ($_isPhone == true && $this->Settings->Get('ls_routingmode') == 'roundrobin') {
                    SWIFT_Call::Create($_displayPhoneNumber, '', $_userID, $_REQUEST['fullname'], $_REQUEST['email'], $_roundRobinStaffID,
                        $_staffCache[$_roundRobinStaffID]['fullname'], $_SWIFT_ChatObject->GetChatObjectID(), $_REQUEST['departmentid'],
                        true, SWIFT_Call::STATUS_PENDING, SWIFT_Call::TYPE_INBOUND);

                    $_SWIFT_ChatObject->UpdateCallStatus(SWIFT_Call::STATUS_PENDING);
                } else if ($_isPhone == true) { // Openqueue
                    SWIFT_Call::Create($_displayPhoneNumber, '', $_userID, $_REQUEST['fullname'], $_REQUEST['email'], 0,
                        '', $_SWIFT_ChatObject->GetChatObjectID(), $_REQUEST['departmentid'],
                        true, SWIFT_Call::STATUS_PENDING, SWIFT_Call::TYPE_INBOUND);

                    $_SWIFT_ChatObject->UpdateCallStatus(SWIFT_Call::STATUS_PENDING);
                }

                $this->_ProcessChatCookie();

                SWIFT_VisitorData::DeleteOnDataKey($_REQUEST['sessionid'], $_SWIFT->Language->Get('chattemplategroup'));
                SWIFT_VisitorData::Insert($_REQUEST['sessionid'], 0, SWIFT_VisitorData::DATATYPE_VARIABLE, $_SWIFT->Language->Get('chattemplategroup'), $_SWIFT->Template->GetTemplateGroupName());
                // Process the skill sets for this visitor
                if (_is_array($_visitorSkillIDList)) {
                    foreach ($_visitorSkillIDList as $_key => $_val) {
                        SWIFT_ChatVariable::Create($_SWIFT_ChatObject->GetChatObjectID(), SWIFT_ChatVariable::TYPE_SKILL, $_val);
                    }
                }

                // Update Custom Field Values
                $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_SWIFT_ChatObject->GetChatObjectID());

                $_customFieldGroupContainer = SWIFT_CustomFieldManager::Retrieve(array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPRE), 0, $_REQUEST['departmentid'], false);
                if (_is_array($_customFieldGroupContainer)) {
                    foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
                        foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
                            if (isset($_POST[$_customField['fieldname']])) {
                                $_fieldValue = '';
                                if (_is_array($_POST[$_customField['fieldname']])) {
                                    /**
                                     * BUG FIX - Mansi Wason <mansi.wason@opencart.com.vn>
                                     *
                                     * SWIFT-1918 Error when using Linked Select custom field with "Group Type" = Live Chat - "Pre Chat"
                                     */
                                    foreach ($_POST[$_customField['fieldname']] as $_val) {
                                        if (!_is_array($_val)) {
                                            $_fieldValue .= htmlspecialchars($_val) . '<br />' . SWIFT_CRLF;
                                        }
                                    }
                                } else {
                                    $_fieldValue = htmlspecialchars($_POST[$_customField['fieldname']]);
                                }

                                SWIFT_VisitorData::DeleteOnDataKey($_REQUEST['sessionid'], $_customField['title']);
                                SWIFT_VisitorData::Insert($_REQUEST['sessionid'], 0, SWIFT_VisitorData::DATATYPE_VARIABLE, $_customField['title'], $_fieldValue);
                            }
                        }
                    }
                }

                $this->Template->Assign('_chatSessionID', $_SWIFT_ChatObject->GetChatSessionID());
                $this->Template->Assign("_displayClockTicker", true);
                $this->Template->Assign("_displayChatPostContainer", true);
                $this->Template->Assign("_inChat", true);
                $this->Template->Assign('_userFullName', text_to_html_entities($_REQUEST['fullname']));
                $this->Template->Assign("_swiftChatURL", SWIFT_Chat::GetChatURL());

                $_parentDepartmentID = $_departmentCache[$_REQUEST['departmentid']]['parentdepartmentid'];

                if (empty($_departmentCache[$_REQUEST['departmentid']]['parentdepartmentid'])) {
                    $this->Template->Assign("_departmentBreadcrumb", text_to_html_entities($_departmentCache[$_REQUEST['departmentid']]['title']));
                } else if (isset($_departmentCache[$_parentDepartmentID])) {
                    $this->Template->Assign("_departmentBreadcrumb", text_to_html_entities($_departmentCache[$_parentDepartmentID]['title']) . ' &raquo; ' . text_to_html_entities($_departmentCache[$_REQUEST['departmentid']]['title']));
                } else {
                    $this->Template->Assign("_departmentBreadcrumb", text_to_html_entities($_departmentCache[$_REQUEST['departmentid']]['title']));
                }

                // Begin Hook: visitor_chat_start
                unset($_hookCode);
                ($_hookCode = SWIFT_Hook::Execute('visitor_chat_start')) ? eval($_hookCode) : false;
                // End Hook

                $this->Template->Render('chatcore');

                return true;
            } else {
                $this->Load->Message(array_merge($_chatArguments, $_POST));
            }
        } else {
            $this->Load->Message(array_merge($_chatArguments, $_POST));
        }

        return false;
    }

    /**
     * The Chat Loop Handler
     *
     * @author Varun Shoor
     * @param array $_argumentContainer The Argument Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function Loop($_argumentContainer = array())
    {
        if (!_is_array($_argumentContainer)) {
            return false;
        }

        $_sessionID = $_chatSessionID = $_chatStatus = $_isFirstTime = false;

        if (isset($_argumentContainer['_sessionID'])) {
            $_sessionID = $_argumentContainer['_sessionID'];
        }

        if (isset($_argumentContainer['_chatSessionID'])) {
            $_chatSessionID = $_argumentContainer['_chatSessionID'];
        }

        if (isset($_argumentContainer['_chatStatus'])) {
            $_chatStatus = (int)($_argumentContainer['_chatStatus']);
        }

        if (isset($_argumentContainer['_isFirstTime'])) {
            $_isFirstTime = (int)($_argumentContainer['_isFirstTime']);
        }

        $_isUserTyping = false;
        if (isset($_argumentContainer['_isTyping']) && $_argumentContainer['_isTyping'] == 'true') {
            $_isUserTyping = true;
        }

        $_filterDepartmentID = 0;
        if (isset($_argumentContainer['_filterDepartmentID'])) {
            $_filterDepartmentID = $_argumentContainer['_filterDepartmentID'];
        }

        $_transfer = 0;
        if (isset($_argumentContainer['_transfer'])) {
            $_transfer = $_argumentContainer['_transfer'];
        }

        $this->_LoadChatLibraries();

        $_guidList = array();
        if (isset($_POST['guid']) && _is_array($_POST['guid'])) {
            $_guidList = $_POST['guid'];
        }
        SWIFT_ChatQueue::DeleteGUID($_guidList);

        if (!$_sessionID || !$_chatSessionID || !$_chatStatus) {
            return false;
        }

        HeaderNoCache();

        header('Content-Type: text/xml');
        $this->ChatEventClient->PrepareClientPacket($_sessionID, $_chatSessionID, $_chatStatus, $_isFirstTime, $_isUserTyping, $_filterDepartmentID, $_transfer);

        return true;
    }

    /**
     * Ends the chat
     *
     * @author Varun Shoor
     * @param string $_chatSessionID The Chat Session ID
     * @param string $_visitorSessionID The Visitor Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function End($_chatSessionID = '', $_visitorSessionID = '')
    {
        if (empty($_chatSessionID) || empty($_visitorSessionID)) {
            return false;
        }

        $this->_LoadChatLibraries();

        $_ChatObject = SWIFT_Chat::GetChatObjectFromSession($_chatSessionID);
        if (!$_ChatObject instanceof SWIFT_Chat || !$_ChatObject->GetIsClassLoaded()) {
            return false;
        }

        // Begin Hook: visitor_chat_end
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('visitor_chat_end')) ? eval($_hookCode) : false;
        // End Hook

        $_ChatObject->EndChat(SWIFT_Chat::CHATEND_CLIENT);

        echo '1';

        return true;
    }

    /**
     * Sends a message from Client to Server
     *
     * @author Varun Shoor
     * @param string $_chatSessionID The Chat Session ID
     * @param string $_visitorSessionID The Visitor Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function SubmitMessage($_chatSessionID = '', $_visitorSessionID = '')
    {
        if (empty($_chatSessionID) || empty($_visitorSessionID)) {
            return false;
        }

        $this->_LoadChatLibraries();

        $_ChatObject = SWIFT_Chat::GetChatObjectFromSession($_chatSessionID);
        if (!$_ChatObject instanceof SWIFT_Chat || !$_ChatObject->GetIsClassLoaded() || !isset($_POST['message']) || trim($_POST['message']) == '') {
            return false;
        }

        // Begin Hook: visitor_chat_messagesubmit
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('visitor_chat_messagesubmit')) ? eval($_hookCode) : false;
        // End Hook

        $_ChatObject->_SWIFT_ChatQueueObject->AddMessageToQueue(SWIFT_ChatQueue::MESSAGE_CLIENT, SWIFT_ChatQueue::SUBMIT_CLIENT, base64_encode(utf8_urldecode($_POST['message'])), true);

        echo '1';

        return true;
    }

    /**
     * Retrieve the file and dispatch it to the user
     *
     * @author Varun Shoor
     * @param int $_fileID The File ID
     * @param string $_fileHash The File Hash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetFile($_fileID, $_fileHash)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_fileID) || empty($_fileHash)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_fileManagerObject = new  SWIFT_FileManager($_fileID);

        // Attempt to compare the hash
        if ($_fileManagerObject->GetProperty('filehash') != $_fileHash) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Once we are done with the comparisons, we need to dispatch the file over
        $_fileManagerObject->Dispatch();

        return true;
    }

    /**
     * Email the conversation
     *
     * @author Varun Shoor
     * @param string $_chatSessionID The Chat Session ID
     * @param string $_visitorSessionID The Visitor Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function SendEmail($_chatSessionID = '', $_visitorSessionID = '')
    {
        if (empty($_chatSessionID) || empty($_visitorSessionID) || !isset($_POST['email']) || empty($_POST['email'])) {
            return false;
        }

        $this->_LoadChatLibraries();

        $_ChatObject = SWIFT_Chat::GetChatObjectFromSession($_chatSessionID);
        if (!$_ChatObject instanceof SWIFT_Chat || !$_ChatObject->GetIsClassLoaded()) {
            return false;
        }

        $_emailAddresses = $_POST['email'];

        $_emailList = array();
        if (stristr($_emailAddresses, ';')) {
            $_emailArray = explode(';', $_emailAddresses);
            foreach ($_emailArray as $_key => $_val) {
                $_val = trim($_val);

                if (IsEmailValid($_val)) {
                    $_emailList[] = $_val;
                }
            }
        } else if (IsEmailValid($_emailAddresses)) {
            $_emailList[] = $_emailAddresses;
        }

        // No valid email found?
        if (!count($_emailList)) {
            return false;
        }

        $_ChatObject->Email($_emailList, '', '', true);

        echo '1';

        return true;
    }

    /**
     * Print the conversation
     *
     * @author Varun Shoor
     * @param string $_chatSessionID The Chat Session ID
     * @param string $_visitorSessionID The Visitor Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function PrintChat($_chatSessionID = '', $_visitorSessionID = '')
    {
        if (empty($_chatSessionID) || empty($_visitorSessionID)) {
            return false;
        }

        $this->_LoadChatLibraries();

        $_ChatObject = SWIFT_Chat::GetChatObjectFromSession($_chatSessionID);
        if (!$_ChatObject instanceof SWIFT_Chat || !$_ChatObject->GetIsClassLoaded()) {
            return false;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');
        $_chatDataArray = $_ChatObject->GetConversationArray(true);

        $this->Template->Assign('_chatDepartment', text_to_html_entities($_departmentCache[$_ChatObject->GetProperty('departmentid')]['title']));
        $this->Template->Assign('_chatFullName', htmlentities($_ChatObject->GetProperty('userfullname')));
        $this->Template->Assign('_chatEmail', htmlspecialchars($_ChatObject->GetProperty('useremail')));
        $this->Template->Assign('_chatSubject', htmlspecialchars($_ChatObject->GetProperty('subject')));
        $this->Template->Assign('_chatStaff', htmlentities($_staffCache[$_ChatObject->GetProperty('staffid')]['fullname']));
        $this->Template->Assign('_chatConversation', $_chatDataArray);

        $this->Template->Render('printchat');

        return true;
    }

    /**
     * Assign the Required Variables
     *
     * @author Varun Shoor
     * @param array $_arguments The Arguments Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected function _AssignVariables($_arguments)
    {
        $_SWIFT = SWIFT::GetInstance();

        $this->Template->Assign('_displayLanguageSelection', true);
        $this->Template->Assign('_chatLanding', true);

        $_chatSessionID = '';
        if (isset($_arguments['_chatSessionID'])) {
            $_chatSessionID = $_arguments['_chatSessionID'];
        } else if (isset($_arguments['chatsessionid'])) {
            $_chatSessionID = $_arguments['chatsessionid'];
        }

        $this->Template->Assign('_chatSessionID', htmlspecialchars($_chatSessionID));

        /* Bug Fix : Simaranjit Singh
         * SWIFT-4324 : Security issue (medium)
         */
        if (isset($_arguments['_filterDepartmentID'])) {
            $this->Template->Assign('_filterDepartmentID', preg_replace('/[^0-9,]+/', '', $_arguments['_filterDepartmentID']));
        } else if (isset($_arguments['filterdepartmentid'])) {
            $this->Template->Assign('_filterDepartmentID', preg_replace('/[^0-9,]+/', '', $_arguments['filterdepartmentid']));
        }

        $_visitorSessionID = '';
        if (isset($_arguments['_sessionID'])) {
            $this->Template->Assign('_getSessionID', htmlspecialchars($_arguments['_sessionID']));
            $this->Template->Assign('_sessionID', htmlspecialchars($_arguments['_sessionID']));
            $_visitorSessionID = $_arguments['_sessionID'];
        } else if (isset($_arguments['sessionid'])) {
            $this->Template->Assign('_sessionID', htmlspecialchars($_arguments['sessionid']));
            $_visitorSessionID = $_arguments['sessionid'];
        }

        $_SWIFT_VisitorObject = new SWIFT_Visitor($_visitorSessionID);

        if (!$_SWIFT_VisitorObject instanceof SWIFT_Visitor || !$_SWIFT_VisitorObject->GetIsClassLoaded()) {
            return false;
        }

        $_hasFootprints = $_SWIFT_VisitorObject->HasFootprints();

        $_uniqueID = substr(BuildHash(), 0, 10);
        $this->Template->Assign('_hasFootprints', $_hasFootprints);
        $this->Template->Assign('_uniqueID', $_uniqueID);

        if (isset($_arguments['_fullName']) && !isset($_arguments['fullname'])) {
            $this->Template->Assign('_getFullName', htmlentities($_arguments['_fullName']));
        }

        if (isset($_arguments['_email']) && !isset($_arguments['email'])) {
            $this->Template->Assign('_getEmail', htmlspecialchars($_arguments['_email']));
        }

        if (isset($_arguments['_subject']) && !isset($_arguments['subject'])) {
            $this->Template->Assign('_getSubject', htmlspecialchars($_arguments['_subject']));
        }

        if ((isset($_arguments['_inline']) && $_arguments['_inline'] == '1') || (isset($_arguments['inline']) && $_arguments['inline'] == '1') || (isset($_POST['inline']) && $_POST['inline'] == '1')) {
            $this->Template->Assign('_isInline', true);
        } else {
            $this->Template->Assign('_isInline', false);
        }

        $this->Cookie->Parse('livechatdetails');

        $_fullName = $_email = $_subject = $_message = '';
        if (isset($_POST['fullname'])) {
            $_fullName = $_POST['fullname'];
        } else if ($this->Cookie->GetVariable('livechatdetails', 'fullname')) {
            $_fullName = $this->Cookie->GetVariable('livechatdetails', 'fullname');
        }

        if (isset($_POST['email'])) {
            $_email = $_POST['email'];
        } else if ($this->Cookie->GetVariable('livechatdetails', 'email')) {
            $_email = $this->Cookie->GetVariable('livechatdetails', 'email');
        }

        /*
         * BUG FIX - Abhishek Mittal
         *
         * SWIFT-1188: Fields for live chat should be filled automatically, if client is logged in.
         *
         * Comments: Apply after SWIFT-692 patch only
         */
        if ($this->_IsUserLoggedIn()) {
            $_SWIFT_UserObject = new SWIFT_User($this->_GetSession()->GetProperty('typeid'));

            $_fullName = IIF(empty($_fullName), $_SWIFT_UserObject->GetProperty('fullname'), $_fullName);
            $_userEmailList = $_SWIFT_UserObject->GetEmailList();
            $_email = IIF(empty($_email), $_userEmailList[0], $_email);
        }

        if (isset($_POST['subject'])) {
            $_subject = $_POST['subject'];
        }

        if (isset($_POST['message'])) {
            $_message = $_POST['message'];
        }

        $this->Template->Assign('_email', htmlspecialchars($_email));
        $this->Template->Assign('_fullName', text_to_html_entities($_fullName));
        $this->Template->Assign('_subject', htmlspecialchars($_subject));
        $this->Template->Assign('_message', htmlspecialchars($_message));
        $this->Template->Assign('_userFullName', text_to_html_entities($_fullName));

        if (isset($_arguments['_userID'])) {
            $this->Template->Assign('_getUserID', (int)($_arguments['_userID']));
        }

        if (isset($_arguments['_departmentID'])) {
            $this->Template->Assign('_departmentID', (int)($_arguments['_departmentID']));
        }

        $this->Template->Assign('_refreshInterval', $this->Settings->Get('livesupport_clientchatrefresh') * 1000);

        return true;
    }

    /**
     * Display the Live Chat Message Form
     *
     * @author Varun Shoor
     * @param array $_messageArguments The Message Arguments
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Message($_messageArguments = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $this->_LoadMessageLibraries();

        if (!is_array($_messageArguments)) {
            return false;
        }

        $_cookieSessionID = $this->Cookie->Get('sessionid' . SWIFT_Interface::INTERFACE_VISITOR);

        if (trim($_cookieSessionID) != '') {
            SWIFT_Session::Start($_SWIFT->Interface);
            $_messageArguments['_sessionID'] = $_cookieSessionID;
        } else if (!empty($_messageArguments['_sessionID'])) {
            SWIFT_Session::Insert($_SWIFT->Interface->GetInterface(), 0, $_messageArguments['_sessionID']);
        } else {
            $_messageArguments['_sessionID'] = SWIFT_Session::InsertAndStart(0);
        }

        if (isset($_messageArguments['sessionid']) && !isset($_messageArguments['_sessionID'])) {
            $_messageArguments['_sessionID'] = $_messageArguments['sessionid'];
        }

        if (isset($_messageArguments['departmentid']) && !isset($_messageArguments['_departmentID'])) {
            $_messageArguments['_departmentID'] = $_messageArguments['departmentid'];
        }

        if (isset($_messageArguments['fullname']) && !isset($_messageArguments['_fullName'])) {
            $_messageArguments['_fullName'] = $_messageArguments['fullname'];
        }

        if (isset($_messageArguments['email']) && !isset($_messageArguments['_email'])) {
            $_messageArguments['_email'] = $_messageArguments['email'];
        }

        if (isset($_messageArguments['subject']) && !isset($_messageArguments['_subject'])) {
            $_messageArguments['_subject'] = $_messageArguments['subject'];
        }

        if (!isset($_messageArguments['_sessionID']) || empty($_messageArguments['_sessionID'])) {
            return false;
        }

        if (!isset($_messageArguments['_departmentID'])) {
            $_messageArguments['_departmentID'] = 0;
        }

        if (!isset($_messageArguments['_filterDepartmentID'])) {
            $_messageArguments['_filterDepartmentID'] = '';
        }

        $this->Template->Assign('_isStaffOnline', '0');
        $this->Template->Assign('_isInline', false);

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@opencart.com.vn>
         *
         * SWIFT-4304 Chat gets initiated while selecting Offline departments.
         *
         * Comment: Selected department should be remain selected.
         */
        if (isset($_messageArguments['_departmentID']) && !empty($_messageArguments['_departmentID'])) {
            $this->Template->Assign('_setDepartmentID', $_messageArguments['_departmentID']);
        }

        if (isset($_messageArguments['_sessionID']) && !empty($_messageArguments['_sessionID'])) {
            $this->Template->Assign('_sessionID', $_messageArguments['_sessionID']);
        } else {
            $this->Template->Assign('_sessionID', '');
        }

        if (isset($_messageArguments['_chatSessionID']) && !empty($_messageArguments['_chatSessionID'])) {
            $this->Template->Assign('_chatSessionID', $_messageArguments['_chatSessionID']);
        } else {
            $this->Template->Assign('_chatSessionID', '');
        }

        if (isset($_messageArguments['_fullName']) && !empty($_messageArguments['_fullName'])) {
            $this->Template->Assign('_fullName', $_messageArguments['_fullName']);
        } else {
            $this->Template->Assign('_fullName', '');
        }

        if (isset($_messageArguments['_email']) && !empty($_messageArguments['_email'])) {
            $this->Template->Assign('_email', $_messageArguments['_email']);
        } else {
            $this->Template->Assign('_email', '');
        }

        if (isset($_messageArguments['_subject']) && !empty($_messageArguments['_subject'])) {
            $this->Template->Assign('_subject', $_messageArguments['_subject']);
        } else {
            $this->Template->Assign('_subject', '');
        }

        if (isset($_messageArguments['_message']) && !empty($_messageArguments['_message'])) {
            $this->Template->Assign('_message', $_messageArguments['_message']);
        } else {
            $this->Template->Assign('_message', '');
        }

        if (isset($_messageArguments['_proactive']) && !empty($_messageArguments['_proactive']) && is_numeric($_messageArguments['_proactive'])) {
            $this->Template->Assign('_proactive', $_messageArguments['_proactive']);
        } else {
            $this->Template->Assign('_proactive', '');
        }


        if (isset($_messageArguments['_captcha']) && $_messageArguments['_captcha'])
        {
            $this->Template->Assign('_captchaError', true);
        }

        $this->Template->Assign('_extendedPromptType', 'message');

        // Get Department Status
        $_messageArguments['_filterDepartmentIDList'] = array();
        $_messageArguments = $this->_ProcessArgumentContainer($_messageArguments);

        $_departmentStatus = SWIFT_Chat::GetDepartmentStatus($_messageArguments['_filterDepartmentIDList'], false, SWIFT::Get('usergroupid'));
        $this->Template->Assign('_departmentStatusContainer', $_departmentStatus);

        $languageID = $this->Cookie->GetVariable('client', 'languageid');
        $_registrationPolicyURL = SWIFT_PolicyLink::RetrieveURL($languageID);
        $this->Template->Assign('_registrationPolicyURL', $_registrationPolicyURL);

        // Captcha
        $this->Template->Assign('_canCaptcha', false);
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('livesupport_captcha') == '1') && !$this->_IsUserLoggedIn()) {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded()) {
                $_captchaHTML = $_CaptchaObject->GetHTML();
                if ($_captchaHTML) {
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

        // Assign All Variables
        $this->_AssignVariables($_messageArguments);

        $this->Template->Render('leavemessage');

        return true;
    }

    /**
     * Process the Submitted Message
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function MessageSubmit($_messageArguments)
    {
        $_SWIFT = SWIFT::GetInstance();


        $this->_LoadMessageLibraries();

        if (!_is_array($_messageArguments)) {
            $_messageArguments = array();
        }

        $_departmentCache = $this->Cache->Get('departmentcache');

        if (isset($_POST['fullname']) && empty($_POST['fullname'])) {
            $_messageArguments['_fullName'] = $_POST['fullname'];
        }

        if (isset($_POST['email']) && empty($_POST['email'])) {
            $_messageArguments['_email'] = $_POST['email'];
        }

        if (isset($_POST['subject']) && empty($_POST['subject'])) {
            $_messageArguments['_subject'] = $_POST['subject'];
        }

        if (isset($_POST['message']) && empty($_POST['message'])) {
            $_messageArguments['_message'] = $_POST['message'];
        }

        if (isset($_POST['departmentid']) && empty($_POST['departmentid'])) {
            $_messageArguments['_departmentID'] = $_POST['departmentid'];
        }
        $this->Template->Assign('_email', '');
        $this->Template->Assign('_isInline', false);

        $_filterDepartmentID = '';
        if (isset($_POST['filterdepartmentid']) && !empty($_POST['filterdepartmentid'])) {
            $_filterDepartmentID = $_POST['filterdepartmentid'];
        }
        $this->Template->Assign('_filterDepartmentID', htmlspecialchars($_filterDepartmentID));

        $_sessionID = false;
        if (isset($_messageArguments['_sessionID']) && !empty($_messageArguments['_sessionID'])) {
            $_sessionID = $_messageArguments['_sessionID'];
        } else if (isset($_POST['sessionid']) && !empty($_POST['sessionid'])) {
            $_sessionID = $_POST['sessionid'];
        }

        // Sanity checks..
        if (!isset($_POST['fullname']) || empty($_POST['fullname']) || !isset($_POST['email']) || empty($_POST['email']) || !isset($_POST['subject']) || !IsEmailValid($_POST['email'])
            || empty($_POST['subject']) || !isset($_POST['message']) || empty($_POST['message']) || !isset($_POST['departmentid'])
            || empty($_POST['departmentid']) || !$_sessionID || !isset($_departmentCache[$_POST['departmentid']])) {
            $this->Message($_messageArguments);

            return true;
        }

        //captcha check
        SWIFT_Session::Start($this->Interface);
        if (!$this->internal_ut && ($this->isSaas || $this->Settings->Get('livesupport_captcha') == '1') && !$this->_IsUserLoggedIn()) {
            $_CaptchaObject = SWIFT_CaptchaManager::GetCaptchaObject();
            if ($_CaptchaObject instanceof SWIFT_CaptchaManager && $_CaptchaObject->GetIsClassLoaded() && !$_CaptchaObject->IsValidCaptcha()) {
                $_messageArguments['_captcha'] = true;
                $this->Message($_messageArguments);
                return true;
            }
        }

        // Begin Hook: visitor_message_submit
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('visitor_message_submit')) ? eval($_hookCode) : false;
        // End Hook

        $_SWIFT_MessageObject = SWIFT_Message::Create($_POST['fullname'], $_POST['email'], $_POST['subject'], $_POST['departmentid'], $_POST['message'], $_SWIFT->TemplateGroup->GetTemplateGroupID());
        if ($_SWIFT_MessageObject) {
            // Assign All Variables

            /*
             * BUG FIX - Simaranjit Singh
             *
             * SWIFT-3508 Undefined index: _uniqueID (./__swift/cache/35a4c3b5da885920b53f4b1ce0752aeb.php:70)
             */
            $_SWIFT_VisitorObject = new SWIFT_Visitor($_sessionID);

            if (!$_SWIFT_VisitorObject instanceof SWIFT_Visitor || !$_SWIFT_VisitorObject->GetIsClassLoaded()) {
                return false;
            }

            $_hasFootprints = $_SWIFT_VisitorObject->HasFootprints();

            $_uniqueID = substr(BuildHash(), 0, 10);
            $this->Template->Assign('_hasFootprints', $_hasFootprints);
            $this->Template->Assign('_uniqueID', $_uniqueID);

            $this->Template->Assign('_extendedPromptType', 'message');
            $this->Template->Assign('_userFullName', '');
            $this->Template->Assign('_isStaffOnline', '0');
            $this->Template->Assign('_displayLanguageSelection', false);
            $this->Template->Assign('_chatLanding', true);
            $this->Template->Assign('_getSessionID', htmlspecialchars($_sessionID));
            $this->Template->Assign('_sessionID', htmlspecialchars($_sessionID));
            $this->Template->Assign('_chatSessionID', '');
            $this->Template->Assign('_messageFullName', text_to_html_entities($_POST['fullname'], 1));
            $this->Template->Assign('_messageEmail', htmlspecialchars($_POST['email']));
            $this->Template->Assign('_messageSubject', htmlspecialchars($_POST['subject']));
            $this->Template->Assign('_departmentID', (int)($_POST['departmentid']));
            $this->Template->Assign('_messageDepartmentTitle', text_to_html_entities($_departmentCache[$_POST['departmentid']]['title']));
            $this->Template->Assign('_messageContents', nl2br(htmlspecialchars($_POST['message'])));
            $this->Template->Assign('_refreshInterval', $this->Settings->Get('livesupport_clientchatrefresh') * 1000);
            $this->Template->Render('leavemessageconfirmation');

            return true;
        }

        return false;
    }

    /**
     * Render the Survey form
     *
     * @author Varun Shoor
     * @param string $_chatSessionID The Chat Session ID
     * @param string $_visitorSessionID The Visitor Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function Survey($_chatSessionID = '', $_visitorSessionID = '')
    {
        if (empty($_chatSessionID) || empty($_visitorSessionID)) {
            return false;
        }

        $this->_LoadChatLibraries();
        $this->_LoadMessageLibraries();

        $_ChatObject = SWIFT_Chat::GetChatObjectFromSession($_chatSessionID);
        if (!$_ChatObject instanceof SWIFT_Chat || !$_ChatObject->GetIsClassLoaded() || $_ChatObject->GetProperty('visitorsessionid') != $_visitorSessionID) {
            return false;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');

        if (!isset($_departmentCache[$_ChatObject->GetProperty('departmentid')])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->ProcessDialogs();
        $this->Template->Assign('_footerScript', $this->UserInterface->GetFooterScript());

        // Assign All Variables
        $this->_AssignVariables(array());

        $this->Template->Assign('_isStaffOnline', '0');
        $this->Template->Assign('_displayLanguageSelection', false);

        $this->Template->Assign('_sessionID', $_visitorSessionID);

        $this->Template->Assign('_chatSessionID', $_chatSessionID);

        $this->Template->Assign('_fullName', text_to_html_entities($_ChatObject->GetProperty('userfullname')));
        $this->Template->Assign('_email', htmlspecialchars($_ChatObject->GetProperty('useremail')));
        $this->Template->Assign('_subject', htmlspecialchars($_ChatObject->GetProperty('subject')));
        $this->Template->Assign('_departmentID', (int)($_ChatObject->GetProperty('departmentid')));
        $this->Template->Assign('_message', '');

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-2307 Chat Survey form bug
         *
         */
        $this->Template->Assign('_filterDepartmentID', '');
        $this->Template->Assign('_isInline', 0);

        $_departmentTitle = $_departmentCache[$_ChatObject->GetProperty('departmentid')]['title'];
        $this->Template->Assign('_surveyDepartmentTitle', text_to_html_entities($_departmentTitle));

        // Custom Fields
        $this->Template->Assign('_isLiveChat', true);
        $this->CustomFieldRendererClient->Render(SWIFT_CustomFieldRenderer::TYPE_FIELDS, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST), 0, $_ChatObject->GetProperty('departmentid'));

        // Process Ratings
        $_chatRatingContainer = SWIFT_Rating::Retrieve(array(SWIFT_Rating::TYPE_CHATSURVEY), false, SWIFT_PUBLIC, $_ChatObject->GetProperty('departmentid'), SWIFT::Get('usergroupid'));

        $_chatRatingIDList = array_keys($_chatRatingContainer);

        $_chatRatingResultContainer = SWIFT_RatingResult::Retrieve($_chatRatingIDList, array($_ChatObject->GetChatObjectID()));

        foreach ($_chatRatingResultContainer as $_ratingID => $_chatRatingResultContainer_Sub) {
            foreach ($_chatRatingResultContainer_Sub as $_chatRatingResult) {
                $_chatRatingContainer[$_ratingID]['result'] = $_chatRatingResult['ratingresult'];

                if ($_chatRatingContainer[$_ratingID]['iseditable'] == '0') {
                    $_chatRatingContainer[$_ratingID]['isdisabled'] = true;
                }
            }
        }

        $this->Template->Assign('_chatRatingCount', count($_chatRatingContainer));
        $this->Template->Assign('_chatRatingContainer', $_chatRatingContainer);

        // Begin Hook: visitor_chat_survey
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('visitor_chat_survey')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->ProcessDialogs();
        $this->Template->Render('chatsurvey');

        return true;
    }

    /**
     * Dispatch the Survey
     *
     * @author Varun Shoor
     * @param string $_chatSessionID The Chat Session ID
     * @param string $_visitorSessionID The Visitor Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function SurveySubmit($_chatSessionID, $_visitorSessionID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_chatSessionID) || empty($_visitorSessionID)) {
            return false;
        }

        $this->_LoadChatLibraries();
        $this->_LoadMessageLibraries();

        $_ChatObject = SWIFT_Chat::GetChatObjectFromSession($_chatSessionID);
        if (!$_ChatObject instanceof SWIFT_Chat || !$_ChatObject->GetIsClassLoaded() || $_ChatObject->GetProperty('visitorsessionid') != $_visitorSessionID) {
            return false;
        }

        // Custom Field Check
        $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST), SWIFT_CustomFieldManager::CHECKMODE_CLIENT);
        if ($_customFieldCheckResult[0] == false) {
            call_user_func_array(array('SWIFT', 'ErrorField'), $_customFieldCheckResult[1]);

            $this->UserInterface->Error(true, $this->Language->Get('requiredfieldempty'));
            $this->Load->Survey($_chatSessionID, $_visitorSessionID);

            return false;
        }

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_staffCache = $this->Cache->Get('staffcache');

        if (!isset($_departmentCache[$_ChatObject->GetProperty('departmentid')])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Assign All Variables
        $this->_AssignVariables(array());

        // Sanity checks..
        if (!isset($_POST['fullname']) || empty($_POST['fullname']) || !isset($_POST['email']) || empty($_POST['email']) || !isset($_POST['message']) || !isset($_POST['departmentid']) || empty($_POST['departmentid']) || !isset($_departmentCache[$_POST['departmentid']])) {
            $this->Load->Survey($_chatSessionID, $_visitorSessionID);

            return true;
        }

        if (!isset($_POST['messagerating'])) {
            $_POST['messagerating'] = '0';
        }

        $_finalSubject = '(no subject)';
        if (isset($_POST['subject']) && !empty($_POST['subject'])) {
            $_finalSubject = $_POST['subject'];
        }

        // Update Custom Field Values
        $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_INSERT, array(SWIFT_CustomFieldGroup::GROUP_LIVECHATPOST), SWIFT_CustomFieldManager::CHECKMODE_CLIENT, $_ChatObject->GetChatObjectID());

        // Process Ratings
        $_chatRatingContainer = SWIFT_Rating::Retrieve(array(SWIFT_Rating::TYPE_CHATSURVEY), false, SWIFT_PUBLIC, $_ChatObject->GetProperty('departmentid'), SWIFT::Get('usergroupid'));

        $_chatRatingIDList = array_keys($_chatRatingContainer);

        $_chatRatingResultContainer = SWIFT_RatingResult::Retrieve($_chatRatingIDList, array($_ChatObject->GetChatObjectID()));

        foreach ($_chatRatingResultContainer as $_ratingID => $_chatRatingResultContainer_Sub) {
            foreach ($_chatRatingResultContainer_Sub as $_chatRatingResult) {
                $_chatRatingContainer[$_ratingID]['result'] = $_chatRatingResult['ratingresult'];

                if ($_chatRatingContainer[$_ratingID]['iseditable'] == '0') {
                    $_chatRatingContainer[$_ratingID]['isdisabled'] = true;
                }
            }
        }

        $_userEmail = $_ChatObject->GetProperty('useremail');
        if (empty($_userEmail)) {
            $_userEmail = $_POST['email'];
        }

        $_SWIFT_UserObject = false;
        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded()) {
            $_SWIFT_UserObject = $_SWIFT->User;
        } else {
            $_userID = SWIFT_Chat::GetOrCreateUserID($_ChatObject->GetProperty('userfullname'), $_userEmail, $_SWIFT->TemplateGroup->GetProperty('regusergroupid'));
            $_SWIFT_UserObject = new SWIFT_User(new SWIFT_DataID($_userID));
        }

        // Begin Hook: visitor_chat_surveysubmitpre
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('visitor_chat_surveysubmitpre')) ? eval($_hookCode) : false;
        // End Hook

        foreach ($_chatRatingContainer as $_ratingID => $_chatRating) {
            if (isset($_chatRating['isdisabled']) && $_chatRating['isdisabled'] == true) {
                continue;
            }

            if (!isset($_POST['rating']) || !isset($_POST['rating'][$_ratingID])) {
                continue;
            }


            $_SWIFT_RatingObject = new SWIFT_Rating((int)($_ratingID));
            if (!$_SWIFT_RatingObject instanceof SWIFT_Rating || !$_SWIFT_RatingObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_RatingResult = SWIFT_RatingResult::CreateOrUpdateIfExists($_SWIFT_RatingObject, $_ChatObject->GetChatObjectID(), $_POST['rating'][$_ratingID], SWIFT_RatingResult::CREATOR_USER, $_SWIFT_UserObject->GetUserID());
            /**
             * BUG FIX: Nidhi Gupta <nidhi.gupta@opencart.com.vn>
             *
             * SWIFT-3052: Rating scale for ticket only displays 5 points even if added more than this.
             *
             */
            $_chatRatingContainer[$_ratingID]['result'] = $_SWIFT_RatingResult->Get('ratingresult');
            $this->Template->Assign('_chatRatingContainer', $_chatRatingContainer);
        }

        $_message = isset($_POST['message']) && !empty($_POST['message']) ? $_POST['message'] : '(no message)';

        $_SWIFT_MessageSurveyObject = SWIFT_MessageSurvey::Create($_POST['fullname'], $_POST['email'], $_finalSubject, $_POST['departmentid'], $_ChatObject->GetProperty('chatobjectid'), $_POST['messagerating'], $_message);
        if ($_SWIFT_MessageSurveyObject !== null) {

            // Begin Hook: visitor_chat_surveysubmitpost
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('visitor_chat_surveysubmitpost')) ? eval($_hookCode) : false;
            // End Hook

            // Assign All Variables
            $this->Template->Assign('_userFullName', '');
            $this->Template->Assign('_isStaffOnline', '0');
            $this->Template->Assign('_displayLanguageSelection', false);
            $this->Template->Assign('_chatLanding', true);
            $this->Template->Assign('_getSessionID', htmlspecialchars($_visitorSessionID));
            $this->Template->Assign('_sessionID', htmlspecialchars($_visitorSessionID));
            $this->Template->Assign('_chatSessionID', htmlspecialchars($_chatSessionID));

            $_departmentTitle = $_departmentCache[$_ChatObject->GetProperty('departmentid')]['title'];
            $this->Template->Assign('_surveyDepartmentTitle', text_to_html_entities($_departmentTitle));

            $this->Template->Assign('_messageRating', htmlspecialchars(SWIFT_MessageSurvey::SanitizeMessageRating($_POST['messagerating'])));
            $this->Template->Assign('_surveyFullName', text_to_html_entities($_POST['fullname']));
            $this->Template->Assign('_surveyEmail', htmlspecialchars($_POST['email']));
            $this->Template->Assign('_surveySubject', htmlspecialchars($_finalSubject));
            $this->Template->Assign('_departmentID', (int)($_POST['departmentid']));
            $this->Template->Assign('_surveyComments', nl2br(htmlspecialchars($_POST['message'])));
            $this->Template->Assign('_refreshInterval', $this->Settings->Get('livesupport_clientchatrefresh') * 1000);

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-2307 Chat Survey form bug
             *
             */
            $this->Template->Assign('_filterDepartmentID', '');
            $this->Template->Assign('_isInline', 0);

            $this->Template->Render('chatsurveyconfirmation');

            return true;
        }

        return false;
    }

    /**
     * Render the CSS Template
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function CSS()
    {
        header('Content-Type: text/css');

        $this->Template->Render('clientcss');

        return true;
    }

    /**
     * Update the Last Chat Cookie timeline
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _UpdateLastChatCookie()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Cookie->Parse('visitor');
        $this->Cookie->AddVariable('visitor', 'lastchat', DATENOW);
        $this->Cookie->Rebuild('visitor', true);

        return true;
    }

    /**
     * @return bool|SWIFT_Session
     * @throws SWIFT_Exception
     */
    private function _GetSession() {
        $_SWIFT = SWIFT::GetInstance();
        return SWIFT_Session::RetrieveSession(Clean($_SWIFT->Cookie->Get('sessionid' . SWIFT_Interface::INTERFACE_CLIENT)), new SWIFT_Interface(SWIFT_Interface::INTERFACE_CLIENT));
    }

    /**
     * @return bool
     * @throws SWIFT_Exception
     */
    private function _IsUserLoggedIn() {
        $_SWIFT_SessionObject = $this->_GetSession();

        return ($_SWIFT_SessionObject instanceof SWIFT_Session && $_SWIFT_SessionObject->GetProperty('typeid') && $_SWIFT_SessionObject->GetProperty('sessiontype') == SWIFT_Interface::INTERFACE_CLIENT);
    }
}
