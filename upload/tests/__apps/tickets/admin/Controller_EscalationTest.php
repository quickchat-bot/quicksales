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
 * Class Controller_EscalationTest
 * @group tickets
 */
class Controller_EscalationTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_Escalation', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListWorks()
    {
        $obj = $this->getController();

        $this->assertTrue($obj::DeleteList([1], true),
            'Returns true after deleting with admin_tcandeleteescalations = 1');

        $this->assertFalse($obj::DeleteList([], true),
            'Returns false after rendering with admin_tcandeleteescalations = 0');

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
            'Returns true with admin_tcanviewescalations = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true with admin_tcanviewescalations = 0');

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
        $_POST['slaplanid'] = 1;

        SWIFT::Set('isdemo', true);

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false in demo mode');

        SWIFT::Set('isdemo', false);

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with admin_tcaninsertescalations = 1');

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with admin_tcaninsertescalations = 0');

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
            'Returns true with admin_tcaninsertescalations = 1');

        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $cache->method('Get')->willReturn(false);

        $obj->Cache = $cache;

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertescalations = 0');

        $this->assertClassNotLoaded($obj, 'Insert');
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

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true in insert mode');

        $_POST['title'] = 1;
        $_POST['staffid'] = 1;
        $_POST['slaplanid'] = 1;
        $_POST['flagtype'] = 1;
        $_POST['newslaplanid'] = 1;
        $_POST['departmentid'] = 1;
        $_POST['priorityid'] = 1;
        $_POST['tickettypeid'] = 1;
        $_POST['taginput_addtags'] = '1';
        $_POST['taginput_removetags'] = '1';

        $this->assertTrue($method->invoke($obj, 2),
            'Returns true in edit mode');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetNotificationContainerThrowsException()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_GetNotificationContainer');
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
            'escalationruleid' => 1,
        ]);

        $obj->_passChecks = true;
        $_POST['notifications'] = [1 => [0 => 'user', 1 => '2', 2 => '3']];
        $_POST['title'] = 1;
        $_POST['slaplanid'] = 1;
        $_POST['staffid'] = 1;
        $_POST['ruletype'] = 1;
        $_POST['tickettypeid'] = 1;
        $_POST['priorityid'] = 1;
        $_POST['ticketstatusid'] = 1;
        $_POST['departmentid'] = 1;
        $_POST['flagtype'] = 1;
        $_POST['newslaplanid'] = 1;

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
            'escalationruleid' => 1,
        ]);

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdateescalations = 1');

        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $cache->method('Get')->willReturn(false);

        $obj->Cache = $cache;

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdateescalations = 0');

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
            'escalationruleid' => 1,
        ]);

        $this->assertFalse($obj->EditSubmit(1));

        $obj->_passChecks = true;
        $_POST['title'] = 1;
        $_POST['slaplanid'] = 1;
        $_POST['staffid'] = 1;
        $_POST['ruletype'] = 1;
        $_POST['tickettypeid'] = 1;
        $_POST['priorityid'] = 1;
        $_POST['ticketstatusid'] = 1;
        $_POST['departmentid'] = 1;
        $_POST['flagtype'] = 1;
        $_POST['newslaplanid'] = 1;

        $this->assertTrue($obj->EditSubmit(1));

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_EscalationMock
     */
    private function getController()
    {
        $view = $this->getMockBuilder('Tickets\Admin\View_Escalation')
            ->disableOriginalConstructor()
            ->getMock();

        $flag = $this->getMockBuilder('Tickets\Library\Flag\SWIFT_TicketFlag')
            ->disableOriginalConstructor()
            ->getMock();
        $flag->method('GetFlagList')->willReturn([
            1 => '1',
        ]);

        return $this->getMockObject('Tickets\Admin\Controller_EscalationMock', [
            'View' => $view,
            'TicketFlag' => $flag,
        ]);
    }
}

class Controller_EscalationMock extends Controller_Escalation
{
    public $_passChecks = false;

    public $Cache;

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

