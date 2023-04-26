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
 * The S3 Request Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_AmazonS3Request extends SWIFT_Library
{
    private $_actionType = '';
    private $_bucketName = '';
    private $_uri = '';
    private $_resource = '';
    private $_requestParameters = array();
    private $_amazonHeaders = array();
    private $_requestHeaders = array('Host' => '', 'Date' => '', 'Content-MD5' => '', 'Content-Type' => '');
    private $_size = 0;
    private $_data = false;
    private $_filePointer = false;
    private $_SWIFT_AmazonS3ResponseObject;

    // Core Constants
    const ACTION_GET = 'GET';
    const ACTION_PUT = 'PUT';
    const ACTION_DELETE = 'DELETE';
    const ACTION_HEAD = 'HEAD';

    /**
     * Constructor
     *
     * @param string $_actionType The S3 Request Action Type
     * @param string $_bucketName The Bucket Name
     * @param string $_uri The Object URIe
     */
    function __construct($_actionType, $_bucketName = '', $_uri = '') {
        parent::__construct();

        $_finalURI = $_uri !== '' ? '/'.$_uri : '/';

        if (!$this->SetAction($_actionType) || !$this->SetBucket($_bucketName) || !$this->SetURI($_finalURI))
        {
            $this->SetIsClassLoaded(false);

            return;
        }

        if ($_bucketName !== '')
        {
            $_bucketContainer = explode('/', $_bucketName);
            $this->SetResource('/' . $_bucketContainer[0] . $this->GetURI());
            $this->SetHeader('Host', $_bucketContainer[0] . '.' . SWIFT_AmazonS3::BASE_URL);
        } else {
            $this->SetHeader('Host', SWIFT_AmazonS3::BASE_URL);

            if (strlen($this->GetURI()) > 1)
            {
                $this->SetResource('/' . $this->GetBucket() . $this->GetURI());
            } else {
                $this->SetResource($this->GetURI());
            }
        }

        $this->SetHeader('Date', gmdate('D, d M Y H:i:s T'));

        $this->SetResponseObject(new SWIFT_AmazonS3Response());
    }

    /**
     * Set the Request Data
     *
     * @author Varun Shoor
     * @param string $_data The Request Data
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetData($_data)
    {
        if (empty($_data))
        {
            return false;
        }

        $this->_data = $_data;

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
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_data;
    }

    /**
     * Set the File Poitner
     *
     * @author Varun Shoor
     * @param mixed $_filePointer The File Pointer Resource
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetFilePointer($_filePointer)
    {
        if (!is_resource($_filePointer))
        {
            return false;
        }

        $this->_filePointer = $_filePointer;

        return true;
    }

    /**
     * Retrieve the currently set file pointer
     *
     * @author Varun Shoor
     * @return mixed "_filePointer" (RESOURCE) on Success, "false" otherwise
     */
    public function GetFilePointer()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_filePointer;
    }

    /**
     * Retrieve the Size
     *
     * @author Varun Shoor
     * @return mixed "_size" (INT) on Success, "false" otherwise
     */
    public function GetSize()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_size;
    }

    /**
     * Set the Size
     *
     * @author Varun Shoor
     * @param mixed $_size The Size
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
     * @param string $_actionType The S3 Request Action Type
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
     * @param string $_actionType The S3 Request Action Type
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetAction($_actionType)
    {
        if (!self::IsValidAction($_actionType))
        {
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
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_actionType;
    }

    /**
     * Set the Bucket
     *
     * @author Varun Shoor
     * @param string $_bucketName The Bucket Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetBucket($_bucketName)
    {
        $_bucketName = strtolower($_bucketName);

        $this->_bucketName = $_bucketName;

        return true;
    }

    /**
     * Retrieve the currently set bucket
     *
     * @author Varun Shoor
     * @return mixed "_bucketName" (STRING) on Success, "false" otherwise
     */
    public function GetBucket()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_bucketName;
    }

    /**
     * Set the URI
     *
     * @author Varun Shoor
     * @param string $_uri The URI
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetURI($_uri)
    {
        $_uri !== '' ? '/'.$_uri : '/';

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
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_uri;
    }

    /**
     * Set the S3 Resrouce
     *
     * @author Varun Shoor
     * @param string $_resource The S3 Resource
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetResource($_resource)
    {
        if (empty($_resource))
        {
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
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_resource;
    }

    /**
     * Set the Response Object
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonS3Response $_SWIFT_AmazonS3ResponseObject The SWIFT_AmazonS3Response Object Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetResponseObject(SWIFT_AmazonS3Response $_SWIFT_AmazonS3ResponseObject)
    {
        if (!$_SWIFT_AmazonS3ResponseObject->GetIsClassLoaded())
        {
            return false;
        }

        $this->_SWIFT_AmazonS3ResponseObject = $_SWIFT_AmazonS3ResponseObject;

        return true;
    }

    /**
     * Retrieve the currently set response object
     *
     * @author Varun Shoor
     * @return mixed "_SWIFT_AmazonS3ResponseObject" (SWIFT_AmazonS3Response Object) on Success, "false" otherwise
     */
    public function GetResponseObject()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_SWIFT_AmazonS3ResponseObject;
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
     * Set x-amz-meta-* header
     *
     * @author Varun Shoor
     * @param string $_key The Header Key
     * @param string $_value The Key Value
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetAmazonHeader($_key, $_value)
    {
        if (empty($_key) || empty($_value))
        {
            return false;
        }

        $this->_amazonHeaders[$_key] = $_value;

        return true;
    }

    /**
     * Get x-amz-meta-* header
     *
     * @author Varun Shoor
     * @param string $_key The Header Key
     * @return mixed "_amazonHeaders[_key]" (STRING) on Success, "false" otherwise
     */
    public function GetAmazonHeader($_key)
    {
        if (empty($_key) || !isset($this->_amazonHeaders[$_key]))
        {
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
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_amazonHeaders;
    }

    /**
     * Get the Response Object
     *
     * @author Varun Shoor
     * @param SWIFT_AmazonS3 $_SWIFT_AmazonS3Object The SWIFT_AmazonS3 Object Pointer
     * @return bool|SWIFT_AmazonS3Response
     */
    public function GetResponse(SWIFT_AmazonS3 $_SWIFT_AmazonS3Object)
    {
        if (!$this->GetIsClassLoaded() || !$_SWIFT_AmazonS3Object->GetIsClassLoaded())
        {
            return false;
        }

        $_query = '';
        if (sizeof($this->GetParameters()) > 0)
        {
            $_query = substr($this->GetURI(), -1) !== '?' ? '?' : '&';

            foreach ($this->GetParameters() as $_var => $_value)
            {
                if ($_value == null || $_value == '')
                {
                    $_query .= $_var . '&';
                } else {
                    $_query .= $_var . '=' . $_value . '&';
                }
            }

            $_query = substr($_query, 0, -1);
            $this->SetURI($this->GetURI() . $_query);

            if (array_key_exists('acl', $this->GetParameters()) || array_key_exists('location', $this->GetParameters()) || array_key_exists('torrent', $this->GetParameters()) || array_key_exists('lifecycle', $this->GetParameters()) || array_key_exists('logging', $this->GetParameters()))
            {
                $this->SetResource($this->GetResource() . $_query);
            }
        }

        $_url = (($_SWIFT_AmazonS3Object->CanUseSSL() && extension_loaded('openssl')) ? 'https://':'http://') . $this->GetHeader('Host') . $this->GetURI();

        // Basic setup
        $_curlHandle = curl_init();
        curl_setopt($_curlHandle, CURLOPT_USERAGENT, 'SWIFT_AmazonS3');
        curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($_curlHandle, CURLOPT_URL, $_url);

        // Headers
        $_requestHeaders = array(); $_amazonHeaders = array();
        foreach ($this->GetAmazonHeaders() as $_header => $_value)
        {
            if (strlen($_value) > 0)
            {
                $_requestHeaders[] = $_header . ': ' . $_value;

                // For AMZ Signature
                $_amazonHeaders[] = strtolower($_header) . ':' . $_value;
            }
        }

        foreach ($this->GetHeaders() as $_header => $_value)
        {
            if (strlen($_value) > 0)
            {
                $_requestHeaders[] = $_header . ': ' . $_value;
            }
        }

        // AMZ headers must be sorted (thanks Malone)
        $_amazonHeaderContainer = '';
        if (sizeof($_amazonHeaders) > 0)
        {
            sort($_amazonHeaders);
            $_amazonHeaderContainer .= "\n".implode("\n", $_amazonHeaders);
        }

//        print_r($_requestHeaders);
//        echo $_url;

        // Authorization string
        $_requestHeaders[] = 'Authorization: ' . $_SWIFT_AmazonS3Object->__GetSignature(
            $this->GetAction() . "\n" .
            $this->GetHeader('Content-MD5') . "\n" .
            $this->GetHeader('Content-Type') . "\n" .
            $this->GetHeader('Date') . $_amazonHeaderContainer . "\n" . $this->GetResource()
        );

        $_authInfo = SWIFT_CRLF . $this->GetAction() . "\n" .
            $this->GetHeader('Content-MD5') . "\n" .
            $this->GetHeader('Content-Type') . "\n" .
            $this->GetHeader('Date') . $_amazonHeaderContainer . "\n" . $this->GetResource();

        curl_setopt($_curlHandle, CURLOPT_HTTPHEADER, $_requestHeaders);
        curl_setopt($_curlHandle, CURLOPT_HEADER, false);
        curl_setopt($_curlHandle, CURLOPT_NOPROGRESS, false);
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
                if ($this->GetFilePointer() !== false)
                {
                    curl_setopt($_curlHandle, CURLOPT_PUT, true);
                    curl_setopt($_curlHandle, CURLOPT_INFILE, $this->GetFilePointer());

                    if ($this->GetSize() >= 0) {
                        curl_setopt($_curlHandle, CURLOPT_INFILESIZE, $this->GetSize());
                    }

                } else if ($this->GetData() !== false) {
                    curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, self::ACTION_PUT);
                    curl_setopt($_curlHandle, CURLOPT_POSTFIELDS, $this->GetData());

                    if ($this->GetSize() >= 0)
                    {
                        curl_setopt($_curlHandle, CURLOPT_BUFFERSIZE, $this->GetSize());
                    }

                } else {
                    curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, self::ACTION_PUT);
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
        if (curl_exec($_curlHandle))
        {
            $this->GetResponseObject()->SetHTTPCode(curl_getinfo($_curlHandle, CURLINFO_HTTP_CODE));
        } else {
            $this->GetResponseObject()->Error(curl_errno($_curlHandle), curl_error($_curlHandle), $this->GetResource());
        }

        @curl_close($_curlHandle);

        // Parse body into XML
        if ($this->GetResponseObject()->GetError() === false && $this->GetResponseObject()->GetHeader('type') == 'application/xml' && trim($this->GetResponseObject()->GetBody()) != '')
        {
            $this->GetResponseObject()->SetBodyObject(simplexml_load_string($this->GetResponseObject()->GetBody()));

            // Grab S3 errors
            if (!in_array($this->GetResponseObject()->GetHTTPCode(), array(200, 204)) && isset($this->GetResponseObject()->GetBodyObject()->Code, $this->GetResponseObject()->GetBodyObject()->Message)) {

                $_errorMessage = (string)$this->GetResponseObject()->GetBodyObject()->Message;
                $_errorMessageExtended = SWIFT_CRLF . implode(SWIFT_CRLF, $_requestHeaders) . SWIFT_CRLF. $_authInfo;
                $this->GetResponseObject()->Error((string)$this->GetResponseObject()->GetBodyObject()->Code, $_errorMessage . $_errorMessageExtended);

                if (isset($this->GetResponseObject()->GetBodyObject()->Resource)) {
                    $this->GetResponseObject()->Error('', '', (string)$this->GetResponseObject()->GetBodyObject()->Resource . $_errorMessageExtended);
                }
            }
        }

        // Clean up file resources
        if ($this->GetFilePointer() !== false && is_resource($this->GetFilePointer()))
        {
            fclose($this->GetFilePointer());
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
        if ($this->GetResponseObject()->GetHTTPCode() == 200 && $this->GetFilePointer() !== false)
        {
            return fwrite($this->GetFilePointer(), $_data);
        } else {
            $this->GetResponseObject()->AppendBody($_data);
        }

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
