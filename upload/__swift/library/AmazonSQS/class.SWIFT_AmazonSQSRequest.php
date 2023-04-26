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
* $Id: S3.php 33 2008-07-30 17:30:20Z don.schonknecht $
*
* Copyright (c) 2007, Donovan Schonknecht.  All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice,
*   this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright
*   notice, this list of conditions and the following disclaimer in the
*   documentation and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
* AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
* IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
* ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
* LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
* CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
* SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
* INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
* CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
* ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * The SQS Request Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_AmazonSQSRequest extends SWIFT_Library
{
    private $_actionType = '';
    private $_queueName = '';
    private $_expiry = false;
    private $_actionMethod = '';
    private $_requestParameters = array();
    private $_requestHeaders = array('Host' => '', 'Date' => '', 'Content-MD5' => '', 'Content-Type' => '');
    private $_SWIFT_AmazonSQSResponseObject;

    // Core Constants
    const ACTION_GET = 'GET';
    const ACTION_PUT = 'PUT';
    const ACTION_POST = 'POST';
    const ACTION_DELETE = 'DELETE';
    const ACTION_HEAD = 'HEAD';

    /**
     * Constructor
     *
     * @param mixed $_actionType The SQS Request Action Type
     * @param string $_actionMethod The Action Method (ListQueues/CreateQueue etc.)
     * @param string $_queueName The Queue Name
     * @param int|false $_expiry (OPTIONAL) The Expirye
     */
    function __construct($_actionType, $_actionMethod, $_queueName = '', $_expiry = false) {
        parent::__construct();

        if (!$this->SetAction($_actionType) || !$this->SetQueue($_queueName) || !$this->SetActionMethod($_actionMethod) || !$this->SetExpiry($_expiry))
        {
            $this->SetIsClassLoaded(false);

            throw new SWIFT_AmazonSQS_Exception(SWIFT_CREATEFAILED);
        }

        $this->SetParameter('Version', '2009-02-01');

        $this->SetResponseObject(new SWIFT_AmazonSQSResponse());
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
     * @param string $_actionType The SQS Request Action Type
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
     * @param string $_actionType The SQS Request Action Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If Invalid Data is Provided
     */
    public function SetAction($_actionType)
    {
        if (!self::IsValidAction($_actionType))
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

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
     * @throws SWIFT_AmazonSQS_Exception If the Class is not loaded
     */
    public function GetAction()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_actionType;
    }

    /**
     * Set the Queue
     *
     * @author Varun Shoor
     * @param string $_queueName The Queue Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetQueue($_queueName)
    {
        $this->_queueName = $_queueName;

        return true;
    }

    /**
     * Retrieve the currently set queue name
     *
     * @author Varun Shoor
     * @return mixed "_queueName" (STRING) on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If the Class is not loaded
     */
    public function GetQueue()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_queueName;
    }

    /**
     * Set the Action Method
     *
     * @author Varun Shoor
     * @param string $_actionMethod The Action Method
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If Invalid Data is PRovided
     */
    public function SetActionMethod($_actionMethod)
    {
        if (empty($_actionMethod))
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

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
     * @throws SWIFT_AmazonSQS_Exception If the Class is not loaded
     */
    public function GetActionMethod()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_actionMethod;
    }

    /**
     * Set the Expiry
     *
     * @author Varun Shoor
     * @param int $_expiry The Expiry
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If Invalid Data is PRovided
     */
    public function SetExpiry($_expiry)
    {
        $_expiry = $_expiry;

        $this->_expiry = $_expiry;

        return true;
    }

    /**
     * Retrieve the currently set action method
     *
     * @author Varun Shoor
     * @return mixed "_expiry" (INT) on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If the Class is not loaded
     */
    public function GetExpiry()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_expiry;
    }

    /**
     * Set the Response Object
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonSQSResponse $_SWIFT_AmazonSQSResponseObject The SWIFT_AmazonSQSResponse Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_AmazonSQS_Exception If Invalid Data is Provided
     */
    public function SetResponseObject(SWIFT_AmazonSQSResponse $_SWIFT_AmazonSQSResponseObject)
    {
        if (!$_SWIFT_AmazonSQSResponseObject instanceof SWIFT_AmazonSQSResponse || !$_SWIFT_AmazonSQSResponseObject->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_SWIFT_AmazonSQSResponseObject = $_SWIFT_AmazonSQSResponseObject;

        return true;
    }

    /**
     * Retrieve the currently set response object
     *
     * @author Varun Shoor
     * @return false|SWIFT_AmazonSQSResponse "_SWIFT_AmazonSQSResponseObject" (SWIFT_AmazonSQSResponse Object) on Success, "false" otherwise
     */
    public function GetResponseObject()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_SWIFT_AmazonSQSResponseObject;
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
     * @param SWIFT_AmazonSQS $_SWIFT_AmazonSQSObject The SWIFT_AmazonSQS Object Pointer
     * @return bool|SWIFT_AmazonSQSResponse
     * @throws SWIFT_AmazonSQS_Exception If the Class is not loaded
     */
    public function GetResponse(SWIFT_AmazonSQS $_SWIFT_AmazonSQSObject)
    {
        if (!$this->GetIsClassLoaded() || !$_SWIFT_AmazonSQSObject->GetIsClassLoaded())
        {
            throw new SWIFT_AmazonSQS_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->SetParameter('Action', $this->GetActionMethod());

        if ($_SWIFT_AmazonSQSObject->GetSecretKey() != '')
        {
            $this->SetParameter('AWSAccessKeyId', $_SWIFT_AmazonSQSObject->GetAccessKey());
            $this->SetParameter('SignatureVersion', '2');
            $this->SetParameter('SignatureMethod', 'HmacSHA256');
        }

        if ($this->GetExpiry())
        {
            $this->SetParameter('Expires', gmdate('c'));
        } else {
            $this->SetParameter('Timestamp', gmdate('c'));
        }

        $_parameterContainer = array();
        foreach ($this->GetParameters() as $_var => $_value)
        {
            $_parameterContainer[] = $_var . '=' . rawurlencode($_value);
        }

        sort($_parameterContainer, SORT_STRING);

        $_queryString = implode('&', $_parameterContainer);


        $_extendedAccountNumber = '';
        $_queueName = $this->GetQueue();
        if (!empty($_queueName))
        {
            $_extendedAccountNumber = '/' . $_SWIFT_AmazonSQSObject->GetAWSAccountNumber();
        }

        $_stringToSign = $this->GetAction() . "\n" . SWIFT_AmazonSQS::BASE_URL . "\n" . $_extendedAccountNumber . "/" . $this->GetQueue() . "\n" . $_queryString;

        if ($_SWIFT_AmazonSQSObject->GetSecretKey() != '')
        {
            $_queryString .= '&Signature=' . rawurlencode($_SWIFT_AmazonSQSObject->__GetSignature($_stringToSign));
        }

        $_url = (($_SWIFT_AmazonSQSObject->CanUseSSL() && extension_loaded('openssl')) ? 'https://':'http://') . SWIFT_AmazonSQS::BASE_URL . $_extendedAccountNumber . '/' . $this->GetQueue() . '?' . $_queryString;
        //echo "\n\n==" . $_url . "==\n\n";

        // Basic setup
        $_curlHandle = curl_init();
        curl_setopt($_curlHandle, CURLOPT_USERAGENT, 'SWIFT_AmazonSQS');

        if ($_SWIFT_AmazonSQSObject->CanUseSSL()) {
            curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYHOST, $_SWIFT_AmazonSQSObject->GetVerifyHost());
            curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYPEER, $_SWIFT_AmazonSQSObject->GetVerifyPeer());
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

            case self::ACTION_PUT:
            case self::ACTION_POST:
                curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, $this->GetAction());
                $_requestHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
                $_requestHeaders[] = 'Content-Length: 0';

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
            $this->GetResponseObject()->SetBodyObject(@simplexml_load_string($this->GetResponseObject()->GetBody()));

            // Grab SQS errors
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
