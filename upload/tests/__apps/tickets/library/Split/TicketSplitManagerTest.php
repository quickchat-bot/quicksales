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

namespace Tickets\Library\Split;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class TicketSplitManagerTest
 * @group tickets
 */
class TicketSplitManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Library\Split\SWIFT_TicketSplitManager', $obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketSplitManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\Split\SWIFT_TicketSplitManagerMock');
    }
}

class SWIFT_TicketSplitManagerMock extends SWIFT_TicketSplitManager
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

