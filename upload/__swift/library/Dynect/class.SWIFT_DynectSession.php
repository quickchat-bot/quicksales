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
 * @copyright      Copyright (c) 2001-2013, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Dynect Session Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_DynectSession extends SWIFT_DynectBase
{
    protected $_sessionToken = false;

    const BASE_URL = 'Session';

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param string $_sessionToken The Session Token
     *
     * @throws SWIFT_Exception If Object Creation Fails
     */
    public function __construct($_sessionToken)
    {
        parent::__construct(true);

        $this->SetToken($_sessionToken);

        $this->SetSession($this);
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        $this->End();

        parent::__destruct();
    }

    /**
     * Set the Session Token
     *
     * @author Varun Shoor
     *
     * @param string $_sessionToken The Session Token
     *
     * @return SWIFT_DynectSession
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetToken($_sessionToken)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_sessionToken)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_sessionToken = $_sessionToken;

        return $this;
    }

    /**
     * Retrieve the currently set session token
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetToken()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_sessionToken;
    }

    /**
     * Create a new session
     *
     * @author Varun Shoor
     *
     * @param string $_customerName
     * @param string $_userName
     * @param string $_password
     *
     * @return SWIFT_DynectSession
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Create($_customerName, $_userName, $_password)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Check for existing session
        if ($_SWIFT->DynectSession instanceof SWIFT_DynectSession && $_SWIFT->DynectSession->GetIsClassLoaded()) {
            return $_SWIFT->DynectSession;
        }

        if (empty($_customerName) || empty($_userName) || empty($_password)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ResponseObject = self::GetBase(true)->Post(self::BASE_URL, array('customer_name' => $_customerName, 'user_name' => $_userName, 'password' => $_password));
        if (!self::GetBase(true)->CheckResponse($_ResponseObject, 'SWIFT_DynectSession::Create', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_JSONObject = $_ResponseObject->GetBodyJSONObject();
        if (!isset($_JSONObject->data->token)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_sessionToken = (string) $_JSONObject->data->token;
        if (empty($_sessionToken)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_DynectSessionObject = new SWIFT_DynectSession($_sessionToken);

        $_SWIFT->SetClass('DynectSession', $_SWIFT_DynectSessionObject);

        return $_SWIFT_DynectSessionObject;
    }

    /**
     * Send a NOOP to keep the session alive
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Ping()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ResponseObject = $this->Put(self::BASE_URL);
        if (!$this->CheckResponse($_ResponseObject, 'SWIFT_DynectSession::Ping', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return true;
    }

    /**
     * Check to see if the session is alive
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsAlive()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ResponseObject = $this->Get(self::BASE_URL);
        if (!$this->CheckResponse($_ResponseObject, 'SWIFT_DynectSession::IsAlive', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_JSONObject = $_ResponseObject->GetBodyJSONObject();
        if (isset($_JSONObject->status) && (string) $_JSONObject->status == self::STATUS_SUCCESS) {
            return true;
        }

        return false;
    }

    /**
     * End the Session
     *
     * @author Varun Shoor
     * @return SWIFT_DynectSession
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function End()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ResponseObject = $this->Delete(self::BASE_URL);
        if (!$this->CheckResponse($_ResponseObject, 'SWIFT_DynectSession::End', 200)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ResponseObject->GetBodyJSONObject();

        return $this;
    }

    /**
     * Loads the Session Data into $_SWIFT Variable
     *
     * @author Varun Shoor
     * @return SWIFT_DynectSession
     * @throws SWIFT_Exception If Class is not Loaded or If Data Provided is Invalid
     */
    public function LoadIntoSWIFTNamespace()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if ($_SWIFT->DynectSession && $_SWIFT->DynectSession instanceof SWIFT_DynectSession && $_SWIFT->DynectSession->GetIsClassLoaded()) {
            return $this;
        }

        return $this;
    }
}