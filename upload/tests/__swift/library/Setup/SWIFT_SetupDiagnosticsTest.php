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
 * Class SetupDatabaseIndexTest
 * @group library_setup
 */
class SWIFT_SetupDiagnosticsTest extends SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = new SWIFT_SetupDiagnostics();
        $this->assertInstanceOf('SWIFT_SetupDiagnostics', $obj);
    }
}
