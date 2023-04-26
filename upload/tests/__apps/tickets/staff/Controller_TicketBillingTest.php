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
class Controller_TicketBillingTest extends \SWIFT_TestCase
{
    private $arr = [
        'ticketid' => 1,
        'iswatched' => 0,
        'lastpostid' => 0,
        'departmentid' => 1,
        'userid' => 1,
        'ticketpostid' => 1,
        'linktype' => 1,
        'trasholddepartmentid' => 0,
        'ticketstatusid' => 1,
        'ownerstaffid' => 0,
        'priorityid' => 1,
        'tickettypeid' => 1,
        'totalreplies' => 0,
        'lastactivity' => 0,
        'fullname' => 'fullname',
        'email' => 'email',
        'ratingid' => 1,
        'iseditable' => 1,
        'isclientonly' => 1,
        'staffid' => 1,
        'tickettimetrackid' => 1,
        'tickettimetracknoteid' => 1,
        'timeworked' => 0,
        'timebilled' => 0,
        'wasreopened' => 0,
        'bayescategoryid' => 0,
        'duetime' => 1,
        'resolutionduedateline' => 1,
        'userorganizationid' => 0,
        'charset' => 'UTF-8',
        'subject' => 'subject',
        'searchstoreid' => 1,
    ];

    public function testBillingThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Billing', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testBillingReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([], [1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->Billing(1),
            'Returns true with staff_tcanviewtickets = 0');

        $this->assertTrue($obj->Billing(1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->assertClassNotLoaded($obj, 'Billing', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testBillingUserReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
        ]);

        $this->assertTrue($obj->BillingUser(1),
            'Returns true with staff_tcanviewtickets = 1');

        $this->assertClassNotLoaded($obj, 'BillingUser', 1);
    }

    public function testBillingSubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'BillingSubmit', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testBillingSubmitReturnsTrue()
    {
        $mgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldManager')
            ->disableOriginalConstructor()
            ->getMock();
        $obj = $this->getMocked([
            'CustomFieldManager' => $mgr,
        ]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->arr);

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $staff;

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->BillingSubmit(1),
            'Returns false without access');

        \SWIFT::GetInstance()->Staff->method('GetIsClassLoaded')->willReturn(true);
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $_POST['billingtimeworked'] = 1;
        $_POST['billingtimebillable'] = 1;
        $_POST['notecolor_billingnotes'] = 1;
        $_POST['billingnotes'] = 1;
        $this->assertTrue($obj->BillingSubmit(1));

        $_POST['billingworkerstaffid'] = 1;
        $this->assertTrue($obj->BillingSubmit(1));

        $_POST['billworkdate'] = '1:00';
        $_POST['billworkdate_hour'] = '1';
        $_POST['billworkdate_minute'] = '0';
        $_POST['billdate'] = '1:00';
        $_POST['billdate_hour'] = '1';
        $_POST['billdate_minute'] = '1';
        $this->assertTrue($obj->BillingSubmit(1));

        $this->assertClassNotLoaded($obj, 'BillingSubmit', 1);
    }

    public function testEditBillingThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'EditBilling', '', 1);
    }

    public function testEditBillingThrowsInvalidException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'EditBilling', 'none', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditBillingUserReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([], [1]);
        $_SWIFT->Database->method('QueryFetch')->willReturn($this->arr);

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->EditBilling(1, 1),
            'Returns false without access');

        $this->assertFalse($obj->EditBilling(1, 1),
            'Returns false with staff_tcanupdatebilling = 0');

        $_SWIFT->Staff->method('GetPermission')->willReturn(1);

        $this->assertTrue($obj->EditBilling(1, 1),
            'Returns true with staff_tcanupdatebilling = 1');

        $this->assertClassNotLoaded($obj, 'EditBilling', 1, 1);
    }


    public function testEditBillingSubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'EditBillingSubmit', '', 1);
    }

    public function testEditBillingSubmitThrowsInvalidDataException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'EditBillingSubmit', 'none', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditBillingSubmitReturnsTrue()
    {
        $mgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldManager')
            ->disableOriginalConstructor()
            ->getMock();
        $obj = $this->getMocked([
            'CustomFieldManager' => $mgr,
        ]);

        // advance permission
        \SWIFT::GetInstance()->Staff->GetPermission('none');
        $this->assertFalse($obj->EditBillingSubmit(1, 1),
            'Returns false without access');

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->arr);

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $staff;

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->EditBillingSubmit(1, 1),
            'Returns false without access');

        \SWIFT::GetInstance()->Staff->method('GetIsClassLoaded')->willReturn(true);
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $_POST['ebillingtimeworked'] = 1;
        $_POST['ebillingtimebillable'] = 1;
        $_POST['notecolor_ebillingnotes'] = 1;
        $_POST['ebillingnotes'] = 1;
        $this->assertTrue($obj->EditBillingSubmit(1, 1));

        $_POST['ebillingtimeworked'] = 1;
        $_POST['ebillingtimebillable'] = 1;
        $_POST['notecolor_ebillingnotes'] = 1;
        $_POST['ebillingnotes'] = 1;
        $_POST['ebillingworkerstaffid'] = 1;
        $this->assertTrue($obj->EditBillingSubmit(1, 1));

        $_POST['ebillingtimeworked'] = 1;
        $_POST['ebillingtimebillable'] = 1;
        $_POST['notecolor_ebillingnotes'] = 1;
        $_POST['ebillingnotes'] = 1;
        $_POST['ebillingworkerstaffid'] = 1;
        $_POST['ebillworkdate'] = '1:00';
        $_POST['ebillworkdate_hour'] = '1';
        $_POST['ebillworkdate_minute'] = '0';
        $_POST['ebilldate'] = '1:00';
        $_POST['ebilldate_hour'] = '1';
        $_POST['ebilldate_minute'] = '1';
        $this->assertTrue($obj->EditBillingSubmit(1, 1));

        $this->assertClassNotLoaded($obj, 'EditBillingSubmit', 1, 1);
    }

    public function testDeleteBillingThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'DeleteBilling', '', 1);
    }

    public function testDeleteBillingThrowsInvalidException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'DeleteBilling', 'none', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteBillingUserReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([], [1]);
        $_SWIFT->Database->method('QueryFetch')->willReturn($this->arr);

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->DeleteBilling(1, 1),
            'Returns false without access');

        $this->assertFalse($obj->DeleteBilling(1, 1),
            'Returns false with staff_tcanupdatebilling = 0');

        $_SWIFT->Staff->method('GetPermission')->willReturn(1);

        $this->assertTrue($obj->DeleteBilling(1, 1),
            'Returns true with staff_tcanupdatebilling = 1');

        $this->assertClassNotLoaded($obj, 'DeleteBilling', 1, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetBillingTimeWorks() {
        $obj = $this->getMocked();

        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('GetBillingTime');
        $method->setAccessible(true);
        $this->assertEquals(0, $method->invoke($obj, '00:00'));
        $this->assertEquals(0, $method->invoke($obj, ':01:00'));
        $this->assertEquals(0, $method->invoke($obj, '01:01:01'));
        $this->assertEquals(60, $method->invoke($obj, '00:01'));
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
