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

namespace Tests\Api\Troubleshooter;

use Base\Models\Attachment\SWIFT_Attachment;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;
use Tests\Api\BaseApiTestCase;

/**
 * Class AttachmentTest
 * @group troubleshooter
 * @group tsattachment
 */
class AttachmentTest extends BaseApiTestCase
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
            static::$_categoryId = SWIFT_TroubleshooterCategory::Create('TestCategory', 'TestCategory', SWIFT_TroubleshooterCategory::TYPE_GLOBAL, 1);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create Category: ' . $ex->getMessage());
        }

        try {
            \Colors::errlny('Creating Step "Test Step"...');
            static::$_stepId = SWIFT_TroubleshooterStep::Create(self::$_categoryId, SWIFT_TroubleshooterStep::STATUS_PUBLISHED, 'Test Step', 'Test Contents', 1, true);
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create Step: ' . $ex->getMessage());
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
        if (isset(static::$_stepId)) {
            $response = $this->getResponse('/Troubleshooter/Attachment', 'POST', [
                'troubleshooterstepid' => static::$_stepId,
                'filename' => 'file.txt',
                'contents' => base64_encode('contents'),
            ]);
            $list = $this->getArrayFromResponse($response);
            $this->assertEquals('file.txt', $list['troubleshooterattachment']['filename']);
            static::$_id = $list['troubleshooterattachment']['id'];
        } else {
            $this->fail('Step was not created');
        }
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/Troubleshooter/Attachment/ListAll/' . static::$_stepId);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals(static::$_id, $list['troubleshooterattachment']['id']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/Troubleshooter/Attachment/' . static::$_stepId . '/' . self::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('file.txt', $list['troubleshooterattachment']['filename']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/Troubleshooter/Attachment/' . static::$_stepId . '/' . self::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
