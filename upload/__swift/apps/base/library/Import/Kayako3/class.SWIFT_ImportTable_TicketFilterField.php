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

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Base\Library\Rules\SWIFT_Rules;
use Tickets\Library\Search\SWIFT_TicketSearch;
use Tickets\Models\Filter\SWIFT_TicketFilter;

/**
 * Import Table: TicketFilterField
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_TicketFilterField extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'TicketFilterField');

        if (!$this->TableExists(TABLE_PREFIX . 'ticketfilterfields')) {
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

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketfilterfields ORDER BY ticketfilterfieldid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newTicketFilterID = $this->ImportManager->GetImportRegistry()->GetKey('ticketfilter', $this->DatabaseImport->Record['ticketfilterid']);
            if (empty($_newTicketFilterID)) {
                $this->GetImportManager()->AddToLog('Importing of Ticket Filter Field failed due to non Ticket Filter: ' . htmlspecialchars($this->DatabaseImport->Record['ticketfilterfieldid']) . ' (Probable Explanation: Incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            /*
            define("OP_EQUAL", 1);
            define("OP_NOTEQUAL", 2);
            define("OP_REGEXP", 3);
            define("OP_CONTAINS", 4);
            define("OP_NOTCONTAINS", 5);
            define("OP_GREATER", 6);
            define("OP_LESS", 7);
            */

            $_fieldOper = SWIFT_Rules::OP_EQUAL;
            if ($this->DatabaseImport->Record['fieldoper'] == '2') {
                $_fieldOper = SWIFT_Rules::OP_NOTEQUAL;
            } elseif ($this->DatabaseImport->Record['fieldoper'] == '3') {
                $_fieldOper = SWIFT_Rules::OP_REGEXP;
            } elseif ($this->DatabaseImport->Record['fieldoper'] == '4') {
                $_fieldOper = SWIFT_Rules::OP_CONTAINS;
            } elseif ($this->DatabaseImport->Record['fieldoper'] == '5') {
                $_fieldOper = SWIFT_Rules::OP_NOTCONTAINS;
            } elseif ($this->DatabaseImport->Record['fieldoper'] == '6') {
                $_fieldOper = SWIFT_Rules::OP_GREATER;
            } elseif ($this->DatabaseImport->Record['fieldoper'] == '7') {
                $_fieldOper = SWIFT_Rules::OP_LESS;
            }

            $_fieldValue = $this->DatabaseImport->Record['fieldvalue'];

            $_rangeCheck = false;

            $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_SUBJECT;
            if ($this->DatabaseImport->Record['fieldtitle'] == 'subject') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_SUBJECT;
            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'contents') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_MESSAGELIKE;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'fullname') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_FULLNAME;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'email') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_EMAIL;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'departmentid') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_DEPARTMENT;
                $_fieldValue = $this->ImportManager->GetImportRegistry()->GetKey('department', $this->DatabaseImport->Record['fieldvalue']);

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'ticketstatusid') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_STATUS;
                $_fieldValue = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $this->DatabaseImport->Record['fieldvalue']);

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'priorityid') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_PRIORITY;
                $_fieldValue = $this->ImportManager->GetImportRegistry()->GetKey('ticketpriority', $this->DatabaseImport->Record['fieldvalue']);

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'tgroupid') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_TEMPLATEGROUP;
                $_fieldValue = $this->ImportManager->GetImportRegistry()->GetKey('templategroup', $this->DatabaseImport->Record['fieldvalue']);

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'ownerstaffid') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_OWNER;
                $_fieldValue = $this->ImportManager->GetImportRegistry()->GetKey('staff', $this->DatabaseImport->Record['fieldvalue']);

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'flagtype') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_FLAG;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'isescalated') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_ESCALATIONLEVELCOUNT;
                $_fieldOper = SWIFT_Rules::OP_GREATER;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'hasattachments') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_HASATTACHMENTS;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'hasnotes') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_HASNOTES;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'notes') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_TICKETNOTES;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'duetime') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_DUE;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'lastactivity') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_LASTACTIVITY;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'dateline') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_CREATIONDATE;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'slaplanid') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_SLAPLAN;
                $_fieldValue = $this->ImportManager->GetImportRegistry()->GetKey('slaplan', $this->DatabaseImport->Record['fieldvalue']);

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'isemailed') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_ISEMAILED;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'datelinerange') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_CREATIONDATERANGE;
                $_rangeCheck = true;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'lastactivityrange') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_LASTACTIVITYRANGE;
                $_rangeCheck = true;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'lastuserreplytime') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_LASTUSERREPLY;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'lastuserreplytimerange') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_LASTUSERREPLYRANGE;
                $_rangeCheck = true;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'laststaffreplytime') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_LASTSTAFFREPLY;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'laststaffreplytimerange') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_LASTSTAFFREPLYRANGE;
                $_rangeCheck = true;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'escalatedon') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_ESCALATEDDATE;

            } elseif ($this->DatabaseImport->Record['fieldtitle'] == 'escalatedonrange') {
                $_fieldTitle = SWIFT_TicketSearch::TICKETSEARCH_ESCALATEDDATERANGE;
                $_rangeCheck = true;

            } else {
                $this->GetImportManager()->AddToLog('Ignoring Unsupported Ticket Filter Field: ' . htmlspecialchars($this->DatabaseImport->Record['fieldtitle']) . '', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            if (empty($_fieldValue)) {
                $this->GetImportManager()->AddToLog('Ignoring Unsupported Ticket Filter Field: ' . htmlspecialchars($this->DatabaseImport->Record['fieldtitle']) . '', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            if ($_rangeCheck == true) {
                if ($_fieldValue == 'yesterday') {
                    $_fieldValue = SWIFT_Rules::DATERANGE_YESTERDAY;

                } elseif ($_fieldValue == 'today') {
                    $_fieldValue = SWIFT_Rules::DATERANGE_TODAY;

                } elseif ($_fieldValue == 'thisweek') {
                    $_fieldValue = SWIFT_Rules::DATERANGE_CURRENTWEEKTODATE;

                } elseif ($_fieldValue == 'thismonth') {
                    $_fieldValue = SWIFT_Rules::DATERANGE_CURRENTMONTHTODATE;

                } elseif ($_fieldValue == 'thisyear') {
                    $_fieldValue = SWIFT_Rules::DATERANGE_CURRENTYEARTODATE;

                } elseif ($_fieldValue == 'lastweek') {
                    $_fieldValue = SWIFT_Rules::DATERANGE_LAST7DAYS;

                } elseif ($_fieldValue == 'lastmonth') {
                    $_fieldValue = SWIFT_Rules::DATERANGE_LAST30DAYS;

                } elseif ($_fieldValue == 'lastthreemonths') {
                    $_fieldValue = SWIFT_Rules::DATERANGE_LAST90DAYS;

                } elseif ($_fieldValue == 'lastsixmonths') {
                    $_fieldValue = SWIFT_Rules::DATERANGE_LAST180DAYS;

                } elseif ($_fieldValue == 'lastyear') {
                    $_fieldValue = SWIFT_Rules::DATERANGE_LAST365DAYS;

                } else {
                    $_fieldValue = SWIFT_Rules::DATERANGE_TODAY;
                }
            }

            $this->GetImportManager()->AddToLog('Importing Ticket Filter Field: ' . htmlspecialchars($this->DatabaseImport->Record['ticketfilterfieldid']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'ticketfilterfields',
                array('ticketfilterid' => $_newTicketFilterID, 'dateline' => $this->DatabaseImport->Record['dateline'], 'fieldtitle' => $_fieldTitle,
                    'fieldoper' => $_fieldOper, 'fieldvalue' => $_fieldValue), 'INSERT');
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "ticketfilterfields");
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
