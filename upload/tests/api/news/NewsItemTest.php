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

namespace Tests\Api\News;

use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use News\Models\Category\SWIFT_NewsCategory;
use News\Models\NewsItem\SWIFT_NewsItem;
use Tests\Api\BaseApiTestCase;

/**
 * Class NewsItemTest
 * @group news
 * @group newsitem
 */
class NewsItemTest extends BaseApiTestCase
{
    private static $_id;
    private static $_staffId;
    private static $_staffGroupId;
    private static $_categoryId;

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

        try {
            \Colors::errlny('Creating Category "Test Category"...');
            static::$_categoryId = SWIFT_NewsCategory::Create('Test Category',SWIFT_PUBLIC);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create Category: ' . $ex->getMessage());
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            try {
                SWIFT_NewsItem::DeleteList([static::$_id]); // cleanup
            } catch (\Exception $ex) {
            }
        }

        if (isset(static::$_categoryId)) {
            try {
                \Colors::errlny('Deleting Category "Test Category"...');
                SWIFT_NewsCategory::DeleteList([static::$_categoryId]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete Category: ' . $ex->getMessage());
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
        $response = $this->getResponse('/News/NewsItem', 'POST', [
            'subject' => 'Test Subject',
            'contents' => 'Test Contents',
            'staffid' => static::$_staffId,
            'newscategoryidlist' => static::$_categoryId,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Test Subject', $list['newsitem']['subject']);
        static::$_id = $list['newsitem']['id'];
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/News/NewsItem');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['newsitem']['subject'])) {
            $list = array_values(array_filter($list['newsitem'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['newsitem']];
        }
        $this->assertEquals('Test Subject', $list[0]['subject']);
    }

    /**
     * Test GET all from Category ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetAllFromCategoryId(): void
    {
        $response = $this->getResponse('/News/NewsItem/ListAll/' . static::$_categoryId);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Test Subject', $list['newsitem']['subject']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAllFromCategoryId
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/News/NewsItem/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Test Subject', $list['newsitem']['subject']);
    }

    /**
     * Test PUT endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testPut(): void
    {
        $response = $this->getResponse('/News/NewsItem/' . static::$_id, 'PUT', [
            'subject' => 'New Test Subject',
            'contents' => 'New Test Contents',
            'editedstaffid' => static::$_staffId,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('New Test Subject', $list['newsitem']['subject']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPut
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/News/NewsItem/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
