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
 * The SWIFT Setup Database Index Manager
 * 
 * @author Varun Shoor
 */
class SWIFT_SetupDatabaseIndex extends SWIFT_Base
{
    private $_tableName;
    private $_indexName;
    private $_indexFields = array();
    private $_optionsContainer = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_indexName The Table Name
     * @param string $_tableName The Table Name
     * @param string $_indexFields The Table Fields Container
     * @param array $_optionsContainer The Options Containere
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function __construct($_indexName, $_tableName, $_indexFields, $_optionsContainer = array())
    {
        if (!$this->SetName($_indexName) || !$this->SetTableName($_tableName) || !$this->SetFields($_indexFields) || !$this->SetOptions($_optionsContainer))
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);
        }

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
     * Set the Table Name
     * 
     * @author Varun Shoor
     * @param string $_tableName The Table Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If Invalid Data is Provided
     */
    public function SetTableName($_tableName)
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
    public function GetTableName()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_tableName;
    }

    /**
     * Set the Index Name
     * 
     * @author Varun Shoor
     * @param string $_indexName The Table Name
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If Invalid Data is Provided
     */
    public function SetName($_indexName)
    {
        $_indexName = Clean($_indexName);

        if (empty($_indexName))
        {
            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_indexName = $_indexName;

        return true;
    }

    /**
     * Retrieve the Index Name
     * 
     * @author Varun Shoor
     * @return mixed "_indexName" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetName()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_indexName;
    }

    /**
     * Set the Index Fields
     * 
     * @author Varun Shoor
     * @param string $_indexFields The Table Fields Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If Invalid Data is Provided
     */
    public function SetFields($_indexFields)
    {
        if (empty($_indexFields))
        {
            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_indexFields = $_indexFields;

        return true;
    }

    /**
     * Retrieve the Index Fields Container
     * 
     * @author Varun Shoor
     * @return mixed "_indexFields" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetFields()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_indexFields;
    }

    /**
     * Set the Index Options
     * 
     * @author Varun Shoor
     * @param array $_optionsContainer The Options Container
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetOptions($_optionsContainer)
    {
        if (!_is_array($_optionsContainer))
        {
            $_optionsContainer = array();
        }

        $this->_optionsContainer = $_optionsContainer;

        return true;
    }

    /**
     * Get the Options Container
     * 
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetOptions()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_optionsContainer;
    }
}

?>