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

namespace Tickets\Models\Note;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketNoteTest
* @group tickets
*/
class TicketNoteTest extends \SWIFT_TestCase
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
            'ticketnoteid' => 1,
            'linktype' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Note\SWIFT_TicketNoteMock');
        $this->assertInstanceOf('Tickets\Models\Note\SWIFT_TicketNote', $obj);
    }
}

class SWIFT_TicketNoteMock extends SWIFT_TicketNote
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

