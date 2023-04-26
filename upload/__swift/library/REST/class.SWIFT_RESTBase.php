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
 * The REST Base Class (For Interacting with any REST based API Interface)
 *
 * @author Varun Shoor
 */
class SWIFT_RESTBase extends SWIFT_Library
{
    protected $_baseURL = false;
    protected $_curlHandle = false;
    protected $_resultBody = '';
    protected $_httpCode = false;
    protected $_requestHeaders = array();
    protected $_postType = false;

    /**
     * @var SWIFT_RESTResponse|false
     */
    protected $RESTResponse = false;

    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_GET    = 'GET';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD   = 'HEAD';

    const POSTTYPE_FORM = 1;
    const POSTTYPE_JSON = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     *
     * @param string $_baseURL
     * @param int    $_postType
     *
     * @throws SWIFT_REST_Exception If Creation Fails
     */
    public function __construct($_baseURL, $_postType = self::POSTTYPE_FORM)
    {
        parent::__construct();

        $this->SetBaseURL($_baseURL);
        $this->SetPOSTType($_postType);

        $this->SetResponseObject(new SWIFT_RESTResponse());
    }

    /**
     * Set the POST Type
     *
     * @author Varun Shoor
     *
     * @param int $_postType
     *
     * @return SWIFT_RESTBase
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetPOSTType($_postType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidPOSTType($_postType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->_postType = $_postType;

        return $this;
    }

    /**
     * Retrieve the POST Type
     *
     * @author Varun Shoor
     * @return int
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPOSTType()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_postType;
    }

    /**
     * Set the Base URL
     *
     * @author Varun Shoor
     *
     * @param string $_baseURL
     *
     * @return SWIFT_RESTBase
     * @throws SWIFT_REST_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetBaseURL($_baseURL)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_baseURL)) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        $this->_baseURL = $_baseURL;

        return $this;
    }

    /**
     * Retrieve the currently set base URL
     *
     * @author Varun Shoor
     * @return string
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function GetBaseURL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_baseURL;
    }

    /**
     * Check to see if it is a valid method
     *
     * @author Varun Shoor
     *
     * @param int $_method
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidMethod($_method)
    {
        return ($_method == self::METHOD_POST || $_method == self::METHOD_PUT || $_method == self::METHOD_DELETE || $_method == self::METHOD_GET);
    }

    /**
     * Check to see if its a valid POST type
     *
     * @author Varun Shoor
     *
     * @param int $_postType
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidPOSTType($_postType)
    {
        return ($_postType == self::POSTTYPE_FORM || $_postType == self::POSTTYPE_JSON);
    }

    /**
     * Retrieve the CURL signature based on the parameters
     *
     * @author Varun Shoor
     *
     * @param int    $_method
     * @param string $_controllerPath
     * @param string $_salt
     *
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetCURLSignature($_method, $_controllerPath, $_salt)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return '';
    }

    /**
     * Set the Request Header
     *
     * @author Varun Shoor
     *
     * @param string $_key
     * @param string $_value
     *
     * @return SWIFT_RESTBase|false
     */
    public function SetHeader($_key, $_value)
    {
        if (empty($_key) || empty($_value)) {
            return false;
        }

        $this->_requestHeaders[$_key] = $_value;

        return $this;
    }

    /**
     * Retrieve the value of a header key
     *
     * @author Varun Shoor
     *
     * @param string $_key
     *
     * @return string
     */
    public function GetHeader($_key)
    {
        if (empty($_key) || !isset($this->_requestHeaders[$_key])) {
            return '';
        }

        return $this->_requestHeaders[$_key];
    }

    /**
     * Get the Complete Request Headers
     *
     * @author Varun Shoor
     * @return array|false
     */
    public function GetHeaders()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_requestHeaders;
    }

    /**
     * Initialize the CURL Object
     *
     * @author Varun Shoor
     *
     * @param int    $_method
     * @param string $_controllerPath
     * @param array  $_queryStringContainer
     *
     * @return SWIFT_RESTBase
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    protected function InitializeCURL($_method, $_controllerPath, $_queryStringContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->GetResponseObject()->SetBody('');

        // Headers
        $_requestHeaders  = array();
        $_headerContainer = $this->GetHeaders();
        if (!isset($_headerContainer['Content-Type'])) {
            if ($this->GetPOSTType() == self::POSTTYPE_FORM && ($_method == self::METHOD_POST || $_method == self::METHOD_PUT)) {
                $_headerContainer['Content-Type'] = 'application/x-www-form-urlencoded';
            } else if ($this->GetPOSTType() == self::POSTTYPE_JSON) {
                $_headerContainer['Content-Type'] = 'application/json';
            }
        }

        foreach ($_headerContainer as $_header => $_value) {
            if (strlen($_value) > 0) {
                $_requestHeaders[] = $_header . ': ' . $_value;
            }
        }

        $_debugInfo = '';

        $_finalQueryStringContainer = array();
        $_queryString               = '';
        $_postData                  = false;

        $_salt = BuildHash();

        if ($this->GetPOSTType() == self::POSTTYPE_FORM) {
            $_queryStringContainer['e']    = $_controllerPath;
            $_queryStringContainer['salt'] = $_salt;
        }

        if ($this->GetPOSTType() == self::POSTTYPE_FORM || $_method == self::METHOD_GET || $_method == self::METHOD_DELETE) {
            foreach ($_queryStringContainer as $_key => $_val) {
                $_finalQueryStringContainer[] = $_key . '=' . urlencode($_val);
            }

            sort($_finalQueryStringContainer, SORT_STRING);
            $_queryString = implode('&', $_finalQueryStringContainer);
        } else if ($this->GetPOSTType() == self::POSTTYPE_JSON && ($_method == self::METHOD_POST || $_method == self::METHOD_PUT)) {
            $_finalQueryStringContainer = $_queryStringContainer;
            $_queryString               = json_encode($_queryStringContainer);
        }

        $_signature = $this->GetCURLSignature($_method, $_controllerPath, $_salt);

        if (!empty($_signature) && $this->GetPOSTType() == self::POSTTYPE_FORM) {
            $_queryString .= IIF(!empty($_queryString), '&') . 'signature=' . urlencode($_signature);
            $_queryStringContainer['signature'] = $_signature;
        } else if (!empty($_signature) && $this->GetPOSTType() == self::POSTTYPE_JSON) {
            $_queryStringContainer['signature'] = $_signature;
        }

        // Final Query String Processing for POST/PUT
        if ($this->GetPOSTType() == self::POSTTYPE_FORM && ($_method == self::METHOD_POST || $_method == self::METHOD_PUT)) {
            $_postData = $_queryString;
        } else if ($this->GetPOSTType() == self::POSTTYPE_JSON && ($_method == self::METHOD_POST || $_method == self::METHOD_PUT)) {
            $_postData = $_queryString;
        }

        $_urlExtend = '';
        if ($this->GetPOSTType() == self::POSTTYPE_FORM && ($_method == self::METHOD_GET || $_method == self::METHOD_DELETE)) {
//            $_urlExtend = 'e=' . $_controllerPath;
        } else if ($this->GetPOSTType() == self::POSTTYPE_FORM && ($_method == self::METHOD_POST || $_method == self::METHOD_PUT)) {
//            $_urlExtend = $_controllerPath;
        } else {
            $_urlExtend = $_controllerPath;
        }

        $_url = StripTrailingSlash($this->GetBaseURL()) . $_urlExtend;

        /*        if ($this->GetPOSTType() == self::POSTTYPE_FORM && ($_method == self::METHOD_POST || $_method == self::METHOD_PUT) && substr($_url, -1) == '?') {
                    $_url = substr($_url, 0, strlen($_url)-1);
                }*/

        $_extenderChar = '?';
        if (stristr($_url, '?')) {
            $_extenderChar = '&';
        }

        $_url .= IIF($_method != self::METHOD_PUT && $_method != self::METHOD_POST && !empty($_queryString), $_extenderChar . $_queryString);

        $_debugInfo .= 'URL: ' . $_url . SWIFT_CRLF;
        $_debugInfo .= 'Method: ' . $_method . SWIFT_CRLF;
        $_debugInfo .= 'Data: ' . $_postData . SWIFT_CRLF;
        $_debugInfo .= 'Headers: ' . implode(', ', $_requestHeaders) . SWIFT_CRLF;

        $this->GetResponseObject()->SetDebugInfo($_debugInfo);

        // Basic setup
        $this->_resultBody = '';
        $this->_httpCode   = false;
        $_curlHandle       = curl_init();
        curl_setopt($_curlHandle, CURLOPT_USERAGENT, 'SWIFT_REST');
        curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($_curlHandle, CURLOPT_URL, $_url);
        curl_setopt($_curlHandle, CURLOPT_TIMEOUT, 300);
        curl_setopt($_curlHandle, CURLOPT_CONNECTTIMEOUT, 300);

        curl_setopt($_curlHandle, CURLOPT_HEADER, false);
        curl_setopt($_curlHandle, CURLOPT_NOPROGRESS, true);
        curl_setopt($_curlHandle, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($_curlHandle, CURLOPT_WRITEFUNCTION, array($this, '__ResponseWriteCallback'));
        curl_setopt($_curlHandle, CURLOPT_HEADERFUNCTION, array($this, '__ResponseHeaderCallback'));
        curl_setopt($_curlHandle, CURLOPT_FOLLOWLOCATION, true);

        /*        if ($_method == self::METHOD_PUT || $_method == self::METHOD_POST)
                {
                }*/

        // Request types
        switch ($_method) {
            case self::METHOD_GET:
                break;

            case self::METHOD_PUT:
            case self::METHOD_POST:
                curl_setopt($_curlHandle, CURLOPT_POST, 1);
                curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, $_method);
                curl_setopt($_curlHandle, CURLOPT_POSTFIELDS, $_postData);

                break;

            case self::METHOD_HEAD:
                curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, self::METHOD_HEAD);
                curl_setopt($_curlHandle, CURLOPT_NOBODY, true);

                break;

            case self::METHOD_DELETE:
                curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, self::METHOD_DELETE);

                break;

            default:
                break;
        }

        curl_setopt($_curlHandle, CURLOPT_HTTPHEADER, $_requestHeaders);

        $this->_curlHandle = $_curlHandle;

        return $this;
    }

    /**
     * Execute the CURL Request
     *
     * @author Varun Shoor
     * @return SWIFT_RESTResponse
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    protected function ExecuteCURL()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$this->_curlHandle) {
            $this->GetResponseObject()->Error(curl_errno($this->_curlHandle), curl_error($this->_curlHandle));
        }

        // Execute, grab errors
        if ($this->_curlHandle && curl_exec($this->_curlHandle)) {
            $this->GetResponseObject()->SetHTTPCode(curl_getinfo($this->_curlHandle, CURLINFO_HTTP_CODE));
        } else {
            $this->GetResponseObject()->Error(curl_errno($this->_curlHandle), curl_error($this->_curlHandle));
        }

        @curl_close($this->_curlHandle);

        // Parse body into XML
        if ($this->GetResponseObject()->GetError() === false && trim($this->GetResponseObject()->GetBody()) != '' && $this->GetResponseObject()->GetHeader('Content-Type') == 'text/xml') {
            $this->GetResponseObject()->SetBodyXMLObject(simplexml_load_string($this->GetResponseObject()->GetBody()));
        } else if ($this->GetResponseObject()->GetError() === false && trim($this->GetResponseObject()->GetBody()) != '' && $this->GetResponseObject()
            ->GetHeader('Content-Type') == 'application/json'
        ) {
            $this->GetResponseObject()->SetBodyJSONObject(json_decode($this->GetResponseObject()->GetBody()));
        }

        return $this->GetResponseObject();
    }

    /**
     * Set the Response Object
     *
     * @author Varun Shoor
     *
     * @param SWIFT_RESTResponse $_SWIFT_RESTResponseObject
     *
     * @return SWIFT_RESTBase
     * @throws SWIFT_REST_Exception If Invalid Data is Provided
     */
    public function SetResponseObject(SWIFT_RESTResponse $_SWIFT_RESTResponseObject)
    {
        if (!$_SWIFT_RESTResponseObject instanceof SWIFT_RESTResponse || !$_SWIFT_RESTResponseObject->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        $this->RESTResponse = $_SWIFT_RESTResponseObject;

        return $this;
    }

    /**
     * Retrieve the currently set response object
     *
     * @author Varun Shoor
     * @return SWIFT_RESTResponse|false
     */
    public function GetResponseObject()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->RESTResponse;
    }

    /**
     * CURL write callback
     *
     * @param resource $_curlHandle
     * @param string   $_data
     *
     * @return int
     */
    public function __ResponseWriteCallback(&$_curlHandle, &$_data)
    {
        $this->GetResponseObject()->AppendBody($_data);

        return strlen($_data);
    }

    /**
     * CURL header callback
     *
     * @param resource $_curlHandle
     * @param string   $_data
     *
     * @return int
     */
    public function __ResponseHeaderCallback(&$_curlHandle, &$_data)
    {
        if (($_stringLength = strlen($_data)) <= 2) {
            return $_stringLength;
        }

        if (substr($_data, 0, 4) == 'HTTP') {
            $this->GetResponseObject()->SetHTTPCode((int) (substr($_data, 9, 3)));
        } else {
            list($_header, $_value) = explode(': ', trim($_data), 2);

            $this->GetResponseObject()->SetHeader($_header, (is_numeric($_value) ? (int) ($_value) : $_value));
        }

        return $_stringLength;
    }

    /**
     * Check the REST Response to make sure the error codes are right
     *
     * @author Varun Shoor
     *
     * @param SWIFT_RESTResponse $_SWIFT_RESTResponseObject The SWIFT_RESTResponse Object Pointer
     * @param string             $_callingFunction          (OPTIONAL) The Name of Function Running this Check
     * @param int                $_httpCode                 (OPTIONAL) The HTTP Code to Check Against
     * @param bool               $_endExecution             (BOOL) Whether to End the Execution if Error Encountered
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

        if ($_SWIFT_RESTResponseObject->GetError() !== false) {

            if ($_endExecution) {
                $_errorContainer = $_SWIFT_RESTResponseObject->GetError();

                $_responseBody = $_SWIFT_RESTResponseObject->GetBody();
                if ($_SWIFT_RESTResponseObject->GetHeader('Content-Type') == 'application/json') {
                    $_responseBody = print_r($_SWIFT_RESTResponseObject->GetBodyJSONObject(), true);
                }

                throw new SWIFT_REST_Exception(sprintf("SWIFT_RESTBase::" . $_callingFunction . ": [%s] %s", $_errorContainer['code'], $_errorContainer['message']) . SWIFT_CRLF . $_responseBody);
            }

            return false;
        }

        return true;
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
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function Get($_controllerPath, $_queryStringContainer = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_controllerPath) || !is_array($_queryStringContainer)) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        $this->InitializeCURL(self::METHOD_GET, $_controllerPath, $_queryStringContainer);

        $this->ExecuteCURL();

        return $this->GetResponseObject();
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
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function Put($_controllerPath, $_queryStringContainer = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_controllerPath) || !is_array($_queryStringContainer)) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        $this->InitializeCURL(self::METHOD_PUT, $_controllerPath, $_queryStringContainer);

        curl_setopt($this->_curlHandle, CURLOPT_CUSTOMREQUEST, self::METHOD_PUT);

        $this->ExecuteCURL();

        return $this->GetResponseObject();
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
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function Post($_controllerPath, $_queryStringContainer = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_controllerPath) || !is_array($_queryStringContainer)) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        $this->InitializeCURL(self::METHOD_POST, $_controllerPath, $_queryStringContainer);

        curl_setopt($this->_curlHandle, CURLOPT_CUSTOMREQUEST, self::METHOD_POST);

        $this->ExecuteCURL();

        return $this->GetResponseObject();
    }

    /**
     * Perform a DELETE operation
     *
     * @author Varun Shoor
     *
     * @param string $_controllerPath
     * @param array  $_queryStringContainer (OPTIONAL) The Query String to Dispatch
     *
     * @return SWIFT_RESTResponse
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function Delete($_controllerPath, $_queryStringContainer = array())
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_controllerPath) || !is_array($_queryStringContainer)) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        $this->InitializeCURL(self::METHOD_DELETE, $_controllerPath, $_queryStringContainer);

        curl_setopt($this->_curlHandle, CURLOPT_CUSTOMREQUEST, self::METHOD_DELETE);

        $this->ExecuteCURL();

        return $this->GetResponseObject();
    }
}
