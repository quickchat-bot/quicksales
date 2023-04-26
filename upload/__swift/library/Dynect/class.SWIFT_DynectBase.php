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
 * @copyright      Copyright (c) 2001-2013, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Base Dynect REST Class
 *
 * @author Varun Shoor
 */
class SWIFT_DynectBase extends SWIFT_RESTBase
{
    const API_URL = 'https://api2.dynect.net/REST/';

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    /**
     * @var SWIFT_DynectSession|bool
     */
    protected $DynectSession = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param bool $_ignoreSession Whether to Ignore the presence of dynect session
     *
     * @throws SWIFT_Exception If Invalid Dynect Session is Available
     */
    public function __construct($_ignoreSession = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        parent::__construct(self::API_URL, self::POSTTYPE_JSON);

        if ($_ignoreSession == false && (!$_SWIFT->DynectSession instanceof SWIFT_DynectSession || !$_SWIFT->DynectSession->GetIsClassLoaded())) {
            throw new SWIFT_Exception('No Dynect Session Object Available');
        }

        if ($_SWIFT->DynectSession instanceof SWIFT_DynectSession && $_SWIFT->DynectSession->GetIsClassLoaded()) {
            $this->SetSession($_SWIFT->DynectSession);
        }
    }

    /**
     * Set the Dynect Session Object
     *
     * @author Varun Shoor
     *
     * @param SWIFT_DynectSession $_SWIFT_DynectSessionObject
     *
     * @return SWIFT_DynectBase
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetSession(SWIFT_DynectSession $_SWIFT_DynectSessionObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_DynectSessionObject instanceof SWIFT_DynectSession || !$_SWIFT_DynectSessionObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->DynectSession = $_SWIFT_DynectSessionObject;

        $this->SetHeader('Auth-Token', $_SWIFT_DynectSessionObject->GetToken());

        return $this;
    }

    /**
     * Get the Dynect Session Object
     *
     * @author Varun Shoor
     * @return SWIFT_DynectSession
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSession()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->DynectSession;
    }

    /**
     * Perform a GET operation
     *
     * @author Varun Shoor
     *
     * @param string $_controllerPath
     * @param array  $_queryStringContainer (OPTIONAL) The Query String to Dispatch
     *
     * @return SWIFT_RESTResponse
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_controllerPath, $_queryStringContainer = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_controllerPath) || !is_array($_queryStringContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return parent::Get('/' . $_controllerPath, $_queryStringContainer);
    }

    /**
     * Perform a PUT operation
     *
     * @author Varun Shoor
     *
     * @param string $_controllerPath
     * @param array  $_queryStringContainer (OPTIONAL) The Query String to Dispatch
     *
     * @return SWIFT_RESTResponse
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_controllerPath, $_queryStringContainer = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_controllerPath) || !is_array($_queryStringContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return parent::Put('/' . $_controllerPath, $_queryStringContainer);
    }

    /**
     * Perform a POST operation
     *
     * @author Varun Shoor
     *
     * @param string $_controllerPath
     * @param array  $_queryStringContainer (OPTIONAL) The Query String to Dispatch
     *
     * @return SWIFT_RESTResponse
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post($_controllerPath, $_queryStringContainer = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_controllerPath) || !is_array($_queryStringContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return parent::Post('/' . $_controllerPath, $_queryStringContainer);
    }

    /**
     * Perform a DELETE operation
     *
     * @author Varun Shoor
     *
     * @param string $_controllerPath
     * @param array|string  $_queryStringContainer (OPTIONAL) The Query String to Dispatch
     *
     * @return SWIFT_RESTResponse
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_controllerPath, $_queryStringContainer = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_controllerPath) || !is_array($_queryStringContainer)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return parent::Delete('/' . $_controllerPath, $_queryStringContainer);
    }

    /**
     * Retrieve a temporary base object
     *
     * @author Varun Shoor
     *
     * @param bool $_ignoreSession Whether to Ignore the presence of dynect session
     *
     * @return SWIFT_DynectBase
     */
    public static function GetBase($_ignoreSession = false)
    {
        $_SWIFT_DynectBaseObject = new SWIFT_DynectBase($_ignoreSession);

        return $_SWIFT_DynectBaseObject;
    }

    /**
     * Check the REST Response to make sure the error codes are right
     *
     * @author Varun Shoor
     *
     * @param SWIFT_RESTResponse $_SWIFT_RESTResponseObject
     * @param string             $_callingFunction (OPTIONAL) The Name of Function Running this Check
     * @param int                $_httpCode        (OPTIONAL) The HTTP Code to Check Against
     * @param bool               $_endExecution    (BOOL) Whether to End the Execution if Error Encountered
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function CheckResponse(SWIFT_RESTResponse $_SWIFT_RESTResponseObject, $_callingFunction = '', $_httpCode = 200, $_endExecution = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!$_SWIFT_RESTResponseObject->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT_RESTResponseObject->GetError() === false && $_SWIFT_RESTResponseObject->GetHTTPCode() !== $_httpCode) {
            $_SWIFT_RESTResponseObject->Error($_SWIFT_RESTResponseObject->GetHTTPCode(), 'Unexpected HTTP status');
        }

        $_JSONObject  = $_SWIFT_RESTResponseObject->GetBodyJSONObject();
        $_jsonFailure = false;
        if (isset($_JSONObject->status) && (string) $_JSONObject->status == self::STATUS_FAILURE) {
            $_jsonFailure = true;
        }

        if ($_SWIFT_RESTResponseObject->GetError() !== false || $_jsonFailure == true) {

            if ($_endExecution) {
                $_errorContainer = $_SWIFT_RESTResponseObject->GetError();

                $_responseBody = '';
                $_index        = 1;
                if (!$_jsonFailure) {
                    $_responseBody = print_r($_SWIFT_RESTResponseObject->GetBodyJSONObject(), true);
                } else {
                    if (isset($_JSONObject->msgs)) {
                        foreach ($_JSONObject->msgs as $_jsonMsg) {
                            $_responseBody .= $_index . '. ' . (string) $_jsonMsg->LVL . ': ' . (string) $_jsonMsg->INFO . ' (' . (string) $_jsonMsg->SOURCE . ')' . SWIFT_CRLF;

                            $_index++;
                        }
                    }
                }

                throw new SWIFT_REST_Exception(sprintf('SWIFT_DynectBase::' . $_callingFunction . ': [%s] %s', $_errorContainer['code'], $_errorContainer['message']) . SWIFT_CRLF . $_responseBody . SWIFT_CRLF . $_SWIFT_RESTResponseObject->GetDebugInfo());
            }

            return false;
        }

        return true;
    }
}
