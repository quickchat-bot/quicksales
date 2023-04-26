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

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_StaffTest
 * @group base
 * @group base-api
 */
class Controller_StaffTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Api\Controller_Staff', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->Record = [
            'staffid' => 1,
            'staffgroupid' => 1,
            'firstname' => 'Test',
            'lastname' => 'User',
            'fullname' => 'Test User',
            'username' => 'test.user',
            'email' => 'test@test.com',
            'designation' => 'designation',
            'greeting' => 'greeting',
            'mobilenumber' => '8275638745',
            'isenabled' => 1,
            'timezonephp' => 'UTC',
            'enabledst' => '8275638745',
            'signature' => 'signature'
        ];

        $this->assertTrue($obj->GetList(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->Record = [
            'staffid' => 1,
            'staffgroupid' => 1,
            'firstname' => 'Test',
            'lastname' => 'User',
            'fullname' => 'Test User',
            'username' => 'test.user',
            'email' => 'test@test.com',
            'designation' => 'designation',
            'greeting' => 'greeting',
            'mobilenumber' => '8275638745',
            'isenabled' => 1,
            'timezonephp' => 'UTC',
            'enabledst' => '8275638745',
            'signature' => 'signature'
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

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['firstname'] = 'Test';

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['lastname'] = 'User';

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['email'] = 'test@test.com';

        $this->assertFalse($obj->Post(),
            'Returns false');

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'staffgroupcache') {
                return [
                    1 => []
                ];
            }
        };

        $_POST['staffgroupid'] = 1;

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['password'] = 'Test';

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['username'] = 'Test';
        $_POST['designation'] = 'Test';
        $_POST['mobilenumber'] = 'Test';
        $_POST['staffsignature'] = 'Test';
        $_POST['isenabled'] = '1';
        $_POST['greeting'] = 'Mr';
        $_POST['timezone'] = 'utc';
        $_POST['enabledst'] = '1';

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['staffid' => 1, 'totalitems' => 1]);

        \SWIFT::Set('licensedstaff', 1);

        $this->assertFalse($obj->Post(),
            'Returns false');

        \SWIFT::Set('licensedstaff', 2);

        $this->assertTrue($obj->Post(),
            'Returns true');

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

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'staffid' => 1,
            'email' => 'test@test.com',
            'staffgroupid' => 1,
            'designation' => 'designation',
            'mobilenumber' => '1123123',
            'signature' => 'signature',
            'isenabled' => 1,
            'greeting' => 'Hello',
            'timezonephp' => 'utc',
            'enabledst' => 1,
            'username' => 'test',
            'isenabled' => 1,
        ]);

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['firstname'] = 'Test';

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['lastname'] = 'User';

        $this->assertFalse($obj->Put(1),
            'Returns false');

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'staffgroupcache') {
                return [
                    1 => []
                ];
            }
        };

        $_POST['staffgroupid'] = 1;
        $_POST['password'] = 'Test';
        $_POST['username'] = 'Test';
        $_POST['email'] = 'test@test.com';
        $_POST['designation'] = 'Test';
        $_POST['mobilenumber'] = 'Test';
        $_POST['staffsignature'] = 'Test';
        $_POST['isenabled'] = '1';
        $_POST['greeting'] = 'Mr';
        $_POST['timezone'] = 'utc';
        $_POST['enabledst'] = '1';

        $this->assertTrue($obj->Put(1),
            'Returns true');

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

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['staffid' => 1]);

        $this->assertTrue($obj->Delete(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessStaffClassNotLoaded()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod('Base\Api\Controller_StaffMock', 'ProcessStaff');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);

        $obj->SetIsClassLoaded(false);

        $method->invoke($obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_StaffMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Api\Controller_StaffMock');
    }
}

class Controller_StaffMock extends Controller_Staff
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

