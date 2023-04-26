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

/**
 * The Settings Group Model
 *
 * @author Varun Shoor
 */
class SWIFT_SettingsGroup extends SWIFT_Model implements SWIFT_Model_Interface
{
    const TABLE_NAME = 'settingsgroups';
    const PRIMARY_KEY = 'sgroupid';

    const TABLE_STRUCTURE = "sgroupid I PRIMARY AUTO NOTNULL,
                            name C(100) DEFAULT '' NOTNULL,
                            app C(100) DEFAULT '' NOTNULL,
                            displayorder I DEFAULT '0' NOTNULL,
                            ishidden I2 DEFAULT '0' NOTNULL";

    const INDEX_1 = 'app';
    const INDEX_2 = 'name';

    const COLUMN_RENAME_MODULE = 'app';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_Data $_SWIFT_DataObject The SWIFT_Data Object
     * @throws SWIFT_Exception If the Record could not be loaded
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject)
    {
        parent::__construct($_SWIFT_DataObject);
    }

    /**
     * Create a new Setting Group
     *
     * @author Varun Shoor
     * @param string $_groupName
     * @param string $_appName
     * @param int $_displayOrder
     * @param bool $_isHidden (OPTIONAL)
     * @return int Setting Group ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_groupName, $_appName, $_displayOrder, $_isHidden = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_groupName) || empty($_appName)) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'settingsgroups', array('name' => $_groupName, 'app' => $_appName, 'displayorder' => ($_displayOrder), 'ishiddne' => ($_isHidden)), 'INSERT');
        $_settingGroupID = $_SWIFT->Database->Insert_ID();

        if (!$_settingGroupID) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CREATEFAILED);
        }

        return $_settingGroupID;
    }

    /**
     * Delete a list of Setting groups
     *
     * @author Varun Shoor
     * @param array $_settingGroupIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_settingGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_settingGroupIDList)) {
            return false;
        }

        SWIFT_SettingsField::DeleteOnSettingGroup($_settingGroupIDList);

        parent::DeleteList($_settingGroupIDList);

        return true;
    }

    /**
     * Delete on a list of App Names
     *
     * @author Varun Shoor
     * @param string $_appNameList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnApp($_appNameList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_appNameList)) {
            return false;
        }

        $_settingGroupIDList = array();
        $_SWIFT->Database->Query("SELECT sgroupid FROM " . TABLE_PREFIX . "settingsgroups
            WHERE app IN (" . BuildIN($_appNameList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_settingGroupIDList[] = $_SWIFT->Database->Record['sgroupid'];
        }

        self::DeleteList($_settingGroupIDList);

        return true;
    }

}
?>
