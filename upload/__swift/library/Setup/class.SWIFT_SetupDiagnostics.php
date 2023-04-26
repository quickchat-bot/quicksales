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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * The Setup Diagnostics System
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDiagnostics extends SWIFT_Model
{
    // Core Constants
    const TABLE_MISSING = 'tbmissing';
    const TABLE_RENAME = 'tbrename';

    const COLUMN_MISSING = 'clmissing';
    const COLUMN_METATYPEMISMATCH = 'clmetatypemismatch';
    const COLUMN_LENGTHMISMATCH = 'cllengthmismatch';
    const COLUMN_QUERIES = 'clqueries';
    const COLUMN_RENAME = 'clrename';

    const INDEX_MISSING = 'inmissing';
    const INDEX_MISMATCH = 'inmismatch';

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Retrieve the Table Structure
     *
     * @author Varun Shoor
     * @return array array(array(tableName, SQL), ...) The Table Structure Array
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetTableStructure($_appName = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ADODBDICT = $this->Database->GetADODBDictionaryObject();

        $_databaseTables = $_ADODBDICT->MetaTables();
        $_appStructureContainer = $this->GetInstalledAppStructures($_appName);

        $_renamedTables = $_appStructureContainer[3];

        $_tableStructureContainer = array();

        foreach ($_appStructureContainer[1] as $_key=>$_val)
        {
            foreach ($_val as $_tableKey=>$_tableVal)
            {
                // Missing Tables
                if (!in_array($_tableVal->GetName(), $_databaseTables) && !isset($_renamedTables[$_key][$_tableVal->GetName()]))
                {
                    $_sql = $_ADODBDICT->CreateTableSQL($_tableVal->GetName(), $_tableVal->GetFields());
                    $_tableStructureContainer[self::TABLE_MISSING][$_key][] = array($_tableVal->GetName(), $_sql);

                // Table renamed?
                } else if (!in_array($_tableVal->GetName(), $_databaseTables) && isset($_renamedTables[$_key][$_tableVal->GetName()]) && in_array($_renamedTables[$_key][$_tableVal->GetName()], $_databaseTables)) {
                    $_tableStructureContainer[self::TABLE_RENAME][$_key][] = array($_renamedTables[$_key][$_tableVal->GetName()], array('RENAME TABLE ' . $_renamedTables[$_key][$_tableVal->GetName()] . ' TO ' . $_tableVal->GetName()));
                }
            }
        }

        return $_tableStructureContainer;
    }

    /**
     * Retrieve the Column Structure
     *
     * @author Varun Shoor
     * @return array|bool array(array(columnName, SQL), ...) The Column Structure Array
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetColumnStructure($_appName = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ADODBDICT = $this->Database->GetADODBDictionaryObject();

        $_databaseTables = $_ADODBDICT->MetaTables();
        $_appStructureContainer = $this->GetInstalledAppStructures($_appName);

        $_renamedColumns = $_appStructureContainer[4];

        $_columnStructureContainer = array();

        foreach ($_appStructureContainer[1] as $_key=>$_val)
        {
            foreach ($_val as $_tableKey=>$_tableVal)
            {
                // The actual Database structure of the table
                $_databaseTableStructure = $_ADODBDICT->MetaColumns($_tableVal->GetName());
                // The structure specified in app/setup.php
                $_tableStructure = $this->GetTableFieldProcessedArray($_tableVal->GetFields());

                foreach ($_tableStructure as $_structureKey=>$_structureVal)
                {
                    $_changeTableSQLResult = $_ADODBDICT->ChangeTableSQL(TABLE_PREFIX.$_tableKey, $_tableVal->GetFields());

                    // Place in all the possible queries for this table..
                    if (_is_array($_changeTableSQLResult))
                    {
                        $_columnStructureContainer[self::COLUMN_QUERIES][strtolower($_tableVal->GetName())] = $_changeTableSQLResult;
                    }

                    if (substr($_changeTableSQLResult[0], 0, strlen('CREATE TABLE ' . TABLE_PREFIX.$_tableVal->GetName() . ' (')) == 'CREATE TABLE ' . TABLE_PREFIX . $_tableVal->GetName() . ' (')
                    {
                        continue;
                    }

                    // ====> Column does not exist?
                    if (!isset($_databaseTableStructure[strtoupper($_structureKey)]))
                    {
                        // Rename the column
                        $_renameColumnValue = false;
                        if (isset($_renamedColumns[$_key][$_tableVal->GetName()][$_structureKey])) {
                            $_renameColumnValue = $_renamedColumns[$_key][$_tableVal->GetName()][$_structureKey];
                        }

                        if (_is_array($_changeTableSQLResult))
                        {
                            foreach ($_changeTableSQLResult as $_changeTableKey=>$_changeTableVal)
                            {
                                if (preg_match('/' . TABLE_PREFIX . $_tableKey . ' ADD ' . $_structureKey . '/', $_changeTableVal, $_matches))
                                {
                                    // Column has to be renamed
                                    $_renameMatches = array();
                                    if (!empty($_renameColumnValue) && isset($_databaseTableStructure[strtoupper($_renameColumnValue)]) && preg_match('/(.*)ADD ' . $_structureKey . ' (.*)$/i', $_changeTableVal, $_renameMatches)) {
                                        $_columnStructureContainer[self::COLUMN_RENAME][strtolower($_tableVal->GetName())][] = array($_renameColumnValue, 'ALTER TABLE ' . $_tableVal->GetName() . ' CHANGE ' . $_renameColumnValue . ' ' . $_structureKey . ' ' . $_renameMatches[2]);
                                    } else if (in_array('PRIMARY', $_tableStructure[$_structureKey])) {
                                        $_columnStructureContainer[self::COLUMN_MISSING][strtolower($_tableVal->GetName())][] = array($_structureKey, $_changeTableVal . ', ADD PRIMARY KEY (' . $_structureKey . ')');
                                    } else {
                                        $_columnStructureContainer[self::COLUMN_MISSING][strtolower($_tableVal->GetName())][] = array($_structureKey, $_changeTableVal);
                                    }
                                }
                            }
                        } else {
                            // Something went wrong..

                            return false;
                        }
                    } else {
                        // Check the sanity of the column structure
                        foreach ($_structureVal as $_subKey=>$_subVal)
                        {
                            $_fieldName = strtoupper($_structureKey);

                            $_matches = array();

                            if (preg_match('/^([a-zA-Z]+)\(([0-9]+)\)$/', strtoupper($_subVal), $_matches))
                            {
                                $_metaType = $_ADODBDICT->ActualType($_matches[1]);
                            } else {
                                $_metaType = $_ADODBDICT->ActualType($_subVal);
                            }

                            $_dbStructureType = strtoupper($_databaseTableStructure[$_fieldName]->type);
                            if ($_dbStructureType == 'INT' && $_metaType == 'INTEGER')
                            {
                                $_dbStructureType = 'INTEGER';
                            }

                            if ($_dbStructureType == 'DECIMAL' && $_metaType == 'NUMERIC')
                            {
                                $_dbStructureType = 'NUMERIC';
                            }


                            // Length Specified C(100)
                            if (preg_match('/^([a-zA-Z]+)\(([0-9]+)\)$/', strtoupper($_subVal), $_matches))
                            {
                                // ====> Meta Type is different
                                if (strtoupper($_metaType) != $_dbStructureType)
                                {
                                    if (_is_array($_changeTableSQLResult))
                                    {
                                        foreach ($_changeTableSQLResult as $changetablekey=>$changetableval)
                                        {
                                            if (stristr($changetableval, 'ALTER TABLE ' . TABLE_PREFIX . 'attachmentchunks MODIFY COLUMN contents'))
                                            {
                                                continue;
                                            }

                                            if (preg_match('/'. TABLE_PREFIX.$_tableKey .' MODIFY COLUMN '. $_structureKey .'/', $changetableval, $_matches))
                                            {
                                                $_columnStructureContainer[self::COLUMN_METATYPEMISMATCH][strtolower($_tableVal->GetName())][] = array($_structureKey, $changetableval);
                                            }
                                        }
                                    } else {
                                        // Something went wrong..

                                        return false;
                                    }
                                // ====> Character Length is different
                                } else if ($_matches[2] != $_databaseTableStructure[$_fieldName]->max_length) {
                                    if (_is_array($_changeTableSQLResult))
                                    {
                                        foreach ($_changeTableSQLResult as $changetablekey=>$changetableval)
                                        {
                                            if (stristr($changetableval, 'ALTER TABLE ' . TABLE_PREFIX . 'attachmentchunks MODIFY COLUMN contents'))
                                            {
                                                continue;
                                            }

                                            if (preg_match('/'. TABLE_PREFIX.$_tableKey .' MODIFY COLUMN '. $_structureKey .'/', $changetableval, $_matches))
                                            {
                                                $_columnStructureContainer[self::COLUMN_LENGTHMISMATCH][strtolower($_tableVal->GetName())][] = array($_structureKey, $changetableval);
                                            }
                                        }
                                    } else {
                                        // Something went wrong..

                                        return false;
                                    }
                                }

                            // Fixed Length
                            } else if ($_subVal == 'I' || $_subVal == 'I1' || $_subVal == 'I2' || $_subVal == 'I4' || $_subVal == 'I8' || $_subVal == 'X' || $_subVal == 'C' || $_subVal == 'XL' || $_subVal == 'C2' || $_subVal == 'X2' || $_subVal == 'B' || $_subVal == 'D' || $_subVal == 'T' || $_subVal == 'L' || $_subVal == 'F' || $_subVal == 'N') {

                                // ====> Meta Type is Different
                                if (strtoupper($_metaType) != $_dbStructureType)
                                {
                                    if (_is_array($_changeTableSQLResult))
                                    {
                                        foreach ($_changeTableSQLResult as $changetablekey=>$changetableval)
                                        {
                                            if (stristr($changetableval, 'ALTER TABLE ' . TABLE_PREFIX . 'attachmentchunks MODIFY COLUMN contents'))
                                            {
                                                continue;
                                            }

                                            if (preg_match('/'. TABLE_PREFIX.$_tableKey .' MODIFY COLUMN '. $_structureKey .'/', $changetableval, $_matches))
                                            {
                                                $_columnStructureContainer[self::COLUMN_METATYPEMISMATCH][strtolower($_tableVal->GetName())][] = array($_structureKey, $changetableval);
                                            }
                                        }
                                    } else {
                                        // Something went wrong..

                                        return false;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $_columnStructureContainer;
    }

    /**
     * Retrieve the Index Structure
     *
     * @author Varun Shoor
     * @return array array(array(indexName, SQL), ...) The Index Structure Array
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetIndexStructure($_appName = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ADODBDICT = $this->Database->GetADODBDictionaryObject();

        $_databaseTables = $_ADODBDICT->MetaTables();
        $_appStructureContainer = $this->GetInstalledAppStructures($_appName);

        $_indexStructureContainer = array();

        foreach ($_appStructureContainer[2] as $_key=>$_val)
        {
            foreach ($_val as $_indexContainerKey=>$_indexContainerVal)
            {
                $_indexTableStructure = $_ADODBDICT->MetaIndexes(TABLE_PREFIX.$_indexContainerKey);
                $_indexContainer = array();
                if (_is_array($_indexTableStructure))
                {
                    foreach ($_indexTableStructure as $_indexTSKey => $_indexTSVal)
                    {
                        $_indexContainer[$_indexTSKey] = $_indexTSVal;
                    }
                }

                if (_is_array($_indexContainerVal))
                {
                    foreach ($_indexContainerVal as $_indexKey=>$_indexVal)
                    {
                        if (!isset($_indexContainer[$_indexVal->GetName()]))
                        {
                            $_indexStructureContainer[self::INDEX_MISSING][TABLE_PREFIX.strtolower($_indexContainerKey)][] = array($_indexVal->GetName(), $_ADODBDICT->CreateIndexSQL($_indexVal->GetName(), $_indexVal->GetTableName(), $_indexVal->GetFields(), $_indexVal->GetOptions()));
                        } else {
                            $_indexFields = explode(",", $_indexVal->GetFields());
                            foreach ($_indexFields as $_indexFieldsKey=>$_indexFieldsVal)
                            {
                                if ($_indexFieldsVal != "sha1") {
                                    $_indexFieldsVal = trim(preg_replace('/[(\d)]/', '', $_indexFieldsVal));
                                }

                                if (!in_array($_indexFieldsVal, $_indexContainer[$_indexVal->GetName()]['columns']))
                                {
                                    $_indexStructureContainer[self::INDEX_MISMATCH][TABLE_PREFIX.strtolower($_indexContainerKey)][] = array($_indexVal->GetName(), array_merge($_ADODBDICT->DropIndexSQL($_indexVal->GetName(), TABLE_PREFIX.strtolower($_indexContainerKey)), $_ADODBDICT->CreateIndexSQL($_indexVal->GetName(), $_indexVal->GetTableName(), $_indexVal->GetFields(), $_indexVal->GetOptions())));
                                }
                            }

                            // If mismatch not declared yet then check for unique key..
                            $_isUnique = false;
                            if (in_array('UNIQUE', $_indexVal->GetOptions()))
                            {
                                $_isUnique = true;
                            }

                            if (!isset($_indexStructureContainer[self::INDEX_MISMATCH][TABLE_PREFIX.strtolower($_indexContainerKey)]) && ($_indexContainer[$_indexVal->GetName()]['unique'] != $_isUnique))
                            {
                                $_indexStructureContainer[self::INDEX_MISMATCH][TABLE_PREFIX.strtolower($_indexContainerKey)][] = array($_indexVal->GetName(), array_merge($_ADODBDICT->DropIndexSQL($_indexVal->GetName(), TABLE_PREFIX.strtolower($_indexContainerKey)), $_ADODBDICT->CreateIndexSQL($_indexVal->GetName(), $_indexVal->GetTableName(), $_indexVal->GetFields(), $_indexVal->GetOptions())));
                            }
                        }
                    }
                }
            }
        }

        return $_indexStructureContainer;
    }

    /**
     * Processed the ADODB Dictionary field data into an array
     *
     * @author Varun Shoor
     * @return array|bool The Processed Array
     */
    protected function GetTableFieldProcessedArray($_fields)
    {
        if (!strstr($_fields, ','))
        {
            $_fieldContainer = array($_fields);
        } else {
            $_fieldContainer = explode(',', $_fields);
        }

        if (!_is_array($_fieldContainer))
        {
            return false;
        }

        $_processedContainer = array();
        foreach ($_fieldContainer as $key=>$val)
        {
            preg_match_all("/\S+(?:\s+'[^']+')?/", $val, $_fieldDataContainer);

            if (empty($_fieldDataContainer[0][0]))
            {
                continue;
            }


            $_processedContainer[$_fieldDataContainer[0][0]] = $_fieldDataContainer[0];
        }

        return $_processedContainer;
    }

    /**
     * Retrieve the Database Structure of Registered Apps
     *
     * @author Varun Shoor
     * @return mixed
     */
    protected function GetInstalledAppStructures($_appName = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_installedAppList = array();

        if (!empty($_appName))
        {
            $_installedAppList[$_appName] = '1';
        } else {
            $this->Database->Query("SELECT * FROM ". TABLE_PREFIX . "settings WHERE section = 'installedapps' ORDER BY settingid ASC");
            while ($this->Database->NextRecord())
            {
                $_installedAppList[$this->Database->Record['vkey']] = $this->Database->Record['data'];
            }
            $_installedAppList[SWIFT_App::SWIFT_APPCORE] = "1";

            if (SWIFT::Get('dbappstructurecache'))
            {
                return SWIFT::Get('dbappstructurecache');
            }
        }

        $_appList = SWIFT_App::ListApps();
        if (!_is_array($_appList))
        {
            return false;
        }

        $_finalAppList = $_dbStructure = $_indexStructure = $_renamedTables = $_renamedColumns = array();
        foreach ($_appList as $_appName)
        {
            if (isset($_installedAppList[$_appName]) && $_installedAppList[$_appName] == '1')
            {
                $_finalAppList[] = $_appName;

                $_appDirectory = SWIFT_App::GetAppDirectory($_appName);
                if (!$_appDirectory)
                {
                    return false;
                }

                $_appSetupClassName = 'SWIFT_SetupDatabase_' . $_appName;
                $_appSetupFile = $_appDirectory . '/' . SWIFT_CONFIGDIRECTORY . '/class.'. $_appSetupClassName . '.php';
                $_appSetupClassName = prepend_app_namespace($_appName, $_appSetupClassName);
                if (!file_exists($_appSetupFile))
                {
                    return false;
                }

                require_once ($_appSetupFile);
                if (!class_exists($_appSetupClassName, false))
                {
                    return false;
                }

                $_SWIFT_SetupDatabaseObject = new $_appSetupClassName();
                if (!$_SWIFT_SetupDatabaseObject instanceof SWIFT_SetupDatabase || !$_SWIFT_SetupDatabaseObject->GetIsClassLoaded())
                {
                    return false;
                }

                $_SWIFT_SetupDatabaseObject->LoadTables();

                $_dbStructure[$_appName] = $_SWIFT_SetupDatabaseObject->GetTableContainer();
                $_indexStructure[$_appName] = $_SWIFT_SetupDatabaseObject->GetIndexContainer();
                $_renamedTables[$_appName] = $_SWIFT_SetupDatabaseObject->GetRenamedTables();
                $_renamedColumns[$_appName] = $_SWIFT_SetupDatabaseObject->GetRenamedColumns();
            }
        }

        SWIFT::Set('dbappstructurecache', array($_finalAppList, $_dbStructure, $_indexStructure, $_renamedTables, $_renamedColumns));

        return SWIFT::Get('dbappstructurecache');
    }
}
?>