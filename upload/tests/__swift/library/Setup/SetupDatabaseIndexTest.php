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
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

/**
 * Class SetupDatabaseIndexTest
 * @group library_setup
 */
class SetupDatabaseIndexTest extends SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $_idxName = '';
        $_tblName = '';
        $_idxFields = '';

        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_INVALIDDATA);
        new SWIFT_SetupDatabaseIndex($_idxName, $_tblName, $_idxFields);

        $_idxName = 'idx';
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_INVALIDDATA);
        new SWIFT_SetupDatabaseIndex($_idxName, $_tblName, $_idxFields);

        $_tblName = 'tbl';
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_INVALIDDATA);
        new SWIFT_SetupDatabaseIndex($_idxName, $_tblName, $_idxFields);

        $_idxFields = 'id';
        $obj = new SWIFT_SetupDatabaseIndex($_idxName, $_tblName, $_idxFields);
        $this->assertInstanceOf('SWIFT_SetupDatabaseIndex', $obj);

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_CLASSNOTLOADED);
        new SWIFT_SetupDatabaseIndex($_idxName, $_tblName, $_idxFields);
    }
}
