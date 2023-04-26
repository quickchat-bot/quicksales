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
class Controller_TicketNoteTest extends \SWIFT_TestCase
{
    public function testAddNoteThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'AddNote', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testAddNoteReturnsTrue()
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

        $this->assertTrue($obj->AddNote(1),
            'Returns true with staff_tcaninsertticketnote = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->AddNote(1),
            'Returns true with staff_tcaninsertticketnote = 0');

        $this->assertClassNotLoaded($obj, 'AddNote', 1);
    }

    public function testAddNoteSubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'AddNoteSubmit', 'none');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testAddNoteSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
            'userorganizationid' => 0,
            'duetime' => 1,
            'isresolved' => 1,
        ]);

        $_POST['forstaffid'] = 1;
        $_POST['ticketnotes'] = 'notes';
        $_POST['notecolor_ticketnotes'] = 'notes';
        $_POST['notetype'] = 1;

        $this->assertTrue($obj->AddNoteSubmit(1),
            'Returns true with staff_tcaninsertticketnote = 1');

        $this->expectOutputRegex('/msgnoperm/');

        $this->assertFalse($obj->AddNoteSubmit(1),
            'Returns true with staff_tcaninsertticketnote = 0');

        $this->assertClassNotLoaded($obj, 'AddNoteSubmit', 1);
    }

    public function testEditNoteThrowsInvalidDataException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'EditNote', '0', 1);
    }

    public function testEditNoteThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'EditNote', 'none', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditNoteReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
            'ticketnoteid' => 1,
            'linktype' => 1,
        ]);

        $this->assertTrue($obj->EditNote(1, 1),
            'Returns true with staff_tcaninsertticketnote = 1');

        $this->assertFalse($obj->EditNote(1, 1),
            'Returns false with staff_tcaninsertticketnote = 0');

        $this->expectOutputRegex('/msgnoperm/');

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertFalse($obj->EditNote(1, 1),
            'Returns false without access');

        $this->assertClassNotLoaded($obj, 'EditNote', 1, 1);
    }

    public function testEditNoteSubmitThrowsInvalidDataException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'EditNoteSubmit', '0', 1);
    }

    public function testEditNoteSubmitThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'EditNoteSubmit', 'none', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditNoteSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
            'ticketnoteid' => 1,
            'linktype' => 1,
        ]);

        $_POST['ticketnotes'] = 'notes';
        $_POST['notecolor_ticketnotes'] = 'notes';

        $this->assertTrue($obj->EditNoteSubmit(1, 1),
            'Returns true with staff_tcaninsertticketnote = 1');

        $this->assertFalse($obj->EditNoteSubmit(1, 1),
            'Returns false with staff_tcaninsertticketnote = 0');

        $this->expectOutputRegex('/msgnoperm/');

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertFalse($obj->EditNoteSubmit(1, 1),
            'Returns false without access');

        $this->assertClassNotLoaded($obj, 'EditNoteSubmit', 1, 1);
    }

    public function testDeleteNoteThrowsInvalidDataException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'DeleteNote', '0', 1);
    }

    public function testDeleteNoteThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'DeleteNote', 'none', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteNoteReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
            'ticketnoteid' => 1,
            'linktype' => 1,
            'trasholddepartmentid' => 0,
            'ticketstatusid' => 1,
            'ownerstaffid' => 0,
            'priorityid' => 1,
            'tickettypeid' => 1,
            'totalreplies' => 0,
            'lastactivity' => 0,
        ]);

        $this->assertTrue($obj->DeleteNote(1, 1),
            'Returns true with staff_tcaninsertticketnote = 1');

        $this->assertFalse($obj->DeleteNote(1, 1),
            'Returns false with staff_tcaninsertticketnote = 0');

        $this->expectOutputRegex('/msgnoperm/');

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertFalse($obj->DeleteNote(1, 1),
            'Returns false without access');

        $this->assertClassNotLoaded($obj, 'DeleteNote', 1, 1);
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
