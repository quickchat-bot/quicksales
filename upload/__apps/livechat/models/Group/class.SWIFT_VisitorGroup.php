<?php
//=======================================
//###################################
// QuickSupport Singapore Pte. Ltd. - SWIFT Framework
//
// Source Copyright 2001-2009 QuickSupport Singapore Pte. Ltd.
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//                          www.kayako.com
//###################################
//=======================================
namespace LiveChat\Models\Group;

use LiveChat\Models\Group\SWIFT_Group_Exception;
use SWIFT;
use SWIFT_Model;

/**
 * Live Chat Visitor Groups Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_VisitorGroup extends SWIFT_Model
{
    const TABLE_NAME = 'visitorgroups';
    const PRIMARY_KEY = 'visitorgroupid';

    const TABLE_STRUCTURE = "visitorgroupid I PRIMARY AUTO NOTNULL,
                                title C(255) DEFAULT '' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL,
                                color C(100) DEFAULT '' NOTNULL";

    const INDEX_1 = 'title'; // Unified Search


    private $_visitorGroup = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Group_Exception If the Class is not Loaded
     */
    public function __construct($_visitorGroupID)
    {
        parent::__construct();

        if (!$this->LoadData($_visitorGroupID)) {
            throw new SWIFT_Group_Exception(SWIFT_CLASSNOTLOADED);
        }
    }

    /**
     * Processes the Update Pool Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Group_Exception If the Class is not Loaded
     */
    public function ProcessUpdatePool()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        } else if (!_is_array($this->GetUpdatePool())) {
            return false;
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'visitorgroups', $this->GetUpdatePool(), 'UPDATE', "visitorgroupid = '" . ($this->GetVisitorGroupID()) . "'");

        self::RebuildVisitorGroupCache();

        return true;
    }

    /**
     * Loads the Data from this Visitor Group
     *
     * @author Varun Shoor
     * @param \SWIFT_Data|int $_visitorGroupID The Visitor Group ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Group_Exception If Invalid Data Provided
     */
    protected function LoadData($_visitorGroupID)
    {
        $_visitorGroup = $this->Database->QueryFetch("SELECT * FROM " . TABLE_PREFIX . "visitorgroups WHERE visitorgroupid = '" . $_visitorGroupID . "'");
        if (empty($_visitorGroup['visitorgroupid'])) {
            throw new SWIFT_Group_Exception(SWIFT_INVALIDDATA);
        }

        $this->_visitorGroup = $_visitorGroup;

        return true;
    }

    /**
     * Retrieves the Visitor Group ID
     *
     * @author Varun Shoor
     * @return int
     * @throws SWIFT_Group_Exception If the Class is not Loaded
     */
    public function GetVisitorGroupID()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Group_Exception(SWIFT_CLASSNOTLOADED);
        }

        return (int)($this->_visitorGroup['visitorgroupid']);
    }

    /**
     * Insert a visitor group
     *
     * @author Varun Shoor
     * @param string $_title The Visitor Group Title
     * @param string $_color The HEX Color Code for the Visitor Group
     * @return mixed "SWIFT_VisitorGroup" Object on Success, "false" otherwise
     * @throws SWIFT_Group_Exception If Invalid Data Provided or If Creation Fails
     */
    public static function Insert($_title, $_color)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_title)) {
            throw new SWIFT_Group_Exception(SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'visitorgroups', array('title' => ReturnNone($_title), 'dateline' => DATENOW, 'color' => ReturnNone($_color)), 'INSERT');

        $_visitorGroupID = $_SWIFT->Database->Insert_ID();
        if (!$_visitorGroupID) {
            throw new SWIFT_Group_Exception(SWIFT_CREATEFAILED);
        }

        $_SWIFT_VisitorGroupObject = new SWIFT_VisitorGroup($_visitorGroupID);
        if (!$_SWIFT_VisitorGroupObject instanceof SWIFT_VisitorGroup || !$_SWIFT_VisitorGroupObject->GetIsClassLoaded()) {
            throw new SWIFT_Group_Exception(SWIFT_CREATEFAILED);
        }

        self::RebuildVisitorGroupCache();

        return $_SWIFT_VisitorGroupObject;
    }

    /**
     * Update the existing visitor group
     *
     * @author Varun Shoor
     * @param string $_title The Visitor Group Title
     * @param string $_color The HEX Color Code for the Visitor Group
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Group_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Update($_title, $_color)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Group_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_title)) {
            throw new SWIFT_Group_Exception(SWIFT_INVALIDDATA);
        }

        $this->Database->AutoExecute(TABLE_PREFIX . 'visitorgroups', array('title' => ReturnNone($_title), 'dateline' => DATENOW, 'color' => ReturnNone($_color)), 'UPDATE', "visitorgroupid = '" . ($this->GetVisitorGroupID()) . "'");

        self::RebuildVisitorGroupCache();

        return true;
    }

    /**
     * Sets the Color for this Visitor Group
     *
     * @author Varun Shoor
     * @param string $_color The HEX Color Code for this Visitor Group
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Group_Exception If the Class is not Loaded
     */
    public function SetColor($_color)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Group_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('color', $_color);
        $this->UpdatePool('dateline', DATENOW);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieves the Data Array
     *
     * @author Varun Shoor
     * @return mixed "_visitorGroup" Array on Success, "false" otherwise
     * @throws SWIFT_Group_Exception If the Class is not Loaded
     */
    public function GetDataArray()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Group_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_visitorGroup;
    }

    /**
     * Sets the Title for this Visitor Group
     *
     * @author Varun Shoor
     * @param string $_title The Visitor Group Title
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Group_Exception If the Class is not Loaded
     */
    public function SetTitle($_title)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Group_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UpdatePool('title', $_title);
        $this->UpdatePool('dateline', DATENOW);

        $this->ProcessUpdatePool();

        return true;
    }

    /**
     * Retrieves the property of the array
     *
     * @author Varun Shoor
     * @param string $_key The Visitor Group Key
     * @return mixed "Property String" on Success, "false" otherwise
     * @throws SWIFT_Group_Exception If the Class is not Loaded
     */
    public function GetProperty($_key)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Group_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_visitorGroup[$_key];
    }

    /**
     * Delete the existing visitor group
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Group_Exception If the Class is not Loaded
     */
    public function Delete()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Group_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorgroups WHERE visitorgroupid = '" . ($this->GetVisitorGroupID()) . "'");

        $this->SetIsClassLoaded(false);

        self::RebuildVisitorGroupCache();

        return true;
    }

    /**
     * Delete the existing visitor group(s)
     *
     * @author Varun Shoor
     * @param array $_visitorGroupIDList The list of visitor group ids to delete
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteVisitorGroupList($_visitorGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_visitorGroupIDList)) {
            return false;
        }

        $_finalVisitorGroupIDList = array();
        $_index = 1;
        $_finalText = '';
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorgroups WHERE visitorgroupid IN (" . BuildIN($_visitorGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_finalText .= $_index . '. ' . '<img src="' . SWIFT::Get('themepath') . 'images/icon_visitorgroup.gif" align="absmiddle" border="0" /> ' . htmlspecialchars($_SWIFT->Database->Record['title']) . '<br />';
            $_finalVisitorGroupIDList[] = $_SWIFT->Database->Record['visitorgroupid'];
            $_index++;
        }

        SWIFT::Info(sprintf($_SWIFT->Language->Get('titledelvisitorgroup'), count($_finalVisitorGroupIDList)), $_SWIFT->Language->Get('msgdelvisitorgroup') . '<br />' . $_finalText);

        $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "visitorgroups WHERE visitorgroupid IN (" . BuildIN($_visitorGroupIDList) . ")");

        self::RebuildVisitorGroupCache();

        return true;
    }

    /**
     * Rebuilds the Visitor Group Cache
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function RebuildVisitorGroupCache()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_cache = array();

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorgroups ORDER BY visitorgroupid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_cache[$_SWIFT->Database->Record["visitorgroupid"]] = $_SWIFT->Database->Record;
        }

        $_SWIFT->Cache->Update('visitorgroupcache', $_cache);

        return true;
    }

    /**
     * Dispatches the Javascript Variable
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DispatchJSVariable()
    {
        $_SWIFT = SWIFT::GetInstance();

        echo '<script type="text/javascript" language="Javascript">';
        $_visitorGroups = array();
        $_index = 0;
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "visitorgroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_visitorGroups[] = '"' . $_index . '": {"0": "' . (int)($_SWIFT->Database->Record['visitorgroupid']) . '", "1": "' . addslashes($_SWIFT->Database->Record['title']) . '"}';
            $_index++;
        }

        if (!_is_array($_visitorGroups)) {
            $_visitorGroups[] = '"0": {"0": "0", "1": "' . addslashes($_SWIFT->Language->Get('notavailable')) . '"}';
        }

        echo 'var lsvisitorgroupobj = {' . implode(',', $_visitorGroups) . '}';
        echo '</script>';
    }
}
