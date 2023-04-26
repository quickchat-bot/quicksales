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
class Controller_TicketHistoryTest extends \SWIFT_TestCase
{
    public function testHistoryThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'History', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testHistoryReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        $this->assertTrue($obj->History(1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->History(1),
            'Returns true with staff_tcanviewtickets = 0');

        $this->assertClassNotLoaded($obj, 'History', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testHistoryUserReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'userid' => 1,
        ]);

        $this->assertTrue($obj->HistoryUser(1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->assertClassNotLoaded($obj, 'HistoryUser', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testHistoryEmailsReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->HistoryEmails(base64_encode('email[]=me@email.com')));

        $this->assertClassNotLoaded($obj, 'HistoryEmails', '');
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
