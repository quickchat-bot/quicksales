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

/**
 * The REST API Management Class
 * 
 * @author Varun Shoor
 */
class SWIFT_RESTManager extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();
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
     * ReGenerate the Authentication Details
     * 
     * @author Varun Shoor
     * @param bool $_rebuildCache Whether to Rebuild the Settings Cache at the end
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReGenerateAuthenticationData($_rebuildCache = true)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        $_apiKey = GenerateUUID();
        $_secretKey = substr(base64_encode(GenerateUUID() . GenerateUUID()), 0, 254);

        $this->Settings->UpdateKey('restapi', 'apikey', $_apiKey, true);
        $this->Settings->UpdateKey('restapi', 'secretkey', $_secretKey, true);

        if ($_rebuildCache)
        {
            SWIFT_Settings::RebuildCache();
        }

        return true;
    }

    /**
     * Authenticate a Signature Token
     * 
     * @author Varun Shoor
     * @param string $_incomingAPIKey The API Key Received
     * @param string $_signature The Signature Received
     * @param string $_signatureToken The Signature Token
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Authenticate($_incomingAPIKey, $_signature, $_signatureToken)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
    
            return false;
        }

        // API Interface Disabled Altogether?
        if ($this->Settings->Get('g_enableapiinterface') != '1')
        {
            return false;
        }

        $_apiKey = $this->Settings->GetKey('restapi', 'apikey');
        $_secretKey = $this->Settings->GetKey('restapi', 'secretkey');

        $_incomingAPIKey = trim($_incomingAPIKey);
        $_signature = trim($_signature);

        // Invalid API Key?
        if ($_apiKey != $_incomingAPIKey || empty($_incomingAPIKey) || trim($_apiKey) == '')
        {
            return false;
        }

        // Invalid Signature?
        if (empty($_signature) || trim($_signature) == '')
        {
            return false;
        }

        $_calculatedSignature = base64_encode(hash_hmac('sha256', $_signatureToken, $_secretKey, true));

        if ($_calculatedSignature != $_signature)
        {
            return false;
        }

        return true;
    }
}
?>