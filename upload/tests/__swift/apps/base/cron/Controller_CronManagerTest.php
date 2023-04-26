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

namespace Base\Cron;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_CronManagerTest
 * @group base
 * @group base-cron
 */
class Controller_CronManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Cron\Controller_CronManager', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExecuteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->expectOutputRegex('/.*GIF.*/i');

        $this->assertTrue($obj->Execute(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Execute');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_CronManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Cron\Controller_CronManagerMock');
    }
}

class Controller_CronManagerMock extends Controller_CronManager
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

