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

use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\Department\SWIFT_Department;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use SWIFT;
use Tests\Api\BaseApiTestCase;
use Tickets\Library\Ticket\SWIFT_TicketManager;
use Tickets\Models\Priority\SWIFT_TicketPriority;
use Tickets\Models\Status\SWIFT_TicketStatus;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Type\SWIFT_TicketType;

/**
 * Class TicketCustomFieldTest
 * @group tickets
 * @group ticketcustomfield
 */
class TicketCustomFieldTest extends BaseApiTestCase
{
    private static $_fieldId;
    private static $_groupId;
    private static $_departmentId;
    private static $_staffGroupId;
    private static $_staffId;
    private static $_statusId;
    private static $_priorityId;
    private static $_typeId;
    private static $_ticketId;

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
            \Colors::errlny('Creating CustomFieldGroup "TestGroup"...');
            static::$_groupId = SWIFT_CustomFieldGroup::Create('TestGroup', SWIFT_CustomFieldGroup::GROUP_STAFFTICKET, 0,
                [static::$_departmentId], [], []);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create CustomFieldGroup');
        }

        try {
            \Colors::errlny('Creating CustomField "TestField"...');
            /** @var SWIFT_CustomField $_SWIFT_CustomFieldObject */
            $_SWIFT_CustomFieldObject = SWIFT_CustomField::Create(static::$_groupId, SWIFT_CustomField::TYPE_TEXT,
                'TestField', 'TestField', 'TestField', 'Default Value',
                1, 0, 1, 1,
                '', []);
            static::$_fieldId = $_SWIFT_CustomFieldObject->GetID();
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create CustomField');
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

        try {
            \Colors::errlny('Creating Ticket "Test Ticket"...');
            SWIFT_Ticket::LoadLanguageTable();
            /** @var SWIFT_Ticket $_SWIFT_TicketObject */
            $_SWIFT_TicketObject = SWIFT_Ticket::Create('Custom Subject', 'fullname', 'testemail@mail.com', 'ticket contents',
                static::$_staffId, self::$_departmentId, self::$_statusId, self::$_priorityId, self::$_typeId,
                0, static::$_staffId, SWIFT_Ticket::TYPE_DEFAULT, SWIFT_Ticket::CREATOR_STAFF, SWIFT_Ticket::CREATIONMODE_SITEBADGE, '', 0, false);
            static::$_ticketId = $_SWIFT_TicketObject->GetID();
            $_SWIFT_TicketObject->SetTemplateGroup(SWIFT::GetInstance()->TemplateGroup->GetTemplateGroupID());
            $_SWIFT_TicketObject->ProcessUpdatePool();
            SWIFT_TicketManager::RebuildCache();
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create Ticket: ' . $ex->getMessage());
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_fieldId)) {
            \Colors::errlny('Deleting CustomField "TestField"...');
            SWIFT_CustomField::DeleteList([static::$_fieldId]);
        }

        if (isset(static::$_groupId)) {
            \Colors::errlny('Deleting CustomFieldGroup "TestGroup"...');
            SWIFT_CustomFieldGroup::DeleteList([static::$_groupId]);
        }

        if (isset(static::$_ticketId)) {
            try {
                \Colors::errlny('Deleting Ticket "Test Ticket"...');
                SWIFT_Ticket::DeleteList([static::$_ticketId]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete Ticket: ' . $ex->getMessage());
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
        $response = $this->getResponse('/Tickets/TicketCustomField/' . static::$_ticketId, 'POST', [
            'TestField' => 'New Custom value',
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('New Custom value', $list['group']['field']);
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/Tickets/TicketCustomField/' . static::$_ticketId);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('New Custom value', $list['group']['field']);
    }
}
