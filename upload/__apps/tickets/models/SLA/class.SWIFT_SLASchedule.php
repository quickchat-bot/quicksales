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

namespace Tickets\Models\SLA;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The SLA Schedule Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_SLASchedule extends SWIFT_Model
{
    const TABLE_NAME        =    'slaschedules';
    const PRIMARY_KEY        =    'slascheduleid';

    const TABLE_STRUCTURE    =    "slascheduleid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                sunday_open I2 DEFAULT '0' NOTNULL,
                                monday_open I2 DEFAULT '0' NOTNULL,
                                tuesday_open I2 DEFAULT '0' NOTNULL,
                                wednesday_open I2 DEFAULT '0' NOTNULL,
                                thursday_open I2 DEFAULT '0' NOTNULL,
                                friday_open I2 DEFAULT '0' NOTNULL,
                                saturday_open I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";


    protected $_dataStore = array();

    // Core Constants
    const SCHEDULE_SUNDAY = 1;
    const SCHEDULE_MONDAY = 2;
    const SCHEDULE_TUESDAY = 3;
    const SCHEDULE_WEDNESDAY = 4;
    const SCHEDULE_THURSDAY = 5;
    const SCHEDULE_FRIDAY = 6;
    const SCHEDULE_SATURDAY = 7;

    const SCHEDULE_DAYCLOSED = 0;
    const SCHEDULE_DAYOPEN = 1;
    const SCHEDULE_DAYOPEN24 = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_slaScheduleID The SLA Schedule ID
     * @throws SWIFT_Exception
     */
    public function __construct($_slaScheduleID)
    {
        parent::__construct();

        if (!$this->LoadData($_slaScheduleID))
        {
            $this->SetIsClassLoaded(false);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        $this->ProcessUpdatePool();

        parent::__destruct();
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool()) || !$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'slaschedules', $this->GetUpdatePool(), 'UPDATE', "slascheduleid = '". (int) ($this->GetSLAScheduleID()) ."'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the SLA Schedule ID
     *
     * @author Varun Shoor
     * @return mixed "slascheduleid" on Success, "false" otherwise
     */
    public function GetSLAScheduleID()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_dataStore['slascheduleid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_slaScheduleID The SLA Schedule ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_slaScheduleID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM ". TABLE_PREFIX ."slaschedules WHERE slascheduleid = '". $_slaScheduleID ."'");
        if (isset($_dataStore['slascheduleid']) && !empty($_dataStore['slascheduleid']))
        {
            $this->_dataStore = $_dataStore;

            return true;
        }

        return false;
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded() || !isset($this->_dataStore[$_key]))
        {
            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Create a new SLA Schedule
     *
     * @author Varun Shoor
     * @param string $_title The SLA Schedule Title
     * @param array $_daysContainer The Days Container Table
     * @return mixed "_slaScheduleID" (INT) on Success, "false" otherwise
     */
    public static function Create($_title, $_daysContainer)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title) || !_is_array($_daysContainer))
        {
            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'slaschedules', array('title' => $_title, 'sunday_open' => $_daysContainer['sunday']['type'], 'monday_open' => $_daysContainer['monday']['type'], 'tuesday_open' => $_daysContainer['tuesday']['type'], 'wednesday_open' => $_daysContainer['wednesday']['type'], 'thursday_open' => $_daysContainer['thursday']['type'], 'friday_open' => $_daysContainer['friday']['type'], 'saturday_open' => $_daysContainer['saturday']['type'], 'dateline' => DATENOW), 'INSERT');
        $_slaScheduleID = $_SWIFT->Database->Insert_ID();
        if (!$_slaScheduleID)
        {
            return false;
        }

        if (_is_array($_daysContainer))
        {
            foreach ($_daysContainer as $_key => $_val)
            {
                if (_is_array($_val['hours']))
                {
                    foreach ($_val['hours'] as $_hourKey => $_hourVal)
                    {
                        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'slascheduletable', array('slascheduleid' => $_slaScheduleID, 'sladay' => $_key, 'opentimeline' => $_hourVal[0], 'closetimeline' => $_hourVal[1]), 'INSERT');
                    }
                }
            }
        }

        self::RebuildCache();

        return $_slaScheduleID;
    }

    /**
     * Update the SLA Schedule Record
     *
     * @author Varun Shoor
     * @param string $_title The SLA Schedule Title
     * @param array $_daysContainer The Days Container Table
     * @return bool "true" on Success, "false" otherwise
     */
    public function Update($_title, $_daysContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        } else if (empty($_title) || !_is_array($_daysContainer)) {
            return false;
        }

        $_SWIFT = SWIFT::GetInstance();

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('sunday_open', $_daysContainer['sunday']['type']);
        $this->UpdatePool('monday_open', $_daysContainer['monday']['type']);
        $this->UpdatePool('tuesday_open', $_daysContainer['tuesday']['type']);
        $this->UpdatePool('wednesday_open', $_daysContainer['wednesday']['type']);
        $this->UpdatePool('thursday_open', $_daysContainer['thursday']['type']);
        $this->UpdatePool('friday_open', $_daysContainer['friday']['type']);
        $this->UpdatePool('saturday_open', $_daysContainer['saturday']['type']);
        $this->UpdatePool('dateline', DATENOW);

        $this->ProcessUpdatePool();

        $this->EmptyScheduleTable();

        if (_is_array($_daysContainer))
        {
            foreach ($_daysContainer as $_key => $_val)
            {
                if (_is_array($_val['hours']))
                {
                    foreach ($_val['hours'] as $_hourKey => $_hourVal)
                    {
                        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'slascheduletable', array('slascheduleid' => (int) ($this->GetSLAScheduleID()), 'sladay' => $_key, 'opentimeline' => $_hourVal[0], 'closetimeline' => $_hourVal[1]), 'INSERT');
                    }
                }
            }
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Empty the SLA Schedule Table Linked to the currently loaded object
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    private function EmptyScheduleTable()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->Database->Query("DELETE FROM ". TABLE_PREFIX ."slascheduletable WHERE slascheduleid = '". (int) ($this->GetSLAScheduleID()) ."'");

        return true;
    }

    /**
     * Delete the SLA Schedule record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        self::DeleteList(array($this->GetSLAScheduleID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of SLA Schedules
     *
     * @author Varun Shoor
     * @param array $_slaScheduleIDList The List of SLA Schedules
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_slaScheduleIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_slaScheduleIDList))
        {
            return false;
        }

        $_finalSLAScheduleIDList = $_rejectedSLAScheduleIDList = array();

        // Check to see if any schedule is assigned to a SLA Plan?
        $_index = 1;

        $_finalRejectedText = $_finalText = '';
        $_SWIFT->Database->Query("SELECT slaplans.slaplanid, slaschedules.slascheduleid, slaschedules.title FROM ". TABLE_PREFIX ."slaplans AS slaplans LEFT JOIN ". TABLE_PREFIX ."slaschedules AS slaschedules ON (slaplans.slascheduleid = slaschedules.slascheduleid) WHERE slaschedules.slascheduleid IN (". BuildIN($_slaScheduleIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_rejectedSLAScheduleIDList[] = $_SWIFT->Database->Record['slascheduleid'];
            $_finalRejectedText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';
            $_index++;
        }

        if (count($_rejectedSLAScheduleIDList))
        {
            SWIFT::Alert($_SWIFT->Language->Get('titleslaschedulenodel'), $_SWIFT->Language->Get('msgslaschedulenodel') . '<br />' . $_finalRejectedText);
        }

        $_index = 1;
        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."slaschedules WHERE slascheduleid IN (". BuildIN($_slaScheduleIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['slascheduleid'], $_rejectedSLAScheduleIDList))
            {
                $_finalSLAScheduleIDList[] = $_SWIFT->Database->Record['slascheduleid'];

                $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';
                $_index++;
            }
        }

        if (!empty($_finalText))
        {
            SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelslaschedules'), count($_finalSLAScheduleIDList)), $_SWIFT->Language->Get('msgdelslaschedules') . '<br />' . $_finalText);
        }

        if (!count($_finalSLAScheduleIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."slaschedules WHERE slascheduleid IN (". BuildIN($_finalSLAScheduleIDList) .")");
        $_SWIFT->Database->Query("DELETE FROM ". TABLE_PREFIX ."slascheduletable WHERE slascheduleid IN (". BuildIN($_finalSLAScheduleIDList) .")");

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the SLA Schedule Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."slaschedules ORDER BY slascheduleid ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_cache[$_SWIFT->Database->Record3['slascheduleid']] = $_SWIFT->Database->Record3;
        }

        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."slascheduletable ORDER BY slascheduletableid ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            if (!isset($_cache[$_SWIFT->Database->Record3['slascheduleid']]))
            {
                continue;
            }

            $_cache[$_SWIFT->Database->Record3['slascheduleid']][$_SWIFT->Database->Record3['sladay']][$_SWIFT->Database->Record3['slascheduletableid']] = $_SWIFT->Database->Record3;
        }

        $_SWIFT->Cache->Update('slaschedulecache', $_cache);

        return true;
    }

    /**
     * Retrieve the day type set in sla schedule
     *
     * @author Varun Shoor
     * @param int $_dayNumerical The date('w') Numerical Value of Day (Sunday (0) -> Saturday (6))
     * @return string The Day Type
     * @throws SWIFT_SLA_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetDayType($_dayNumerical) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        switch ($_dayNumerical)
        {
            case 0:
                return $this->GetProperty('sunday_open');

            break;

            case 1:
                return $this->GetProperty('monday_open');

            break;

            case 2:
                return $this->GetProperty('tuesday_open');

            break;

            case 3:
                return $this->GetProperty('wednesday_open');

            break;

            case 4:
                return $this->GetProperty('thursday_open');

            break;

            case 5:
                return $this->GetProperty('friday_open');

            break;

            case 6:
                return $this->GetProperty('saturday_open');

            break;

            default:
            break;
        }

        throw new SWIFT_SLA_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Retrieve the day schedule table (open > close timelines)
     *
     * @author Varun Shoor
     * @param int $_dayNumerical The date('w') Numerical Value of Day (Sunday (0) -> Saturday (6))
     * @return array The Schedule Table
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetDayScheduleTable($_dayNumerical) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_slaScheduleCache = $this->Cache->Get('slaschedulecache');
        if (!isset($_slaScheduleCache[$this->GetSLAScheduleID()])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_slaScheduleContainer = $_slaScheduleCache[$this->GetSLAScheduleID()];

        switch ($_dayNumerical)
        {
            case 0:
                if (isset($_slaScheduleContainer['sunday'])) {
                    return self::ProcessScheduleTable($_slaScheduleContainer['sunday']);
                }

            break;

            case 1:
                if (isset($_slaScheduleContainer['monday'])) {
                    return self::ProcessScheduleTable($_slaScheduleContainer['monday']);
                }

            break;

            case 2:
                if (isset($_slaScheduleContainer['tuesday'])) {
                    return self::ProcessScheduleTable($_slaScheduleContainer['tuesday']);
                }

            break;

            case 3:
                if (isset($_slaScheduleContainer['wednesday'])) {
                    return self::ProcessScheduleTable($_slaScheduleContainer['wednesday']);
                }

            break;

            case 4:
                if (isset($_slaScheduleContainer['thursday'])) {
                    return self::ProcessScheduleTable($_slaScheduleContainer['thursday']);
                }

            break;

            case 5:
                if (isset($_slaScheduleContainer['friday'])) {
                    return self::ProcessScheduleTable($_slaScheduleContainer['friday']);
                }

            break;

            case 6:
                if (isset($_slaScheduleContainer['saturday'])) {
                    return self::ProcessScheduleTable($_slaScheduleContainer['saturday']);
                }

            break;

            default:
            break;
        }

        return [];
    }

    /**
     * Process the Schedule Table and return the result for use with the SLA Manager function
     *
     * @author Varun Shoor
     * @param array $_slaScheduleTable The SLA Schedule Table
     * @return array The Processed & Sorted SLA Schedule Table
     */
    public static function ProcessScheduleTable($_slaScheduleTable) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_slaScheduleTable)) {
            return [];
        }

        $_finalSLAScheduleTable = array();
        foreach ($_slaScheduleTable as $_slaScheduleTableID => $_slaScheduleTableContainer) {
            $_finalSLAScheduleTable[$_slaScheduleTableContainer['slascheduletableid']] = $_slaScheduleTableContainer['opentimeline'] . '-' .
                $_slaScheduleTableContainer['closetimeline'];
        }

        // Sort the array
        asort($_finalSLAScheduleTable);

        $_returnSLAScheduleTable = array();
        foreach ($_finalSLAScheduleTable as $_slaScheduleTableID => $_scheduleValue) {
            $_slaScheduleTableContainer = $_slaScheduleTable[$_slaScheduleTableID];
            $_returnSLAScheduleTable[$_slaScheduleTableID] = array($_slaScheduleTableContainer['opentimeline'],
                $_slaScheduleTableContainer['closetimeline']);
        }

        return $_returnSLAScheduleTable;
    }
}
