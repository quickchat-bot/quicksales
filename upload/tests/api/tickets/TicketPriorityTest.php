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

namespace Tests\Api\Tickets;

use Base\Models\Department\SWIFT_Department;
use Tests\Api\BaseApiTestCase;
use Tickets\Models\Priority\SWIFT_TicketPriority;

/**
 * Class TicketPriorityTest
 * @group tickets
 * @group ticketpriority
 */
class TicketPriorityTest extends BaseApiTestCase
{
    private static $_id;
    private static $_departmentId;

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
            \Colors::errlny('Creating TicketPriority "Custom"...');
            static::$_id = SWIFT_TicketPriority::Create('Custom', 1,
                SWIFT_PUBLIC, 'black', 'white',
                false);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create TicketPriority: ' . $ex->getMessage());
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            try {
                SWIFT_TicketPriority::DeleteList([static::$_id]); // cleanup
            } catch (\Exception $ex) {
            }
        }

        if (isset(static::$_departmentId)) {
            \Colors::errlny('Deleting Department "TestDepartment"...');
            SWIFT_Department::DeleteList([static::$_departmentId]);
        }
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/Tickets/TicketPriority');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['ticketpriority']['title'])) {
            $list = array_values(array_filter($list['ticketpriority'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['ticketpriority']];
        }
        $this->assertEquals('Custom', $list[0]['title']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/Tickets/TicketPriority/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['ticketpriority']['title']);
    }
}
