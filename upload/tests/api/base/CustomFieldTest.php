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

namespace Tests\Api\Base;

use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\Department\SWIFT_Department;
use Tests\Api\BaseApiTestCase;

/**
 * Class CustomFieldTest
 * @group base
 * @group customfield
 */
class CustomFieldTest extends BaseApiTestCase
{
    private static $_id;
    private static $_groupId;
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
            \Colors::errlny('Creating CustomFieldGroup "TestGroup"...');
            static::$_groupId = SWIFT_CustomFieldGroup::Create('TestGroup', SWIFT_CustomFieldGroup::GROUP_USERTICKET, 0,
                [static::$_departmentId], [], []);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create CustomFieldGroup');
        }

        try {
            \Colors::errlny('Creating CustomField "TestField"...');
            $_fieldOptionsContainer = [
                1 => ['TestValue1', '1', false, []],
                2 => ['TestValue2', '2', false, []],
            ];
            /** @var SWIFT_CustomField $_SWIFT_CustomFieldObject */
            $_SWIFT_CustomFieldObject = SWIFT_CustomField::Create(static::$_groupId, SWIFT_CustomField::TYPE_CHECKBOX,
                'TestField', 'TestField', 'TestField', '',
                0, 0, 0, 0,
                '', $_fieldOptionsContainer);
            static::$_id = $_SWIFT_CustomFieldObject->GetID();
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create CustomField');
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            \Colors::errlny('Deleting CustomField "TestField"...');
            SWIFT_CustomField::DeleteList([static::$_id]);
        }

        if (isset(static::$_groupId)) {
            \Colors::errlny('Deleting CustomFieldGroup "TestGroup"...');
            SWIFT_CustomFieldGroup::DeleteList([static::$_groupId]);
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
        if (isset(static::$_id)) {
            $response = $this->getResponse('/Base/CustomField');
            $list = $this->getArrayFromResponse($response);
            $id = static::$_id;
            if (!isset($list['customfield']['@attributes']['title'])) {
                $list = array_values(array_filter($list['customfield']['@attributes'], function ($ug) use ($id) {
                    return $ug['customfieldid'] === $id;
                }));
            } else {
                $list = [$list['customfield']['@attributes']];
            }
            $this->assertEquals('TestField', $list[0]['title']);
        } else {
            $this->fail('CustomField was not created');
        }
    }

    /**
     * Test GET ListOptions from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetListOptions(): void
    {
        $response = $this->getResponse('/Base/CustomField/ListOptions/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('TestValue1', $list['option'][0]['@attributes']['optionvalue']);
    }
}
