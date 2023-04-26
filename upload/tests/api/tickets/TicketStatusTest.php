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

namespace Tests\Api\Tickets;

use Base\Models\Department\SWIFT_Department;
use Tests\Api\BaseApiTestCase;
use Tickets\Models\Status\SWIFT_TicketStatus;

/**
 * Class TicketStatusTest
 * @group tickets
 * @group ticketstatus
 */
class TicketStatusTest extends BaseApiTestCase
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
            \Colors::errlny('Creating TicketStatus "Custom"...');
            static::$_id = SWIFT_TicketStatus::Create('Custom', 1, true, 1,
                'black', 'white', static::$_departmentId, SWIFT_PUBLIC,
                0, 'icon.gif', false, false, false);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create TicketStatus: ' . $ex->getMessage());
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            try {
                SWIFT_TicketStatus::DeleteList([static::$_id]); // cleanup
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
        $response = $this->getResponse('/Tickets/TicketStatus');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['ticketstatus']['title'])) {
            $list = array_values(array_filter($list['ticketstatus'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['ticketstatus']];
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
        $response = $this->getResponse('/Tickets/TicketStatus/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['ticketstatus']['title']);
    }
}
