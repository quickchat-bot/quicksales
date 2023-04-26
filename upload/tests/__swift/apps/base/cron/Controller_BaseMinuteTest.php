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

namespace Base\Cron;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_BaseMinuteTest
 * @group base
 * @group base-cron
 */
class Controller_BaseMinuteTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Cron\Controller_BaseMinute', $obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_BaseMinuteMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Cron\Controller_BaseMinuteMock');
    }
}

class Controller_BaseMinuteMock extends Controller_BaseMinute
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

