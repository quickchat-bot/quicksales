<?php
/**
 * ###############################################
 *
 * Kayako Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

/**
 * Class SetupDatabaseSQLTest
 * @group library_setup
 */
class SetupDatabaseSQLTest extends SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $_sql = '';
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_INVALIDDATA);
        new SWIFT_SetupDatabaseSQL($_sql);

        $_sql = 'select 1';
        $obj = new SWIFT_SetupDatabaseSQL($_sql);
        $this->assertInstanceOf('SWIFT_SetupDatabaseSQL', $obj);

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_CLASSNOTLOADED);
        new SWIFT_SetupDatabaseSQL($_sql);
    }
}
