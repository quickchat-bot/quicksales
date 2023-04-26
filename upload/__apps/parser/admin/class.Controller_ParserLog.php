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
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Controller_admin;
use Parser\Library\MailParser\SWIFT_MailParser;
use SWIFT;
use SWIFT_Exception;
use Parser\Models\Log\SWIFT_ParserLog;
use SWIFT_Session;

/**
 * The Parser Log Controller
 *
 * @author Varun Shoor
 *
 * @property SWIFT_MailParser $MailParser
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_ParserLog $View
 */
class Controller_ParserLog extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 4;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('emailparser');
    }

    /**
     * Delete the Parser Log from Mass Action
     *
     * @author Varun Shoor
     *
     * @param mixed $_parserLogIDList The Parser Log ID List Container Array
     * @param bool  $_byPassCSRF      Whether to bypass the CSRF check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_parserLogIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_mpcandeleteparserlogs') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_parserLogIDList)) {
            $_SWIFT->Database->Query("SELECT fromemail, subject FROM " . TABLE_PREFIX . "parserlogs WHERE parserlogid IN (" .
                BuildIN($_parserLogIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activityparserlogdelete'),
                    htmlspecialchars($_SWIFT->Database->Record['subject']), htmlspecialchars($_SWIFT->Database->Record['fromemail'])),
                    SWIFT_StaffActivityLog::ACTION_DELETE, SWIFT_StaffActivityLog::SECTION_PARSER, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            SWIFT_ParserLog::DeleteList($_parserLogIDList);
        }

        return true;
    }

    /**
     * Delete the given Parser Log ID
     *
     * @author Varun Shoor
     *
     * @param int $_parserLogID The Parser Log ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_parserLogID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_parserLogID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Parser Log Grid
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('mailparser') . ' > ' . $this->Language->Get('parserlog'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_mpcanviewparserlogs') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * View a Parser Log Entry
     *
     * @author Varun Shoor
     *
     * @param int $_parserLogID The Parser Log ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function View($_parserLogID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_parserLogID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ParserLogObject = new SWIFT_ParserLog($_parserLogID);
        if (!$_ParserLogObject instanceof SWIFT_ParserLog || !$_ParserLogObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // will not be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        // @codeCoverageIgnoreEnd

        $this->View->Render($_ParserLogObject);

        return true;
    }

    /**
     * Reprocess the Email
     *
     * @author Varun Shoor
     * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
     *
     * @param int $_parserLogID The Parser Log ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ReParse($_parserLogID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_parserLogID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ParserLogObject = new SWIFT_ParserLog($_parserLogID);
        if (!$_ParserLogObject instanceof SWIFT_ParserLog || !$_ParserLogObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // will not be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        // @codeCoverageIgnoreEnd

        $this->Load->Library('MailParser:MailParser', array($_ParserLogObject->GetProperty('contents')), true, false, APP_PARSER);
        $this->MailParser->SetForceReprocessing(true);
        $this->MailParser->Process();

        $this->Load->Manage();

        return true;
    }
}

?>
