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

use Base\Models\Staff\SWIFT_StaffGroup;
use Tests\Api\BaseApiTestCase;

/**
 * Class StaffTest
 * @group base
 * @group staff
 */
class StaffTest extends BaseApiTestCase
{
    private static $_id;
    private static $_staffGroupId;

    public static function setUpBeforeClass()
    {
        try {
            \Colors::errlny('Creating StaffGroup "TestGroup"...');
            /** @var SWIFT_StaffGroup $_SWIFT_StaffGroupObject */
            $_SWIFT_StaffGroupObject = SWIFT_StaffGroup::Insert('TestGroup', false);
            static::$_staffGroupId = $_SWIFT_StaffGroupObject->GetID();
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create StaffGroup');
        }
    }

    public static function tearDownAfterClass()
    {
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
        if (isset(static::$_staffGroupId)) {
            $response = $this->getResponse('/Base/Staff', 'POST', [
                'firstname' => 'The first name',
                'lastname' => 'The last name',
                'username' => 'The login username',
                'password' => 'The staff password',
                'staffgroupid' => static::$_staffGroupId,
                'email' => 'custom@mail.com',
            ]);
            $list = $this->getArrayFromResponse($response);
            $this->assertEquals('custom@mail.com', $list['staff']['email']);
            static::$_id = $list['staff']['id'];
        } else {
            $this->fail('StaffGroup was not created');
        }
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/Base/Staff');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        $list = array_values(array_filter($list['staff'], function ($ug) use ($id) {
            return $ug['id'] === $id;
        }));
        $this->assertEquals('custom@mail.com', $list[0]['email']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/Base/Staff/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('custom@mail.com', $list['staff']['email']);
    }

    /**
     * Test PUT endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testPut(): void
    {
        $response = $this->getResponse('/Base/Staff/' . static::$_id, 'PUT', [
            'firstname' => 'The first name',
            'lastname' => 'The last name',
            'email' => 'newcustom@mail.com',
            'staffgroupid' => static::$_staffGroupId,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('newcustom@mail.com', $list['staff']['email']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPut
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/Base/Staff/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
