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

namespace Tickets\Models\TimeTrack;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class TicketTimeTrackTest
 * @group tickets
 */
class TicketTimeTrackTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Models\TimeTrack\SWIFT_TicketTimeTrack', $obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketTimeTrackMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Models\TimeTrack\SWIFT_TicketTimeTrackMock');
    }
}

class SWIFT_TicketTimeTrackMock extends SWIFT_TicketTimeTrack
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

