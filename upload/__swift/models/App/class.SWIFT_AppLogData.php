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
 * The App Log Data Model
 *
 * @author Varun Shoor
 */
class SWIFT_AppLogData extends SWIFT_Model implements SWIFT_Model_Interface
{

    const TABLE_NAME        =    'applogdata';
    const PRIMARY_KEY        =    'applogdataid';

    const TABLE_STRUCTURE    =    "applogdataid I PRIMARY AUTO NOTNULL,
                                applogid I DEFAULT '0' NOTNULL,
                                logdata X NOTNULL";

    const INDEX_1            =    'applogid';


    const CLEANUP_DAYS        =    60;

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
     * Create a new App Log Data entry
     *
     * @author Varun Shoor
     * @param int $_appLogID
     * @param string $_logData
     * @return int App Log Data ID
     * @throws SWIFT_Exception If Invalid Data is Provided or If the Object could not be created
     */
    public static function Create($_appLogID, $_logData)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (empty($_appLogID)) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_INVALIDDATA);
        }

        $_SWIFT->Database->AutoExecute(TABLE_PREFIX . 'applogdata', array('applogid' => ($_appLogID), 'logdata' => $_logData), 'INSERT');
        $_appLogDataID = $_SWIFT->Database->Insert_ID();

        if (!$_appLogDataID) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CREATEFAILED);
        }

        return $_appLogDataID;
    }

    /**
     * Delete on a list of app logs
     *
     * @author Varun Shoor
     * @param array $_appLogIDList
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function DeleteOnAppLog($_appLogIDList)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!_is_array($_appLogIDList)) {
            return false;
        }

        $_appLogDataIDList = array();
        $_SWIFT->Database->Query('SELECT applogdataid FROM ' . TABLE_PREFIX . 'applogdata
                                WHERE applogid IN (' . BuildIN($_appLogIDList) . ')');
        while ($_SWIFT->Database->NextRecord()) {
            $_appLogDataIDList[] = $_SWIFT->Database->Record['applogdataid'];
        }

        if (!count($_appLogDataIDList)) {
            return false;
        }

        self::DeleteList($_appLogDataIDList);

        return true;
    }

    /**
     * Cleans up the log data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function CleanUp()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_dateThreshold = DATENOW - (self::CLEANUP_DAYS * 86400);

        $_appLogDataIDList = array();

        $_SWIFT->Database->Query('SELECT applogdata.applogdataid AS applogdataid FROM ' . TABLE_PREFIX . 'applogdata AS applogdata
                                LEFT JOIN ' . TABLE_PREFIX . "applogs AS applogs ON (applogdata.applogid = applogs.applogid)
                                WHERE applogs.dateline <= '" . $_dateThreshold . "'");
        while ($_SWIFT->Database->NextRecord()) {
            $_appLogDataIDList[] = $_SWIFT->Database->Record['applogdataid'];
        }

        if (!count($_appLogDataIDList)) {
            return false;
        }

        self::DeleteList($_appLogDataIDList);

        return true;
    }

    /**
     * Runs daily to cleanup the log data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public static function Daily()
    {
        self::CleanUp();

        return true;
    }
}
