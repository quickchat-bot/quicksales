<?php
/**
 * =======================================
 * ###################################
 * SWIFT Framework
 *
 * @package    SWIFT
 * @author    Kayako Singapore Pte. Ltd.
 * @copyright    Copyright (c) 2001-Kayako Singapore Pte. Ltd.h Ltd.
 * @license    http://www.kayako.com/license
 * @link        http://www.kayako.com
 * @filesource
 * ###################################
 * =======================================
 */

namespace Base\Models\Rating;

use Base\Models\Rating\SWIFT_Rating;
use SWIFT;
use SWIFT_Data;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Model;
use Tickets\Library\Ticket\SWIFT_Ticket_Exception;


/**
 * The Ticket Rating Result Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_RatingResult extends SWIFT_Model
{
    const TABLE_NAME = 'ratingresults';
    const TABLE_RENAME = 'benchmarkresults';

    const PRIMARY_KEY = 'ratingresultid';

    const TABLE_STRUCTURE = "ratingresultid I PRIMARY AUTO NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                typeid I DEFAULT '0' NOTNULL,
                                ratingid I DEFAULT '0' NOTNULL,
                                ratingresult F DEFAULT '0' NOTNULL,
                                creatorid I DEFAULT '0' NOTNULL,
                                creatortype I2 DEFAULT '0' NOTNULL,
                                isedited I2 DEFAULT '0' NOTNULL,
                                editorid I DEFAULT '0' NOTNULL,
                                editortype I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'ratingid';
    const INDEX_2 = 'typeid, ratingid';

    const COLUMN_RENAME_BENCHMARKRESULTID = 'ratingresultid';
    const COLUMN_RENAME_BENCHMARKID = 'ratingid';
    const COLUMN_RENAME_BENCHMARKRESULT = 'ratingresult';

    protected $_dataStore = array();

    // Core Constants
    const CREATOR_STAFF = 1;
    const CREATOR_USER = 2;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct();

        if (!$_SWIFT_DataObject instanceof SWIFT_Data || !$_SWIFT_DataObject->GetIsClassLoaded() || !$this->LoadData($_SWIFT_DataObject)) {
            throw new SWIFT_Exception('Failed to load Rating Object');
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!_is_array($this->GetUpdatePool())) {
            return false;
        } else if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'ratingresults', $this->GetUpdatePool(), 'UPDATE', "ratingresultid = '" . (int)($this->GetRatingResultID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Rating Result ID
     *
     * @author Varun Shoor
     * @return mixed "ratingresultid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetRatingResultID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['ratingresultid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Ticket_Exception If Invalid Data is Provided
     */
    protected function LoadData($_SWIFT_DataObject)
    {
        $_SWIFT = SWIFT::GetInstance();

        // Is it a ID?
        if ($_SWIFT_DataObject instanceof SWIFT_DataID && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $_dataStore = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ratingresults WHERE ratingresultid = '" . (int)($_SWIFT_DataObject->GetDataID()) . "'");
            if (isset($_dataStore['ratingresultid']) && !empty($_dataStore['ratingresultid'])) {
                $this->_dataStore = $_dataStore;

                return true;
            }

            // Is it a Store?
        } else if ($_SWIFT_DataObject instanceof SWIFT_DataStore && $_SWIFT_DataObject->GetIsClassLoaded()) {
            $this->_dataStore = $_SWIFT_DataObject->GetDataStore();

            if (!isset($this->_dataStore['ratingresultid']) || empty($this->_dataStore['ratingresultid'])) {
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
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid creator
     *
     * @author Varun Shoor
     * @param mixed $_creatorType The Creator Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidCreator($_creatorType)
    {
        if ($_creatorType == self::CREATOR_STAFF || $_creatorType == self::CREATOR_USER) {
            return true;
        }

        return false;
    }

    /**
     * Create a rating result or update if it already exists
     *
     * @author Varun Shoor
     * @param SWIFT_Rating $_SWIFT_RatingObject The SWIFT_Rating Object Pointer
     * @param int $_typeID The Type ID of the Result Link (Ex: Ticket ID, Chat Object ID etc.)
     * @param float $_ratingResult The Rating Result Value
     * @param mixed $_creatorType The Creator Type
     * @param int $_creatorID The Creator ID
     * @return mixed "SWIFT_RatingResult" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CreateOrUpdateIfExists(SWIFT_Rating $_SWIFT_RatingObject, $_typeID, $_ratingResult, $_creatorType, $_creatorID)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_RatingObject instanceof SWIFT_Rating || !$_SWIFT_RatingObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_ratingIDList = array($_SWIFT_RatingObject->GetRatingID());

        // Attempt to retrieve existing rating results
        $_ratingResultContainer = SWIFT_RatingResult::Retrieve($_ratingIDList, array($_typeID));

        // Is the editing disabled and we are trying to reset it?
        if ($_SWIFT_RatingObject->GetProperty('iseditable') == '0' && isset($_ratingResultContainer[$_SWIFT_RatingObject->GetRatingID()][$_typeID])) {
            return false;

            // If the rating is client only editable and a staff tries to edit/set it, bail out!
        } else if ($_SWIFT_RatingObject->GetProperty('isclientonly') == '1' && $_creatorType == self::CREATOR_STAFF) {
            return false;

        }

        $_ratingResultContainer = $_SWIFT->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "ratingresults WHERE typeid = '" . $_typeID . "' AND ratingid = '" . (int)($_SWIFT_RatingObject->GetRatingID()) . "'");
        if (!isset($_ratingResultContainer['ratingresultid'])) {
            return new SWIFT_RatingResult(new SWIFT_DataID(self::Create($_SWIFT_RatingObject->GetRatingID(), $_typeID, $_ratingResult, $_creatorType, $_creatorID)));
        } else {
            $_SWIFT_RatingResultObject = new SWIFT_RatingResult(new SWIFT_DataStore($_ratingResultContainer));
            if (!$_SWIFT_RatingResultObject instanceof SWIFT_RatingResult || !$_SWIFT_RatingResultObject->GetIsClassLoaded()) {
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            $_SWIFT_RatingResultObject->Update($_ratingResult, $_creatorType, $_creatorID);

            return $_SWIFT_RatingResultObject;
        }

        return false;
    }

    /**
     * Create a new Rating Result
     *
     * @author Varun Shoor
     * @param int $_ratingID The Rating ID
     * @param int $_typeID The Type ID of the Result Link (Ex: Ticket ID, Chat Object ID etc.)
     * @param float $_ratingResult The Rating Result Value
     * @param mixed $_creatorType The Creator Type
     * @param int $_creatorID The Creator ID
     * @return mixed "_ratingResultID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_ratingID, $_typeID, $_ratingResult, $_creatorType, $_creatorID)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_ratingResult = floatval($_ratingResult);
        $_creatorType = (int)($_creatorType);

        if (empty($_ratingID) || empty($_typeID) || !self::IsValidCreator($_creatorType) || empty($_creatorID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'ratingresults', array('ratingid' => $_ratingID,
            'typeid' => $_typeID, 'ratingresult' => floatval($_ratingResult),
            'creatortype' => $_creatorType, 'creatorid' => $_creatorID, 'dateline' => DATENOW), 'INSERT');
        $_ratingResultID = $_SWIFT->Database->Insert_ID();

        if (!$_ratingResultID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        return $_ratingResultID;
    }

    /**
     * Update the Rating Result Record
     *
     * @author Varun Shoor
     * @param float $_ratingResult The Rating Result Value
     * @param mixed $_editorType The Editor Type
     * @param int $_editorID The Editor ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_ratingResult, $_editorType, $_editorID)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ratingResult = floatval($_ratingResult);

        if (!self::IsValidCreator($_editorType) || empty($_editorID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('ratingresult', floatval($_ratingResult));
        $this->UpdatePool('isedited', '1');
        $this->UpdatePool('dateline', DATENOW);
        $this->UpdatePool('editortype', (int)($_editorType));
        $this->UpdatePool('editorid', $_editorID);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Delete the Rating Result record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetRatingResultID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Rating Result ID's
     *
     * @author Varun Shoor
     * @param array $_ratingResultIDList The ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_ratingResultIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ratingResultIDList)) {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "ratingresults WHERE ratingresultid IN (" . BuildIN($_ratingResultIDList) . ")");

        return true;
    }

    /**
     * Delete on the basis of Rating ID List
     *
     * @author Varun Shoor
     * @param array $_ratingIDList The Rating ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnRating($_ratingIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ratingIDList)) {
            return false;
        }

        $_ratingResultIDList = array();
        $_SWIFT->Database->Query("SELECT ratingresultid FROM " . TABLE_PREFIX . "ratingresults WHERE ratingid IN (" . BuildIN($_ratingIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ratingResultIDList[] = $_SWIFT->Database->Record['ratingresultid'];
        }

        if (!count($_ratingResultIDList)) {
            return false;
        }

        self::DeleteList($_ratingResultIDList);

        return true;
    }

    /**
     * Retrieve a list of rating results
     *
     * @author Varun Shoor
     * @param array $_ratingIDList The Rating ID List
     * @param array $_typeIDList The Type ID
     * @return array
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function Retrieve($_ratingIDList, $_typeIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_ratingIDList) || !_is_array($_typeIDList)) {
            return array();
        }

        $_ratingResultContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ratingresults WHERE typeid IN (" . BuildIN($_typeIDList) . ")" . " AND ratingid IN (" . BuildIN($_ratingIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ratingResultContainer[$_SWIFT->Database->Record['ratingid']][$_SWIFT->Database->Record['typeid']] = $_SWIFT->Database->Record;
        }

        return $_ratingResultContainer;
    }

    /**
     * Update secondary user IDs with merged primary user ID
     *
     * @author Pankaj Garg
     *
     * @param int $_primaryUserID
     * @param array $_secondaryUserIDList
     *
     * @return bool
     */
    public static function UpdateUserIDOnMerge($_primaryUserID, $_secondaryUserIDList)
    {
        $_SWIFT = SWIFT::GetInstance();
        if (!_is_array($_secondaryUserIDList)) {
            return false;
        }
        // Update creator
        $_ratingResultContainer = array();
        $_SWIFT->Database->Query("SELECT ratingresultid FROM " . TABLE_PREFIX . self::TABLE_NAME . "
                                   WHERE creatortype = " . self::CREATOR_USER . "
                                     AND creatorid IN ( " . BuildIN($_secondaryUserIDList, true) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ratingResultContainer[$_SWIFT->Database->Record['ratingresultid']] = $_SWIFT->Database->Record;
        }
        foreach ($_ratingResultContainer as $_ratingResult) {
            $_RatingResult = new SWIFT_RatingResult(new SWIFT_DataID($_ratingResult['ratingresultid']));
            $_RatingResult->UpdateCreator($_primaryUserID, self::CREATOR_USER);
        }
        // Update editor
        $_ratingResultContainer = array();
        $_SWIFT->Database->Query("SELECT ratingresultid FROM " . TABLE_PREFIX . self::TABLE_NAME . "
                                   WHERE editortype = " . self::CREATOR_USER . "
                                     AND editorid IN ( " . BuildIN($_secondaryUserIDList, true) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_ratingResultContainer[$_SWIFT->Database->Record['ratingresultid']] = $_SWIFT->Database->Record;
        }
        foreach ($_ratingResultContainer as $_ratingResult) {
            $_RatingResult = new SWIFT_RatingResult(new SWIFT_DataID($_ratingResult['ratingresultid']));
            $_RatingResult->UpdateEditor($_primaryUserID, self::CREATOR_USER);
        }
        return true;
    }

    /**
     * Updates the Creator with which the rating result is linked
     *
     * @author Abhishek Mittal
     *
     * @param int $_creatorID
     * @param int $_creatorType
     *
     * @return SWIFT_RatingResult
     * @throws SWIFT_Exception If the Class is not Loaded OR If Invalid Data is Provided
     */
    public function UpdateCreator($_creatorID, $_creatorType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }
        if (!self::IsValidCreator($_creatorType) || empty($_creatorID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        $this->UpdatePool('creatorid', $_creatorID);
        $this->UpdatePool('creatortype', $_creatorType);
        $this->ProcessUpdatePool();
        return $this;
    }

    /**
     * Updates the Editor with which the rating result is linked
     *
     * @author Abhishek Mittal
     *
     * @param int $_editorID
     * @param int $_editorType
     *
     * @return SWIFT_RatingResult
     * @throws SWIFT_Exception If the Class is not Loaded OR If Invalid Data is Provided
     */
    public function UpdateEditor($_editorID, $_editorType)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }
        if (!self::IsValidCreator($_editorType) || empty($_editorID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
        $this->UpdatePool('editorid', $_editorID);
        $this->UpdatePool('editortype', $_editorType);
        $this->ProcessUpdatePool();
        return $this;
    }
}

?>
