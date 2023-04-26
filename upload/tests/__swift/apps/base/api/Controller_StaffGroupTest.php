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

namespace Base\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_StaffGroupTest
 * @group base
 * @group base-api
 */
class Controller_StaffGroupTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Api\Controller_StaffGroup', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->Record = ['staffgroupid' => 1, 'title' => 'test', 'isadmin' => 1];

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

        \SWIFT::GetInstance()->Database->Record = ['staffgroupid' => 1, 'title' => 'test', 'isadmin' => 1];

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

        \SWIFT::GetInstance()->Database->Record = ['staffgroupid' => 1, 'title' => 'test', 'isadmin' => 1];

        $this->assertFalse($obj->Post(),
            'Returns false');

        $_POST['title'] = 'test';
        $_POST['isadmin'] = '1';

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['staffgroupid' => 1]);

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

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['staffgroupid' => 1, 'isadmin' => 1]);

        $this->assertFalse($obj->Put(1),
            'Returns false');

        $_POST['title'] = 'test';
        $_POST['isadmin'] = '1';

        $this->assertTrue($obj->Put(1),
            'Returns true');

        $_POST['isadmin'] = '0';

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

        \SWIFT::GetInstance()->Database->method('QueryFetch')->will($this->onConsecutiveCalls(
            ['staffgroupid' => 1],
            ['totalitems' => 1],
            ['staffgroupid' => 1],
            ['totalitems' => 0]
        ));

        $this->assertFalse($obj->Delete(1),
            'Returns false');

        $this->assertTrue($obj->Delete(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessStaffGroupsClassNotLoaded()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod('Base\Api\Controller_StaffGroupMock', 'ProcessStaffGroups');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);

        $obj->SetIsClassLoaded(false);

        $method->invoke($obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_StaffGroupMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Api\Controller_StaffGroupMock');
    }
}

class Controller_StaffGroupMock extends Controller_StaffGroup
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

