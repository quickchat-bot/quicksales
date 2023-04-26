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

namespace Tickets\Models\Workflow;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketWorkflowTest
* @group tickets
*/
class TicketWorkflowTest extends \SWIFT_TestCase
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
            'ticketworkflowid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'ticketworkflowid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Workflow\SWIFT_TicketWorkflowMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\Workflow\SWIFT_TicketWorkflow', $obj);
    }
}

class SWIFT_TicketWorkflowMock extends SWIFT_TicketWorkflow
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct($this->Data);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

