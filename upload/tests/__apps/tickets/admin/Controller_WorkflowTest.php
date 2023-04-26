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
 * Class Controller_WorkflowTest
 * @group tickets
 */
class Controller_WorkflowTest extends \SWIFT_TestCase
{
    public static $_next = false;

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_Workflow', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSortListWorks()
    {
        $obj = $this->getController();

        $this->assertFalse($obj::SortList([]),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';

        $this->assertTrue($obj::SortList([1]),
            'Returns true with admin_tcanupdateworkflow = 1');

        $this->assertFalse($obj::SortList([]),
            'Returns false with admin_tcanupdateworkflow = 0');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListWorks()
    {
        $obj = $this->getController();

        $this->assertTrue($obj::DeleteList([1], true),
            'Returns true after deleting with admin_tcandeleteautoclose = 1');

        $this->assertFalse($obj::DeleteList([], true),
            'Returns false after rendering with admin_tcandeleteautoclose = 0');

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
            'Returns true after deleting with admin_tcanupdateautoclose = 1');

        $this->assertFalse($obj::EnableList([], true),
            'Returns false after rendering with admin_tcanupdateautoclose = 0');

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
            'Returns true after deleting with admin_tcanupdateautoclose = 1');

        $this->assertFalse($obj::DisableList([], true),
            'Returns false after rendering with admin_tcanupdateautoclose = 0');

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
            'Returns true with admin_tcanviewbayescategories = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true with admin_tcanviewbayescategories = 0');

        $this->assertClassNotLoaded($obj, 'Manage');
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderConfirmationThrowsException()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_RenderConfirmation');
        $method->setAccessible(true);

        $mock = $this->getMockBuilder('Tickets\Models\Workflow\SWIFT_TicketWorkflow')
            ->disableOriginalConstructor()
            ->getMock();

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);

        $method->invoke($obj, $mock, 1);
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

        $mock = $this->getMockBuilder('Tickets\Models\Workflow\SWIFT_TicketWorkflow')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('GetIsClassLoaded')->willReturn(true);

        $db = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('NextRecord')->willReturnCallback(function () {
            self::$_next = !self::$_next;

            return self::$_next;
        });
        $db->Record = [
            'name' => 'creator',
            'rulematch' => '1',
            'staffid' => '1',
        ];
        $obj->Database = $db;
        SWIFT::GetInstance()->Database = $db;
        $this->assertTrue($method->invoke($obj, $mock, 1),
            'Returns true in insert mode');
        $this->assertTrue($method->invoke($obj, $mock, 2),
            'Returns true in edit mode');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $mock, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_RunChecks');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with empty POST');

        $_POST['title'] = 1;
        $_POST['sortorder'] = 1;
        $_POST['ruleoptions'] = 1;
        $_POST['rulecriteria'] = [1 => [1]];
        $_POST['staffid'] = -2;

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with empty actions');

        $_POST['departmentid'] = 1;
        $_POST['trashticket'] = 1;
        $_POST['bayescategoryid'] = 1;
        $_POST['tickettypeid'] = 1;
        $_POST['newslaplanid'] = 1;
        $_POST['flagtype'] = 1;
        $_POST['staffid'] = 1;
        $_POST['priorityid'] = 1;
        $_POST['ticketstatusid'] = 1;
        $_POST['notes'] = 'notes';
        $_POST['taginput_addtags'] = '1';
        $_POST['taginput_removetags'] = '1';

        SWIFT::Set('isdemo', true);

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false in demo mode');

        SWIFT::Set('isdemo', false);

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with admin_tcaninsertworkflow = 1');

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with admin_tcaninsertworkflow = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessFormRuleActionsThrowsException()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_ProcessFormRuleActions');
        $method->setAccessible(true);

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
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
        $obj = $this->getController();

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertautoclose = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertautoclose = 0');

        $this->assertClassNotLoaded($obj, 'Insert');
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
        $_POST['isenabled'] = 1;
        $_POST['sortorder'] = 1;
        $_POST['ruleoptions'] = 1;
        $_POST['rulecriteria'] = [1];
        $_POST['staffvisibilitycustom'] = 1;
        $_POST['staffgroupidlist'] = [1 => 1];
        $_POST['staffid'] = 1;
        $_POST['notifications'] = [1 => [0 => 'user', 1 => '2', 2 => '3']];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketworkflowid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
            'title' => 1,
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
            'ticketworkflowid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
        ]);

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdateautoclose = 1');

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdateautoclose = 0');

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
            'ticketworkflowid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
        ]);

        $this->assertFalse($obj->EditSubmit(1));

        $obj->_passChecks = true;
        $_POST['title'] = 1;
        $_POST['isenabled'] = 1;
        $_POST['sortorder'] = 1;
        $_POST['ruleoptions'] = 1;
        $_POST['rulecriteria'] = [1];
        $_POST['staffvisibilitycustom'] = 1;
        $_POST['staffgroupidlist'] = [1 => 1];
        $_POST['staffid'] = 1;
        $_POST['notifications'] = [1 => [0 => 'user', 1 => '2', 2 => '3']];

        $this->assertTrue($obj->EditSubmit(1));

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetAssignedStaffGroupIdListReturnsArray()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_GetAssignedStaffGroupIDList');
        $method->setAccessible(true);

        $_POST['staffgroupidlist'] = [1];
        $this->assertCount(1, $method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->assertEmpty($method->invoke($obj));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetNotificationContainerReturnsArray()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_GetNotificationContainer');
        $method->setAccessible(true);

        $_POST['notifications'] = [1 => [0 => 'user', 1 => '2', 2 => '3']];
        $this->assertCount(1, $method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_WorkflowMock
     */
    private function getController()
    {
        $view = $this->getMockBuilder('Tickets\Admin\View_Workflow')
            ->disableOriginalConstructor()
            ->getMock();

        $mgr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mgr->method('Check')->willReturn([true, true]);

        return $this->getMockObject('Tickets\Admin\Controller_WorkflowMock', [
            'View' => $view,
            'CustomFieldManager' => $mgr,
        ]);
    }
}

class Controller_WorkflowMock extends Controller_Workflow
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

    /**
     * @param $_mode
     * @return bool
     * @throws SWIFT_Exception
     */
    protected function _RunChecks($_mode)
    {
        if ($this->_passChecks) {
            return true;
        }

        return parent::_RunChecks($_mode);
    }
}

