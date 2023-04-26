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
use Tickets\Models\SLA\SWIFT_SLASchedule;

/**
 * Import Table: SLASchedule
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_SLASchedule extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'SLASchedule');

        if (!$this->TableExists(TABLE_PREFIX . 'slaschedules')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('SLA:SLASchedule', APP_TICKETS);
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "slaschedules");
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "slascheduletable");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "slaschedules ORDER BY slascheduleid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $this->GetImportManager()->AddToLog('Importing SLA Schedule: ' . htmlspecialchars($this->DatabaseImport->Record['title']), SWIFT_ImportManager::LOG_SUCCESS);

            $_sundayOpen = SWIFT_SLASchedule::SCHEDULE_DAYCLOSED;
            $_mondayOpen = SWIFT_SLASchedule::SCHEDULE_DAYCLOSED;
            $_tuesdayOpen = SWIFT_SLASchedule::SCHEDULE_DAYCLOSED;
            $_wednesdayOpen = SWIFT_SLASchedule::SCHEDULE_DAYCLOSED;
            $_thursdayOpen = SWIFT_SLASchedule::SCHEDULE_DAYCLOSED;
            $_fridayOpen = SWIFT_SLASchedule::SCHEDULE_DAYCLOSED;
            $_saturdayOpen = SWIFT_SLASchedule::SCHEDULE_DAYCLOSED;

            if ($this->DatabaseImport->Record['sunday_enabled'] == '1') {
                $_sundayOpen = SWIFT_SLASchedule::SCHEDULE_DAYOPEN;
            }

            if ($this->DatabaseImport->Record['monday_enabled'] == '1') {
                $_mondayOpen = SWIFT_SLASchedule::SCHEDULE_DAYOPEN;
            }

            if ($this->DatabaseImport->Record['tuesday_enabled'] == '1') {
                $_tuesdayOpen = SWIFT_SLASchedule::SCHEDULE_DAYOPEN;
            }

            if ($this->DatabaseImport->Record['wednesday_enabled'] == '1') {
                $_wednesdayOpen = SWIFT_SLASchedule::SCHEDULE_DAYOPEN;
            }

            if ($this->DatabaseImport->Record['thursday_enabled'] == '1') {
                $_thursdayOpen = SWIFT_SLASchedule::SCHEDULE_DAYOPEN;
            }

            if ($this->DatabaseImport->Record['friday_enabled'] == '1') {
                $_fridayOpen = SWIFT_SLASchedule::SCHEDULE_DAYOPEN;
            }

            if ($this->DatabaseImport->Record['saturday_enabled'] == '1') {
                $_saturdayOpen = SWIFT_SLASchedule::SCHEDULE_DAYOPEN;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'slaschedules',
                array('title' => $this->DatabaseImport->Record['title'], 'dateline' => $this->DatabaseImport->Record['dateline'],
                    'sunday_open' => $_sundayOpen, 'monday_open' => $_mondayOpen, 'tuesday_open' => $_tuesdayOpen,
                    'wednesday_open' => $_wednesdayOpen, 'thursday_open' => $_thursdayOpen, 'friday_open' => $_fridayOpen,
                    'saturday_open' => $_sundayOpen), 'INSERT');
            $_slaScheduleID = $this->Database->InsertID();

            foreach (array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday') as $_slaDay) {
                $_variableName = '_' . $_slaDay . 'Open';
                if ($$_variableName != SWIFT_SLASchedule::SCHEDULE_DAYOPEN) {
                    continue;
                }

                $this->Database->AutoExecute(TABLE_PREFIX . 'slascheduletable',
                    array('sladay' => $_slaDay, 'slascheduleid' => $_slaScheduleID,
                        'opentimeline' => $this->DatabaseImport->Record[$_slaDay . '_open'], 'closetimeline' => $this->DatabaseImport->Record[$_slaDay . '_close']), 'INSERT');
            }

            $this->ImportManager->GetImportRegistry()->UpdateKey('slaschedule', $this->DatabaseImport->Record['slascheduleid'], $_slaScheduleID);
        }

        SWIFT_SLASchedule::RebuildCache();

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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "slaschedules");
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
