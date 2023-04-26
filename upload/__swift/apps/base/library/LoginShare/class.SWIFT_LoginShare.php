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

namespace Base\Library\LoginShare;

use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Base LoginShare Class
 *
 * @author Varun Shoor
 */
abstract class SWIFT_LoginShare extends SWIFT_Library
{
    protected $_responseBody = '';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Dispatches the POST request
     *
     * @author Varun Shoor
     * @param string $_url The URL to Dispatch Request to
     * @param array $_variableContainer The Variable Container
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DispatchPOST($_url, $_variableContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_responseBody = '';

        $_postData = '';
        $_postDataArray = array();
        foreach ($_variableContainer as $_key => $_val) {
            $_postDataArray[] = $_key . '=' . urlencode($_val);
        }

        $_postData = implode('&', $_postDataArray);

        $_requestHeaders = array();
        $_requestHeaders[] = 'Content-Type: application/x-www-form-urlencoded';

        $_curlHandle = curl_init();
        curl_setopt($_curlHandle, CURLOPT_USERAGENT, 'SWIFT_LoginShare');
        curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($_curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($_curlHandle, CURLOPT_URL, $_url);
        curl_setopt($_curlHandle, CURLOPT_TIMEOUT, 100);

        curl_setopt($_curlHandle, CURLOPT_HEADER, false);
        curl_setopt($_curlHandle, CURLOPT_NOPROGRESS, true);
        curl_setopt($_curlHandle, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($_curlHandle, CURLOPT_WRITEFUNCTION, array($this, '__ResponseWriteCallback'));
        curl_setopt($_curlHandle, CURLOPT_HEADERFUNCTION, array($this, '__ResponseHeaderCallback'));

        // SWIFT-594
        if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off') {
            curl_setopt($_curlHandle, CURLOPT_FOLLOWLOCATION, true);
        }

        curl_setopt($_curlHandle, CURLOPT_POST, 1);
        curl_setopt($_curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($_curlHandle, CURLOPT_POSTFIELDS, $_postData);

        curl_setopt($_curlHandle, CURLOPT_HTTPHEADER, $_requestHeaders);

        curl_exec($_curlHandle);
        curl_close($_curlHandle);

        return $this->_responseBody;
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
        $this->_responseBody .= $_data;

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
        if (($_stringLength = strlen($_data)) <= 2) {
            return $_stringLength;
        }

        return $_stringLength;
    }
}

?>
