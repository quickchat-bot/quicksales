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

namespace Base\Models\User;

use SWIFT;
use SWIFT_Data;
use SWIFT_Exception;
use SWIFT_Model;
use SWIFT_Session;

/**
 * The User Consent Management Class
 *
 * @author Arotimi Busayo
 */
class SWIFT_UserConsent extends SWIFT_Model
{
    const TABLE_NAME = 'userconsents';
    const PRIMARY_KEY = 'userconsentid';

    const TABLE_STRUCTURE = "userconsentid I PRIMARY AUTO NOTNULL,
                                type I2 DEFAULT '0' NOTNULL,
                                createdat I DEFAULT '0' NOTNULL,
                                useragent C(100) DEFAULT '' NOTNULL,
                                ipaddress C(20) DEFAULT '' NOTNULL,
                                lastupdatedat I DEFAULT '0' NOTNULL,
                                channel I2 DEFAULT '0' NOTNULL,
                                source I2 DEFAULT '0' NOTNULL,
                                currenturl C(255) DEFAULT '' NOTNULL,
                                userid I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'userconsentid';
    const INDEX_2 = 'userid, type';

    const CONSENT_COOKIE = 1;
    const CONSENT_REGISTRATION = 2;

    const SOURCE_NEW_REGISTRATION = 1;
    const SOURCE_SUBMIT_TICKET = 2;
    const SOURCE_POP_UP = 3;
    const SOURCE_EMAIL_SUBSCRIPTION = 4;
    const SOURCE_LIVE_CHAT = 5;

    const CHANNEL_WEB = 1;
    const CHANNEL_EMAIL = 2;
    const CHANNEL_MESSENGER = 3;
    const CHANNEL_MOBILE = 4;

    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Arotimi Busayo
     * @param int $_userConsentID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_userConsentID)
    {
        parent::__construct();

        if (!$this->LoadData($_userConsentID)) {
            throw new SWIFT_Exception('Unable to Load User Consent: ' .$_userConsentID);
        }
    }

    /**
     * Destructor
     *
     * @author Arotimi Busayo
     * @return bool "true" on Success, "false" otherwise
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Arotimi Busayo
     * @return bool "true" on Success
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'userconsents', $this->GetUpdatePool(), 'UPDATE', "userconsentid = '" . (int)($this->GetUserConsentID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the User Consent ID
     *
     * @author Arotimi Busayo
     * @return mixed "userconsentid" on Success
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserConsentID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['userconsentid'];
    }

    /**
     * Load the Data
     *
     * @author Arotimi Busayo
     * @param int $_userConsentID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_userConsentID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userconsents WHERE userconsentid = '" .$_userConsentID . "'");
        if (isset($_dataStore['userconsentid']) && !empty($_dataStore['userconsentid'])) {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Arotimi Busayo
     * @return mixed "_dataStore" Array on Success
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Is Valid Channel
     *
     * @author Arotimi Busayo
     * @param int $_channel
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidChannel($_channel)
    {
        if ($_channel == self::CHANNEL_EMAIL ||
            $_channel == self::CHANNEL_MESSENGER ||
            $_channel == self::CHANNEL_MOBILE ||
            $_channel == self::CHANNEL_WEB) {
            return true;
        }

        return false;
    }

    /**
     * Is Valid Source
     *
     * @author Arotimi Busayo
     * @param int $_source
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidSource($_source)
    {
        if ($_source == self::SOURCE_EMAIL_SUBSCRIPTION ||
            $_source == self::SOURCE_NEW_REGISTRATION ||
            $_source == self::SOURCE_POP_UP ||
            $_source == self::SOURCE_LIVE_CHAT ||
            $_source == self::SOURCE_SUBMIT_TICKET) {
            return true;
        }

        return false;
    }

    /**
     * is Valid Consent
     *
     * @author Arotimi Busayo
     * @param int $_type The consent Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidConsent($_type)
    {
        if ($_type == self::CONSENT_COOKIE || $_type == self::CONSENT_REGISTRATION) {
            return true;
        }

        return false;
    }

    /**
     * Creates A New User Consent
     *
     * @author Arotimi Busayo
     *
     * @param int $_userId
     * @param int $_consentType
     * @param int $_channel
     * @param int $_source
     * @param string $_currentUrl
     * @return bool|mixed "userConsentID" on Success
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_userId, $_consentType, $_channel, $_source, $_currentUrl)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ((!self::IsValidChannel($_channel) || empty($_channel)) ||
            (!self::IsValidSource($_source) || empty($_source)) ||
            (!self::IsValidConsent($_consentType) || empty($_consentType))) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'userconsents',
            array(
                'type' => $_consentType,
                'source' => $_source,
                'channel' => $_channel,
                'createdat' => DATENOW,
                'lastupdatedat' => DATENOW,
                'currenturl' => $_currentUrl,
                'userid' => $_userId,
                'useragent' => SWIFT_Session::GetUserAgent(),
                'ipaddress' => SWIFT::Get('IP')
            ), 'INSERT');

        $_userConsentID = $_SWIFT->Database->Insert_ID();
        if (!$_userConsentID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_userConsentID;
    }

    /**
     * Update A User Consent Record
     *
     * @author Arotimi Busayo
     *
     * @param int $_channel
     * @param int $_source
     * @param string $_currentUrl
     * @return bool
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public function Update($_channel, $_source, $_currentUrl)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ((!self::IsValidChannel($_channel) || empty($_channel)) ||
            (!self::IsValidSource($_source) || empty($_source))) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('lastupdatedat', DATENOW);
        $this->UpdatePool('source', $_source);
        $this->UpdatePool('channel', $_channel);
        $this->UpdatePool('useragent', SWIFT_Session::GetUserAgent());
        $this->UpdatePool('ipaddress', SWIFT::Get('IP'));
        $this->UpdatePool('currenturl', $_currentUrl);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the User Setting record
     *
     * @author Arotimi Buusayo
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetUserConsentID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of User Settings
     *
     * @author Arotimi Busayo
     * @param array $_userConsentIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_userConsentIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userConsentIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "userconsents WHERE userconsentid IN (" . BuildIN($_userConsentIDList) . ")");

        return true;
    }

    /**
     * Delete the User Settings on the User Record
     *
     * @author Arotimi Busayo
     * @param array $_userIDList The User ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnUser($_userIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_userIDList)) {
            return false;
        }

        $_userConsentIDList = array();
        $_SWIFT->Database->Query("SELECT userconsentid FROM " . TABLE_PREFIX . "userconsents WHERE userid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_userConsentIDList[] = (int)($_SWIFT->Database->Record['userconsentid']);
        }

        if (!count($_userConsentIDList)) {
            return false;
        }

        self::DeleteList($_userConsentIDList);

        return true;
    }

    /**
     * Retrieve A user's consent.
     *
     * @author Arotimi Busayo
     *
     * @param int $_userId
     * @param int $_contentType
     * @return array
     */
    public static function RetrieveConsent($_userId, $_contentType)
    {
        $_SWIFT = SWIFT::GetInstance();
        return $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "userconsents WHERE userid = '" .$_userId . "' AND type = '" . $_contentType . "'");
    }
}

?>
