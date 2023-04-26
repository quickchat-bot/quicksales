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

namespace News\Models\Subscriber;

use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_LanguageEngine;
use SWIFT_Mail;
use SWIFT_Model;
use SWIFT_TemplateEngine;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use SWIFT_XML;

/**
 * The News Subscriber Management Class
 *
 * @property SWIFT_Mail $Mail
 * @author Varun Shoor
 */
class SWIFT_NewsSubscriber extends SWIFT_Model
{
    const TABLE_NAME        =    'newssubscribers';
    const PRIMARY_KEY        =    'newssubscriberid';

    const TABLE_STRUCTURE    =    "newssubscriberid I PRIMARY AUTO NOTNULL,
                                tgroupid I DEFAULT '0' NOTNULL,
                                userid I DEFAULT '0' NOTNULL,
                                email C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                isvalidated I2 DEFAULT '0' NOTNULL,
                                usergroupid I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'tgroupid, isvalidated';
    const INDEX_2            =    'email';
    const INDEXTYPE_2        =    'UNIQUE';
    const INDEX_3            =    'usergroupid, isvalidated';
    const INDEX_4            =    'isvalidated';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_newsSubscriberID The News Subscriber ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_newsSubscriberID)
    {
        parent::__construct();

        if (!$this->LoadData($_newsSubscriberID)) {
            throw new SWIFT_Subscriber_Exception('Failed to load News Subscriber ID: ' . ($_newsSubscriberID));
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
     * @throws SWIFT_Subscriber_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'newssubscribers', $this->GetUpdatePool(), 'UPDATE', "newssubscriberid = '" . ($this->GetNewsSubscriberID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the News Subscriber ID
     *
     * @author Varun Shoor
     * @return mixed "newssubscriberid" on Success, "false" otherwise
     * @throws SWIFT_Subscriber_Exception If the Class is not Loaded
     */
    public function GetNewsSubscriberID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Subscriber_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['newssubscriberid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_newsSubscriberID The News Subscriber ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_newsSubscriberID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "newssubscribers WHERE newssubscriberid = '" . ($_newsSubscriberID) . "'");
        if (isset($_dataStore['newssubscriberid']) && !empty($_dataStore['newssubscriberid']))
        {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Subscriber_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Subscriber_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Subscriber_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Subscriber_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Subscriber_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new News Subscriber
     *
     * @author Varun Shoor
     * @param string $_emailAddress The Subscriber Email Address
     * @param bool $_isValidated Whether this address has been validated
     * @param int $_userID (OPTIONAL) The Logged in User ID
     * @param int $_templateGroupID (OPTIONAL) The Template Group ID
     * @param bool $_sendEmails (OPTIONAL) Send the Welcome/Validation Emails
     * @param int $_userGroupID (OPTIONAL) The User Group ID
     * @return mixed "_newsSubscriberID" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Subscriber_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_emailAddress, $_isValidated, $_userID = 0, $_templateGroupID = 0, $_sendEmails = true, $_userGroupID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_emailAddress = trim($_emailAddress);

        if (empty($_emailAddress))
        {
            throw new SWIFT_Subscriber_Exception(SWIFT_INVALIDDATA);
        }

        /**
         * BUG FIX - Anjali Sharma
         *
         * SWIFT-1159 News subscriber interface is incomplete.
         *
         * It will insert the "userid" and "usergroupid" per matched User
         **/
        $_SWIFT_UserSubscriberObject = SWIFT_User::RetrieveOnEmailList(array(mb_strtolower($_emailAddress)));
        if ($_SWIFT_UserSubscriberObject instanceof SWIFT_User && $_SWIFT_UserSubscriberObject->GetIsClassLoaded()) {
            $_userID      = $_SWIFT_UserSubscriberObject->GetID();
            $_userGroupID = $_SWIFT_UserSubscriberObject->Get('usergroupid');
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'newssubscribers', array('email' => mb_strtolower($_emailAddress), 'isvalidated' => ($_isValidated),
            'userid' => ($_userID), 'tgroupid' => ($_templateGroupID), 'dateline' => DATENOW, 'usergroupid' => ($_userGroupID)), 'INSERT');
        $_newsSubscriberID = $_SWIFT->Database->Insert_ID();

        if (!$_newsSubscriberID)
        {
            throw new SWIFT_Subscriber_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_NewsSubscriberObject = static::GetInstance($_newsSubscriberID);
        if (!$_SWIFT_NewsSubscriberObject instanceof SWIFT_NewsSubscriber || !$_SWIFT_NewsSubscriberObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
            // @codeCoverageIgnoreEnd
        }

        if ($_sendEmails)
        {
            if ($_isValidated == false)
            {
                $_hash = SWIFT_NewsSubscriberHash::Create($_newsSubscriberID);

                $_SWIFT_NewsSubscriberObject->DispatchValidationEmail($_hash);
            } else {
                $_SWIFT_NewsSubscriberObject->DispatchWelcomeEmail();
            }
        }

        return $_newsSubscriberID;
    }

    /**
     * Update the News Subscriber Record
     *
     * @author Varun Shoor
     * @param string $_emailAddress The Subscriber Email Address
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Subscriber_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_emailAddress)
    {
        $_emailAddress = trim($_emailAddress);

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Subscriber_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_emailAddress)) {
            throw new SWIFT_Subscriber_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('email', mb_strtolower($_emailAddress));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the News Subscriber record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Subscriber_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Subscriber_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetNewsSubscriberID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of News Subscribers
     *
     * @author Varun Shoor
     * @param array $_newsSubscriberIDList The News Subscriber ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_newsSubscriberIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_newsSubscriberIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "newssubscribers WHERE newssubscriberid IN (" . BuildIN($_newsSubscriberIDList) . ")");

        return true;
    }

    /**
     * Check to see if the email address is subscribed
     *
     * @author Varun Shoor
     * @param string $_emailAddress The Email Address
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsSubscribed($_emailAddress)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_emailAddress = trim($_emailAddress);

        $_subscriberContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "newssubscribers WHERE email = '" . $_SWIFT->Database->Escape($_emailAddress) . "'");
        if (isset($_subscriberContainer['newssubscriberid']))
        {
            return true;
        }

        return false;
    }

    /**
     * Dispatch the validation email for the subscriber
     *
     * @author Varun Shoor
     * @param string $_hash The Validation Hash
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchValidationEmail($_hash)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('Mail:Mail');

        // Load the phrases from the database..
        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->Queue('news', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        $this->Template->Assign('_hash', urlencode($_hash));

        $_textEmailContents = $this->Template->Get('email_subscribervalidate_text', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_subscribervalidate_html', SWIFT_TemplateEngine::TYPE_DB);

        $this->Mail->SetFromField($this->Settings->Get('general_returnemail'), SWIFT::Get('companyname'));

        $this->Mail->SetToField($this->GetProperty('email'));

        $this->Mail->SetSubjectField($this->Language->Get('nwvalidatesubscriptionsub'));

        $this->Mail->SetDataText($_textEmailContents);
        $this->Mail->SetDataHTML($_htmlEmailContents);

        $this->Mail->SendMail();

        return true;
    }

    /**
     * Dispatch the welcome email for the subscriber
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchWelcomeEmail()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Load->Library('Mail:Mail');

        // Load the phrases from the database..
        $this->Language->Queue('default', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->Queue('news', SWIFT_LanguageEngine::TYPE_DB);
        $this->Language->LoadQueue(SWIFT_LanguageEngine::TYPE_DB);

        $this->Template->Assign('_subscriberEmail', urlencode($this->GetProperty('email')));
        $this->Template->Assign('_subscriberID', $this->GetNewsSubscriberID());
        $this->Template->Assign('_subscriberHash', substr(md5(SWIFT::Get('installationhash') . $this->GetProperty('email')), 0, 20));

        $_textEmailContents = $this->Template->Get('email_subscriberconfirm_text', SWIFT_TemplateEngine::TYPE_DB);
        $_htmlEmailContents = $this->Template->Get('email_subscriberconfirm_html', SWIFT_TemplateEngine::TYPE_DB);

        $this->Mail->SetFromField($this->Settings->Get('general_returnemail'), SWIFT::Get('companyname'));

        $this->Mail->SetToField($this->GetProperty('email'));

        $this->Mail->SetSubjectField($this->Language->Get('nwsubconfirmsub'));

        $this->Mail->SetDataText($_textEmailContents);
        $this->Mail->SetDataHTML($_htmlEmailContents);

        $this->Mail->SendMail();

        return true;
    }

    /**
     * Mark as Validated
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkAsValidated()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('isvalidated', '1');
        $this->ProcessUpdatePool();

        $this->DispatchWelcomeEmail();

        return true;
    }

    /**
     * Generate the Subscriber Filename for Export
     *
     * @author Varun Shoor
     * @return string The Language Filename
     */
    static private function GenerateFileName()
    {
        return strtolower(SWIFT_PRODUCT) . '.' . str_replace('.', '-', SWIFT_VERSION) . '.' . 'subscribers.xml';
    }

    /**
     * Export the Subscribers
     *
     * @author Varun Shoor
     * @param int $_templateGroupID The Template Group ID to filter by
     * @param string $_customFileName (OPTIONAL) The Custom Filename to Export As
     * @return bool "true" on Success, "false" otherwise
     */
    public static function Export($_templateGroupID, $_customFileName = '')
    {
        $_SWIFT = SWIFT::GetInstance();

        $_fileName = $_customFileName;
        if (empty($_customFileName))
        {
            $_fileName = self::GenerateFileName();
        }

        $_finalTemplateGroupID = 0;
        $_templateGroupCache = (array) $_SWIFT->Cache->Get('templategroupcache');
        if (isset($_templateGroupCache[$_templateGroupID]))
        {
            $_finalTemplateGroupID = $_templateGroupID;
        }

        $_SWIFT_XMLObject = new SWIFT_XML();

        $_SWIFT_XMLObject->AddComment(sprintf($_SWIFT->Language->Get('generationdate'), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, DATENOW)));
        $_SWIFT_XMLObject->AddParentTag('subscribers');
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newssubscribers " .
                    IIF(!empty($_finalTemplateGroupID), "WHERE tgroupid = '" . ($_finalTemplateGroupID) . "'") .
                    " ORDER BY email ASC");
            while ($_SWIFT->Database->NextRecord())
            {
                $_SWIFT_XMLObject->AddTag('email', $_SWIFT->Database->Record['email']);
            }

        $_SWIFT_XMLObject->EndTag('subscribers');

        $_xmlData = $_SWIFT_XMLObject->ReturnXML();

        if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
            // IE Bug in download name workaround
            @ini_set( 'zlib.output_compression','Off' );
        }

        @header("Content-Type: application/force-download");

        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')){
            @header("Content-Disposition: attachment; filename=\"". $_fileName ."\"");
        } else{
            @header("Content-Disposition: attachment; filename=\"". $_fileName ."\"");
        }

        @header("Content-Transfer-Encoding: binary\n");
        @header("Content-Length: ". strlen($_xmlData) ."\n");
        echo $_xmlData;

        return true;
    }

    /**
     * Import the Emails
     *
     * @author Varun Shoor
     * @param string $_emailString The Email String
     * @return int The Import Count
     */
    public static function Import($_emailString)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_emailList = $_finalEmailList = array();

        $_importCount = 0;

        if (strpos($_emailString, ','))
        {
            $_emailList = explode(',' , $_emailString);
        } else {
            $_emailList = array($_emailString);
        }

        foreach ($_emailList as $_emailAddress)
        {
            $_finalEmail = trim(mb_strtolower($_emailAddress));

            if (IsEmailValid($_finalEmail))
            {
                $_finalEmailList[] = $_finalEmail;
            }
        }

        if (!count($_finalEmailList))
        {
            return 0;
        }

        $_ignoredEmailList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "newssubscribers WHERE email IN (" . BuildIN($_finalEmailList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalEmail = trim(mb_strtolower($_SWIFT->Database->Record['email']));
            $_ignoredEmailList[] = $_finalEmail;
        }

        foreach ($_finalEmailList as $_emailAddress)
        {
            if (in_array($_emailAddress, $_ignoredEmailList))
            {
                continue;
            }

            SWIFT_NewsSubscriber::Create($_emailAddress, true, 0, 0, false);

            $_importCount++;
        }

        return $_importCount;
    }

    /**
     * @author Bishwanath Jha
     *
     * @param string $_email
     *
     * @return bool
     */
    public static function DeleteOnEmail($_email)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_email)) {
            return false;
        }

        // Delete Subscriber
        $_subscriber = $_SWIFT->Database->QueryFetch("SELECT newssubscriberid FROM " . TABLE_PREFIX . self::TABLE_NAME . " WHERE email = ?", 3, [$_email]);
        if (!empty($_subscriber)) {
            self::DeleteList(array($_subscriber['newssubscriberid']));

            // Delete subscriberhash if it exist
            $_subscriberHash = $_SWIFT->Database->QueryFetch("SELECT newssubscriberhashid FROM " . TABLE_PREFIX . "newssubscriberhash WHERE newssubscriberid = ?", 3, [$_subscriber['newssubscriberid']]);
            if (!empty($_subscriberHash)) {
                SWIFT_NewsSubscriberHash::DeleteList(array($_subscriberHash['newssubscriberhashid']));
            }
        }
        return true;
    }

    /**
     * @author Bishwanath Jha
     *
     * @param int $_userID
     *
     * @return bool
     */
    public static function DeleteOnUserID($_userID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_userID)) {
            return false;
        }

        $_subscriber = $_SWIFT->Database->QueryFetch("SELECT newssubscriberid FROM " . TABLE_PREFIX . self::TABLE_NAME . " WHERE userid = " . ($_userID));
        if (!empty($_subscriber)) {
            self::DeleteList(array($_subscriber['newssubscriberid']));

            // Delete subscriberhash if it exist
            $_subscriberHash = $_SWIFT->Database->QueryFetch("SELECT newssubscriberhashid FROM " . TABLE_PREFIX . "newssubscriberhash WHERE newssubscriberid = '" . $_subscriber['newssubscriberid'] . "'");
            if (!empty($_subscriberHash)) {
                SWIFT_NewsSubscriberHash::DeleteList(array($_subscriberHash['newssubscriberhashid']));
            }
        }

        return true;
    }

    /**
     * @author Bishwanath Jha
     *
     * @param int $_userID
     *
     * @return bool
     */
    public static function IsSubscribedOnUserID($_userID)
    {
        $_SWIFT  = SWIFT::GetInstance();
        $_userID = ($_userID);

        if (empty($_userID)) {
            return false;
        }

        $_subscriber = $_SWIFT->Database->QueryFetch("SELECT userid as userid FROM " . TABLE_PREFIX . self::TABLE_NAME . " WHERE userid = " . $_userID);
        if (isset($_subscriber['userid']) && (0 < $_subscriber['userid'])) {
            return true;
        }

        return false;
    }

    /**
     * @author Bishwanath Jha
     *
     * @param int   $_userID
     * @param array $_emailList
     *
     * @return bool
     */
    public static function Subscribe($_userID, $_emailList)
    {
        $_SWIFT  = SWIFT::GetInstance();

        if (empty($_userID) || empty($_emailList)) {
            return false;
        }

        // Check based on userid and update accordingly.
        if(self::IsSubscribedOnUserID($_userID)) {
            $_userPrimaryEmail = SWIFT_UserEmail::GetPrimaryEmail($_userID);
            $_subscribedEmail  = $_SWIFT->Database->QueryFetch("SELECT email FROM " . TABLE_PREFIX . self::TABLE_NAME . " WHERE userid = " . ($_userID));

            if (!in_array($_userPrimaryEmail, $_subscribedEmail, true)) {
                $_SWIFT->Database->AutoExecute(TABLE_PREFIX . self::TABLE_NAME, array('email' => $_userPrimaryEmail), 'UPDATE', "userid = " . ($_userID));
            }

            return true;
        }

        // Check based on registered user emails and update accordingly.
        foreach ($_emailList as $_email) {
            if (static::IsSubscribed($_email)) {
                $_SWIFT->Database->AutoExecute(TABLE_PREFIX . self::TABLE_NAME,
                    array(
                         'userid'      => ($_userID),
                         'tgroupid'    => $_SWIFT->TemplateGroup->GetTemplateGroupID(),
                         'usergroupid' => SWIFT::Get('usergroupid')
                    ),
                    'UPDATE', "email = '" . $_email . "'");

                return true;
            }
        }

        $isValidated = true;

        // check if news subcriber email verification is enabled
        if($_SWIFT->Settings->Get('nw_svalidate') == '1'){
            $isValidated = false;
        }

        // Otherwise finally, create a new one.
        static::Create(SWIFT_UserEmail::GetPrimaryEmail($_userID), $isValidated, $_userID, $_SWIFT->TemplateGroup->GetTemplateGroupID(), true, SWIFT::Get('usergroupid'));
        return true;

    }

    /**
     * @author Bishwanath Jha
     *
     * @param int   $_userID
     * @param array $_emailList
     *
     * @return bool
     */
    public static function UnSubscribe($_userID, $_emailList)
    {
        // Check based on userid.
        if (self::IsSubscribedOnUserID($_userID)) {
            SWIFT_NewsSubscriber::DeleteOnUserID($_userID);
            return true;
        }

        // Check based on registered user emails.
        foreach ($_emailList as $_email) {
            if (SWIFT_NewsSubscriber::IsSubscribed($_email)) {
                SWIFT_NewsSubscriber::DeleteOnEmail($_email);
                return true;
            }
        }

        return false;
    }

    /**
     * @author Saloni Dhall
     *
     * @param int         $_userID
     * @param bool|string $_emailAddress The Email Address
     *
     * @return SWIFT_NewsSubscriber|bool "SWIFT_NewsSubscriberObject" (OBJECT) on Success, "false" otherwise
     */
    public static function RetreiveSubscriberOnUser($_userID, $_emailAddress = false)
    {
        $_SWIFT = SWIFT::GetInstance();
        $_sqlExtend = '';

        if (static::IsSubscribedOnUserID($_userID)) {
            $_sqlExtend = "userid = " . ($_userID);
        } else if (static::IsSubscribed((string)$_emailAddress)) {
            $_sqlExtend = "email = '" . trim((string)$_emailAddress) . "'";
        }

        $_newsSubscriberContainer = $_SWIFT->Database->QueryFetch("SELECT userid, newssubscriberid FROM " . TABLE_PREFIX . self::TABLE_NAME . " WHERE " . $_sqlExtend);
        if (isset($_newsSubscriberContainer['newssubscriberid'])) {
            $_SWIFT_NewsSubscriberObject = false;
            try {
                $_SWIFT_NewsSubscriberObject = static::GetInstance($_newsSubscriberContainer['newssubscriberid']);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                //Do nothing
            }
            return $_SWIFT_NewsSubscriberObject;
        }

        return false;
    }

    /**
     * @param int $_newsSubscriberID
     * @return SWIFT_NewsSubscriber
     * @throws SWIFT_Subscriber_Exception
     */
    public static function GetInstance($_newsSubscriberID) {
        return new static($_newsSubscriberID);
    }
}
