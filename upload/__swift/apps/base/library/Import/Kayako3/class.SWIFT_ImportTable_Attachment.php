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

namespace Base\Library\Import\QuickSupport3;

use Base\Models\Attachment\SWIFT_Attachment;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;

/**
 * Import Table: Attachment
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_Attachment extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'Attachment');

        if (!$this->TableExists(TABLE_PREFIX . 'attachments')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Varun Shoor
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "attachments ORDER BY attachmentid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            // define("ATTACHMENT_DB", 1);
            // define("ATTACHMENT_FILE", 2);
            // define("ATTACHMENT_DL", 3);

            if ($this->DatabaseImport->Record['attachmenttype'] == '3') {
                $this->GetImportManager()->AddToLog('Ignoring Ticket Attachment as it has a link to download item: ' . htmlspecialchars($this->DatabaseImport->Record['attachmentid']), SWIFT_ImportManager::LOG_SUCCESS);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Ticket Attachment ID: ' . htmlspecialchars($this->DatabaseImport->Record['attachmentid']), SWIFT_ImportManager::LOG_SUCCESS);

            $_attachmentType = false;
            if ($this->DatabaseImport->Record['attachmenttype'] == '1') {
                $_attachmentType = SWIFT_Attachment::TYPE_DATABASE;
            } else {
                $_attachmentType = SWIFT_Attachment::TYPE_FILE;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'attachments',
                array('linktype' => SWIFT_Attachment::LINKTYPE_TICKETPOST, 'linktypeid' => $this->DatabaseImport->Record['ticketpostid'],
                    'downloaditemid' => '0', 'ticketid' => $this->DatabaseImport->Record['ticketid'], 'filename' => $this->DatabaseImport->Record['filename'],
                    'filesize' => $this->DatabaseImport->Record['filesize'], 'filetype' => $this->DatabaseImport->Record['filetype'], 'dateline' => $this->DatabaseImport->Record['dateline'],
                    'attachmenttype' => $_attachmentType, 'storefilename' => $this->DatabaseImport->Record['storefilename']
                ), 'INSERT');
            $_attachmentID = $this->Database->Insert_ID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('attachment', $this->DatabaseImport->Record['attachmentid'], $_attachmentID, true);
        }

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Varun Shoor
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "attachments");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Varun Shoor
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 1000;
    }
}

?>
