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

namespace Base\Library\Import\Kayako3;

use Base\Models\User\SWIFT_UserNoteManager;
use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Tickets\Models\Note\SWIFT_TicketNoteManager;

/**
 * Import Table: TicketNote
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TicketNote extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TicketNote');

        if (!$this->TableExists(TABLE_PREFIX . 'ticketnotes')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);
        SWIFT_Loader::LoadModel('Ticket:TicketPost', APP_TICKETS);
        SWIFT_Loader::LoadModel('Note:TicketNoteManager', APP_TICKETS);
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

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketnotes");
        }

        $_staffCache = $this->Cache->Get('staffcache');

        $_count = 0;

        $_ticketNoteContainer = $_oldUserIDList = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketnotes ORDER BY ticketnoteid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            if ($this->DatabaseImport->Record['notetype'] == '2' && $this->DatabaseImport->Record['typeid'] != '0') {
                $_oldUserIDList[] = $this->DatabaseImport->Record['typeid'];
            }

            $_ticketNoteContainer[$this->DatabaseImport->Record['ticketnoteid']] = $this->DatabaseImport->Record;
        }

        $_newUserIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('user', $_oldUserIDList);

        foreach ($_ticketNoteContainer as $_ticketNoteID => $_ticketNote) {
            $this->GetImportManager()->AddToLog('Importing Ticket Note ID: ' . htmlspecialchars($_ticketNoteID), SWIFT_ImportManager::LOG_SUCCESS);

            $_newByStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticketNote['bystaffid']);
            $_staffFullName = $this->Language->Get('na');
            if (isset($_staffCache[$_newByStaffID])) {
                $_staffFullName = $_staffCache[$_newByStaffID]['fullname'];
            }

            $_newForStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticketNote['forstaffid']);

            $_newUserID = 0;
            if ($_ticketNote['notetype'] == '2' && isset($_newUserIDList[$_ticketNote['typeid']])) {
                $_newUserID = $_newUserIDList[$_ticketNote['typeid']];
            }

            // define("NOTE_TICKET", 1);
            // define("NOTE_USER", 2);

            if ($_ticketNote['notetype'] == '1') {
                $this->Database->AutoExecute(TABLE_PREFIX . 'ticketnotes',
                    array('linktypeid' => $_ticketNote['typeid'], 'linktype' => SWIFT_TicketNoteManager::LINKTYPE_TICKET, 'forstaffid' => $_newForStaffID,
                        'staffid' => $_newByStaffID, 'dateline' => $_ticketNote['dateline'], 'isedited' => '0', 'staffname' => $_staffFullName, 'editedstaffid' => '0',
                        'editedstaffname' => '0', 'editedtimeline' => '0', 'notecolor' => '1', 'note' => html_entity_decode($_ticketNote['notes'], ENT_COMPAT, 'UTF-8')), 'INSERT');
            } elseif ($_ticketNote['notetype'] == '2') {
                if (empty($_newUserID)) {
                    $this->GetImportManager()->AddToLog('Ignoring Ticket Note ID: ' . htmlspecialchars($_ticketNoteID) . ' due to non existant user. (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);
                } else {
                    $this->Database->AutoExecute(TABLE_PREFIX . 'usernotes',
                        array('linktypeid' => $_newUserID, 'linktype' => SWIFT_UserNoteManager::LINKTYPE_USER, 'dateline' => $_ticketNote['dateline'], 'lastupdated' => '0',
                            'isedited' => '0', 'staffid' => $_newByStaffID, 'staffname' => $_staffFullName, 'notecolor' => '1', 'editedstaffid' => '0', 'editedstaffname' => '',
                            'editedtimeline' => '0'), 'INSERT');

                    $_userNoteID = $this->Database->Insert_ID();
                    $this->Database->AutoExecute(TABLE_PREFIX . 'usernotedata',
                        array('usernoteid' => $_userNoteID, 'notecontents' => html_entity_decode($_ticketNote['notes'], ENT_COMPAT, 'UTF-8')), 'INSERT');
                }
            }

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketnotes");
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
