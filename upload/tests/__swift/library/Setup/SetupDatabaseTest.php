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
 * Class SetupDatabaseTest
 * @group library_setup
 */
class SetupDatabaseTest extends SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = new SWIFT_SetupDatabase('base');
        $this->assertInstanceOf('SWIFT_SetupDatabase', $obj);
        $this->assertEquals('base', $obj->GetAppName());
    }
}
