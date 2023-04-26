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
 * The Javascript Management Library
 *
 * @author Varun Shoor
 */
class SWIFT_JavaScript extends SWIFT_Library
{

    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();

        $this->ProcessPayload();
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
     * Processes the Payload to send at the start of each request
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessPayload()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_jsRouterPath = SWIFT::Get('JSRouterPath');

        // Create the core properties
        $_corePropertyContainer = array(
            'basename' => SWIFT::Get('basename'),
            'interfacepath' => SWIFT::Get('interfacepath'),
            'swiftpath' => SWIFT::Get('swiftpath'),
            'ip' => SWIFT::Get('ip'),
            'themepath' => SWIFT::Get('themepath'),
            'themepathinterface' => SWIFT::Get('themepathinterface'),
            'themepathglobal' => SWIFT::Get('themepathglobal'),
            'version' => SWIFT_VERSION,
            'product' => SWIFT_PRODUCT,
            'activestaffcount' => SWIFT::Get('activestaffcount'),
        );

        // Prepare the init payload, this is sent with a 'fresh' page request
        $_initPayload = "SWIFT.Setup('" . $_jsRouterPath . "', " . json_encode($_corePropertyContainer) . ");";

        $this->Template->Assign('_jsInitPayload', $_initPayload);
        SWIFT::Set('jsinitpayload', $_initPayload);

        return true;
    }
}
?>