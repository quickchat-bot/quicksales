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

namespace Tickets\Models\Ticket;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketForwardTest
* @group tickets
*/
class TicketForwardTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();

        $this->assertInstanceOf('Tickets\Models\Ticket\SWIFT_TicketForward', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateFromEmailListReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertEmpty($obj::CreateFromEmailList(0, []),
            'Returns empty array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveEmailListOnTicketPostReturnsArray()
    {
        $obj = $this->getMocked();

        $_post = $this->getMockBuilder(SWIFT_TicketPost::class)
            ->disableOriginalConstructor()
            ->getMock();
        $_post->method('GetIsClassLoaded')->willReturnOnConsecutiveCalls([true, false]);

        $this->assertCount(1, $obj::RetrieveEmailListOnTicketPost($_post),
            'Returns empty array');

        $this->expectException(SWIFT_Exception::class);
        $obj::RetrieveEmailListOnTicketPost($_post);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketForwardMock
     */
    private function getMocked()
    {
        $this->getMockServices();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\SWIFT_Database $mockDb */
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'ticketforwardid' => 1,
        ]);
        return $this->getMockObject('Tickets\Models\Ticket\SWIFT_TicketForwardMock');
    }
}

class SWIFT_TicketForwardMock extends SWIFT_TicketForward
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
