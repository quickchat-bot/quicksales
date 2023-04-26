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

namespace Tickets\Library\StaffAPI;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class TicketStaffAPIManagerTest
 * @group tickets
 */
class TicketStaffAPIManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Library\StaffAPI\SWIFT_TicketStaffAPIManager', $obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketStaffAPIManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\StaffAPI\SWIFT_TicketStaffAPIManagerMock');
    }
}

class SWIFT_TicketStaffAPIManagerMock extends SWIFT_TicketStaffAPIManager
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

