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
class Controller_TicketFollowUpTest extends \SWIFT_TestCase
{
    public function testFollowUpThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'FollowUp', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testFollowUpReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
        ]);

        $this->assertTrue($obj->FollowUp(1, 'inbox', -1, -1, -1, 0, 'fr'),
            'Returns true with staff_tcanfollowup = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->FollowUp(1),
            'Returns true with staff_tcanfollowup = 0');

        $this->assertClassNotLoaded($obj, 'FollowUp', 1);
    }

    public function testFollowUpSubmitThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'FollowUpSubmit', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testFollowUpSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
            'isresolved' => 1,
            'linktype' => 1,
        ]);

        $_POST['fudochangeproperties'] = '2';
        $_POST['fudepartmentid'] = '1';

        $this->assertTrue($obj->FollowUpSubmit(1),
            'Returns true with staff_tcanfollowup = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $staff->method('GetPermission')->willReturnOnConsecutiveCalls('0', '1', '0');
        $staff->method('GetAssignedDepartments')->willReturn([1]);
        $staff->method('GetIsClassLoaded')->willReturn(true);

        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertFalse($obj->FollowUpSubmit(1),
            'Returns false without access');

        $_POST['fudepartmentid'] = '2';

        $this->assertFalse($obj->FollowUpSubmit(1),
            'Returns false with staff_tcanchangeunassigneddepartment = 0');

        $this->assertClassNotLoaded($obj, 'FollowUpSubmit', 1);
    }

    public function testDeleteFollowUpThrowsInvalidDataException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'DeleteFollowUp', '0', 1);
    }

    public function testDeleteFollowUpThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'DeleteFollowUp', 'none', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteFollowUpReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
            'ticketfollowupid' => 1,
            'linktype' => 1,
            'trasholddepartmentid' => 0,
            'ticketstatusid' => 1,
            'ownerstaffid' => 0,
            'priorityid' => 1,
            'tickettypeid' => 1,
            'totalreplies' => 0,
            'lastactivity' => 0,
        ]);

        $this->assertTrue($obj->DeleteFollowUp(1, 1),
            'Returns true with staff_tcaninsertticketnote = 1');

        $this->assertFalse($obj->DeleteFollowUp(1, 1),
            'Returns false with staff_tcaninsertticketnote = 0');

        $this->expectOutputRegex('/msgnoperm/');

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertFalse($obj->DeleteFollowUp(1, 1),
            'Returns false without access');

        $this->assertClassNotLoaded($obj, 'DeleteFollowUp', 1, 1);
    }

    public function testProcessFollowUpThrowsException()
    {
        $obj = $this->getMocked();
        $mock = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInvalidData($obj, '_ProcessFollowUp', $mock, '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessFollowUpReturnsTrue()
    {
        $obj = $this->getMocked();

        $mock = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetIsClassLoaded')->willReturn(true);

        $mockFo = $this->getMockBuilder('Tickets\Models\FollowUp\SWIFT_TicketFollowUp')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFo->method('GetIsClassLoaded')->willReturn(true);

        $_POST['followuptype'] = 'other';
        $_POST['followupcustomvalue'] = '-1';

        $this->assertFalse($obj->_ProcessFollowUp($mock, ''),
            'Returns false with invalid date');

        $_POST['taginput_to'] = 'me@mail.com';
        $_POST['doforward'] = '1';
        $_POST['followuptype'] = 'custom';
        $_POST['followupcustomvalue'] = '1pm';
        $_POST['followupcustomvalue_hour'] = '1';
        $_POST['followupcustomvalue_minute'] = '0';
        $this->assertTrue($obj->_ProcessFollowUp($mock, '', $mockFo),
            'Returns true with valid time');
    }

    /**
     * @dataProvider FollowUpProvider
     * @param $_followUpType
     * @param $_followUpValue
     * @param $expectValue
     * @throws SWIFT_Exception
     */
    public function testReturnFollowUpDateReturnsFalse($_followUpType, $_followUpValue, $expectValue)
    {
        $obj = $this->getMocked();

        $this->assertNotNull($obj::_ReturnFollowUpDate($_followUpType, $_followUpValue));
    }

    public function FollowUpProvider()
    {
        return [
            ['hours', 1, DATENOW + 60 * 60],
            ['days', 1, DATENOW + 60 * 60 * 24],
            ['weeks', 1, DATENOW + 60 * 60 * 24 * 7],
            ['months', 1, DATENOW + 60 * 60 * 24 * 30],
            ['custom', '1pm', strtotime('1pm')],
            ['other', 1, false],
        ];
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
