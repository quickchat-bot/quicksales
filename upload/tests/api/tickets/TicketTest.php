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

namespace Tests\Api\Tickets;

use Base\Models\Department\SWIFT_Department;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use Tests\Api\BaseApiTestCase;
use Tickets\Models\Priority\SWIFT_TicketPriority;
use Tickets\Models\Status\SWIFT_TicketStatus;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Type\SWIFT_TicketType;

/**
 * Class TicketTest
 * @group tickets
 * @group ticket
 */
class TicketTest extends BaseApiTestCase
{
    private static $_id;
    private static $_staffId;
    private static $_staffGroupId;
    private static $_departmentId;
    private static $_statusId;
    private static $_priorityId;
    private static $_typeId;

    public static function setUpBeforeClass()
    {
        try {
            \Colors::errlny('Creating Department "TestDepartment"...');
            /** @var SWIFT_Department $_SWIFT_DepartmentObject */
            $_SWIFT_DepartmentObject = SWIFT_Department::Insert('TestDepartment', APP_TICKETS,
                SWIFT_Department::DEPARTMENT_PUBLIC, 0, 0, 0, []);
            static::$_departmentId = $_SWIFT_DepartmentObject->GetID();
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create Department');
        }

        try {
            \Colors::errlny('Creating StaffGroup "TestGroup"...');
            /** @var SWIFT_StaffGroup $_SWIFT_StaffGroupObject */
            $_SWIFT_StaffGroupObject = SWIFT_StaffGroup::Insert('TestGroup', false);
            static::$_staffGroupId = $_SWIFT_StaffGroupObject->GetID();
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create StaffGroup: ' . $ex->getMessage());
        }

        try {
            \Colors::errlny('Creating Staff "Test Staff"...');
            /** @var SWIFT_Staff $_SWIFT_StaffObject */
            $_SWIFT_StaffObject = SWIFT_Staff::Create('Test', 'Staff', '',
                'teststaff' . static::$_staffGroupId, 'password', self::$_staffGroupId, 'teststaff@mail.com', '',
                '');
            static::$_staffId = $_SWIFT_StaffObject->GetID();
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create Staff: ' . $ex->getMessage());
        }

        try {
            \Colors::errlny('Creating TicketStatus "Custom"...');
            static::$_statusId = SWIFT_TicketStatus::Create('Custom', 1, true, 1,
                'black', 'white', static::$_departmentId, SWIFT_PUBLIC,
                0, 'icon.gif', false, false, false);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create TicketStatus: ' . $ex->getMessage());
        }

        try {
            \Colors::errlny('Creating TicketPriority "Custom"...');
            static::$_priorityId = SWIFT_TicketPriority::Create('Custom', 1,
                SWIFT_PUBLIC, 'black', 'white', false);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create TicketPriority: ' . $ex->getMessage());
        }

        try {
            \Colors::errlny('Creating TicketType "Custom"...');
            static::$_typeId = SWIFT_TicketType::Create('Custom', 'icon', 1,
                SWIFT_PUBLIC, static::$_departmentId);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create TicketType: ' . $ex->getMessage());
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            try {
                SWIFT_Ticket::DeleteList([static::$_id]); // cleanup
            } catch (\Exception $ex) {
            }
        }

        if (isset(static::$_typeId)) {
            try {
                \Colors::errlny('Deleting TicketType "Custom"...');
                SWIFT_TicketType::DeleteList([static::$_typeId]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete TicketType: ' . $ex->getMessage());
            }
        }

        if (isset(static::$_priorityId)) {
            try {
                \Colors::errlny('Deleting TicketPriority "Custom"...');
                SWIFT_TicketPriority::DeleteList([static::$_priorityId]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete TicketPriority: ' . $ex->getMessage());
            }
        }

        if (isset(static::$_statusId)) {
            try {
                \Colors::errlny('Deleting TicketStatus "Custom"...');
                SWIFT_TicketStatus::DeleteList([static::$_statusId]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete TicketStatus: ' . $ex->getMessage());
            }
        }

        if (isset(static::$_staffId)) {
            try {
                \Colors::errlny('Deleting Staff "Test Staff"...');
                SWIFT_Staff::DeleteList([static::$_staffId]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete Staff: ' . $ex->getMessage());
            }
        }

        if (isset(static::$_staffGroupId)) {
            \Colors::errlny('Deleting StaffGroup "TestGroup"...');
            SWIFT_StaffGroup::DeleteList([static::$_staffGroupId]);
        }

        if (isset(static::$_departmentId)) {
            \Colors::errlny('Deleting Department "TestDepartment"...');
            SWIFT_Department::DeleteList([static::$_departmentId]);
        }
    }

    /**
     * Test POST endpoint
     * ** THIS SHOULD BE THE FIRST TEST IN ORDER TO TEST CRUD **
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPost(): void
    {
        $response = $this->getResponse('/Tickets/Ticket', 'POST', [
            'subject' => 'Custom',
            'fullname' => 'fullname',
            'email' => 'testuser@mail.com',
            'contents' => 'Test Contents',
            'departmentid' => static::$_departmentId,
            'ticketstatusid' => static::$_statusId,
            'ticketpriorityid' => static::$_priorityId,
            'tickettypeid' => static::$_typeId,
            'autouserid' => 0,
            'ignoreautoresponder' => 1,
            'staffid' => static::$_staffId,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['ticket']['subject']);
        static::$_id = $list['ticket']['@attributes']['id'];
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/Tickets/Ticket/ListAll/' . static::$_departmentId . '/' . self::$_statusId);
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['ticket']['subject'])) {
            $list = array_values(array_filter($list['ticket'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['ticket']];
        }
        $this->assertEquals('Custom', $list[0]['subject']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/Tickets/Ticket/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['ticket']['subject']);
    }

    /**
     * Test PUT endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testPut(): void
    {
        $response = $this->getResponse('/Tickets/Ticket/' . static::$_id, 'PUT', [
            'subject' => 'NewCustom',
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('NewCustom', $list['ticket']['subject']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPut
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/Tickets/Ticket/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
