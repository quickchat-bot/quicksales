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

namespace Tickets\Staff;

use Base\Library\CustomField\SWIFT_CustomFieldRendererStaff;
use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_StaffBase;
use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Library\CustomField\SWIFT_CustomFieldManager;
use SWIFT_DataID;
use SWIFT_Exception;
use SWIFT_Hook;
use Tickets\Library\Ticket\SWIFT_TicketEmailDispatch;
use Tickets\Models\SLA\SWIFT_SLA;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Draft\SWIFT_TicketDraft;
use Tickets\Models\Ticket\SWIFT_TicketLinkedTable;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Library\Notification\SWIFT_TicketNotification;
use Tickets\Models\Ticket\SWIFT_TicketPost;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Library\View\SWIFT_TicketViewRenderer;
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;
use Tickets\Models\Workflow\SWIFT_TicketWorkflowNotification;
use Base\Models\User\SWIFT_UserGroup;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The View Ticket Controller
 *
 * @method Method($v='', $_ticketID=0, $_listType=0, $_departmentID=0, $_ticketStatusID=0, $_ticketTypeID=0, $_ticketLimitOffset=0);
 * @property SWIFT_CustomFieldManager $CustomFieldManager
 * @method Controller($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @method Library($_libraryName, $_arguments = [], $_initiateInstance = false, $_customAppName = '', $_appName = '')
 * @property Controller_Ticket $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Ticket $View
 * @property SWIFT_TicketEmailDispatch $TicketEmailDispatch
 * @property SWIFT_CustomFieldRendererStaff $CustomFieldRendererStaff
 * @author Varun Shoor
 */
class Controller_Ticket extends Controller_StaffBase
{
    use Controller_TicketViewTrait;
    use Controller_TicketBillingTrait;
    use Controller_TicketPostTrait;
    use Controller_TicketNoteTrait;
    use Controller_TicketFollowUpTrait;
    use Controller_TicketTrait;
    use Controller_TicketDispatchTrait;
    use Controller_TicketHistoryTrait;
    use Controller_TicketRecurrenceTrait;
    use Controller_TicketForwardTrait;
    use Controller_TicketWatchTrait;
    use Controller_TicketReleaseTrait;
    use Controller_TicketReplyTrait;

    // Core Constants
    const MENU_ID = 2;
    const NAVIGATION_ID = 1;

    protected static $_resultType = 'next';
    protected static $_requireChanges = false;
    protected static $_sendEmail = true;
    protected static $_dispatchAutoResponder = true;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct(self::TYPE_STAFF);

        $this->Load->Library('CustomField:CustomFieldRendererStaff', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');

        SWIFT_Ticket::LoadLanguageTable();
    }

    /**
     * @return bool
     */
    public static function processUploadImage(): bool
    {
        $_SWIFT = SWIFT::GetInstance();
        // extract host from productUrl because it might contain a directory
        $_producturl      = rtrim($_SWIFT->Settings->Get('general_producturl'), '/');
        $_domainData      = parse_url($_producturl);
        $host             = $_domainData['scheme'] . '://' . $_domainData['host'];
        $accepted_origins = ['http://localhost', $host];
        $imageFolder      = '__swift/files/';

        reset($_FILES);
        $temp = current($_FILES);

        if (is_uploaded_file($temp['tmp_name'])) {
            if (isset($_SERVER['HTTP_ORIGIN'])) {
                // same-origin requests won't set an origin. If the origin is set, it must be valid.
                if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins, true)) {
                    @header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
                } else {
                    @header('HTTP/1.0 403 Origin Denied - ' . $_SERVER['HTTP_ORIGIN']);

                    return false;
                }
            }

            // Sanitize input
            if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
                @header('HTTP/1.0 400 Invalid file name.');

                return false;
            }

            // Verify extension
            if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), ['gif', 'jpg', 'png'])) {
                @header('HTTP/1.0 400 Invalid extension. - ' . pathinfo($temp['name'], PATHINFO_EXTENSION));

                return false;
            }

            if (!file_exists($temp['tmp_name'])) {
                return false;
            }

            // Accept upload if there was no origin, or if it is an accepted origin
            $filename    = time() . sha1_file($temp['tmp_name']);
            $filetowrite = $imageFolder . $filename . '.' . strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION));

            move_uploaded_file($temp['tmp_name'], $filetowrite);

            // Respond to the successful upload with JSON.
            // Use a location key to specify the path to the saved image resource.
            // { location : '/your/uploaded/image/file'}
            echo json_encode([
                'location' => $_SWIFT->Settings->Get('general_producturl') . '__swift/files/' . $filename . '.' . strtolower(pathinfo($temp['name'],
                        PATHINFO_EXTENSION)),
            ]);

            return true;
        }

        // Notify editor that the upload failed
        @header('HTTP/1.0 500 Server Error');

        return false;
    }

    /**
     * Retrieve an attachment
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_attachmentID The Attachment ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetAttachment($_ticketID, $_attachmentID) {

        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanviewtickets') == '0') {
            $this->UserInterface->Header($this->Language->Get('tickets') . ' > ' . $this->Language->Get('viewticket'), self::MENU_ID,
                    self::NAVIGATION_ID);
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
            $this->UserInterface->Footer();

            return false;

        }

        $_SWIFT_AttachmentObject = new SWIFT_Attachment($_attachmentID);
        // Did the object load up?
        if (!$_SWIFT_AttachmentObject instanceof SWIFT_Attachment || !$_SWIFT_AttachmentObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($_SWIFT_AttachmentObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT_AttachmentObject->Dispatch();

        return true;
    }

    /**
     * Render the Edit tab for this Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Edit($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
            $_ticketLimitOffset = 0) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT = SWIFT::GetInstance();

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;

        }

        $this->View->RenderEdit($_SWIFT_TicketObject, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Edit Tab Submission
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function EditSubmit($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
            $_ticketLimitOffset = 0) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanupdateticket') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;

        }

        /**
        * BUG FIX: Parminder Singh
        *
        * SWIFT-1745: Regular expression during editing custom field
        *
        * Comments: Added Custom Field Check
        */

        $_customFieldCheckResult = $this->CustomFieldManager->Check(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT,
                array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET), SWIFT_CustomFieldManager::CHECKMODE_STAFF, $_SWIFT_TicketObject->GetProperty('departmentid'));
        if ($_customFieldCheckResult[0] == false)
        {
            SWIFT::Alert($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

            $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

            return false;
        }

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-590 Changes made to the user details in Staff CP are not effective (even though the Staff CP shows the updated information)
         * SWIFT-953 User Organization does not get updated while changing the user details in ticket
         *
         * Comments: Changing the original fix because it did not have a provision for creating users in case one didnt exist.
         */
        $_userGroupID = SWIFT_UserGroup::RetrieveDefaultUserGroupID(SWIFT_UserGroup::TYPE_REGISTERED);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1434 Editing and adding a space in the ticket recipient field of a ticket, help desk fails to read the address and shows a blank page while reply
         *
         * Comments: None
         */
        $_editEmail = trim($_POST['editemail']);
        if (!IsEmailValid($_editEmail)) {
            $_editEmail = $_SWIFT_TicketObject->GetProperty('email');
        }

        // Update Properties
        $_SWIFT_TicketObject->Update($_POST['editsubject'], $_POST['editfullname'], $_editEmail);

        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-2759: Error when editing tickets and further bug with creating user accounts
         *
         * Comments: None
         */
        $_userID = SWIFT_Ticket::GetOrCreateUserID($_editEmail, $_editEmail, $_userGroupID);

        /*
         * BUG FIX - Varun Shoor
         *
         * SWIFT-1864 After editing the full name of a ticket from the Edit tab, it still shows the old full name of user on ticket.
         *
         */
        SWIFT_TicketPost::UpdateFullnameAndEmailOnTicketUser($_SWIFT_TicketObject->GetTicketID(), $_SWIFT_TicketObject->GetProperty('userid'), $_userID, $_POST['editfullname'], $_editEmail);

        if (!empty($_userID) && $_userID != $_SWIFT_TicketObject->GetProperty('userid')) {
            $_SWIFT_TicketObject->UpdateUser($_userID);
        }

        // Update the Ticket SLA Plan
        if ($_POST['editticketslaplanid'] != $_SWIFT_TicketObject->GetProperty('ticketslaplanid') && $_POST['editticketslaplanid'] != '0') {
            try {
                $_SWIFT_SLAPlanObject = new SWIFT_SLA(new SWIFT_DataID($_POST['editticketslaplanid']));

                $_SWIFT_TicketObject->SetSLA($_SWIFT_SLAPlanObject);
            } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

                return false;
            }
        } else if ($_POST['editticketslaplanid'] == '0' && $_SWIFT_TicketObject->GetProperty('ticketslaplanid') != '0') {
            $_SWIFT_TicketObject->ClearSLA();
        }

        // Update Recipients
        SWIFT_TicketRecipient::DeleteOnTicket(array($_SWIFT_TicketObject->GetTicketID()));

        $_thirdPartyEmailContainer = self::GetSanitizedEmailList('editthirdparty');
        $_ccEmailContainer = self::GetSanitizedEmailList('editcc');
        $_bccEmailContainer = self::GetSanitizedEmailList('editbcc');

        if (_is_array($_thirdPartyEmailContainer)) {
            SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_THIRDPARTY, $_thirdPartyEmailContainer);
        }

        if (_is_array($_ccEmailContainer)) {
            SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_CC, $_ccEmailContainer);
        }

        if (_is_array($_bccEmailContainer)) {
            SWIFT_TicketRecipient::Create($_SWIFT_TicketObject, SWIFT_TicketRecipient::TYPE_BCC, $_bccEmailContainer);
        }

        // Update Custom Field Values
        $this->CustomFieldManager->Update(SWIFT_CustomFieldManager::MODE_POST, SWIFT_UserInterface::MODE_EDIT,
                array(SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, SWIFT_CustomFieldGroup::GROUP_USERTICKET, SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET),
                SWIFT_CustomFieldManager::CHECKMODE_STAFF, $_SWIFT_TicketObject->GetTicketID(), $_SWIFT_TicketObject->GetProperty('departmentid'));

        $_SWIFT_TicketObject->ProcessUpdatePool();

        /*
        * BUG FIX - Ravi Sharma
        *
        * SWIFT-3212 Due time resets on updating the ticket from edit tab
        *
        * Comments: None
        */
        if ($_SWIFT_TicketObject->GetProperty('isresolved') != '1')
        {
            /*
             * Bug Fix - Mansi Wason
             *
             * SWIFT- 3835 "Incorrect SLA plan gets linked with the ticket when ticket is moved from a resolved status to unresolved status."
             *
             * comment - By clicking on 'Edit' tab and without making any changes if we 'Update' the ticket. This will lead to reset of the 'Reply Due' time which should not be the case.
             */

            $_SWIFT_TicketObject->ExecuteSLA(false, true, false);
        }

        // Do we have to merge it?
        if (!empty($_POST['mergeticketid'])) {
            $_SWIFT_TicketObject_Merge = false;
            $_POST['mergeticketid'] = Clean($_POST['mergeticketid']);
            if (is_numeric($_POST['mergeticketid'])) {
                $_SWIFT_TicketObject_Merge = new SWIFT_Ticket(new SWIFT_DataID($_POST['mergeticketid']));
            } else {
                $_ticketID_Merge = SWIFT_Ticket::GetTicketIDFromMask($_POST['mergeticketid']);
                if (!empty($_ticketID_Merge)) {
                    $_SWIFT_TicketObject_Merge = new SWIFT_Ticket(new SWIFT_DataID($_ticketID_Merge));
                }
            }

            if ($_SWIFT_TicketObject_Merge instanceof SWIFT_Ticket && $_SWIFT_TicketObject_Merge->GetIsClassLoaded()) {
                SWIFT_Ticket::Merge(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT_TicketObject_Merge->GetTicketID(), $_SWIFT->Staff->GetStaffID());
                SWIFT::Info($this->Language->Get('titleeditmergesuccess'), $this->Language->Get('msgeditmergesuccess'));

                $_ticketID = $_SWIFT_TicketObject_Merge->GetTicketID();
                $_departmentID = $_SWIFT_TicketObject_Merge->GetProperty('departmentid');
                $_ticketStatusID = $_SWIFT_TicketObject_Merge->GetProperty('ticketstatusid');
                $_ticketTypeID = $_SWIFT_TicketObject_Merge->GetProperty('tickettypeid');
                $_ticketLimitOffset = -1;
            } else {
                SWIFT::Alert($this->Language->Get('titleeditmergefailed'), $this->Language->Get('msgeditmergefailed'));
            }
        }

        // Begin Hook: staff_ticket_edit
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_ticket_edit')) ? eval($_hookCode) : false;
        // End Hook

        SWIFT_TicketManager::RebuildCache();

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2392: While adding more than one CC email address in a ticket, CC users list do not get updated in ticket
         *
         * Comments: $_POST, $_REQUEST and $_GET contains old values so making them empty
         */
        $GLOBALS['_POST'] = $GLOBALS['_REQUEST'] = $GLOBALS['_GET'] = array();
        $GLOBALS['_POST']['isajax'] = true;

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Retrieve the Sanitized Email List
     *
     * @author Varun Shoor
     *
     * @param string $_fieldName The Field Name
     * @param bool   $_isCheckbox
     *
     * @return array
     */
    protected static function GetSanitizedEmailList($_fieldName, $_isCheckbox = false) {
        $_SWIFT = SWIFT::GetInstance();

        $_emailContainer = array();

        /*
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-2416 Special characters are filtered out, while specifying email address (with special characters) in the 'To' field..
         */
        $_postEmailValues = SWIFT_UserInterface::GetMultipleInputValues($_fieldName, $_isCheckbox, true);
        if (_is_array($_postEmailValues)) {
            foreach ($_postEmailValues as $_key => $_val) {
                if (IsEmailValid($_val)) {
                    $_emailContainer[] = $_val;
                }
            }
        }
        return $_emailContainer;
    }

    /**
     * Render the Audit Log for this Ticket
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function AuditLog($_ticketID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanviewauditlog') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;

        }

        $this->View->RenderAuditLog($_SWIFT_TicketObject);

        return true;
    }

    /**
     * Execute a workflow action
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketWorkflowID The Ticket Workflow ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ExecuteWorkflow($_ticketID, $_ticketWorkflowID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1,
            $_ticketTypeID = -1, $_ticketLimitOffset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcanworkflow') == '0') {
            return false;
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        // Attempt to process workflow
        $_wfresult = SWIFT_TicketWorkflow::ProcessWorkflow($_SWIFT_TicketObject, $_ticketWorkflowID, static::$_requireChanges);

        if ($_wfresult) {
            // Update Custom Field Values
            $this->CustomFieldManager->UpdateForTicket($_ticketWorkflowID, [SWIFT_CustomFieldGroup::GROUP_USER], (int)$_SWIFT_TicketObject->GetProperty('userid'));
            $this->CustomFieldManager->UpdateForTicket($_ticketWorkflowID, [SWIFT_CustomFieldGroup::GROUP_STAFFTICKET,
                SWIFT_CustomFieldGroup::GROUP_USERTICKET,
                SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET,
            ], $_SWIFT_TicketObject->GetTicketID());
        }

        // Trash?
        if ($_SWIFT_TicketObject->GetProperty('departmentid') == '0') {
            $_departmentID = 0;
            $_ticketStatusID = -1;
            $_ticketTypeID = -1;

        // Department Changed?
        } else if ($_SWIFT_TicketObject->GetProperty('departmentid') != $_departmentID && $_departmentID != -1) {
            $_departmentID = (int) ($_SWIFT_TicketObject->GetProperty('departmentid'));
            $_ticketStatusID = -1;
            $_ticketTypeID = -1;
        }

        $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);

        return true;
    }

    /**
     * Ticket Save as Draft
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param string $_listType (OPTIONAL) The List Type
     * @param int $_departmentID (OPTIONAL) The Department ID
     * @param int $_ticketStatusID (OPTIONAL) The Ticket Status ID
     * @param int $_ticketTypeID (OPTIONAL) The Ticket Type ID
     * @param int $_ticketLimitOffset (OPTIONAL) The offset to display ticket posts on
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SaveAsDraft($_ticketID, $_listType = 'inbox', $_departmentID = -1, $_ticketStatusID = -1, $_ticketTypeID = -1,
            $_ticketLimitOffset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_ticketID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if ($_SWIFT->Staff->GetPermission('staff_tcansaveasdraft') == '0') {
            return false;
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff)) {
            echo $this->Language->Get('msgnoperm');

            return false;
        }

        $_SWIFT_TicketViewObject = SWIFT_TicketViewRenderer::GetTicketViewObject($_departmentID);

        $_nextTicketID = false;
        if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
            $_nextTicketID = SWIFT_TicketViewRenderer::GetNextPreviousTicketID($_SWIFT_TicketObject, static::$_resultType, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
        }

        $this->_ProcessDispatchTab($_SWIFT_TicketObject, 'reply');

        /**
         * BUG FIX - Verem Dugeri <verem.dugeri@crossover.com>
         *
         * KAYAKO-4907 - Remove vulnerable tags from post content
         *
         * Comments - None
         */
        $_replyContents = removeTags($_POST['replycontents']);

        // Save as Draft
        if (trim($_replyContents) === '')
        {
            SWIFT_TicketDraft::DeleteOnTicket(array($_SWIFT_TicketObject->GetTicketID()));
            $_SWIFT_TicketObject->ClearHasDraft();
        } else {

            if ($_SWIFT->Settings->Get('t_tinymceeditor') != '0') {
                $_replyContents = htmlspecialchars_decode($_replyContents);
            }

            SWIFT_TicketDraft::CreateIfNotExists($_SWIFT_TicketObject, $_SWIFT->Staff, $_replyContents);
        }

        if (isset($_POST['optreply_watch']) && $_POST['optreply_watch'] == '1')
        {
            SWIFT_Ticket::Watch(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff);
        } else {
            SWIFT_Ticket::UnWatch(array($_SWIFT_TicketObject->GetTicketID()), $_SWIFT->Staff);
        }

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        // Does the new department belong to this staff? if not, we need to jump him back to list!
        $_assignedDepartmentIDList = $_SWIFT->Staff->GetAssignedDepartments();
        if (isset($_POST['replydepartmentid']) && !empty($_POST['replydepartmentid']) && !in_array($_POST['replydepartmentid'], $_assignedDepartmentIDList)) {
            $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);

            return true;
        }

        if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_TICKET)
        {
            $this->Load->Method('View', $_ticketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
        } else if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_ACTIVETICKETLIST) {
            $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);

        } else if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_TOPTICKETLIST) {
            $this->Load->Controller('Manage')->Redirect('inbox', -1, -1, -1);

        } else if ($_SWIFT_TicketViewObject->GetProperty('afterreplyaction') == SWIFT_TicketView::AFTERREPLY_NEXTTICKET) {
            if (!empty($_nextTicketID))
            {
                $this->Load->Method('View', $_nextTicketID, $_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID, $_ticketLimitOffset);
            } else {
                $this->Load->Controller('Manage')->Redirect($_listType, $_departmentID, $_ticketStatusID, $_ticketTypeID);
            }

        }

        return true;
    }

    /**
     * Dispatch data for quoting a reply
     *
     * @author Varun Shoor
     * @param int $_ticketID The Ticket ID
     * @param int $_ticketPostID The Ticket Post ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetQuote($_ticketID, $_ticketPostID) {

        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2815: Uncaught Exception: Invalid data provided in ./__apps/tickets/models/Ticket/class.SWIFT_Ticket.php:409
         *
         * Comments: If current ticket id is merged with other tickets
         */
        $_SWIFT_TicketObject = SWIFT_Ticket::GetObjectOnID($_ticketID);

        // Did the object load up?
        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Check permission
        if (!$_SWIFT_TicketObject->CanAccess($_SWIFT->Staff) || $_SWIFT->Staff->GetPermission('staff_tcanviewtickets') == '0') {
            echo $this->Language->Get('msgnoperm');

            return false;

        }

        $_SWIFT_TicketPostObject = new SWIFT_TicketPost(new SWIFT_DataID($_ticketPostID));
        // Did the object load up?
        if (!$_SWIFT_TicketPostObject instanceof SWIFT_TicketPost || !$_SWIFT_TicketPostObject->GetIsClassLoaded()) {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if ($_SWIFT_TicketPostObject->GetProperty('ticketid') != $_SWIFT_TicketObject->GetTicketID()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        echo $_SWIFT_TicketPostObject->GetQuoteContents();

        return true;
    }

    /**
     * Upload the image
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @return bool
     */
    public function UploadImage(): bool
    {
        return self::processUploadImage();
    }

    /**
     * Checks for valid input for attachments
     *
     * @param string $_finalFieldName
     * @return array|bool
     * @throws SWIFT_Exception
     * @author Werner Garcia
     *
     */
    public static function CheckForValidAttachments(string $_finalFieldName = 'ticketattachments')
    {
        $_SWIFT = SWIFT::GetInstance();

        // If its coming from support center and we cant find anything then return true
        if (!isset($_FILES[$_finalFieldName]) || !_is_array($_FILES[$_finalFieldName]) || !_is_array($_FILES[$_finalFieldName]['name'])) {
            return true;
        }

        $_fileTypeCache    = $_SWIFT->Cache->Get('filetypecache');
        $_fileTypeCacheMap = array();

        // Do we need to sanitize the data?
        if ($_SWIFT->Settings->Get('tickets_resattachments') == '0') {
            return true;
        }

        // Sanitize the data.. do we need to sanitize the data?
        foreach ($_fileTypeCache as $_ticketFileTypeID => $_ticketFileTypeContainer) {
            $_fileTypeCacheMap[mb_strtolower($_ticketFileTypeContainer['extension'])] = $_ticketFileTypeContainer;
        }

        $_resultArray = array();
        $_result      = true;

        // Check the attachments
        foreach ($_FILES[$_finalFieldName]['name'] as $_fileIndex => $_fileName) {
            $_fileExtension = mb_strtolower(substr($_fileName, (strrpos($_fileName, '.') + 1)));

            // Extension isnt added in the list? || Check whether we can accept it from support center? || Invalid File Size?
            if (!isset($_fileTypeCacheMap[$_fileExtension]) ||
                ($_fileTypeCacheMap[$_fileExtension]['maxsize'] != '0' && ($_FILES[$_finalFieldName]['size'][$_fileIndex] / 1024) >= $_fileTypeCacheMap[$_fileExtension]['maxsize'])
            ) {
                $_result        = false;
                $_resultArray[] = $_fileName;
            }
        }

        return array($_result, $_resultArray);
    }
}
