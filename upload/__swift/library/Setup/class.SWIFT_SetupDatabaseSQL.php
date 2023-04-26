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
 * The SWIFT Setup Database Direct SQL Execution Class
 * 
 * @author Varun Shoor
 */
class SWIFT_SetupDatabaseSQL extends SWIFT_Base
{
    private $_sqlStatement;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_sqlStatement The SQL Statemente
     * @throws SWIFT_Setup_Exception If the Class is not Laoded
     */
    public function __construct($_sqlStatement)
    {
        if (!$this->SetSQL($_sqlStatement))
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
     * Sets the SQL Statement
     * 
     * @author Varun Shoor
     * @param string $_sqlStatement The SQL Statement
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If Invalid Data is Provided
     */
    public function SetSQL($_sqlStatement)
    {
        if (empty($_sqlStatement))
        {
            throw new SWIFT_Setup_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_sqlStatement = $_sqlStatement;

        return true;
    }

    /**
     * Gets the SQL Statement
     * 
     * @author Varun Shoor
     * @return mixed "_sqlStatement" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Setup_Exception If the Class is not Loaded
     */
    public function GetSQL()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Setup_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_sqlStatement;
    }
}
?>