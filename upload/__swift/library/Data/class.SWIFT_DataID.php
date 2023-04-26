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
 * The Data Storage ID Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_DataID extends SWIFT_Data
{
    protected $_dataID = 0;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param int $_dataID The Data IDe
     * @throws SWIFT_Exception If Creation Fails
     */
    public function __construct($_dataID)
    {
        parent::__construct();

        if (!$this->SetDataID($_dataID))
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
     * Set the Data ID
     *
     * @author Varun Shoor
     * @param int $_dataID The Data ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function SetDataID($_dataID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        } else if (empty($_dataID)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        $this->_dataID = $_dataID;

        return true;
    }

    /**
     * Retrieve the Data ID
     *
     * @author Varun Shoor
     * @return mixed "_dataID" (INT) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDataID()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        return $this->_dataID;
    }
}
?>