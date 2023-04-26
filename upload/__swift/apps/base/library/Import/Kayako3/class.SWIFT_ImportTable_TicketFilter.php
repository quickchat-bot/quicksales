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

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Tickets\Library\Search\SWIFT_TicketSearch;
use Tickets\Models\Filter\SWIFT_TicketFilter;

/**
 * Import Table: TicketFilter
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TicketFilter extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TicketFilter');

        if (!$this->TableExists(TABLE_PREFIX . 'ticketfilters')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadLibrary('Search:TicketSearch', APP_TICKETS);
        SWIFT_Loader::LoadModel('Filter:TicketFilter', APP_TICKETS);
        SWIFT_Loader::LoadModel('Filter:TicketFilterField', APP_TICKETS);
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

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketfilters ORDER BY ticketfilterid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newStaffID = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['staffid']);

            $_matchType = SWIFT_TicketSearch::RULE_MATCHALL;
            if ($this->DatabaseImport->Record['filteroptions'] == 'any') {
                $_matchType = SWIFT_TicketSearch::RULE_MATCHANY;
            }

            $_filterType = SWIFT_TicketFilter::TYPE_PUBLIC;
            if ($this->DatabaseImport->Record['filtertype'] == 'private') {
                $_filterType = SWIFT_TicketFilter::TYPE_PRIVATE;
            }

            if ($_filterType == SWIFT_TicketFilter::TYPE_PRIVATE && empty($_newStaffID)) {
                $this->GetImportManager()->AddToLog('Importing of Private Ticket Filter failed due to non existant Staff: ' . htmlspecialchars($this->DatabaseImport->Record['title']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $this->GetImportManager()->AddToLog('Importing Ticket Filter: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketfilters',
                array('dateline' => $this->DatabaseImport->Record['dateline'], 'lastactivity' => '0', 'filtertype' => $_filterType, 'title' => $this->DatabaseImport->Record['title'],
                    'staffid' => $_newStaffID, 'restrictstaffgroupid' => '0', 'criteriaoptions' => $_matchType), 'INSERT');
            $_ticketFilterID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('ticketfilter', $this->DatabaseImport->Record['ticketfilterid'], $_ticketFilterID);
        }

        SWIFT_TicketFilter::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketfilters");
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

        return 500;
    }
}

?>
