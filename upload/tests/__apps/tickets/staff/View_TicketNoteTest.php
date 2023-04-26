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
 * Class View_ViewTest
 * @group tickets
 * @group tickets-staff
 */
class View_TicketNoteTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $_record = [];

    public function testRenderNotesThrowsInvalidException()
    {
        $obj = $this->getMocked();
        $mock = $obj->getTicketMock($this, false);
        $mock2 = $obj->getUserMock($this);

        $this->assertInvalidData($obj, 'RenderNotes', $mock, $mock2);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderNotesReturnsTrue()
    {
        $obj = $this->getMocked();
        $mock = $obj->getTicketMock($this);
        $mock2 = $obj->getUserMock($this);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetPermission')->willReturn('1');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);

        \SWIFT::GetInstance()->Staff = $mockStaff;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$_next++;

            if (static::$_next === 1) {
                static::$_record = [
                    'linktype' => 1,
                    'usernoteid' => 1,
                ];
            }

            if (static::$_next === 2) {
                static::$_record = [
                    'linktype' => 2,
                    'usernoteid' => 2,
                ];
            }

            if (static::$_next === 4) {
                static::$_record = [
                    'forstaffid' => 2,
                    'staffid' => 2,
                ];
            }

            if (static::$_next === 5) {
                static::$_record = [
                    'forstaffid' => 1,
                    'staffid' => 1,
                    'linktype' => 1,
                    'ticketnoteid' => 3,
                ];
            }

            return in_array(static::$_next, [1, 2, 4, 5], true);
        });

        $mockDb->Record = &static::$_record;

        $obj->Database = $mockDb;

        $this->assertContains('ticketnotesactions', $obj->RenderNotes($mock, $mock2));

        $this->assertClassNotLoaded($obj, 'RenderNotes', $mock, $mock2);
    }

    /**
     * @throws SWIFT_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function testRenderNoteFormReturnsTrue()
    {
        $obj = $this->getMocked();

        $mock = $obj->getTicketMock($this);
        $mock2 = $this->getMockBuilder('Tickets\Models\Note\SWIFT_TicketNote')
            ->disableOriginalConstructor()
            ->getMock();
        $mock2->method('GetIsClassLoaded')->willReturn(true);
        $mock3 = $obj->getUserMock($this);

        $this->assertTrue($obj->RenderNoteForm(1, $mock, $mock2, $mock3));
        $this->assertTrue($obj->RenderNoteForm(2, $mock, $mock2, $mock3));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderNoteForm(1, $mock, $mock2, $mock3),
            'Returns false if class is not loaded');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_TicketMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\View_TicketMock', $services);
    }
}
