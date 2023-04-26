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
 * The Javascript Packer Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_JavaScriptPacker extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
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
     * Pack the Javascript code
     *
     * @author Varun Shoor
     * @param string $_javaScriptCode The Javascript Code
     * @return mixed "_packedCode" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Pack($_javaScriptCode)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_JavaScriptPackerObject = new \Tholu\Packer\Packer($_javaScriptCode, 'Normal', true, false);
        $_packedCode = $_JavaScriptPackerObject->pack();

        return $_packedCode;
    }
}
?>
