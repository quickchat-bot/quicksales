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
SWIFT_Loader::LoadInterface('REST:REST');
/**
 * The API Controller
 *
 * @author Varun Shoor
 */
class Controller_api extends SWIFT_Controller
{
    // Core Constants
    const FUNCTION_GET = 'Get';
    const FUNCTION_POST = 'Post';
    const FUNCTION_PUT = 'Put';
    const FUNCTION_DELETE = 'Delete';
    const FUNCTION_LIST = 'GetList';

    const SORT_ASC  = 'ASC';
    const SORT_DESC = 'DESC';

    /** @var SWIFT_RESTServer */
    public $RESTServer;
    /** @var SWIFT_RESTManager */
    public $RESTManager;
    /** @var SWIFT_XML */
    public $XML;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $_SWIFT = SWIFT::GetInstance();

        $this->Load->Library('XML:XML');
        $this->Load->Library('REST:RESTServer');
        $this->Load->Library('REST:RESTManager');

        if ($this->Settings->Get('g_enableapiinterface') != '1')
        {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_FORBIDDEN);

            if (in_array(SWIFT_INTERFACE, ['tests', 'console'])) {
                throw new SWIFT_Exception(SWIFT_RESTServer::HTTP_FORBIDDEN);
            }

            log_error_and_exit();
        }

        $_incomingVariableContainer = $this->RESTServer->GetVariableContainer();

        if (!isset($_incomingVariableContainer['salt']) || empty($_incomingVariableContainer['salt'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_UNAUTHORIZED);

            echo 'NO SALT PROVIDED!';

            if (in_array(SWIFT_INTERFACE, ['tests', 'console'])) {
                throw new SWIFT_Exception('NO SALT PROVIDED!');
            }

            log_error_and_exit();
        }

        $_signatureToken = $_incomingVariableContainer['salt'];

        if (!$this->RESTManager->Authenticate($this->RESTServer->Get('apikey'), str_replace(' ', '+', $this->RESTServer->Get('signature')), $_signatureToken))
        {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_UNAUTHORIZED);

            echo 'FAILED TO AUTHENTICATE';

            if (in_array(SWIFT_INTERFACE, ['tests', 'console'])) {
                throw new SWIFT_Exception('FAILED TO AUTHENTICATE');
            }

            log_error_and_exit();
        }

        SWIFT_CronManager::RunPendingTasks();

        @header('Content-type: text/xml');
    }

    /**
     * @author Saloni Dhall <saloni.dhall@opencart.com.vn>
     *
     * @param string $_sortOrder
     *
     * @return bool
     */
    public static function IsValidSortOrder($_sortOrder)
    {
        return in_array(strtoupper($_sortOrder), array(self::SORT_ASC, self::SORT_DESC));
    }

    /**
     * Attempt to call a method in the class pointer if it doesnt exist in this local class
     *
     * @author Varun Shoor
     * @param string $_name The Function Name
     * @param array $_arguments The Function Arguments
     * @return mixed "Function Result" (MIXED) on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function __call($_name, $_arguments)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_functionName = false;
        switch ($this->RESTServer->GetMethod())
        {
            case SWIFT_RESTServer::METHOD_GET:
                $_functionName = self::FUNCTION_GET;

                break;

            case SWIFT_RESTServer::METHOD_POST:
                $_functionName = self::FUNCTION_POST;

                break;

            case SWIFT_RESTServer::METHOD_PUT:
                $_functionName = self::FUNCTION_PUT;

                break;

            case SWIFT_RESTServer::METHOD_DELETE:
                $_functionName = self::FUNCTION_DELETE;

                break;

            default:
                throw new SWIFT_Exception('Invalid Function Called in API Controller: ' . htmlspecialchars($_name));

                break;
        }

        // List Function?
        if ($this->RESTServer->GetMethod() == SWIFT_RESTServer::METHOD_GET && $_name == SWIFT_Controller::DEFAULT_ACTION)
        {
            $_functionName = self::FUNCTION_LIST;
        }

        if ($_name == SWIFT_Controller::DEFAULT_ACTION)
        {
            $_defaultArgument = '';
        } else {
            $_defaultArgument = $_name;
        }

        /*
         * ###############################################
         * PERMISSION CHECKS
         * ###############################################
         */

        if ($this->Settings->Get('g_enableapiinterface') != '1')
        {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_FORBIDDEN);

            if (in_array(SWIFT_INTERFACE, ['tests', 'console'])) {
                throw new SWIFT_Exception(SWIFT_RESTServer::HTTP_FORBIDDEN);
            }

            log_error_and_exit();
        }

        $_argumentContainer = array_merge(array($_defaultArgument), $_arguments);

        /*
         * ###############################################
         * BEGIN AUTHENTICATION
         * ###############################################
         */

        $_incomingVariableContainer = $this->RESTServer->GetVariableContainer();

        if (!isset($_incomingVariableContainer['salt']) || empty($_incomingVariableContainer['salt'])) {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_UNAUTHORIZED);

            echo 'NO SALT PROVIDED!: ' . $_functionName;

            if (in_array(SWIFT_INTERFACE, ['tests', 'console'])) {
                throw new SWIFT_Exception('NO SALT PROVIDED!: ' . $_functionName);
            }

            log_error_and_exit();
        }

        $_signatureToken = $_incomingVariableContainer['salt'];

        if (!$this->RESTManager->Authenticate($this->RESTServer->Get('apikey'), str_replace(' ', '+', $this->RESTServer->Get('signature')), $_signatureToken))
        {
            $this->RESTServer->DispatchStatus(SWIFT_RESTServer::HTTP_UNAUTHORIZED);

            echo 'FAILED TO AUTHENTICATE: ' . $_functionName;

            if (in_array(SWIFT_INTERFACE, ['tests', 'console'])) {
                throw new SWIFT_Exception('FAILED TO AUTHENTICATE: ' . $_functionName);
            }

            log_error_and_exit();
        }


        /**
         * Begin Hook: restauthentication
         */

        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('restauthentication')) ? eval($_hookCode) : false;

        /**
         * End Hook
         */


        $_ReflectionObject = new ReflectionClass($this);

        if (!empty($_functionName) && $_ReflectionObject instanceof ReflectionClass)
        {
            if ($_ReflectionObject->hasMethod($_functionName))
            {
                // Before we call this, we need to update the router..
                $_SWIFT->Router->SetAction($_functionName);

                return call_user_func_array(array($this, $_functionName), $_argumentContainer);
            } else {
                throw new SWIFT_Exception('Undeclared Function Called in API Controller: ' . htmlspecialchars($_functionName));

                return false;
            }
        }

        return false;
    }
}
