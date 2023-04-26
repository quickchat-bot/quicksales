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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_ScheduleTest
 * @group tickets
 */
class Controller_ScheduleTest extends \SWIFT_TestCase
{
    public static $_next = false;

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_Schedule', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListWorks()
    {
        $obj = $this->getController();

        $this->assertTrue($obj::DeleteList([1], true),
            'Returns true after deleting with admin_tcandeleteslaschedules = 1');

        $this->assertFalse($obj::DeleteList([], true),
            'Returns false after rendering with admin_tcandeleteslaschedules = 0');

        $this->assertFalse($obj::DeleteList([], false),
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
            'Returns true with admin_tcanviewslaschedules = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true with admin_tcanviewslaschedules = 0');

        $this->assertClassNotLoaded($obj, 'Manage');
    }

    /**
     * @throws \ReflectionException
     */
    public function testRunChecksReturnsArray()
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

        SWIFT::Set('isdemo', true);

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false in demo mode');

        SWIFT::Set('isdemo', false);

        $_POST['sladay'] = [];

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false without sladay');

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with admin_tcaninsertslaholidays = 0');

        \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn(1);

        $_POST['sladay']['sunday'] = 1;
        $_POST['dayHourOpen']['sunday'] = [1];
        $_POST['dayMinuteOpen']['sunday'] = [2];
        $_POST['dayHourClose']['sunday'] = [1];
        $_POST['dayMinuteClose']['sunday'] = [1];
        $_POST['rowId']['sunday'] = [1];

        $this->assertFalse($method->invoke($obj, 1),
            'Returns true with admin_tcaninsertslaschedules = 1');

        $_POST['dayMinuteOpen']['sunday'] = [1];

        $this->assertCount(1, $method->invoke($obj, 1),
            'Returns true with admin_tcaninsertslaschedules = 1');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertslaschedules = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertslaschedules = 0');

        $this->assertClassNotLoaded($obj, 'Insert');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->InsertSubmit());

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'slascheduleid' => 1,
        ]);

        $obj->_passChecks = true;
        $_POST['title'] = 1;
        $_POST['sladay']['sunday'] = 1;
        $_POST['dayHourOpen']['sunday'] = [1];
        $_POST['dayMinuteOpen']['sunday'] = [1];
        $_POST['dayHourClose']['sunday'] = [1];
        $_POST['dayMinuteClose']['sunday'] = [1];
        $_POST['rowId']['sunday'] = [1];
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
        $db = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('NextRecord')->willReturnCallback(function() {
            static::$_next = !static::$_next;

            return static::$_next;
        });
        $obj = $this->getController([
            'Database' => $db,
        ]);

        SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'slascheduleid' => 1,
        ]);

        $_POST['sladay']['sunday'] = 1;
        $_POST['dayHourOpen']['sunday'] = [1];
        $_POST['dayMinuteOpen']['sunday'] = [1];
        $_POST['dayHourClose']['sunday'] = [1];
        $_POST['dayMinuteClose']['sunday'] = [1];
        $_POST['rowId']['sunday'] = [1];

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdateslaschedules = 1');

        $obj->Database->Record = [
            'sladay' => 'sunday',
            'slascheduletableid' => 1,
        ];

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdateslaschedules = 0');

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
            'slascheduleid' => 1,
        ]);

        $this->assertFalse($obj->EditSubmit(1));

        $obj->_passChecks = true;
        $_POST['title'] = 1;
        $_POST['sladay']['sunday'] = 1;
        $_POST['dayHourOpen']['sunday'] = [1];
        $_POST['dayMinuteOpen']['sunday'] = [1];
        $_POST['dayHourClose']['sunday'] = [1];
        $_POST['dayMinuteClose']['sunday'] = [1];
        $_POST['rowId']['sunday'] = [1];
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

        $this->assertFalse($method->invoke($obj, 1, 1),
            'Returns false without slascheduleid');

        $obj->Database->method('QueryFetch')->willReturn([
            'slascheduleid' => 1,
            'monday_open' => 2,
            'sunday_open' => 1,
        ]);

        $obj->Database->Record = [
          'sladay' => 'sunday',
        ];

        $this->assertTrue($method->invoke($obj, 2, 1),
            'Returns true in edit mode');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_ScheduleMock
     */
    private function getController(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Admin\View_Schedule')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Admin\Controller_ScheduleMock', array_merge([
            'View' => $view,
        ], $services));
    }
}

class Controller_ScheduleMock extends Controller_Schedule
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
            return [
                'sunday' => [
                    'type' => 1,
                    'hours' => [
                        0 => [
                            0 => '1:1',
                            1 => '1:1',
                        ],
                    ],
                ],
            ];
        }

        return parent::RunChecks($_mode);
    }
}

