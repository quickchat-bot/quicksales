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

namespace Base\Client;

use Base\Models\User\SWIFT_User;
use Controller_client;
use SWIFT;
use SWIFT_Exception;

/**
 * The Language Switch Controller
 *
 * @author Varun Shoor
 */
class Controller_Language extends Controller_client
{
    /**
     * Switch the Language
     *
     * @author Varun Shoor
     * @param int $_languageID The Language ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Change($_languageID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_languageCache = $this->Cache->Get('languagecache');
        if (!isset($_languageCache[$_languageID])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Cookie->Parse('client');
        $_SWIFT->Cookie->AddVariable('client', 'languageid', (string)$_languageID);
        $_SWIFT->Cookie->Rebuild('client', true);

        if ($_SWIFT->User instanceof SWIFT_User && $_SWIFT->User->GetIsClassLoaded()) {
            $_SWIFT->User->UpdateLanguage($_languageID);
        }

        header('location: ' . $_SERVER['HTTP_REFERER']);

        return true;
    }
}

?>
