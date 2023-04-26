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

namespace Tickets\Models\Ticket;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketLinkedTableTest
* @group tickets
*/
class TicketLinkedTableTest extends \SWIFT_TestCase
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
            'ticketlinkedtableid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Ticket\SWIFT_TicketLinkedTableMock');
        $this->assertInstanceOf('Tickets\Models\Ticket\SWIFT_TicketLinkedTable', $obj);
    }
}

class SWIFT_TicketLinkedTableMock extends SWIFT_TicketLinkedTable
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

