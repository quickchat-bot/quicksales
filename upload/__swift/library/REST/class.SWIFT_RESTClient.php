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
 * The REST Client Class (For Interacting with SWIFT based API Interfaces Only)
 *
 * @author Varun Shoor
 */
class SWIFT_RESTClient extends SWIFT_RESTBase
{
    protected $_apiKey = false;
    protected $_secretKey = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_baseURL The Base URL
     * @param string $_apiKey The API Key
     * @param string $_secretKey The Secret Keye
     */
    public function __construct($_baseURL, $_apiKey, $_secretKey)
    {
        parent::__construct($_baseURL);

        $this->SetBaseURL($_baseURL);
        $this->SetAPIKey($_apiKey);
        $this->SetSecretKey($_secretKey);
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
     * Set the API Key
     *
     * @author Varun Shoor
     * @param string $_apiKey The API Key
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetAPIKey($_apiKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_apiKey)) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        $this->_apiKey = $_apiKey;

        return true;
    }

    /**
     * Retrieve the currently set API Key
     *
     * @author Varun Shoor
     * @return mixed "_apiKey" (STRING) on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function GetAPIKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_apiKey;
    }

    /**
     * Set the Secret Key
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function SetSecretKey($_secretKey)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_secretKey)) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        $this->_secretKey = $_secretKey;

        return true;
    }

    /**
     * Retrieve the currently set secret key
     *
     * @author Varun Shoor
     * @return mixed "_secretKey" (STRING) on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    public function GetSecretKey()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_secretKey;
    }

    /**
     * Retrieve the calculated signature
     *
     * @author Varun Shoor
     * @param mixed $_method The Call Method
     * @param string $_controllerPath The Controller Path
     * @param string $_salt The Salt
     * @return mixed "_calculatedSignature" (STRING) on Success, "false" otherwise
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    protected function GetSignature($_method, $_controllerPath, $_salt)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!self::IsValidMethod($_method)) {
            throw new SWIFT_REST_Exception(SWIFT_INVALIDDATA);
        }

        $_signatureToken = $_salt;

        $_calculatedSignature = base64_encode(hash_hmac('sha256', $_signatureToken, $this->GetSecretKey(), true));

        return $_calculatedSignature;
    }

    /**
     * Retrieve the CURL signature based on the parameters
     *
     * @author Varun Shoor
     * @param mixed $_method The Call Method
     * @param string $_controllerPath The Controller Path
     * @param string $_salt The Salt
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetCURLSignature($_method, $_controllerPath, $_salt)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_signature = $this->GetSignature($_method, $_controllerPath, $_salt);

        return $_signature;
    }

    /**
     * Initialize the CURL Object
     *
     * @author Varun Shoor
     * @param mixed $_method The Call Method
     * @param string $_controllerPath The Controller Path
     * @param array $_queryStringContainer The Function Arguments
     * @return SWIFT_RESTBase
     * @throws SWIFT_REST_Exception If the Class is not Loaded
     */
    protected function InitializeCURL($_method, $_controllerPath, $_queryStringContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_REST_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_queryStringContainer['apikey'] = $this->GetAPIKey();

        return parent::InitializeCURL($_method, $_controllerPath, $_queryStringContainer);
    }
}
?>
