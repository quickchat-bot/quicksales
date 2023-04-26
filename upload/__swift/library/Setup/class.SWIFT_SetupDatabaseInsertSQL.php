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
 * The SWIFT Setup Database Insert SQL Manager
 * 
 * @author Varun Shoor
 */
class SWIFT_SetupDatabaseInsertSQL extends SWIFT_Base
{
    private $_tableName;
    private $_insertFields = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_tableName The Table Name
     * @param array $_insertFields The SQL Fields Containere
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function __construct($_tableName, $_insertFields)
    {
        if (!$this->SetName($_tableName) || !$this->SetFields($_insertFields))
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Set the Table Name
     * 
     * @author Varun Shoor
     * @param string $_tableName The Table Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If Invalid Data is Provided
     */
    public function SetName($_tableName)
    {
        $_tableName = Clean($_tableName);

        if (empty($_tableName))
        {
            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_tableName = $_tableName;

        return true;
    }

    /**
     * Retrieve the Table Name
     * 
     * @author Varun Shoor
     * @return mixed "_tableName" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetName()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_tableName;
    }

    /**
     * Set the SQL Fields Data
     * 
     * @author Varun Shoor
     * @param array $_insertFields The SQL Fields Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetFields($_insertFields)
    {
        if (!_is_array($_insertFields))
        {
            return false;
        }

        $this->_insertFields = $_insertFields;

        return true;
    }

    /**
     * Retrieve the SQL Fields Container
     * 
     * @author Varun Shoor
     * @return mixed "_insertFields" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetFields()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_insertFields;
    }
}

?>