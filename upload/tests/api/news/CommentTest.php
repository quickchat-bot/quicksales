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

namespace Tests\Api\News;

use Base\Models\Comment\SWIFT_Comment;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use News\Models\NewsItem\SWIFT_NewsItem;
use Tests\Api\BaseApiTestCase;

/**
 * Class CommentTest
 * @group news
 * @group newscomment
 */
class CommentTest extends BaseApiTestCase
{
    private static $_id;
    private static $_staffId;
    private static $_staffGroupId;
    private static $_newsItemId;

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
            \Colors::errlny('Creating NewsItem "Test News"...');
            static::$_newsItemId = SWIFT_NewsItem::Create(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::STATUS_PUBLISHED, 'Test Staff', 'teststaff@mail.com', 'Test Subject', '', 'Test Contents', self::$_staffId);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create NewsItem: ' . $ex->getMessage());
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            try {
                SWIFT_Comment::DeleteList([static::$_id]); // cleanup
            } catch (\Exception $ex) {
            }
        }

        if (isset(static::$_newsItemId)) {
            try {
                \Colors::errlny('Deleting NewsItem "Test News"...');
                SWIFT_NewsItem::DeleteList([static::$_newsItemId]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete NewsItem: ' . $ex->getMessage());
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
        $response = $this->getResponse('/News/Comment', 'POST', [
            'newsitemid' => static::$_newsItemId,
            'contents' => 'Test Contents',
            'creatortype' => SWIFT_Comment::CREATOR_STAFF,
            'creatorid' => static::$_staffId,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Test Contents', $list['newsitemcomment']['contents']);
        static::$_id = $list['newsitemcomment']['id'];
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/News/Comment');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['newsitemcomment']['id'])) {
            $list = array_values(array_filter($list['newsitemcomment'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['newsitemcomment']];
        }
        $this->assertEquals('Test Contents', $list[0]['contents']);
    }

    /**
     * Test GET all from NewsItem ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetAllFromNewsItemId(): void
    {
        $response = $this->getResponse('/News/Comment/ListAll/' . static::$_newsItemId);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Test Contents', $list['newsitemcomment']['contents']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAllFromNewsItemId
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/News/Comment/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Test Contents', $list['newsitemcomment']['contents']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/News/Comment/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
