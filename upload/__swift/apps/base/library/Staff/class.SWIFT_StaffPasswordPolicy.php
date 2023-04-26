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

namespace Base\Library\Staff;

use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Staff Password Policy Manager
 *
 * @author Varun Shoor
 */
class SWIFT_StaffPasswordPolicy extends SWIFT_Library
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
     * Check the Password against current policy
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Check($_password)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (mb_strlen($_password) < (int)(trim($this->Settings->Get('security_sppminchars')))) {
            return false;
        } elseif (strlen(preg_replace("/[^0-9]/", '', $_password)) < (int)(trim($this->Settings->Get('security_sppminnumbers')))) {
            return false;
        } elseif (strlen(preg_replace("/[^A-Z]/", '', $_password)) < (int)(trim($this->Settings->Get('security_sppmincapitalchars')))) {
            return false;
        } elseif (GetSymbolCount($_password) < (int)(trim($this->Settings->Get('security_sppminsymbols')))) {
            return false;
        }

        return true;
    }

    /**
     * Return the Password Policy string
     *
     * @author Varun Shoor
     * @return string "_parsedString" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPasswordPolicyString()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_parsedString = sprintf($this->Language->Get('staffpasswordpolicy'), $this->Settings->Get('security_sppminchars'), $this->Settings->Get('security_sppminnumbers'), $this->Settings->Get('security_sppminsymbols'), $this->Settings->Get('security_sppmincapitalchars'));

        return $_parsedString;
    }
}

?>
