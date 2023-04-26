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

namespace Tickets\Models\SLA;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class SLAScheduleTest
* @group tickets
*/
class SLAScheduleTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $this->getMockServices();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\SWIFT_Database $mockDb */
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'slascheduleid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\SLA\SWIFT_SLAScheduleMock');
        $this->assertInstanceOf('Tickets\Models\SLA\SWIFT_SLASchedule', $obj);
    }
}

class SWIFT_SLAScheduleMock extends SWIFT_SLASchedule
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct(1);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

