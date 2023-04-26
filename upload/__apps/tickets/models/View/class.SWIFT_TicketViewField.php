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

namespace Tickets\Models\View;

use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Ticket View Field Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_TicketViewField extends SWIFT_Model {
    const TABLE_NAME        =    'ticketviewfields';
    const PRIMARY_KEY        =    'ticketviewfieldid';

    const TABLE_STRUCTURE    =    "ticketviewfieldid I PRIMARY AUTO NOTNULL,
                                ticketviewid I DEFAULT '0' NOTNULL,
                                fieldtype I2 DEFAULT '0' NOTNULL,
                                fieldtypeid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'ticketviewid';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_TICKET = '1';
    const TYPE_CUSTOM = '2';

    // Field Constants
    const FIELD_TICKETID = 1;
    const FIELD_SUBJECT = 2;
    const FIELD_QUEUE = 3;
    const FIELD_DEPARTMENT = 4;
    const FIELD_TICKETSTATUS = 5;
    const FIELD_DUEDATE = 6;
    const FIELD_LASTACTIVITY = 7;
    const FIELD_DATE = 8;
    const FIELD_OWNER = 9;
    const FIELD_PRIORITY = 10;
    const FIELD_LASTREPLIER = 11;
    const FIELD_FULLNAME = 12;
    const FIELD_TIMEWORKED = 13;
    const FIELD_EMAIL = 14;
    const FIELD_REPLIES = 15;
    const FIELD_FLAG = 16;
    const FIELD_ASSIGNSTATUS = 17;
    const FIELD_LASTSTAFFREPLY = 18;
    const FIELD_TEMPLATEGROUP = 19;
    const FIELD_LASTUSERREPLY = 20;
    const FIELD_SLAPLAN = 21;
    const FIELD_USERGROUP = 22;
    const FIELD_USERORGANIZATION = 23;
    const FIELD_ESCALATIONRULE = 24;
    const FIELD_ESCALATEDTIME = 25;
    const FIELD_RESOLUTIONDUEDATE = 26;
    const FIELD_TICKETTYPE = 27;
    const FIELD_TICKETTYPEICON = 28;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject) {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Ticket Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct() {
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
    public function ProcessUpdatePool() {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ticketviewfields', $this->GetUpdatePool(), 'UPDATE', "ticketviewfieldid = '" .
                (int) ($this->GetTicketViewFieldID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Ticket View Field ID
     *
     * @author Varun Shoor
     * @return mixed "ticketviewfieldid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTicketViewFieldID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ticketviewfieldid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject) {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ticketviewfields WHERE ticketviewfieldid = '" .
                    (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ticketviewfieldid']) && !empty($_dataStore['ticketviewfieldid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ticketviewfieldid']) || empty($this->_dataStore['ticketviewfieldid'])) {
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
    public function GetDataStore() {
        if (!$this->GetIsClassLoaded()) {
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
    public function GetProperty($_key) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid field type
     *
     * @author Varun Shoor
     * @param mixed $_fieldType The Field Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function IsValidFieldType($_fieldType) {
        $_SWIFT = SWIFT::GetInstance();

        if ($_fieldType == self::TYPE_TICKET || $_fieldType == self::TYPE_CUSTOM)
        {
            return true;
        }

        return false;
    }

    /**
     * Create a new Ticket View Field
     *
     * @author Varun Shoor
     * @param SWIFT_TicketView $_SWIFT_TicketViewObject
     * @param mixed $_fieldType The Field Type
     * @param int $_fieldTypeID The Field Type ID
     * @return int Ticket View Field ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_TicketView $_SWIFT_TicketViewObject, $_fieldType, $_fieldTypeID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidFieldType($_fieldType) || empty($_fieldTypeID) || !$_SWIFT_TicketViewObject instanceof SWIFT_TicketView ||
                !$_SWIFT_TicketViewObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ticketviewfields', array('ticketviewid' => (int) ($_SWIFT_TicketViewObject->GetTicketViewID()),
            'fieldtype' => (int) ($_fieldType), 'fieldtypeid' => $_fieldTypeID), 'INSERT');
        $_ticketViewFieldID = $_SWIFT->Database->Insert_ID();

        if (!$_ticketViewFieldID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_ticketViewFieldID;
    }

    /**
     * Delete the Ticket View Field record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTicketViewFieldID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Retrieve the fields based on ticket view id
     *
     * @author Varun Shoor
     * @param int $_ticketViewID The Ticket View ID
     * @return array Ticket View Fields Container
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnTicketView($_ticketViewID) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_ticketViewID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketViewFieldsContainer = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketviewfields WHERE ticketviewid = '" .
                $_ticketViewID . "' ORDER BY ticketviewfieldid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketViewFieldsContainer[$_SWIFT->Database->Record['ticketviewfieldid']] = $_SWIFT->Database->Record;
        }

        return $_ticketViewFieldsContainer;
    }

    /**
     * Delete a list of Ticket View Fields
     *
     * @author Varun Shoor
     * @param array $_ticketViewFieldIDList The Ticket View Field ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ticketViewFieldIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketViewFieldIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ticketviewfields WHERE ticketviewfieldid IN (" . BuildIN($_ticketViewFieldIDList) .
                ")");

        return true;
    }

    /**
     * Delete the fields based on a list of ticket view ids
     *
     * @author Varun Shoor
     * @param array $_ticketViewIDList The Ticket View ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnTicketView($_ticketViewIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ticketViewIDList)) {
            return false;
        }

        $_ticketViewFieldIDList = array();
        $_SWIFT->Database->Query("SELECT ticketviewfieldid FROM " . TABLE_PREFIX . "ticketviewfields WHERE ticketviewid IN (" . BuildIN($_ticketViewIDList) .
                ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketViewFieldIDList[] = $_SWIFT->Database->Record['ticketviewfieldid'];
        }

        if (!count($_ticketViewFieldIDList)) {
            return false;
        }

        self::DeleteList($_ticketViewFieldIDList);

        return true;
    }

    /**
     * Delete the fields based on field type and field type ID
     *
     * @author Simaranjit Singh
     * @param string $_fieldType The field type
     * @param array|int $_fieldTypeID The Field Type ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
     public static function DeleteOnCustomFieldTypeAndID($_fieldType, $_fieldTypeID) {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidFieldType($_fieldType)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ticketViewFieldIDList = array();

        $_SWIFT->Database->Query("SELECT ticketviewfieldid FROM " . TABLE_PREFIX . "ticketviewfields
            WHERE fieldtype = '" . (int) ($_fieldType) . "' AND fieldtypeid IN (" . BuildIN($_fieldTypeID) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ticketViewFieldIDList[] = $_SWIFT->Database->Record['ticketviewfieldid'];
        }

        if (!count($_ticketViewFieldIDList)) {
            return false;
        }

        self::DeleteList($_ticketViewFieldIDList);

        SWIFT_TicketView::RebuildCache();

        return true;

     }

    /**
     * Retrieve the field container array
     *
     * @author Varun Shoor
     * @return array The Field Container Array
     */
    public static function GetFieldContainer() {
        $_SWIFT = SWIFT::GetInstance();

        $_fieldPointer = array();
        $_fieldPointer[self::FIELD_TICKETID] = array();
        $_fieldPointer[self::FIELD_TICKETID]['name'] = 'tickets.ticketid';
        $_fieldPointer[self::FIELD_TICKETID]['title'] = $_SWIFT->Language->Get('f_ticketid');
        $_fieldPointer[self::FIELD_TICKETID]['width'] = '120';
        $_fieldPointer[self::FIELD_TICKETID]['align'] = 'left';

        $_fieldPointer[self::FIELD_SUBJECT] = array();
        $_fieldPointer[self::FIELD_SUBJECT]['name'] = 'tickets.subject';
        $_fieldPointer[self::FIELD_SUBJECT]['title'] = $_SWIFT->Language->Get('f_subject');
        $_fieldPointer[self::FIELD_SUBJECT]['width'] = '';
        $_fieldPointer[self::FIELD_SUBJECT]['align'] = 'left';

        $_fieldPointer[self::FIELD_QUEUE] = array();
        $_fieldPointer[self::FIELD_QUEUE]['name'] = 'tickets.emailqueueid';
        $_fieldPointer[self::FIELD_QUEUE]['title'] = $_SWIFT->Language->Get('f_queue');
        $_fieldPointer[self::FIELD_QUEUE]['width'] = '180';
        $_fieldPointer[self::FIELD_QUEUE]['align'] = 'left';

        $_fieldPointer[self::FIELD_DEPARTMENT] = array();
        $_fieldPointer[self::FIELD_DEPARTMENT]['name'] = 'tickets.departmenttitle';
        $_fieldPointer[self::FIELD_DEPARTMENT]['title'] = $_SWIFT->Language->Get('f_department');
        $_fieldPointer[self::FIELD_DEPARTMENT]['width'] = '120';
        $_fieldPointer[self::FIELD_DEPARTMENT]['align'] = 'left';

        $_fieldPointer[self::FIELD_TICKETSTATUS] = array();
        $_fieldPointer[self::FIELD_TICKETSTATUS]['name'] = 'ticketstatus.displayorder';
        $_fieldPointer[self::FIELD_TICKETSTATUS]['title'] = $_SWIFT->Language->Get('f_ticketstatus');
        $_fieldPointer[self::FIELD_TICKETSTATUS]['width'] = '100';
        $_fieldPointer[self::FIELD_TICKETSTATUS]['align'] = 'left';

        $_fieldPointer[self::FIELD_DUEDATE] = array();
        $_fieldPointer[self::FIELD_DUEDATE]['name'] = 'tickets.duetime';
        $_fieldPointer[self::FIELD_DUEDATE]['title'] = $_SWIFT->Language->Get('f_duedate');
        $_fieldPointer[self::FIELD_DUEDATE]['width'] = '80';
        $_fieldPointer[self::FIELD_DUEDATE]['align'] = 'center';

        $_fieldPointer[self::FIELD_RESOLUTIONDUEDATE] = array();
        $_fieldPointer[self::FIELD_RESOLUTIONDUEDATE]['name'] = 'tickets.resolutionduedateline';
        $_fieldPointer[self::FIELD_RESOLUTIONDUEDATE]['title'] = $_SWIFT->Language->Get('f_resolutiondue');
        $_fieldPointer[self::FIELD_RESOLUTIONDUEDATE]['width'] = '100';
        $_fieldPointer[self::FIELD_RESOLUTIONDUEDATE]['align'] = 'center';

        $_fieldPointer[self::FIELD_LASTACTIVITY] = array();
        $_fieldPointer[self::FIELD_LASTACTIVITY]['name'] = 'tickets.lastactivity';
        $_fieldPointer[self::FIELD_LASTACTIVITY]['title'] = $_SWIFT->Language->Get('f_lastactivity');
        $_fieldPointer[self::FIELD_LASTACTIVITY]['width'] = '100';
        $_fieldPointer[self::FIELD_LASTACTIVITY]['align'] = 'center';

        $_fieldPointer[self::FIELD_DATE] = array();
        $_fieldPointer[self::FIELD_DATE]['name'] = 'tickets.dateline';
        $_fieldPointer[self::FIELD_DATE]['title'] = $_SWIFT->Language->Get('f_date');
        $_fieldPointer[self::FIELD_DATE]['width'] = '160';
        $_fieldPointer[self::FIELD_DATE]['align'] = 'center';

        $_fieldPointer[self::FIELD_OWNER] = array();
        $_fieldPointer[self::FIELD_OWNER]['name'] = 'tickets.ownerstaffname';
        $_fieldPointer[self::FIELD_OWNER]['title'] = $_SWIFT->Language->Get('f_owner');
        $_fieldPointer[self::FIELD_OWNER]['width'] = '130';
        $_fieldPointer[self::FIELD_OWNER]['align'] = 'left';

        $_fieldPointer[self::FIELD_PRIORITY] = array();
        $_fieldPointer[self::FIELD_PRIORITY]['name'] = 'ticketpriorities.displayorder';
        $_fieldPointer[self::FIELD_PRIORITY]['title'] = $_SWIFT->Language->Get('f_priority');
        $_fieldPointer[self::FIELD_PRIORITY]['width'] = '90';
        $_fieldPointer[self::FIELD_PRIORITY]['align'] = 'center';

        $_fieldPointer[self::FIELD_TICKETTYPE] = array();
        $_fieldPointer[self::FIELD_TICKETTYPE]['name'] = 'tickets.tickettypetitle';
        $_fieldPointer[self::FIELD_TICKETTYPE]['title'] = $_SWIFT->Language->Get('f_type');
        $_fieldPointer[self::FIELD_TICKETTYPE]['width'] = '90';
        $_fieldPointer[self::FIELD_TICKETTYPE]['align'] = 'left';

        $_fieldPointer[self::FIELD_TICKETTYPEICON] = array();
        $_fieldPointer[self::FIELD_TICKETTYPEICON]['name'] = 'tickets.tickettypetitleicon';
        $_fieldPointer[self::FIELD_TICKETTYPEICON]['title'] = $_SWIFT->Language->Get('f_typeicon');
        $_fieldPointer[self::FIELD_TICKETTYPEICON]['gridtitle'] = '&nbsp;';
        $_fieldPointer[self::FIELD_TICKETTYPEICON]['width'] = '16';
        $_fieldPointer[self::FIELD_TICKETTYPEICON]['align'] = 'center';
        $_fieldPointer[self::FIELD_TICKETTYPEICON]['type'] = 'custom';

        $_fieldPointer[self::FIELD_LASTREPLIER] = array();
        $_fieldPointer[self::FIELD_LASTREPLIER]['name'] = 'tickets.lastreplier';
        $_fieldPointer[self::FIELD_LASTREPLIER]['title'] = $_SWIFT->Language->Get('f_lastreplier');
        $_fieldPointer[self::FIELD_LASTREPLIER]['width'] = '130';
        $_fieldPointer[self::FIELD_LASTREPLIER]['align'] = 'left';

        $_fieldPointer[self::FIELD_FULLNAME] = array();
        $_fieldPointer[self::FIELD_FULLNAME]['name'] = 'tickets.fullname';
        $_fieldPointer[self::FIELD_FULLNAME]['title'] = $_SWIFT->Language->Get('f_fullname');
        $_fieldPointer[self::FIELD_FULLNAME]['width'] = '140';
        $_fieldPointer[self::FIELD_FULLNAME]['align'] = 'left';

        $_fieldPointer[self::FIELD_TIMEWORKED] = array();
        $_fieldPointer[self::FIELD_TIMEWORKED]['name'] = 'tickets.timeworked';
        $_fieldPointer[self::FIELD_TIMEWORKED]['title'] = $_SWIFT->Language->Get('f_timeworked');
        $_fieldPointer[self::FIELD_TIMEWORKED]['width'] = '120';
        $_fieldPointer[self::FIELD_TIMEWORKED]['align'] = 'center';

        $_fieldPointer[self::FIELD_EMAIL] = array();
        $_fieldPointer[self::FIELD_EMAIL]['name'] = 'tickets.email';
        $_fieldPointer[self::FIELD_EMAIL]['title'] = $_SWIFT->Language->Get('f_email');
        $_fieldPointer[self::FIELD_EMAIL]['width'] = '180';
        $_fieldPointer[self::FIELD_EMAIL]['align'] = 'left';

        $_fieldPointer[self::FIELD_REPLIES] = array();
        $_fieldPointer[self::FIELD_REPLIES]['name'] = 'tickets.totalreplies';
        $_fieldPointer[self::FIELD_REPLIES]['title'] = $_SWIFT->Language->Get('f_totalreplies');
        $_fieldPointer[self::FIELD_REPLIES]['width'] = '50';
        $_fieldPointer[self::FIELD_REPLIES]['align'] = 'center';

        $_fieldPointer[self::FIELD_FLAG] = array();
        $_fieldPointer[self::FIELD_FLAG]['name'] = 'tickets.flagtype';
        $_fieldPointer[self::FIELD_FLAG]['title'] = $_SWIFT->Language->Get('f_flagtype');
        $_fieldPointer[self::FIELD_FLAG]['gridtitle'] = '&nbsp';
        $_fieldPointer[self::FIELD_FLAG]['width'] = '18';
        $_fieldPointer[self::FIELD_FLAG]['align'] = 'center';
        $_fieldPointer[self::FIELD_FLAG]['space'] = true;

        $_fieldPointer[self::FIELD_LASTSTAFFREPLY] = array();
        $_fieldPointer[self::FIELD_LASTSTAFFREPLY]['name'] = 'tickets.laststaffreplytime';
        $_fieldPointer[self::FIELD_LASTSTAFFREPLY]['title'] = $_SWIFT->Language->Get('f_laststaffreply');
        $_fieldPointer[self::FIELD_LASTSTAFFREPLY]['width'] = '80';
        $_fieldPointer[self::FIELD_LASTSTAFFREPLY]['align'] = 'center';

        $_fieldPointer[self::FIELD_LASTUSERREPLY] = array();
        $_fieldPointer[self::FIELD_LASTUSERREPLY]['name'] = 'tickets.lastuserreplytime';
        $_fieldPointer[self::FIELD_LASTUSERREPLY]['title'] = $_SWIFT->Language->Get('f_lastuserreply');
        $_fieldPointer[self::FIELD_LASTUSERREPLY]['width'] = '80';
        $_fieldPointer[self::FIELD_LASTUSERREPLY]['align'] = 'center';

        $_fieldPointer[self::FIELD_TEMPLATEGROUP] = array();
        $_fieldPointer[self::FIELD_TEMPLATEGROUP]['name'] = 'tickets.tgroupid';
        $_fieldPointer[self::FIELD_TEMPLATEGROUP]['title'] = $_SWIFT->Language->Get('f_tgroup');
        $_fieldPointer[self::FIELD_TEMPLATEGROUP]['width'] = '100';
        $_fieldPointer[self::FIELD_TEMPLATEGROUP]['align'] = 'center';

        $_fieldPointer[self::FIELD_SLAPLAN] = array();
        $_fieldPointer[self::FIELD_SLAPLAN]['name'] = 'tickets.slaplanid';
        $_fieldPointer[self::FIELD_SLAPLAN]['title'] = $_SWIFT->Language->Get('f_slaplan');
        $_fieldPointer[self::FIELD_SLAPLAN]['width'] = '120';
        $_fieldPointer[self::FIELD_SLAPLAN]['align'] = 'center';

        $_fieldPointer[self::FIELD_USERGROUP] = array();
        $_fieldPointer[self::FIELD_USERGROUP]['name'] = 'users.usergroupid';
        $_fieldPointer[self::FIELD_USERGROUP]['title'] = $_SWIFT->Language->Get('f_usergroup');
        $_fieldPointer[self::FIELD_USERGROUP]['width'] = '120';
        $_fieldPointer[self::FIELD_USERGROUP]['align'] = 'center';

        $_fieldPointer[self::FIELD_USERORGANIZATION] = array();
        $_fieldPointer[self::FIELD_USERORGANIZATION]['name'] = 'userorganizations.organizationname';
        $_fieldPointer[self::FIELD_USERORGANIZATION]['title'] = $_SWIFT->Language->Get('f_userorganization');
        $_fieldPointer[self::FIELD_USERORGANIZATION]['width'] = '120';
        $_fieldPointer[self::FIELD_USERORGANIZATION]['align'] = 'center';

        $_fieldPointer[self::FIELD_ESCALATIONRULE] = array();
        $_fieldPointer[self::FIELD_ESCALATIONRULE]['name'] = 'tickets.escalationruleid';
        $_fieldPointer[self::FIELD_ESCALATIONRULE]['title'] = $_SWIFT->Language->Get('f_escalationrule');
        $_fieldPointer[self::FIELD_ESCALATIONRULE]['width'] = '120';
        $_fieldPointer[self::FIELD_ESCALATIONRULE]['align'] = 'center';

        $_fieldPointer[self::FIELD_ESCALATEDTIME] = array();
        $_fieldPointer[self::FIELD_ESCALATEDTIME]['name'] = 'tickets.escalatedtime';
        $_fieldPointer[self::FIELD_ESCALATEDTIME]['title'] = $_SWIFT->Language->Get('f_escalatedtime');
        $_fieldPointer[self::FIELD_ESCALATEDTIME]['width'] = '100';
        $_fieldPointer[self::FIELD_ESCALATEDTIME]['align'] = 'center';

        $_customFieldIDCache = $_SWIFT->Cache->Get('customfieldidcache');
        $_customFieldMapCache = $_SWIFT->Cache->Get('customfieldmapcache');

        $_ticketCustomFieldIDList = $_userCustomFieldIDList = $_userOrganizationCustomFieldIDList = array();
        if (isset($_customFieldIDCache['ticketcustomfieldidlist'])) {
            $_ticketCustomFieldIDList = $_customFieldIDCache['ticketcustomfieldidlist'];
        }

        if (isset($_customFieldIDCache['usercustomfieldidlist'])) {
            $_userCustomFieldIDList = $_customFieldIDCache['usercustomfieldidlist'];
        }

        if (isset($_customFieldIDCache['userorganizationcustomfieldidlist'])) {
            $_userOrganizationCustomFieldIDList = $_customFieldIDCache['userorganizationcustomfieldidlist'];
        }

        $_baseCustomFieldIDList = array_merge($_ticketCustomFieldIDList, $_userCustomFieldIDList, $_userOrganizationCustomFieldIDList);

        foreach ($_baseCustomFieldIDList as $_customFieldID) {
            if (!isset($_customFieldMapCache[$_customFieldID])) {
                continue;
            }

            $_fieldName = 'c_' . $_customFieldID;
            $_fieldPointer[$_fieldName] = array();
            $_fieldPointer[$_fieldName]['name'] = 'custom' . $_customFieldID;
            $_fieldPointer[$_fieldName]['title'] = $_customFieldMapCache[$_customFieldID]['title'];
            $_fieldPointer[$_fieldName]['width'] = mb_strlen($_customFieldMapCache[$_customFieldID]['title'])*10;
            $_fieldPointer[$_fieldName]['align'] = 'center';
            $_fieldPointer[$_fieldName]['type'] = 'custom';
        }


        return $_fieldPointer;
    }
}
?>
