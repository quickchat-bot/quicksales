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

namespace Tickets;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class App_ticketsTest
 * @group tickets
 * @group tickets-config
 */
class App_ticketsTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testInitializeReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->doInitialize = true;
        $this->assertTrue($obj->Initialize());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_App_ticketsMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\SWIFT_App_ticketsMock');
    }
}

class SWIFT_App_ticketsMock extends SWIFT_App_tickets
{
    public $doInitialize = false;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct(APP_TICKETS);
    }

    public function Initialize()
    {
        if ($this->doInitialize) {
            parent::Initialize();
        }

        // override
        return true;
    }
}

