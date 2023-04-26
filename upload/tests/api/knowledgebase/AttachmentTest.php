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

namespace Tests\Api\Knowledgebase;

use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Tests\Api\BaseApiTestCase;

/**
 * Class AttachmentTest
 * @group knowledgebase
 * @group kbattachment
 */
class AttachmentTest extends BaseApiTestCase
{
    private static $_id;
    private static $_staffId;
    private static $_staffGroupId;
    private static $_articleId;

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
            \Colors::errlny('Creating Article "Test Article"...');
            static::$_articleId = SWIFT_KnowledgebaseArticle::Create(SWIFT_KnowledgebaseArticle::CREATOR_STAFF,
                static::$_staffId, 'Test Staff', 'teststaff@mail.com', SWIFT_KnowledgebaseArticle::STATUS_PUBLISHED,
                'Test Subject', 'test-subject-seo', 'Test Contents');
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create Article: ' . $ex->getMessage());
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            try {
                SWIFT_Attachment::DeleteList([static::$_id]); // cleanup
            } catch (\Exception $ex) {
            }
        }

        if (isset(static::$_articleId)) {
            try {
                \Colors::errlny('Deleting Article "Test Article"...');
                SWIFT_KnowledgebaseArticle::DeleteList([static::$_articleId]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete Article: ' . $ex->getMessage());
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
        if (isset(static::$_articleId)) {
            $response = $this->getResponse('/Knowledgebase/Attachment', 'POST', [
                'kbarticleid' => static::$_articleId,
                'filename' => 'file.txt',
                'contents' => base64_encode('contents'),
            ]);
            $list = $this->getArrayFromResponse($response);
            $this->assertEquals('file.txt', $list['kbattachment']['filename']);
            static::$_id = $list['kbattachment']['id'];
        } else {
            $this->fail('Article was not created');
        }
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/Knowledgebase/Attachment/ListAll/' . static::$_articleId);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals(static::$_id, $list['kbattachment']['id']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/Knowledgebase/Attachment/' . static::$_articleId . '/' . self::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('file.txt', $list['kbattachment']['filename']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/Knowledgebase/Attachment/' . static::$_articleId . '/' . self::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
