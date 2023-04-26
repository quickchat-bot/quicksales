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

use Base\Models\Department\SWIFT_Department;
use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_DepartmentTest
 * @group base
 * @group base-api
 */
class Controller_DepartmentTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Api\Controller_Department', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        \SWIFT::GetInstance()->Database->Record = [
            'departmentid' => 1,
            'departmenttype' => SWIFT_Department::DEPARTMENT_PUBLIC,
            'title' => 'test',
            'departmentapp' => APP_TICKETS,
            'displayorder' => 'asc',
            'parentdepartmentid' => 0,
            'uservisibilitycustom' => 0,
            'toassignid' => 1,
            'usergroupid' => 1,
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

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        \SWIFT::GetInstance()->Database->Record = [
            'departmentid' => 1,
            'departmenttype' => SWIFT_Department::DEPARTMENT_PUBLIC,
            'title' => 'test',
            'departmentapp' => APP_TICKETS,
            'displayorder' => 1,
            'parentdepartmentid' => 0,
            'uservisibilitycustom' => 0,
            'toassignid' => 1,
            'usergroupid' => 1,
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

        $_POST['app'] = 'test';

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['module'] = APP_TICKETS;

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['type'] = 'public';

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['parentdepartmentid'] = '1';

        $this->assertFalse($obj->Post(),
            'Returns false');

        \SWIFT::GetInstance()->Database->method('QueryFetch')->will($this->onConsecutiveCalls(
            ['departmentid' => 1, 'departmentapp' => 'test'],
            ['departmentid' => 1, 'departmentapp' => APP_TICKETS],
            ['departmentid' => 1, 'departmentapp' => APP_TICKETS],
            ['departmentid' => 1, 'departmentapp' => APP_TICKETS, 'parentdepartmentid' => '0'],
            ['departmentid' => 1]
        ));

        $this->assertFalse($obj->Post(),
            'Returns false');

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['title'] = 'Test';
        $_POST['displayorder'] = 2;
        $_POST['uservisibilitycustom'] = '1';
        $_POST['usergroupid'] = [1, 2, 3];

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

        \SWIFT::GetInstance()->Database->method('QueryFetch')->will($this->onConsecutiveCalls(
            ['departmentid' => 1],
            ['departmentid' => 1],
            null,
            ['departmentid' => 1, 'departmentapp' => APP_TICKETS],
            ['departmentid' => 1, 'departmentapp' => 'test'],
            ['departmentid' => 1, 'departmentapp' => APP_TICKETS],
            ['departmentid' => 1, 'departmentapp' => APP_TICKETS, 'departmenttype' => 'public', 'displayorder' => 1],
            ['departmentid' => 1, 'departmentapp' => APP_TICKETS, 'departmenttype' => 'public', 'displayorder' => 1],
            ['departmentid' => 1, 'departmentapp' => APP_TICKETS, 'departmenttype' => 'public', 'displayorder' => 1]
        ));

        $_POST['type'] = 'test';

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['type'] = 'public';
        $_POST['parentdepartmentid'] = '1';

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $this->assertFalse($obj->Put(1),
            'Returns false');

        unset($_POST['parentdepartmentid']);

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['title'] = 'Test';
        $_POST['displayorder'] = 2;
        $_POST['uservisibilitycustom'] = '1';
        $_POST['usergroupid'] = [1, 2, 3];

        $this->assertTrue($obj->Put(1),
            'Returns True');

        $_POST['type'] = 'private';

        $this->assertTrue($obj->Put(1),
            'Returns True');

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

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['departmentid' => 1]);

        $this->assertTrue($obj->Delete(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessDepartmentsClassNotLoaded()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod('Base\Api\Controller_DepartmentMock', 'ProcessDepartments');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);

        $obj->SetIsClassLoaded(false);

        $method->invoke($obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_DepartmentMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Api\Controller_DepartmentMock');
    }
}

class Controller_DepartmentMock extends Controller_Department
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

