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

namespace Base;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class App_baseTest
 * @group base
 */
class App_baseTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\SWIFT_App_base', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInitializeReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Interface->SetInterface(\SWIFT_Interface::INTERFACE_CLIENT);

        $this->assertTrue($obj->Initialize(),
            'Returns true');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_App_baseMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\SWIFT_App_baseMock');
    }
}

class SWIFT_App_baseMock extends SWIFT_App_base
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct('base');
    }
}

