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

/**
 * The RDS Request Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_AmazonRDSRequest extends SWIFT_Library
{
    private $_actionType = '';
    private $_actionMethod = '';
    private $_requestParameters = array();
    private $_requestHeaders = array('Host' => '', 'Date' => '', 'Content-MD5' => '', 'Content-Type' => '');
    private $_SWIFT_AmazonRDSResponseObject;

    // Core Constants
    const ACTION_GET = 'GET';
    const ACTION_PUT = 'PUT';
    const ACTION_POST = 'POST';
    const ACTION_DELETE = 'DELETE';
    const ACTION_HEAD = 'HEAD';

    /**
     * Constructor
     *
     * @param mixed $_actionType The RDS Request Action Type
     * @param string $_actionMethod The Action Method (DescribeDBInstance etc.)e
     */
    function __construct($_actionType, $_actionMethod) {
        parent::__construct();

        if (!$this->SetAction($_actionType) || !$this->SetActionMethod($_actionMethod))
        {
            $this->SetIsClassLoaded(false);

            throw new SWIFT_AmazonRDS_Exception(SWIFT_CREATEFAILED);
    }

        $this->SetParameter('Version', '2010-07-28');

        $this->SetResponseObject(new SWIFT_AmazonRDSResponse());
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
     * Check to see if its a valid action type
     *
     * @author Varun Shoor
     * @param string $_actionType The RDS Request Action Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidAction($_actionType)
    {
        if ($_actionType == self::ACTION_GET || $_actionType == self::ACTION_PUT || $_actionType == self::ACTION_DELETE || $_actionType == self::ACTION_HEAD)
        {
            return true;
        }

        return false;
    }

    /**
     * Set the Current Action
     *
     * @author Varun Shoor
     * @param string $_actionType The RDS Request Action Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonRDS_Exception If Invalid Data is Provided
     */
    public function SetAction($_actionType)
    {
        if (!self::IsValidAction($_actionType))
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_actionType = $_actionType;

        return true;
    }

    /**
     * Retrieve the currently set action type
     *
     * @author Varun Shoor
     * @return mixed "_actionType" (STRING) on Success, "false" otherwise
     * @throws SWIFT_AmazonRDS_Exception If the Class is not loaded
     */
    public function GetAction()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_actionType;
    }

    /**
     * Set the Action Method
     *
     * @author Varun Shoor
     * @param string $_actionMethod The Action Method
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonRDS_Exception If Invalid Data is PRovided
     */
    public function SetActionMethod($_actionMethod)
    {
        if (empty($_actionMethod))
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_actionMethod = $_actionMethod;

        return true;
    }

    /**
     * Retrieve the currently set action method
     *
     * @author Varun Shoor
     * @return mixed "_actionMethod" (STRING) on Success, "false" otherwise
     * @throws SWIFT_AmazonRDS_Exception If the Class is not loaded
     */
    public function GetActionMethod()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_actionMethod;
    }

    /**
     * Set the Response Object
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonRDSResponse $_SWIFT_AmazonRDSResponseObject The SWIFT_AmazonRDSResponse Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonRDS_Exception If Invalid Data is Provided
     */
    public function SetResponseObject(SWIFT_AmazonRDSResponse $_SWIFT_AmazonRDSResponseObject)
    {
        if (!$_SWIFT_AmazonRDSResponseObject instanceof SWIFT_AmazonRDSResponse || !$_SWIFT_AmazonRDSResponseObject->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_SWIFT_AmazonRDSResponseObject = $_SWIFT_AmazonRDSResponseObject;

        return true;
    }

    /**
     * Retrieve the currently set response object
     *
     * @author Varun Shoor
     * @return mixed "_SWIFT_AmazonRDSResponseObject" (SWIFT_AmazonRDSResponse Object) on Success, "false" otherwise
     */
    public function GetResponseObject()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_SWIFT_AmazonRDSResponseObject;
    }

    /**
     * Set the Request Header
     *
     * @author Varun Shoor
     * @param string $_key The Header Key
     * @param string $_value The Key Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetHeader($_key, $_value)
    {
        if (empty($_key) || empty($_value))
        {
            return false;
        }

        $this->_requestHeaders[$_key] = $_value;

        return true;
    }

    /**
     * Retrieve the value of a header key
     *
     * @author Varun Shoor
     * @param string $_key The Header Key
     * @return mixed "_requestHeaders[_key]" (STRING) on Success, "false" otherwise
     */
    public function GetHeader($_key)
    {
        if (empty($_key) || !isset($this->_requestHeaders[$_key]))
        {
            return '';
        }

        return $this->_requestHeaders[$_key];
    }

    /**
     * Get the Complete Request Headers
     *
     * @author Varun Shoor
     * @return mixed "_requestHeaders" (ARRAY) on Success, "false" otherwise
     */
    public function GetHeaders()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_requestHeaders;
    }

    /**
     * Set the Request Parameter
     *
     * @author Varun Shoor
     * @param string $_key The Header Key
     * @param string $_value The Key Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetParameter($_key, $_value)
    {
        if (empty($_key))
        {
            return false;
        }

        $this->_requestParameters[$_key] = $_value;

        return true;
    }

    /**
     * Get a Request Parameter
     *
     * @author Varun Shoor
     * @param string $_key The Header Key
     * @return mixed "_requestParamaters[_key]" (STRING) on Success, "false" otherwise
     */
    public function GetParameter($_key)
    {
        if (empty($_key) || !isset($this->_requestParameters[$_key]))
        {
            return '';
        }

        return $this->_requestParameters[$_key];
    }

    /**
     * Get Complete Request Parameters
     *
     * @author Varun Shoor
     * @return mixed "_requestParamaters" (ARRAY) on Success, "false" otherwise
     */
    public function GetParameters()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_requestParameters;
    }

    /**
     * Get the Response Object
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonRDS $_SWIFT_AmazonRDSObject The SWIFT_AmazonRDS Object Pointer
     * @return SWIFT_AmazonRDSResponse|bool
     * @throws SWIFT_AmazonRDS_Exception If the Class is not loaded
     */
    public function GetResponse(SWIFT_AmazonRDS $_SWIFT_AmazonRDSObject)
    {
        if (!$this->GetIsClassLoaded() || !$_SWIFT_AmazonRDSObject->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonRDS_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->SetParameter('Action', $this->GetActionMethod());

        $this->SetParameter('AWSAccessKeyId', $_SWIFT_AmazonRDSObject->GetAccessKey());
        $this->SetParameter('SignatureVersion', '2');
        $this->SetParameter('SignatureMethod', 'HmacSHA256');

        $this->SetParameter('Timestamp', gmdate('c'));

        $_parameterContainer = array();

        foreach ($this->GetParameters() as $_var => $_value)
        {
            $_parameterContainer[strtolower($_var)] = $_var . '=' . rawurlencode($_value);
        }

        sort($_parameterContainer, SORT_STRING);

        $_queryString = implode('&', $_parameterContainer);

        $_stringToSign = $this->GetAction() . "\n" . $_SWIFT_AmazonRDSObject->GetBaseURL() . "\n" . '/' . "\n" . $_queryString;

        if ($_SWIFT_AmazonRDSObject->GetSecretKey() != '')
        {
            $_queryString .= '&Signature=' . rawurlencode($_SWIFT_AmazonRDSObject->__GetSignature($_stringToSign));
        }

        $_url = (($_SWIFT_AmazonRDSObject->CanUseSSL() && extension_loaded('openssl')) ? 'https://':'http://') . $_SWIFT_AmazonRDSObject->GetBaseURL() . '/?' . $_queryString;
//        echo "\n\n==" . $_url . "==\n\n";

        // Basic setup
        $_curlHandle = curl_init();
        curl_setopt($_curlHandle, CURLOPT_USERAGENT, 'SWIFT_AmazonRDS');

        if ($_SWIFT_AmazonRDSObject->CanUseSSL()) {
            curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYHOST, $_SWIFT_AmazonRDSObject->GetVerifyHost());
            curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYPEER, $_SWIFT_AmazonRDSObject->GetVerifyPeer());
        }

        curl_setopt($_curlHandle, CURLOPT_URL, $_url);

        // Headers
        $_requestHeaders = array();
        foreach ($this->GetHeaders() as $_header => $_value)
        {
            if (strlen($_value) > 0)
            {
                $_requestHeaders[] = $_header . ': ' . $_value;
            }
        }

        curl_setopt($_curlHandle, CURLOPT_TIMEOUT, 60);
        curl_setopt($_curlHandle, CURLOPT_HEADER, false);
        curl_setopt($_curlHandle, CURLOPT_NOPROGRESS, true);
        curl_setopt($_curlHandle, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($_curlHandle, CURLOPT_WRITEFUNCTION, array($this, '__ResponseWriteCallback'));
        curl_setopt($_curlHandle, CURLOPT_HEADERFUNCTION, array($this, '__ResponseHeaderCallback'));
        curl_setopt($_curlHandle, CURLOPT_FOLLOWLOCATION, true);

        // Request types
        switch ($this->GetAction())
        {
            case self::ACTION_GET:
                break;

            case self::ACTION_PUT: case self::ACTION_POST:
                curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, $this->GetAction());
                $_requestHeaders[] = 'Content-Type: application/x-www-form-urlencoded';

                break;

            case self::ACTION_HEAD:
                curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, self::ACTION_HEAD);
                curl_setopt($_curlHandle, CURLOPT_NOBODY, true);

                break;

            case self::ACTION_DELETE:
                curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, self::ACTION_DELETE);

                break;

            default:
                break;
        }

        curl_setopt($_curlHandle, CURLOPT_HTTPHEADER, $_requestHeaders);

        // Execute, grab errors
        if (curl_exec($_curlHandle))
        {
            $this->GetResponseObject()->SetHTTPCode(curl_getinfo($_curlHandle, CURLINFO_HTTP_CODE));
        } else {
            $this->GetResponseObject()->Error(curl_errno($_curlHandle), curl_error($_curlHandle));
        }

        @curl_close($_curlHandle);

        // Parse body into XML
        if ($this->GetResponseObject()->GetError() === false && trim($this->GetResponseObject()->GetBody()) != '')
        {
            $this->GetResponseObject()->SetBodyObject(simplexml_load_string($this->GetResponseObject()->GetBody()));

            // Grab RDS errors
            if (!in_array($this->GetResponseObject()->GetHTTPCode(), array(200, 204)) && isset($this->GetResponseObject()->GetBodyObject()->Code, $this->GetResponseObject()->GetBodyObject()->Message)) {

                $this->GetResponseObject()->Error((string)$this->GetResponseObject()->GetBodyObject()->Code, (string)$this->GetResponseObject()->GetBodyObject()->Message);

                if (isset($this->GetResponseObject()->GetBodyObject()->Resource)) {
                    $this->GetResponseObject()->Error('', '', (string)$this->GetResponseObject()->GetBodyObject()->Resource);
                }
            }
        }

        return $this->GetResponseObject();
    }

    /**
     * CURL write callback
     *
     * @param resource $_curlHandle CURL resource
     * @param string $_data Data
     * @return integer
     */
    public function __ResponseWriteCallback(&$_curlHandle, &$_data)
    {
        $this->GetResponseObject()->AppendBody($_data);

        return strlen($_data);
    }

    /**
     * CURL header callback
     *
     * @param resource $_curlHandle CURL resource
     * @param string $_data Data
     * @return integer
     */
    public function __ResponseHeaderCallback(&$_curlHandle, &$_data)
    {
        if (($_stringLength = strlen($_data)) <= 2)
        {
            return $_stringLength;
        }

        if (substr($_data, 0, 4) == 'HTTP')
        {
            $this->GetResponseObject()->SetHTTPCode((int) (substr($_data, 9, 3)));
        } else {
            list($_header, $_value) = explode(': ', trim($_data), 2);

            if ($_header == 'Last-Modified')
            {
                $this->GetResponseObject()->SetHeader('time', strtotime($_value));
            } else if ($_header == 'Content-Length') {
                $this->GetResponseObject()->SetHeader('size', (int) ($_value));
            } else if ($_header == 'Content-Type') {
                $this->GetResponseObject()->SetHeader('type', $_value);
            } else if ($_header == 'ETag') {
                $this->GetResponseObject()->SetHeader('hash', substr($_value, 1, -1));
            } else if (preg_match('/^x-amz-meta-.*$/', $_header)) {
                $this->GetResponseObject()->SetHeader($_header, (is_numeric($_value) ? (int) ($_value) : $_value));
            }
        }

        return $_stringLength;
    }
}
?>