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
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\Import\QuickSupport3;

use SWIFT_DataStore;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * Import Table: TicketRebuild
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TicketRebuild extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TicketRebuild');

        if (!$this->TableExists(TABLE_PREFIX . 'tickets')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
        SWIFT_Loader::LoadLibrary('Ticket:TicketManager', APP_TICKETS);
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

        $_ticketObjectContainer = array();

        $this->DatabaseImport->QueryLimit("SELECT ticketid FROM " . TABLE_PREFIX . "tickets ORDER BY ticketid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;
        }

        $this->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "tickets ORDER BY ticketid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->Database->NextRecord()) {
            $_ticketObjectContainer[$this->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($this->Database->Record));
        }

        foreach ($_ticketObjectContainer as $_ticketID => $_SWIFT_TicketObject) {
            $this->GetImportManager()->AddToLog('Rebuilding Ticket Properties for Ticket ID: ' . htmlspecialchars($_SWIFT_TicketObject->GetTicketDisplayID()), SWIFT_ImportManager::LOG_SUCCESS);

            $_SWIFT_TicketObject->RebuildProperties();

            $_SWIFT_TicketObject->ProcessUpdatePool();
        }

        SWIFT_TicketManager::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "tickets");
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
