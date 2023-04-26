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

namespace Tickets\Library\SLA;

use DateTime;
use SWIFT;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Library;
use Tickets\Models\SLA\SWIFT_SLA;
use Tickets\Models\SLA\SWIFT_SLAHoliday;
use Tickets\Models\SLA\SWIFT_SLASchedule;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserOrganization;

/**
 * The SLA Management Class. Handles the actual execution and setting of SLA rules
 *
 * @author Varun Shoor
 */
class SWIFT_SLAManager extends SWIFT_Library
{
    protected $_slaPlanCache = false;
    protected $_slaScheduleCache = false;
    protected $_slaHolidayCache = false;
    protected $_ticketPropertiesChanged = false;

    // Core Constants
    const TYPE_OVERDUE = 1;
    const TYPE_RESOLUTIONDUE = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        // Load the caches
        $this->Cache->Queue('slaplancache');
        $this->Cache->Queue('slaschedulecache');
        $this->Cache->Queue('slaholidaycache');

        $this->Cache->LoadQueue();

        $this->_slaPlanCache = (array) $this->Cache->Get('slaplancache');
        $this->_slaScheduleCache = (array) $this->Cache->Get('slaschedulecache');
        $this->_slaHolidayCache = (array) $this->Cache->Get('slaholidaycache');
    }

    /**
     * Retrieves the seconds from the floating hour notation. The hour value could be 2 or 2.30
     *
     * @author Varun Shoor
     * @param float $_hourValue The Hour Value
     * @return mixed "_totalSeconds" (INT) on Success, "false" otherwise
     */
    public static function GetSecondsFromHour($_hourValue) {
        $_hourValue = number_format($_hourValue, 2, '.', '');
        $_hourContainer = explode('.', $_hourValue);

        $_actualHours = (int) $_hourContainer[0];
        $_actualMinutes = (int) $_hourContainer[1];

        $_totalSeconds = $_actualHours * 60 * 60;

        if ($_actualMinutes > 60) {
            $_actualMinutes = 60;
        }

        $_totalSeconds += $_actualMinutes * 60;

        return $_totalSeconds;
    }

    /**
     * Return the default overdue seconds + the current date
     *
     * @author Varun Shoor
     * @return mixed "0, $_defaultOverdueSeconds" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetDefaultOverdueSeconds() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Settings->Get('t_encustomoverdue') != '1') {
            return array(0, 0);
        }

        $_defaultOverdueHours = floatval($this->Settings->Get('t_overduehrs'));

        $_defaultOverdueSeconds = DATENOW + static::GetSecondsFromHour($_defaultOverdueHours);

        return array(0, $_defaultOverdueSeconds);
    }

    /**
     * Returns the default resolution due seconds + the current date
     *
     * @author Varun Shoor
     * @return mixed "0, $_defaultResolutionDueSeconds" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetDefaultResolutionDueSeconds() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->Settings->Get('t_encustomoverdue') != '1') {
            return array(0, 0);
        }

        $_defaultResolutionDueHours = floatval($this->Settings->Get('t_resolutionduehrs'));

        $_defaultResolutionDueSeconds = DATENOW + static::GetSecondsFromHour($_defaultResolutionDueHours);

        return array(0, $_defaultResolutionDueSeconds);
    }

    /**
     * Retrieve the SLA plan for a given ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIF_Ticket Object Pointer
     * @return mixed "SWIFT_SLA" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    protected function ExecuteSLAPlans(SWIFT_Ticket $_SWIFT_TicketObject) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_slaProperties = $_SWIFT_TicketObject->GetSLAProperties();

        $_SWIFT_SLAPlanObject_Default = false;

        foreach ($this->_slaPlanCache as $_slaPlanID => $_slaPlanDataStore) {
            $_SWIFT_SLAPlanObject = new SWIFT_SLA(new SWIFT_DataStore($_slaPlanDataStore));
            if (!$_SWIFT_SLAPlanObject instanceof SWIFT_SLA || !$_SWIFT_SLAPlanObject->GetIsClassLoaded()) {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
                // @codeCoverageIgnoreEnd
            }

            if ($_SWIFT_SLAPlanObject->GetProperty('isenabled') == '0')
            {
                continue;
            }

            // Get old ticket properties
            $_oldTicketProperties = $_SWIFT_TicketObject->GetOldTicketProperties();

            // Get only changed properties by array_diff_assoc
            $_slaPropertiesChange = array_diff_assoc($_slaProperties, $_oldTicketProperties);

            // Nothing changed, continue to next process.
            if (!_is_array($_slaPropertiesChange)) {
                // if there is no change in properties then we should contiunue to check other stuff.
                continue;
            }

            /*
             * BUG FIX - Ravi Sharma
             *
             * SWIFT-3187 SLA with multiple criteria not triggered
             */
            $_SWIFT_SLAPlanObject->SetSLAProperties($_slaProperties);

            // Attempt to execute this SLA Plan
            if ($_SWIFT_SLAPlanObject->Execute()) {
                // Yay! we found the plan we need to act upon
                $_SWIFT_SLAPlanObject_Default = $_SWIFT_SLAPlanObject;

                // Ticket properties changed set to true
                $this->_ticketPropertiesChanged = true;

                break;
            }
        }

        return $_SWIFT_SLAPlanObject_Default;
    }

    /**
     * Retrieve the due time based on the given ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIF_Ticket Object Pointer
     * @return mixed "SWIFT_SLA, _overdueSeconds" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetDueTime(SWIFT_Ticket $_SWIFT_TicketObject) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // First see if we have any SLA plans in first place.. if not, return the default overdue seconds
        if (!_is_array($this->_slaPlanCache) || !_is_array($this->_slaScheduleCache)) {
            return $this->GetDefaultOverdueSeconds();
        }



        $_SWIFT_SLAPlanObject = false;
        // Does this ticket have a fixed SLA Plan?
        if ($_SWIFT_TicketObject->GetProperty('ticketslaplanid') != '0' && isset($this->_slaPlanCache[$_SWIFT_TicketObject->GetProperty('ticketslaplanid')])) {
            $_SWIFT_SLAPlanObject = new SWIFT_SLA(new SWIFT_DataID($_SWIFT_TicketObject->GetProperty('ticketslaplanid')));

        }

        /*
         * BUG FIX - Abhishek Mittal, Ravi Sharma
         *
         * SWIFT-3913 SLA plan selected for a User Organization is overriding the SLA plan selected for a User account.
         * SWIFT-2696 Not able to change the SLA plan on the ticket in Staff CP under Edit tab, if there is a SLA already specified in respective user account.
         * SWIFT-4317 Deletion of an SLA plan linked to User Profile/Organization Profile causes an error when updating ticket
         */
        if (!$_SWIFT_SLAPlanObject instanceof SWIFT_SLA || !$_SWIFT_SLAPlanObject->GetIsClassLoaded()) {
            $_User = $_SWIFT_TicketObject->GetUserObject();

            if ($_User instanceof SWIFT_User && $_User->GetIsClassLoaded() && $_User->Get('slaplanid') != '0' && ($_User->Get('slaexpirytimeline') == '0' || $_User->Get('slaexpirytimeline') > DATENOW) && isset($this->_slaPlanCache[$_User->Get('slaplanid')])) {
                $_SWIFT_SLAPlanObjectUser = new SWIFT_SLA(new SWIFT_DataID($_User->Get('slaplanid')));
                try {
                    if ($_SWIFT_SLAPlanObjectUser->GetProperty('isenabled') !== 0) {
                        $_SWIFT_SLAPlanObject = new SWIFT_SLA(new SWIFT_DataID($_User->Get('slaplanid')));
                    }
                } catch (SWIFT_Exception $e) {
                    //the sla plan applied for user profile is not enabled or does not exists
                }
                unset($_SWIFT_SLAPlanObjectUser);
            }
        }

        /*
         * BUG FIX - Abhishek Mittal, Ravi Sharma
         *
         * SWIFT-3913 SLA plan selected for a User Organization is overriding the SLA plan selected for a User account.
         * SWIFT-2696 Not able to change the SLA plan on the ticket in Staff CP under Edit tab, if there is a SLA already specified in respective user account.
         * SWIFT-4317 Deletion of an SLA plan linked to User Profile/Organization Profile causes an error when updating ticket
         */
        if (!$_SWIFT_SLAPlanObject instanceof SWIFT_SLA || !$_SWIFT_SLAPlanObject->GetIsClassLoaded()) {
            $_UserOrganization = $_SWIFT_TicketObject->GetUserOrganizationObject();

            if ($_UserOrganization instanceof SWIFT_UserOrganization && $_UserOrganization->GetIsClassLoaded() && $_UserOrganization->Get('slaplanid') != '0'
                && ($_UserOrganization->Get('slaexpirytimeline') == '0' || $_UserOrganization->Get('slaexpirytimeline') > DATENOW) && isset($this->_slaPlanCache[$_UserOrganization->Get('slaplanid')])
            ) {
                $_SWIFT_SLAPlanObject = new SWIFT_SLA(new SWIFT_DataID($_UserOrganization->Get('slaplanid')));
            }
        }

        // We now run the SLA rules based on the data we have in ticket
        if (!$_SWIFT_SLAPlanObject instanceof SWIFT_SLA || !$_SWIFT_SLAPlanObject->GetIsClassLoaded()) {
            $_SWIFT_SLAPlanObject = $this->ExecuteSLAPlans($_SWIFT_TicketObject);
        }

        if (!$this->_ticketPropertiesChanged && _is_array($_SWIFT_TicketObject->GetOldTicketProperties())) {
            // there is no chage in ticket properties. return array(0, 0) from here.
            return array(0, 0);
        }

        if (!$_SWIFT_SLAPlanObject instanceof SWIFT_SLA || !$_SWIFT_SLAPlanObject->GetIsClassLoaded()) {
            return $this->GetDefaultOverdueSeconds();
        }

        // Now that we have the SLA Plan associated with this ticket, we need to get the overdue seconds based on associated schedule and holidays
        $_overdueSeconds = $this->GetDueSeconds(static::TYPE_OVERDUE, $_SWIFT_SLAPlanObject);

        return array($_SWIFT_SLAPlanObject, $_overdueSeconds);
    }

    /**
     * Retrieve the resolution due time based on the given ticket
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIF_Ticket Object Pointer
     * @param SWIFT_SLA|null $_SWIFT_SLAPlanObject_Custom
     * @return mixed "SWIFT_SLA, _resolutionDueSeconds" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetResolutionTime(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_SLA $_SWIFT_SLAPlanObject_Custom = null) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // First see if we have any SLA plans in first place.. if not, return the default resolution due seconds
        if (!_is_array($this->_slaPlanCache) || !_is_array($this->_slaScheduleCache)) {
            return $this->GetDefaultResolutionDueSeconds();
        }

        // We now run the SLA rules based on the data we have in ticket
        $_SWIFT_SLAPlanObject = false;
        if ($_SWIFT_SLAPlanObject_Custom instanceof SWIFT_SLA && $_SWIFT_SLAPlanObject_Custom->GetIsClassLoaded()) {
            $_SWIFT_SLAPlanObject = $_SWIFT_SLAPlanObject_Custom;
        } else {
            $_SWIFT_SLAPlanObject = $this->ExecuteSLAPlans($_SWIFT_TicketObject);
            if (!$_SWIFT_SLAPlanObject instanceof SWIFT_SLA || !$_SWIFT_SLAPlanObject->GetIsClassLoaded()) {
                return $this->GetDefaultResolutionDueSeconds();
            }
        }

        // Now that we have the SLA Plan associated with this ticket, we need to get the resolution due seconds based on
        // associated schedule and holidays
        $_resolutionDueSeconds = $this->GetDueSeconds(static::TYPE_RESOLUTIONDUE, $_SWIFT_SLAPlanObject);

        return array($_SWIFT_SLAPlanObject, $_resolutionDueSeconds);
    }

    /**
     * Check to see if its a valid type
     *
     * @author Varun Shoor
     * @param mixed $_type The Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_type) {
        if ($_type == static::TYPE_OVERDUE || $_type == static::TYPE_RESOLUTIONDUE) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves the number of seconds left in a day from the current hour and minute
     *
     * @author Varun Shoor
     * @param int $_currentHour The Current Hour
     * @param int $_currentMinute The Current Minute
     * @return int The number of seconds left in the day
     */
    protected static function GetTimeLeftInDay($_currentHour, $_currentMinute) {
//        echo 'TIME LEFT IN DAY: ' . $_currentHour . ':' . $_currentMinute . SWIFT_CRLF;

        $_currentTimeline = mktime($_currentHour, $_currentMinute, 0);
//        echo 'CURRENT TIMELINE: ' . date('d M Y h:i:s A', $_currentTimeline) . SWIFT_CRLF;

        $_endTimeline = mktime(24, 0, 0);

//        echo 'END TIMELINE: ' . date('d M Y h:i:s A', $_endTimeline) . SWIFT_CRLF;

        $_secondsLeftInDay = $_endTimeline - $_currentTimeline;

//        echo 'SECONDS LEFT: ' . $_secondsLeftInDay . SWIFT_CRLF;

        return $_secondsLeftInDay;
    }

    /**
     * Retrieves the unix epoch of start of day
     *
     * @author Varun Shoor
     * @param int $_currentTimeline The Current Time line
     * @return int The epoch of start of day
     */
    protected static function GetStartOfDay($_currentTimeline) {
        $_currentTimeline = mktime(0, 0, 0, gmdate('n', $_currentTimeline), gmdate('j', $_currentTimeline), gmdate('Y', $_currentTimeline));

        return $_currentTimeline;
    }

    /**
     * Retrieves the timestamp based on the current SLA hour
     *
     * @author Varun Shoor
     * @param string $_slaHour The SLA Hour 00:00
     * @param int $_currentTimeline The Current Timeline
     * @return int The Calculated Timestamp
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetTimestampOnSLAHour($_slaHour, $_currentTimeline) {
        if (strpos($_slaHour, ':') === false) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_slaHourContainer = explode(':', $_slaHour);

        $_newTimeline = mktime($_slaHourContainer[0], $_slaHourContainer[1], 0, date('n', $_currentTimeline), date('j', $_currentTimeline),
            date('Y', $_currentTimeline));

        return $_newTimeline;
    }

    /**
     * Get the timestamp in currently active time zone
     *
     * @author Varun Shoor
     * @return int $_finalTimeStamp
     */
    public static function GetTimezoneTimestamp() {
        $_SWIFT = SWIFT::GetInstance();

        $_timeStamp = time();

        $_oldTimezone = date_default_timezone_get();
        date_default_timezone_set($_SWIFT->Settings->Get('dt_timezonephp'));

        $_hourDifference = gmdate('Z');
        date_default_timezone_set($_oldTimezone);

        return $_timeStamp + (int)$_hourDifference;
    }

    /**
     * Get the due seconds based on the schedules and holidays (global and linked)
     *
     * @author Varun Shoor
     * @param mixed $_type The Type of Value to Return
     * @param SWIFT_SLA $_SWIFT_SLAPlanObject The SWIFT_SLA Object Pointer
     * @param int|bool $_customStartTimeline (OPTIONAL) The Custom Starting Time
     * @return int
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function GetDueSeconds($_type, SWIFT_SLA $_SWIFT_SLAPlanObject, $_customStartTimeline = false) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!static::IsValidType($_type) || !$_SWIFT_SLAPlanObject instanceof SWIFT_SLA || !$_SWIFT_SLAPlanObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_overDueSeconds = false;
        if ($_type == static::TYPE_OVERDUE) {
            $_overDueSeconds = static::GetSecondsFromHour($_SWIFT_SLAPlanObject->GetProperty('overduehrs'));
        } else {
            $_overDueSeconds = static::GetSecondsFromHour($_SWIFT_SLAPlanObject->GetProperty('resolutionduehrs'));
        }

        $_SWIFT_SLAScheduleObject = $_SWIFT_SLAPlanObject->GetScheduleObject();

        // Setting Default Timezome as per Admin Settings while calculation then will set to original one at the end
        $_lastActiveTimezone = date_default_timezone_get();
        date_default_timezone_set($this->Settings->Get('dt_timezonephp'));

        // The starting timeline.. we calculate the starting timeline ONLY ON THE SYSTEM TIMEZONE..
        $_startTimeline = time();
        if ($_customStartTimeline) {
            $_startTimeline = $_customStartTimeline;
        }

        // Loop for results
        $_runLoop = true;

        // The number of seconds to add to the current date

        /**
         * Shift seconds are the TOTAL seconds to calculate for overdue and add to starttimeline. This includes time from closures and holidays
         */
        $_shiftSeconds = 0;

        /**
         * Open Overdue seconds are the TOTAL seconds whilst the shift was open.. these should never be more than $_overDueSeconds
         */
        $_openOverDueSeconds = 0;
        $_currentTimeline = $_startTimeline;

        $_itterationCount = $_indexCount = 0;

        while ($_runLoop) {
            $_indexCount++;

            $_currentSLAHour = date('H:i', $_currentTimeline);
            $_currentDay = date('w', $_currentTimeline);

            $interfaceName = \SWIFT::GetInstance()->Interface->GetName()?:SWIFT_INTERFACE;
            if ($_openOverDueSeconds >= $_overDueSeconds || $_itterationCount >= 1000 || ($interfaceName === 'tests' && $_itterationCount > 1)) {

                break;
            }

            $_actualOverDueSecondsLeft = $_overDueSeconds - $_openOverDueSeconds;

            /**
             * First check if day is closed or if its a holiday
             */
            if ($_SWIFT_SLAScheduleObject->GetDayType($_currentDay) == SWIFT_SLASchedule::SCHEDULE_DAYCLOSED ||
                    SWIFT_SLAHoliday::IsHoliday($_currentTimeline, $_SWIFT_SLAPlanObject)) {
                /**
                 * If its a holiday or if the day is closed then we get the remaining time left and add it to our overdue seconds + current timeline
                 */
                $_timeLeftInDay = static::GetTimeLeftInDay(date('H', $_currentTimeline), date('i', $_currentTimeline));

                $_shiftSeconds += $_timeLeftInDay;
                $_currentTimeline += $_timeLeftInDay;

            /**
             * If the day is open 24 hours, we need to check and see if the overdue seconds are less than the time left in day
             * If Overdue Seconds < Time left in day then add the diff and bail out
             * If it is > time left in day then add the diff and let it move to the next day
             */
            } else if ($_SWIFT_SLAScheduleObject->GetDayType($_currentDay) == SWIFT_SLASchedule::SCHEDULE_DAYOPEN24) {
                $_timeLeftInDay = static::GetTimeLeftInDay(date('H', $_currentTimeline), date('i', $_currentTimeline));

                // If over due seconds left is less than time left in day then add them to shift seconds and bail out
                if ($_actualOverDueSecondsLeft < $_timeLeftInDay) {
                    $_currentTimeline += $_actualOverDueSecondsLeft;
                    $_shiftSeconds += $_actualOverDueSecondsLeft;
                    $_openOverDueSeconds += $_actualOverDueSecondsLeft;

                    break;

                // Well.. seems like it will span another day so add the time left in day and let this run for the next itteration
                }

                $_currentTimeline += $_timeLeftInDay;
                $_shiftSeconds += $_timeLeftInDay;
                $_openOverDueSeconds += $_timeLeftInDay;

                /**
             * If the day has custom rules, then we are pretty much screwed.. This is complex, we need to:
             * a) Add time till the first schedule rule because we are closed till then
             * b) If $_actualOverDueSecondsLeft is less than the schedule duration then add the time left and bail
             * c) If it is more than the schedule duration than add duration to open overdue seconds and let it move on
             * d) If the schedule had a previous rule activated, then calculate the difference between previous ending time and opening time and process
             * e) Continue processing.. if last schedule rule is hit then add end time to day ending time and let it move onto the next day
             */
            } else if ($_SWIFT_SLAScheduleObject->GetDayType($_currentDay) == SWIFT_SLASchedule::SCHEDULE_DAYOPEN) {
                $_slaScheduleTable = $_SWIFT_SLAScheduleObject->GetDayScheduleTable($_currentDay);
                $_timeLeftInDay = static::GetTimeLeftInDay(date('H', $_currentTimeline), date('i', $_currentTimeline));
                $_dayEndTimeStamp = mktime(24, 0, 0, gmdate('n', $_currentTimeline), gmdate('j', $_currentTimeline), gmdate('Y', $_currentTimeline));

                // If we dont have ANY entry in the schedule table then we treat the rest of time in the day as closed
                if (!_is_array($_slaScheduleTable)) {
                    $_currentTimeline += $_timeLeftInDay;
                    $_shiftSeconds += $_timeLeftInDay;

                // Ok, so this day has some rules associated with it.. lets process em.. Varun.. are you prepared to handle whats coming? Yes, I am!
                } else {
                    $_lastTimeStampClose = 0;
                    $_scheduleTableIndex = 0;

                    foreach ($_slaScheduleTable as $_slaScheduleTableID => $_slaScheduleTableContainer) {
                        $_timeStampOpen = static::GetTimestampOnSLAHour($_slaScheduleTableContainer[0], $_currentTimeline);
                        $_timeStampClose = static::GetTimestampOnSLAHour($_slaScheduleTableContainer[1], $_currentTimeline);
                        $_timeStampDuration = $_timeStampClose - $_timeStampOpen;

                        // This should never happen but still.. you never know.
                        if ($_timeStampDuration < 0) {
                            continue;
                        }

                        // Is it the first one and the day hasnt started yet? then we add the time from start of day to the starting of this schedule
                        if ($_scheduleTableIndex == 0 && $_timeStampOpen > $_currentTimeline) {
                            $_startOfDay = static::GetStartOfDay($_currentTimeline);

                            $_secondsSinceStart = $_timeStampOpen - $_startOfDay;

                            $_timeToStart = $_timeStampOpen - $_currentTimeline;

                            $_shiftSeconds += $_timeToStart;
                            $_currentTimeline += $_timeToStart;
                        }

                        $_currentTimeline -= gmdate('s', $_currentTimeline);

                        list($_currentTimeline, $_openOverDueSeconds, $_shiftSeconds, $_result, $_lastTimeStampClose) = $this->checkIsCurrentTimeOpen($_lastTimeStampClose,
                            $_currentTimeline, $_overDueSeconds,
                            $_openOverDueSeconds, $_shiftSeconds, $_timeStampOpen, $_timeStampClose);
                        if ($_result !== null && $_result === false) {
                            // @codeCoverageIgnoreStart
                            // this code will never be executed
                            break;
                            // @codeCoverageIgnoreEnd
                        } else {
                            list($_shiftSeconds, $_currentTimeline, $_openOverDueSeconds, $_result, $_lastTimeStampClose) = $this->checkIsCurrentTimeLessOpen($_currentTimeline,
                                $_timeStampOpen, $_lastTimeStampClose, $_shiftSeconds, $_actualOverDueSecondsLeft,
                                $_timeStampDuration, $_openOverDueSeconds, $_timeStampClose);
                            if ($_result !== null && $_result === false) {
                                break;
                            }
                        }

                        $_actualOverDueSecondsLeft = $_overDueSeconds - $_openOverDueSeconds;

                        $_scheduleTableIndex++;
                    }

                    /**
                     * After we have processed the schedule table and STILL NOT BAILED OUT, we need to see how much time difference is left in day
                     * add it and let it move onto the next day.
                     * IMPORTANT: We also need to see if some rules were executed at all, if not.. we add the FULL time of the day as CLOSED.
                     */
                    $_dayClosingDifference = $_dayEndTimeStamp - $_lastTimeStampClose;
                    if ($_openOverDueSeconds < $_overDueSeconds) {
                        if ($_lastTimeStampClose && $_dayClosingDifference > 0) {
                            // @codeCoverageIgnoreStart
                            // this code will never be executed
                            $_shiftSeconds += $_dayClosingDifference;
                            $_currentTimeline += $_dayClosingDifference;
                            // @codeCoverageIgnoreEnd
                        //    $_scheduleTableIndex = 0;
                        } else if ($_lastTimeStampClose == 0) {
                            $_currentTimeline += $_timeLeftInDay;
                            $_shiftSeconds += $_timeLeftInDay;
                        }
                    }
                }
            }

            $_itterationCount++;
        }

        $_finalTimeline = (int) ($_startTimeline + $_shiftSeconds);

        date_default_timezone_set($_lastActiveTimezone);

        return $_finalTimeline;
    }

    /**
     * Gets seconds of working time between two timestamps according to the SLA Plan
     *
     * @author Pankaj Garg
     *
     * @param SWIFT_SLA $_SLA
     * @param int $_startTimeLine
     * @param int $_endTimeLine
     *
     * @return int
     * @throws SWIFT_Exception
     */
    public function GetSLAResponseTime(SWIFT_SLA $_SLA, $_startTimeLine, $_endTimeLine)
    {
        $_SLASchedule = $_SLA->GetScheduleObject();

        $_currentTimeLine = $_startTimeLine;
        $_SLAResponseTime = 0;
        $_index           = 0;

        $_DateTimeStart = new DateTime(date('Y-m-d', $_startTimeLine));
        $_DateTimeEnd   = new DateTime(date('Y-m-d', $_endTimeLine));
        $_interval      = $_DateTimeStart->diff($_DateTimeEnd);
        $_maxLoopCount  = (int) ($_interval->days) + 1;

        while ($_index != $_maxLoopCount) {
            $_currentDay     = date('w', $_currentTimeLine);
            $_currentDayType = $_SLASchedule->GetDayType($_currentDay);

            $_startOfTheDay = static::GetStartOfDay($_currentTimeLine);
            $_endOfTheDay   = mktime(24, 0, 0, date('n', $_currentTimeLine), date('j', $_currentTimeLine), date('Y', $_currentTimeLine));

            if ($_currentDayType == SWIFT_SLASchedule::SCHEDULE_DAYCLOSED || SWIFT_SLAHoliday::IsHoliday($_currentTimeLine, $_SLA)) {
                if ($_endTimeLine > $_endOfTheDay) {
                    $_currentTimeLine += (static::GetTimeLeftInDay(date('H', $_currentTimeLine), date('i', $_currentTimeLine)) + 1); // point to next day now.

                    $_index++;

                    continue;
                }

                // If reply posted on close day or holiday.
                break;
            }

            if ($_currentDayType == SWIFT_SLASchedule::SCHEDULE_DAYOPEN24) {
                if ($_endTimeLine > $_endOfTheDay) {
                    $_SLAResponseTime += (static::GetTimeLeftInDay(date('H', $_currentTimeLine), date('i', $_currentTimeLine)) + 1);

                    $_currentTimeLine = ($_endOfTheDay + 1); // point to next day now.

                    $_index++;

                    continue;
                }

                if ($_endTimeLine >= $_startOfTheDay && $_endTimeLine <= $_endOfTheDay) { // Check that response is made on this day.
                    if ($_startTimeLine >= $_startOfTheDay && $_startTimeLine <= $_endOfTheDay) { // If Ticket was also created on this day.
                        $_SLAResponseTime += ($_endTimeLine - $_startTimeLine);
                    } else {
                        $_SLAResponseTime += ($_endTimeLine - $_startOfTheDay);
                    }

                    break;
                }
            } else if ($_currentDayType == SWIFT_SLASchedule::SCHEDULE_DAYOPEN) {
                $_SLAScheduleTable      = $_SLASchedule->GetDayScheduleTable($_currentDay);
                $_timeLeftInDay         = static::GetTimeLeftInDay(date('H', $_currentTimeLine), date('i', $_currentTimeLine));
                $_scheduleContainer     = array();
                $_totalWorkingTimeInDay = 0;

                if (!_is_array($_SLAScheduleTable)) {
                    $_currentTimeLine += $_timeLeftInDay;
                    $_index++;
                    continue;
                }

                foreach ($_SLAScheduleTable as $_SLAScheduleTableContainer) {
                    $_openTimeLine  = static::GetTimestampOnSLAHour($_SLAScheduleTableContainer[0], $_currentTimeLine);
                    $_closeTimeLine = static::GetTimestampOnSLAHour($_SLAScheduleTableContainer[1], $_currentTimeLine);

                    $_durationTimeLine = $_closeTimeLine - $_openTimeLine;

                    if ($_durationTimeLine < 0) {
                        continue;
                    }

                    $_scheduleContainer[] = array('opentimeline' => $_openTimeLine, 'closetimeline' => $_closeTimeLine);
                    $_totalWorkingTimeInDay += ($_closeTimeLine - $_openTimeLine);
                }

                // Sort the container in ascending order of opentime.
                usort($_scheduleContainer, [$this, 'sortByOpenTimeline']);

                $_iterationCount = 0;
                $_firstOpenTime  = '';

                foreach ($_scheduleContainer as $_scheduleList) {
                    $_iterationCount++;

                    if ($_iterationCount == 1) {
                        $_firstOpenTime = $_scheduleList['opentimeline'];
                    }

                    list($_result, $_SLAResponseTime, $_currentTimeLine) = $this->checkIsTimelineInDay($_currentTimeLine, $_startTimeLine,
                        $_endTimeLine, $_endOfTheDay, $_startOfTheDay, $_firstOpenTime, $_totalWorkingTimeInDay,
                        $_SLAResponseTime, $_scheduleList, $_iterationCount, $_scheduleContainer);

                    if ($_result !== null) {
                        if ($_result === true) {
                            continue;
                        }

                        break;
                    }

                    if ($_endTimeLine >= $_scheduleList['opentimeline'] && $_endTimeLine <= $_scheduleList['closetimeline']) { // If found the response time, break from the loop
                        if ($_currentTimeLine >= $_scheduleList['opentimeline'] && $_currentTimeLine <= $_scheduleList['closetimeline']) { // If current timeline also existing between this schedule, then take difference of time of response and current timeline.
                            $_SLAResponseTime += ($_endTimeLine - $_currentTimeLine);
                        } else if ($_startTimeLine >= $_scheduleList['opentimeline'] && $_startTimeLine <= $_scheduleList['closetimeline']) {
                            $_SLAResponseTime += ($_endTimeLine - $_startTimeLine);
                        } else {
                            // @codeCoverageIgnoreStart
                            // this code will never be executed
                            $_SLAResponseTime += ($_endTimeLine - $_scheduleList['opentimeline']);
                            // @codeCoverageIgnoreEnd
                        }

                        break 2;
                    }

                    if ($_endTimeLine < $_scheduleList['opentimeline'] && $_endTimeLine > $_currentTimeLine) {

                        break;
                    }

                    if ($_endTimeLine > $_scheduleList['closetimeline'] && $_startTimeLine > $_scheduleList['closetimeline']) {

                        $_currentTimeLine = $_scheduleList['closetimeline'] + 1;

                        continue;
                    }

                    if ($_startTimeLine >= $_scheduleList['opentimeline'] && $_startTimeLine <= $_scheduleList['closetimeline']) {
                        $_SLAResponseTime += $_scheduleList['closetimeline'] - $_startTimeLine;

                    } else {
                        $_SLAResponseTime += $_scheduleList['closetimeline'] - $_scheduleList['opentimeline'];

                    }

                    if ($_iterationCount != count($_scheduleContainer)) { // If this is not last schedule, set current timeline to its closing time , add working response time and continue to next schedule.
                        $_currentTimeLine = $_scheduleList['closetimeline'] + 1;
                    } else { //If this is last schedule for the day, then move to next day.
                        $_currentTimeLine = $_endOfTheDay + 1;
                    }
                }
            }

            $_index++;
        }

        return $_SLAResponseTime;
    }

    /**
     * @param mixed $_lastTimeStampClose
     * @param mixed $_currentTimeline
     * @param mixed $_overDueSeconds
     * @param mixed $_openOverDueSeconds
     * @param mixed $_shiftSeconds
     * @param mixed $_timeStampOpen
     * @param mixed $_timeStampClose
     * @return array
     */
    protected function checkIsCurrentTimeOpen(
        $_lastTimeStampClose,
        $_currentTimeline,
        $_overDueSeconds,
        $_openOverDueSeconds,
        $_shiftSeconds,
        $_timeStampOpen,
        $_timeStampClose
    ) {
        $_result = null;

        if ($_currentTimeline > $_timeStampOpen && $_currentTimeline < $_timeStampClose) {
            $_result = true;

            /**
             * Now we need to see whether we have moved more than the start timelines. Sometimes a user might create two rules.
             * Example: 9:00 -> 13:00 AND 10:30 -> 15:00
             * In such a case we check whether we have moved and then calculate the difference between the current timeline and
             * the closing timeline of the stamp and add it to currently open office seconds
             * HOWEVER: If the current timeline is also greater than the closing timeline.. then we ignore this step. This will happen
             * If the user has specified rules like: 9:00 -> 13:00 AND 10:30 -> 12:30
             */
            $_timeDifference = $_timeStampClose - $_currentTimeline;

            if ($_overDueSeconds <= $_timeDifference) {
                $_currentTimeline += $_overDueSeconds;
                $_openOverDueSeconds += $_overDueSeconds;
                $_shiftSeconds += $_overDueSeconds;

                $_result = false;
            }

            if ($_result) {
                $_currentTimeline += $_timeDifference;
                $_openOverDueSeconds += $_timeDifference;

                // Is the time difference greater than overdue seconds required?
                if ($_timeDifference > $_overDueSeconds) {
                    // Then only add up the difference
                    // @codeCoverageIgnoreStart
                    // this code will never be executed, it doesn't make sense.
                    // the condition is met and exits above
                    $_shiftSeconds += $_overDueSeconds - $_shiftSeconds;
                } else {
                    // @codeCoverageIgnoreEnd
                    $_shiftSeconds += $_timeDifference;
                }
                $_lastTimeStampClose = $_timeStampClose;
            }
        }

        return [
            $_currentTimeline,
            $_openOverDueSeconds,
            $_shiftSeconds,
            $_result,
            $_lastTimeStampClose
        ];
    }

    /**
     * @param mixed $_currentTimeline
     * @param mixed $_timeStampOpen
     * @param mixed $_lastTimeStampClose
     * @param mixed $_shiftSeconds
     * @param mixed $_actualOverDueSecondsLeft
     * @param mixed $_timeStampDuration
     * @param mixed $_openOverDueSeconds
     * @param mixed $_timeStampClose
     * @return array
     */
    protected function checkIsCurrentTimeLessOpen(
        $_currentTimeline,
        $_timeStampOpen,
        $_lastTimeStampClose,
        $_shiftSeconds,
        $_actualOverDueSecondsLeft,
        $_timeStampDuration,
        $_openOverDueSeconds,
        $_timeStampClose
    ) {
        $_result = null;
        if ($_currentTimeline <= $_timeStampOpen) {
            $_result = true;

            /**
             * Process ONLY if current time line <= time stamp of open
             * This is a normal rule execution and we need to check if over due seconds left is less than duration. If it is
             * we add the difference and bail out, if not.. we add the duration seconds and let it move onto the next table
             */

            /**
             * We need to first check the difference between last close and our start..
             * If we find some, this was the time when our office was supposedly closed..
             */
            $_closingDifference = $_timeStampOpen - $_lastTimeStampClose;

            if ($_lastTimeStampClose && $_closingDifference > 0) {
                $_shiftSeconds += $_closingDifference;
                $_currentTimeline += $_closingDifference;
            }

            $_timeToStart = $_timeStampOpen - $_currentTimeline;
            if ($_timeToStart > 0) {
                $_currentTimeline += $_timeToStart;
                $_shiftSeconds += $_timeToStart;
            }

            /**
             * Process the actual stuff
             */
            if ($_actualOverDueSecondsLeft <= $_timeStampDuration) {
                $_currentTimeline += $_actualOverDueSecondsLeft;
                $_shiftSeconds += $_actualOverDueSecondsLeft;
                $_openOverDueSeconds += $_actualOverDueSecondsLeft;

                $_result = false;
            }

            if ($_result) {
                $_currentTimeline += $_timeStampDuration;
                $_shiftSeconds += $_timeStampDuration;
                $_openOverDueSeconds += $_timeStampDuration;

                $_lastTimeStampClose = $_timeStampClose;
            }
        }
        return [$_shiftSeconds, $_currentTimeline, $_openOverDueSeconds, $_result, $_lastTimeStampClose];
    }

    protected function sortByOpenTimeline ($a, $b) {
        return $a['opentimeline'] - $b['opentimeline'];
    }

    /**
     * @param mixed $_currentTimeLine
     * @param mixed $_startTimeLine
     * @param mixed $_endTimeLine
     * @param mixed $_endOfTheDay
     * @param mixed $_startOfTheDay
     * @param mixed $_firstOpenTime
     * @param mixed $_totalWorkingTimeInDay
     * @param mixed $_SLAResponseTime
     * @param mixed $_scheduleList
     * @param mixed $_iterationCount
     * @param mixed $_scheduleContainer
     * @return array
     */
    protected function checkIsTimelineInDay(
        $_currentTimeLine,
        $_startTimeLine,
        $_endTimeLine,
        $_endOfTheDay,
        $_startOfTheDay,
        $_firstOpenTime,
        $_totalWorkingTimeInDay,
        $_SLAResponseTime,
        $_scheduleList,
        $_iterationCount,
        $_scheduleContainer
    ) {
        $_result = null;
        if ($_endTimeLine > $_endOfTheDay) { // Reply not posted this day...

            if ($_startTimeLine > $_startOfTheDay && $_startTimeLine < $_endOfTheDay) {
                if ($_startTimeLine <= $_firstOpenTime) {

                    $_SLAResponseTime += $_totalWorkingTimeInDay;

                    $_currentTimeLine = $_endOfTheDay + 1;

                    $_result = false;
                }

                if ($_result === null) {
                    if ($_startTimeLine >= $_scheduleList['opentimeline'] && $_startTimeLine <= $_scheduleList['closetimeline']) {
                        $_SLAResponseTime += ($_scheduleList['closetimeline'] - $_startTimeLine);

                        $_currentTimeLine = ($_iterationCount == count($_scheduleContainer)) ? ($_endOfTheDay + 1) : ($_scheduleList['closetimeline'] + 1);

                        $_result = true;
                    }

                    if ($_result === null) {
                        if (($_scheduleList['opentimeline'] > $_startTimeLine) && ($_scheduleList['closetimeline'] > $_startTimeLine)) {
                            $_SLAResponseTime += ($_scheduleList['closetimeline'] - $_scheduleList['opentimeline']);

                            $_currentTimeLine = ($_iterationCount == count($_scheduleContainer)) ? ($_endOfTheDay + 1) : ($_scheduleList['closetimeline'] + 1);

                            $_result = true;
                        }

                        if ($_result === null) {
                            if ($_iterationCount == count($_scheduleContainer)) {
                                $_currentTimeLine = $_endOfTheDay + 1;

                                $_result = false;
                            }
                        }

                        if ($_result === null) {
                            $_result = true;
                        }
                    }
                }
            }

            if ($_result === null) {
                $_SLAResponseTime += $_totalWorkingTimeInDay;

                $_currentTimeLine = $_endOfTheDay + 1;

                $_result = false;
            }
        }

        return [$_result, $_SLAResponseTime, $_currentTimeLine];
    }
}
