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

use Base\Models\User\SWIFT_UserGroup;
use Tests\Api\BaseApiTestCase;

/**
 * Class UserTest
 * @group base
 * @group user
 */
class UserTest extends BaseApiTestCase
{
    private static $_id;
    private static $_id2;
    private static $_userGroupId;

    public static function setUpBeforeClass()
    {
        try {
            \Colors::errlny('Creating UserGroup "TestGroup"...');
            /** @var SWIFT_UserGroup $_SWIFT_UserGroupObject */
            $_SWIFT_UserGroupObject = SWIFT_UserGroup::Create('TestGroup', SWIFT_UserGroup::TYPE_REGISTERED);
            static::$_userGroupId = $_SWIFT_UserGroupObject->GetID();
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create UserGroup');
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_userGroupId)) {
            try {
                \Colors::errlny('Deleting UserGroup "TestGroup"...');
                SWIFT_UserGroup::DeleteList([static::$_userGroupId]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete UserGroup');
            }
        }
    }

    /**
     * Test POST endpoint
     * ** THIS SHOULD BE THE FIRST TEST IN ORDER TO TEST CRUD **
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPost(): void
    {
        if (isset(static::$_userGroupId)) {
            $response = $this->getResponse('/Base/User', 'POST', [
                'fullname' => 'Custom',
                'usergroupid' => static::$_userGroupId,
                'password' => 'The User Password',
                'email' => 'custom@mail.com',
            ]);
            $list = $this->getArrayFromResponse($response);
            $this->assertEquals('Custom', $list['user']['fullname']);
            static::$_id = $list['user']['id'];

            $response = $this->getResponse('/Base/User', 'POST', [
                'fullname' => 'Custom2',
                'usergroupid' => static::$_userGroupId,
                'password' => 'The User Password',
                'email' => 'custom2@mail.com',
            ]);
            $list = $this->getArrayFromResponse($response);
            $this->assertEquals('Custom2', $list['user']['fullname']);
            static::$_id2 = $list['user']['id'];
        } else {
            $this->fail('UserGroup was not created');
        }
    }

    /**
     * Test GET all endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testGetAll(): void
    {
        $response = $this->getResponse('/Base/User');
        $list = $this->getArrayFromResponse($response);
        $id = static::$_id;
        $id2 = static::$_id2;
        $list = array_values(array_filter($list['user'], function ($ug) use ($id, $id2) {
            return in_array($ug['id'], [$id, $id2], true);
        }));
        $this->assertEquals('Custom', $list[0]['fullname']);
        $this->assertEquals('Custom2', $list[1]['fullname']);
    }

    /**
     * Test GET Filter endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPost
     */
    public function testFilter(): void
    {
        // retrieve 2 elements
        $response = $this->getResponse(sprintf('/Base/User/Filter/%d/2', static::$_id));
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['user'][0]['fullname']);
        $this->assertEquals('Custom2', $list['user'][1]['fullname']);
    }

    /**
     * Test GET from ID endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetAll
     */
    public function testGetFromId(): void
    {
        $response = $this->getResponse('/Base/User/' . static::$_id);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('Custom', $list['user']['fullname']);
    }

    /**
     * Test PUT endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testGetFromId
     */
    public function testPut(): void
    {
        $response = $this->getResponse('/Base/User/' . static::$_id, 'PUT', [
            'fullname' => 'NewCustom',
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals('NewCustom', $list['user']['fullname']);
    }

    /**
     * Test DELETE endpoint
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @depends testPut
     */
    public function testDelete(): void
    {
        $response = $this->getResponse('/Base/User/' . static::$_id, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());

        $response = $this->getResponse('/Base/User/' . static::$_id2, 'DELETE');
        $this->assertEmpty($response->getBody()->getContents());
    }
}
