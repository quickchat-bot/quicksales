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

namespace Tickets\Models\Merge;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class TicketMergeLogTest
* @group tickets
*/
class TicketMergeLogTest extends \SWIFT_TestCase
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
            'ticketmergelogid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Merge\SWIFT_TicketMergeLogMock');
        $this->assertInstanceOf('Tickets\Models\Merge\SWIFT_TicketMergeLog', $obj);
    }
}

class SWIFT_TicketMergeLogMock extends SWIFT_TicketMergeLog
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

