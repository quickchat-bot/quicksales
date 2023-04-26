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

namespace Base\Models\Rating;

use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Model;
use Base\Library\Rating\SWIFT_Rating_Exception;
use Base\Models\Rating\SWIFT_RatingResult;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroupLink;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserGroupAssign;

/**
 * The Rating Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_Rating extends SWIFT_Model
{
    const TABLE_NAME = 'ratings';
    const TABLE_RENAME = 'benchmarks';
    const PRIMARY_KEY = 'ratingid';

    const TABLE_STRUCTURE = "ratingid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                departmentid I DEFAULT '0' NOTNULL,
                                displayorder I DEFAULT '0' NOTNULL,
                                ratingscale I2 DEFAULT '0' NOTNULL,
                                ratingvisibility C(100) DEFAULT 'private' NOTNULL,
                                ratingtype I2 DEFAULT '0' NOTNULL,
                                staffvisibilitycustom I2 DEFAULT '0' NOTNULL,
                                uservisibilitycustom I2 DEFAULT '0' NOTNULL,
                                iseditable I2 DEFAULT '0' NOTNULL,
                                isclientonly I2 DEFAULT '0' NOTNULL,
                                ratingtitle C(255) DEFAULT '' NOTNULL";

    const INDEX_1 = 'ratingtype, departmentid';
    const INDEX_2 = 'departmentid';

    const COLUMN_RENAME_BENCHMARKID = 'ratingid';
    const COLUMN_RENAME_BENCHMARKSCALE = 'ratingscale';
    const COLUMN_RENAME_BENCHMARKVISIBILITY = 'ratingvisibility';
    const COLUMN_RENAME_BENCHMARKTYPE = 'ratingtype';
    const COLUMN_RENAME_BENCHMARKTITLE = 'ratingtitle';

    protected $_dataStore = array();

    // Core Constants
    const TYPE_TICKET = 1;
    const TYPE_TICKETPOST = 2;
    const TYPE_CHATSURVEY = 3;
    const TYPE_KNOWLEDGEBASE = 4;
    const TYPE_TROUBLESHOOTER = 6;
    const TYPE_NEWS = 7;
    const TYPE_CHATHISTORY = 8;

    // Types Array
    const RATING_TYPES = [self::TYPE_TICKET, self::TYPE_TICKETPOST, self::TYPE_CHATSURVEY, self::TYPE_CHATHISTORY,
                          self::TYPE_KNOWLEDGEBASE, self::TYPE_TROUBLESHOOTER, self::TYPE_NEWS];

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_ratingID The Rating ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Rating_Exception If the Record could not be loaded
     */
    public function __construct($_ratingID)
    {
        parent::__construct();

        if (!$this->LoadData($_ratingID)) {
            throw new SWIFT_Rating_Exception('Failed to load Rating ID: ' . $_ratingID);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
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
     * @throws SWIFT_Rating_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rating_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ratings', $this->GetUpdatePool(), 'UPDATE', "ratingid = '" . (int)($this->GetRatingID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Rating ID
     *
     * @author Varun Shoor
     * @return mixed "ratingid" on Success, "false" otherwise
     * @throws SWIFT_Rating_Exception If the Class is not Loaded
     */
    public function GetRatingID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rating_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ratingid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_ratingID The Rating ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_ratingID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ratings WHERE ratingid = '" . $_ratingID . "'");
        if (isset($_dataStore['ratingid']) && !empty($_dataStore['ratingid'])) {
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
     * @throws SWIFT_Rating_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rating_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_Rating_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rating_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Rating_Exception(SWIFT_INVALIDDATA . ': ' . $_key);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid Rating Type
     *
     * @author Varun Shoor
     * @param mixed $_ratingType The Rating Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_ratingType)
    {
        if ($_ratingType == self::TYPE_TICKET || $_ratingType == self::TYPE_TICKETPOST ||
            $_ratingType == self::TYPE_CHATSURVEY || $_ratingType == self::TYPE_KNOWLEDGEBASE ||
            $_ratingType == self::TYPE_TROUBLESHOOTER || $_ratingType == self::TYPE_NEWS ||
            $_ratingType == self::TYPE_CHATHISTORY) {
            return true;
        }

        return false;
    }


    /**
     * Create a new Rating
     *
     * @author Varun Shoor
     * @param string $_ratingTitle The Rating Title
     * @param int $_displayOrder The Rating Display Order
     * @param mixed $_ratingType The Rating Type
     * @param bool $_departmentID The Department ID the Rating is Linked to
     * @param bool $_isEditable Whether the rating result is editable after its been created
     * @param bool $_isClientOnly Whether its client only
     * @param int $_ratingScale The Scale of Rating
     * @param int $_ratingVisibility The Public/Private Property of this Rating
     * @param bool $_staffVisibilityCustom Whether the rating should be visible to only select staff groups
     * @param bool $_userVisibilityCustom Whether the rating should be visible to only select user groups
     * @param array $_staffGroupIDList The Staff Groups the Rating should be Linked With
     * @param array $_userGroupIDList The User Groups the Rating should be Linked With
     * @return mixed "_ratingID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Rating_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_ratingTitle, $_displayOrder, $_ratingType, $_departmentID, $_isEditable, $_isClientOnly,
                                  $_ratingScale, $_ratingVisibility, $_staffVisibilityCustom, $_userVisibilityCustom,
                                  $_staffGroupIDList = array(), $_userGroupIDList = array())
    {
        $_SWIFT = SWIFT::GetInstance();

        $_displayOrder = $_displayOrder;

        if (empty($_ratingTitle) || empty($_displayOrder) || !self::IsValidType($_ratingType)) {
            throw new SWIFT_Rating_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ratings', array('ratingtitle' => $_ratingTitle,
            'displayorder' => $_displayOrder, 'ratingtype' => (int)($_ratingType), 'isclientonly' => (int)($_isClientOnly),
            'departmentid' => $_departmentID, 'iseditable' => (int)($_isEditable), 'ratingscale' => $_ratingScale,
            'ratingvisibility' => $_ratingVisibility, 'staffvisibilitycustom' => (int)($_staffVisibilityCustom),
            'uservisibilitycustom' => (int)($_userVisibilityCustom)), 'INSERT');
        $_ratingID = $_SWIFT->Database->Insert_ID();

        if (!$_ratingID) {
            throw new SWIFT_Rating_Exception(SWIFT_CREATEFAILED);
        }

        // Process Rating <> Staff Group Links
        if (_is_array($_staffGroupIDList) && $_staffVisibilityCustom == 1) {
            foreach ($_staffGroupIDList as $_key => $_val) {
                SWIFT_StaffGroupLink::Create($_val, SWIFT_StaffGroupLink::TYPE_RATING, $_ratingID, false);
            }
        }

        SWIFT_StaffGroupLink::RebuildCache();

        // Process Rating <> User Group Links
        if (_is_array($_userGroupIDList) && $_userVisibilityCustom == 1) {
            foreach ($_userGroupIDList as $_key => $_val) {
                SWIFT_UserGroupAssign::Insert($_ratingID, SWIFT_UserGroupAssign::TYPE_RATING, $_val, false);
            }
        }

        SWIFT_UserGroupAssign::RebuildCache();

        self::RebuildCache();

        return $_ratingID;
    }

    /**
     * Update the Rating Record
     *
     * @author Varun Shoor
     * @param string $_ratingTitle The Rating Title
     * @param int $_displayOrder The Rating Display Order
     * @param int $_departmentID The Department ID the Rating is Linked to
     * @param bool $_isEditable Whether the rating result is editable after its been created
     * @param bool $_isClientOnly Whether its client only
     * @param int $_ratingScale The Scale of Rating
     * @param int $_ratingVisibility The Public/Private Property of this Rating
     * @param bool $_staffVisibilityCustom Whether the rating should be visible to only select staff groups
     * @param bool $_userVisibilityCustom Whether the rating should be visible to only select user groups
     * @param array $_staffGroupIDList The Staff Groups the Rating should be Linked With
     * @param array $_userGroupIDList The User Groups the Rating should be Linked With
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws SWIFT_Rating_Exception If the Class is not Loaded or If Invalid Data is Provided
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     */
    public function Update($_ratingTitle, $_displayOrder, $_departmentID, $_isEditable, $_isClientOnly, $_ratingScale,
                           $_ratingVisibility, $_staffVisibilityCustom, $_userVisibilityCustom, $_staffGroupIDList = array(),
                           $_userGroupIDList = array())
    {
        $_displayOrder = $_displayOrder;

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rating_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_ratingTitle) || empty($_displayOrder)) {
            throw new SWIFT_Rating_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('ratingtitle', $_ratingTitle);
        $this->UpdatePool('displayorder', $_displayOrder);
        $this->UpdatePool('departmentid', $_departmentID);
        $this->UpdatePool('iseditable', (int)($_isEditable));
        $this->UpdatePool('isclientonly', (int)($_isClientOnly));
        $this->UpdatePool('ratingscale', $_ratingScale);
        $this->UpdatePool('ratingvisibility', $_ratingVisibility);
        $this->UpdatePool('staffvisibilitycustom', (int)($_staffVisibilityCustom));
        $this->UpdatePool('uservisibilitycustom', (int)($_userVisibilityCustom));

        $this->ProcessUpdatePool();

        // Process Rating <> Staff Group Links
        SWIFT_StaffGroupLink::DeleteOnLink(SWIFT_StaffGroupLink::TYPE_RATING, $this->GetRatingID());
        if (_is_array($_staffGroupIDList) && $_staffVisibilityCustom == 1) {
            foreach ($_staffGroupIDList as $_key => $_val) {
                SWIFT_StaffGroupLink::Create($_val, SWIFT_StaffGroupLink::TYPE_RATING, $this->GetRatingID(), false);
            }
        }

        SWIFT_StaffGroupLink::RebuildCache();

        // Process Rating <> User Group Links
        SWIFT_UserGroupAssign::DeleteList(array($this->GetRatingID()), SWIFT_UserGroupAssign::TYPE_RATING, false);
        if (_is_array($_userGroupIDList) && $_userVisibilityCustom == 1) {
            foreach ($_userGroupIDList as $_key => $_val) {
                SWIFT_UserGroupAssign::Insert($this->GetRatingID(), SWIFT_UserGroupAssign::TYPE_RATING, $_val, false);
            }
        }

        SWIFT_UserGroupAssign::RebuildCache();


        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve the Staff Group ID's linked with this Rating
     *
     * @author Varun Shoor
     * @return mixed "_staffGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedStaffGroupIDList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_StaffGroupLink::RetrieveList(SWIFT_StaffGroupLink::TYPE_RATING, $this->GetRatingID());
    }

    /**
     * Retrieve the User Group ID's linked with this Rating
     *
     * @author Varun Shoor
     * @return mixed "_userGroupIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetLinkedUserGroupIDList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return SWIFT_UserGroupAssign::RetrieveList(SWIFT_UserGroupAssign::TYPE_RATING, $this->GetRatingID());
    }

    /**
     * Delete the Rating record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Rating_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Rating_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetRatingID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Ratings
     *
     * @author Varun Shoor
     * @param array $_ratingIDList The Rating ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ratingIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ratingIDList)) {
            return false;
        }

        $_finalRatingIDList = array();

        $_index = 1;
        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ratings WHERE ratingid IN (" . BuildIN($_ratingIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['ratingtitle']) . ' (' . self::GetLabel($_SWIFT->Database->Record['ratingtype']) . ')<BR />';

            $_finalRatingIDList[] = $_SWIFT->Database->Record['ratingid'];

            $_index++;
        }

        if (!count($_finalRatingIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelrating'), count($_finalRatingIDList)), $_SWIFT->Language->Get('msgdelrating') . '<BR />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ratings WHERE ratingid IN (" . BuildIN($_finalRatingIDList) . ")");

        SWIFT_RatingResult::DeleteOnRating($_finalRatingIDList);

        SWIFT_StaffGroupLink::DeleteOnLinkList(SWIFT_StaffGroupLink::TYPE_RATING, $_finalRatingIDList);

        SWIFT_UserGroupAssign::DeleteList($_finalRatingIDList, SWIFT_UserGroupAssign::TYPE_RATING);

        self::RebuildCache();

        return true;
    }

    /**
     * Rebuild the Rating Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ratings ORDER BY displayorder ASC", 3);
        while ($_SWIFT->Database->NextRecord(3)) {
            $_index++;
            $_cache[$_SWIFT->Database->Record3['ratingid']] = $_SWIFT->Database->Record3;
            $_cache[$_SWIFT->Database->Record3['ratingid']]['index'] = $_index;
        }

        $_SWIFT->Cache->Update('ratingcache', $_cache);

        return true;
    }

    /**
     * Retrieve the Last Possible Display Order for a Rating
     *
     * @author Varun Shoor
     * @return int|string The Last Possible Display Order
     */
    public static function GetLastDisplayOrder()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_SWIFT->Cache->Load('ratingcache');

        $_ratingCache = $_SWIFT->Cache->Get('ratingcache');

        if (!$_ratingCache) {
            return '1';
        }

        // Get Last Insert ID
        $_lastInsertID = max(array_keys($_ratingCache));

        $_displayOrder = (int)($_ratingCache[$_lastInsertID]['displayorder'] + 1);

        return $_displayOrder;
    }

    /**
     * Retrieve the Rating Label
     *
     * @author Varun Shoor
     * @param mixed $_ratingType The Rating Type
     * @return mixed "Rating Label" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Rating_Exception If Invalid Data is Provided
     */
    public static function GetLabel($_ratingType)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!self::IsValidType($_ratingType)) {
            throw new SWIFT_Rating_Exception(SWIFT_INVALIDDATA);
        }

        switch ($_ratingType) {
            case self::TYPE_TICKET:
                return $_SWIFT->Language->Get('ratingticket');

                break;

            case self::TYPE_TICKETPOST:
                return $_SWIFT->Language->Get('ratingticketpost');

                break;

            case self::TYPE_CHATSURVEY:
                return $_SWIFT->Language->Get('ratingchatsurvey');

                break;

            case self::TYPE_CHATHISTORY:
                return $_SWIFT->Language->Get('ratingchathistory');

                break;

            case self::TYPE_KNOWLEDGEBASE:
                return $_SWIFT->Language->Get('ratingknowledgebase');

                break;

            case self::TYPE_TROUBLESHOOTER:
                return $_SWIFT->Language->Get('ratingtroubleshooter');

                break;

            case self::TYPE_NEWS:
                return $_SWIFT->Language->Get('ratingnews');

                break;

            default:
                break;
        }

        return false;
    }

    /**
     * Retrieve all ratings based on given criteria
     *
     * @author Varun Shoor
     * @param array $_ratingTypeList The Rating Type
     * @param SWIFT_Model $_SWIFT_CreatorObject The SWIFT_Creator Object
     * @param mixed $_visibilityType The Rating Visibility
     * @param int $_departmentID The Department ID
     * @param int $_userGroupID
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     */
    public static function Retrieve($_ratingTypeList, $_SWIFT_CreatorObject, $_visibilityType = false, $_departmentID = 0, $_userGroupID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        foreach ($_ratingTypeList as $_ratingType) {
            if (!self::IsValidType($_ratingType)) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }
        }

        $_ratingContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ratings WHERE ratingtype IN (" . BuildIN($_ratingTypeList) . ") ORDER BY displayorder ASC", 3);
        while ($_SWIFT->Database->NextRecord(3)) {
            // Check for staff visibility
            if ($_SWIFT->Database->Record3['staffvisibilitycustom'] == '1' && $_SWIFT_CreatorObject instanceof SWIFT_Staff) {
                $_staffGroupIDList = SWIFT_StaffGroupLink::RetrieveList(SWIFT_StaffGroupLink::TYPE_RATING, $_SWIFT->Database->Record3['ratingid']);
                if (!in_array($_SWIFT_CreatorObject->GetProperty('staffgroupid'), $_staffGroupIDList)) {
                    continue;
                }

                // Check for user visibility
            } else if ($_SWIFT->Database->Record3['uservisibilitycustom'] == '1' && ($_SWIFT_CreatorObject instanceof SWIFT_User || !empty($_userGroupID))) {
                $_userGroupIDList = SWIFT_UserGroupAssign::RetrieveList(SWIFT_UserGroupAssign::TYPE_RATING, $_SWIFT->Database->Record3['ratingid']);

                $_finalUserGroupID = $_userGroupID;
                if ($_SWIFT_CreatorObject instanceof SWIFT_User) {
                    $_finalUserGroupID = $_SWIFT_CreatorObject->GetProperty('usergroupid');
                }

                if (!in_array($_finalUserGroupID, $_userGroupIDList)) {
                    continue;
                }

            }

            // Check for visibility filteration
            if ($_visibilityType !== false && $_SWIFT->Database->Record3['ratingvisibility'] != $_visibilityType) {
                continue;

            }

            // Department filteration?
            if ($_departmentID !== false && !empty($_SWIFT->Database->Record3['departmentid']) && $_SWIFT->Database->Record3['departmentid'] != $_departmentID) {
                continue;

            }

            $_ratingContainer[$_SWIFT->Database->Record3['ratingid']] = $_SWIFT->Database->Record3;
        }

        return $_ratingContainer;
    }

    /**
     * Update the Display Order List
     *
     * @author Varun Shoor
     * @param array $_ratingIDSortList The Rating ID Sort List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function UpdateDisplayOrderList($_ratingIDSortList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ratingIDSortList)) {
            return false;
        }

        foreach ($_ratingIDSortList as $_ratingID => $_displayOrder) {
            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ratings', array('displayorder' => $_displayOrder), 'UPDATE', "ratingid = '" . $_ratingID . "'");
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve the available rating types
     *
     * @author Varun Shoor
     * @return array The Rating Types
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveAvailableTypes()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ratingTypeContainer = array();

        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            $_ratingTypeContainer[self::TYPE_TICKET] = $_SWIFT->Language->Get('ratingticket');
            $_ratingTypeContainer[self::TYPE_TICKETPOST] = $_SWIFT->Language->Get('ratingticketpost');
        }

        if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            $_ratingTypeContainer[self::TYPE_CHATSURVEY] = $_SWIFT->Language->Get('ratingchatsurvey');
            $_ratingTypeContainer[self::TYPE_CHATHISTORY] = $_SWIFT->Language->Get('ratingchathistory');
        }

        return $_ratingTypeContainer;
    }

    /**
     * Delete a list of ratings based on type
     *
     * @author Varun Shoor
     * @param array $_ratingTypeList The Rating Type List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnType($_ratingTypeList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ratingTypeList)) {
            return false;
        }

        $_ratingIDList = array();
        $_SWIFT->Database->Query("SELECT ratingid FROM " . TABLE_PREFIX . "ratings WHERE ratingtype IN (" . BuildIN($_ratingTypeList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ratingIDList[] = $_SWIFT->Database->Record['ratingid'];
        }

        if (!count($_ratingIDList)) {
            return false;
        }

        self::DeleteList($_ratingIDList);

        return true;
    }
}

?>
