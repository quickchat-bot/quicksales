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

namespace LiveChat\Models\Canned;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Canned Response Class
 *
 * @author Varun Shoor
 */
class SWIFT_CannedResponse extends SWIFT_Model
{
    const TABLE_NAME = 'cannedresponses';
    const PRIMARY_KEY = 'cannedresponseid';

    const TABLE_STRUCTURE = "cannedresponseid I PRIMARY AUTO NOTNULL,
                                cannedcategoryid I DEFAULT '0' NOTNULL,
                                staffid I DEFAULT '0' NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                urldata C(255) DEFAULT '' NOTNULL,
                                imagedata C(255) DEFAULT '' NOTNULL,
                                responsetype I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'cannedcategoryid';
    const INDEX_2 = 'staffid';


    protected $_dataStore = array();

    // Core Constants
    const TYPE_NONE = 1;
    const TYPE_CODE = 2;
    const TYPE_MESSAGE = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_cannedResponseID The Canned Response ID
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct($_cannedResponseID)
    {
        parent::__construct();

        if (!$this->LoadData($_cannedResponseID)) {
            throw new SWIFT_Exception('Failed to load Canned Response ID: ' . ($_cannedResponseID));
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
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'cannedresponses', $this->GetUpdatePool(), 'UPDATE', "cannedresponseid = '" . (int)($this->GetCannedResponseID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Canned Response ID
     *
     * @author Varun Shoor
     * @return mixed "cannedresponseid" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetCannedResponseID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['cannedresponseid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_cannedResponseID The Canned Response ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_cannedResponseID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT cannedresponses.*, cannedresponsedata.contents FROM " . TABLE_PREFIX . "cannedresponses AS cannedresponses LEFT JOIN " . TABLE_PREFIX . "cannedresponsedata AS cannedresponsedata ON (cannedresponses.cannedresponseid = cannedresponsedata.cannedresponseid) WHERE cannedresponses.cannedresponseid = '" . $_cannedResponseID . "'");
        if (isset($_dataStore['cannedresponseid']) && !empty($_dataStore['cannedresponseid'])) {
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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_dataStore[$_key])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Check to see if its a valid response type
     *
     * @author Varun Shoor
     * @param mixed $_responseType The Response Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_responseType)
    {
        if ($_responseType == self::TYPE_NONE || $_responseType == self::TYPE_CODE || $_responseType == self::TYPE_MESSAGE) {
            return true;
        }

        return false;
    }

    /**
     * Create a new Canned Response
     *
     * @author Varun Shoor
     * @param int $_cannedCategoryID The Canned Category ID
     * @param string $_title The Response Title
     * @param string $_urlData Any Push URL Data
     * @param string $_imageData Any Push Image Data
     * @param mixed $_responseType The Response Type
     * @param string $_responseContents The Response Contents
     * @param int $_staffID The Staff ID creating the response
     * @return mixed "_cannedResponseID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_cannedCategoryID, $_title, $_urlData, $_imageData, $_responseType, $_responseContents, $_staffID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cannedCategoryID = $_cannedCategoryID;

        if (!self::IsValidType($_responseType) || empty($_title)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'cannedresponses', array('cannedcategoryid' => $_cannedCategoryID, 'title' => $_title, 'urldata' => $_urlData, 'imagedata' => $_imageData, 'responsetype' => (int)($_responseType), 'staffid' => $_staffID), 'INSERT');
        $_cannedResponseID = $_SWIFT->Database->Insert_ID();

        if (!$_cannedResponseID) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'cannedresponsedata', array('cannedresponseid' => $_cannedResponseID, 'contents' => $_responseContents), 'INSERT');

        return $_cannedResponseID;
    }

    /**
     * Update the canned response record
     *
     * @author Varun Shoor
     * @param int $_cannedCategoryID The Canned Category ID
     * @param string $_title The Response Title
     * @param string $_urlData Any Push URL Data
     * @param string $_imageData Any Push Image Data
     * @param mixed $_responseType The Response Type
     * @param string $_responseContents The Response Contents
     * @param int $_staffID The Staff ID creating the response
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Update($_cannedCategoryID, $_title, $_urlData, $_imageData, $_responseType, $_responseContents, $_staffID = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_cannedCategoryID = $_cannedCategoryID;

        if (!self::IsValidType($_responseType) || empty($_title)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->UpdatePool('cannedcategoryid', $_cannedCategoryID);
        $this->UpdatePool('title', $_title);
        $this->UpdatePool('urldata', $_urlData);
        $this->UpdatePool('imagedata', $_imageData);
        $this->UpdatePool('responsetype', (int)($_responseType));
        $this->UpdatePool('staffid', $_staffID);

        $this->ProcessUpdatePool();

        $this->Database->AutoExecute(TABLE_PREFIX . 'cannedresponsedata', array('contents' => $_responseContents), 'UPDATE', "cannedresponseid = '" . (int)($this->GetCannedResponseID()) . "'");

        return true;
    }

    /**
     * Delete Canned Response record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        self::DeleteList(array($this->GetCannedResponseID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Delete a list of Canned Responses
     *
     * @author Varun Shoor
     * @param array $_cannedResponseIDList The Canned Response ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_cannedResponseIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_cannedResponseIDList)) {
            return false;
        }

        $_index = 1;
        $_finalText = '';

        $_finalCannedResponseIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "cannedresponses WHERE cannedresponseid IN (" . BuildIN($_cannedResponseIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalCannedResponseIDList[] = $_SWIFT->Database->Record['cannedresponseid'];

            $_finalText .= $_index . '. ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<BR />';

            $_index++;
        }

        if (!_is_array($_finalCannedResponseIDList)) {
            return false;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelcannedresp'), count($_finalCannedResponseIDList)), $_SWIFT->Language->Get('msgdelcannedresp') . '<BR />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "cannedresponses WHERE cannedresponseid IN (" . BuildIN($_finalCannedResponseIDList) . ")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "cannedresponsedata WHERE cannedresponseid IN (" . BuildIN($_finalCannedResponseIDList) . ")");

        return true;
    }

    /**
     * Delete canned responses based on given categories..
     *
     * @author Varun Shoor
     * @param array $_cannedCategoryIDList The Canned Category ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnCannedCategory($_cannedCategoryIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (_is_array($_cannedCategoryIDList)) {
            return false;
        }

        $_cannedResponseIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "cannedresponses WHERE cannedcategoryid IN (" . BuildIN($_cannedCategoryIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_cannedResponseIDList[] = $_SWIFT->Database->Record['cannedresponseid'];
        }

        if (!_is_array($_cannedResponseIDList)) {
            return false;
        }

        self::DeleteList($_cannedResponseIDList);

        return true;
    }

    /**
     * Retrieve all the canned resopnses
     *
     * @author Varun Shoor
     * @param bool $_loadCannedResponseData (OPTIONAL) Whether to load canned response data, true by default.
     * @return array The Response Container Array
     * @throws SWIFT_Exception
     */
    public static function RetrieveCannedResponses($_loadCannedResponseData = true)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cannedResponsesContainer = $_responseParentMap = array();

        if ($_loadCannedResponseData) {
            $_SWIFT->Database->Query("SELECT cannedresponses.*, cannedresponsedata.contents AS contents FROM " . TABLE_PREFIX . "cannedresponses AS cannedresponses LEFT JOIN " . TABLE_PREFIX . "cannedresponsedata AS cannedresponsedata ON (cannedresponses.cannedresponseid = cannedresponsedata.cannedresponseid) ORDER BY cannedresponses.title ASC");
        } else {
            $_SWIFT->Database->Query("SELECT cannedresponses.* FROM " . TABLE_PREFIX . "cannedresponses AS cannedresponses ORDER BY cannedresponses.title ASC");
        }
        while ($_SWIFT->Database->NextRecord()) {
            $_cannedResponsesContainer[$_SWIFT->Database->Record['cannedresponseid']] = $_SWIFT->Database->Record;

            if (!isset($_responseParentMap[$_SWIFT->Database->Record['cannedcategoryid']])) {
                $_responseParentMap[$_SWIFT->Database->Record['cannedcategoryid']] = array();
            }

            $_responseParentMap[$_SWIFT->Database->Record['cannedcategoryid']][] = $_SWIFT->Database->Record;
        }

        return array('_cannedResponsesContainer' => $_cannedResponsesContainer, '_responseParentMap' => $_responseParentMap);
    }
}
