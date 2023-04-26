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

use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserGroup;
use Tests\Api\BaseApiTestCase;

/**
 * Class UserSearchTest
 * @group base
 * @group usersearch
 */
class UserSearchTest extends BaseApiTestCase
{
    private static $_id;
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

        try {
            \Colors::errlny('Creating User "Test User"...');
            /** @var SWIFT_User $_SWIFT_UserObject */
            $_SWIFT_UserObject = SWIFT_User::Create(static::$_userGroupId, 0, '', 'Test User' . static::$_userGroupId, '', '', 1, SWIFT_User::ROLE_USER, ['testuser@mail.com'], 'password', 0, '', 0, 0, 0, 0, false, true);
            static::$_id = $_SWIFT_UserObject->GetID();
        } catch (\Exception $ex) {
            \Colors::errlnr('Unable to create User');
        }
    }

    public static function tearDownAfterClass()
    {
        if (isset(static::$_id)) {
            try {
                \Colors::errlny('Deleting User "Test User"...');
                SWIFT_User::DeleteList([static::$_id]);
            } catch (\Exception $ex) {
                \Colors::errlnr('Unable to delete User');
            }
        }

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
        $response = $this->getResponse('/Base/UserSearch', 'POST', [
            'query' => 'Test User' . static::$_userGroupId,
            'phrase' => 1,
        ]);
        $list = $this->getArrayFromResponse($response);
        $this->assertEquals(static::$_id, $list['user']['id']);
    }
}
