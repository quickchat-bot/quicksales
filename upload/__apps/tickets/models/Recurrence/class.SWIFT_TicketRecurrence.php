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

namespace Tickets\Models\Recurrence;

use SWIFT;
use Base\Models\CustomField\SWIFT_CustomFieldValue;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Template\SWIFT_TemplateGroup;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Base\Models\User\SWIFT_User;
use Tickets\Library\Ticket\SWIFT_TicketEmailDispatch;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Recipient\SWIFT_TicketRecipient;
use Base\Library\HTML\SWIFT_HTML;

/**
 * The Ticket Recurrence Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketRecurrence extends SWIFT_Model
{
    const TABLE_NAME        =    'ticketrecurrences';
    const PRIMARY_KEY        =    'ticketrecurrenceid';

    const TABLE_STRUCTURE    =    "ticketrecurrenceid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                ticketid I DEFAULT '0' NOTNULL,
                                tickettype I2 DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                ownerstaffid I DEFAULT '0' NOTNULL,
                                tickettypeid I DEFAULT '0' NOTNULL,
                                ticketstatusid I DEFAULT '0' NOTNULL,
                                ticketpriorityid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,

                                dontsendemail I2 DEFAULT '0' NOTNULL,
                                dispatchautoresponder I2 DEFAULT '0' NOTNULL,

                                intervaltype I2 DEFAULT '0' NOTNULL,
                                intervalstep I DEFAULT '0' NOTNULL,

                                daily_everyweekday I2 DEFAULT '0' NOTNULL,
                                weekly_monday I2 DEFAULT '0' NOTNULL,
                                weekly_tuesday I2 DEFAULT '0' NOTNULL,
                                weekly_wednesday I2 DEFAULT '0' NOTNULL,
                                weekly_thursday I2 DEFAULT '0' NOTNULL,
                                weekly_friday I2 DEFAULT '0' NOTNULL,
                                weekly_saturday I2 DEFAULT '0' NOTNULL,
                                weekly_sunday I2 DEFAULT '0' NOTNULL,
                                monthly_type I2 DEFAULT '0' NOTNULL,
                                monthly_day I2 DEFAULT '0' NOTNULL,
                                monthly_extdaystep C(50) DEFAULT '0' NOTNULL,
                                monthly_extday C(50) DEFAULT '0' NOTNULL,
                                yearly_type I2 DEFAULT '0' NOTNULL,
                                yearly_month I2 DEFAULT '0' NOTNULL,
                                yearly_monthday I2 DEFAULT '0' NOTNULL,
                                yearly_extdaystep C(50) DEFAULT '0' NOTNULL,
                                yearly_extday C(50) DEFAULT '0' NOTNULL,
                                yearly_extmonth I2 DEFAULT '0' NOTNULL,


                                startdateline I DEFAULT '0' NOTNULL,
                                endtype I2 DEFAULT '0' NOTNULL,
                                enddateline I DEFAULT '0' NOTNULL,
                                endcount I DEFAULT '0' NOTNULL,
                                creationcount I DEFAULT '0' NOTNULL,
                                nextrecurrence I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'nextrecurrence, startdateline';
    const INDEX_2            =    'ticketid';


    protected $_dataStore = array();

    // Core Constants
    const INTERVAL_DAILY = 1;
    const INTERVAL_WEEKLY = 2;
    const INTERVAL_MONTHLY = 3;
    const INTERVAL_YEARLY = 4;

    const MONTHLY_DEFAULT = 1;
    const MONTHLY_EXTENDED = 2;

    const YEARLY_DEFAULT = 1;
    const YEARLY_EXTENDED = 2;

    const END_NOEND = 1;
    const END_DATE = 2;
    const END_OCCURENCES = 3;

    const TICKETTYPE_SENDEMAIL = 1;
    const TICKETTYPE_ASUSER = 2;

    const TICKETRECUR_ITEMS_LIMIT = 1000;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Ticket Recurrence Object');
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketrecurrences', $this->GetUpdatePool(), 'UPDATE', "ticketrecurrenceid = '" . ($this->GetTicketRecurrenceID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket Recurrence ID
     *
     * @author Varun Shoor
     * @return mixed "ticketrecurrenceid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketRecurrenceID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketrecurrenceid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded())
        {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketrecurrences WHERE ticketrecurrenceid = '" . ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketrecurrenceid']) && !empty($_dataStore['ticketrecurrenceid']))
            {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ticketrecurrenceid']) || empty($this->_dataStore['ticketrecurrenceid']))
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            return true;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }

    /**
     * Returns the Data Store Array
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" Array on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid interval type
     *
     * @author Varun Shoor
     * @param mixed $_intervalType The Interval Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidIntervalType($_intervalType)
    {
        if ($_intervalType == self::INTERVAL_DAILY || $_intervalType == self::INTERVAL_MONTHLY || $_intervalType == self::INTERVAL_WEEKLY || $_intervalType == self::INTERVAL_YEARLY)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid monthly type
     *
     * @author Varun Shoor
     * @param mixed $_monthlyType The Monthly Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidMonthlyType($_monthlyType)
    {
        if ($_monthlyType == self::MONTHLY_DEFAULT || $_monthlyType == self::MONTHLY_EXTENDED)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid yearly type
     *
     * @author Varun Shoor
     * @param mixed $_yearlyType The Yearly Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidYearlyType($_yearlyType)
    {
        if ($_yearlyType == self::YEARLY_DEFAULT || $_yearlyType == self::YEARLY_EXTENDED)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid end type
     *
     * @author Varun Shoor
     * @param mixed $_endType The End Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidEndType($_endType)
    {
        if ($_endType == self::END_NOEND || $_endType == self::END_DATE || $_endType == self::END_OCCURENCES)
        {
            return true;
        }

        return false;
    }

    /**
     * Check to see if its a valid ticket type
     *
     * @author Varun Shoor
     * @param mixed $_ticketType The Ticket Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidTicketType($_ticketType)
    {
        if ($_ticketType == self::TICKETTYPE_SENDEMAIL || $_ticketType == self::TICKETTYPE_ASUSER)
        {
            return true;
        }

        return false;
    }

    /**
     * Create a new Ticket Recurrence Record: DAILY
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param mixed $_ticketType The Ticket Type (SEND EMAIL.AS USER)
     * @param int $_departmentID
     * @param int $_ownerStaffID
     * @param int $_ticketTypeID
     * @param int $_ticketStatusID
     * @param int $_ticketPriorityID
     * @param int $_intervalStep The Interval Step. Ex. Every >1< DAY
     * @param bool $_dailyEveryWeekday
     * @param int $_startDateline
     * @param mixed $_endType The End Type
     * @param int $_endDateline End at X Date
     * @param int $_endCount End after X Occurences
     * @param bool $_dontSendEmail
     * @param bool $_dispatchAutoResponder
     * @return SWIFT_TicketRecurrence The Newly Created Ticket Recurrence Object
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function CreateDaily(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject, $_ticketType, $_departmentID, $_ownerStaffID, $_ticketTypeID, $_ticketStatusID, $_ticketPriorityID,
            $_intervalStep, $_dailyEveryWeekday, $_startDateline, $_endType, $_endDateline, $_endCount, $_dontSendEmail, $_dispatchAutoResponder)
    {
        $_SWIFT = SWIFT::GetInstance();

        return self::Create($_SWIFT_TicketObject, $_SWIFT_StaffObject, $_ticketType, $_departmentID, $_ownerStaffID, $_ticketTypeID, $_ticketStatusID, $_ticketPriorityID,
                self::INTERVAL_DAILY, $_intervalStep, $_dailyEveryWeekday, false, false, false, false, false, false, false, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                $_startDateline, $_endType, $_endDateline, $_endCount, $_dontSendEmail, $_dispatchAutoResponder);
    }


    /**
     * Create a new Ticket Recurrence Record: WEEKLY
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param mixed $_ticketType The Ticket Type (SEND EMAIL.AS USER)
     * @param int $_departmentID
     * @param int $_ownerStaffID
     * @param int $_ticketTypeID
     * @param int $_ticketStatusID
     * @param int $_ticketPriorityID
     * @param int $_intervalStep The Interval Step. Ex. Every >1< DAY
     * @param bool $_weeklyMonday
     * @param bool $_weeklyTuesday
     * @param bool $_weeklyWednesday
     * @param bool $_weeklyThursday
     * @param bool $_weeklyFriday
     * @param bool $_weeklySaturday
     * @param bool $_weeklySunday
     * @param int $_startDateline
     * @param mixed $_endType The End Type
     * @param int $_endDateline End at X Date
     * @param int $_endCount End after X Occurences
     * @param bool $_dontSendEmail
     * @param bool $_dispatchAutoResponder
     * @return SWIFT_TicketRecurrence The Newly Created Ticket Recurrence Object
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function CreateWeekly(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject, $_ticketType, $_departmentID, $_ownerStaffID, $_ticketTypeID, $_ticketStatusID, $_ticketPriorityID,
            $_intervalStep, $_weeklyMonday, $_weeklyTuesday, $_weeklyWednesday, $_weeklyThursday, $_weeklyFriday, $_weeklySaturday, $_weeklySunday,
            $_startDateline, $_endType, $_endDateline, $_endCount, $_dontSendEmail, $_dispatchAutoResponder)
    {
        $_SWIFT = SWIFT::GetInstance();

        return self::Create($_SWIFT_TicketObject, $_SWIFT_StaffObject, $_ticketType, $_departmentID, $_ownerStaffID, $_ticketTypeID, $_ticketStatusID, $_ticketPriorityID,
                self::INTERVAL_WEEKLY, $_intervalStep, 0, $_weeklyMonday, $_weeklyTuesday, $_weeklyWednesday, $_weeklyThursday, $_weeklyFriday, $_weeklySaturday, $_weeklySunday,
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                $_startDateline, $_endType, $_endDateline, $_endCount, $_dontSendEmail, $_dispatchAutoResponder);
    }


    /**
     * Create a new Ticket Recurrence Record: MONTHLY
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param mixed $_ticketType The Ticket Type (SEND EMAIL.AS USER)
     * @param int $_departmentID
     * @param int $_ownerStaffID
     * @param int $_ticketTypeID
     * @param int $_ticketStatusID
     * @param int $_ticketPriorityID
     * @param int $_intervalStep The Interval Step. Ex. Every >1< DAY
     * @param bool $_monthlyType The Monthly Type (DEFAULT/EXTENDED)
     * @param int $_monthlyDay The Monthly Day (SAY EVERY >19< of >1< MONTHS)
     * @param int $_monthlyExtendedDay The Extended Monthly Day (Every Third >WEDNESDAY< of Every 1 Months)
     * @param int $_monthlyExtendedDayStep The Extended Monthly Day (Eevery >THIRD< Wednesday of Every 1 Months)
     * @param int $_startDateline
     * @param mixed $_endType The End Type
     * @param int $_endDateline End at X Date
     * @param int $_endCount End after X Occurences
     * @param bool $_dontSendEmail
     * @param bool $_dispatchAutoResponder
     * @return SWIFT_TicketRecurrence The Newly Created Ticket Recurrence Object
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function CreateMonthly(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject, $_ticketType, $_departmentID, $_ownerStaffID, $_ticketTypeID, $_ticketStatusID, $_ticketPriorityID,
            $_intervalStep, $_monthlyType, $_monthlyDay, $_monthlyExtendedDay, $_monthlyExtendedDayStep, $_startDateline, $_endType, $_endDateline, $_endCount, $_dontSendEmail, $_dispatchAutoResponder)
    {
        $_SWIFT = SWIFT::GetInstance();

        return self::Create($_SWIFT_TicketObject, $_SWIFT_StaffObject, $_ticketType, $_departmentID, $_ownerStaffID, $_ticketTypeID, $_ticketStatusID, $_ticketPriorityID,
                self::INTERVAL_MONTHLY, $_intervalStep, 0, 0, 0, 0, 0, 0, 0, 0,
                $_monthlyType, $_monthlyDay, $_monthlyExtendedDay, $_monthlyExtendedDayStep,
                0, 0, 0, 0, 0, 0,
                $_startDateline, $_endType, $_endDateline, $_endCount, $_dontSendEmail, $_dispatchAutoResponder);
    }


    /**
     * Create a new Ticket Recurrence Record: YEARLY
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param mixed $_ticketType The Ticket Type (SEND EMAIL.AS USER)
     * @param int $_departmentID
     * @param int $_ownerStaffID
     * @param int $_ticketTypeID
     * @param int $_ticketStatusID
     * @param int $_ticketPriorityID
     * @param int $_yearlyType The Yearly Type (DEFAULT/EXTENDED)
     * @param int $_yearlyMonth (Every >MAY< 19)
     * @param int $_yearlyMonthDay (Every May >19<)
     * @param int $_yearlyExtendedDay (Every Third >Wednesday< of May)
     * @param int $_yearlyExtendedDayStep (Every >Third< Wednesday of May)
     * @param int $_yearlyExtendedMonth (Every Third Wednesday of >May<)
     * @param int $_startDateline
     * @param mixed $_endType The End Type
     * @param int $_endDateline End at X Date
     * @param int $_endCount End after X Occurences
     * @param bool $_dontSendEmail
     * @param bool $_dispatchAutoResponder
     * @return SWIFT_TicketRecurrence The Newly Created Ticket Recurrence Object
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function CreateYearly(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject, $_ticketType, $_departmentID, $_ownerStaffID, $_ticketTypeID, $_ticketStatusID, $_ticketPriorityID,
            $_yearlyType, $_yearlyMonth, $_yearlyMonthDay, $_yearlyExtendedDay, $_yearlyExtendedDayStep, $_yearlyExtendedMonth, $_startDateline, $_endType, $_endDateline, $_endCount, $_dontSendEmail, $_dispatchAutoResponder)
    {
        $_SWIFT = SWIFT::GetInstance();

        return self::Create($_SWIFT_TicketObject, $_SWIFT_StaffObject, $_ticketType, $_departmentID, $_ownerStaffID, $_ticketTypeID, $_ticketStatusID, $_ticketPriorityID,
                self::INTERVAL_YEARLY, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                $_yearlyType, $_yearlyMonth, $_yearlyMonthDay, $_yearlyExtendedDay, $_yearlyExtendedDayStep, $_yearlyExtendedMonth,
                $_startDateline, $_endType, $_endDateline, $_endCount, $_dontSendEmail, $_dispatchAutoResponder);
    }

    /**
     * Create a new Ticket Recurrence Record
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param mixed $_ticketType The Ticket Type (SEND EMAIL.AS USER)
     * @param int $_departmentID
     * @param int $_ownerStaffID
     * @param int $_ticketTypeID
     * @param int $_ticketStatusID
     * @param int $_ticketPriorityID
     * @param mixed $_intervalType The Interval Type: DAILY/WEEKLY/MONTHLY/YEARLY
     * @param int $_intervalStep The Interval Step. Ex. Every >1< DAY
     * @param bool $_dailyEveryWeekday
     * @param bool $_weeklyMonday
     * @param bool $_weeklyTuesday
     * @param bool $_weeklyWednesday
     * @param bool $_weeklyThursday
     * @param bool $_weeklyFriday
     * @param bool $_weeklySaturday
     * @param bool $_weeklySunday
     * @param bool $_monthlyType The Monthly Type (DEFAULT/EXTENDED)
     * @param int $_monthlyDay The Monthly Day (SAY EVERY >19< of >1< MONTHS)
     * @param int $_monthlyExtendedDay The Extended Monthly Day (Every Third >WEDNESDAY< of Every 1 Months)
     * @param int $_monthlyExtendedDayStep The Extended Monthly Day (Eevery >THIRD< Wednesday of Every 1 Months)
     * @param int $_yearlyType The Yearly Type (DEFAULT/EXTENDED)
     * @param int $_yearlyMonth (Every >MAY< 19)
     * @param int $_yearlyMonthDay (Every May >19<)
     * @param int $_yearlyExtendedDay (Every Third >Wednesday< of May)
     * @param int $_yearlyExtendedDayStep (Every >Third< Wednesday of May)
     * @param int $_yearlyExtendedMonth (Every Third Wednesday of >May<)
     * @param int $_startDateline
     * @param mixed $_endType The End Type
     * @param int $_endDateline End at X Date
     * @param int $_endCount End after X Occurences
     * @param bool $_dontSendEmail
     * @param bool $_dispatchAutoResponder
     * @return SWIFT_TicketRecurrence The Newly Created Ticket Recurrence Object
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    protected static function Create(SWIFT_Ticket $_SWIFT_TicketObject, SWIFT_Staff $_SWIFT_StaffObject, $_ticketType, $_departmentID, $_ownerStaffID, $_ticketTypeID, $_ticketStatusID, $_ticketPriorityID,
            $_intervalType, $_intervalStep, $_dailyEveryWeekday, $_weeklyMonday, $_weeklyTuesday, $_weeklyWednesday, $_weeklyThursday, $_weeklyFriday, $_weeklySaturday, $_weeklySunday,
            $_monthlyType, $_monthlyDay, $_monthlyExtendedDay, $_monthlyExtendedDayStep, $_yearlyType, $_yearlyMonth, $_yearlyMonthDay, $_yearlyExtendedDay, $_yearlyExtendedDayStep,
            $_yearlyExtendedMonth, $_startDateline, $_endType, $_endDateline, $_endCount, $_dontSendEmail, $_dispatchAutoResponder)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded() || !$_SWIFT_StaffObject instanceof SWIFT_Staff || !$_SWIFT_StaffObject->GetIsClassLoaded() ||
                !self::IsValidIntervalType($_intervalType) || empty($_startDateline) || !self::IsValidEndType($_endType) || !self::IsValidTicketType($_ticketType))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketrecurrences', array('dateline' => DATENOW, 'ticketid' => $_SWIFT_TicketObject->GetTicketID(), 'tickettype' => ($_ticketType), 'departmentid' => $_departmentID,
            'ownerstaffid' => $_ownerStaffID, 'tickettypeid' => $_ticketTypeID, 'ticketstatusid' => $_ticketStatusID, 'ticketpriorityid' => $_ticketPriorityID,
            'staffid' => ($_SWIFT_StaffObject->GetStaffID()), 'intervaltype' => (int)($_intervalType), 'intervalstep' =>  (int)($_intervalStep), 'daily_everyweekday' => (int)($_dailyEveryWeekday),
            'weekly_monday' => (int)($_weeklyMonday), 'weekly_tuesday' => (int)($_weeklyTuesday), 'weekly_wednesday' => (int)($_weeklyWednesday), 'weekly_thursday' => (int)($_weeklyThursday), 'weekly_friday' => (int)($_weeklyFriday),
            'weekly_saturday' => (int)($_weeklySaturday), 'weekly_sunday' => (int)($_weeklySunday), 'monthly_type' => (int)($_monthlyType), 'monthly_day' => (int)($_monthlyDay), 'monthly_extdaystep' => $_monthlyExtendedDayStep, 'monthly_extday' => $_monthlyExtendedDay,
            'yearly_type' =>  (int)($_yearlyType), 'yearly_month' =>  (int)($_yearlyMonth), 'yearly_monthday' => (int)($_yearlyMonthDay), 'yearly_extdaystep' => $_yearlyExtendedDayStep, 'yearly_extday' => $_yearlyExtendedDay, 'yearly_extmonth' =>  ($_yearlyExtendedMonth),
            'startdateline' => (int)($_startDateline), 'endtype' => ($_endType), 'enddateline' => (int)($_endDateline), 'endcount' => (int)($_endCount),
            'dontsendemail' => (int)($_dontSendEmail), 'dispatchautoresponder' => (int)($_dispatchAutoResponder)), 'INSERT');
        $_ticketRecurrenceID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketRecurrenceID)
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TicketRecurrenceObject = new SWIFT_TicketRecurrence(new SWIFT_DataID($_ticketRecurrenceID));
        if (!$_SWIFT_TicketRecurrenceObject instanceof SWIFT_TicketRecurrence || !$_SWIFT_TicketRecurrenceObject->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_TicketRecurrenceObject->UpdatePool('nextrecurrence', ($_SWIFT_TicketRecurrenceObject->GetNextRecurrence()));

        return $_SWIFT_TicketRecurrenceObject;
    }

    /**
     * Update Recurrence Record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return true;
    }

    /**
     * Delete the Ticket Recurrence record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketRecurrenceID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ticket Recurrences
     *
     * @author Varun Shoor
     * @param array $_ticketRecurrenceIDList The Ticket Recurrence ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketRecurrenceIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketRecurrenceIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketrecurrences WHERE ticketrecurrenceid IN (" . BuildIN($_ticketRecurrenceIDList) . ")");

        return true;
    }

    /**
     * Delete the ticket recurrences based on a list of ticket ids
     *
     * @author Varun Shoor
     * @param array $_ticketIDList The Ticket ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnTicket($_ticketIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketIDList))
        {
            return false;
        }

        $_ticketRecurrenceIDList = array();
        $_SWIFT->Database->Query("SELECT ticketrecurrenceid FROM " . TABLE_PREFIX . "ticketrecurrences WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketRecurrenceIDList[] = $_SWIFT->Database->Record['ticketrecurrenceid'];
        }

        if (!count($_ticketRecurrenceIDList))
        {
            return false;
        }

        self::DeleteList($_ticketRecurrenceIDList);

        return true;
    }

    /**
     * Get the next weekly date
     *
     * @author Varun Shoor
     * @param int $_currentDate
     * @param int $_intervalStep
     * @param bool $_isMonday
     * @param bool $_isTuesday
     * @param bool $_isWednesday
     * @param bool $_isThursday
     * @param bool $_isFriday
     * @param bool $_isSaturday
     * @param bool $_isSunday
     * @return bool "true" on Success, "false" otherwise
     */
    public static function GetNextWeeklyDate($_currentDate, $_intervalStep, $_isMonday, $_isTuesday, $_isWednesday, $_isThursday, $_isFriday, $_isSaturday, $_isSunday)
    {
        $_SWIFT = SWIFT::GetInstance();

        /**
         * BUG FIX : Nidhi Gupta
         *
         * SWIFT-4417 : Weekly recurrence does not work
         *
         * Comment- Changed gmdate to date to get zone specific time.
         */
        $_currentDay = date('D', $_currentDate);

        // First get the start of week for current date
        $_startOfWeek = 0;
        if ($_currentDay == 'Mon')
        {
            $_startOfWeek = $_currentDate;
        } else {
            $_startOfWeek = strtotime('last Monday', $_currentDate);
        }

        // Now we loop through days
        $_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $_activeDate = $_startOfWeek;
        $_nextRecurrence = 0;

        foreach ($_days as $_day)
        {
            $_boolVariable = '_is' . $_day;
            // If day == Tuesday and It is Active and date is greater than current date and its not same as current date
            if (date('l', $_activeDate) == $_day && $$_boolVariable == true && $_activeDate > $_currentDate && date('d M Y', $_activeDate) != date('d M Y', $_currentDate))
            {
                $_nextRecurrence = $_activeDate;

                break;
            }

            $_activeDate = $_activeDate + 86400;
        }

        if (empty($_nextRecurrence))
        {
            // Say +1 week into it
            $_nextWeekDate = strtotime('+' . ($_intervalStep) . ' week Monday    ', $_startOfWeek);

            $_activeDate = $_nextWeekDate;
            foreach ($_days as $_day)
            {
                $_boolVariable = '_is' . $_day;
                // If day == Tuesday and It is Active and date is greater than current date and its not same as current date
                if (date('l', $_activeDate) == $_day && $$_boolVariable == true && $_activeDate > $_currentDate && date('d M Y', $_activeDate) != date('d M Y', $_currentDate))
                {
                    $_nextRecurrence = $_activeDate;

                    break;
                }
                $_activeDate = $_activeDate + 86400;
            }
        }

        return $_nextRecurrence;
    }

    /**
     * Retrieve the Next Occurence Date
     *
     * @author Varun Shoor
     * @return int The Next Recurrence
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetNextRecurrence()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-4643 Avoid multiple tickets creation in monthly recurring scheduled task exeuction
         *
         * Comments : Setting timezone to GMT to avoid difference between timezone
         */
        $_currentTimeZone =  date_default_timezone_get();
        date_default_timezone_set('UTC');

        $_currentDate = SWIFT_Date::FloorDate(DATENOW);
        /**
         * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-4685 'Every weekday' option under 'Daily' recurrence should not create ticket on Saturdays
         *
         * Comments : gmdate to date change.
         */
        $_currentDay = date('D', $_currentDate);
        $_nextRecurrence = 0;

        /**
         * ---------------------------------------------
         * DAILY
         * ---------------------------------------------
         */
        if ($this->GetProperty('intervaltype') == self::INTERVAL_DAILY)
        {
            // Do we have to execute it only on weekdays?
            if ($this->GetProperty('daily_everyweekday') != '0')
            {
                // Is it Saturday?
                if ($_currentDay == 'Sat') {
                    $_nextRecurrence = $_currentDate + (2*86400);
                } else if ($_currentDay == 'Fri') {
                    $_nextRecurrence = $_currentDate + (3*86400);
                } else {
                    $_nextRecurrence = $_currentDate + 86400;
                }
            } else {
                $_nextRecurrence = $_currentDate + (($this->GetProperty('intervalstep'))*86400);
            }


        /**
         * ---------------------------------------------
         * WEEKLY
         * ---------------------------------------------
         */
        } else if ($this->GetProperty('intervaltype') == self::INTERVAL_WEEKLY) {
            $_nextRecurrence = self::GetNextWeeklyDate($_currentDate, $this->GetProperty('intervalstep'), $this->GetProperty('weekly_monday'), $this->GetProperty('weekly_tuesday'),
                    $this->GetProperty('weekly_wednesday'), $this->GetProperty('weekly_thursday'), $this->GetProperty('weekly_friday'), $this->GetProperty('weekly_saturday'), $this->GetProperty('weekly_sunday'));



        /**
         * ---------------------------------------------
         * MONTHLY
         * ---------------------------------------------
         */
        } else if ($this->GetProperty('intervaltype') == self::INTERVAL_MONTHLY) {
            $_currentDay = date('d', $_currentDate);
            if ($this->GetProperty('monthly_type') == self::MONTHLY_DEFAULT) {
                //Check to get recurrence for current month while its Month end (31st specifically)
                if ($_currentDay == '31') {
                    $_newTime        = gmmktime(0, 0, 0, date('m', $_currentDate), date('d', $_currentDate), date('Y', $_currentDate));
                    $_nextMonthDate = strtotime('first day of ' . $this->GetProperty('intervalstep'). 'month' , $_newTime);
                } else {
                    $_nextMonthDate = strtotime('+' . ($this->GetProperty('intervalstep')) . ' month', $_currentDate);
                }
                /**
                 * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
                 *
                 * SWIFT-4206: Issue with monthly ticket recurrence.
                 *
                 * Comments : Adjusted next recurrence if it falls in current month
                 */
                $_nextMonthRecurrence    = gmmktime(0, 0, 0, date('m', $_nextMonthDate), ($this->GetProperty('monthly_day')), date('Y', $_nextMonthDate));
                $_currentMonthRecurrence = gmmktime(0, 0, 0, date('m', $_currentDate), ($this->GetProperty('monthly_day')), date('Y', $_currentDate));

                if ($_currentMonthRecurrence < time()) {
                    $_nextRecurrence = $_nextMonthRecurrence;
                } else {
                    $_nextRecurrence = $_currentMonthRecurrence;
                }
            } else {
                //Check to get recurrence for current month while its Month end (31st specifically)
                if ($_currentDay == 31) {
                    $_nextMonth = strtotime('first day of ' . $this->GetProperty('intervalstep') . 'month', $_currentDate);
                } else {
                    $_nextMonth = strtotime('+' . ($this->GetProperty('intervalstep')) . ' month', $_currentDate);
                }

                //While required day is first day of month it was skipping first week
                $_startOfMonth        = gmmktime(0, 0, 0, date('m', $_nextMonth), 1, date('Y', $_nextMonth));
                $_nextMonthRecurrence = strtotime($this->GetProperty('monthly_extdaystep') . ' ' . $this->GetProperty('monthly_extday') . ' of ' . date('F Y', $_startOfMonth));

                $_startOfCurrentMonth    = gmmktime(0, 0, 0, date('m', time()), 1, date('Y', time()));
                $_currentMonthRecurrence = strtotime($this->GetProperty('monthly_extdaystep') . ' ' . $this->GetProperty('monthly_extday') . ' of ' . date('F Y', $_startOfCurrentMonth));
                if ($_currentMonthRecurrence < time()) {
                    $_nextRecurrence = $_nextMonthRecurrence;
                } else {
                    $_nextRecurrence = $_currentMonthRecurrence;
                }
            }



        /**
         * ---------------------------------------------
         * YEARLY
         * ---------------------------------------------
         */
        } else if ($this->GetProperty('intervaltype') == self::INTERVAL_YEARLY) {
            if ($this->GetProperty('yearly_type') == self::YEARLY_DEFAULT)
            {
                $_nextYear = strtotime('+1 year', $_currentDate);
                /**
                 * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
                 *
                 * SWIFT-4643 Multiple tickets get created with every cron while setting Monthly recurrence
                 *
                 * Comments : Changed gmdate to date to get zone specific time.
                 */
                $_nextRecurrence = mktime(0, 0, 0, $this->GetProperty('yearly_month'), $this->GetProperty('yearly_monthday'), date('Y', $_nextYear));
                /**
                 * BUG FIX : Mansi Wason <mansi.wason@kayako.com>
                 *
                 * SWIFT-5034 Adjust to support current year within yearly ticket recurrence
                 *
                 * Comments : Adjusted the time calculations properly.
                 */
                $_currentRecurrence = mktime(0, 0, 0, $this->GetProperty('yearly_month'), $this->GetProperty('yearly_monthday'), date('Y', $_currentDate));
                if ($_currentRecurrence < time()) {
                    $_nextRecurrence = $_nextRecurrence;
                } else {
                    $_nextRecurrence = $_currentRecurrence;
                }
            } else {
                $_nextYear = strtotime('+1 year', $_currentDate);
                $_startOfMonth = mktime(0, 0, 0, $this->GetProperty('yearly_month'), 1, gmdate('Y', $_nextYear));
                $_nextRecurrence = strtotime($this->GetProperty('yearly_extdaystep') . ' ' . $this->GetProperty('yearly_extday'), $_startOfMonth);

            }
        }
        date_default_timezone_set($_currentTimeZone);
        return $_nextRecurrence;
    }

    /**
     * Update the Next Recurrence Date
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function UpdateNextRecurrence()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('nextrecurrence', ($this->GetNextRecurrence()));
        $this->UpdatePool('creationcount', $this->GetProperty('creationcount') + 1);
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Create Ticket from set Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CreateTicket()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Language->Load('staff_ticketsmain');
        $this->Language->Load('staff_ticketsmanage');

        SWIFT_Ticket::LoadLanguageTable();

        /**
         * ---------------------------------------------
         * ATTEMPT TO LOAD EXISTING TICKET
         * ---------------------------------------------
         */

        $_SWIFT_TicketObject = false;
        try
        {
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataID($this->GetProperty('ticketid')));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {

            return false;
        }

        // Load the user object for the user based tickets
        $_SWIFT_UserObject = false;
        if ($this->GetProperty('tickettype') == self::TICKETTYPE_ASUSER)
        {
            $_SWIFT_UserObject = $_SWIFT_TicketObject->GetUserObject();
            if (!$_SWIFT_UserObject instanceof SWIFT_User || !$_SWIFT_UserObject->GetIsClassLoaded())
            {
                throw new SWIFT_Exception('No User Object Found. Unable to create recurring ticket.');
            }
        }

        $_emailQueueID = $_SWIFT_TicketObject->GetProperty('emailqueueid');

        $_dontSendEmail = ($this->GetProperty('dontsendemail'));
        $_dispatchAutoResponder = ($this->GetProperty('dispatchautoresponder'));

        // Create the ticket
        $_creatorFullName = $_creatorEmail = $_phoneNumber = $_destinationEmail = '';
        $_userID = 0;
        $_ownerStaffID = $_SWIFT_TicketObject->GetProperty('ownerstaffid');
        $_ticketPhoneType = SWIFT_Ticket::TYPE_DEFAULT;
        $_ticketCreator = SWIFT_Ticket::CREATOR_STAFF;

        if ($this->GetProperty('tickettype') == self::TICKETTYPE_SENDEMAIL)
        {
            $_creatorFullName = $_SWIFT_TicketObject->GetProperty('fullname');
            $_creatorEmail = $_SWIFT_TicketObject->GetProperty('email');
            $_userID = $_SWIFT_TicketObject->GetProperty('userid');
            $_ticketCreator = SWIFT_Ticket::CREATOR_STAFF;
            $_phoneNumber = '';

            $_destinationEmail = $_SWIFT_TicketObject->GetProperty('email');
            if ($_SWIFT_TicketObject->GetProperty('replyto') != '') {
                $_destinationEmail = $_SWIFT_TicketObject->GetProperty('replyto');
            }

        } else if ($this->GetProperty('tickettype') == self::TICKETTYPE_ASUSER) {
            $_creatorFullName = $_SWIFT_UserObject->GetProperty('fullname');
            $_creatorEmailList = $_SWIFT_UserObject->GetEmailList();
            $_creatorEmail = $_creatorEmailList[0];

            $_userID = $_SWIFT_UserObject->GetUserID();

            if ($_SWIFT_TicketObject->GetProperty('tickettype') == SWIFT_Ticket::TYPE_PHONE)
            {
                $_ticketPhoneType = SWIFT_Ticket::TYPE_PHONE;
            }

            $_ticketCreator = SWIFT_Ticket::CREATOR_USER;
            $_phoneNumber = $_SWIFT_UserObject->GetProperty('phone');

        }

        $_SWIFT_TicketPostObject = $_SWIFT_TicketObject->GetFirstPostObject();

        $_SWIFT_TicketObject_New = SWIFT_Ticket::Create($_SWIFT_TicketObject->GetProperty('subject'), $_creatorFullName, $_creatorEmail,
                                                        $_SWIFT_TicketPostObject->GetProperty('contents'), $_ownerStaffID, $this->GetProperty('departmentid'),
                                                        $this->GetProperty('ticketstatusid'), $this->GetProperty('ticketpriorityid'), $this->GetProperty('tickettypeid'),
                                                        $_userID, $this->GetProperty('staffid'), $_ticketPhoneType, $_ticketCreator, SWIFT_Ticket::CREATIONMODE_STAFFCP,
                                                        $_phoneNumber, $_emailQueueID, false, $_destinationEmail);
        /**
         * BUG FIX - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-3818 Custom Field values missing on recurring tickets.
         */
        SWIFT_CustomFieldValue::CustomfieldvalueOnType($_SWIFT_TicketObject_New, $_SWIFT_TicketObject);
        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4268 Issue with ticket recurrence, if ticket is created using 'As a user' option.
         */
        $_templateGroupCache  = $this->Cache->Get('templategroupcache');
        $_userTemplateGroupID = ($_SWIFT_UserObject instanceof SWIFT_User) ? $_SWIFT_UserObject->GetTemplateGroupID() : false;

        // Is user linked to any template ?
        if (!empty($_userTemplateGroupID) && isset($_templateGroupCache[$_userTemplateGroupID])) {
            $_SWIFT_TicketObject_New->SetTemplateGroup($_userTemplateGroupID);
        } else {
            $_userTemplateGroupID = SWIFT_TemplateGroup::GetDefaultGroupID();
            $_SWIFT_TicketObject_New->SetTemplateGroup($_userTemplateGroupID);
        }

        // Did the object load up?
        if (!$_SWIFT_TicketObject_New instanceof SWIFT_Ticket || !$_SWIFT_TicketObject_New->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        // Update the recurrencefrom column with the actual ticket ID from which, new ticket is generated
        $_SWIFT_TicketObject_New->UpdatePool('recurrencefromticketid', ($_SWIFT_TicketObject->GetID()));
        try {
            $_SWIFT_StaffObject = new SWIFT_Staff(new SWIFT_DataID($this->GetProperty('staffid')));
        } catch(\Exception $ex) {
            $_SWIFT_StaffObject = false;
        }
        // We dispatch the autoresponder after creation so that we end up adding all the CC users etc.
        if ($_dispatchAutoResponder)
        {
            $_SWIFT_TicketObject_New->DispatchAutoresponder();
        }

        $_fromEmailAddress = '';

        $_emailQueueCache = $_SWIFT->Cache->Get('queuecache');

        if (isset($_emailQueueCache['list'][$_emailQueueID])) {
            $_fromEmailAddress = $_emailQueueCache['list'][$_emailQueueID]['email'];
        }

        if (empty($_fromEmailAddress))
        {
            $_fromEmailAddress = $_SWIFT->Settings->Get('general_returnemail');
        }
        /**
         * FEATURE - Mansi Wason <mansi.wason@kayako.com>
         *
         * SWIFT-4687 'CC' recipients should be maintained in recurred tickets.
         **/
        // Find and add any additional recipients.
        $_ticketRecipientContainer = SWIFT_TicketRecipient::RetrieveOnTicket($_SWIFT_TicketObject);
        if (_is_array($_ticketRecipientContainer)) {
            foreach ($_ticketRecipientContainer as $_recipientType => $_recipientName) {
                SWIFT_TicketRecipient::Create($_SWIFT_TicketObject_New, $_recipientType, $_recipientName);
            }
        }

        if ($_dontSendEmail == false && $_SWIFT_StaffObject)
        {
            // Carry out the email dispatch logic
            $_SWIFT_TicketEmailDispatchObject = new SWIFT_TicketEmailDispatch($_SWIFT_TicketObject_New);

            $_ticketContents = $this->Emoji->decode($_SWIFT_TicketPostObject->GetProperty('contents'));

            $_isHTML = SWIFT_HTML::DetectHTMLContent($_ticketContents);

            $_SWIFT_TicketEmailDispatchObject->DispatchStaffReply($_SWIFT_StaffObject, $_ticketContents, $_isHTML, $_fromEmailAddress);
        }

        $_SWIFT_TicketObject->ProcessUpdatePool();
        SWIFT_TicketManager::RebuildCache();

        return true;
    }

    /**
     * Execute the Recurring Tickets
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Execute()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ticketIDList = $_ticketObjectContainer = $_ticketRecurrenceContainer = array();

        $_SWIFT->Database->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "ticketrecurrences WHERE nextrecurrence <= '" . DATENOW . "' AND nextrecurrence != '0' and startdateline <= '" . DATENOW . "'", self::TICKETRECUR_ITEMS_LIMIT);
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketIDList[] = ($_SWIFT->Database->Record['ticketid']);
            $_ticketRecurrenceContainer[$_SWIFT->Database->Record['ticketrecurrenceid']] = $_SWIFT->Database->Record;
        }

        if (!count($_ticketIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketObjectContainer[$_SWIFT->Database->Record['ticketid']] = new SWIFT_Ticket(new SWIFT_DataStore($_SWIFT->Database->Record));
        }

        $_deleteTicketRecurrenceIDList = array();

        // Now process the recurrence
        foreach ($_ticketRecurrenceContainer as $_ticketRecurrenceID => $_ticketRecurrence)
        {
            if (!isset($_ticketObjectContainer[$_ticketRecurrence['ticketid']]))
            {
                continue;
            }

            $_SWIFT_TicketRecurrenceObject = new SWIFT_TicketRecurrence(new SWIFT_DataStore($_ticketRecurrence));
            if (!$_SWIFT_TicketRecurrenceObject instanceof SWIFT_TicketRecurrence || !$_SWIFT_TicketRecurrenceObject->GetIsClassLoaded())
            {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_TicketRecurrenceObject->CreateTicket();
            $_SWIFT_TicketRecurrenceObject->UpdateNextRecurrence();

            /**
             * BUG FIX : Nidhi Gupta <nidhi.gupta@kayako.com>
             *
             * SWIFT-4512 Recurrence tab is removed after one occurrence and no further tickets are re-created
             *
             * Comments : Rectified Logical mistake to execute block.
             */
            if (($_SWIFT_TicketRecurrenceObject->GetProperty('endtype') == self::END_DATE && $_SWIFT_TicketRecurrenceObject->GetProperty('enddateline') <= DATENOW) ||
                ($_SWIFT_TicketRecurrenceObject->GetProperty('endtype') == self::END_OCCURENCES && $_SWIFT_TicketRecurrenceObject->GetProperty('creationcount') >= $_SWIFT_TicketRecurrenceObject->GetProperty('endcount')))
            {
                $_deleteTicketRecurrenceIDList[] = $_SWIFT_TicketRecurrenceObject->GetTicketRecurrenceID();
            }
        }

        // We need to delete the recurrences?
        if (count($_deleteTicketRecurrenceIDList))
        {
            self::DeleteList($_deleteTicketRecurrenceIDList);
        }

        return true;
    }


    /**
     * Retrieve the ticket recurrence object based on the given ticket
     *
     * @author Parminder Singh
     *
     * @param SWIFT_Ticket $_Ticket
     *
     * @return SWIFT_TicketRecurrence|bool
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicket(SWIFT_Ticket $_Ticket)
    {
        if (!$_Ticket instanceof SWIFT_Ticket || !$_Ticket->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT                     = SWIFT::GetInstance();
        $_ticketRecurrenceContainer = $_SWIFT->Database->QueryFetch("SELECT ticketrecurrenceid FROM " . TABLE_PREFIX . self::TABLE_NAME . "
                                                                     WHERE ticketid = " . ($_Ticket->GetID()));

        if (!isset($_ticketRecurrenceContainer['ticketrecurrenceid']) || empty($_ticketRecurrenceContainer['ticketrecurrenceid'])) {
            return false;
        }

        return new SWIFT_TicketRecurrence(new SWIFT_DataID($_ticketRecurrenceContainer['ticketrecurrenceid']));
    }

    /**
     * Reset recurrence fields before updating
     *
     * @author Parminder Singh
     *
     * @return SWIFT_TicketRecurrence
     * @throws SWIFT_Exception If class is not loaded
     */
    public function Reset()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('intervaltype', '0');
        $this->UpdatePool('intervalstep', '0');

        $this->UpdatePool('daily_everyweekday', '0');

        $this->UpdatePool('weekly_monday', '0');
        $this->UpdatePool('weekly_tuesday', '0');
        $this->UpdatePool('weekly_wednesday', '0');
        $this->UpdatePool('weekly_thursday', '0');
        $this->UpdatePool('weekly_friday', '0');
        $this->UpdatePool('weekly_saturday', '0');
        $this->UpdatePool('weekly_sunday', '0');

        $this->UpdatePool('monthly_type', '0');
        $this->UpdatePool('monthly_day', '0');
        $this->UpdatePool('monthly_extdaystep', '0');
        $this->UpdatePool('monthly_extday', '0');

        $this->UpdatePool('yearly_type', '0');
        $this->UpdatePool('yearly_month', '0');
        $this->UpdatePool('yearly_monthday', '0');
        $this->UpdatePool('yearly_extdaystep', '0');
        $this->UpdatePool('yearly_extday', '0');
        $this->UpdatePool('yearly_extmonth', '0');

        $this->ProcessUpdatePool();

        return $this;
    }

    /**
     * Update the Ticket Recurrence Record: DAILY
     *
     * @author Parminder Singh
     *
     * @param int  $_intervalStep
     * @param bool $_dailyEveryWeekday
     *
     * @return SWIFT_TicketRecurrence
     * @throws SWIFT_Exception If class is not loaded
     */
    public function UpdateDaily($_intervalStep, $_dailyEveryWeekday)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('intervaltype', self::INTERVAL_DAILY);
        $this->UpdatePool('intervalstep', ($_intervalStep));
        $this->UpdatePool('daily_everyweekday', ($_dailyEveryWeekday));

        $this->ProcessUpdatePool();

        return $this;
    }

    /**
     * Update the Ticket Recurrence Record: WEEKLY
     *
     * @author Parminder Singh
     *
     * @param int  $_intervalStep
     * @param bool $_weeklyMonday
     * @param bool $_weeklyTuesday
     * @param bool $_weeklyWednesday
     * @param bool $_weeklyThursday
     * @param bool $_weeklyFriday
     * @param bool $_weeklySaturday
     * @param bool $_weeklySunday
     *
     * @return SWIFT_TicketRecurrence
     * @throws SWIFT_Exception If class is not loaded
     */
    public function UpdateWeekly($_intervalStep, $_weeklyMonday, $_weeklyTuesday, $_weeklyWednesday, $_weeklyThursday, $_weeklyFriday, $_weeklySaturday, $_weeklySunday)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('intervaltype', self::INTERVAL_WEEKLY);
        $this->UpdatePool('intervalstep', ($_intervalStep));
        $this->UpdatePool('weekly_monday', ($_weeklyMonday));
        $this->UpdatePool('weekly_tuesday', ($_weeklyTuesday));
        $this->UpdatePool('weekly_wednesday', ($_weeklyWednesday));
        $this->UpdatePool('weekly_thursday', ($_weeklyThursday));
        $this->UpdatePool('weekly_friday', ($_weeklyFriday));
        $this->UpdatePool('weekly_saturday', ($_weeklySaturday));
        $this->UpdatePool('weekly_sunday', ($_weeklySunday));

        $this->ProcessUpdatePool();

        return $this;
    }

    /**
     * Update the Ticket Recurrence Record: MONTHLY
     *
     * @author Parminder Singh
     *
     * @param int  $_intervalStep
     * @param bool $_monthlyType
     * @param int  $_monthlyDay
     * @param int  $_monthlyExtendedDay
     * @param int  $_monthlyExtendedDayStep
     *
     * @return SWIFT_TicketRecurrence
     * @throws SWIFT_Exception If class is not loaded
     */
    public function UpdateMonthly($_intervalStep, $_monthlyType, $_monthlyDay, $_monthlyExtendedDay, $_monthlyExtendedDayStep)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('intervaltype', self::INTERVAL_MONTHLY);
        $this->UpdatePool('intervalstep', ($_intervalStep));
        $this->UpdatePool('monthly_type', ($_monthlyType));
        $this->UpdatePool('monthly_day', ($_monthlyDay));
        $this->UpdatePool('monthly_extdaystep', $_monthlyExtendedDayStep);
        $this->UpdatePool('monthly_extday', $_monthlyExtendedDay);

        $this->ProcessUpdatePool();

        return $this;
    }

    /**
     * Update the Ticket Recurrence Record: YEARLY
     *
     * @author Parminder Singh
     *
     * @param int $_yearlyType
     * @param int $_yearlyMonth
     * @param int $_yearlyMonthDay
     * @param int $_yearlyExtendedDay
     * @param int $_yearlyExtendedDayStep
     * @param int $_yearlyExtendedMonth
     *
     * @return SWIFT_TicketRecurrence
     * @throws SWIFT_Exception If class is not loaded
     */
    public function UpdateYearly($_yearlyType, $_yearlyMonth, $_yearlyMonthDay, $_yearlyExtendedDay, $_yearlyExtendedDayStep, $_yearlyExtendedMonth)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('intervaltype', self::INTERVAL_YEARLY);
        $this->UpdatePool('yearly_type', ($_yearlyType));
        $this->UpdatePool('yearly_month', ($_yearlyMonth));
        $this->UpdatePool('yearly_monthday', ($_yearlyMonthDay));
        $this->UpdatePool('yearly_extdaystep', $_yearlyExtendedDayStep);
        $this->UpdatePool('yearly_extday', $_yearlyExtendedDay);
        $this->UpdatePool('yearly_extmonth', ($_yearlyExtendedMonth));

        $this->ProcessUpdatePool();

        return $this;
    }

    /**
     * Update the Recurrence Range
     *
     * @author Parminder Singh
     *
     * @param int $_startDateline
     * @param int $_endType
     * @param int $_endDateline
     * @param int $_endCount
     *
     * @return SWIFT_TicketRecurrence
     * @throws SWIFT_Exception If class is not loaded
     */
    public function UpdateRecurrenceRange($_startDateline, $_endType, $_endDateline, $_endCount)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('startdateline', ($_startDateline));
        $this->UpdatePool('endtype', ($_endType));
        $this->UpdatePool('enddateline', ($_endDateline));
        $this->UpdatePool('endcount', ($_endCount));

        $this->ProcessUpdatePool();

        return $this;
    }

}
