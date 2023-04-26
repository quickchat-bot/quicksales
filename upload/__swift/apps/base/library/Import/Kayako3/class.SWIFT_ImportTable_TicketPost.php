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

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * Import Table: TicketPost
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TicketPost extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TicketPost');

        if (!$this->TableExists(TABLE_PREFIX . 'ticketposts')) {
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

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketposts");
        }

        $_count = 0;

        $_ticketPostContainer = $_ticketPostIDList = $_oldUserIDList = $_ticketIDList = $_newTicketIDList = array();

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketposts ORDER BY ticketpostid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_ticketPostContainer[$this->DatabaseImport->Record['ticketpostid']] = $this->DatabaseImport->Record;
            $_ticketIDList[] = $this->DatabaseImport->Record['ticketid'];
            $_ticketPostIDList[] = $this->DatabaseImport->Record['ticketpostid'];

            if ($this->DatabaseImport->Record['userid'] != '0') {
                $_oldUserIDList[] = $this->DatabaseImport->Record['userid'];
            }
        }

        $_newUserIDList = $this->ImportManager->GetImportRegistry()->GetNonCached('user', $_oldUserIDList);

        $this->Database->Query("SELECT ticketid FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($this->Database->NextRecord()) {
            $_newTicketIDList[$this->Database->Record['ticketid']] = $this->Database->Record['ticketid'];
        }

        foreach ($_ticketPostContainer as $_ticketPostID => $_ticketPost) {
            if (!isset($_newTicketIDList[$_ticketPost['ticketid']])) {
                $this->GetImportManager()->AddToLog('Import for Ticket Post ID #: ' . (int)($_ticketPost['ticketpostid']) . ' failed due to non existant ticket id: ' . htmlspecialchars($_ticketPost['ticketid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);
                continue;
            }

            $_newUserID = 0;
            if (isset($_newUserIDList[$_ticketPost['userid']])) {
                $_newUserID = $_newUserIDList[$_ticketPost['userid']];
            }

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticketPost['staffid']);

            $_newEditedByStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $_ticketPost['editedbystaffid']);

            $_newCreator = SWIFT_TicketPost::CREATOR_CLIENT;
            $_isThirdParty = false;

            //define("POST_STAFF", 1);
            //define("POST_CLIENT", 2);
            //define("POST_THIRDPARTY", 3);
            //define("POST_RECIPIENT", 4);
            //define("POST_USER", 2);
            //define("POST_FORWARD", 5);
            if ($_ticketPost['creator'] == '3') {
                $_newCreator = SWIFT_TicketPost::CREATOR_THIRDPARTY;
                $_isThirdParty = true;
            } elseif ($_ticketPost['creator'] == '1') {
                $_newCreator = SWIFT_TicketPost::CREATOR_STAFF;
            } elseif ($_ticketPost['creator'] == '4') {
                $_newCreator = SWIFT_TicketPost::CREATOR_CC;
            }

            $this->GetImportManager()->AddToLog('Importing Ticket Post ID: ' . htmlspecialchars($_ticketPostID), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketposts',
                array('ticketpostid' => $_ticketPostID, 'ticketid' => $_ticketPost['ticketid'], 'dateline' => $_ticketPost['dateline'], 'userid' => $_newUserID,
                    'fullname' => $_ticketPost['fullname'], 'email' => $_ticketPost['email'], 'emailto' => $_ticketPost['emailto'], 'subject' => $_ticketPost['subject'],
                    'ipaddress' => $_ticketPost['ipaddress'], 'hasattachments' => $_ticketPost['hasattachments'], 'edited' => (int)($_ticketPost['edited']),
                    'editedbystaffid' => $_newEditedByStaffID, 'editeddateline' => $_ticketPost['editeddateline'], 'creator' => $_newCreator, 'isthirdparty' => $_isThirdParty,
                    'ishtml' => $_ticketPost['ishtml'], 'isemailed' => $_ticketPost['isemailed'], 'staffid' => $_newStaffID, 'contents' => $_ticketPost['contents'],
                    'contenthash' => $_ticketPost['contenthash'], 'subjecthash' => $_ticketPost['subjecthash']
                ), 'INSERT');
            $_ticketPostID = $this->Database->InsertID();

            if ($_ticketPostID != $_ticketPost['ticketpostid']) {
                $this->GetImportManager()->AddToLog('Import for Ticket Post ID #: ' . (int)($_ticketPost['ticketpostid']) . ' failed due to invalid autoincrement result, make sure that this desk is not live and no tickets are being created during the import process', SWIFT_ImportManager::LOG_FAILURE);
                break;
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketposts");
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
