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

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Models\User\SWIFT_UserEmail;
use Controller_admin;
use SWIFT;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use Parser\Models\EmailQueue\SWIFT_EmailQueueMailbox;
use Parser\Models\EmailQueue\SWIFT_EmailQueuePipe;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType_Backend;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType_News;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType_Tickets;
use Parser\Library\MailParser\SWIFT_MailParserIMAP;
use SWIFT_Cryptor;
use SWIFT_Exception;
use SWIFT_Loader;
use SWIFT_Session;
use SWIFT_OAuth;

/**
 * The Email Queue Controller
 *
 * @property SWIFT_Cryptor $Cryptor
 * @property SWIFT_Loader $Load
 * @property View_EmailQueue $View
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class Controller_EmailQueue extends Controller_admin
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
     * Delete the Email Queues from Mass Action
     *
     * @author Varun Shoor
     *
     * @param mixed $_emailQueueIDList The Email Queue ID List Container Array
     * @param bool  $_byPassCSRF       Whether to bypass the CSRF check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_emailQueueIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_mpcandeletequeue') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_emailQueueIDList)) {
            $_SWIFT->Database->Query("SELECT email FROM " . TABLE_PREFIX . "emailqueues WHERE emailqueueid IN (" . BuildIN($_emailQueueIDList) .
                ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(
                    sprintf(
                        $_SWIFT->Language->Get('activitydeletemailqueue'),
                        htmlspecialchars(StripName($_SWIFT->Database->Record['email'], 100))
                    ),
                    SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_PARSER,
                    SWIFT_StaffActivityLog::INTERFACE_ADMIN
                );
            }

            SWIFT_EmailQueue::DeleteList($_emailQueueIDList);
        }

        return true;
    }

    /**
     * Enable the Email Queues from Mass Action
     *
     * @author Varun Shoor
     *
     * @param mixed $_emailQueueIDList The Email Queue ID List Container Array
     * @param bool  $_byPassCSRF       Whether to bypass the CSRF check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function EnableList($_emailQueueIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_mpcanupdatequeue') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_emailQueueIDList)) {
            $_SWIFT->Database->Query("SELECT email FROM " . TABLE_PREFIX . "emailqueues WHERE emailqueueid IN (" . BuildIN($_emailQueueIDList) .
                ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(
                    sprintf(
                        $_SWIFT->Language->Get('activityenableemailqueue'),
                        htmlspecialchars(StripName($_SWIFT->Database->Record['email'], 100))
                    ),
                    SWIFT_StaffActivityLog::ACTION_UPDATE,
                    SWIFT_StaffActivityLog::SECTION_PARSER,
                    SWIFT_StaffActivityLog::INTERFACE_ADMIN
                );
            }

            SWIFT_EmailQueue::EnableList($_emailQueueIDList);
        }

        return true;
    }

    /**
     * Disable the Email Queues from Mass Action
     *
     * @author Varun Shoor
     *
     * @param mixed $_emailQueueIDList The Email Queue ID List Container Array
     * @param bool  $_byPassCSRF       Whether to bypass the CSRF check
     *
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DisableList($_emailQueueIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('admin_mpcanupdatequeue') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_emailQueueIDList)) {
            $_SWIFT->Database->Query("SELECT email FROM " . TABLE_PREFIX . "emailqueues WHERE emailqueueid IN (" . BuildIN($_emailQueueIDList) .
                ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(
                    sprintf(
                        $_SWIFT->Language->Get('activitydisableemailqueue'),
                        htmlspecialchars(StripName($_SWIFT->Database->Record['email'], 100))
                    ),
                    SWIFT_StaffActivityLog::ACTION_UPDATE,
                    SWIFT_StaffActivityLog::SECTION_PARSER,
                    SWIFT_StaffActivityLog::INTERFACE_ADMIN
                );
            }

            SWIFT_EmailQueue::DisableList($_emailQueueIDList);
        }

        return true;
    }

    /**
     * Delete the Given Email Queue ID
     *
     * @author Varun Shoor
     *
     * @param int $_emailQueueID The Email Queue ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_emailQueueID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_emailQueueID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Parser Queue Grid
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

        $this->UserInterface->Header(
            $this->Language->Get('mailparser') . ' > ' . $this->Language->Get('emailqueues'),
            self::MENU_ID,
            self::NAVIGATION_ID
        );

        if ($_SWIFT->Staff->GetPermission('admin_mpcanviewqueues') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid();
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Runs the Checks for Insertion/Editing
     *
     * @author Varun Shoor
     *
     * @param int  $_mode           The User Interface Mode
     * @param bool $_extendedChecks (OPTIONAL) Whether to Run Extended Checks
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode, $_extendedChecks = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        $_staffCache = $this->Cache->Get('staffcache');

        if (isset($_POST['port'])) {
            $_POST['port'] = (int)($_POST['port']);
        }
        /**
         * BUG FIX : Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-5195 : Better handling of email address for a user account
         *
         * Comments : Prevent email queue to be added if it is associated with any customer.
         **/
        $_userEmailList = SWIFT_UserEmail::RetrieveEmailofAllUsers();

        if (in_array($_POST['email'], $_userEmailList)) {
            $this->UserInterface->CheckFields('email');

            SWIFT::ErrorField('email');

            $this->UserInterface->Error($this->Language->Get('titleinvalidemail'), $this->Language->Get('msginvalidemail'));

            return false;
        } else if (trim($_POST['email']) == '' || !IsEmailValid($_POST['email'])) {
            $this->UserInterface->CheckFields('email');

            SWIFT::ErrorField('email');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if ($_POST['customfromname'] != '' && trim($this->Input->SanitizeForXSS($_POST['customfromname'])) == '') {
            SWIFT::ErrorField('customfromname');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (trim($_POST['customfromemail']) != '' && !IsEmailValid($_POST['customfromemail'])) {
            SWIFT::ErrorField('customfromemail');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if ($_POST['fetchtype'] != SWIFT_EmailQueue::FETCH_PIPE && $_POST['authtype'] == 'basic' && (!isset($_POST['host']) || !isset($_POST['port']) ||
            !isset($_POST['username']) || trim($_POST['host']) == '' || empty(trim($_POST['port'])) || trim($_POST['username']) == '')) {
            $this->UserInterface->CheckFields('host', 'port', 'username');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if ($_POST['fetchtype'] != SWIFT_EmailQueue::FETCH_PIPE && $_POST['authtype'] == 'oauth' && (!isset($_POST['host']) || !isset($_POST['port']) ||
            !isset($_POST['authclientid']) || trim($_POST['host']) == '' || empty(trim($_POST['port'])) || trim($_POST['authclientid']) == '')) {
            $this->UserInterface->CheckFields('host', 'port', 'authclientid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        } else if (!empty($_POST['prefix']) && !SWIFT_EmailQueue::IsValidQueuePrefix($_POST['prefix'])) {
            $this->UserInterface->Error($this->Language->Get('titleinvalidqueueprefix'), $this->Language->Get('msginvalidqueueprefix'));

            return false;
        } else if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if (($_mode == SWIFT_UserInterface::MODE_INSERT && $_SWIFT->Staff->GetPermission('admin_mpcaninsertqueue') == '0') ||
            ($_mode == SWIFT_UserInterface::MODE_EDIT && $_SWIFT->Staff->GetPermission('admin_mpcanupdatequeue') == '0')
        ) {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        if (!isset($_templateGroupCache[$_POST['templategroupid']])) {
            SWIFT::ErrorField('templategroupid');

            $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            return false;
        }

        foreach ($_staffCache as $_key => $_val) {
            if ($_val['email'] == $_POST['email']) {
                SWIFT::ErrorField('email');

                $this->UserInterface->Error($this->Language->Get('titlestaffemail'), sprintf(
                    $this->Language->Get('msgstaffemail'),
                    text_to_html_entities($_val['fullname'])
                ));

                return false;
            }
        }

        if ($_extendedChecks == true) {
            $this->Cache->Queue('departmentcache', 'statuscache', 'prioritycache', 'tickettypecache');
            $this->Cache->LoadQueue();

            $_departmentCache = $this->Cache->Get('departmentcache');
            $_statusCache = $this->Cache->Get('statuscache');
            $_priorityCache = $this->Cache->Get('prioritycache');
            $_ticketTypeCache = $this->Cache->Get('tickettypecache');

            if ($_POST['type'] == SWIFT_EmailQueueType::TYPE_TICKETS) {
                if (!isset($_POST['departmentid']) || !isset($_departmentCache[$_POST['departmentid']])) {
                    SWIFT::ErrorField('departmentid');

                    $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                    return false;
                } else if (!isset($_POST['tickettypeid']) || !isset($_ticketTypeCache[$_POST['tickettypeid']])) {
                    SWIFT::ErrorField('tickettypeid');

                    $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                    return false;
                } else if (!isset($_POST['ticketstatusid']) || !isset($_statusCache[$_POST['ticketstatusid']])) {
                    SWIFT::ErrorField('ticketstatusid');

                    $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                    return false;
                } else if (!isset($_POST['ticketpriorityid']) || !isset($_priorityCache[$_POST['ticketpriorityid']])) {
                    SWIFT::ErrorField('ticketpriorityid');

                    $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Insert a new Email Queue
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Insert()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header(
            $this->Language->Get('mailparser') . ' > ' . $this->Language->Get('insertemailqueue'),
            self::MENU_ID,
            self::NAVIGATION_ID
        );

        if ($_SWIFT->Staff->GetPermission('admin_mpcaninsertqueue') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_INSERT);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render the Confirmation for InsertSubmit/EditSubmit
     *
     * @author Varun Shoor
     *
     * @param mixed $_mode The User Interface Mode
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            $_type = 'update';
        } else {
            $_type = 'insert';
        }

        $this->Cache->Queue('templategroupcache', 'departmentcache', 'statuscache', 'prioritycache', 'tickettypecache');
        $this->Cache->LoadQueue();

        $_templateGroupCache = $this->Cache->Get('templategroupcache');
        $_departmentCache = $this->Cache->Get('departmentcache');
        $_statusCache = $this->Cache->Get('statuscache');
        $_priorityCache = $this->Cache->Get('prioritycache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');

        $_finalText = '<b>' . $this->Language->Get('emailaddress') . ':</b> ' . htmlspecialchars($_POST['email']) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('emailtype') . ':</b> ' . htmlspecialchars(ucfirst($_POST['type'])) . '<br />';
        $_finalText .= '<b>' . $this->Language->Get('emailfetchtype') . ':</b> ' . htmlspecialchars(strtoupper($_POST['fetchtype'])) . '<br />';

        if (isset($_templateGroupCache[$_POST['templategroupid']])) {
            $_finalText .= '<b>' . $this->Language->Get('templategroup') . ':</b> ' .
                htmlspecialchars($_templateGroupCache[$_POST['templategroupid']]['title']) . '<br />';
        } else {
            $_finalText .= '<b>' . $this->Language->Get('templategroup') . ':</b> ' . $this->Language->Get('na') . '<br />';
        }

        $_finalText .= '<b>' . $this->Language->Get('queueisenabled') . ':</b> ' . IIF(
            $_POST['isenabled'] == 1,
            $this->Language->Get('yes'),
            $this->Language->Get('no')
        ) . '<br />';

        if (
            $_POST['fetchtype'] == SWIFT_EmailQueue::FETCH_POP3 || $_POST['fetchtype'] == SWIFT_EmailQueue::FETCH_POP3SSL ||
            $_POST['fetchtype'] == SWIFT_EmailQueue::FETCH_POP3TLS || $_POST['fetchtype'] == SWIFT_EmailQueue::FETCH_IMAP ||
            $_POST['fetchtype'] == SWIFT_EmailQueue::FETCH_IMAPSSL || $_POST['fetchtype'] == SWIFT_EmailQueue::FETCH_IMAPTLS
        ) {
            $_finalText .= '<b>' . $this->Language->Get('emailhost') . ':</b> ' . htmlspecialchars($_POST['host']) . '<br />';
            $_finalText .= '<b>' . $this->Language->Get('emailport') . ':</b> ' . htmlspecialchars($_POST['port']) . '<br />';
            $_finalText .= '<b>' . $this->Language->Get('emailusername') . ':</b> ' . htmlspecialchars($_POST['username']) . '<br />';
            $_finalText .= '<b>' . $this->Language->Get('leavecopyonserver') . ':</b> ' .
                IIF($_POST['leavecopyonserver'] == 1, $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';
            $_finalText .= '<b>' . $this->Language->Get('usequeuesmtp') . ':</b> ' .
                IIF($_POST['usequeuesmtp'] == 1, $this->Language->Get('yes'), $this->Language->Get('no')) . '<br />';

            if ($_POST['usequeuesmtp'] == '1') {
                $_finalText .= '<b>' . $this->Language->Get('smtptype') . ':</b> ' . htmlspecialchars(strtoupper($_POST['smtptype'])) . '<br />';
            }
        }

        if ($_POST['type'] == SWIFT_EmailQueueType::TYPE_TICKETS) {
            if (isset($_departmentCache[$_POST['departmentid']])) {
                $_finalText .= '<b>' . $this->Language->Get('queuedepartment') . ':</b> ' .
                    text_to_html_entities($_departmentCache[$_POST['departmentid']]['title']) . '<br />';
            } else {
                $_finalText .= '<b>' . $this->Language->Get('queuedepartment') . ':</b> ' . $this->Language->Get('na') . '<br />';
            }

            if (isset($_ticketTypeCache[$_POST['tickettypeid']])) {
                $_finalText .= '<b>' . $this->Language->Get('queuetickettype') . ':</b> ' .
                    htmlspecialchars($_ticketTypeCache[$_POST['tickettypeid']]['title']) . '<br />';
            } else {
                $_finalText .= '<b>' . $this->Language->Get('queuetickettype') . ':</b> ' . $this->Language->Get('na') . '<br />';
            }

            if (isset($_statusCache[$_POST['ticketstatusid']])) {
                $_finalText .= '<b>' . $this->Language->Get('queueticketstatus') . ':</b> ' .
                    htmlspecialchars($_statusCache[$_POST['ticketstatusid']]['title']) . '<br />';
            } else {
                $_finalText .= '<b>' . $this->Language->Get('queueticketstatus') . ':</b> ' . $this->Language->Get('na') . '<br />';
            }

            if (isset($_priorityCache[$_POST['ticketpriorityid']])) {
                $_finalText .= '<b>' . $this->Language->Get('queuepriority') . ':</b> ' .
                    htmlspecialchars($_priorityCache[$_POST['ticketpriorityid']]['title']) . '<br />';
            } else {
                $_finalText .= '<b>' . $this->Language->Get('queuepriority') . ':</b> ' . $this->Language->Get('na') . '<br />';
            }
        }

        SWIFT::Info($this->Language->Get('titlequeue' . $_type), sprintf(
            $this->Language->Get('msgqueue' . $_type),
            htmlspecialchars($_POST['email'])
        ) . '<br />' . $_finalText);

        return true;
    }

    /**
     * The First Step Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InsertStep()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_postStep = 2;
        if (!$this->RunChecks(SWIFT_UserInterface::MODE_INSERT, false)) {
            $_postStep = 1;
        }

        $this->UserInterface->Header(
            $this->Language->Get('mailparser') . ' > ' . $this->Language->Get('insertemailqueue'),
            self::MENU_ID,
            self::NAVIGATION_ID
        );
        $this->View->Render(SWIFT_UserInterface::MODE_INSERT, null, $_postStep);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Insert Submission Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function InsertSubmit()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(SWIFT_UserInterface::MODE_INSERT)) {
            $_EmailQueueTypeObject = $this->_GetMailQueueTypeObject();
            if (!$_EmailQueueTypeObject) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_emailQueueID = false;

            $_registrationRequired = false;
            if (isset($_POST['registrationrequired'])) {
                $_registrationRequired = $_POST['registrationrequired'];
            }

            // Create EmailQueuePipe
            if ($_POST['fetchtype'] == 'pipe') {
                $_emailQueueID = SWIFT_EmailQueuePipe::Create(
                    $_POST['email'],
                    $_EmailQueueTypeObject,
                    $_POST['prefix'],
                    trim($_POST['customfromname']),
                    $_POST['customfromemail'],
                    $_POST['signature'],
                    $_registrationRequired,
                    $_POST['isenabled']
                );

                // Create EmailQueueMailbox
            } else {
                /*
                 * Verem Dugeri <verem.dugeri@crossover.com>
                 * BUG-FIX - KAYAKO-2895 - Email queue passwords are saved in plain text in the database
                 */
                $userPassword = SWIFT_Cryptor::Encrypt($_POST['userpassword']);

                $_emailQueueID = SWIFT_EmailQueueMailbox::Create(
                    $_POST['email'],
                    $_EmailQueueTypeObject,
                    $_POST['fetchtype'],
                    $_POST['prefix'],
                    trim($_POST['customfromname']),
                    $_POST['customfromemail'],
                    $_POST['signature'],
                    $_registrationRequired,
                    $_POST['isenabled'],
                    $_POST['host'],
                    $_POST['port'],
                    $_POST['username'],
                    $userPassword,
                    $_POST['authtype'],
                    $_POST['authclientid'],
                    $_POST['authclientsecret'],
                    $_POST['authendpoint'],
                    $_POST['tokenendpoint'],
                    $_POST['authscope'],
                    $_POST['accesstoken'],
                    $_POST['refreshtoken'],
                    $_POST['tokenexpiry'],
                    $_POST['leavecopyonserver'],
                    $_POST['usequeuesmtp'],
                    $_POST['smtphost'],
                    $_POST['smtpport'],
                    $_POST['smtptype'],
                    $_POST['forcequeue']
                );
            }

            SWIFT_StaffActivityLog::AddToLog(
                sprintf($this->Language->Get('activityinsertemailqueue'), htmlspecialchars(StripName($_POST['email'], 100))),
                SWIFT_StaffActivityLog::ACTION_INSERT,
                SWIFT_StaffActivityLog::SECTION_PARSER,
                SWIFT_StaffActivityLog::INTERFACE_ADMIN
            );

            if (!$_emailQueueID) {
                // @codeCoverageIgnoreStart
                // will not be reached
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
            // @codeCoverageIgnoreEnd

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_INSERT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Insert();

        return false;
    }

    /**
     * Retrieve the Email Queue Type Object
     *
     * @author Varun Shoor
     * @return mixed "_EmailQueueTypeObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _GetMailQueueTypeObject()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_EmailQueueTypeObject = false;

        if ($_POST['type'] == SWIFT_EmailQueueType::TYPE_TICKETS) {
            $_EmailQueueTypeObject = new SWIFT_EmailQueueType_Tickets(
                $_POST['templategroupid'],
                $_POST['departmentid'],
                $_POST['tickettypeid'],
                $_POST['ticketpriorityid'],
                $_POST['ticketstatusid'],
                $_POST['ticketautoresponder']
            );
        } else if ($_POST['type'] == SWIFT_EmailQueueType::TYPE_NEWS) {
            $_EmailQueueTypeObject = new SWIFT_EmailQueueType_News();
        } else if ($_POST['type'] == SWIFT_EmailQueueType::TYPE_BACKEND) {
            $_EmailQueueTypeObject = new SWIFT_EmailQueueType_Backend();
        }

        return $_EmailQueueTypeObject;
    }

    /**
     * Edit the Email Queue ID
     *
     * @author Varun Shoor
     *
     * @param int $_emailQueueID The Email Queue ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_emailQueueID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_emailQueueID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_EmailQueueObject = SWIFT_EmailQueue::Retrieve($_emailQueueID);
        if (!$_SWIFT_EmailQueueObject instanceof SWIFT_EmailQueue || !$_SWIFT_EmailQueueObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UserInterface->Header(
            $this->Language->Get('mailparser') . ' > ' . $this->Language->Get('editemailqueue'),
            self::MENU_ID,
            self::NAVIGATION_ID
        );

        if ($_SWIFT->Staff->GetPermission('admin_mpcanupdatequeue') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render(SWIFT_UserInterface::MODE_EDIT, $_SWIFT_EmailQueueObject);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Edit Submission Processor
     *
     * @author Varun Shoor
     *
     * @param int $_emailQueueID The Email Queue ID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function EditSubmit($_emailQueueID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_emailQueueID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_EmailQueueObject = SWIFT_EmailQueue::Retrieve($_emailQueueID);
        if (!$_SWIFT_EmailQueueObject instanceof SWIFT_EmailQueue || !$_SWIFT_EmailQueueObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }


        if ($this->RunChecks(SWIFT_UserInterface::MODE_EDIT)) {
            $_EmailQueueTypeObject = $this->_GetMailQueueTypeObject();
            if (!$_EmailQueueTypeObject) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            if ($_POST['fetchtype'] != $_SWIFT_EmailQueueObject->GetProperty('fetchtype')) {
                $_SWIFT_EmailQueueObject->UpdateFetchType($_POST['fetchtype']);
                $_SWIFT_EmailQueueObject = SWIFT_EmailQueue::Retrieve($_emailQueueID);
            }

            $_customFromname = $this->Input->SanitizeForXSS($_POST['customfromname']);

            // Create EmailQueuePipe
            if ($_POST['fetchtype'] == 'pipe') {
                $_updateResult = $_SWIFT_EmailQueueObject->Update(
                    $_POST['email'],
                    $_EmailQueueTypeObject,
                    $_POST['prefix'],
                    $_customFromname,
                    $_POST['customfromemail'],
                    $_POST['signature'],
                    $_POST['registrationrequired'],
                    $_POST['isenabled']
                );
                // Create EmailQueueMailbox
            } else {
                /*
                 * Werner Garcia <werner.garcia@crossover.com>
                 * BUNBTX/KAYAKO-3050 - Tickets are not created when a mail is
                 * sent to the Email Queue ID
                 */
                $userPassword = $_SWIFT_EmailQueueObject->GetProperty('userpassword');
                if (strcmp($userPassword, $_POST['userpassword']) !== 0) {
                    // if the password is different, encrypt it
                    $userPassword = SWIFT_Cryptor::Encrypt($_POST['userpassword']);
                }

                $_updateResult = $_SWIFT_EmailQueueObject->Update(
                    $_POST['email'],
                    $_EmailQueueTypeObject,
                    $_POST['fetchtype'],
                    $_POST['prefix'],
                    $_customFromname,
                    $_POST['customfromemail'],
                    $_POST['signature'],
                    $_POST['registrationrequired'],
                    $_POST['isenabled'],
                    $_POST['host'],
                    $_POST['port'],
                    $_POST['username'],
                    $userPassword,
                    $_POST['authtype'],
                    $_POST['authclientid'],
                    $_POST['authclientsecret'],
                    $_POST['authendpoint'],
                    $_POST['tokenendpoint'],
                    $_POST['authscope'],
                    $_POST['accesstoken'],
                    $_POST['refreshtoken'],
                    $_POST['tokenexpiry'],
                    $_POST['leavecopyonserver'],
                    $_POST['usequeuesmtp'],
                    $_POST['smtphost'],
                    $_POST['smtpport'],
                    $_POST['smtptype'],
                    $_POST['forcequeue']
                );
            }

            SWIFT_StaffActivityLog::AddToLog(
                sprintf($this->Language->Get('activityupdateemailqueue'), htmlspecialchars(StripName($_POST['email'], 100))),
                SWIFT_StaffActivityLog::ACTION_UPDATE,
                SWIFT_StaffActivityLog::SECTION_PARSER,
                SWIFT_StaffActivityLog::INTERFACE_ADMIN
            );

            if (!$_updateResult) {
                // @codeCoverageIgnoreStart
                // will not be reached
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
            // @codeCoverageIgnoreEnd

            $this->_RenderConfirmation(SWIFT_UserInterface::MODE_EDIT);

            $this->Load->Manage();

            return true;
        }

        $this->Load->Edit($_emailQueueID);

        return false;
    }

    /**
     * Verify the email connection
     *
     * @author Varun Shoor
     *
     * @param string $_chunkContainer The BASE64 Encoded Chunk Container
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function VerifyConnection($_chunkContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_currentStatus = true;
        $_connectionContainer = array();

        $_getChunkContainer = explode('&', base64_decode($_chunkContainer));

        $_finalValueContainer = array();

        foreach ($_getChunkContainer as $key => $val) {
            $_getExtractDetails = explode('=', $val);
            $_finalValueContainer[$_getExtractDetails[0]] = rawurldecode($_getExtractDetails[1]);
        }

        if (!_is_array($_finalValueContainer)) {
            // @codeCoverageIgnoreStart
            // will not be reached (function explode() will return array or one empty element)
            $_connectionContainer[] = array($this->Language->Get('vcvariablesanity'), false);
            $_currentStatus = false;
        }
        // @codeCoverageIgnoreEnd


        /*
         * ###############################################
         * CHECK FOR SANITY OF DATA
         * ###############################################
         */
        if ($_currentStatus == true && (!isset($_finalValueContainer['host']) || empty($_finalValueContainer['host']) ||
            !isset($_finalValueContainer['port']) || empty($_finalValueContainer['port']) || !isset($_finalValueContainer['username']) ||
            empty($_finalValueContainer['username']) || !isset($_finalValueContainer['userpassword']) ||
            empty($_finalValueContainer['userpassword']) || !isset($_finalValueContainer['fetchtype']) ||
            empty($_finalValueContainer['fetchtype']) || $_finalValueContainer['fetchtype'] == SWIFT_EmailQueue::FETCH_PIPE)) {
            $_connectionContainer[] = array($this->Language->Get('vcvariablesanity'), false);
            $_currentStatus = false;
        } else if ($_currentStatus == true) {
            $_connectionContainer[] = array($this->Language->Get('vcvariablesanity'), true);
        }

        if ($_currentStatus == true) {
            $_currentStatus = $this->connectToIMAPServer($_finalValueContainer, $_connectionContainer);
        }

        $this->UserInterface->Header($this->Language->Get('mailparser'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->RenderVerifyConnection($_connectionContainer);
        $this->UserInterface->Footer();

        return true;
    }


    public function VerifyOAuth($_chunkContainer = '')
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($_chunkContainer == '') {
            // This is because in the authcode flow, the redirect cannot base64 encode the parameters
            $_finalValueContainer = $_GET;
        } else {
            $_getChunkContainer = explode('&', base64_decode($_chunkContainer));
            $_finalValueContainer = array();
            foreach ($_getChunkContainer as $key => $val) {
                $_getExtractDetails = explode('=', $val);
                $_finalValueContainer[$_getExtractDetails[0]] = rawurldecode($_getExtractDetails[1]);
            }
        }

        if (isset($_finalValueContainer['code'])) {
            // The user has logged in and we received the code
            if (isset($_finalValueContainer['clientsecret'])) {
                //The main verification window is asking to finalise the verification
                $_accessToken = null;
                $_currentStatus = true;
                $_connectionContainer = array();

                if ($_currentStatus == true && (!isset($_finalValueContainer['host']) || empty($_finalValueContainer['host']) ||
                    !isset($_finalValueContainer['port']) || empty($_finalValueContainer['port']) || !isset($_finalValueContainer['fetchtype']) ||
                    empty($_finalValueContainer['fetchtype']) || $_finalValueContainer['fetchtype'] == SWIFT_EmailQueue::FETCH_PIPE)) {
                    $_connectionContainer[] = array($this->Language->Get('vcvariablesanity'), false);
                    $_currentStatus = false;
                } else if ($_currentStatus == true) {
                    $_connectionContainer[] = array($this->Language->Get('vcvariablesanity'), true);
                }

                if ($_currentStatus == true) {
                    $_tokens = SWIFT_OAuth::exchangeCode(
                        $_finalValueContainer['tokenurl'],
                        $_finalValueContainer['clientid'],
                        $_finalValueContainer['clientsecret'],
                        $this->getOAuthRedirectURL(),
                        $_finalValueContainer['code']
                    );
                    $_finalValueContainer['tokenexpiry'] = 0;
                    if (isset($_tokens['expires_in'])) {
                        $_finalValueContainer['tokenexpiry'] = DATENOW + $_tokens['expires_in'];
                    }
                    if (isset($_tokens['access_token'])) {
                        $_finalValueContainer['accesstoken'] = $_tokens['access_token'];
                        $_finalValueContainer['refreshtoken'] = $_tokens['refresh_token'];
                        $_idToken = $_tokens['id_token'];
                        $_connectionContainer[] = array($this->Language->Get('vcaccesstoken'), true);
                        if (isset($_idToken)) {
                            $_decodedIdToken = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $_idToken)[1]))), true);
                            if (isset($_decodedIdToken['email'])) {
                                $_finalValueContainer['username'] = $_decodedIdToken['email'];
                                $_connectionContainer[] = array($this->Language->Get('vcidtoken'), true);
                            } else {
                                $_connectionContainer[] = array($this->Language->Get('vcidtoken'), false);
                                $_currentStatus = false;
                            }
                        } else {
                            $_connectionContainer[] = array($this->Language->Get('vcidtoken'), false);
                            $_currentStatus = true;
                        }
                    } else {
                        $_connectionContainer[] = array($this->Language->Get('vcaccesstoken'), false);
                        $_currentStatus = false;
                    }
                }
                if ($_currentStatus == true) {
                    $_currentStatus = $this->connectToIMAPServer($_finalValueContainer, $_connectionContainer);
                }
                $_setTokenScript = '';
                if ($_currentStatus == true) {
                    $_setTokenScript = "<script>UpdateAccessToken('" . $_finalValueContainer["accesstoken"] . "', '" . $_finalValueContainer["refreshtoken"] . "', '" . $_finalValueContainer['tokenexpiry'] . "', '" . $_finalValueContainer['username'] . "');</script>";
                }
                $this->UserInterface->Header($this->Language->Get('mailparser'), self::MENU_ID, self::NAVIGATION_ID);
                $this->View->RenderVerifyConnection($_connectionContainer, $_setTokenScript);
                $this->UserInterface->Footer();
            } else {
                //The login window has returned the code, send the event to the main window and close the login window
                $this->UserInterface->Start(get_short_class($this) . 'verifycon', '/Parser/EmailQueue/VerifyConnection', SWIFT_UserInterface::MODE_EDIT, true);
                $this->UserInterface->AppendHTML("<script>window.opener.receiveAuthCode('" . $_finalValueContainer['code'] . "');</script>");
                $this->UserInterface->End();
            }
        } else if (isset($_finalValueContainer['clientid']) && isset($_finalValueContainer['authurl'])) {
            // The login window is wanting to initiate the authorization code flow
            $authorizationUrl = $_finalValueContainer['authurl'] . "?response_type=code&client_id=" . $_finalValueContainer['clientid'] . "&redirect_uri=" . $this->getOAuthRedirectURL() . "&nonce=123&scope=" . $_finalValueContainer['authscope'] . "&access_type=offline";
            header('Location: ' . $authorizationUrl);
        }
    }

    public function getOAuthRedirectURL()
    {
        $_SWIFT = SWIFT::GetInstance();
        $_productURL = $_SWIFT->Settings->Get('general_producturl');

        $_URL = sprintf('%sadmin/verifyoauth', $_productURL);
        return $_URL;
    }


    public function connectToIMAPServer($_finalValueContainer, &$_connectionContainer)
    {
        $_currentStatus = true;
        // We require IMAP.  If it's not installed we can't fetch email through this script anyways.
        if ($_currentStatus == true && !extension_loaded('imap')) {
            // @codeCoverageIgnoreStart
            // imap extension enabled
            $_connectionContainer[] = array($this->Language->Get('vcimapnotcompiled'), false);
            $_currentStatus = false;
            // @codeCoverageIgnoreEnd
        } else if ($_currentStatus == true && extension_loaded('imap')) {
            $_connectionContainer[] = array($this->Language->Get('vcimapcompiled'), true);
        }

        if ($_currentStatus == true) {
            $_connectionContainer[] = array($this->Language->Get('vcattemptconnection'), true);

            $_fetchTypeArgument = SWIFT_EmailQueue::GetIMAPArgument($_finalValueContainer['fetchtype']);

            if (isset($_finalValueContainer['userpassword'])) {
                $_userPassword = $_finalValueContainer['userpassword'];
                try {
                    // try to decrypt the password if it's already encrypted
                    $decryptedUserPassword = SWIFT_Cryptor::Decrypt($_userPassword);
                } catch (\Exception $ex) {
                    // if the password is not encrypted, use it
                    $decryptedUserPassword = $_userPassword;
                }
            } else if (isset($_finalValueContainer['accesstoken'])) {
                $accessToken = $_finalValueContainer['accesstoken'];
            }


            try {
                $protocol = null;
                if (isset($decryptedUserPassword)) {
                    [$protocol, $protocolType] = SWIFT_MailParserIMAP::fetchZendRequest($_finalValueContainer);
                    $protocol->login($_finalValueContainer['username'], $decryptedUserPassword);
                } else if (isset($accessToken)) {
                    [$protocol, $protocolType] = SWIFT_MailParserIMAP::fetchZendRequest($_finalValueContainer);
                    $b64str = base64_encode("user=" . trim($_finalValueContainer['username']) . "\1auth=Bearer " . $accessToken . "\1\1");
                    if ($protocolType == 'pop3') {
                        $result = $protocol->sendRequests(['AUTH XOAUTH2', $b64str]);
                        SWIFT_StaffActivityLog::AddToLog(
                            'Auth result for POP3 user ' . $_finalValueContainer['username'] . ' is ' . $result,
                            SWIFT_StaffActivityLog::ACTION_OTHER,
                            SWIFT_StaffActivityLog::SECTION_PARSER,
                            SWIFT_StaffActivityLog::INTERFACE_ADMIN
                        );
                        if (!strpos($result, '+OK')) {
                            throw new \Exception('Fail when authenticate POP3 oauth for user ' . $_finalValueContainer['username']);
                        }
                    } else if ($protocolType == 'imap') {
                        $authenticateParams = array('XOAUTH2', $b64str);
                        $protocol->sendRequest('AUTHENTICATE', $authenticateParams);
                    }
                }
                $storage = SWIFT_MailParserIMAP::fetchZendRequest($_finalValueContainer, true, $protocol);
                $_connectionContainer[] = array($this->Language->Get('vcconnectionsuccess'), true);
                $_totalMessageCount = $storage->countMessages();
                $_connectionContainer[] = array(sprintf($this->Language->Get('vctotalmessages'), $_totalMessageCount), true);
            } catch (\Exception $e) {
                $_connectionContainer[] = array(sprintf($this->Language->Get('vcconnectionfailed'), $e->getMessage()), false);
                $_currentStatus = false;
            }
        }
        return $_currentStatus;
    }
}
