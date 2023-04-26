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

namespace Tickets\Models\Filter;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketFilterTest
* @group tickets
*/
class TicketFilterTest extends \SWIFT_TestCase
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
            'ticketfilterid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'ticketfilterid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Filter\SWIFT_TicketFilterMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\Filter\SWIFT_TicketFilter', $obj);
    }
}

class SWIFT_TicketFilterMock extends SWIFT_TicketFilter
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

