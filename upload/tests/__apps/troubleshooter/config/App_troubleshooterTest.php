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

namespace Troubleshooter;

/**
 * Class App_troubleshooterTest
 * @group troubleshooter
 */
class App_troubleshooterTest extends \SWIFT_TestCase
{
    /**
     * @throws \SWIFT_Exception
     */
    public function testInitializeReturnsTrue()
    {
        $obj = new SWIFT_App_troubleshooter('troubleshooter');
        $this->assertInstanceOf('Troubleshooter\SWIFT_App_troubleshooter', $obj);

        $obj->SetIsClassLoaded(true);
        $this->assertTrue($obj->Initialize());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $this->assertFalse($obj->Initialize());
    }
}
