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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_TicketRecurrenceTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRecurrenceReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketrecurrenceid' => 1,
        ]);

        $this->assertTrue($obj->Recurrence(1, 1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->Recurrence(1, 1),
            'Returns true with staff_tcanviewtickets = 0');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPauseOrResumeRecurrenceReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturnCallback(function ($x) {
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'ticketrecurrenceid' => 1,
                'nextrecurrence' => 0,
                'intervaltype' => 1,
                'daily_everyweekday' => 1,
            ];

            if (false !== strpos($x, "ticketrecurrenceid = '2'")) {
                $arr['nextrecurrence'] = 1;
            }

            return $arr;
        });

        $this->assertTrue($obj->PauseOrResumeRecurrence(1, 1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->PauseOrResumeRecurrence(1, 1),
            'Returns true with staff_tcanviewtickets = 0');

        $this->assertTrue($obj->PauseOrResumeRecurrence(2, 2),
            'Returns true with nextrecurrence = 1');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testStopRecurrenceReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketrecurrenceid' => 1,
        ]);

        $this->assertTrue($obj->StopRecurrence(1, 1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->StopRecurrence(1, 1),
            'Returns true with staff_tcanviewtickets = 0');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateRecurrenceReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketrecurrenceid' => 1,
            'intervaltype' => 0,
            'enddateline' => 2,
            'startdateline' => 1,
            'ownerstaffid' => 1,
        ]);

        $_POST['recurrencetype'] = 1;
        $_POST['recurrence_daily_type'] = 'default';
        $this->assertTrue($obj->UpdateRecurrence(1, 1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->UpdateRecurrence(1, 1),
            'Returns false with staff_tcanviewtickets = 0');

        $_POST['recurrencetype'] = -1;
        $this->assertTrue($obj->UpdateRecurrence(1, 1),
            'Returns true with invalid recurrence type');

        $_POST['recurrencetype'] = 0;
        $this->assertTrue($obj->UpdateRecurrence(1, 1),
            'Returns true with no recurrence type');

        $_POST['recurrence_daily_type'] = 'extended';
        $this->assertTrue($obj->UpdateRecurrence(1, 1));

        $_POST['recurrencetype'] = 2;
        $_POST['recurrence_weekly_ismonday'] = 1;
        $_POST['recurrence_weekly_istuesday'] = 1;
        $_POST['recurrence_weekly_iswednesday'] = 1;
        $_POST['recurrence_weekly_isthursday'] = 1;
        $_POST['recurrence_weekly_isfriday'] = 1;
        $_POST['recurrence_weekly_issaturday'] = 1;
        $_POST['recurrence_weekly_issunday'] = 1;
        $this->assertTrue($obj->UpdateRecurrence(1, 1));

        $_POST['recurrencetype'] = 3;
        $_POST['recurrence_monthly_type'] = 'other';
        $_POST['recurrence_monthly_day'] = 1;
        $this->assertTrue($obj->UpdateRecurrence(1, 1));

        $_POST['recurrence_monthly_type'] = 'extended';
        $_POST['recurrence_monthly_extdaystep'] = 1;
        $_POST['recurrence_monthly_extday'] = 1;
        $this->assertTrue($obj->UpdateRecurrence(1, 1));

        $_POST['recurrencetype'] = 4;
        $_POST['recurrence_yearly_type'] = 'other';

        $_POST['recurrence_endtype'] = 3;
        $_POST['recurrence_endcount'] = 1;
        $this->assertTrue($obj->UpdateRecurrence(1, 1));

        $_POST['recurrence_yearly_type'] = 'extended';
        $_POST['recurrence_yearly_extday'] = 1;
        $_POST['recurrence_yearly_extdaystep'] = 1;
        $_POST['recurrence_yearly_extmonth'] = 1;

        $_POST['recurrence_endtype'] = 2;
        $_POST['recurrence_start'] = strtotime('1');
        $_POST['recurrence_enddateline'] = strtotime('2');
        $this->assertTrue($obj->UpdateRecurrence(1, 1));
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
