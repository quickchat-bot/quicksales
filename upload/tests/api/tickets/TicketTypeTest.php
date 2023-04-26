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
use Tests\Api\BaseApiTestCase;
use Tickets\Models\Type\SWIFT_TicketType;

/**
 * Class TicketTypeTest
 * @group tickets
 * @group tickettype
 */
class TicketTypeTest extends BaseApiTestCase
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
            \Colors::errlny('Creating TicketType "Custom"...');
            static::$_id = SWIFT_TicketType::Create('Custom', 'icon', 1, SWIFT_PUBLIC, self::$_departmentId);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create TicketType: ' . $ex->getMessage());
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            try {
                SWIFT_TicketType::DeleteList([static::$_id]); // cleanup
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
        $response = $this->getResponse('/Tickets/TicketType');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['tickettype']['title'])) {
            $list = array_values(array_filter($list['tickettype'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['tickettype']];
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
        $response = $this->getResponse('/Tickets/TicketType/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['tickettype']['title']);
    }
}
