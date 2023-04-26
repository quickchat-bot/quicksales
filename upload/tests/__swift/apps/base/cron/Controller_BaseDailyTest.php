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
 * Class Controller_BaseDailyTest
 * @group base
 * @group base-cron
 */
class Controller_BaseDailyTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Cron\Controller_BaseDaily', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCleanupReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['cronid' => 1, 'name' => 'Test']);


        $this->assertTrue($obj->Cleanup(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Cleanup');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_BaseDailyMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Cron\Controller_BaseDailyMock');
    }
}

class Controller_BaseDailyMock extends Controller_BaseDaily
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

