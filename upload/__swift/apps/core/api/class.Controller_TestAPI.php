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
 * The Test API Controller. This controller is used to test API calls.
 *
 * @author Varun Shoor
 */
class Controller_TestAPI extends Controller_api implements SWIFT_REST_Interface
{
    /**
     * List Test
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetList()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo 'GET List' . PHP_EOL . PHP_EOL;

        echo 'ARGS: ';
        print_r(func_get_args());

        echo PHP_EOL;

        echo 'VARS: ';
        print_r($_GET);

        return true;
    }

    /**
     * Get Test
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get($_ID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo 'GET: ' . $_ID . PHP_EOL . PHP_EOL;

        echo 'ARGS: ';
        print_r(func_get_args());

        echo PHP_EOL;

        echo 'VARS: ';
        print_r($_GET);

        return true;
    }

    /**
     * Post Test
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Post()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo 'POST' . PHP_EOL . PHP_EOL;

        echo 'ARGS: ';
        print_r(func_get_args());

        echo PHP_EOL;

        echo 'VARS: ';
        print_r($_POST);

        return true;
    }

    /**
     * Put Test
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Put($_ID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo 'PUT: ' . $_ID . PHP_EOL . PHP_EOL;

        echo 'ARGS: ';
        print_r(func_get_args());

        echo PHP_EOL;

        echo 'VARS: ';
        print_r($_POST);

        return true;
    }

    /**
     * Delete Test
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Delete($_ID)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        echo 'DELETE: ' . $_ID . PHP_EOL . PHP_EOL;

        echo 'ARGS: ';
        print_r(func_get_args());

        return true;
    }
}
