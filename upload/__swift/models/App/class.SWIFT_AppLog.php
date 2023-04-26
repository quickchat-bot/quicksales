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

/**
 * The App Installation/Upgrade Log
 *
 * @author Varun Shoor
 */
class SWIFT_AppLog extends SWIFT_Model implements SWIFT_Model_Interface
{
    const TABLE_NAME        =    'applogs';
    const PRIMARY_KEY        =    'applogid';

    const TABLE_STRUCTURE    =    "applogid I PRIMARY AUTO NOTNULL,
                                appname C(255) DEFAULT '' NOTNULL,
                                logtype I2 DEFAULT '0' NOTNULL,
                                dateline I DEFAULT '0' NOTNULL";

    const INDEX_1            =    'appname, logtype';
    const INDEX_2            =    'logtype';

    const TYPE_INSTALL        =    1;
    const TYPE_UNINSTALL    =    2;
    const TYPE_UPGRADE        =    3;

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
     * Check to see whether its a valid log type
     *
     * @author Varun Shoor
     * @param mixed $_logType
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_logType)
    {
        return ($_logType == self::TYPE_INSTALL || $_logType == self::TYPE_UNINSTALL || $_logType == self::TYPE_UPGRADE);
    }

    /**
     * Create a new App Log
     *
     * @author Varun Shoor
     * @param SWIFT_App $_SWIFT_AppObject
     * @param mixed $_logType
     * @param string $_logData
     * @return int App Log ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create(SWIFT_App $_SWIFT_AppObject, $_logType, $_logData)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$_SWIFT_AppObject instanceof SWIFT_App || !$_SWIFT_AppObject->GetIsClassLoaded() || !self::IsValidType($_logType)) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'applogs', array('appname' => $_SWIFT_AppObject->GetName(), 'logtype' => (int) ($_logType), 'dateline' => DATENOW));
        $_appLogID = $_SWIFT->Database->Insert_ID();

        if (!$_appLogID) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CREATEFAILED);
        }

        SWIFT_AppLogData::Create($_appLogID, $_logData);

        return $_appLogID;
    }

    /**
     * Delete a list of App Log's
     *
     * @author Varun Shoor
     * @param array $_appLogIDList
     * @return bool "true" on Success, "false" otherwise
     */
    public static function DeleteList($_appLogIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_appLogIDList)) {
            return false;
        }

        SWIFT_AppLogData::DeleteOnAppLog($_appLogIDList);

        parent::DeleteList($_appLogIDList);

        return true;
    }

}
?>
