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

namespace Tests\Api\Troubleshooter;

use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use Tests\Api\BaseApiTestCase;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;

/**
 * Class CategoryTest
 * @group troubleshooter
 * @group tscategory
 */
class CategoryTest extends BaseApiTestCase
{
    private static $_id;
    private static $_staffId;
    private static $_staffGroupId;

    public static function setUpBeforeClass()
    {
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
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            try {
                SWIFT_TroubleshooterCategory::DeleteList([static::$_id]); // cleanup
            } catch (\Exception $ex) {
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
    }

    /**
     * Test POST endpoint
     * ** THIS SHOULD BE THE FIRST TEST IN ORDER TO TEST CRUD **
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPost(): void
    {
        $response = $this->getResponse('/Troubleshooter/Category', 'POST', [
            'title' => 'Custom',
            'categorytype' => SWIFT_TroubleshooterCategory::TYPE_GLOBAL,
            'staffid' => static::$_staffId,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['troubleshootercategory']['title']);
        static::$_id = $list['troubleshootercategory']['id'];
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/Troubleshooter/Category');
        $list = $this->getArrayFromResponse($response);
        if (!isset($list['troubleshootercategory']['title'])) {
            $id = static::$_id;
            $list = array_values(array_filter($list['troubleshootercategory'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['troubleshootercategory']];
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
        $response = $this->getResponse('/Troubleshooter/Category/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['troubleshootercategory']['title']);
    }

    /**
     * Test PUT endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testPut(): void
    {
        $response = $this->getResponse('/Troubleshooter/Category/' . static::$_id, 'PUT', [
            'title' => 'NewCustom',
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('NewCustom', $list['troubleshootercategory']['title']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPut
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/Troubleshooter/Category/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
