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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_TicketPostTest extends \SWIFT_TestCase
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
    ];

    public function testDeletePostThrowsInvalidDataException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->arr);

        $this->assertInvalidData($obj, 'DeletePost', '1', 1);
    }

    public function testDeletePostThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'DeletePost', 'none', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeletePostReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->arr);

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertFalse($obj->DeletePost(1, 1),
            'Returns false without access');

        \SWIFT::GetInstance()->Staff->method('GetIsClassLoaded')->willReturn(true);
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);
        $this->assertTrue($obj->DeletePost(1, 1));

        $this->assertClassNotLoaded($obj, 'DeletePost', 1, 1);
    }

    public function testEditPostThrowsInvalidDataException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->arr);

        $this->assertInvalidData($obj, 'EditPost', '1', 1);
    }

    public function testEditPostThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'EditPost', 'none', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditPostReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->arr);

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertFalse($obj->EditPost(1, 1),
            'Returns false without access');

        \SWIFT::GetInstance()->Staff->method('GetIsClassLoaded')->willReturn(true);
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);
        $this->assertTrue($obj->EditPost(1, 1));

        $this->assertClassNotLoaded($obj, 'EditPost', 1, 1);
    }

    public function testEditPostSubmitThrowsInvalidDataException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->arr);

        $this->assertInvalidData($obj, 'EditPostSubmit', '1', 1);
    }

    public function testEditPostSubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'EditPostSubmit', 'none', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditPostSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->arr);

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertFalse($obj->EditPostSubmit(1, 1),
            'Returns false without access');

        \SWIFT::GetInstance()->Staff->method('GetIsClassLoaded')->willReturn(true);
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);
        $this->assertTrue($obj->EditPostSubmit(1, 1));

        $this->assertClassNotLoaded($obj, 'EditPostSubmit', 1, 1);
    }

    public function testRatingPostThrowsInvalidDataException()
    {
        $obj = $this->getMocked();
        $_POST['ratingid'] = 1;
        $_POST['ratingvalue'] = 1;
        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $staff;
        $this->assertInvalidData($obj, 'RatingPost', 'none', 1);
    }

    public function testRatingPostThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'RatingPost', 0, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRatingPostReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->RatingPost(1, 1),
            'Returns false without POST values');

        $_POST['ratingid'] = 1;
        $_POST['ratingvalue'] = 1;

        $this->assertFalse($obj->RatingPost(1, 1),
            'Returns false without permission');

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetAssignedDepartments')->willReturn([1]);
        \SWIFT::GetInstance()->Staff = $staff;

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($this->arr);

        $this->expectOutputRegex('/msgnoperm/');
        $this->assertFalse($obj->RatingPost(1, 1),
            'Returns false without permission');

        \SWIFT::GetInstance()->Staff->method('GetIsClassLoaded')->willReturn(true);
        $this->assertTrue($obj->RatingPost(1, 1));

        $this->assertClassNotLoaded($obj, 'RatingPost', 1, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCheckPostEmailContainerReturnsTrue() {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_CheckPOSTEmailContainer');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 'subject'));

        $_POST['taginput_subject'] = 'subject';
        $this->assertFalse($method->invoke($obj, 'subject'));

        $_POST['taginput_email'] = 'me@mail.com';
        $this->assertTrue($method->invoke($obj, 'email'));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 'subject');
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
