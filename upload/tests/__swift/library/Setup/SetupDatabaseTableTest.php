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
 * Class SetupDatabaseTableTest
 * @group library_setup
 */
class SetupDatabaseTableTest extends SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $_tblName = '';
        $_tblFields = '';
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_INVALIDDATA);
        new SWIFT_SetupDatabaseTable($_tblName, $_tblFields);

        $_tblName = 'dummy';
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_INVALIDDATA);
        new SWIFT_SetupDatabaseTable($_tblName, $_tblFields);

        $_tblFields = 'id I PRIMARY AUTO NOTNULL';
        $obj = new SWIFT_SetupDatabaseTable($_tblName, $_tblFields);
        $this->assertInstanceOf('SWIFT_SetupDatabaseTable', $obj);

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Setup_Exception', SWIFT_CLASSNOTLOADED);
        new SWIFT_SetupDatabaseTable($_tblName, $_tblFields);
    }
}
