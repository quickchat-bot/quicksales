<?php

namespace Base\Library\Import\Cerberus5;

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
class SWIFT_ImportTable_Pop3_account extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'Pop3_account');

        if (!$this->TableExists('pop3_account')) {
            $this->SetIsClassLoaded(false);
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

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "emailqueues");
        }

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

        $this->DatabaseImport->QueryLimit("SELECT * FROM pop3_account ORDER BY id ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;
            if (!SWIFT_EmailQueue::IsValidFetchType($this->DatabaseImport->Record['protocol'])) {
                $this->GetImportManager()->AddToLog('Email Queue import failed for "' . htmlspecialchars($this->DatabaseImport->Record['nickname']) . '" due to invalid fetch type', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_ticketStatusID = $_masterTicketStatusID;

            $_ticketPriorityID = $_masterTicketPriorityID;

            $_departmentID = $_masterTicketDepartmentID;

            $_templateGroupID = $_masterTemplateGroupID;

            $this->GetImportManager()->AddToLog('Importing Email Queue: ' . htmlspecialchars($this->DatabaseImport->Record['nickname']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'emailqueues',
                array('email' => $this->DatabaseImport->Record['nickname'], 'type' => APP_TICKETS, 'fetchtype' => $this->DatabaseImport->Record['protocol'],
                    'host' => $this->DatabaseImport->Record['host'], 'port' => $this->DatabaseImport->Record['port'], 'username' => $this->DatabaseImport->Record['username'],
                    'userpassword' => $this->DatabaseImport->Record['password'], 'tickettypeid' => $_masterTicketTypeID, 'priorityid' => $_ticketPriorityID,
                    'ticketstatusid' => $_ticketStatusID, 'departmentid' => $_departmentID,
                    'tgroupid' => $_templateGroupID, 'leavecopyonserver' => '0',
                    'usequeuesmtp' => '0', 'smtptype' => SWIFT_EmailQueueMailbox::SMTP_NONSSL, 'isenabled' => $this->DatabaseImport->Record['enabled']), 'INSERT');
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM pop3_account");
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
