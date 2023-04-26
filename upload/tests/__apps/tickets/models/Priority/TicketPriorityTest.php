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

namespace Tickets\Models\Priority;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketPriorityTest
* @group tickets
*/
class TicketPriorityTest extends \SWIFT_TestCase
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
            'priorityid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Priority\SWIFT_TicketPriorityMock');
        $this->assertInstanceOf('Tickets\Models\Priority\SWIFT_TicketPriority', $obj);
    }
}

class SWIFT_TicketPriorityMock extends SWIFT_TicketPriority
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

