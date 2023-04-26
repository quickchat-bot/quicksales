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
 * The CloudFront Response Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_AmazonCloudFrontResponse extends SWIFT_Library
{
    private $_errorContainer = false;
    private $_httpCode = false;
    private $_httpBody = '';
    private $_bodyXMLObject = false;
    private $_requestHeaders = array();

    // Core Constants
    const ERROR_CODE     = 'code';
    const ERROR_MESSAGE  = 'message';
    const ERROR_RESOURCE = 'resource';

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
     * Set the HTTP Response Code
     *
     * @author Varun Shoor
     *
     * @param int $_httpCode The HTTP Response Code
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetHTTPCode($_httpCode)
    {
        if (empty($_httpCode)) {
            return false;
        }

        $this->_httpCode = $_httpCode;

        return true;
    }

    /**
     * Retrieve the Currently Set Response Code
     *
     * @author Varun Shoor
     * @return mixed "_httpCode" (INT) on Success, "false" otherwise
     */
    public function GetHTTPCode()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_httpCode;
    }

    /**
     * Set the Response Body XML Object
     *
     * @author Varun Shoor
     *
     * @param object $_bodyXMLObject The HTTP SimpleXML Body Object
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetBodyObject($_bodyXMLObject)
    {
        $this->_bodyXMLObject = $_bodyXMLObject;

        return true;
    }

    /**
     * Get the currently set response body object
     *
     * @author Varun Shoor
     * @return mixed "_bodyXMLObject" (OBJECT) on Success, "false" otherwise
     */
    public function GetBodyObject()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_bodyXMLObject;
    }

    /**
     * Set the Response Body
     *
     * @author Varun Shoor
     *
     * @param string $_httpBody The HTTP Response Body
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetBody($_httpBody)
    {
        $this->_httpBody = $_httpBody;

        return true;
    }

    /**
     * Append the Response Body
     *
     * @author Varun Shoor
     *
     * @param string $_httpBody The HTTP Response Body
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function AppendBody($_httpBody)
    {
        $this->_httpBody .= $_httpBody;

        return true;
    }

    /**
     * Get the currently set response body
     *
     * @author Varun Shoor
     * @return mixed "_httpBody" (STRING) on Success, "false" otherwise
     */
    public function GetBody()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_httpBody;
    }

    /**
     * Set the Error
     *
     * @author Varun Shoor
     *
     * @param int    $_errorCode     The Error Code
     * @param string $_errorMessage  The Error Message
     * @param string $_errorResource The Error Resource
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public function Error($_errorCode, $_errorMessage, $_errorResource = '')
    {
        $this->_errorContainer = array(self::ERROR_CODE => $_errorCode, self::ERROR_MESSAGE => $_errorMessage, self::ERROR_RESOURCE => $_errorResource);

        return true;
    }

    /**
     * Retrieve the Error Container
     *
     * @author Varun Shoor
     * @return mixed "_errorContainer" (ARRAY) on Success, "false" otherwise
     */
    public function GetError()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_errorContainer;
    }
}
