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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Models\Staff;

use Base\Models\CustomField\SWIFT_CustomFieldGroupPermission;
use Base\Models\User\SWIFT_UserVerifyHash;
use SWIFT;
use SWIFT_App;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Interface;
use SWIFT_Loader;
use Base\Library\LoginShare\SWIFT_LoginShareStaff;
use SWIFT_Mail;
use SWIFT_Model;
use SWIFT_ReportSchedule;
use SWIFT_Session;
use Base\Library\Staff\SWIFT_Staff_Exception;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Models\Draft\SWIFT_TicketDraft;
use Tickets\Models\Escalation\SWIFT_EscalationPath;
use Tickets\Models\Note\SWIFT_TicketNoteManager;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack;

/**
 * The Staff Management Class
 *
 * @property SWIFT_Mail $Mail
 * @author Varun Shoor
 */
class SWIFT_Staff extends SWIFT_Model
{
    const TABLE_NAME = 'staff';
    const PRIMARY_KEY = 'staffid';

    const TABLE_STRUCTURE = "staffid I PRIMARY AUTO NOTNULL,
                                firstname C(100) DEFAULT '' NOTNULL,
                                lastname C(100) DEFAULT '' NOTNULL,
                                fullname C(200) DEFAULT '' NOTNULL,
                                username C(100) DEFAULT '' NOTNULL,
                                staffpassword C(100) DEFAULT '' NOTNULL,
                                islegacypassword I2 DEFAULT '0' NOTNULL,
                                designation C(200) DEFAULT '' NOTNULL,
                                greeting C(255) DEFAULT '' NOTNULL,
                                staffgroupid I DEFAULT '0' NOTNULL,
                                email C(255) DEFAULT '' NOTNULL,
                                mobilenumber C(20) DEFAULT '' NOTNULL,
                                statusmessage C(255) DEFAULT '' NOTNULL,
                                lastprofileupdate I DEFAULT '0' NOTNULL,
                                lastvisit I DEFAULT '0' NOTNULL,
                                lastvisit2 I DEFAULT '0' NOTNULL,
                                lastactivity I DEFAULT '0' NOTNULL,
                                enabledst I2 DEFAULT '0' NOTNULL,
                                startofweek I DEFAULT '1' NOTNULL,
                                pmunread I DEFAULT '0' NOTNULL,
                                groupassigns I2 DEFAULT '1' NOTNULL,
                                enablepmalerts I2 DEFAULT '1' NOTNULL,
                                enablepmjsalerts I2 DEFAULT '1' NOTNULL,
                                ticketviewid I DEFAULT '0' NOTNULL,
                                isenabled I2 DEFAULT '1' NOTNULL,
                                passwordupdatetimeline I DEFAULT '0' NOTNULL,
                                iprestriction C(255) DEFAULT '' NOTNULL,
                                timezonephp C(100) DEFAULT '' NOTNULL";

    const INDEX_1 = 'staffgroupid';


    static private $_assignCacheContainer = array();

    protected $_dataStore = array();

    private $_staffPermissionCache = array();

    /**
     * Retrieves the Staf ID
     *
     * @author Varun Shoor
     * @return mixed "staffid" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function GetStaffID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['staffid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT staff.*, staffgroup.*, staffgroup.title AS grouptitle, signatures.signature
                FROM " . TABLE_PREFIX . "staff AS staff
                LEFT JOIN " . TABLE_PREFIX . "signatures AS signatures ON (staff.staffid = signatures.staffid)
                LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON (staff.staffgroupid = staffgroup.staffgroupid)
                WHERE staff.staffid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['staffid']) && !empty($_dataStore['staffid'])) {
                $this->_dataStore = $_dataStore;

                SWIFT::Set('activestaffcount', self::ActiveStaffCount());

                $this->JavaScript->ProcessPayload();

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['staffid']) || empty($this->_dataStore['staffid'])) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Get the Permission for this Staff
     *
     * @author Varun Shoor
     * @param string $_permissionKey The Permission Identifier
     * @return string "1" on Success, "0" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPermission($_permissionKey)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Always return false, to prevent any action if the class failed to load
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_staffPermissionCache[$_permissionKey])) {
            return '1';
        } else if (isset($this->_staffPermissionCache[$_permissionKey])) {
            return $this->_staffPermissionCache[$_permissionKey];
        }

        // Seems like this permission isnt set, we return true
        return '1';
    }

    /**
     * Get the Permission for this Staff on a given Department
     *
     * @author Varun Shoor
     * @param int $_departmentID The Department ID
     * @param string $_permissionKey The Permission Identifier
     * @return string "1" on Success, "0" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDepartmentPermission($_departmentID, $_permissionKey)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffPermissionsCache = $_SWIFT->Cache->Get('staffpermissionscache');

        // Always return false, to prevent any action if the class failed to load
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($_staffPermissionsCache[$this->GetStaffID()][$_departmentID][$_permissionKey])) {
            return '1';
        } else if (isset($_staffPermissionsCache[$this->GetStaffID()][$_departmentID][$_permissionKey])) {
            return $_staffPermissionsCache[$this->GetStaffID()][$_departmentID][$_permissionKey];
        }

        // Seems like this permission isnt set, we return true
        return '1';
    }

    /**
     * Loads the Staff Data into $_SWIFT Variable
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Class is not Loaded or If Data Provided is Invalid
     */
    public function LoadIntoSWIFTNamespace()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        } else if ($_SWIFT->Staff instanceof SWIFT_Staff && $_SWIFT->Staff->GetIsClassLoaded()) {
            return true;
        }

        $_groupSettingCache = $_SWIFT->Cache->Get('groupsettingcache');
        $_staffPermissionCache = $_SWIFT->Cache->Get('staffpermissionscache');
        $_staffGroupCache = $_SWIFT->Cache->Get('staffgroupcache');

        $_staff = $this->GetDataStore();
        $_staffGroupID = $_staff['staffgroupid'];

        $_isAdmin = false;
        if (isset($_staffGroupCache[$_staffGroupID]) && $_staffGroupCache[$_staffGroupID]['isadmin'] == '1') {
            $_isAdmin = true;
        }

        $this->Template->Assign('_staffIsAdmin', $_isAdmin);

        $_permissionContainer = array();
        if (_is_array($_staff) && isset($_groupSettingCache[$_staffGroupID]) && is_array($_groupSettingCache[$_staffGroupID])) {
            $_permissionContainer = $_groupSettingCache[$_staffGroupID];
        }

        $_SWIFT->SetClass('Staff', $this);

        if (isset($_staffPermissionCache[$this->GetStaffID()]) && is_array($_staffPermissionCache[$this->GetStaffID()])) {
            $_permissionContainer = array_merge($_permissionContainer, $_staffPermissionCache[$this->GetStaffID()]);
        }

        $this->_staffPermissionCache = $_permissionContainer;

        $this->Template->Assign('staffname', text_to_html_entities($this->GetProperty('fullname')));
        $this->Template->Assign('username', htmlspecialchars($this->GetProperty('username')));

        // If the staff user has chosen a timezone to override the default, use it here.
        if ($this->GetProperty('timezonephp') != '') // '' is "use default"
        {
            SWIFT::Set('timezone', $this->GetProperty('timezonephp'));
            SWIFT::Set('daylightsavings', ($this->GetProperty('enabledst') != '0') ? true : false);

            // Set the override timezone in PHP.
            if (!date_default_timezone_set(SWIFT::Get('timezone'))) {
                date_default_timezone_set('GMT');
            }
        }

        return true;
    }

    /**
     * Updates the last visit timeline for this staff
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function UpdateLastVisit()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('lastvisit', $this->GetProperty('lastvisit2'));
        $this->UpdatePool('lastvisit2', DATENOW);

        $this->ProcessUpdatePool();
    }

    /**
     * Updates the last activity timeline for this staff
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function UpdateLastActivity()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('lastactivity', DATENOW);

        $this->ProcessUpdatePool();
    }

    /**
     * Checks to see if current staff user is an admin
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function IsAdmin()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetProperty('isadmin') == 1) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the computed password
     *
     * @author Varun Shoor
     * @param string $_staffPassword The Staff Password
     * @return string The Computed Password
     */
    public static function GetComputedPassword($_staffPassword)
    {
        /*
         * ###############################################
         * TODO: Add Installation Hash + Key License # as salt
         * ###############################################
         */

        return sha1($_staffPassword);
    }

    /**
     * Insert a new staff member
     *
     * @author Varun Shoor
     * @param string $_firstName The Staff First Name
     * @param string $_lastName The Staff Last Name
     * @param string $_designation The Staff Designation
     * @param string $_userName The Staff User Name
     * @param string $_staffPassword The Staff Password
     * @param int $_staffGroupID The Staff Group ID
     * @param string $_email The Staff Email Address
     * @param string $_mobileNumber The Mobile Number
     * @param string $_signature The Staff Signature
     * @param bool $_groupAssigns Whether Group Departments should be used
     * @param bool $_isEnabled (OPTIONAL) Whether Staff is Enabled
     * @param string $_greeting (OPTIONAL) The Live Chat Greeting
     * @param string $_ipRestriction (OPTIONAL) The IP Restriction subnet or IP
     * @param string $_timeZonePHP (OPTIONAL) The Time Zone String
     * @param bool $_enableDST (OPTIONAL) Whether to enable Daylight Savings time
     * @return SWIFT_Staff
     * @throws SWIFT_Staff_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_firstName, $_lastName, $_designation, $_userName, $_staffPassword, $_staffGroupID, $_email, $_mobileNumber,
                                  $_signature, $_groupAssigns = true, $_isEnabled = true, $_greeting = '', $_ipRestriction = '', $_timeZonePHP = '', $_enableDST = true)
    {

        $_SWIFT = SWIFT::GetInstance();

        if ((empty($_firstName) && empty($_lastName)) || empty($_userName) || empty($_staffPassword) ||
            empty($_staffGroupID) || empty($_email) || !SWIFT_StaffGroup::IsValidStaffGroupID($_staffGroupID)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_fullName = $_firstName . ' ' . $_lastName;

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'staff', array('firstname' => trim($_firstName), 'lastname' => trim($_lastName),
            'designation' => trim($_designation), 'fullname' => trim($_fullName), 'username' => trim($_userName),
            'staffpassword' => self::GetComputedPassword($_staffPassword), 'islegacypassword' => '0', 'staffgroupid' => $_staffGroupID,
            'email' => strtolower(trim($_email)), 'mobilenumber' => ReturnNone($_mobileNumber), 'lastvisit' => DATENOW, 'lastactivity' => DATENOW,
            'startofweek' => '0', 'pmunread' => '0', 'groupassigns' => (int)($_groupAssigns),
            'enablepmalerts' => '1', 'enablepmjsalerts' => '1', 'isenabled' => (int)($_isEnabled), 'greeting' => $_greeting,
            'passwordupdatetimeline' => DATENOW, 'iprestriction' => $_ipRestriction, 'timezonephp' => $_timeZonePHP,
            'enabledst' => (int)($_enableDST)), 'INSERT');

        $_staffID = $_SWIFT->Database->Insert_ID();
        if (!$_staffID) {
            throw new SWIFT_Staff_Exception(SWIFT_CREATEFAILED);
        }

        // Add the staff signature
        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'signatures', array('dateline' => DATENOW, 'staffid' => $_staffID, 'signature' => ReturnNone($_signature)), 'INSERT');

        self::RebuildCache();

        SWIFT_StaffSettings::RebuildCache();

        /*
         * ==============================
         * TODO: Insert the Custom Field Permission
         * ==============================
         */


        return new SWIFT_Staff(new SWIFT_DataID($_staffID));
    }

    /**
     * Update the Staff Record
     *
     * @author Varun Shoor
     * @param string $_firstName The Staff First Name
     * @param string $_lastName The Staff Last Name
     * @param string $_designation The Staff Designation
     * @param string $_userName The Staff User Name
     * @param string $_staffPassword The Staff Password
     * @param int $_staffGroupID The Staff Group ID
     * @param string $_email The Staff Email Address
     * @param string $_mobileNumber The Mobile Number
     * @param string $_signature The Staff Signature
     * @param bool $_groupAssigns Whether Group Departments should be used
     * @param bool $_isEnabled (OPTIONAL) Whether Staff is Enabled
     * @param string $_greeting (OPTIONAL) The Live Chat Greeting
     * @param string $_ipRestriction (OPTIONAL) The IP Restriction subnet or IP
     * @param string $_timeZonePHP (OPTIONAL) The Time Zone String
     * @param bool $_enableDST (OPTIONAL) Whether to enable Daylight Savings time
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_firstName, $_lastName, $_designation, $_userName, $_staffPassword, $_staffGroupID, $_email, $_mobileNumber, $_signature,
                           $_groupAssigns, $_isEnabled = true, $_greeting = '', $_ipRestriction = '', $_timeZonePHP = '', $_enableDST = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        } else if ((empty($_firstName) || empty($_lastName)) || empty($_userName) || empty($_staffGroupID) || empty($_email) || !SWIFT_StaffGroup::IsValidStaffGroupID($_staffGroupID)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_fullName = $_firstName . ' ' . $_lastName;

        $this->UpdatePool('firstname', trim($_firstName));
        $this->UpdatePool('lastname', trim($_lastName));
        $this->UpdatePool('designation', trim($_designation));
        $this->UpdatePool('fullname', trim($_fullName));
        $this->UpdatePool('username', trim($_userName));
        $this->UpdatePool('staffgroupid', $_staffGroupID);
        $this->UpdatePool('email', strtolower(trim($_email)));
        $this->UpdatePool('mobilenumber', $_mobileNumber);
        $this->UpdatePool('iprestriction', $_ipRestriction);
        $this->UpdatePool('groupassigns', (int)($_groupAssigns));
        $this->UpdatePool('isenabled', (int)($_isEnabled));
        $this->UpdatePool('greeting', trim($_greeting));
        $this->UpdatePool('timezonephp', $_timeZonePHP);
        $this->UpdatePool('enabledst', (int)($_enableDST));

        if (!empty($_staffPassword)) {
            $this->UpdatePool('staffpassword', self::GetComputedPassword($_staffPassword));
            $this->UpdatePool('passwordupdatetimeline', DATENOW);
            $this->UpdatePool('islegacypassword', '0');
        }

        $this->ProcessUpdatePool();

        // Update the staff signature
        $this->Database->AutoExecute(TABLE_PREFIX . 'signatures', array('signature' => $_signature), 'UPDATE', "staffid = '" . (int)($this->GetStaffID()) . "'");

        self::RebuildCache();

        // Update other properties
        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
            SWIFT_Ticket::UpdateGlobalProperty('ownerstaffname', $_fullName, 'ownerstaffid', $this->GetStaffID());

            SWIFT_Loader::LoadModel('Draft:TicketDraft', APP_TICKETS);
            SWIFT_TicketDraft::UpdateGlobalProperty('staffname', $_fullName, 'staffid', $this->GetStaffID());
            SWIFT_TicketDraft::UpdateGlobalProperty('editedstaffname', $_fullName, 'editedbystaffid', $this->GetStaffID());

            SWIFT_Loader::LoadModel('AuditLog:TicketAuditLog', APP_TICKETS);
            SWIFT_TicketAuditLog::UpdateGlobalProperty('creatorfullname', $_fullName, 'creatorid', $this->GetStaffID(), " AND creatortype = '" . SWIFT_TicketAuditLog::CREATOR_STAFF . "'");

            SWIFT_Loader::LoadModel('Note:TicketNoteManager', APP_TICKETS);
            SWIFT_TicketNoteManager::UpdateGlobalProperty('staffname', $_fullName, 'staffid', $this->GetStaffID());
            SWIFT_TicketNoteManager::UpdateGlobalProperty('editedstaffname', $_fullName, 'editedstaffid', $this->GetStaffID());

            SWIFT_Loader::LoadModel('Escalation:EscalationPath', APP_TICKETS);
            SWIFT_EscalationPath::UpdateGlobalProperty('ownerstaffname', $_fullName, 'ownerstaffid', $this->GetStaffID());

            SWIFT_Loader::LoadModel('TimeTrack:TicketTimeTrack', APP_TICKETS);
            SWIFT_TicketTimeTrack::UpdateGlobalProperty('creatorstaffname', $_fullName, 'creatorstaffid', $this->GetStaffID());
            SWIFT_TicketTimeTrack::UpdateGlobalProperty('editedstaffname', $_fullName, 'editedstaffid', $this->GetStaffID());
            SWIFT_TicketTimeTrack::UpdateGlobalProperty('workerstaffname', $_fullName, 'workerstaffid', $this->GetStaffID());
        }

        return true;
    }

    /**
     * Update the Staff Record
     *
     * @author Varun Shoor
     * @param string $_firstName The Staff First Name
     * @param string $_lastName The Staff Last Name
     * @param string $_designation The Staff Designation
     * @param string $_userName The Staff User Name
     * @param int $_staffGroupID The Staff Group ID
     * @param string $_email The Staff Email Address
     * @param string $_mobileNumber The Mobile Number
     * @param string $_signature The Staff Signature
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateLoginShare($_firstName, $_lastName, $_designation, $_userName, $_staffGroupID, $_email, $_mobileNumber, $_signature)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_firstName) || (empty($_firstName) && empty($_lastName)) || empty($_userName) || empty($_staffGroupID) || empty($_email) ||
            !SWIFT_StaffGroup::IsValidStaffGroupID($_staffGroupID)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $_fullName = $_firstName . ' ' . $_lastName;

        $this->UpdatePool('firstname', trim($_firstName));
        $this->UpdatePool('lastname', trim($_lastName));
        $this->UpdatePool('designation', trim($_designation));
        $this->UpdatePool('fullname', trim($_fullName));
        $this->UpdatePool('username', trim($_userName));
        $this->UpdatePool('staffgroupid', $_staffGroupID);
        $this->UpdatePool('email', strtolower(trim($_email)));
        $this->UpdatePool('mobilenumber', $_mobileNumber);

        $this->ProcessUpdatePool();

        // Update the staff signature
        $this->Database->AutoExecute(TABLE_PREFIX . 'signatures', array('signature' => $_signature), 'UPDATE', "staffid = '" . (int)($this->GetStaffID()) . "'");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the current Staff record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetStaffID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Run the Forgot Password Routines
     *
     * @author Werner Garcia
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Staff Verification Hash Creation Fails
     */
    public function ForgotPassword()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffVerifyHashID = SWIFT_UserVerifyHash::Create(SWIFT_UserVerifyHash::TYPE_FORGOTPASSWORD, $this->GetStaffID());
        if (!$_staffVerifyHashID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $this->DispatchForgotPasswordEmail($_staffVerifyHashID);

        return true;
    }

    /**
     * Dispatches the Forgot Password Email
     *
     * @author Werner Garcia
     * @param string $_staffVerifyHashID The Forgot Password Hash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function DispatchForgotPasswordEmail($_staffVerifyHashID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_staffVerifyHashID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->Load->Library('Mail:Mail');

        $_validationLink = SWIFT::Get('basename') . $this->Template->GetTemplateGroupPrefix() . '/Base/StaffLostPassword/Validate/' . $_staffVerifyHashID;

        $_emailValidationContents = sprintf($this->Language->Get('forgotpasswordemail'), SWIFT::Get('companyname'), SWIFT::Get('swiftpath') . 'index.php?' . $this->Template->GetTemplateGroupPrefix(), $_validationLink);

        $this->Mail->SetFromField($this->Settings->Get('general_returnemail'), SWIFT::Get('companyname'));
        $this->Mail->SetToField($this->GetProperty('email'));
        $this->Mail->SetSubjectField(sprintf($this->Language->Get('lostpasswordemailsubject'), SWIFT::Get('companyname')));
        $this->Mail->SetDataText($_emailValidationContents);
        $this->Mail->SetDataHTML(nl2br($_emailValidationContents));
        $this->Mail->SendMail();

        return true;
    }

    /**
     * Change the password for the staff
     *
     * @author Varun Shoor
     * @param string $_newPassword The new staff password
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ChangePassword($_newPassword)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_newPassword)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('staffpassword', self::GetComputedPassword($_newPassword));
        $this->UpdatePool('passwordupdatetimeline', DATENOW);
        $this->UpdatePool('islegacypassword', '0');
        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Update the Staff from Preferences
     *
     * @author Varun Shoor
     * @param string $_firstName The Staff First Name
     * @param string $_lastName The Staff Last Name
     * @param string $_email The Staff Email Address
     * @param string $_mobileNumber The Mobile Number
     * @param string $_signature The Staff Signature
     * @param string $_greeting (OPTIONAL) The Live Chat Greeting
     * @param string $_timeZonePHP (OPTIONAL) The Time Zone String
     * @param bool $_enableDST (OPTIONAL) Whether to enable Daylight Savings time
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdatePreferences($_firstName, $_lastName, $_email, $_mobileNumber, $_signature, $_greeting = '', $_timeZonePHP = '', $_enableDST = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        } else if ((empty($_firstName) || empty($_lastName)) || empty($_email)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        $fields = [
           '_firstName' => $_firstName,
           '_lastName' => $_lastName,
           '_email' => $_email,
           '_mobileNumber' => $_mobileNumber,
           '_greeting' => $_greeting,
           '_timeZonePHP' => $_timeZonePHP
        ];
        foreach ($fields as $key => $value) {
            $fields[$key] = str_replace('-- EMPTY HTML --', '', removeTags($value, true));
        }
        extract($fields);

        $_fullName = $_firstName . ' ' . $_lastName;

        $this->UpdatePool('firstname', trim($_firstName));
        $this->UpdatePool('lastname', trim($_lastName));
        $this->UpdatePool('fullname', trim($_fullName));
        $this->UpdatePool('email', strtolower(trim($_email)));
        $this->UpdatePool('mobilenumber', $_mobileNumber);
        $this->UpdatePool('greeting', trim($_greeting));
        $this->UpdatePool('timezonephp', $_timeZonePHP);
        $this->UpdatePool('enabledst', (int)($_enableDST));

        $this->ProcessUpdatePool();

        // Update the staff signature
        $this->Database->AutoExecute(TABLE_PREFIX . 'signatures', array('signature' => $_signature), 'UPDATE', "staffid = '" . (int)($this->GetStaffID()) . "'");

        self::RebuildCache();

        return true;
    }

    /**
     * Check to see if password has expired
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Staff_Exception If the Class is not Loaded
     */
    public function HasPasswordExpired()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetProperty('islegacypassword') == '1') {
            return true;
        }

        $_passwordExpiry = (int)($this->Settings->Get('security_sppexpiredays'));
        if (empty($_passwordExpiry) || $_passwordExpiry < 1) {
            return false;
        }

        $_passwordUpdateTimeline = (int)($this->GetProperty('passwordupdatetimeline'));
        if (empty($_passwordUpdateTimeline)) {
            return false;
        }

        $_dateThreshold = $_passwordUpdateTimeline + ($_passwordExpiry * 86400);

        if (DATENOW > $_dateThreshold) {
            return true;
        }

        return false;
    }

    /**
     * Enable a given Staff ID List
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function EnableStaffList($_staffIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("UPDATE " . TABLE_PREFIX . "staff SET isenabled ='1' WHERE staffid IN (" . BuildIN($_staffIDList) . ")");

        self::RebuildCache();

        return true;
    }

    /**
     * Disables the Given Staff ID List
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DisableStaffList($_staffIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("UPDATE " . TABLE_PREFIX . "staff SET isenabled ='0' WHERE staffid IN (" . BuildIN($_staffIDList) . ")");

        SWIFT_Session::KillSessionListOnType(array(SWIFT_Interface::INTERFACE_API, SWIFT_Interface::INTERFACE_STAFF, SWIFT_Interface::INTERFACE_INTRANET, SWIFT_Interface::INTERFACE_ADMIN,
            SWIFT_Interface::INTERFACE_WINAPP, SWIFT_Interface::INTERFACE_PDA, SWIFT_Interface::INTERFACE_RSS, SWIFT_Interface::INTERFACE_SYNCWORKS, SWIFT_Interface::INTERFACE_INSTAALERT,
            SWIFT_Interface::INTERFACE_MOBILE, SWIFT_Interface::INTERFACE_STAFFAPI), $_staffIDList);

        self::RebuildCache();

        return true;
    }

    /**
     * Delete Staff based on given list
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_staffIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_alertRuleIDList = $_ticketFilterIDList = array();

        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_finalText = '';
        $_index = 1;

        $_finalStaffIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff WHERE staffid IN (" . BuildIN($_staffIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalStaffIDList[] = $_SWIFT->Database->Record['staffid'];

            $_finalText .= $_index . '. ' . text_to_html_entities($_SWIFT->Database->Record['fullname']) . ' (' . htmlspecialchars($_SWIFT->Database->Record['email']) . ")<BR />\n";

            $_index++;
        }

        if (!count($_finalStaffIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelstaffmul'), count($_finalStaffIDList)), sprintf($_SWIFT->Language->Get('msgdelstaffmul'), $_finalText));

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "staff WHERE staffid IN (" . BuildIN($_finalStaffIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "signatures WHERE staffid IN (" . BuildIN($_finalStaffIDList) . ")");

        SWIFT_StaffAssign::DeleteOnStaff($_finalStaffIDList);
        SWIFT_StaffSettings::DeleteOnStaffList($_finalStaffIDList);
        SWIFT_StaffProfileImage::DeleteOnStaff($_finalStaffIDList);

        SWIFT_CustomFieldGroupPermission::DeleteOnStaff($_finalStaffIDList);

        // Clear Properties
        SWIFT_StaffProperty::DeleteOnStaff($_finalStaffIDList);

        // Clear schedules
        SWIFT_ReportSchedule::DeleteOnStaff($_finalStaffIDList);

        // Kill the Sessions
        SWIFT_Session::KillSessionListOnType(array(SWIFT_Interface::INTERFACE_API, SWIFT_Interface::INTERFACE_STAFF, SWIFT_Interface::INTERFACE_INTRANET, SWIFT_Interface::INTERFACE_ADMIN,
            SWIFT_Interface::INTERFACE_WINAPP, SWIFT_Interface::INTERFACE_PDA, SWIFT_Interface::INTERFACE_RSS, SWIFT_Interface::INTERFACE_SYNCWORKS, SWIFT_Interface::INTERFACE_INSTAALERT,
            SWIFT_Interface::INTERFACE_MOBILE, SWIFT_Interface::INTERFACE_STAFFAPI), $_finalStaffIDList);

        self::RebuildCache();

        return true;
    }

    /**
     * Authenticate the Staff Member
     *
     * @author Varun Shoor
     * @param string $_username The Staff Username
     * @param string $_password The Staff Password
     * @param bool $_shouldBeAdmin Whether the staff is required to be an admin
     * @param bool $_computeHash Whether the hashing should be done in the function itself
     * @return bool|SWIFT_Staff "true" on Success, "false" otherwise
     */
    public static function Authenticate($_username, $_password, $_shouldBeAdmin = false, $_computeHash = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_finalPassword = $_password;
        if ($_computeHash == true) {
            $_finalPassword = self::GetComputedPassword($_password);
        }

        // LoginShare Logic
        if (SWIFT_LoginShareStaff::IsActive()) {
            $_SWIFT_LoginShareStaffObject = new SWIFT_LoginShareStaff();
            if (!$_SWIFT_LoginShareStaffObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA . 'LOGINSHARE');
            }

            $_loginShareResult = $_SWIFT_LoginShareStaffObject->Authenticate($_username, $_password, SWIFT::Get('IP'), $_shouldBeAdmin);

            // Ok so we failed with loginshare in admin cp, we allow it to fall back
            if ($_shouldBeAdmin && !$_loginShareResult) {
                // Fallback
            } else {
                return $_loginShareResult;
            }
        }

        $_staff = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staff AS staff WHERE staff.username = '" . $_SWIFT->Database->Escape($_username) . "'");
        if (!isset($_staff['staffid']) || empty($_staff['staffid'])) {
            return false;
        }

        if ($_staff['islegacypassword'] == '1') {
            $_finalPassword = md5($_password);
        }

        if ($_staff['staffpassword'] != $_finalPassword || empty($_finalPassword)) {
            return false;
        }

        $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($_staff['staffid']));
        if (!$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded()) {
            return false;
        }

        $_SWIFT_StaffObject->LoadIntoSWIFTNamespace();

        $_ipMatch = false;
        if ($_SWIFT_StaffObject->GetProperty('iprestriction') == '') {
            $_ipMatch = true;
        } else {
            // We need to match the IP
            $_ipMatch = $_SWIFT_StaffObject->ValidateIP();
        }

        if ($_SWIFT_StaffObject->GetProperty('staffid') && $_SWIFT_StaffObject->GetProperty('isenabled') == '1' && $_ipMatch == true) {
            if ($_shouldBeAdmin == true && $_SWIFT_StaffObject->IsAdmin() == "1") {
                $_SWIFT_StaffObject->UpdateLastVisit();

                return $_SWIFT_StaffObject;
            } else if ($_shouldBeAdmin == true && $_SWIFT_StaffObject->IsAdmin() != "1") {
                SWIFT::Set('errorstring', $_SWIFT->Language->Get('staff_not_admin'));

                return false;
            } else if ($_shouldBeAdmin == false) {
                $_SWIFT_StaffObject->UpdateLastVisit();

                return $_SWIFT_StaffObject;
            }

        } else if ($_ipMatch != true) {
            SWIFT::Set('errorstring', $_SWIFT->Language->Get('invaliduseripres'));

            return false;

        } else if ($_SWIFT_StaffObject->GetProperty('isenabled') == '0') {
            SWIFT::Set('errorstring', $_SWIFT->Language->Get('invaliduserdisabled'));

            return false;
        } else {
            SWIFT::Set('errorstring', $_SWIFT->Language->Get('invaliduser'));

            return false;
        }

        SWIFT::Set('errorstring', $_SWIFT->Language->Get('invaliduser'));

        return false;
    }

    /**
     * Rebuild the Staff Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT staff.*, staffgroup.*, signatures.*, staff.staffid AS sstaffid FROM " . TABLE_PREFIX . "staff AS staff
            LEFT JOIN " . TABLE_PREFIX . "signatures AS signatures ON (staff.staffid = signatures.staffid)
            LEFT JOIN " . TABLE_PREFIX . "staffgroup AS staffgroup ON(staff.staffgroupid = staffgroup.staffgroupid)
            ORDER BY staff.fullname ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record['sstaffid']] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('staffcache', $_cache);

        return true;
    }

    /**
     * Validate the IP Address of Staff
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ValidateIP()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

        }

        if (trim($this->GetProperty('iprestriction')) == '') {
            return true;
        }

        if (stristr($this->GetProperty('iprestriction'), ',')) {
            $_networkContainer = explode(',', $this->GetProperty('iprestriction'));
            if (_is_array($_networkContainer)) {
                foreach ($_networkContainer as $_networkString) {
                    if (NetMatch(trim($_networkString), SWIFT::Get('IP'))) {
                        return true;
                    }
                }
            }
        } else {
            if (NetMatch($this->GetProperty('iprestriction'), SWIFT::Get('IP'))) {
                return true;
            }
        }

        // Unable to match, check if the interface is winapp and match X-Forwarded-For and verify it's a QuickSupport IP
        $_forwardedIPIsAllowed = false;
        if ($this->Interface->GetInterface() == SWIFT_Interface::INTERFACE_WINAPP && defined('ENABLECHATGATEWAYBYPASS') && ENABLECHATGATEWAYBYPASS === true && isset($_SERVER['HTTP_X_ORIGINATED_FROM']) && !empty($_SERVER['HTTP_X_ORIGINATED_FROM'])) {
            if (stristr($this->GetProperty('iprestriction'), ',')) {
                $_networkContainer = explode(',', $this->GetProperty('iprestriction'));
                if (_is_array($_networkContainer)) {
                    foreach ($_networkContainer as $_networkString) {
                        if (NetMatch(trim($_networkString), $_SERVER['HTTP_X_ORIGINATED_FROM'])) {
                            $_forwardedIPIsAllowed = true;

                            break;
                        }
                    }
                }
            } else if (NetMatch($this->GetProperty('iprestriction'), $_SERVER['HTTP_X_ORIGINATED_FROM'])) {
                $_forwardedIPIsAllowed = true;
            }

            $_gatewayIP = $_SERVER['REMOTE_ADDR'];

            if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !stristr($_SERVER['HTTP_X_FORWARDED_FOR'], ',')) {
                $_gatewayIP = GetClientIPFromXForwardedFor($_SERVER['HTTP_X_FORWARDED_FOR']);
            }

            if ($_forwardedIPIsAllowed === true && IsQuickSupportIP($_gatewayIP)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve the Department IDs assigned to this staff
     *
     * @author Varun Shoor
     * @param mixed $_appName (OPTIONAL) The Filter App
     * @return array|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAssignedDepartments($_appName = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Staff_Exception(SWIFT_CLASSNOTLOADED);
        }

        return self::GetAssignedDepartmentsOnStaffID($this->GetStaffID(), $_appName);
    }

    /**
     * Load the Asigned Department on Staff ID
     *
     * @author Varun Shoor
     * @param int $_staffID The Staff ID to load the list from
     * @param mixed $_appName (OPTIONAL) The Filter App
     * @return array|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetAssignedDepartmentsOnStaffID($_staffID, $_appName = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_staffID)) {
            throw new SWIFT_Staff_Exception(SWIFT_INVALIDDATA);
        }

        if (empty($_appName)) {
            $_appName = APP_TICKETS;
        } else if ($_appName == -1) {
            $_appName = false;
        }

        $idx = 'assigncache_' . $_staffID;
        if (isset(self::$_assignCacheContainer[$idx])) {
            return self::$_assignCacheContainer[$idx];
        }

        $_staffCache = $_SWIFT->Cache->Get('staffcache');

        if (!isset($_staffCache[$_staffID])) {
            return false;
        }

        $_staffGroupID = (int)($_staffCache[$_staffID]['staffgroupid']);
        $_groupAssigns = (int)($_staffCache[$_staffID]['groupassigns']);

        if (empty($_staffGroupID)) {
            return array();
        }

        $_departmentIDList = array();

        $_groupAssignCache = $_SWIFT->Cache->Get('groupassigncache');
        $_staffAssignCache = $_SWIFT->Cache->Get('staffassigncache');

        if ($_groupAssigns == 1 && $_groupAssignCache && isset($_groupAssignCache[$_staffGroupID]) && _is_array($_groupAssignCache[$_staffGroupID])) {
            foreach ($_groupAssignCache[$_staffGroupID] as $_key => $_val) {
                if (!in_array($_val, $_departmentIDList)) {
                    $_departmentIDList[] = (int)($_val);
                }
            }
        } else {
            if (!$_staffAssignCache || !isset($_staffAssignCache[$_staffID]) || !_is_array($_staffAssignCache[$_staffID])) {
                return array();
            }

            foreach ($_staffAssignCache[$_staffID] as $_key => $_val) {
                if (!in_array($_val, $_departmentIDList)) {
                    $_departmentIDList[] = (int)($_val);
                }
            }
        }

        // End right here if we didnt receive any data
        if (!_is_array($_departmentIDList)) {
            return array();
        }

        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_finalDepartmentIDList = array();
        foreach ($_departmentIDList as $_key => $_val) {
            if ($_departmentCache && isset($_departmentCache[$_val]) && (!$_appName || $_departmentCache[$_val]['departmentapp'] == $_appName)) {
                $_finalDepartmentIDList[] = $_val;
            }
        }

        self::$_assignCacheContainer['assigncache' . $_staffID] = $_finalDepartmentIDList;

        return $_finalDepartmentIDList;
    }

    /**
     * Retrieve the Staff Details based on Username
     *
     * @author Varun Shoor
     * @param string $_staffUserName The Staff User Name
     * @return mixed "_staffContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveOnUsername($_staffUserName)
    {
        if (empty($_staffUserName)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_staffContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staff WHERE username = '" . $_SWIFT->Database->Escape($_staffUserName) . "'");

        return $_staffContainer;
    }

    /**
     * Retrieve the Staff Details based on Email
     *
     * @author Varun Shoor
     * @param string $_staffEmail The Staff Email
     * @return mixed "_staffID" (INT) on Success, "false" otherwise
     */
    public static function RetrieveOnEmail($_staffEmail)
    {
        if (empty($_staffEmail)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_staffContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staff WHERE email = '" . $_SWIFT->Database->Escape($_staffEmail) . "'");

        return $_staffContainer;

    }

    /**
     * Retrieve a list of Staff as Array
     *
     * @author Varun Shoor
     * @param array $_staffIDList The Staff ID List
     * @return mixed "_staffContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveList($_staffIDList)
    {
        if (!_is_array($_staffIDList)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_staffContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff WHERE staffid IN (" . BuildIN($_staffIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffContainer[$_SWIFT->Database->Record['staffid']] = $_SWIFT->Database->Record;
        }

        if (!count($_staffContainer)) {
            return false;
        }

        return $_staffContainer;
    }

    /**
     * Retrieve a list of Staff as Array based on List of Staff Group ID's
     *
     * @author Varun Shoor
     * @param array $_staffGroupIDList The Staff Group ID List
     * @return mixed "_staffContainer" (ARRAY) on Success, "false" otherwise
     */
    public static function RetrieveListOnStaffGroup($_staffGroupIDList)
    {
        if (!_is_array($_staffGroupIDList)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_staffContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff WHERE staffgroupid IN (" . BuildIN($_staffGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffContainer[$_SWIFT->Database->Record['staffid']] = $_SWIFT->Database->Record;
        }

        if (!count($_staffContainer)) {
            return false;
        }

        return $_staffContainer;
    }

    /**
     * Delete Staff on Staff Group ID List
     *
     * @author Varun Shoor
     * @param array $_staffGroupIDList The Staff Group ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnStaffGroup($_staffGroupIDList)
    {
        if (!_is_array($_staffGroupIDList)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_staffIDList = array();

        $_SWIFT->Database->Query("SELECT staffid FROM " . TABLE_PREFIX . "staff WHERE staffgroupid IN (" . BuildIN($_staffGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_staffIDList[] = $_SWIFT->Database->Record['staffid'];
        }

        if (!count($_staffIDList)) {
            return false;
        }

        self::DeleteList($_staffIDList);

        return true;
    }

    /**
     * Update the Status Message for Staff
     *
     * @author Varun Shoor
     * @param string $_statusMessage The Staff Status Message
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateStatusMessage($_statusMessage)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('statusmessage', mb_substr($_statusMessage, 0, 255));
        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Update the Profile Timeline for Staff
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateProfileTimeline()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('lastprofileupdate', DATENOW);
        $this->ProcessUpdatePool();

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve the email addresses of all staff users assigned to the provided staff group
     *
     * @author Varun Shoor
     * @param int $_staffGroupID The Staff Group ID
     * @return array The Email Address List
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveEmailOnStaffGroupID($_staffGroupID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_staffGroupID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_emailList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff WHERE staffgroupid = '" . $_staffGroupID . "'");
        while ($_SWIFT->Database->NextRecord()) {
            if (!in_array($_SWIFT->Database->Record['email'], $_emailList) && $_SWIFT->Database->Record['isenabled'] == '1') {
                $_emailList[] = $_SWIFT->Database->Record['email'];
            }
        }

        return $_emailList;
    }

    /**
     * Check to see whether the given email address is that of a staff
     *
     * @author Varun Shoor
     * @param string $_emailAddress The Email Address
     * @return mixed "Staff ID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsStaffEmail($_emailAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_emailAddress)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_staffContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "staff WHERE email = '" . $_SWIFT->Database->Escape($_emailAddress) . "'");
        if (isset($_staffContainer['staffid']) && !empty($_staffContainer['staffid'])) {
            return $_staffContainer['staffid'];
        }

        return false;
    }

    /**
     * Get the total count of active staff
     *
     * @author Parminder Singh
     * @return int Total Staff Count
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function ActiveStaffCount()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_activeStaffCountContainer = $_SWIFT->Database->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "staff WHERE isenabled = '1'");
        if (isset($_activeStaffCountContainer['totalitems'])) {
            return $_activeStaffCountContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the Staff Group Based on StaffID
     *
     * @author Mansi Wason <mansi.wason@kayako.com>
     *
     * @param int $_staffID
     *
     * @return array|bool $_staffGroupID
     */
    public static function RetrieveStaffGroupOnStaffID($_staffID)
    {
        if (empty($_staffID)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Database->QueryLimit("SELECT staffgroupid FROM " . TABLE_PREFIX . "staff WHERE staffid = '" . $_staffID . "'");

        $_staffGroupID = [];
        while ($_SWIFT->Database->NextRecord()) {
            $_staffGroupID = $_SWIFT->Database->Record['staffgroupid'];
        }
        return $_staffGroupID;

    }
}

?>
