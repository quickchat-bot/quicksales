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

namespace Tickets\Library\Flag;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class TicketFlagTest
 * @group tickets
 * @group tickets-lib4
 */
class TicketFlagTest extends \SWIFT_TestCase
{
    public function testIsValidFlagTypeReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj::IsValidFlagType(0));
    }

    public function testGetFlagListThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertClassNotLoaded($obj, 'GetFlagList');
    }

    public function testGetFlagContainerThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertClassNotLoaded($obj, 'GetFlagContainer');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketFlagMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\Flag\SWIFT_TicketFlagMock');
    }
}

class SWIFT_TicketFlagMock extends SWIFT_TicketFlag
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

