<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Saloni Dhall
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2014, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Base\Library\User;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The User Password Policy Manager
 *
 * @author Saloni Dhall
 */
class SWIFT_UserPasswordPolicy extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Saloni Dhall
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check the Password against current policy
     *
     * @author Saloni Dhall
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Check($_password)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (mb_strlen($_password) < (int)(trim($this->Settings->Get('security_scpminchars')))) {
            return false;
        } elseif (strlen(preg_replace("/[^0-9]/", '', $_password)) < (int)(trim($this->Settings->Get('security_scpminnumbers')))) {
            return false;
        } elseif (strlen(preg_replace("/[^A-Z]/", '', $_password)) < (int)(trim($this->Settings->Get('security_scpmincapitalchars')))) {
            return false;
        } elseif (GetSymbolCount($_password) < (int)(trim($this->Settings->Get('security_scpminsymbols')))) {
            return false;
        }

        return true;
    }

    /**
     * Return the Password Policy string
     *
     * @author Saloni Dhall
     * @return string "_parsedString" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPasswordPolicyString()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_parsedString = sprintf($this->Language->Get('userpasswordpolicy'), $this->Settings->Get('security_scpminchars'), $this->Settings->Get('security_scpminnumbers'), $this->Settings->Get('security_scpminsymbols'), $this->Settings->Get('security_scpmincapitalchars'));

        return $_parsedString;
    }

    /**
     * Create the Password using current UserPasswordPolicy
     *
     * @author Saloni Dhall
     * @return string "$_password" on Success
     */
    public static function GenerateUserPassword()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_password = '';
        $_minSymbols = $_SWIFT->Settings->Get('security_scpminsymbols');
        $_minDigits = $_SWIFT->Settings->Get('security_scpminnumbers');
        $_minCapitalizedLetters = $_SWIFT->Settings->Get('security_scpmincapitalchars');

        // Add numbers.
        $_numbers = '0123456789';
        $_password .= substr(str_shuffle($_numbers), 0, $_minDigits);

        //Add symbols
        $_symbols = '`~!@#$%^&*()-_+={}[]\|;:"?/><.,';
        $_password .= substr(str_shuffle($_symbols), 0, $_minSymbols);

        // Add upper case letters.
        $_upperLetters = 'AEUBDGHJLMNPQRSTVWXYZ';
        $_password .= substr(str_shuffle($_upperLetters), 0, $_minCapitalizedLetters);

        if (mb_strlen($_password) < (int)(trim($_SWIFT->Settings->Get('security_scpminchars')))) {
            $_addMoreCharacters = abs((int)(trim($_SWIFT->Settings->Get('security_scpminchars'))) - mb_strlen($_password));
            $_newPasswordString = substr(BuildHash(), 0, $_addMoreCharacters);
            return ($_password . $_newPasswordString);
        } elseif (mb_strlen($_password) > (int)(trim($_SWIFT->Settings->Get('security_scpminchars')))) {
            return $_password;
        }

        return $_password;
    }
}

?>
