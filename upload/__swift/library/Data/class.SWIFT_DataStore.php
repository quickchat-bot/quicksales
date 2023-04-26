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
 * The Data Storage Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_DataStore extends SWIFT_Data
{
    protected $_dataStore = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param array $_dataStore The Data Storagee
     * @throws SWIFT_Exception If object creation fails
     */
    public function __construct(array $_dataStore)
    {
        parent::__construct();

        if (!$this->SetDataStore($_dataStore))
        {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }
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
     * Set the Data Store
     *
     * @author Varun Shoor
     * @param array $_dataStore The Data Storage
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function SetDataStore(array $_dataStore)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_dataStore = $_dataStore;

        return true;
    }

    /**
     * Retrieve the currently set data store
     *
     * @author Varun Shoor
     * @return mixed "_dataStore" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataStore()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataStore;
    }
}
?>