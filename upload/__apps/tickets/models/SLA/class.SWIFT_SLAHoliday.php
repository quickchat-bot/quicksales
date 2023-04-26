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

namespace Tickets\Models\SLA;

use SWIFT;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The SLA Holiday Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_SLAHoliday extends SWIFT_Model
{
    const TABLE_NAME        =    'slaholidays';
    const PRIMARY_KEY        =    'slaholidayid';

    const TABLE_STRUCTURE    =    "slaholidayid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                holidayday I2 DEFAULT '0' NOTNULL,
                                holidaymonth I2 DEFAULT '0' NOTNULL,
                                holidaydate C(200) DEFAULT '' NOTNULL,
                                flagicon C(255) DEFAULT '' NOTNULL,
                                iscustom I2 DEFAULT '0' NOTNULL";

    const INDEX_1            =    'holidayday, holidaymonth';
    const INDEX_2            =    'holidaydate, iscustom';


    protected $_dataStore = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_slaHolidayID The SLA Holiday ID
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function __construct($_slaHolidayID)
    {
        parent::__construct();

        if (!$this->LoadData($_slaHolidayID))
        {
            throw new SWIFT_SLA_Exception(SWIFT_CREATEFAILED);
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
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'slaholidays', $this->GetUpdatePool(), 'UPDATE', "slaholidayid = '" . (int) ($this->GetSLAHolidayID()) . "'");

        $this->ClearUpdatePool();

        return true;
    }

    /**
     * Retrieves the SLA Holiday ID
     *
     * @author Varun Shoor
     * @return mixed "slaholidayid" on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function GetSLAHolidayID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore['slaholidayid'];
    }

    /**
     * Load the Data
     *
     * @author Varun Shoor
     * @param int $_slaHolidayID The SLA Holiday ID
     * @return bool "true" on Success, "false" otherwise
     */
    protected function LoadData($_slaHolidayID)
    {
        $_dataStore = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "slaholidays WHERE slaholidayid = '" . $_slaHolidayID . "'");
        if (isset($_dataStore['slaholidayid']) && !empty($_dataStore['slaholidayid']))
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
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_dataStore;
    }

    /**
     * Retrieves a Property Value from Data Store
     *
     * @author Varun Shoor
     * @param string $_key The Key Identifier
     * @return mixed Property Data on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset($this->_dataStore[$_key]))
        {
            return false;
        }

        return $this->_dataStore[$_key];
    }

    /**
     * Link a List of SLA Plans with the SLA Holiday
     *
     * @author Varun Shoor
     * @param array $_slaPlanIDList Link SLA Plan ID Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function LinkSLAPlanIDList($_slaPlanIDList)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!_is_array($_slaPlanIDList)) {
            return false;
        }

        foreach ($_slaPlanIDList as $_key => $_val)
        {
            $this->Database->AutoExecute(TABLE_PREFIX . 'slaholidaylinks', array('slaplanid' => (int) ($_val), 'slaholidayid' => (int) ($this->GetSLAHolidayID())), 'INSERT');
        }

        return true;
    }

    /**
     * Link a List of SLA Holidays with a SLA Plan
     *
     * @author Varun Shoor
     * @param int $_slaPlanID The SLA Plan ID
     * @param array $_slaHolidayIDList Link SLA Holiday ID Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function LinkSLAHolidayIDList($_slaPlanID, $_slaHolidayIDList)
    {
        if (!_is_array($_slaHolidayIDList) || empty($_slaPlanID))
        {
            throw new SWIFT_SLA_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT = SWIFT::GetInstance();

        foreach ($_slaHolidayIDList as $_key => $_val)
        {
            if (empty($_val))
            {
                continue;
            }

            $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'slaholidaylinks', array('slaplanid' => $_slaPlanID, 'slaholidayid' => (int) ($_val)), 'INSERT');
        }

        return true;
    }

    /**
     * Clears all the links with the SLA Plans
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function ClearSLAPlanLinks()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "slaholidaylinks WHERE slaholidayid = '". (int) ($this->GetSLAHolidayID()) ."'");

        return true;
    }

    /**
     * Create a new SLA Holiday Record
     *
     * @author Varun Shoor
     * @param string $_title SLA Holiday Title
     * @param bool $_isCustom Whether this is linked to custom SLA Plans
     * @param int $_holidayDay The Holiday Day
     * @param int $_holidayMonth The Holiday Month
     * @param string $_flagIcon The Flag Icon
     * @param array $_slaPlanIDList The SLA Plan ID List
     * @return mixed "_SWIFT_SLAHolidayObject" (OBJECT) on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided or If Creation Fails
     */
    public static function Create($_title, $_isCustom, $_holidayDay, $_holidayMonth, $_flagIcon, $_slaPlanIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title) || empty($_holidayDay) || empty($_holidayMonth))
        {
            throw new SWIFT_SLA_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'slaholidays', array('holidaydate' =>  ($_holidayDay) . '-' . $_holidayMonth, 'title' => $_title, 'iscustom' => (int) ($_isCustom), 'flagicon' => ReturnNone($_flagIcon), 'holidayday' =>  ($_holidayDay), 'holidaymonth' =>  ($_holidayMonth)), 'INSERT');
        $_slaHolidayID = $_SWIFT->Database->Insert_ID();
        if (!$_slaHolidayID)
        {
            throw new SWIFT_SLA_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_SLAHolidayObject = new SWIFT_SLAHoliday($_slaHolidayID);
        if (!$_SWIFT_SLAHolidayObject instanceof SWIFT_SLAHoliday || !$_SWIFT_SLAHolidayObject->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CREATEFAILED);
        }

        if (_is_array($_slaPlanIDList) && $_isCustom == true)
        {
            $_SWIFT_SLAHolidayObject->LinkSLAPlanIDList($_slaPlanIDList);
        }

        self::RebuildCache();

        return $_SWIFT_SLAHolidayObject;
    }

    /**
     * Update the SLA Holiday Record
     *
     * @author Varun Shoor
     * @param string $_title SLA Holiday Title
     * @param bool $_isCustom Whether this is linked to custom SLA Plans
     * @param int $_holidayDay The Holiday Day
     * @param int $_holidayMonth The Holiday Month
     * @param string $_flagIcon The Flag Icon
     * @param array $_slaPlanIDList The SLA Plan ID List
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Update($_title, $_isCustom, $_holidayDay, $_holidayMonth, $_flagIcon, $_slaPlanIDList)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (empty($_title) || empty($_holidayDay) || empty($_holidayMonth)) {
            throw new SWIFT_SLA_Exception(SWIFT_INVALIDDATA);
        }

        $this->UpdatePool('holidaydate',  ($_holidayDay) . '-' . $_holidayMonth);
        $this->UpdatePool('title', $_title);
        $this->UpdatePool('iscustom', (int) ($_isCustom));
        $this->UpdatePool('flagicon', $_flagIcon);
        $this->UpdatePool('holidayday',  ($_holidayDay));
        $this->UpdatePool('holidaymonth',  ($_holidayMonth));

        $this->ProcessUpdatePool();

        $this->ClearSLAPlanLinks();

        if (_is_array($_slaPlanIDList) && $_isCustom == true)
        {
            $this->LinkSLAPlanIDList($_slaPlanIDList);
        }

        self::RebuildCache();

        return true;
    }

    /**
     * Retrieve the SLA Plan ID List associated with this SLA Holiday
     *
     * @author Varun Shoor
     * @return array "_slaPlanIDList" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSLAPlanIDList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_slaPlanIDList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaholidaylinks WHERE slaholidayid = '" . (int) ($this->GetSLAHolidayID()) . "'");
        while ($this->Database->NextRecord())
        {
            $_slaPlanIDList[] = $this->Database->Record['slaplanid'];
        }

        return $_slaPlanIDList;
    }

    /**
     * Delete the SLA Holiday record
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_SLA_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_SLA_Exception(SWIFT_CLASSNOTLOADED);
        }

        self::DeleteList(array($this->GetSLAHolidayID()));

        $this->SetIsClassLoaded(false);

        return true;
    }

    /**
     * Return the processed flag icon for this Holiday
     *
     * @author Varun Shoor
     * @param string $_flagIcon The Flag Icon Record
     * @return string The Processed Flag Icon Path
     */
    public static function GetFlagIcon($_flagIcon)
    {
        if (!empty($_flagIcon))
        {
            return str_replace('{$themepath}', SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_THEMESDIRECTORY . '/' . SWIFT_THEMEGLOBALDIRECTORY . '/flags/', $_flagIcon);
        }

        return SWIFT::Get('themepath') . 'images/icon_calendar.svg';
    }

    /**
     * Delete a list of SLA Holidays
     *
     * @author Varun Shoor
     * @param array $_slaHolidayIDList The SLA Holiday ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_slaHolidayIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_slaHolidayIDList))
        {
            return false;
        }

        $_finalSLAHolidayIDList = array();
        $_index = 1;

        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM ". TABLE_PREFIX ."slaholidays WHERE slaholidayid IN (". BuildIN($_slaHolidayIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_finalText .= $_index . '. ' . '<img src="' . self::GetFlagIcon($_SWIFT->Database->Record['flagicon']) . '" align="absmiddle" border="0" /> '. htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';
            $_finalSLAHolidayIDList[] = $_SWIFT->Database->Record['slaholidayid'];

            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelslaholidays'), count($_finalSLAHolidayIDList)), $_SWIFT->Language->Get('msgdelslaholidays') . '<br />' . $_finalText);

        if (!count($_finalSLAHolidayIDList))
        {
            return false;
        }

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "slaholidays WHERE slaholidayid IN (". BuildIN($_finalSLAHolidayIDList) .")");
        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "slaholidaylinks WHERE slaholidayid IN (". BuildIN($_finalSLAHolidayIDList) .")");

        self::RebuildCache();

        return true;
    }

    /**
     * Delete the SLA Holidays based on SLA Plan ID's
     *
     * @author Varun Shoor
     * @param string $_slaPlanIDList The SLA Plan ID List
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteOnSLAPlanIDList($_slaPlanIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_slaPlanIDList))
        {
            return false;
        }

        $_slaHolidayLinkIDList = array();
        $_SWIFT->Database->Query("SELECT slaholidaylinkid FROM ". TABLE_PREFIX ."slaholidaylinks WHERE slaplanid IN (". BuildIN($_slaPlanIDList) .")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_slaHolidayLinkIDList[] = $_SWIFT->Database->Record['slaholidaylinkid'];
        }

        if (count($_slaHolidayLinkIDList))
        {
            $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "slaholidaylinks WHERE slaholidaylinkid IN (" . BuildIN($_slaHolidayLinkIDList) . ")");
        }

        return true;
    }

    /**
     * Rebuild the SLA Holiday Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = $_slaHolidayIDList = array();

        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaholidays ORDER BY slaholidayid ASC", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_index++;

            if ($_SWIFT->Database->Record3['iscustom'] == '1') {
                $_slaHolidayIDList[] = (int) ($_SWIFT->Database->Record3['slaholidayid']);
            }

            $_cache['map'][$_SWIFT->Database->Record3['holidaydate']][$_SWIFT->Database->Record3['slaholidayid']] = $_SWIFT->Database->Record3;
            $_cache['map'][$_SWIFT->Database->Record3['holidaydate']][$_SWIFT->Database->Record3['slaholidayid']]['index'] = $_index;
            $_cache['map'][$_SWIFT->Database->Record3['holidaydate']][$_SWIFT->Database->Record3['slaholidayid']]['links'] = array();

            $_cache['list'][$_SWIFT->Database->Record3['slaholidayid']] = $_SWIFT->Database->Record3;
            $_cache['list'][$_SWIFT->Database->Record3['slaholidayid']]['index'] = $_index;
            $_cache['list'][$_SWIFT->Database->Record3['slaholidayid']]['links'] = array();
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaholidaylinks WHERE slaholidayid IN (". BuildIN($_slaHolidayIDList) .")", 3);
        while ($_SWIFT->Database->NextRecord(3))
        {
            $_holidayDate = $_cache['list'][$_SWIFT->Database->Record3['slaholidayid']]['holidaydate'];

            $_cache['list'][$_SWIFT->Database->Record3['slaholidayid']]['links'][] = $_SWIFT->Database->Record3['slaplanid'];
            $_cache['map'][$_holidayDate][$_SWIFT->Database->Record3['slaholidayid']]['links'][] = $_SWIFT->Database->Record3['slaplanid'];
        }

        $_SWIFT->Cache->Update('slaholidaycache', $_cache);

        return true;
    }

    /**
     * Check whether the given day is a holiday
     *
     * @author Varun Shoor
     * @param int $_dateLine The Dateline
     * @param SWIFT_SLA $_SWIFT_SLAPlanObject (OPTIONAL) The SWIFT_SLA Object Pointer to check for any linked holidays
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsHoliday($_dateLine, SWIFT_SLA $_SWIFT_SLAPlanObject = null) {
        $_SWIFT = SWIFT::GetInstance();

        $_slaHolidayCache = $_SWIFT->Cache->Get('slaholidaycache');

        /**
         * BUG FIX: Nidhi Gupta <nidhi.gupta@kayako.com>
         *
         * SWIFT-4636: Holidays are not considered while calculating the reply due time
         *
         * Comments: gmdate to date as gmdate is making date change one day prior or later.
         */
        $_holidayDate = date('j-n', $_dateLine);

        // First find out global holidays for a given date
        if (isset($_slaHolidayCache['map'][$_holidayDate]) && _is_array($_slaHolidayCache['map'][$_holidayDate])) {
            $_slaHolidayContainer = $_slaHolidayCache['map'][$_holidayDate];

            foreach ($_slaHolidayCache['map'][$_holidayDate] as $_slaHolidayID => $_slaHolidayContainer) {
                // If its not a custom but a global holiday
                if ($_slaHolidayContainer['iscustom'] == '0') {
                    return true;

                // If its not a custom holiday but its linked to our plan then return true;
                } else if ($_slaHolidayContainer['iscustom'] == '1' && $_SWIFT_SLAPlanObject instanceof SWIFT_SLA &&
                        $_SWIFT_SLAPlanObject->GetIsClassLoaded() &&
                        in_array($_SWIFT_SLAPlanObject->GetSLAPlanID(), $_slaHolidayContainer['links'])) {
                    return true;
                }
            }
        }

        return false;
    }
}
?>
