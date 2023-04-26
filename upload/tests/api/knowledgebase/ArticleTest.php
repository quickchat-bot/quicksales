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

use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use Tests\Api\BaseApiTestCase;

/**
 * Class ArticleTest
 * @group knowledgebase
 * @group kbarticle
 */
class ArticleTest extends BaseApiTestCase
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
            static::$_categoryId = SWIFT_KnowledgebaseCategory::Create(0,
                'Test Category', SWIFT_KnowledgebaseCategory::TYPE_GLOBAL, 0, SWIFT_KnowledgebaseCategory::SORT_TITLE, true,
                false, true);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create Category: ' . $ex->getMessage());
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_categoryId)) {
            try {
                \Colors::errlny('Deleting Category "Test Category"...');
                SWIFT_KnowledgebaseCategory::DeleteList([static::$_categoryId]);
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
        if (isset(static::$_staffId)) {
            $response = $this->getResponse('/Knowledgebase/Article', 'POST', [
                'subject' => 'Custom Subject',
                'contents' => 'Custom contents',
                'creatorid' => static::$_staffId,
                'categoryid' => static::$_categoryId,
            ]);
            $list = $this->getArrayFromResponse($response);
            $this->assertEquals('Custom Subject', $list['kbarticle']['subject']);
            static::$_id = $list['kbarticle']['kbarticleid'];
        } else {
            $this->fail('Staff user was not created');
        }
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        // TODO: test ListAll/{categoryid}/{count}/{start}/{sortField}/{sortOrder}"
        $response = $this->getResponse('/Knowledgebase/Article');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        if (!isset($list['kbarticle']['subject'])) {
            $list = array_values(array_filter($list['kbarticle'], function ($ug) use ($id) {
                return $ug['kbarticleid'] === $id;
            }));
        } else {
            $list = [$list['kbarticle']];
        }
        $this->assertEquals('Custom Subject', $list[0]['subject']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/Knowledgebase/Article/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom Subject', $list['kbarticle']['subject']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetArticleCountFromCategoryId(): void
    {
        $response = $this->getResponse('/Knowledgebase/Article/GetArticleCount/' . static::$_categoryId);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals(1, $list['totalarticles']);
    }

    /**
     * Test PUT endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testPut(): void
    {
        $response = $this->getResponse('/Knowledgebase/Article/' . static::$_id, 'PUT', [
            'editedstaffid' => static::$_staffId,
            'subject' => 'NewCustom Subject',
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('NewCustom Subject', $list['kbarticle']['subject']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPut
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/Knowledgebase/Article/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
