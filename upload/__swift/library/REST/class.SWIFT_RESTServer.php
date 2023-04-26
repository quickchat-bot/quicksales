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

/**
 * The REST Server Implementation Class
 *
 * @author Varun Shoor
 */
class SWIFT_RESTServer extends SWIFT_Library
{
    private $_activeMethod = false;
    private $_variableContainer = array();
    private $_rawData = '';

    // Core Constants
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_GET = 'GET';
    const METHOD_DELETE = 'DELETE';

    const HTTP_NOTFOUND = '404 Not Found';
    const HTTP_OK = '200 OK';
    const HTTP_CREATED = '201 Created';
    const HTTP_DELETE = '204 No Content';
    const HTTP_ACCEPTED = '202 Accepted';
    const HTTP_UNAUTHORIZED = '401 Unauthorized';
    const HTTP_BADREQUEST = '400 Bad Request';
    const HTTP_FORBIDDEN = '403 Forbidden';
    const HTTP_NOTALLOWED = '405 Not Allowed';

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();

        if (!$this->InitializeServer())
        {
            $this->SetIsClassLoaded(false);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Initialize the REST Server
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    protected function InitializeServer()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        // First Decide on the Method
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->SetMethod(self::METHOD_GET);
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->SetMethod(self::METHOD_POST);
        } else if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            $this->SetMethod(self::METHOD_PUT);
        } else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $this->SetMethod(self::METHOD_DELETE);
        } else {
            return false;
        }

        // Now try to parse the incoming variables
        if (!$this->ParseVariables())
        {
            return false;
        }

        return true;
    }

    /**
     * Checks to see if the given code is a valid code
     *
     * @author Varun Shoor
     * @param mixed $_httpCode The HTTP Code
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidHTTPCode($_httpCode)
    {
        if ($_httpCode == self::HTTP_NOTFOUND || $_httpCode == self::HTTP_OK || $_httpCode == self::HTTP_CREATED || $_httpCode == self::HTTP_DELETE || $_httpCode == self::HTTP_ACCEPTED || $_httpCode == self::HTTP_UNAUTHORIZED || $_httpCode == self::HTTP_BADREQUEST || $_httpCode == self::HTTP_FORBIDDEN || $_httpCode == self::HTTP_NOTALLOWED)
        {
            return true;
        }

        return false;
    }

    /**
     * Dispatch the relevant HTTP Status
     *
     * @author Varun Shoor
     * @param mixed $_httpCode The HTTP Code
     * @param string $_customContent (OPTIONAL) The Custom Content at End
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function DispatchStatus($_httpCode, $_customContent = '')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidHTTPCode($_httpCode)) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        header('HTTP/1.1 ' . $_httpCode);
        echo $_customContent;

        return true;
    }

    /**
     * Parse the Incoming Variables
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    protected function ParseVariables()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        switch ($this->GetMethod())
        {
            case self::METHOD_GET:
                    $this->SetVariableContainer($this->ParseInputStream(self::METHOD_GET));

                    return true;
                break;

            case self::METHOD_POST:
                    $this->SetVariableContainer($this->ParseInputStream(self::METHOD_POST));

                    return true;
                break;

            case self::METHOD_PUT:
                    $this->SetVariableContainer($this->ParseInputStream(self::METHOD_PUT));

                    return true;
                break;

            case self::METHOD_DELETE:
                    $this->SetVariableContainer($this->ParseInputStream(self::METHOD_DELETE));

                    return true;
                break;

            default:
                return false;

                break;
        }

        return false;
    }

    /**
     * Parse the Input Stream for Variables
     *
     * @author Varun Shoor
     * @param mixed $_method The Request Method
     * @return array
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    private function ParseInputStream($_method)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_variableContainer = array();

        $_inputStream = file_get_contents('php://input');
        $this->SetRawData($_inputStream);

        if (isset($_SERVER['CONTENT_TYPE']) && strstr($_SERVER['CONTENT_TYPE'], 'application/json')) {
            return json_decode($_inputStream, true);
        }

        if ($_method == self::METHOD_GET && _is_array($_GET)) {
            return $_GET;
        } else if ($_method == self::METHOD_POST && _is_array($_POST)) {
            return $_POST;
        } else if ($_method == self::METHOD_DELETE && _is_array($_GET)) {
            return $_GET;
        } else if ($_method == self::METHOD_PUT && _is_array($_POST)) {
            return $_POST;
        } else if ($_method == self::METHOD_PUT && _is_array($_GET)) {
            return $_GET;
        }

        parse_str($_inputStream, $_variableContainer);

        if ($_method == self::METHOD_PUT) {
            $_POST = $_variableContainer;
        }

        return $_variableContainer;
    }

    /**
     * Set incoming raw data
     *
     * @author Ruchi Kothari
     * @param string $_inputStream Raw data
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    private function SetRawData($_inputStream)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_rawData = $_inputStream;

        return true;
    }

    /**
     * Get raw data
     *
     * @author Ruchi Kothari
     * @return string Raw data on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */

    public function GetRawData()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_rawData;
    }

    /**
     * Get raw data in json format
     *
     * @author Ruchi Kothari
     * @return mixed JSON decoded data on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function GetRawDataASJSON()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return json_decode($this->_rawData);
    }

    /**
     *
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    private function GetSignature()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        //        return base64_encode(hash_hmac('sha256', $string, self::$__secretKey, true));
    }

    /**
     * Set the Variable Container
     *
     * @author Varun Shoor
     * @param array $_variableContainer The Variable Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    protected function SetVariableContainer($_variableContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!is_array($_variableContainer)) {
            return false;
        }

        $this->_variableContainer = $_variableContainer;

        return true;
    }

    /**
     * Get the Variable Value
     *
     * @author Varun Shoor
     * @param string $_variableKey The Variable Key
     * @return mixed "_variableContainer[_variableKey]" (STRING) on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function Get($_variableKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_variableContainer[$_variableKey])) {
            return false;
        }

        return $this->_variableContainer[$_variableKey];
    }

    /**
     * Get the Variable Container Array
     *
     * @author Varun Shoor
     * @return mixed "_variableContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function GetVariableContainer()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_variableContainer;
    }

    /**
     * Check to see if it is a valid method
     *
     * @author Varun Shoor
     * @param mixed $_method The REST Method
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidMethod($_method)
    {
        if ($_method == self::METHOD_POST || $_method == self::METHOD_PUT || $_method == self::METHOD_DELETE || $_method == self::METHOD_GET)
        {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the currently active method
     *
     * @author Varun Shoor
     * @return mixed "_activeMethod" (STRING) on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function GetMethod()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_activeMethod;
    }

    /**
     * Set the Method
     *
     * @author Varun Shoor
     * @param mixed $_method The REST Method
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    protected function SetMethod($_method)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidMethod($_method)) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        $this->_activeMethod = $_method;

        return true;
    }

    /**
     * Returns the function name for method
     *
     * @author Varun Shoor
     * @param string $_defaultAction
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetMethodFunction($_defaultAction = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        switch ($this->GetMethod()) {
            case self::METHOD_GET:
                if ($_defaultAction == SWIFT_Controller::DEFAULT_ACTION) {
                    return 'GetList';
                }

                return 'Get';

                break;

            case self::METHOD_POST:
                return 'Post';

                break;

            case self::METHOD_PUT:
                return 'Put';

                break;

            case self::METHOD_DELETE:
                return 'Delete';

                break;

            default:
                break;
        }

        throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
    }
}
?>