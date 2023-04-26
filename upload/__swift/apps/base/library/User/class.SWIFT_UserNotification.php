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

namespace Base\Library\User;

use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserOrganization;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_LanguageEngine;
use SWIFT_Library;
use SWIFT_Mail;
use SWIFT_TemplateEngine;

/**
 * The User Notification Management Class
 *
 * @property SWIFT_Mail $Mail
 * @author Varun Shoor
 */
class SWIFT_UserNotification extends SWIFT_Library
{
    protected $User = false;
    static protected $_notificationDispatchCache = array();
    protected $_updateContainer = array();

    // Core Constants
    const TYPE_USER = 1;
    const TYPE_USERORGANIZATION = 2;
    const TYPE_CUSTOM = 3;

    const CONTENT_TEXT = 1;
    const CONTENT_HTML = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the creation fals
     */
    public function __construct(SWIFT_User $_SWIFT_UserObject)
    {
        parent::__construct();

        if (!$this->SetUser($_SWIFT_UserObject)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }
    }

    /**
     * Set the User object
     *
     * @author Varun Shoor
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User object pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetUser(SWIFT_User $_SWIFT_UserObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded()) {
            return false;
        }

        $this->User = $_SWIFT_UserObject;

        return true;
    }

    /**
     * Check to see if its a valid type
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsValidType($_notificationType)
    {
        if ($_notificationType == self::TYPE_USER || $_notificationType == self::TYPE_USERORGANIZATION || $_notificationType == self::TYPE_CUSTOM) {
            return true;
        }

        return false;
    }

    /**
     * Update a property
     *
     * @author Varun Shoor
     * @param string $_title The Property Title
     * @param string $_oldValue The Old Value
     * @param string $_newValue The New Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_title, $_oldValue, $_newValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (empty($_title)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_updateContainer[$_title] = array($_oldValue, $_newValue);

        return true;
    }

    /**
     * Get the Email List for the relevant notification type
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @param array $_customEmailList (OPTIONAL) The Custom Email List
     * @return array The Email List
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid data is provided
     */
    protected function GetEmailList($_notificationType, $_customEmailList = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!self::IsValidType($_notificationType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_emailList = array();

        switch ($_notificationType) {
            case self::TYPE_USER:
                {
                    $_userEmailList = SWIFT_UserEmail::RetrieveList($this->User->GetUserID());

                    foreach ($_userEmailList as $_emailAddress) {
                        if (!in_array($_emailAddress, $_emailList)) {
                            $_emailList[] = $_emailAddress;
                        }
                    }
                }
                break;

            case self::TYPE_USERORGANIZATION:
                {
                    $_SWIFT_UserOrganizationObject = false;
                    try {
                        $_SWIFT_UserOrganizationObject = new SWIFT_UserOrganization($this->User->GetProperty('userorganizationid'));
                    } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                    }

                    if ($_SWIFT_UserOrganizationObject instanceof SWIFT_UserOrganization && $_SWIFT_UserOrganizationObject->GetIsClassLoaded()) {
                        $_userIDList = SWIFT_User::GetUserIDListOnOrganization($_SWIFT_UserOrganizationObject->GetUserOrganizationID());

                        $_userEmailList = SWIFT_UserEmail::RetrieveListOnUserIDList($_userIDList);

                        foreach ($_userEmailList as $_emailAddress) {
                            if (!in_array($_emailAddress, $_emailList)) {
                                $_emailList[] = $_emailAddress;
                            }
                        }
                    }
                }
                break;

            case self::TYPE_CUSTOM:
                {
                    return $_customEmailList;
                }
                break;

            default:
                break;
        }

        return array_merge($_emailList, $_customEmailList);
    }

    /**
     * Retrieve the base properties in Text & HTML
     *
     * @author Varun Shoor
     * @return array array(Text Content, HTML Content)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetBaseContent()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userGroupCache = $this->Cache->Get('usergroupcache');

        $_baseContentsText = $_baseContentsHTML = '';

        // Full Name
        $_baseContentsText .= self::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_fullname')) . $this->User->GetProperty('fullname') . SWIFT_CRLF;
        $_baseContentsHTML .= self::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_fullname')) . $this->User->GetProperty('fullname') . '<br />' . SWIFT_CRLF;

        if ($this->User->GetProperty('userdesignation') != '') {
            // Designation
            $_baseContentsText .= self::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_designation')) . $this->User->GetProperty('userdesignation') . SWIFT_CRLF;
            $_baseContentsHTML .= self::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_designation')) . $this->User->GetProperty('userdesignation') . '<br />' . SWIFT_CRLF;
        }

        $_emailList = $this->User->GetEmailList();
        foreach ($_emailList as $_emailAddress) {
            // Email
            $_baseContentsText .= self::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_email')) . $_emailAddress . SWIFT_CRLF;
            $_baseContentsHTML .= self::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_email')) . $_emailAddress . '<br />' . SWIFT_CRLF;
        }

        if ($this->User->GetProperty('userorganizationid') != '0') {
            $_organizationName = $this->User->GetOrganizationName();

            if (!empty($_organizationName)) {
                // User Organization
                $_baseContentsText .= self::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_organization')) . $_organizationName . SWIFT_CRLF;
                $_baseContentsHTML .= self::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_organization')) . $_organizationName . '<br />' . SWIFT_CRLF;
            }
        }

        if ($this->User->GetProperty('phone') != '') {
            // Phone
            $_baseContentsText .= self::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_phone')) . $this->User->GetProperty('phone') . SWIFT_CRLF;
            $_baseContentsHTML .= self::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_phone')) . $this->User->GetProperty('phone') . '<br />' . SWIFT_CRLF;
        }

        // URL
        $_originalBaseName = SWIFT_BASENAME;
        if (!empty($_originalBaseName)) {
            $_originalBaseName = '/' . $_originalBaseName;
        }

        $_userURL = SWIFT::Get('swiftpath') . 'staff' . $_originalBaseName . '/Base/User/Edit/' . $this->User->GetUserID();
        $_baseContentsText .= self::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_url')) . $_userURL . SWIFT_CRLF;
        $_baseContentsHTML .= self::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_url')) . '<a href="' . $_userURL . '">' . $_userURL . '</a><br />' . SWIFT_CRLF;

        // Created
        $_createdDate = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->User->GetProperty('dateline'));
        $_baseContentsText .= self::GetTitle(self::CONTENT_TEXT, $this->Language->Get('ntitle_created')) . $_createdDate . SWIFT_CRLF;
        $_baseContentsHTML .= self::GetTitle(self::CONTENT_HTML, $this->Language->Get('ntitle_created')) . htmlspecialchars($_createdDate) . '<br />' . SWIFT_CRLF;

        return array($_baseContentsText, $_baseContentsHTML);
    }

    /**
     * Get the Padding for a property title
     *
     * @author Varun Shoor
     * @param mixed $_contentType Content Type
     * @param string $_title The Title
     * @return string|bool Property Padding
     */
    protected static function GetTitle($_contentType, $_title)
    {
        $_baseLine = 18;

        /*
         * BUG FIX - Pankaj Garg
         *
         * SWIFT-3554, User Creation - Error
         *
         * Comments: In function str_repeat ,multiplier has to be greater than or equal to 0.
         */

        $_repeatMultiplier = ($_baseLine - mb_strlen(StripName($_title, 18)));
        if ($_repeatMultiplier < 0) {
            $_repeatMultiplier = 0;
        }

        if ($_contentType == self::CONTENT_TEXT) {
            return str_repeat(' ', $_repeatMultiplier) . $_title;
        } elseif ($_contentType == self::CONTENT_HTML) {
            return str_repeat('&nbsp;', $_repeatMultiplier) . $_title;
        }

        return false;
    }

    /**
     * Prepare the final email content
     *
     * @author Varun Shoor
     * @param string $_notificationContents (OPTIONAL) The Notification Contents
     * @return array array(Text Contents, HTML Contents)
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Prepare($_notificationContents = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // First get the base contents
        $_baseContentContainer = $this->GetBaseContent();
        $_baseContentsText = $_baseContentContainer[0];
        $_baseContentsHTML = $_baseContentContainer[1];

        // Add Notification Contents if necessary
        if (!empty($_notificationContents)) {
            $_baseContentsText .= SWIFT_CRLF . SWIFT_CRLF . $_notificationContents;
            $_baseContentsHTML .= '<br />' . SWIFT_CRLF . '<br />' . SWIFT_CRLF . nl2br(htmlspecialchars($_notificationContents));
        }

        // Now prepare the contents prefix
        $_contentPrefix = $_infoTitle = '';

        if (isset($_SWIFT->Staff) && $_SWIFT->Staff instanceof SWIFT_Staff && $_SWIFT->Staff->GetIsClassLoaded()) {
            $_infoTitle = sprintf($this->Language->Get('ninfoupdatedstaff'), $_SWIFT->Staff->GetProperty('fullname'), $this->User->GetProperty('fullname'));
        } elseif ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_CLIENT || $_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_RSS) {
            $_infoTitle = sprintf($this->Language->Get('ninfoupdateduser'), $this->User->GetProperty('fullname'));
        }

        if ($_infoTitle != '') {
            $_contentPrefix .= $_infoTitle . SWIFT_CRLF;
            $_contentPrefix .= str_repeat('-', mb_strlen($_infoTitle)) . SWIFT_CRLF . SWIFT_CRLF;
        }

        $_contentPropertiesText = $_contentPropertiesHTML = '';
        foreach ($this->_updateContainer as $_updateTitle => $_updateValues) {
            // Old Value == '' && New Value != ''
            if ($_updateValues[0] == '' && $_updateValues[1] != '') {
                $_contentPropertiesText .= self::GetTitle(self::CONTENT_TEXT, $_updateTitle) . $_updateValues[1] . SWIFT_CRLF;
                $_contentPropertiesHTML .= self::GetTitle(self::CONTENT_HTML, $_updateTitle) . $_updateValues[1] . '<br />' . SWIFT_CRLF;

                // Old Value != '' && New Value == ''
            } elseif ($_updateValues[0] != '' && $_updateValues[1] == '') {
                $_contentPropertiesText .= self::GetTitle(self::CONTENT_TEXT, $_updateTitle) . $this->Language->Get('notificationcleared') . sprintf($this->Language->Get('notificationwas'), $_updateValues[0]) . SWIFT_CRLF;
                $_contentPropertiesHTML .= self::GetTitle(self::CONTENT_HTML, $_updateTitle) . $this->Language->Get('notificationcleared') . sprintf($this->Language->Get('notificationwas'), $_updateValues[0]) . '<br />' . SWIFT_CRLF;


                // Old Value != '' && New Value != ''
            } elseif ($_updateValues[0] != '' && $_updateValues[1] != '') {
                $_contentPropertiesText .= self::GetTitle(self::CONTENT_TEXT, $_updateTitle) . $_updateValues[1] . sprintf($this->Language->Get('notificationwas'), $_updateValues[0]) . SWIFT_CRLF;
                $_contentPropertiesHTML .= self::GetTitle(self::CONTENT_HTML, $_updateTitle) . $_updateValues[1] . sprintf($this->Language->Get('notificationwas'), $_updateValues[0]) . '<br />' . SWIFT_CRLF;

            }
        }

        if (count($this->_updateContainer)) {
            $_contentPropertiesText .= SWIFT_CRLF;
            $_contentPropertiesHTML .= '<br />' . SWIFT_CRLF;
        }

        // Prepare the final email
        $_finalEmailContentsText = $_contentPrefix . $_contentPropertiesText . $_baseContentsText;
        $_finalEmailContentsHTML = nl2br(htmlspecialchars($_contentPrefix)) . $_contentPropertiesHTML . $_baseContentsHTML;

        return array($_finalEmailContentsText, $_finalEmailContentsHTML);
    }

    /**
     * Dispatch the email
     *
     * @author Varun Shoor
     * @param mixed $_notificationType The Notification Type
     * @param array $_customEmailList (OPTIONAL) The Custom Email List
     * @param string $_notificationContents (OPTIONAL) The Notification Contents
     * @param string $_customFromName (OPTIONAL) The Custom From Name
     * @param bool $_requireChanges (OPTIONAL) Whether it requires something to be changed
     * @param string $_emailPrefix (OPTIONAL) The Email Prefix
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Dispatch($_notificationType, $_customEmailList = array(), $_notificationContents = '', $_customFromName = '', $_requireChanges = false, $_emailPrefix = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!self::IsValidType($_notificationType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        } elseif ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_SETUP) {
            return false;
        }

        $_event = $this->User->NotificationManager->GetEvent();
        if ($_requireChanges == true && (empty($_notificationContents) && !count($this->_updateContainer) && empty($_event))) {
            return false;
        }

        $_emailList = $this->GetEmailList($_notificationType, $_customEmailList);

        if (!count($_emailList)) {
            return false;
        }

        $_emailContentContainer = $this->Prepare($_notificationContents);

        $this->Load->Library('Mail:Mail');

        // Load the phrases from the database..
        $this->Language->Load('users_notifications', SWIFT_LanguageEngine::TYPE_FILE);

        $this->Template->Assign('_notificationContentsText', $_emailContentContainer[0]);
        $this->Template->Assign('_notificationContentsHTML', $_emailContentContainer[1]);

        $_textEmailContents = $this->Template->Get('emailnotificationuser_text', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('emailnotificationuser_html', SWIFT_TemplateEngine::TYPE_DB);

        $_finalEmailPrefix = '';
        if (!empty($_emailPrefix)) {
            $_finalEmailPrefix = $_emailPrefix . ' - ';
        }

        $_mailSubject = sprintf($this->Language->Get('nusernotsubject'), $_finalEmailPrefix . $this->User->GetProperty('fullname'));

        foreach ($_emailList as $_emailAddress) {
            if (!isset(self::$_notificationDispatchCache[$this->User->GetUserID()])) {
                self::$_notificationDispatchCache[$this->User->GetUserID()] = array();
            }

            if (in_array($_emailAddress, self::$_notificationDispatchCache[$this->User->GetUserID()])) {
                continue;
            }

            $this->Mail = new SWIFT_Mail();

            /*            $_debugMsg = 'To: ' . $_emailAddress . SWIFT_CRLF;
                        $_debugMsg .= 'From Email: ' . $this->Settings->Get('general_returnemail') . SWIFT_CRLF;
                        $_debugMsg .= 'From Name: ' . IIF(empty($_customFromName), SWIFT::Get('companyname'), $_customFromName) . SWIFT_CRLF;
                        $_debugMsg .= 'Subject: ' . $_mailSubject . SWIFT_CRLF;
                        $_debugMsg .= 'Contents: ' . SWIFT_CRLF . $_textEmailContents . SWIFT_CRLF;
                        echo nl2br($_debugMsg);*/

            $this->Mail->SetToField($_emailAddress);
            $this->Mail->SetFromField($this->Settings->Get('general_returnemail'),
                IIF(empty($_customFromName), SWIFT::Get('companyname'), $_customFromName));
            $this->Mail->SetSubjectField($_mailSubject);
            $this->Mail->SetDataText($_textEmailContents);
            $this->Mail->SetDataHTML($_htmlEmailContents);

            $this->Mail->SendMail();
            self::$_notificationDispatchCache[$this->User->GetUserID()][] = $_emailAddress;
        }

        return true;
    }
}

?>
