<?php

namespace Base\Library\Import\DeskPRO;

use SWIFT_App;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use Parser\Models\EmailQueue\SWIFT_EmailQueueMailbox;
use Parser\Models\EmailQueue\SWIFT_EmailQueueSignature;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: EmailQueue
 *
 * @author Nicolás Ibarra Sabogal
 */
class SWIFT_ImportTable_Gateway_emails extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Nicolás Ibarra Sabogal
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'Gateway_emails');

        if (!$this->TableExists('gateway_emails')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('EmailQueue:EmailQueue', APP_PARSER);
        SWIFT_Loader::LoadModel('EmailQueue:EmailQueueMailbox', APP_PARSER);
        SWIFT_Loader::LoadModel('EmailQueue:EmailQueuePipe', APP_PARSER);
        SWIFT_Loader::LoadModel('EmailQueue:EmailQueueSignature', APP_PARSER);
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // definitions
        $_temp_autoreply = null;
        $_temp_replyautoresponder = null;

//        if ($this->GetOffset() == 0)
//        {
//            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "emailqueues");
//        }

        // Retrieve Master Template Group ID
        $_masterTemplateGroupID = false;
        $_masterTemplateGroupContainer = $this->Database->QueryFetch("SELECT tgroupid FROM " . TABLE_PREFIX . "templategroups WHERE ismaster = '1'");
        if (isset($_masterTemplateGroupContainer['tgroupid']) && !empty($_masterTemplateGroupContainer['tgroupid'])) {
            $_masterTemplateGroupID = (int)($_masterTemplateGroupContainer['tgroupid']);
        } else {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Retrieve Default Ticket Status ID
        $_masterTicketStatusID = false;
        if ($this->TableExists(TABLE_PREFIX . 'ticketstatus') && SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_masterTicketStatusContainer = $this->Database->QueryFetch("SELECT ticketstatusid FROM " . TABLE_PREFIX . "ticketstatus
                WHERE statustype = '" . SWIFT_PUBLIC . "' AND markasresolved = '0' ORDER BY displayorder ASC");
            if (isset($_masterTicketStatusContainer['ticketstatusid']) && !empty($_masterTicketStatusContainer['ticketstatusid'])) {
                $_masterTicketStatusID = (int)($_masterTicketStatusContainer['ticketstatusid']);
            }
        }

        // Retrieve Default Ticket Priority ID
        $_masterTicketPriorityID = false;
        if ($this->TableExists(TABLE_PREFIX . 'ticketpriorities') && SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_masterTicketPriorityContainer = $this->Database->QueryFetch("SELECT priorityid FROM " . TABLE_PREFIX . "ticketpriorities
                WHERE type = '" . SWIFT_PUBLIC . "' ORDER BY displayorder ASC");
            if (isset($_masterTicketPriorityContainer['priorityid']) && !empty($_masterTicketPriorityContainer['priorityid'])) {
                $_masterTicketPriorityID = (int)($_masterTicketPriorityContainer['priorityid']);
            }
        }

        // Retrieve Default Ticket Department ID
        $_masterTicketDepartmentID = false;
        $_masterTicketDepartmentContainer = $this->Database->QueryFetch("SELECT departmentid FROM " . TABLE_PREFIX . "departments
            WHERE departmentapp = '" . APP_TICKETS . "' ORDER BY departmentid ASC");
        if (isset($_masterTicketDepartmentContainer['departmentid']) && !empty($_masterTicketDepartmentContainer['departmentid'])) {
            $_masterTicketDepartmentID = (int)($_masterTicketDepartmentContainer['departmentid']);
        }

        // Retrieve Default Ticket Type ID
        $_masterTicketTypeID = false;
        $_masterTicketTypeContainer = $this->Database->QueryFetch("SELECT tickettypeid FROM " . TABLE_PREFIX . "tickettypes
            WHERE ismaster = '1' ORDER BY tickettypeid ASC");
        if (isset($_masterTicketTypeContainer['tickettypeid']) && !empty($_masterTicketTypeContainer['tickettypeid'])) {
            $_masterTicketTypeID = (int)($_masterTicketTypeContainer['tickettypeid']);
        }

        $_count = 0;

        $_temp_departmentid = 1;
        $_temp_ticketpriorityid = 1;
        $_temp_ticketstatusid = 1;
        $this->DatabaseImport->QueryLimit("SELECT * FROM gateway_emails ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $this->DatabaseImport->QueryLimit("SELECT * FROM ticket_rules_mail WHERE accountid = " . (int)($this->DatabaseImport->Record['id']) . " ORDER BY id ASC", 1, 0, 2);
            while ($this->DatabaseImport->NextRecord(2)) {
//                Get the email queue actions
                $_actions = unserialize($this->DatabaseImport->Record2['actions']);

                $_temp_departmentid = $_actions['category'];

                if (!empty($_actions['priority'])) {
                    $_temp_ticketpriorityid = $_actions['priority'];
                }

                if (!empty($_actions['workflow'])) {
                    $_temp_ticketstatusid = $_actions['workflow'];
                }

                $_temp_autoreply = $this->DatabaseImport->Record2['auto_new'];
                $_temp_replyautoresponder = $this->DatabaseImport->Record2['auto_reply'];

            }

            $_ticketStatusID = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $_temp_ticketstatusid);
            if ($_ticketStatusID == false) {
                $_ticketStatusID = $_masterTicketStatusID;
            }

            $_ticketPriorityID = $this->ImportManager->GetImportRegistry()->GetKey('ticketpriority', $_temp_ticketpriorityid);
            if ($_ticketPriorityID == false) {
                $_ticketPriorityID = $_masterTicketPriorityID;
            }

            $_departmentID = $this->ImportManager->GetImportRegistry()->GetKey('department', $_temp_departmentid);
            if ($_departmentID == false) {
                $_departmentID = $_masterTicketDepartmentID;
            }

//            $_templateGroupID = $this->ImportManager->GetImportRegistry()->GetKey('templategroup', $this->DatabaseImport->Record['tgroupid']);
//            if ($_templateGroupID == false)
//            {
            $_templateGroupID = $_masterTemplateGroupID;
//            }

            $this->GetImportManager()->AddToLog('Importing Email Queue: ' . htmlspecialchars($this->DatabaseImport->Record['email']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'emailqueues',
                array('email' => $this->DatabaseImport->Record['email'], 'type' => APP_TICKETS, 'fetchtype' => 'pipe',
                    'host' => '', 'port' => '', 'username' => '',
                    'userpassword' => '', 'customfromname' => $this->DatabaseImport->Record['name'],
                    'customfromemail' => $this->DatabaseImport->Record['email'], 'tickettypeid' => $_masterTicketTypeID, 'priorityid' => $_ticketPriorityID,
                    'ticketstatusid' => $_ticketStatusID, 'departmentid' => $_departmentID, 'prefix' => '',
                    'ticketautoresponder' => (int)($_temp_autoreply), 'replyautoresponder' => (int)($_temp_replyautoresponder),
                    'registrationrequired' => '0', 'tgroupid' => $_templateGroupID, 'leavecopyonserver' => '0',
                    'usequeuesmtp' => '0', 'smtptype' => SWIFT_EmailQueueMailbox::SMTP_NONSSL, 'isenabled' => '1'), 'INSERT');
            $_emailQueueID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('emailqueue', $this->DatabaseImport->Record['id'], $_emailQueueID);

            SWIFT_EmailQueueSignature::Create($_emailQueueID, '');
        }

        SWIFT_EmailQueue::RebuildCache();

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM gateway_emails");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Nicolás Ibarra Sabogal
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 500;
    }
}

?>
