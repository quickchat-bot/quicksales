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
 * Class SetupDatabaseInsertSQLTest
 * @group library_setup
 */
class SetupDatabaseInsertSQLTest extends SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $_tableName = '';
        $_insertFields = [];
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_INVALIDDATA);
        new SWIFT_SetupDatabaseInsertSQL($_tableName, $_insertFields);

        $_tableName = 'dummy';
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_INVALIDDATA);
        new SWIFT_SetupDatabaseInsertSQL($_tableName, $_insertFields);

        $_insertFields = ['id' => 1];
        $obj = new SWIFT_SetupDatabaseInsertSQL($_tableName, $_insertFields);
        $this->assertInstanceOf('SWIFT_SetupDatabaseInsertSQL', $obj);

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_CLASSNOTLOADED);
        new SWIFT_SetupDatabaseInsertSQL($_tableName, $_insertFields);
    }
}
