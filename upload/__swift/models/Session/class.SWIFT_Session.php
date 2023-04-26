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

use Base\Library\Staff\SWIFT_Staff_Exception;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;

/**
 * The Core Session Management Class
 *
 * @property SWIFT_User $User
 * @property SWIFT_Staff $Staff
 * @author Varun Shoor
 */
class SWIFT_Session extends SWIFT_Model
{
    const TABLE_NAME        =    'sessions';
    const PRIMARY_KEY        =    'sessionid';

    const TABLE_STRUCTURE    =    "sessionid C(255) PRIMARY DEFAULT '' NOTNULL,
                                ipaddress C(50) DEFAULT '0.0.0.0' NOTNULL,
                                lastactivity I DEFAULT '0' NOTNULL,
                                lastactivitycustom I DEFAULT '0' NOTNULL,
                                useragent C(60) DEFAULT '' NOTNULL,
                                isloggedin I2 DEFAULT '0' NOTNULL,
                                sessiontype I DEFAULT '0' NOTNULL,
                                typeid I DEFAULT '0' NOTNULL,
                                sessionhits I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                status I2 DEFAULT '0' NOTNULL,
                                phonestatus I2 DEFAULT '0' NOTNULL,
                                captcha C(20) DEFAULT '' NOTNULL,
                                gridcolor C(30) DEFAULT '' NOTNULL,
                                visitorgroupid I2 DEFAULT '0' NOTNULL,
                                departmentid I2 DEFAULT '0' NOTNULL,
                                proactiveresult I2 DEFAULT '0' NOTNULL,
                                ticketviewid I2 DEFAULT '0' NOTNULL,
                                iswinapp I2 DEFAULT '0' NOTNULL,
                                csrfhash C(50) DEFAULT '' NOTNULL,
                                languagecode C(10) DEFAULT '' NOTNULL";

    const INDEX_1            =    'sessiontype, lastactivity, status';
    const INDEX_2            =    'typeid, sessiontype';
    const INDEX_3            =    'sessionid, sessiontype';

    protected $_dataStore = array();

    // Core Constants
    const STATUS_ONLINE = 1;
    const STATUS_OFFLINE = 2;
    const STATUS_AWAY = 3;
    const STATUS_BACK = 4;
    const STATUS_BUSY = 5;
    const STATUS_AUTOAWAY = 6;
    const STATUS_INVISIBLE = 7;

    const PHONESTATUS_AVAILABLE = 1;
    const PHONESTATUS_PRIVATE = 2;
    const PHONESTATUS_DND = 3;
    const PHONESTATUS_OFFLINE = 4;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function __construct($_sessionData)
    {
        parent::__construct();

        if (!$this->LoadData($_sessionData)) {
            $this->SetIsClassLoaded(false);

            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'sessions', $this->GetUpdatePool(), 'UPDATE', "sessionid = '" . $this->Database->Escape($this->GetSessionID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param mixed $_sessionData The Session ID or Session Data Container
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_sessionData)
    {
        if (_is_array($_sessionData) && isset($_sessionData['sessionid']) && !empty($_sessionData['sessionid'])) {
            $_dataStore = $_sessionData;
        } else if (is_string($_sessionData)) {
            $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE sessionid = '" . $this->Database->Escape($_sessionData) . "'");
        }

        if (isset($_dataStore['sessionid']) && !empty($_dataStore['sessionid'])) {
            $this->_dataStore = $_dataStore;

            SWIFT::Set('sessionid', $this->_dataStore['sessionid']);
            SWIFT::SetReference('session', $this->_dataStore);

            $this->UpdateActivityCombined();

            return true;
        }

        return false;
    }

    /**
     * Update the Session Activity Combined..
     *
     * @author Varun Shoor
     * @param bool $_updateLastActivity (OPTIONAL) Whether to update the last activity timelines
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateActivityCombined($_updateLastActivity = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_sessionID = $this->GetProperty('sessionid');

        $_sessionIDList = array();
        $_sessionIDList[] = $_sessionID;

        // Is this a winapp session and do we have a staff sessionid with it too? If thats the case then we need to update the staff session id heart beat too!
        if ($this->GetProperty('sessiontype') == SWIFT_Interface::INTERFACE_WINAPP && !empty($_POST["staffsessionid"])) {
            $_sessionIDList[] = $_POST['staffsessionid'];
        }

        // The visitor heartbeat is managed separated in the update html code framework...
        if ($this->GetProperty('sessiontype') != SWIFT_Interface::INTERFACE_VISITOR) {
            // Update this session last activity
            $_sessionStatus = 0;
            $_phoneStatus = 0;

            if (isset($_POST['status']) && !empty($_POST['status']) && ($this->GetProperty('sessiontype') == SWIFT_Interface::INTERFACE_WINAPP)) {
                $_sessionStatus = ($_POST['status']);
            } else if ($this->GetProperty('sessiontype') == SWIFT_Interface::INTERFACE_WINAPP) {
                $_sessionStatus = -1;
            }

            if (isset($_POST['voipstatus']) && !empty($_POST['voipstatus']) && ($this->GetProperty('sessiontype') == SWIFT_Interface::INTERFACE_WINAPP)) {
                $_phoneStatus = ($_POST['voipstatus']);
            } else if ($this->GetProperty('sessiontype') == SWIFT_Interface::INTERFACE_WINAPP) {
                $_phoneStatus = -1;
            }

            if (strtolower($this->Router->GetController()) == 'fetchvisitors' && $_updateLastActivity == true) {
                $this->_dataStore['lastactivitycustom'] = time();
                $this->_dataStore['lastactivity'] = time();
            }

            self::UpdateHeartbeatAndStatus($_sessionIDList, $_sessionStatus, $_phoneStatus);
        }

        return true;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Retrieve the Session ID
     *
     * @author Varun Shoor
     * @return mixed "_sessionID" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not LOaded
     */
    public function GetSessionID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (isset($this->_dataStore['sessionid'])) {
            return $this->_dataStore['sessionid'];
        }

        return false;
    }

    /**
     * Update the Heartbeat and Hits on the Type ID
     *
     * @author Varun Shoor
     * @param array $_typeIDList The Type ID List
     * @param int $_sessionType The Session Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateHeartbeatAndHitsOnTypeID($_typeIDList, $_sessionType)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!_is_array($_typeIDList) || !SWIFT_Interface::IsValidInterfaceType($_sessionType)) {
            return false;
        }

        $_sessionIDList = $_sessionHitsPointer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE typeid IN (" . BuildIN($_typeIDList) . ") and sessiontype = '" . ($_sessionType) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_sessionIDList[] = $_SWIFT->Database->Record['sessionid'];
            $_sessionHitsPointer[$_SWIFT->Database->Record['sessionid']] = ($_SWIFT->Database->Record['sessionhits']);
        }

        if (!count($_sessionIDList)) {
            return false;
        }

        foreach ($_sessionIDList as $_key => $_val) {
            if (!isset($_sessionHitsPointer[$_val])) {
                continue;
            }

            $_sessionHits = ($_sessionHitsPointer[$_val]) + 1;

            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'sessions', array('sessionhits' => $_sessionHits, 'lastactivity' => time()), 'UPDATE', "sessionid = '" . $_SWIFT->Database->Escape($_val) . "'");
        }

        return true;
    }

    /**
     * Update the Heartbeat and Hits on the Session ID
     *
     * @author Varun Shoor
     * @param array $_sessionIDList The Session ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateHeartbeatAndHitsOnSessionID($_sessionIDList)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!_is_array($_sessionIDList)) {
            return false;
        }

        $_sessionHitsPointer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE sessionid IN (" . BuildIN($_sessionIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_sessionHitsPointer[$_SWIFT->Database->Record['sessionid']] = ($_SWIFT->Database->Record['sessionhits']);
        }

        foreach ($_sessionIDList as $_key => $_val) {
            if (!isset($_sessionHitsPointer[$_val])) {
                continue;
            }

            $_sessionHits = ($_sessionHitsPointer[$_val]) + 1;

            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'sessions', array('sessionhits' => $_sessionHits, 'lastactivity' => time()), 'UPDATE', "sessionid = '" . $_SWIFT->Database->Escape($_val) . "'");
        }

        return true;
    }

    /**
     * Update the Heartbeat on the Type ID
     *
     * @author Varun Shoor
     * @param array $_typeIDList The Type ID List
     * @param int $_sessionType The Session Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateHeartbeatOnTypeID($_typeIDList, $_sessionType)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!_is_array($_typeIDList) || !SWIFT_Interface::IsValidInterfaceType($_sessionType)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'sessions', array('lastactivity' => time()), 'UPDATE', "typeid IN (" . BuildIN($_typeIDList) . ") AND sessiontype = '" . ($_sessionType) . "'");

        return true;
    }

    /**
     * Update the last activity timeline for a list of session ids
     *
     * @author Varun Shoor
     * @param array $_sessionIDList The Session ID List
     * @param int $_sessionStatus The Session Status
     * @param int $_phoneStatus The Phone Status
     * @return bool "true" on Success, "false" otherwise
     */
    public static function UpdateHeartbeatAndStatus(
        $_sessionIDList,
        $_sessionStatus = 0,
        $_phoneStatus = 0
    ) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_sessionIDList)) {
            return false;
        }

        $_fieldsContainer = array();
        if ($_sessionStatus != -1) {
            $_fieldsContainer['status'] = ($_sessionStatus);
        }

        if ($_phoneStatus != -1) {
            $_fieldsContainer['phonestatus'] = ($_phoneStatus);
        }

        if (strtolower($_SWIFT->Router->GetController()) == 'fetchvisitors') {
            $_fieldsContainer['lastactivitycustom'] = time();
        }

        $_fieldsContainer['lastactivity'] = time();

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'sessions', $_fieldsContainer, 'UPDATE', "sessionid IN (" . BuildIN($_sessionIDList) . ")");

        return true;
    }

    /**
     * Insert a new session
     *
     * @author Varun Shoor
     * @param int $_interfaceType The Interface Type
     * @param mixed $_typeID The Unique Type ID (INT/STRING)
     * @param string $_customSessionID The Custom Session ID (If Any)
     * @return bool|string "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Insert($_interfaceType, $_typeID, $_customSessionID = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_sessionFields = array();

        if (!SWIFT_Interface::IsValidInterfaceType($_interfaceType)) {
            return false;
        }

        /**
         * IMPROVEMENT- Mansi Wason <mansi.wason@opencart.com.vn>
         *
         * SWIFT-4979 Improve random function of SWIFT
         */
        if ($_customSessionID != '') {
            $_sessionID = $_customSessionID;
        } else {
            $_sessionID = GenerateID();
        }

        $_isLoggedIn = 0;

        /*
         * BUG FIX -Simaranjit Singh
         *
         * SWIFT-3630 Staff CP should support only one concurrent session
         *
         * Comments: None
         */
        if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_WINAPP && $_interfaceType == SWIFT_Interface::INTERFACE_STAFF) {
            if (!empty($_typeID)) {
                // Checking whether (staff) secure sessions setting is activated
                // so as to clear existing sessions.
                if (
                    $_SWIFT->Settings->Get('security_securesessions')
                    && SWIFT_Interface::INTERFACE_STAFF == $_interfaceType
                ) {
                    $_sessionIDList = array();
                    $_SWIFT->Database->Query("SELECT sessionid FROM " . TABLE_PREFIX . "sessions
                                              WHERE sessiontype = '" . ($_interfaceType) . "'
                                                AND typeid = '" . ($_typeID) . "'
                                                AND iswinapp = 1");
                    while ($_SWIFT->Database->NextRecord()) {
                        $_sessionIDList[] = $_SWIFT->Database->Record['sessionid'];
                    }

                    self::DeleteList($_sessionIDList);
                }

                $_isLoggedIn = '1';
            } else {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
        } else if (
            $_interfaceType == SWIFT_Interface::INTERFACE_WINAPP || $_interfaceType == SWIFT_Interface::INTERFACE_ADMIN || $_interfaceType == SWIFT_Interface::INTERFACE_API
            || $_interfaceType == SWIFT_Interface::INTERFACE_PDA || $_interfaceType == SWIFT_Interface::INTERFACE_SYNCWORKS || $_interfaceType == SWIFT_Interface::INTERFACE_MOBILE
            || $_interfaceType == SWIFT_Interface::INTERFACE_INTRANET || $_interfaceType == SWIFT_Interface::INTERFACE_STAFFAPI || $_interfaceType == SWIFT_Interface::INTERFACE_STAFF
            || $_interfaceType == SWIFT_Interface::INTERFACE_CLIENT
        ) {
            if (!empty($_typeID)) {
                // Checking whether (staff) secure sessions setting is activated
                // so as to clear existing sessions.
                if (
                    $_SWIFT->Settings->Get('security_securesessions')
                    && SWIFT_Interface::INTERFACE_STAFF == $_interfaceType
                ) {
                    $_sessionIDList = array();
                    $_SWIFT->Database->Query("SELECT sessionid FROM " . TABLE_PREFIX . "sessions
                                              WHERE sessiontype = '" . ($_interfaceType) . "'
                                                AND typeid = '" . ($_typeID) . "'");
                    while ($_SWIFT->Database->NextRecord()) {
                        $_sessionIDList[] = $_SWIFT->Database->Record['sessionid'];
                    }
                    self::DeleteList($_sessionIDList);
                }

                $_isLoggedIn = '1';
            } else if ($_interfaceType != SWIFT_Interface::INTERFACE_CLIENT && $_interfaceType != SWIFT_Interface::INTERFACE_STAFF) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
        } else if ($_interfaceType == SWIFT_Interface::INTERFACE_INSTAALERT) {
            if (!empty($_typeID)) {
                // Clear all sessions which have been inactive.. we do allow multiple logins for staff interface
                $_clearTimeline = DATENOW - $_SWIFT->Settings->Get('security_sessioninactivity');

                $_sessionIDList = array();
                $_SWIFT->Database->Query("SELECT sessionid FROM " . TABLE_PREFIX . "sessions
                                          WHERE sessiontype = '" . ($_interfaceType) . "'
                                            AND lastactivity < '" . ($_clearTimeline) . "'");
                while ($_SWIFT->Database->NextRecord()) {
                    $_sessionIDList[] = $_SWIFT->Database->Record['sessionid'];
                }

                self::DeleteList($_sessionIDList);

                $_isLoggedIn = '1';
            } else {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
        } else if (
            $_interfaceType == SWIFT_Interface::INTERFACE_VISITOR || $_interfaceType == SWIFT_Interface::INTERFACE_RSS || $_interfaceType == SWIFT_Interface::INTERFACE_CHAT
        ) {
            // Do Nothing
        } else {
            return false;
        }

        // Status Declaration
        $_sessionFields['status']      = '0';
        $_sessionFields['phonestatus'] = '0';
        $_sessionFields['iswinapp']    = '0';
        if (
            $_interfaceType == SWIFT_Interface::INTERFACE_WINAPP || $_interfaceType == SWIFT_Interface::INTERFACE_INSTAALERT || $_interfaceType == SWIFT_Interface::INTERFACE_MOBILE
            || $_interfaceType == SWIFT_Interface::INTERFACE_SYNCWORKS || $_interfaceType == SWIFT_Interface::INTERFACE_STAFFAPI
        ) {
            $_sessionFields['status']      = self::STATUS_ONLINE;
            $_sessionFields['phonestatus'] = self::PHONESTATUS_AVAILABLE;
            $_sessionFields['iswinapp']    = '1';
        } else if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_WINAPP) {
            $_sessionFields['iswinapp'] = '1';
        }

        $_sessionFields['sessionid']    = Clean($_sessionID);
        $_sessionFields['ipaddress']    = ReturnNone(SWIFT::Get('IP'));
        $_sessionFields['lastactivity'] = DATENOW;
        $_sessionFields['dateline']     = DATENOW;
        $_sessionFields['useragent']    = ReturnNone(self::GetUserAgent());
        $_sessionFields['isloggedin']   = ($_isLoggedIn);
        $_sessionFields['sessiontype']  = ($_interfaceType);
        $_sessionFields['typeid']       = $_SWIFT->Database->Escape($_typeID);
        $_sessionFields['csrfhash']     = BuildHash();

        // Admin Locale Detection
        if (isset($_POST['languagecode'])) {
            $_languageCode = Clean($_POST['languagecode']);
            if (!empty($_languageCode) && is_dir('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LOCALEDIRECTORY . $_languageCode)) {
                $_sessionFields['languagecode'] = $_languageCode;
            }
        }

        $_SWIFT->Database->Replace(TABLE_PREFIX . 'sessions', $_sessionFields, array('sessionid'));

        echo $_SWIFT->Database->FetchLastError();

        $_SWIFT->Cookie->Set('sessionid' . $_interfaceType, $_sessionID);

        return $_sessionID;
    }

    /**
     * Inserts a new session and then starts it
     *
     * @author Varun Shoor
     * @param mixed $_typeID The Unique Type ID (INT/STRING)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or if Session creation fails
     */
    public static function InsertAndStart($_typeID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_interfaceType = $_SWIFT->Interface->GetInterface();
        if (empty($_interfaceType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_sessionID = self::Insert($_interfaceType, $_typeID);
        if (!$_sessionID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return self::Start($_SWIFT->Interface, $_sessionID);
    }

    /**
     * Start a session on the Interface
     *
     * @author Varun Shoor
     * @param SWIFT_Interface $_SWIFT_InterfaceObject The SWIFT Interface Object Pointer
     * @param string|bool $_customSessionID The Custom Session ID (If Any)
     * @return bool|string "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Interface Class is not Loaded or If Invalid Data is Provided
     */
    public static function Start(SWIFT_Interface $_SWIFT_InterfaceObject, $_customSessionID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_InterfaceObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_interfaceType = $_SWIFT_InterfaceObject->GetInterface();
        if (empty($_interfaceType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_cookieSessionID = Clean($_SWIFT->Cookie->Get('sessionid' . $_interfaceType));

        if (empty($_customSessionID)) {
            // Priority Order POST > GET > COOKIE > I AM DEAD
            if (!empty($_POST['sessionid'])) {
                $_sessionID = Clean($_POST['sessionid']);
            } else if (!empty($_GET['sessionid'])) {
                $_sessionID = Clean($_GET['sessionid']);
            } else if (!empty($_cookieSessionID)) {
                $_sessionID = $_cookieSessionID;
            } else {
                // We return false and dont set a session, in this case a login form is displayed
                // [Session Expired Debug]: Unable to find session id in POST, GET or COOKIE variable.. Clearing Out...

                return false;
            }
        } else {
            $_sessionID = $_customSessionID;
        }

        // Seems like the cookie is empty, attempt to set it..
        if (!empty($_REQUEST['sessionid']) && empty($_cookieSessionID)) {
            $_SWIFT->Cookie->Set('sessionid' . $_interfaceType, $_sessionID);
        }

        // We should have a sessionid by now, try to fetch it
        $_SWIFT_SessionObject = self::CheckAndGet($_sessionID, $_SWIFT_InterfaceObject);

        if (!($_SWIFT_SessionObject instanceof SWIFT_Session && $_SWIFT_SessionObject->GetIsClassLoaded())) {
            $_SWIFT->Cookie->Delete('sessionid' . $_interfaceType);

            SWIFT::Set('errorstring', $_SWIFT->Language->Get('invalid_sessionid'));

            //            echo "[Session Expired Debug]: Unable to fetch a valid session for session id: ".$_sessionID;

            return false;
        }

        // Is this a client or visitor session?
        if ($_interfaceType == SWIFT_Interface::INTERFACE_CLIENT || $_interfaceType == SWIFT_Interface::INTERFACE_VISITOR) {
            // Yes! Sir!.. ok so we need to see whether this user is registered or not? This is where we load the default permissions too!

            $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');
            if ($_SWIFT_SessionObject->GetProperty('typeid') && isset($_templateGroupCache['regusergroupid'])) {
                // He is a registered user, so set the group as registered
                //$_SWIF['usergroupid'] = $_templateGroupCache['regusergroupid'];
            } else if (isset($_templateGroupCache['guestusergroupid'])) {
                // He is a guest, so set the default usergroupid as the guest user group of the template group... sounds complex? yeah yeah it is..
                //$_SWIF['usergroupid'] = $_templateGroupCache['guestusergroupid'];
            }

            // Is there a user id set? we will need to load the user then..
            $_sessionUserID = ($_SWIFT_SessionObject->GetProperty('typeid'));
            if (!empty($_sessionUserID)) {
                try {
                    $_SWIFT_SessionObject->Load->Model('User:User', [new SWIFT_DataID($_SWIFT_SessionObject->GetProperty('typeid'))], true, false, APP_BASE);
                } catch (SWIFT_Staff_Exception $_SWIFT_ExceptionObject) {
                    $_SWIFT_SessionObject->Update(0);
                }

                if (!$_SWIFT_SessionObject->User instanceof SWIFT_User || !$_SWIFT_SessionObject->User->GetIsClassLoaded()) {
                    // Well something failed, user couldnt be loaded.. this is no good, remove the session immediately
                    $_SWIFT_SessionObject->Update(0);
                    $_SWIFT_SessionObject->End();

                    //echo "[Session Expired Debug]: Unable to load any staff user for type id: ".$this->typeid;
                    return false;
                }

                $_SWIFT_SessionObject->User->LoadIntoSWIFTNamespace();

                if ((DATENOW - $_SWIFT_SessionObject->User->GetProperty('lastactivity')) > 180) {
                    $_SWIFT_SessionObject->User->UpdateLastActivity();
                }

                // If its been more than 30 minutes since last update to lastvisit then reset it
                if ((DATENOW - $_SWIFT_SessionObject->User->GetProperty('lastvisit2')) > 1800) {
                    $_SWIFT_SessionObject->User->UpdateLastVisit();
                }
            }
        }

        if ($_SWIFT->Settings->Get('security_sessioninactivity') != '0' && ((DATENOW - $_SWIFT_SessionObject->GetProperty('lastactivity')) > $_SWIFT->Settings->Get('security_sessioninactivity')) && $_SWIFT_SessionObject->GetProperty('lastactivity') != 0) {
            // Session expired.
            $_SWIFT_SessionObject->End();
            SWIFT::Set('errorstring', $_SWIFT->Language->Get('invalid_sessionid'));

            //echo "[Session Expired Debug]: Session expired due to inactivity, Date difference between current system date (".date("d M Y h:i:s A", DATENOW) .") and last activity (". date("d M Y h:i:s A", $this->_S["lastactivity"]) .") is ". (DATENOW-$this->_S["lastactivity"]) ." seconds whereas inactivity timing setting under Admin CP > Settings is ". $settings->store["security_sessioninactivity"] ." seconds.";

            return false;
        }

        $_SWIFT->Template->Assign('_session', $_SWIFT_SessionObject->GetDataStore());

        // We end this session starting right here if the end user is not under admin, staff, winapp, syncml or wap
        if (
            $_interfaceType != SWIFT_Interface::INTERFACE_ADMIN && $_interfaceType != SWIFT_Interface::INTERFACE_STAFF && $_interfaceType != SWIFT_Interface::INTERFACE_INTRANET
            && $_interfaceType != SWIFT_Interface::INTERFACE_WINAPP && $_interfaceType != SWIFT_Interface::INTERFACE_API && $_interfaceType != SWIFT_Interface::INTERFACE_PDA
            && $_interfaceType != SWIFT_Interface::INTERFACE_SYNCWORKS && $_interfaceType != SWIFT_Interface::INTERFACE_INSTAALERT && $_interfaceType != SWIFT_Interface::INTERFACE_MOBILE
            && $_interfaceType != SWIFT_Interface::INTERFACE_STAFFAPI
        ) {
            $_SWIFT->Session = $_SWIFT_SessionObject;

            return $_sessionID;
        }

        // Session should be loaded by now, so all went well, staff is genuine, load up his data
        $_sessionStaffID = (int) ($_SWIFT_SessionObject->GetProperty('typeid'));
        if (!empty($_sessionStaffID)) {
            try {
                $_SWIFT_SessionObject->Load->Model(
                    'Staff:Staff',
                    [new SWIFT_DataID($_sessionStaffID)],
                    true,
                    false,
                    'base'
                );
            } catch (SWIFT_Staff_Exception $_SWIFT_ExceptionObject) {
                $_SWIFT_SessionObject = false;
                $_sessionID = false;
            }

            if (!$_SWIFT_SessionObject->Staff instanceof SWIFT_Staff || !$_SWIFT_SessionObject->Staff->GetIsClassLoaded()) {
                // Well something failed, staff couldnt be loaded.. this is no good, remove the session immediately
                $_SWIFT_SessionObject->End();

                //echo "[Session Expired Debug]: Unable to load any staff user for type id: ".$this->typeid;
                return false;
            }

            // Ok, by now we should have staff data loaded. Now we need to confirm whether the staff is admin, if not its some pesky old staff member trying to act as admin
            if (($_interfaceType == SWIFT_Interface::INTERFACE_ADMIN) && $_SWIFT_SessionObject->Staff->IsAdmin() != '1') {
                // ok, its him!
                $_SWIFT_SessionObject->End();

                SWIFT::Set('errorstring', $_SWIFT->Language->Get('staff_not_admin'));

                //echo "[Session Expired Debug]: User isnt admin and is trying to login to Admin CP.. bailing out..";

                return false;
            }

            $_SWIFT_SessionObject->Staff->LoadIntoSWIFTNamespace();

            if ((DATENOW - $_SWIFT_SessionObject->Staff->GetProperty('lastactivity')) > 180) {
                $_SWIFT_SessionObject->Staff->UpdateLastActivity();
            }

            // If its been more than 30 minutes since last update to lastvisit then reset it
            if ((DATENOW - $_SWIFT_SessionObject->Staff->GetProperty('lastvisit2')) > 1800) {
                $_SWIFT_SessionObject->Staff->UpdateLastVisit();
            }
        }

        $_SWIFT->Session = $_SWIFT_SessionObject;

        return $_sessionID;
    }

    /**
     * Check the session user agent, ip and other parameters and then dispatch the session object
     *
     * @author Varun Shoor
     * @param string $_sessionID The Session ID
     * @param SWIFT_Interface $_SWIFT_InterfaceObject The SWIFT_Interface object pointer
     * @return SWIFT_Session|null "SWIFT_Session" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    static private function CheckAndGet($_sessionID, SWIFT_Interface $_SWIFT_InterfaceObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_InterfaceObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_interfaceType = $_SWIFT_InterfaceObject->GetInterface();
        if (empty($_interfaceType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_sessionContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE sessionid = '" . $_SWIFT->Database->Escape(Clean($_sessionID)) . "'");

        $_dontProceed = $_userAgentFailed = false;

        // Always restrict to the user agent
        if (trim($_sessionContainer["useragent"]) != trim(self::GetUserAgent())) {
            $_dontProceed = false;
        } else {
            $_userAgentFailed = true;
            $_dontProceed = false;
        }

        if ($_sessionContainer["sessiontype"] == $_interfaceType) // && !$_dontProceed)
        {
            /*
             * IMPROVEMENT - Bishwanath Jha
             *
             * SWIFT-3718  - session_ipcheck shall be a setting at Admin CP, Additionaly check if request has come from the same subnet.
             *
             */
            // Check for the IP Address
            if (($_SWIFT->Settings->Get('security_sessionipcheck')) == 1 && !(NetMatch(trim($_sessionContainer['ipaddress']) . '/16', SWIFT::Get('IP')))) {
                return null;

                // We cannot have empty typeid for any of these interfaces
            } else if (($_interfaceType == SWIFT_Interface::INTERFACE_INTRANET || $_interfaceType == SWIFT_Interface::INTERFACE_WINAPP
                    || $_interfaceType == SWIFT_Interface::INTERFACE_ADMIN || $_interfaceType == SWIFT_Interface::INTERFACE_PDA || $_interfaceType == SWIFT_Interface::INTERFACE_MOBILE
                    || $_interfaceType == SWIFT_Interface::INTERFACE_SYNCWORKS || $_interfaceType == SWIFT_Interface::INTERFACE_INSTAALERT || $_interfaceType == SWIFT_Interface::INTERFACE_API
                    || $_interfaceType == SWIFT_Interface::INTERFACE_STAFFAPI)

                && empty($_sessionContainer["typeid"])
            ) {

                return null;
            }

            // Load up session, everything is OK
            $_SWIFT_SessionObject = new SWIFT_Session($_sessionContainer);
            if (!$_SWIFT_SessionObject instanceof SWIFT_Session || !$_SWIFT_SessionObject->GetIsClassLoaded()) {
                return null;
            }

            return $_SWIFT_SessionObject;
        } else {
            if ($_userAgentFailed) {
                // [Session Expired Debug]: User Agent Doesnt Match!! Current: ".trim($this->useragent).", DB Stored Value: ".$_S["useragent"];
            }

            // [Session Expired Debug]: Session type doesnt match.. Current: ".$sessiontype.", DB Stored Value: ".$_S["sessiontype"];
        }

        return null;
    }

    /**
     * Ends the current session
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function End()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteList(array($this->GetSessionID()));

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-2215 "Login as user" makes staff logout.
         *
         */
        $this->Cookie->Delete('sessionid' . $this->GetProperty('sessiontype'));

        SWIFT::Set('sessionid');
        SWIFT::Set('session');

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * End a Custom Session
     *
     * @author Varun Shoor
     * @param string $_customSessionID The Custom Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function EndCustomSession($_customSessionID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_customSessionID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        self::DeleteList(array($_customSessionID));

        return true;
    }

    /**
     * Attempts to log the session out from the interface
     *
     * @author Varun Shoor
     * @param SWIFT_Interface $_SWIFT_InterfaceObject The SWIFT Interface Object Pointer
     * @param bool $_ignoreActiveSession (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Logout(SWIFT_Interface $_SWIFT_InterfaceObject, $_ignoreActiveSession = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_InterfaceObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_interfaceType = $_SWIFT_InterfaceObject->GetInterface();
        if (empty($_interfaceType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_cookieSessionID = Clean($_SWIFT->Cookie->Get('sessionid' . $_interfaceType));

        // Priority Chart POST > GET > Cookie > I GIVE UP!
        $_sessionID = false;
        if (!empty($_POST["sessionid"])) {
            $_sessionID = Clean($_POST["sessionid"]);
        } else if (!empty($_GET["sessionid"])) {
            $_sessionID = Clean($_GET["sessionid"]);
        } else if (!empty($_cookieSessionID)) {
            $_sessionID = $_cookieSessionID;
        }

        // We first check for a session object in global name space
        if (!$_ignoreActiveSession && isset($_SWIFT->Session) && $_SWIFT->Session instanceof SWIFT_Session && $_SWIFT->Session->GetIsClassLoaded()) {
            $_SWIFT->Session->End();

            // Seems like its the same session as in the strings above.. end right here..
            return false;
        }

        if (!empty($_sessionID)) {
            // We have a session id by now.. and its different.. load it forcefully and end it!
            $_SWIFT_SessionObject = false;
            try {
                $_SWIFT_SessionObject = new SWIFT_Session($_sessionID);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                return true;
            }

            if ($_SWIFT_SessionObject instanceof SWIFT_Session && $_SWIFT_SessionObject->GetIsClassLoaded()) {
                $_SWIFT_SessionObject->End();
            }

            return true;
        }

        return false;
    }

    /**
     * Update the Session Details
     *
     * @author Varun Shoor
     * @param int $_typeID The Type ID for the Session
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Update($_typeID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (!empty($_typeID)) {
            SWIFT_Session::DeleteOnTypeID(array($_typeID), $this->Interface->GetInterface());
        }

        $this->UpdatePool('typeid', $_typeID);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the Session Status
     *
     * @author Varun Shoor
     * @param int $_sessionStatus The Session Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateStatus($_sessionStatus)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UpdatePool('status', $_sessionStatus);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the Phone Status
     *
     * @author Varun Shoor
     * @param int $_phoneStatus The Phone Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdatePhoneStatus($_phoneStatus)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UpdatePool('phonestatus', $_phoneStatus);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Update the Last Activity
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateLastActivity()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UpdatePool('lastactivity', time());
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Checks the CSRF Hash for Validity
     *
     * @author Varun Shoor
     * @param string $_csrfHash The Cross Site Request Forgery Hash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CheckCSRFHash($_csrfHash)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT->Session instanceof SWIFT_Session || !$_SWIFT->Session->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        if (trim($_csrfHash) == '' || $_SWIFT->Session->GetProperty('csrfhash') != $_csrfHash) {
            return false;
        }

        return true;
    }

    /**
     * Flushes the inactive sessions
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function FlushInactive()
    {
        $_SWIFT = SWIFT::GetInstance();

        // Clear all sessions which have been inactive..
        $_clearTimeline = time() - $_SWIFT->Settings->Get('security_sessioninactivity');

        $_sessionIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE lastactivity < '" . ($_clearTimeline) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_sessionIDList[] = $_SWIFT->Database->Record['sessionid'];
        }

        self::DeleteList($_sessionIDList);

        return true;
    }

    /**
     * Retrieve the User Agent string
     *
     * @author Varun Shoor
     * @return string The User Agent String
     */
    public static function GetUserAgent()
    {
        $_userAgent = '';

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $_userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        return trim(mb_substr($_userAgent, 0, 60));
    }

    /**
     * Store the Captcha word for this session
     *
     * @author Varun Shoor
     * @param string $_captchaWord The Captcha Word
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetCaptcha($_captchaWord)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UpdatePool('captcha', $_captchaWord);
        $this->_dataStore['captcha'] = $_captchaWord;

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete a list of sessions
     *
     * @author Varun Shoor
     * @param array $_sessionIDList The Session ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_sessionIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_sessionIDList)) {
            return false;
        }

        // First retrieve the sessions...
        $_sessionContainer = $_updateLoginLogList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE sessionid IN (" . BuildIN($_sessionIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_sessionContainer[$_SWIFT->Database->Record['sessionid']] = $_SWIFT->Database->Record;

            // For all the staff or user related interfaces we attempt to update the login log
            if (
                $_SWIFT->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_STAFF || $_SWIFT->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_INTRANET
                || $_SWIFT->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_WINAPP || $_SWIFT->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_ADMIN
                || $_SWIFT->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_PDA || $_SWIFT->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_INSTAALERT
                || $_SWIFT->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_SYNCWORKS || $_SWIFT->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_MOBILE
                || $_SWIFT->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_STAFFAPI || $_SWIFT->Database->Record['sessiontype'] == SWIFT_Interface::INTERFACE_CLIENT
            ) {
                $_updateLoginLogList[] = $_SWIFT->Database->Record['sessionid'];
            }
        }

        foreach ($_updateLoginLogList as $_key => $_val) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'staffloginlog', array('logoutdateline' => DATENOW), 'UPDATE', "sessionid = '" . $_SWIFT->Database->Escape($_val) . "'");
            // We also need to update the logoutdateline in userlogin logs.
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'userloginlog', array('logoutdateline' => DATENOW), 'UPDATE', "sessionid = '" . $_SWIFT->Database->Escape($_val) . "'");
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "sessions WHERE sessionid IN (" . BuildIN($_sessionIDList) . ")");

        return true;
    }

    /**
     * Kill a List of Sessions
     *
     * @author Varun Shoor
     * @param array $_sessionIDList The Session ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function KillSessionList($_sessionIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_sessionIDList)) {
            return false;
        }

        self::DeleteList($_sessionIDList);

        return true;
    }

    /**
     * Kill Sessions based on Session Types
     *
     * @author Varun Shoor
     * @param array $_sessionTypeList The Session Type List
     * @param array $_typeIDList The Type ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function KillSessionListOnType($_sessionTypeList, $_typeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_sessionTypeList) || !_is_array($_typeIDList)) {
            return false;
        }

        $_sessionIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "sessions WHERE sessiontype IN (" . BuildIN($_sessionTypeList) . ") AND typeid IN (" . BuildIN($_typeIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_sessionIDList[] = $_SWIFT->Database->Record['sessionid'];
        }

        self::DeleteList($_sessionIDList);

        return true;
    }

    /**
     * Check to see if the user/staff is currently logged in..
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsLoggedIn()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_interfaceType = $this->GetProperty('sessiontype');
        $_typeID = ($this->GetProperty('typeid'));

        // Is this a client or visitor session?
        if ($_interfaceType == SWIFT_Interface::INTERFACE_CLIENT || $_interfaceType == SWIFT_Interface::INTERFACE_VISITOR) {
            // This session has a typeid set so this person is logged in
            if (!empty($_typeID)) {
                return true;

                // No type id means hes a guest
            } else {
                return false;
            }
        }

        // If this is a staff session we also verify the Staff class in SWIFT namespace..
        if (
            $_interfaceType == SWIFT_Interface::INTERFACE_ADMIN || $_interfaceType == SWIFT_Interface::INTERFACE_STAFF || $_interfaceType == SWIFT_Interface::INTERFACE_INTRANET
            || $_interfaceType == SWIFT_Interface::INTERFACE_WINAPP || $_interfaceType == SWIFT_Interface::INTERFACE_API || $_interfaceType == SWIFT_Interface::INTERFACE_PDA
            || $_interfaceType == SWIFT_Interface::INTERFACE_SYNCWORKS || $_interfaceType == SWIFT_Interface::INTERFACE_INSTAALERT || $_interfaceType == SWIFT_Interface::INTERFACE_MOBILE
            || $_interfaceType == SWIFT_Interface::INTERFACE_STAFFAPI
        ) {
            if (!empty($_typeID) && $_SWIFT->Staff instanceof SWIFT_Staff && $_SWIFT->Staff->GetIsClassLoaded()) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Update the current ticket view id
     *
     * @author Varun Shoor
     * @param int $_ticketViewID The Ticket View ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateTicketView($_ticketViewID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->UpdatePool('ticketviewid', ($_ticketViewID));
        $this->ProcessUpdatePool();



        return true;
    }

    /**
     * Reset Hits on the Session ID
     *
     * @author Parminder Singh
     * @param int $_sessionID The Session ID
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ResetHitsOnSessionID($_sessionID)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (empty($_sessionID)) {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'sessions', array('sessionhits' => '0'), 'UPDATE', "sessionid = '" . $_SWIFT->Database->Escape($_sessionID) . "'");

        return true;
    }

    /**
     * Reset type ID according to interface type
     *
     * @author Simaranjit Singh
     *
     * @param array $_typeIDList
     * @param int   $_sessionType
     *
     * @return bool
     */
    public static function DeleteOnTypeID($_typeIDList, $_sessionType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_typeIDList) || empty($_sessionType) || !SWIFT_Interface::IsValidInterfaceType($_sessionType)) {
            return false;
        }

        $_sessionIDList = array();
        $_SWIFT->Database->Query("SELECT sessionid FROM " . TABLE_PREFIX . "sessions
                                  WHERE sessiontype = " . ($_sessionType) . "
                                    AND typeid IN (" . BuildIN($_typeIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_sessionIDList[] = $_SWIFT->Database->Record['sessionid'];
        }

        return self::DeleteList($_sessionIDList);
    }

    /**
     * Retrieve session
     *
     * @author   Abhishek Mittal
     *
     * @param string $_sessionID
     * @param SWIFT_Interface $_Interface
     *
     * @return SWIFT_Session|null
     * @throws SWIFT_Exception
     */
    public static function RetrieveSession($_sessionID, SWIFT_Interface $_Interface)
    {
        if (empty($_sessionID)) {
            return null;
        }

        return self::CheckAndGet($_sessionID, $_Interface);
    }
}
