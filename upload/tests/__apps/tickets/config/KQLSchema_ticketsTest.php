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
 * Class KQLSchema_ticketsTest
 * @group tickets
 * @group tickets-config
 */
class KQLSchema_ticketsTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\SWIFT_KQLSchema_tickets', $obj);
    }

    public function testGetSchemaThrowsException() {
        $obj = $this->getMocked();
        $this->assertClassNotLoaded($obj, 'GetSchema');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_KQLSchema_ticketsMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\SWIFT_KQLSchema_ticketsMock');
    }
}

class SWIFT_KQLSchema_ticketsMock extends SWIFT_KQLSchema_tickets
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

