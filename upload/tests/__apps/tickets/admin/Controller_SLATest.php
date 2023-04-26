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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_SLATest
 * @group tickets
 */
class Controller_SLATest extends \SWIFT_TestCase
{
    public static $_next = false;

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_SLA', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListWorks()
    {
        $obj = $this->getController();

        $this->assertTrue($obj::DeleteList([1], true),
            'Returns true after deleting with admin_tcandeleteslaplans = 1');

        $this->assertFalse($obj::DeleteList([], true),
            'Returns false after rendering with admin_tcandeleteslaplans = 0');

        $this->assertFalse($obj::DeleteList([], false),
            'Returns false if csrfhash is not provided');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEnableListWorks()
    {
        $obj = $this->getController();

        $this->assertTrue($obj::EnableList([1], true),
            'Returns true after deleting with admin_tcanupdateslaplan = 1');

        $this->assertFalse($obj::EnableList([], true),
            'Returns false after rendering with admin_tcanupdateslaplan = 0');

        $this->assertFalse($obj::EnableList([], false),
            'Returns false if csrfhash is not provided');
    }


    /**
     * @throws SWIFT_Exception
     */
    public function testDisableListWorks()
    {
        $obj = $this->getController();

        $this->assertTrue($obj::DisableList([1], true),
            'Returns true after deleting with admin_tcanupdateslaplan = 1');

        $this->assertFalse($obj::DisableList([], true),
            'Returns false after rendering with admin_tcanupdateslaplan = 0');

        $this->assertFalse($obj::DisableList([], false),
            'Returns false if csrfhash is not provided');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Delete(1));

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Manage(),
            'Returns true with admin_tcanviewslaplans = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true with admin_tcanviewslaplans = 0');

        $this->assertClassNotLoaded($obj, 'Manage');
    }

    /**
     * @throws \ReflectionException
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('RunChecks');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with empty POST');

        $_POST['title'] = 1;
        $_POST['overduehrs'] = 1;
        $_POST['slascheduleid'] = 1;
        $_POST['resolutionduehrs'] = 1;

        SWIFT::Set('isdemo', true);

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false in demo mode');

        SWIFT::Set('isdemo', false);

        $_POST['overduehrs'] = -1;
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false negative overdue sla');

        $_POST['overduehrs'] = 0;
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false zero overdue sla');

        $_POST['resolutionduehrs'] = -1;
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false negative resolution sla');

        $_POST['resolutionduehrs'] = 0;
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false zero resolution sla');

        $_POST['overduehrs'] = 1;
        $_POST['resolutionduehrs'] = 1;

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without rulecriteria');

        $_POST['rulecriteria'] = [1 => [1]];

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with admin_tcaninsertslaplan = 1');

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with admin_tcaninsertslaplan = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testLoadPostVariablesReturnsTrue()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_LoadPOSTVariables');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($obj, 1));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $cache->method('Get')->willReturnOnConsecutiveCalls([1=>[1]], []);
        $obj = $this->getController([
            'Cache' => $cache,
        ]);

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertslaplan = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertslaplan = 0');

        $this->assertClassNotLoaded($obj, 'Insert');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetAssignedSlaHolidayIdListReturnsArray()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_GetAssignedSLAHolidayIDList');
        $method->setAccessible(true);

        $this->assertEmpty($method->invoke($obj));

        $_POST['slaholidays'] = [1 => 1];

        $this->assertCount(1, $method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->InsertSubmit());

        $obj->_passChecks = true;

        $_POST['title'] = 1;
        $_POST['overduehrs'] = 1;
        $_POST['resolutionduehrs'] = 1;
        $_POST['slascheduleid'] = 1;
        $_POST['isenabled'] = 1;
        $_POST['sortorder'] = 1;
        $_POST['ruleoptions'] = 1;
        $_POST['rulecriteria'] = [1 => [1]];
        $_POST['slaholidays'] = [1 => 1];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'slaplanid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
        ]);

        $this->assertTrue($obj->InsertSubmit());

        $this->assertClassNotLoaded($obj, 'InsertSubmit');
    }

    public function testEditThrowsException()
    {
        $obj = $this->getController();
        $this->assertInvalidData($obj, 'Edit', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditReturnsTrue()
    {
        $obj = $this->getController();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'slaplanid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
        ]);

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdateslaplan = 1');

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdateslaplan = 0');

        $this->assertClassNotLoaded($obj, 'Edit', 1);
    }

    public function testEditSubmitThrowsException()
    {
        $obj = $this->getController();
        $this->assertInvalidData($obj, 'EditSubmit', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitReturnsTrue()
    {
        $obj = $this->getController();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'slaplanid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
        ]);

        $this->assertFalse($obj->EditSubmit(1));

        $obj->_passChecks = true;
        $_POST['title'] = 1;
        $_POST['overduehrs'] = 1;
        $_POST['resolutionduehrs'] = 1;
        $_POST['slascheduleid'] = 1;
        $_POST['isenabled'] = 1;
        $_POST['sortorder'] = 1;
        $_POST['ruleoptions'] = 1;
        $_POST['rulecriteria'] = [1 => [1]];

        $this->assertTrue($obj->EditSubmit(1));

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderConfirmationReturnsTrue()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_RenderConfirmation');
        $method->setAccessible(true);

        $db = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('QueryFetch')->willReturn([
            'slascheduleid' => 1,
        ]);
        $db->method('NextRecord')->willReturnCallback(function () {
            self::$_next = !self::$_next;

            return self::$_next;
        });
        $db->Record = [
            'name' => 'ticketstatus',
            'rulematch' => '0',
        ];
        $obj->Database = $db;
        $this->assertTrue($method->invoke($obj, 1, 1));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_SLAMock
     */
    private function getController(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Admin\View_SLA')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Admin\Controller_SLAMock', array_merge([
            'View' => $view,
        ], $services));
    }
}

class Controller_SLAMock extends Controller_SLA
{
    public $_passChecks = false;

    public $Database;

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

    protected function RunChecks($_mode)
    {
        if ($this->_passChecks) {
            return true;
        }

        return parent::RunChecks($_mode);
    }
}

