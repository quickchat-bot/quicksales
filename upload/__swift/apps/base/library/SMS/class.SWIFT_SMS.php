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

namespace Base\Library\SMS;

use SWIFT_Model;
use Base\Library\SMS\SWIFT_SMS_Exception;

/**
 * The SMS Alert Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_SMS extends SWIFT_Model
{
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
     * Send SMS Alert using QuickSupport Gateway
     *
     * @author Varun Shoor
     * @param string $_phoneNumber The Phone Number to Send Message To
     * @param string $_message The Message to Send
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_SMS_Exception If the Class is not Loaded
     */
    public function Send($_phoneNumber, $_message)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_SMS_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Settings->Get('gateway_enable') != 1) {
            return false;
        }

        // TODO: Update to v4 Specifications
//        $_returnData = @file_get_contents("http://master.kayako.net/gateway.php?username=".urlencode($this->Settings->Get('gateway_username'))."&password=".urlencode($this->Settings->Get('gateway_password')) ."&message=".urlencode($_message) ."&number=".urlencode($_phoneNumber)));
        $_returnData = '0';

        if (trim($_returnData) == '0') {
            return true;
        }

        return false;
    }
}

?>
