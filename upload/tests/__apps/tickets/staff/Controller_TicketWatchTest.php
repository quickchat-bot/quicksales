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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_TicketWatchTest extends \SWIFT_TestCase
{
    public function testWatchThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Watch', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testWatchReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
            'subject' => 'subject',
            'fullname' => 'fullname',
            'ticketmaskid' => '0',
        ]);

        \SWIFT::GetInstance()->Database->Record = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
            'subject' => 'subject',
            'fullname' => 'fullname',
            'ticketmaskid' => '0',
        ];

        $this->assertTrue($obj->Watch(1),
            'Returns true with staff_tcanupdateticket = 1');

        $this->assertFalse($obj->Watch(1),
            'Returns true with staff_tcanupdateticket = 0');

        $this->assertClassNotLoaded($obj, 'Watch', 1);
    }

    public function testUnWatchThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'UnWatch', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUnWatchReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
            'subject' => 'subject',
            'fullname' => 'fullname',
            'ticketmaskid' => '0',
        ]);

        \SWIFT::GetInstance()->Database->Record = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketstatusid' => 1,
            'subject' => 'subject',
            'fullname' => 'fullname',
            'ticketmaskid' => '0',
        ];

        $this->assertTrue($obj->UnWatch(1),
            'Returns true with staff_tcanupdateticket = 1');

        $this->assertFalse($obj->UnWatch(1),
            'Returns true with staff_tcanupdateticket = 0');

        $this->assertClassNotLoaded($obj, 'UnWatch', 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketMock
     */
    private function getMocked(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Staff\View_Ticket')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Staff\Controller_TicketMock', array_merge([
            'View' => $view,
        ], $services));
    }
}
