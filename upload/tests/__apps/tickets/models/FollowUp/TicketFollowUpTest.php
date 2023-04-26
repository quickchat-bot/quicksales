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

namespace Tickets\Models\FollowUp;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketFollowUpTest
* @group tickets
*/
class TicketFollowUpTest extends \SWIFT_TestCase
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
            'ticketfollowupid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'ticketfollowupid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\FollowUp\SWIFT_TicketFollowUpMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\FollowUp\SWIFT_TicketFollowUp', $obj);
    }
}

class SWIFT_TicketFollowUpMock extends SWIFT_TicketFollowUp
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

