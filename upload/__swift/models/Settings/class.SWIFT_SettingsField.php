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
 * The Settings Field Model
 *
 * @author Varun Shoor
 */
class SWIFT_SettingsField extends SWIFT_Model implements SWIFT_Model_Interface
{

    const TABLE_NAME = 'settingsfields';
    const PRIMARY_KEY = 'sfieldid';

    const TABLE_STRUCTURE = "sfieldid I PRIMARY AUTO NOTNULL,
                            sgroupid I DEFAULT '0' NOTNULL,
                            name C(200) DEFAULT '' NOTNULL,
                            customvalue XL,
                            iscustom I2 DEFAULT '0' NOTNULL,
                            settingtype C(100) DEFAULT 'text' NOTNULL,
                            app C(100) DEFAULT '' NOTNULL,
                            displayorder I DEFAULT '0' NOTNULL";

    const INDEX_1 = 'sgroupid';

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
     * Create a new Setting Field
     *
     * @author Varun Shoor
     * @param int $_settingGroupID
     * @param string $_fieldName
     * @param string $_customValue
     * @param bool $_isCustom
     * @param string $_settingType
     * @param string $_appName
     * @param int $_displayOrder
     * @return int Setting Field ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_settingGroupID, $_fieldName, $_customValue, $_isCustom, $_settingType, $_appName, $_displayOrder)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_settingGroupID) || empty($_fieldName) || empty($_appName)) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'settingsfields', array('sgroupid' => ($_settingGroupID), 'name' => $_fieldName, 'customvalue' => $_customValue,
            'iscustom' => ($_isCustom), 'settingtype' => $_settingType, 'app' => $_appName, 'displayorder' => ($_displayOrder)), 'INSERT');
        $_settingFieldID = $_SWIFT->Database->Insert_ID();

        if (!$_settingFieldID) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CREATEFAILED);
        }

        return $_settingFieldID;
    }

    /**
     * Delete a list of Setting Fields
     *
     * @author Varun Shoor
     * @param array $_settingFieldIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_settingFieldIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_settingFieldIDList)) {
            return false;
        }

        $_settingNameList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "settingsfields
            WHERE sfieldid IN (" . BuildIN($_settingFieldIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_settingNameList[] = $_SWIFT->Database->Record['name'];
        }

        if (count($_settingNameList)) {
            $_SWIFT->Database->Query("DELETE FROM " . TABLE_PREFIX . "settings WHERE section = 'settings' AND vkey IN (" . BuildIN($_settingNameList) . ")");
        }

        parent::DeleteList($_settingFieldIDList);

        SWIFT_Settings::RebuildCache();

        return true;
    }

    /**
     * Delete the Settings on a list of setting groups
     *
     * @author Varun Shoor
     * @param array $_settingGroupIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function DeleteOnSettingGroup($_settingGroupIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_settingGroupIDList)) {
            return false;
        }

        $_settingFieldIDList = array();
        $_SWIFT->Database->Query("SELECT sfieldid FROM " . TABLE_PREFIX . "settingsfields
            WHERE sgroupid IN (" . BuildIN($_settingGroupIDList) . ")");
        while ($_SWIFT->Database->NextRecord()) {
            $_settingFieldIDList[] = $_SWIFT->Database->Record['sfieldid'];
        }

        self::DeleteList($_settingFieldIDList);

        return true;
    }

}
?>
