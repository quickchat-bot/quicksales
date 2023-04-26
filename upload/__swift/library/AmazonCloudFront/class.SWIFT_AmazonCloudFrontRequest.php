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
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
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
 * The CloudFront Request Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_AmazonCloudFrontRequest extends SWIFT_Library
{
    private $_actionType = '';
    private $_uri = '';
    private $_resource = '';
    private $_requestParameters = array();
    private $_amazonHeaders = array();
    private $_requestHeaders = array('Host' => '', 'Date' => '', 'Content-MD5' => '', 'Content-Type' => '');
    private $_size = 0;
    private $_data = false;
    private $_SWIFT_AmazonCloudFrontResponseObject;

    // Core Constants
    const ACTION_GET    = 'GET';
    const ACTION_PUT    = 'PUT';
    const ACTION_DELETE = 'DELETE';
    const ACTION_HEAD   = 'HEAD';
    const ACTION_POST   = 'POST';

    /**
     * Constructor
     *
     * @param string $_actionType The CloudFront Request Action Type
     * @param string $_uri        The Object URI
     */
    function __construct($_actionType, $_uri = '')
    {
        parent::__construct();

        $_finalURI = $_uri !== '' ? '/' . $_uri : '/';

        if (!$this->SetAction($_actionType) || !$this->SetURI($_finalURI)) {
            throw new SWIFT_AmazonEC2_Exception(SWIFT_INVALIDDATA);
        }

        $this->SetHeader('Host', SWIFT_AmazonCloudFront::BASE_URL);
        $this->SetResource($this->GetURI());
        $this->SetHeader('Date', gmdate('D, d M Y H:i:s T'));

        $this->SetResponseObject(new SWIFT_AmazonCloudFrontResponse());
    }

    /**
     * Set the Request Data
     *
     * @author Varun Shoor
     *
     * @param string $_data The Request Data
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetData($_data)
    {
        if (empty($_data)) {
            return false;
        }

        $this->_data = $_data;
        $this->SetSize(mb_strlen($_data));

        return true;
    }

    /**
     * Get the Request Data
     *
     * @author Varun Shoor
     * @return mixed "_data" (STRING) on Success, "false" otherwise
     */
    public function GetData()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_data;
    }

    /**
     * Retrieve the Size
     *
     * @author Varun Shoor
     * @return mixed "_size" (INT) on Success, "false" otherwise
     */
    public function GetSize()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_size;
    }

    /**
     * Set the Size
     *
     * @author Varun Shoor
     *
     * @param mixed $_size The Size
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetSize($_size)
    {
        $this->_size = (int) ($_size);

        return true;
    }

    /**
     * Check to see if its a valid action type
     *
     * @author Varun Shoor
     *
     * @param string $_actionType The CloudFront Request Action Type
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidAction($_actionType)
    {
        if ($_actionType == self::ACTION_GET || $_actionType == self::ACTION_PUT || $_actionType == self::ACTION_DELETE ||
            $_actionType == self::ACTION_HEAD || $_actionType == self::ACTION_POST
        ) {
            return true;
        }

        return false;
    }

    /**
     * Set the Current Action
     *
     * @author Varun Shoor
     *
     * @param string $_actionType The CloudFront Request Action Type
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetAction($_actionType)
    {
        if (!self::IsValidAction($_actionType)) {
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
     */
    public function GetAction()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_actionType;
    }

    /**
     * Set the URI
     *
     * @author Varun Shoor
     *
     * @param string $_uri The URI
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetURI($_uri)
    {
        $_uri !== '' ? '/' . $_uri : '/';

        $this->_uri = $_uri;

        return true;
    }

    /**
     * Retrieve the URI
     *
     * @author Varun Shoor
     * @return mixed "_uri" (STRING) on Success, "false" otherwise
     */
    public function GetURI()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_uri;
    }

    /**
     * Set the CloudFront Resrouce
     *
     * @author Varun Shoor
     *
     * @param string $_resource The CloudFront Resource
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetResource($_resource)
    {
        if (empty($_resource)) {
            return false;
        }

        $this->_resource = $_resource;

        return true;
    }

    /**
     * Retrieve the Currently set Resource
     *
     * @author Varun Shoor
     * @return mixed "_resource" (STRING) on Success, "false" otherwise
     */
    public function GetResource()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_resource;
    }

    /**
     * Set the Response Object
     *
     * @author Varun Shoor
     *
     * @param SWIFT_AmazonCloudFrontResponse $_SWIFT_AmazonCloudFrontResponseObject The SWIFT_AmazonCloudFrontResponse Object Pointer
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetResponseObject(SWIFT_AmazonCloudFrontResponse $_SWIFT_AmazonCloudFrontResponseObject)
    {
        if (!$_SWIFT_AmazonCloudFrontResponseObject->GetIsClassLoaded()) {
            return false;
        }

        $this->_SWIFT_AmazonCloudFrontResponseObject = $_SWIFT_AmazonCloudFrontResponseObject;

        return true;
    }

    /**
     * Retrieve the currently set response object
     *
     * @author Varun Shoor
     * @return mixed "_SWIFT_AmazonCloudFrontResponseObject" (SWIFT_AmazonCloudFrontResponse Object) on Success, "false" otherwise
     */
    public function GetResponseObject()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_SWIFT_AmazonCloudFrontResponseObject;
    }

    /**
     * Set the Request Header
     *
     * @author Varun Shoor
     *
     * @param string $_key   The Header Key
     * @param string $_value The Key Value
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetHeader($_key, $_value)
    {
        if (empty($_key) || empty($_value)) {
            return false;
        }

        $this->_requestHeaders[$_key] = $_value;

        return true;
    }

    /**
     * Retrieve the value of a header key
     *
     * @author Varun Shoor
     *
     * @param string $_key The Header Key
     *
     * @return mixed "_requestHeaders[_key]" (STRING) on Success, "false" otherwise
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
     * @return mixed "_requestHeaders" (ARRAY) on Success, "false" otherwise
     */
    public function GetHeaders()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_requestHeaders;
    }

    /**
     * Set the Request Parameter
     *
     * @author Varun Shoor
     *
     * @param string $_key   The Header Key
     * @param string $_value The Key Value
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetParameter($_key, $_value)
    {
        if (empty($_key)) {
            return false;
        }

        $this->_requestParameters[$_key] = $_value;

        return true;
    }

    /**
     * Get a Request Parameter
     *
     * @author Varun Shoor
     *
     * @param string $_key The Header Key
     *
     * @return mixed "_requestParamaters[_key]" (STRING) on Success, "false" otherwise
     */
    public function GetParameter($_key)
    {
        if (empty($_key) || !isset($this->_requestParameters[$_key])) {
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
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_requestParameters;
    }

    /**
     * Set x-amz-meta-* header
     *
     * @author Varun Shoor
     *
     * @param string $_key   The Header Key
     * @param string $_value The Key Value
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetAmazonHeader($_key, $_value)
    {
        if (empty($_key) || empty($_value)) {
            return false;
        }

        $this->_amazonHeaders[$_key] = $_value;

        return true;
    }

    /**
     * Get x-amz-meta-* header
     *
     * @author Varun Shoor
     *
     * @param string $_key The Header Key
     *
     * @return mixed "_amazonHeaders[_key]" (STRING) on Success, "false" otherwise
     */
    public function GetAmazonHeader($_key)
    {
        if (empty($_key) || !isset($this->_amazonHeaders[$_key])) {
            return '';
        }

        return $this->_amazonHeaders[$_key];
    }

    /**
     * Get the x-amz-meta Container Array
     *
     * @author Varun Shoor
     * @return mixed "_amazonHeaders" (ARRAY) on Success, "false" otherwise
     */
    public function GetAmazonHeaders()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_amazonHeaders;
    }

    /**
     * Get the Response Object
     *
     * @author Varun Shoor
     *
     * @param SWIFT_AmazonCloudFront $_SWIFT_AmazonCloudFrontObject The SWIFT_AmazonCloudFront Object Pointer
     *
     * @return mixed "true" on Success, "false" otherwise
     */
    public function GetResponse(SWIFT_AmazonCloudFront $_SWIFT_AmazonCloudFrontObject)
    {
        if (!$this->GetIsClassLoaded() || !$_SWIFT_AmazonCloudFrontObject->GetIsClassLoaded()) {
            return false;
        }

        $_query = '';
        if (sizeof($this->GetParameters()) > 0) {
            $_query = substr($this->GetURI(), -1) !== '?' ? '?' : '&';

            foreach ($this->GetParameters() as $_var => $_value) {
                if ($_value == null || $_value == '') {
                    $_query .= $_var . '&';
                } else {
                    $_query .= $_var . '=' . $_value . '&';
                }
            }

            $_query = substr($_query, 0, -1);
            $this->SetURI($this->GetURI() . $_query);
        }

        $_url = (($_SWIFT_AmazonCloudFrontObject->CanUseSSL() && extension_loaded('openssl')) ? 'https://' : 'http://') . $this->GetHeader('Host') . $this->GetURI();

        // Basic setup
        $_curlHandle = curl_init();
        curl_setopt($_curlHandle, CURLOPT_USERAGENT, 'SWIFT_AmazonCloudFront');
        curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($_curlHandle, CURLOPT_URL, $_url);

        // Headers
        $_requestHeaders = array();
        $_amazonHeaders  = array();
        foreach ($this->GetAmazonHeaders() as $_header => $_value) {
            if (strlen($_value) > 0) {
                $_requestHeaders[] = $_header . ': ' . $_value;

                // For AMZ Signature
                $_amazonHeaders[] = strtolower($_header) . ':' . $_value;
            }
        }

        foreach ($this->GetHeaders() as $_header => $_value) {
            if (strlen($_value) > 0) {
                $_requestHeaders[] = $_header . ': ' . $_value;
            }
        }

        // AMZ headers must be sorted (thanks Malone)
        $_amazonHeaderContainer = '';
        if (sizeof($_amazonHeaders) > 0) {
            sort($_amazonHeaders);
            $_amazonHeaderContainer .= "\n" . implode("\n", $_amazonHeaders);
        }

        // Authorization string
        $_requestHeaders[] = 'Authorization: ' . $_SWIFT_AmazonCloudFrontObject->__GetSignature(
            $this->GetHeader('Date')
        );

        curl_setopt($_curlHandle, CURLOPT_HTTPHEADER, $_requestHeaders);
        curl_setopt($_curlHandle, CURLOPT_HEADER, false);
        curl_setopt($_curlHandle, CURLOPT_NOPROGRESS, false);
        curl_setopt($_curlHandle, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($_curlHandle, CURLOPT_WRITEFUNCTION, array($this, '__ResponseWriteCallback'));
        curl_setopt($_curlHandle, CURLOPT_HEADERFUNCTION, array($this, '__ResponseHeaderCallback'));
        curl_setopt($_curlHandle, CURLOPT_FOLLOWLOCATION, true);

        // Request types
        switch ($this->GetAction()) {
            case self::ACTION_GET:
                break;

            case self::ACTION_PUT:
            case self::ACTION_POST:
                if ($this->GetData() !== false) {
                    curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, $this->GetAction());
                    curl_setopt($_curlHandle, CURLOPT_POSTFIELDS, $this->GetData());

                    if ($this->GetSize() >= 0) {
                        curl_setopt($_curlHandle, CURLOPT_BUFFERSIZE, $this->GetSize());
                    }
                } else {
                    curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, $this->GetAction());
                }

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

        // Execute, grab errors
        if (curl_exec($_curlHandle)) {
            $this->GetResponseObject()->SetHTTPCode(curl_getinfo($_curlHandle, CURLINFO_HTTP_CODE));
        } else {
            $this->GetResponseObject()->Error(curl_errno($_curlHandle), curl_error($_curlHandle), $this->GetResource());
        }

        @curl_close($_curlHandle);

        // Parse body into XML
        if ($this->GetResponseObject()->GetError() === false && $this->GetResponseObject()->GetHeader('type') == 'text/xml' && trim($this->GetResponseObject()->GetBody()) != '') {
            $this->GetResponseObject()->SetBodyObject(simplexml_load_string($this->GetResponseObject()->GetBody()));

            // Grab CloudFront errors
            if (!in_array($this->GetResponseObject()->GetHTTPCode(), array(200, 204)) && isset($this->GetResponseObject()->GetBodyObject()->Code, $this->GetResponseObject()
                ->GetBodyObject()->Message)
            ) {

                $this->GetResponseObject()->Error((string) $this->GetResponseObject()->GetBodyObject()->Code, (string) $this->GetResponseObject()->GetBodyObject()->Message);

                if (isset($this->GetResponseObject()->GetBodyObject()->Resource)) {
                    $this->GetResponseObject()->Error('', '', (string) $this->GetResponseObject()->GetBodyObject()->Resource);
                }
            }
        }

        return $this->GetResponseObject();
    }

    /**
     * CURL write callback
     *
     * @param resource $_curlHandle CURL resource
     * @param string   $_data       Data
     *
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
     * @param string   $_data Data
     *
     * @return integer
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

            if ($_header == 'Last-Modified') {
                $this->GetResponseObject()->SetHeader('time', strtotime($_value));
            } else if ($_header == 'Content-Length') {
                $this->GetResponseObject()->SetHeader('size', (int) ($_value));
            } else if ($_header == 'Content-Type') {
                $this->GetResponseObject()->SetHeader('type', $_value);
            } else if ($_header == 'ETag') {
                $this->GetResponseObject()->SetHeader('hash', $_value);
            } else if (preg_match('/^x-amz-meta-.*$/', $_header)) {
                $this->GetResponseObject()->SetHeader($_header, (is_numeric($_value) ? (int) ($_value) : $_value));
            }
        }

        return $_stringLength;
    }
}
