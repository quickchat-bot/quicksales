<?php
//=======================================
//###################################
// Kayako Singapore Pte. Ltd. - SWIFT Framework
//
// Source Copyright 2001-2009 Kayako Singapore Pte. Ltd.
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//                          www.kayako.com
//###################################
//=======================================
namespace LiveChat\Models\Visitor;

use LiveChat\Models\Visitor\SWIFT_Visitor_Exception;
use SWIFT;
use SWIFT_Model;

/**
 * The Visitor Data Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_VisitorData extends SWIFT_Model
{
    const TABLE_NAME = 'visitordata';
    const PRIMARY_KEY = 'visitordataid';

    const TABLE_STRUCTURE = "visitordataid I PRIMARY AUTO NOTNULL,
                                visitorsessionid C(255) DEFAULT '' NOTNULL,
                                visitorruleid I DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                datakey C(255) DEFAULT '' NOTNULL,
                                datavalue C(255) DEFAULT '' NOTNULL,
                                datatype I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'visitorsessionid, datatype';
    const INDEX_2 = 'visitorruleid';
    const INDEX_3 = 'visitorsessionid, visitorruleid';


    protected $_dataStore = array();

    const DATATYPE_VARIABLE = 1;
    const DATATYPE_SKILL = 2;
    const DATATYPE_ALERT = 3;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function __construct($_visitorDataID)
    {
        parent::__construct();

        if (!$this->LoadData($_visitorDataID)) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);
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
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'visitordata', $this->GetUpdatePool(), 'UPDATE', "visitordataid = '" . (int)($this->GetVisitorDataID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the Visitor Data ID
     *
     * @author Varun Shoor
     * @return mixed "visitordataid" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function GetVisitorDataID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore['visitordataid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_visitorDataID The Visitor Data ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_visitorDataID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "visitordata WHERE visitordataid = '" . $_visitorDataID . "'");
        if (isset($_dataStore['visitordataid']) && !empty($_dataStore['visitordataid'])) {
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
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);

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
     * @throws SWIFT_Visitor_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Visitor_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (!isset($this->_dataStore[$_key])) {
            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Insert a new visitor data record
     *
     * @author Varun Shoor
     * @param string $_visitorSessionID The Visitor Session ID
     * @param int $_visitorRuleID The Visitor Rule ID
     * @param int $_dataType The Visitor Data Type
     * @param string $_dataKey The Data Key
     * @param string $_dataValue The Data Value
     * @return mixed "visitorDataID" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If Invalid Data Provided or If Creation Fails
     */
    public static function Insert($_visitorSessionID, $_visitorRuleID, $_dataType, $_dataKey, $_dataValue)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_visitorRuleID = $_visitorRuleID;

        if (!self::IsValidDataType($_dataType) || empty($_visitorSessionID)) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'visitordata', array('visitorsessionid' => $_visitorSessionID, 'datatype' => $_dataType, 'datakey' => ReturnNone($_dataKey), 'datavalue' => ReturnNone($_dataValue), 'dateline' => DATENOW, 'visitorruleid' => $_visitorRuleID), 'INSERT');
        $_visitorDataID = $_SWIFT->Database->Insert_ID();
        if (!$_visitorDataID) {
            throw new SWIFT_Visitor_Exception(SWIFT_CREATEFAILED);

            return false;
        }

        return $_visitorDataID;
    }

    /**
     * Checks to see if its a valid data type
     *
     * @author Varun Shoor
     * @param int $_dataType The Visitor Data Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidDataType($_dataType)
    {
        if ($_dataType == self::DATATYPE_VARIABLE || $_dataType == self::DATATYPE_SKILL || $_dataType == self::DATATYPE_ALERT) {
            return true;
        }

        return false;
    }

    /**
     * Deletes the combination of visitor data for session id and visitor rule id
     *
     * @author Varun Shoor
     * @param string $_visitorSessionID The Visitor Session ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If Invalid Data Provided
     */
    public static function DeleteList($_visitorSessionID)
    {
        $_visitorRuleIDList = func_get_arg(1);

        $_SWIFT = SWIFT::GetInstance();

        if (empty($_visitorSessionID) || $_visitorRuleIDList === false) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitordata
            WHERE visitorsessionid = '" . $_SWIFT->Database->Escape($_visitorSessionID) . "' AND visitorruleid IN (" . BuildIN($_visitorRuleIDList) . ")");

        return true;
    }

    /**
     * Retrieve the visitor data on the visitor session id
     *
     * @author Varun Shoor
     * @param string $_visitorSessionID The Visitor Session ID
     * @param mixed $_dataType (OPTIONAL) The Data Type to filter results on
     * @return mixed "_visitorDataContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If Invalid Data is Provided
     */
    public static function RetrieveOnVisitorSession($_visitorSessionID, $_dataType = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_visitorSessionID) || (!empty($_dataType) && !self::IsValidDataType($_dataType))) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_extendedSQL = '';
        if (!empty($_dataType)) {
            $_extendedSQL = " AND datatype = '" . (int)($_dataType) . "'";
        }

        $_visitorDataContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitordata WHERE visitorsessionid = '" . $_SWIFT->Database->Escape($_visitorSessionID) . "'" . $_extendedSQL);
        while ($_SWIFT->Database->NextRecord()) {
            $_visitorDataContainer[$_SWIFT->Database->Record['datakey']] = $_SWIFT->Database->Record['datavalue'];
        }

        if (!count($_visitorDataContainer)) {
            return false;
        }

        return $_visitorDataContainer;
    }

    /**
     * Deletes the combination of visitor data for session id and data key
     *
     * @author Parminder Singh
     * @param string $_visitorSessionID The Visitor Session ID
     * @param string $_dataKey The Data Key String
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Visitor_Exception If Invalid Data Provided
     */
    public static function DeleteOnDataKey($_visitorSessionID, $_dataKey)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_visitorSessionID)) {
            throw new SWIFT_Visitor_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitordata
            WHERE visitorsessionid = '" . $_SWIFT->Database->Escape($_visitorSessionID) . "' AND datakey = '" . $_SWIFT->Database->Escape($_dataKey) . "'");

        return true;
    }
}

