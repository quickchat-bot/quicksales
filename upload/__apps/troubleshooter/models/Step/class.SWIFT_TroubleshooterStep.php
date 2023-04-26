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

namespace Troubleshooter\Models\Step;

use SWIFT;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Attachment\SWIFT_AttachmentStoreFile;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Models\Staff\SWIFT_Staff;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;
use Troubleshooter\Models\Link\SWIFT_TroubleshooterLink;

/**
 * The Troubleshooter Step Model
 *
 * @author Varun Shoor
 */
class SWIFT_TroubleshooterStep extends SWIFT_Model {
    const TABLE_NAME        =    'troubleshootersteps';
    const PRIMARY_KEY        =    'troubleshooterstepid';

    const TABLE_STRUCTURE    =    "troubleshooterstepid I PRIMARY AUTO NOTNULL,
                                troubleshootercategoryid I DEFAULT '0' NOTNULL,
                                stepstatus I2 DEFAULT '0' NOTNULL,

                                staffid I DEFAULT '0' NOTNULL,
                                staffname C(255) DEFAULT '' NOTNULL,

                                subject C(255) DEFAULT '' NOTNULL,

                                edited I2 DEFAULT '0' NOTNULL,
                                editedstaffid I DEFAULT '0' NOTNULL,
                                editedstaffname C(255) DEFAULT '' NOTNULL,
                                editeddateline I DEFAULT '0' NOTNULL,

                                dateline I DEFAULT '0' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                views I DEFAULT '0' NOTNULL,
                                allowcomments I2 DEFAULT '0' NOTNULL,
                                hasattachments I2 DEFAULT '0' NOTNULL,

                                redirecttickets I2 DEFAULT '0' NOTNULL,
                                ticketsubject C(255) DEFAULT '' NOTNULL,
                                redirectdepartmentid I DEFAULT '0' NOTNULL,
                                tickettypeid I DEFAULT '0' NOTNULL,
                                priorityid I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'troubleshootercategoryid';


    protected $_dataStore = array();

    // Core Constants
    const STATUS_PUBLISHED = 1;
    const STATUS_DRAFT = 2;

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
            throw new SWIFT_Exception('Failed to load Troubleshooter Step Object');
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
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
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'troubleshootersteps', $this->GetUpdatePool(), 'UPDATE', "troubleshooterstepid = '" . (int) ($this->GetTroubleshooterStepID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Troubleshooter Step ID
     *
     * @author Varun Shoor
     * @return mixed "troubleshooterstepid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetTroubleshooterStepID() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['troubleshooterstepid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    protected function LoadData($_SWIFT_DataObject) {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT troubleshootersteps.*, troubleshooterdata.* FROM " . TABLE_PREFIX . "troubleshootersteps AS troubleshootersteps
                LEFT JOIN " . TABLE_PREFIX . "troubleshooterdata AS troubleshooterdata ON (troubleshootersteps.troubleshooterstepid = troubleshooterdata.troubleshooterstepid)
                WHERE troubleshootersteps.troubleshooterstepid = '" . (int) ($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['troubleshooterstepid']) && !empty($_dataStore['troubleshooterstepid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['troubleshooterstepid']) || empty($this->_dataStore['troubleshooterstepid'])) {
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
            throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid Troubleshooter Status
     *
     * @author Varun Shoor
     * @param mixed $_troubleshooterStatus The Troubleshooter Status
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidStatus($_troubleshooterStatus) {
        return $_troubleshooterStatus == self::STATUS_DRAFT || $_troubleshooterStatus == self::STATUS_PUBLISHED;
    }

    /**
     * Create a new Troubleshooter Step
     *
     * @author Varun Shoor
     * @param int $_categoryId
     * @param mixed $_stepStatus The Current Step Status
     * @param string $_subject The Step Subject
     * @param string $_contents The Step Contents
     * @param int $_displayOrder The Display Order
     * @param bool $_allowComments Whether to allow comments
     * @param bool $_redirectTickets (OPTIONAL) Whether to redirect to tickets
     * @param string $_ticketSubject (OPTIONAL) The Ticket Subject
     * @param bool|int $_redirectDepartmentID (OPTIONAL) The Redirect Department ID
     * @param bool|int $_ticketTypeID (OPTIONAL) The Ticket Type ID to be preselected
     * @param bool|int $_ticketPriorityID (OPTIONAL) The Ticket Priority ID to be preselected
     * @param array $_parentTroubleshooterStepIDList (OPTIONAL) The Parent Troubleshooter Step ID List
     * @param bool|SWIFT_Model $_SWIFT_StaffObject (OPTIONAL) The SWIFT_Staff Object of Creator
     * @return int The Troubleshooter Step ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_categoryId, $_stepStatus, $_subject, $_contents, $_displayOrder, $_allowComments,
            $_redirectTickets = false, $_ticketSubject = '', $_redirectDepartmentID = false, $_ticketTypeID = false, $_ticketPriorityID = false,
            $_parentTroubleshooterStepIDList = array(), $_SWIFT_StaffObject = false) {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_subject) ||
                !self::IsValidStatus($_stepStatus) || empty($_contents)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        try {
            $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID($_categoryId));
        } catch (SWIFT_Exception $_SWIFT_Exception) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA . ': invalid category');
        }

        $_staffID = 0;
        $_staffName = '';

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
            $_staffName = $_SWIFT_StaffObject->GetProperty('fullname');
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'troubleshootersteps', array('troubleshootercategoryid' => $_categoryId,
            'stepstatus' => (int) ($_stepStatus), 'staffid' => (int) ($_staffID), 'staffname' => $_staffName, 'subject' => $_subject, 'edited' => '0', 'editedstaffid' => '0',
            'editedstaffname' => '', 'dateline' => DATENOW, 'displayorder' =>  ($_displayOrder), 'views' => '0', 'allowcomments' => (int) ($_allowComments), 'hasattachments' => '0',
            'redirecttickets' => (int) ($_redirectTickets), 'ticketsubject' => $_ticketSubject, 'redirectdepartmentid' =>  ($_redirectDepartmentID),
            'tickettypeid' => ($_ticketTypeID), 'priorityid' => $_ticketPriorityID
        ), 'INSERT');
        $_troubleshooterStepID = $_SWIFT->Database->Insert_ID();

        if (!$_troubleshooterStepID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'troubleshooterdata', array('troubleshooterstepid' => (int) ($_troubleshooterStepID), 'contents' => $_contents), 'INSERT');

        foreach ($_parentTroubleshooterStepIDList as $_parentTroubleshooterStepID) {
            SWIFT_TroubleshooterLink::Create($_categoryId, $_parentTroubleshooterStepID, $_troubleshooterStepID);
        }

        return $_troubleshooterStepID;
    }

    /**
     * Update Troubleshooter Step Record
     *
     * @author Varun Shoor
     * @param string $_subject The Step Subject
     * @param string $_contents The Step Contents
     * @param int $_displayOrder The Display Order
     * @param bool $_allowComments Whether to allow comments
     * @param bool $_redirectTickets (OPTIONAL) Whether to redirect to tickets
     * @param string $_ticketSubject (OPTIONAL) The Ticket Subject
     * @param int|bool $_redirectDepartmentID (OPTIONAL) The Redirect Department ID
     * @param int|bool $_ticketTypeID (OPTIONAL) The Ticket Type ID to be preselected
     * @param int|bool $_ticketPriorityID (OPTIONAL) The Ticket Priority ID to be preselected
     * @param array $_parentTroubleshooterStepIDList (OPTIONAL) The Parent Troubleshooter Step ID List
     * @param bool|SWIFT_Model $_SWIFT_StaffObject (OPTIONAL) The SWIFT_Staff Object of Creator
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_subject, $_contents, $_displayOrder, $_allowComments,
            $_redirectTickets = false, $_ticketSubject = '', $_redirectDepartmentID = false, $_ticketTypeID = false, $_ticketPriorityID = false,
            $_parentTroubleshooterStepIDList = array(), $_SWIFT_StaffObject = false) {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_staffID = 0;
        $_staffName = '';

        if ($_SWIFT_StaffObject instanceof SWIFT_Staff && $_SWIFT_StaffObject->GetIsClassLoaded()) {
            $_staffID = $_SWIFT_StaffObject->GetStaffID();
            $_staffName = $_SWIFT_StaffObject->GetProperty('fullname');
        }

        $this->UpdatePool('edited', '1');
        $this->UpdatePool('editedstaffid', $_staffID);
        $this->UpdatePool('editedstaffname', $_staffName);
        $this->UpdatePool('editeddateline', DATENOW);

        $this->UpdatePool('subject', $_subject);
        $this->UpdatePool('displayorder', $_displayOrder);
        $this->UpdatePool('allowcomments', (int) ($_allowComments));
        $this->UpdatePool('redirecttickets', (int) ($_redirectTickets));
        $this->UpdatePool('redirectdepartmentid', (int) ($_redirectDepartmentID));
        $this->UpdatePool('ticketsubject', $_ticketSubject);
        $this->UpdatePool('tickettypeid', $_ticketTypeID);
        $this->UpdatePool('priorityid', $_ticketPriorityID);

        $this->ProcessUpdatePool();

        $this->Database->AutoExecute(TABLE_PREFIX . 'troubleshooterdata', array('contents' => $_contents), 'UPDATE', "troubleshooterstepid = '" . (int) ($this->GetTroubleshooterStepID()) . "'");

        SWIFT_TroubleshooterLink::DeleteOnChildTroubleshooterStep(array($this->GetTroubleshooterStepID()))
                ;
        foreach ($_parentTroubleshooterStepIDList as $_parentTroubleshooterStepID) {
            SWIFT_TroubleshooterLink::Create($this->GetProperty('troubleshootercategoryid'), $_parentTroubleshooterStepID, $this->GetTroubleshooterStepID());
        }

        return true;
    }

    /**
     * Update the Step Status
     *
     * @author Varun Shoor
     * @param mixed $_troubleshooterStatus The Troubleshooter Step Status
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function UpdateStatus($_troubleshooterStatus)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!self::IsValidStatus($_troubleshooterStatus)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('stepstatus', (int) ($_troubleshooterStatus));
        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Troubleshooter Step record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetTroubleshooterStepID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Troubleshooter Steps
     *
     * @author Varun Shoor
     * @param array $_troubleshooterStepIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteList($_troubleshooterStepIDList) {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_troubleshooterStepIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "troubleshootersteps WHERE troubleshooterstepid IN (" . BuildIN($_troubleshooterStepIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "troubleshooterdata WHERE troubleshooterstepid IN (" . BuildIN($_troubleshooterStepIDList) . ")");

        SWIFT_TroubleshooterLink::DeleteOnTroubleshooterStep($_troubleshooterStepIDList);

        return true;
    }

    /**
     * Retrieve the Last Possible Display Order for a Troubleshooter Step
     *
     * @author Varun Shoor
     * @param bool $_troubleshooterCategoryID (OPTIONAL) The Troubleshooter Category to filter results on
     * @return int The Last Possible Display Order
     */
    public static function GetLastDisplayOrder($_troubleshooterCategoryID = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_sqlExtended = '';
        if (!empty($_troubleshooterCategoryID))
        {
            $_sqlExtended = " WHERE troubleshootercategoryid = '" . (int) ($_troubleshooterCategoryID) . "'";
        }

        $_displayOrderContainer = $_SWIFT->Database->QueryFetch("SELECT MAX(displayorder) AS displayorder FROM " . TABLE_PREFIX . "troubleshootersteps" . $_sqlExtended);
        if (!isset($_displayOrderContainer['displayorder']))
        {
            return 1;
        }

        $_displayOrder = (int) ($_displayOrderContainer['displayorder'] + 1);

        return $_displayOrder;
    }

    /**
     * Retrieve the troubleshooter steps in one go..
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID
     * @return array An array containig the steps and the parent <> child relationship map
     * @throws SWIFT_Exception
     */
    public static function RetrieveSteps($_troubleshooterCategoryID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_troubleshooterStepContainer = $_troubleshooterParentMap = array();

        $_troubleshooterParentMap[0] = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshootersteps WHERE troubleshootercategoryid = '" .  ($_troubleshooterCategoryID) . "' ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_troubleshooterStepContainer[$_SWIFT->Database->Record['troubleshooterstepid']] = $_SWIFT->Database->Record;

            if (!isset($_troubleshooterParentMap[$_SWIFT->Database->Record['troubleshooterstepid']]))
            {
                $_troubleshooterParentMap[$_SWIFT->Database->Record['troubleshooterstepid']] = array();
            }
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshooterlinks WHERE troubleshootercategoryid = '" .  ($_troubleshooterCategoryID) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            if (!isset($_troubleshooterStepContainer[$_SWIFT->Database->Record['childtroubleshooterstepid']]) ||
                            !isset($_troubleshooterParentMap[$_SWIFT->Database->Record['parenttroubleshooterstepid']]))
            {
                continue;
            }

            $_troubleshooterParentMap[$_SWIFT->Database->Record['parenttroubleshooterstepid']][] = $_troubleshooterStepContainer[$_SWIFT->Database->Record['childtroubleshooterstepid']];
        }

        return array('_troubleshooterStepContainer' => $_troubleshooterStepContainer, '_troubleshooterParentMap' => $_troubleshooterParentMap);
    }

    /**
     * Processes the POST attachment field (trattachments) and adds the attachments to the ticket
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function ProcessPostAttachments() {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * ---------------------------------------------
         * REPLICA EXISTS IN KBARTICLE CLASS
         * ---------------------------------------------
         */

        $_finalFieldName = 'trattachments';

        $_attachmentContainer = SWIFT_Attachment::Retrieve(SWIFT_Attachment::LINKTYPE_TROUBLESHOOTERSTEP, $this->GetTroubleshooterStepID());
        $_existingAttachmentIDList = array();
        if (isset($_POST['_existingAttachmentIDList']) && _is_array($_POST['_existingAttachmentIDList']))
        {
            $_existingAttachmentIDList = $_POST['_existingAttachmentIDList'];
        }

        $_deleteAttachmentIDList = $_attachmentFileList = $_attachmentFileMap = array();
        if (_is_array($_attachmentContainer))
        {
            foreach ($_attachmentContainer as $_attachment)
            {
                $_attachmentFileList[] = $_attachment['filename'];
                $_attachmentFileMap[$_attachment['filename']] = $_attachment['attachmentid'];

                if (!in_array($_attachment['attachmentid'], $_existingAttachmentIDList))
                {
                    $_deleteAttachmentIDList[] = $_attachment['attachmentid'];
                }
            }

            SWIFT_Attachment::DeleteList($_deleteAttachmentIDList);
        }

        if (!isset($_FILES[$_finalFieldName]) || !_is_array($_FILES[$_finalFieldName]) || !_is_array($_FILES[$_finalFieldName]['name'])) {
            return false;
        }

        // Create the attachments
        $_attachmentCount = 0;
        foreach ($_FILES[$_finalFieldName]['name'] as $_fileIndex => $_fileName) {
            if (empty($_fileName) || empty($_FILES[$_finalFieldName]['type'][$_fileIndex]) || empty($_FILES[$_finalFieldName]['size'][$_fileIndex]) ||
                    !is_uploaded_file($_FILES[$_finalFieldName]['tmp_name'][$_fileIndex]))
            {
                continue;
            }

            // @codeCoverageIgnoreStart
            // This code will not be executed from a test
            // If a file with same filename already exists then delete it
            if (in_array($_fileName, $_attachmentFileList) && isset($_attachmentFileMap[$_fileName]))
            {
                SWIFT_Attachment::DeleteList(array($_attachmentFileMap[$_fileName]));
            }

            $_SWIFT_AttachmentStoreObject = new SWIFT_AttachmentStoreFile($_FILES[$_finalFieldName]['tmp_name'][$_fileIndex],
                    $_FILES[$_finalFieldName]['type'][$_fileIndex], $_fileName);

            $_SWIFT_AttachmentObject = SWIFT_Attachment::Create(SWIFT_Attachment::LINKTYPE_TROUBLESHOOTERSTEP, $this->GetTroubleshooterStepID(), $_SWIFT_AttachmentStoreObject);

            $_attachmentCount++;
        }

        if ($_attachmentCount > 0) {
            $this->UpdatePool('hasattachments', '1');
            $this->ProcessUpdatePool();
        }
        // @codeCoverageIgnoreEnd

        return true;
    }

    /**
     * Retrieve the troubleshooter sub steps in one go..
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID
     * @param int $_troubleshooterStepID
     * @return array An array containig the steps and the parent <> child relationship map
     * @throws SWIFT_Exception
     */
    public static function RetrieveSubSteps($_troubleshooterCategoryID, $_troubleshooterStepID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_troubleshooterStepContainer = $_troubleshooterStepIDList = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshooterlinks
            WHERE troubleshootercategoryid = '" .  ($_troubleshooterCategoryID) . "' AND parenttroubleshooterstepid = '" .  ($_troubleshooterStepID) . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_troubleshooterStepIDList[] = (int) ($_SWIFT->Database->Record['childtroubleshooterstepid']);
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "troubleshootersteps WHERE troubleshooterstepid IN (" . BuildIN($_troubleshooterStepIDList) . ") ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_troubleshooterStepContainer[$_SWIFT->Database->Record['troubleshooterstepid']] = $_SWIFT->Database->Record;
            /**
             * BUG FIX : Saloni Dhall <saloni.dhall@kayako.com>
             *
             * SWIFT-3987 : Security issue (medium)
             *
             * Comments : We can't sanitize data while inserting into database, UI components sanitization also does not work as the subject field contains text box, fields getting $_POST.
             *            So while rendering it, sanitizing the 'subject' field to prevent vulnerability. Other option may be sanitizing the data directly into template by using 'escape' function which is like <{$_troubleshooterStep[subject]|escape}> but avoiding that approach.
             */
            $_troubleshooterStepContainer[$_SWIFT->Database->Record['troubleshooterstepid']]['subject'] = $_SWIFT->Input->SanitizeForXSS($_SWIFT->Database->Record['subject']);
        }

        return $_troubleshooterStepContainer;
    }

    /**
     * Retrieve the Status label
     *
     * @author Varun Shoor
     * @param mixed $_stepStatus The Step Status
     * @return string The Step Status Label
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function GetStatusLabel($_stepStatus)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!static::IsValidStatus($_stepStatus))
        {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_stepStatus) {
            case self::STATUS_PUBLISHED:
                return $_SWIFT->Language->Get('published');

                break;

            case self::STATUS_DRAFT:
                return $_SWIFT->Language->Get('draft');

                break;

            default:
                break;
        }

        throw new SWIFT_Exception(SWIFT_INVALIDDATA);
    }
}
