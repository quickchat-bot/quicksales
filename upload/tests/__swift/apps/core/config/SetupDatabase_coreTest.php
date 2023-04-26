<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

/**
 * Class SetupDatabase_coreTest
 * @group app_core_config
 */
class SetupDatabase_coreTest extends SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = new SWIFT_SetupDatabase_core();
        $this->assertInstanceOf('SWIFT_SetupDatabase_core', $obj);
        $this->assertEquals('core', $obj->GetAppName());
    }
}
