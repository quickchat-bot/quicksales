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
 * The SWIFT Setup Database Table handler
 * 
 * @author Varun Shoor
 */
class SWIFT_SetupDatabaseTable extends SWIFT_Base
{
    private $_tableName;
    private $_tableFields;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_tableName The Table Namee
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function __construct($_tableName, $_tableFields)
    {
        if (!$this->SetName($_tableName) || !$this->SetFields($_tableFields))
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
     * Set the Table Fields
     * 
     * @author Varun Shoor
     * @param string $_tableFields The Table Fields Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If Invalid Data is Provided
     */
    public function SetFields($_tableFields)
    {
        if (empty($_tableFields))
        {
            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_tableFields = $_tableFields;

        return true;
    }

    /**
     * Retrieve the Table Fields Container
     * 
     * @author Varun Shoor
     * @return mixed "_tableFields" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetFields()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_tableFields;
    }
}

?>