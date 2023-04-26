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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_HolidayTest
 * @group tickets
 */
class Controller_HolidayTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_Holiday', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListWorks()
    {
        $obj = $this->getController();

        $this->assertTrue($obj::DeleteList([1], true),
            'Returns true after deleting with admin_tcandeleteslaholidays = 1');

        $this->assertFalse($obj::DeleteList([], true),
            'Returns false after rendering with admin_tcandeleteslaholidays = 0');

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
            'Returns true with admin_tcanviewslaholidays = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true with admin_tcanviewslaholidays = 0');

        $this->assertClassNotLoaded($obj, 'Manage');
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

        $this->assertTrue($method->invoke($obj, 1, 1),
            'Returns true in insert mode');

        $_POST['title'] = 1;
        $_POST['iscustom'] = 1;
        $_POST['slaplans'] = [
            1 => 1,
            2 => 2,
        ];

        $this->assertTrue($method->invoke($obj, 2, 1),
            'Returns true in edit mode');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, 1);
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

        SWIFT::Set('isdemo', true);

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false in demo mode');

        SWIFT::Set('isdemo', false);

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with admin_tcaninsertslaholidays = 1');

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with admin_tcaninsertslaholidays = 0');

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
            'Returns true with admin_tcaninsertslaholidays = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertslaholidays = 0');

        $this->assertClassNotLoaded($obj, 'Insert');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetAssignedSlaPlanIdListThrowsException()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_GetAssignedSLAPlanIDList');
        $method->setAccessible(true);
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

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'slaholidayid' => 1,
        ]);

        $obj->_passChecks = true;
        $_POST['slaplans'] = [1 => 1];
        $_POST['title'] = 1;
        $_POST['iscustom'] = 1;
        $_POST['holidayday'] = 1;
        $_POST['holidaymonth'] = 1;
        $_POST['flagicon'] = 1;
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
            'slaholidayid' => 1,
        ]);

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdateslaholidays = 1');

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdateslaholidays = 0');

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
            'slaholidayid' => 1,
        ]);

        $this->assertFalse($obj->EditSubmit(1));

        $obj->_passChecks = true;
        $_POST['title'] = 1;
        $_POST['iscustom'] = 1;
        $_POST['holidayday'] = 1;
        $_POST['holidaymonth'] = 1;
        $_POST['flagicon'] = 1;
        $this->assertTrue($obj->EditSubmit(1));

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_HolidayMock
     */
    private function getController()
    {
        $view = $this->getMockBuilder('Tickets\Admin\View_Holiday')
            ->disableOriginalConstructor()
            ->getMock();

        $input = $this->getMockBuilder('SWIFT_Input')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Admin\Controller_HolidayMock', [
            'Input' => $input,
            'View' => $view,
        ]);
    }
}

class Controller_HolidayMock extends Controller_Holiday
{
    public $_passChecks = false;

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

