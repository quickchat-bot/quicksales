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

namespace Base\Api;

use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmailManager;
use Base\Models\User\SWIFT_UserGroup;
use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_UserTest
 * @group base
 * @group base-api
 */
class Controller_UserTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Api\Controller_User', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        \SWIFT::GetInstance()->Database->Record = [
            'userid' => 1,
            'linktypeid' => 1,
            'email' => 'test@test.com',
            'userrole' => SWIFT_User::ROLE_MANAGER,
            'salutation' => SWIFT_User::SALUTATION_MR,
        ];

        $this->assertTrue($obj->GetList(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testFilterReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        \SWIFT::GetInstance()->Database->Record = [
            'userid' => 1,
            'linktypeid' => 1,
            'email' => 'test@test.com',
            'userrole' => SWIFT_User::ROLE_MANAGER,
            'salutation' => SWIFT_User::SALUTATION_MR,
        ];

        $this->assertTrue($obj->Filter(1, 2000),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Filter');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        \SWIFT::GetInstance()->Database->Record = [
            'userid' => 1,
            'linktypeid' => 1,
            'email' => 'test@test.com',
            'userrole' => SWIFT_User::ROLE_MANAGER,
            'salutation' => SWIFT_User::SALUTATION_MR,
        ];

        $this->assertTrue($obj->Get(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Get', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        static::$databaseCallback['CacheGet'] = function ($x) {
            switch ($x) {
                case 'usergroupcache':
                    return [
                        1 => ['grouptype' => SWIFT_UserGroup::TYPE_GUEST],
                        2 => ['grouptype' => SWIFT_UserGroup::TYPE_REGISTERED],
                    ];
                case 'slaplancache':
                    return [1 => []];
                default:
                    return [1 => []];
            }
        };

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['usergroupid'] = 1;

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['usergroupid'] = 2;
        $_POST['salutation'] = '';
        $_POST['userorganizationid'] = 1;

        $this->assertFalse($obj->Post(),
            'Returns false');

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'userorganizationid' => 1,
            'usergroupid' => 1,
            'userid' => 1,
            'useremailid' => 1,
            'linktype' => SWIFT_UserEmailManager::LINKTYPE_USER,
        ]);

        $_POST['designation'] = 'designation';
        $_POST['phone'] = 'phone';
        $_POST['isenabled'] = '1';
        $_POST['userrole'] = 'manager';
        $_POST['timezone'] = 'utc';
        $_POST['enabledst'] = '1';
        $_POST['slaplanid'] = 2;
        $_POST['slaplanexpiry'] = 124252342234;
        $_POST['userexpiry'] = 1342342352;
        $_POST['sendwelcomeemail'] = 1;
        $_POST['fullname'] = 'test user';
        $_POST['password'] = 'test';
        $_POST['sendwelcomeemail'] = 1;

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['email'] = 'test@test.com';

        \SWIFT::GetInstance()->Database->Record = ['email' => 'test@test.com'];


        $this->assertFalse($obj->Post(),
            'Returns false');

        \SWIFT::GetInstance()->Database->Record = [];

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['email'] = ['test@test.com', 'test'];

        static::$nextRecordType = static::NEXT_RECORD_CUSTOM_LIMIT;
        static::$nextRecordLimit = 1;

        $this->assertFalse($obj->Post(),
            'Returns false');

        static::$nextRecordCount = 0;

        $_POST['slaplanid'] = 1;

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['fullname'] = '';

        $this->assertFalse($obj->Post(),
            'Returns false');

        $this->assertClassNotLoaded($obj, 'Post');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPutReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $data = [
            'usergroupid' => 1,
            'userid' => 1,
            'userorganizationid' => 1,
            'userrole' => SWIFT_User::ROLE_MANAGER,
            'useremailid' => 1,
            'linktype' => SWIFT_UserEmailManager::LINKTYPE_USER,
            'salutation' => '',
            'userdesignation' => 'userdesignation',
            'phone' => 'phone',
            'isenabled' => '1',
            'enabledst' => '1',
            'slaplanid' => '1',
            'slaexpirytimeline' => 12312343213,
            'usergroupid' => 1,
            'userexpirytimeline' => 1231234131,
            'timezonephp' => 'utc',
        ];

        $count = 1;
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturnCallback(function ($x) use (&$data, &$count) {
            if (isset($data['dynamic_userorganizationid']) && $data['dynamic_userorganizationid']) {
                $data['userorganizationid'] = $count % 2 ? 1 : '';
                $count++;
            }
            return $data;
        });

        $_POST['salutation'] = '';
        $_POST['userorganizationid'] = 1;

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        static::$databaseCallback['CacheGet'] = function ($x) {
            switch ($x) {
                case 'usergroupcache':
                    return [
                        1 => ['grouptype' => SWIFT_UserGroup::TYPE_GUEST],
                        2 => ['grouptype' => SWIFT_UserGroup::TYPE_REGISTERED],
                    ];
                case 'slaplancache':
                    return [1 => []];
                default:
                    return [1 => []];
            }
        };

        \SWIFT::GetInstance()->Database->Record = ['email' => 'test@test.com'];

        $data['dynamic_userorganizationid'] = true;

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $data['dynamic_userorganizationid'] = false;
        $data['userorganizationid'] = 1;

        $_POST['designation'] = 'designation';
        $_POST['phone'] = 'phone';
        $_POST['isenabled'] = '1';
        $_POST['userrole'] = 'manager';
        $_POST['timezone'] = 'utc';
        $_POST['enabledst'] = '1';
        $_POST['slaplanid'] = 2;

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['userrole'] = 'user';
        $_POST['slaplanid'] = 1;
        $_POST['slaplanexpiry'] = 124252342234;
        $_POST['userexpiry'] = 1342342352;
        $_POST['sendwelcomeemail'] = 1;
        $_POST['fullname'] = 'test user';
        $_POST['usergroupid'] = 3;

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['usergroupid'] = 1;

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['usergroupid'] = 2;

        $this->assertTrue($obj->Put(1),
            'Returns true');

        $_POST['fullname'] = '';

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['email'] = 'test@test.com';

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['email'] = 'test2@test.com';

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['email'] = ['test@test.com', 'test'];

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $this->assertClassNotLoaded($obj, 'Put', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Delete(1),
            'Returns false');

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['userid' => 1]);

        $this->assertTrue($obj->Delete(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessUsersReturnsTrue()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod('Base\Api\Controller_UserMock', 'ProcessUsers');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);

        $obj->SetIsClassLoaded(false);

        $method->invoke($obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_UserMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Api\Controller_UserMock');
    }
}

class Controller_UserMock extends Controller_User
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

