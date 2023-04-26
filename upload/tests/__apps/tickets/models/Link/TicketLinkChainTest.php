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

namespace Tickets\Models\Link;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketLinkChainTest
* @group tickets
*/
class TicketLinkChainTest extends \SWIFT_TestCase
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
            'ticketlinkchainid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Link\SWIFT_TicketLinkChainMock');
        $this->assertInstanceOf('Tickets\Models\Link\SWIFT_TicketLinkChain', $obj);
    }
}

class SWIFT_TicketLinkChainMock extends SWIFT_TicketLinkChain
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

