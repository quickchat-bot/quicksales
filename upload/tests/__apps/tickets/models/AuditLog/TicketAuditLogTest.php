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

namespace Tickets\Models\AuditLog;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketAuditLogTest
* @group tickets
*/
class TicketAuditLogTest extends \SWIFT_TestCase
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
            'ticketauditlogid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\AuditLog\SWIFT_TicketAuditLogMock');
        $this->assertInstanceOf('Tickets\Models\AuditLog\SWIFT_TicketAuditLog', $obj);
    }
}

class SWIFT_TicketAuditLogMock extends SWIFT_TicketAuditLog
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

