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

namespace LiveChat\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\Rules\SWIFT_Rules;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Models\SearchStore\SWIFT_SearchStore;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use LiveChat\Models\Call\SWIFT_Call;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_FileManager;
use SWIFT_Hook;
use SWIFT_Session;

/**
 * The Call Controller
 *
 * @author Varun Shoor
 *
 * @property View_Call $View
 */
class Controller_Call extends Controller_staff
{
    // Core Constants
    const MENU_ID = 3;
    const NAVIGATION_ID = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('staff_livechat');
    }

    /**
     * Delete the Calls from Mass Action
     *
     * @author Varun Shoor
     * @param mixed $_callIDList The Call ID List Container Array
     * @param bool $_byPassCSRF Whether to bypass the CSRF check
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_callIDList, $_byPassCSRF = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        // BEGIN CSRF HASH CHECK

        if (!$_byPassCSRF && !SWIFT_Session::CheckCSRFHash($_POST['csrfhash'])) {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_SWIFT->Staff->GetPermission('staff_lscandeletecalls') == '0') {
            SWIFT::Error($_SWIFT->Language->Get('titlenoperm'), $_SWIFT->Language->Get('msgnoperm'));

            return false;
        }

        if (_is_array($_callIDList)) {
            $_SWIFT->Database->Query("SELECT phonenumber FROM " . TABLE_PREFIX . "calls WHERE callid IN (" .
                BuildIN($_callIDList) . ")");
            while ($_SWIFT->Database->NextRecord()) {
                SWIFT_StaffActivityLog::AddToLog(sprintf($_SWIFT->Language->Get('activitydeletecalls'),
                    htmlspecialchars($_SWIFT->Database->Record['phonenumber'])), SWIFT_StaffActivityLog::ACTION_DELETE,
                    SWIFT_StaffActivityLog::SECTION_LIVESUPPORT, SWIFT_StaffActivityLog::INTERFACE_STAFF);
            }

            // Begin Hook: staff_call_delete
            unset($_hookCode);
            ($_hookCode = SWIFT_Hook::Execute('staff_call_delete')) ? eval($_hookCode) : false;
            // End Hook

            SWIFT_Call::DeleteList($_callIDList);
        }

        return true;
    }

    /**
     * Delete the Given Call ID
     *
     * @author Varun Shoor
     * @param int $_callID The Call ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_callID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($_callID), true);

        $this->Load->Manage();

        return true;
    }

    /**
     * Displays the Call Grid
     *
     * @author Varun Shoor
     * @param int $_searchStoreID The Search Store ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Manage($_searchStoreID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!is_numeric($_searchStoreID)) {
            $_searchStoreID = 0;
        }

        if ($_SWIFT->Staff->GetPermission('staff_lscanviewcalls') != '0') {
            $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->View->RenderTree());
        }

        $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('calllogs'), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_lscanviewcalls') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->RenderGrid($_searchStoreID);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * View a Call Log
     *
     * @author Varun Shoor
     * @param int $_callID THe Call ID
     * @param int $_noDialog Whether to display dialog
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ViewCall($_callID, $_noDialog = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_callID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_CallObject = new SWIFT_Call(new SWIFT_DataID($_callID));
        if (!$_SWIFT_CallObject instanceof SWIFT_Call || !$_SWIFT_CallObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (!is_numeric($_noDialog)) {
            $_noDialog = 0;
        }

        if ($_SWIFT->Staff->GetPermission('staff_lscanviewcalls') != '0') {
            $this->UserInterface->AddNavigationBox($this->Language->Get('quickfilter'), $this->View->RenderTree());
        }

        $this->UserInterface->Header(sprintf($this->Language->Get('viewcallext'), htmlspecialchars($_SWIFT_CallObject->GetProperty('phonenumber'))), self::MENU_ID,
            self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('staff_lscanviewcalls') == '0') {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render($_SWIFT_CallObject, $_noDialog);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Quick Filter Options
     *
     * @author Varun Shoor
     * @param string $_filterType The Filter Type
     * @param string $_filterValue The Filter Value
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function QuickFilter($_filterType, $_filterValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_searchStoreID = -1;

        $_callIDList = array();

        $_gridSortContainer = SWIFT_UserInterfaceGrid::GetGridSortField('callgrid', 'dateline', 'desc');

        switch ($_filterType) {
            case 'status':
                {
                    if ($_filterValue == 'pending') {
                        $this->Database->QueryLimit("SELECT callid FROM " . TABLE_PREFIX . "calls
                        WHERE callstatus = '" . (SWIFT_Call::STATUS_PENDING) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'accepted') {
                        $this->Database->QueryLimit("SELECT callid FROM " . TABLE_PREFIX . "calls
                        WHERE callstatus = '" . (SWIFT_Call::STATUS_ACCEPTED) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'ended') {
                        $this->Database->QueryLimit("SELECT callid FROM " . TABLE_PREFIX . "calls
                        WHERE callstatus = '" . (SWIFT_Call::STATUS_ENDED) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'rejected') {
                        $this->Database->QueryLimit("SELECT callid FROM " . TABLE_PREFIX . "calls
                        WHERE callstatus = '" . (SWIFT_Call::STATUS_REJECTED) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'unanswered') {
                        $this->Database->QueryLimit("SELECT callid FROM " . TABLE_PREFIX . "calls
                        WHERE callstatus = '" . (SWIFT_Call::STATUS_UNANSWERED) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    }
                    while ($this->Database->NextRecord()) {
                        $_callIDList[] = $this->Database->Record['callid'];
                    }

                }
                break;

            case 'type':
                {
                    if ($_filterValue == 'inbound') {
                        $this->Database->QueryLimit("SELECT callid FROM " . TABLE_PREFIX . "calls
                        WHERE calltype = '" . (SWIFT_Call::TYPE_INBOUND) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'outbound') {
                        $this->Database->QueryLimit("SELECT callid FROM " . TABLE_PREFIX . "calls
                        WHERE calltype = '" . (SWIFT_Call::TYPE_OUTBOUND) . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    } else if ($_filterValue == 'missed') {
                        $this->Database->QueryLimit("SELECT callid FROM " . TABLE_PREFIX . "calls
                        WHERE calltype = '" . (SWIFT_Call::TYPE_INBOUND) . "' AND callstatus = '" . SWIFT_Call::STATUS_PENDING . "'
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                    }
                    while ($this->Database->NextRecord()) {
                        $_callIDList[] = $this->Database->Record['callid'];
                    }

                }
                break;

            case 'date':
                {
                    $_extendedSQL = false;

                    if ($_filterValue == 'today') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('dateline', SWIFT_Rules::DATERANGE_TODAY);
                    } else if ($_filterValue == 'yesterday') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('dateline', SWIFT_Rules::DATERANGE_YESTERDAY);
                    } else if ($_filterValue == 'l7') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('dateline', SWIFT_Rules::DATERANGE_LAST7DAYS);
                    } else if ($_filterValue == 'l30') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('dateline', SWIFT_Rules::DATERANGE_LAST30DAYS);
                    } else if ($_filterValue == 'l180') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('dateline', SWIFT_Rules::DATERANGE_LAST180DAYS);
                    } else if ($_filterValue == 'l365') {
                        $_extendedSQL = SWIFT_Rules::BuildSQLDateRange('dateline', SWIFT_Rules::DATERANGE_LAST365DAYS);
                    }

                    if (!empty($_extendedSQL)) {
                        $this->Database->QueryLimit("SELECT callid FROM " . TABLE_PREFIX . "calls
                        WHERE " . $_extendedSQL . "
                        ORDER BY " . $_gridSortContainer[0] . ' ' . $_gridSortContainer[1], 100);
                        while ($this->Database->NextRecord()) {
                            $_callIDList[] = $this->Database->Record['callid'];
                        }
                    }

                }
                break;

            default:
                break;
        }

        $_searchStoreID = SWIFT_SearchStore::Create($_SWIFT->Session->GetSessionID(), SWIFT_SearchStore::TYPE_CALLS, $_callIDList,
            $_SWIFT->Staff->GetStaffID());

        if (!_is_array($_callIDList)) {
            SWIFT::Alert($this->Language->Get('titlesearchfailed'), $this->Language->Get('msgsearchfailed'));
        }

        $this->Load->Manage($_searchStoreID);

        return true;
    }

    /**
     * View Call History
     *
     * @author Varun Shoor
     * @param string $_queryString The Query String
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function History($_queryString)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_userDetails = array();

        parse_str(base64_decode($_queryString), $_userDetails);

        if (!isset($_userDetails['userid']) && !isset($_userDetails['email'])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_userID = 0;
        if (isset($_userDetails['userid'])) {
            $_userID = $_userDetails['userid'];
        }

        $_userEmailList = array();
        if (isset($_userDetails['email']) && _is_array($_userDetails['email'])) {
            $_userEmailList = $_userDetails['email'];
        } else if (isset($_userDetails['email']) && is_string($_userDetails['email'])) {
            $_userEmailList[] = $_userDetails['email'];
        }

        $_historyContainer = SWIFT_Call::RetrieveHistoryExtended($_userID, $_userEmailList);

        $this->View->RenderCallHistoryGrid($_historyContainer);

        return true;
    }

    /**
     * Play recording
     *
     * @author Parminder Singh
     * @param string $_callID The Call ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function PlayRecording($_callID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_CallObject = false;

        try {
            $_SWIFT_CallObject = new SWIFT_Call(new SWIFT_DataID($_callID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if (!$_SWIFT_CallObject instanceof SWIFT_Call || !$_SWIFT_CallObject->GetIsClassLoaded() || $_SWIFT_CallObject->GetProperty('fileid') == '0') {
            return false;
        }

        $_SWIFT_FileManagerObject = false;

        try {
            $_SWIFT_FileManagerObject = new SWIFT_FileManager($_SWIFT_CallObject->GetProperty('fileid'));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if (!$_SWIFT_FileManagerObject instanceof SWIFT_FileManager || !$_SWIFT_FileManagerObject->GetIsClassLoaded()) {
            return false;
        }


        /**
         * ---------------------------------------------
         * Remote Attachment Logic
         * ---------------------------------------------
         */
        if ($_SWIFT_FileManagerObject->HasRemote()) {
            $_remoteFileInfo = $_SWIFT_FileManagerObject->GetRemoteInfo();
            if (!$_remoteFileInfo) {
                echo 'Unable to locate remote file "' . $_SWIFT_FileManagerObject->GetProperty('filename') . '". Please contact QuickSupport support for assistance.';

                return false;
            }

            // Yes, just read out the file
            $_filePointer = @fopen($_remoteFileInfo['url'], 'rb');
            if (!$_filePointer) {
                echo 'Unable to open remote file "' . $_SWIFT_FileManagerObject->GetProperty('filename') . '", info: ' . print_r($_remoteFileInfo, true) . '. Please contact QuickSupport support for assistance.';

                return false;
            }

            header('Content-Type: audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3');
            header('Content-Length: ' . $_remoteFileInfo['size']);
            header('Content-Disposition: filename="' . $_SWIFT_FileManagerObject->GetProperty('originalfilename'));
            header('Cache-Control: no-cache, no-store, must-revalidate');

            while (!feof($_filePointer)) {
                echo fread($_filePointer, 8192);
                flush();
            }

            fclose($_filePointer);

            /**
             * ---------------------------------------------
             * Local Attachment Logic
             * ---------------------------------------------
             */
        } else {
            $_filePath = $_SWIFT_FileManagerObject->GetPath();
            if (!file_exists($_filePath)) {
                return false;
            }

            header('Content-Type: audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3');
            header('Content-Length: ' . filesize($_filePath));
            header('Content-Disposition: filename="' . $_SWIFT_FileManagerObject->GetProperty('originalfilename'));
            header('Cache-Control: no-cache, no-store, must-revalidate');

            readfile($_filePath);
        }

        return true;
    }

    /**
     * Download
     *
     * @author Simaranjit Singh
     *
     * @param string $_callID
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Download($_callID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Check permission
        if ($_SWIFT->Staff->GetPermission('staff_lscanviewcalls') == '0') {
            $this->UserInterface->Header($this->Language->Get('livechat') . ' > ' . $this->Language->Get('tabcalls'), self::MENU_ID,
                self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;
        }

        $_Call = false;

        try {
            $_Call = new SWIFT_Call(new SWIFT_DataID($_callID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if (!$_Call instanceof SWIFT_Call || !$_Call->GetIsClassLoaded() || $_Call->GetProperty('fileid') == '0') {
            return false;
        }

        $_FileManager = false;

        try {
            $_FileManager = new SWIFT_FileManager($_Call->GetProperty('fileid'));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if (!$_FileManager instanceof SWIFT_FileManager || !$_FileManager->GetIsClassLoaded()) {
            return false;
        }

        $_FileManager->Dispatch();

        return true;
    }
}
