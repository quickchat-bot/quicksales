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

namespace Tickets\Models\TimeTrack;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketTimeTrackNoteTest
* @group tickets
*/
class TicketTimeTrackNoteTest extends \SWIFT_TestCase
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
            'tickettimetracknoteid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\TimeTrack\SWIFT_TicketTimeTrackNoteMock');
        $this->assertInstanceOf('Tickets\Models\TimeTrack\SWIFT_TicketTimeTrackNote', $obj);
    }
}

class SWIFT_TicketTimeTrackNoteMock extends SWIFT_TicketTimeTrackNote
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

