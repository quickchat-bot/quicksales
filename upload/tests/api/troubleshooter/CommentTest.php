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

use Base\Models\Comment\SWIFT_Comment;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use Tests\Api\BaseApiTestCase;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;

/**
 * Class CommentTest
 * @group troubleshooter
 * @group tscomment
 */
class CommentTest extends BaseApiTestCase
{
    private static $_id;
    private static $_staffId;
    private static $_staffGroupId;
    private static $_stepId;
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
            \Colors::errlny('Creating Category "TestCategory"...');
            static::$_categoryId = SWIFT_TroubleshooterCategory::Create('TestCategory', 'TestCategory', SWIFT_TroubleshooterCategory::TYPE_GLOBAL, 0);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create Category: ' . $ex->getMessage());
        }

        try {
            \Colors::errlny('Creating Step "Test Step"...');
            static::$_stepId = SWIFT_TroubleshooterStep::Create(self::$_categoryId, SWIFT_TroubleshooterStep::STATUS_PUBLISHED, 'Test Step', 'Test Contents', 0, true);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create Step: ' . $ex->getMessage());
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

        if (isset(static::$_stepId)) {
            try {
                \Colors::errlny('Deleting Step "Test Step"...');
                SWIFT_TroubleshooterStep::DeleteList([static::$_stepId]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete Step: ' . $ex->getMessage());
            }
        }

        if (isset(static::$_categoryId)) {
            try {
                \Colors::errlny('Deleting Category "TestCategory"...');
                SWIFT_TroubleshooterCategory::DeleteList([static::$_categoryId]);
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
        $response = $this->getResponse('/Troubleshooter/Comment', 'POST', [
            'troubleshooterstepid' => static::$_stepId,
            'contents' => 'Custom',
            'creatortype' => SWIFT_Comment::CREATOR_STAFF,
            'creatorid' => static::$_staffId,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['troubleshooterstepcomment']['contents']);
        static::$_id = $list['troubleshooterstepcomment']['id'];
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/Troubleshooter/Comment');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['troubleshooterstepcomment']['contents'])) {
            $list = array_values(array_filter($list['troubleshooterstepcomment'], function ($ug) use ($id) {
                return $ug['id'] === $id;
            }));
        } else {
            $list = [$list['troubleshooterstepcomment']];
        }
        $this->assertEquals('Custom', $list[0]['contents']);
    }

    /**
     * Test GET all from NewsItem ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetAllFromStepId(): void
    {
        $response = $this->getResponse('/Troubleshooter/Comment/ListAll/' . static::$_stepId);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['troubleshooterstepcomment']['contents']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAllFromStepId
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/Troubleshooter/Comment/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['troubleshooterstepcomment']['contents']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/Troubleshooter/Comment/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
