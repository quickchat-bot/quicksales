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

namespace Tests\Api\Base;

use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use Base\Models\Department\SWIFT_Department;
use Tests\Api\BaseApiTestCase;

/**
 * Class CustomFieldGroupTest
 * @group base
 * @group customfieldgroup
 */
class CustomFieldGroupTest extends BaseApiTestCase
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
            \Colors::errlny('Creating CustomFieldGroup "TestGroup"...');
            static::$_id = SWIFT_CustomFieldGroup::Create('TestGroup', SWIFT_CustomFieldGroup::GROUP_USERTICKET, 0,
                [static::$_departmentId], [], []);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create CustomFieldGroup');
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            \Colors::errlny('Deleting CustomFieldGroup "TestGroup"...');
            SWIFT_CustomFieldGroup::DeleteList([static::$_id]);
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
            $response = $this->getResponse('/Base/CustomFieldGroup');
            $list = $this->getArrayFromResponse($response);
            $id = static::$_id;
            if (!isset($list['customfieldgroup']['@attributes']['title'])) {
                $list = array_values(array_filter($list['customfieldgroup']['@attributes'], function ($ug) use ($id) {
                    return $ug['customfieldgroupid'] === $id;
                }));
            } else {
                $list = [$list['customfieldgroup']['@attributes']];
            }
            $this->assertEquals('TestGroup', $list[0]['title']);
        } else {
            $this->fail('CustomFieldGroup was not created');
        }
    }

    /**
     * Test GET from department ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetFromDepartmentId(): void
    {
        if (isset(static::$_departmentId)) {
            $response = $this->getResponse('/Base/CustomFieldGroup/' . static::$_departmentId);
            $list = $this->getArrayFromResponse($response);
            $this->assertEquals('TestGroup', $list['customfieldgroup']['@attributes']['title']);
        } else {
            $this->fail('Department was not created');
        }
    }
}
