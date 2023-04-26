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
 * Class Controller_PriorityTest
 * @group tickets
 */
class Controller_PriorityTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_Priority', $obj);
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
            'Returns true with admin_tcanupdatepriority = 1');

        $this->assertFalse($obj::SortList([]),
            'Returns false with admin_tcanupdatepriority = 0');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListWorks()
    {
        $obj = $this->getController();

        \SWIFT::GetInstance()->Database->Record = [
            'ismaster' => 0,
            'priorityid' => 1,
            'title' => 1,
        ];

        $this->assertTrue($obj::DeleteList([1], true),
            'Returns true after deleting with admin_tcandeletepriority = 1');

        $this->assertFalse($obj::DeleteList([], true),
            'Returns false after rendering with admin_tcandeletepriority = 0');

        $db = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $db->Record = [
            'ismaster' => 1,
            'priorityid' => 1,
            'title' => 1,
        ];
        $db->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false);

        \SWIFT::GetInstance()->Database = $db;

        \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn(1);

        $this->assertTrue($obj::DeleteList([1], true),
            'Returns true if ismaster = 1');

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
            'Returns true with admin_tcanviewpriorities = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true with admin_tcanviewpriorities = 0');

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
        $_POST['displayorder'] = 1;
        $_POST['type'] = 1;

        SWIFT::Set('isdemo', true);

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false in demo mode');

        SWIFT::Set('isdemo', false);

        $_POST['frcolorcode'] = 'white';

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without frcolorcode');

        $_POST['frcolorcode'] = '#ffffff';
        $_POST['bgcolorcode'] = 'black';

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without bgcolorcode');

        $_POST['bgcolorcode'] = '#ffffff';

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with admin_tcaninsertpriority = 1');

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with admin_tcaninsertpriority = 0');

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
            'Returns true with admin_tcaninsertpriority = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true with admin_tcaninsertpriority = 0');

        $this->assertClassNotLoaded($obj, 'Insert');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetAssignedUserGroupIdListThrowsException()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_GetAssignedUserGroupIDList');
        $method->setAccessible(true);

        $_POST['usergroupidlist'] = [1];
        $this->assertCount(1, $method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->assertEmpty($method->invoke($obj));
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

        $this->assertTrue($method->invoke($obj, 1));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
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
        $_POST['title'] = 1;
        $_POST['displayorder'] = 1;
        $_POST['type'] = 1;
        $_POST['frcolorcode'] = '#000000';
        $_POST['bgcolorcode'] = '#000000';
        $_POST['uservisibilitycustom'] = 1;
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
            'priorityid' => 1,
        ]);

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdatepriority = 1');

        $this->assertTrue($obj->Edit(1),
            'Returns true with admin_tcanupdatepriority = 0');

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
            'priorityid' => 1,
        ]);

        $this->assertFalse($obj->EditSubmit(1));

        $obj->_passChecks = true;
        $_POST['title'] = 1;
        $_POST['displayorder'] = 1;
        $_POST['type'] = 1;
        $_POST['frcolorcode'] = '#000000';
        $_POST['bgcolorcode'] = '#000000';
        $_POST['uservisibilitycustom'] = 1;
        $this->assertTrue($obj->EditSubmit(1));

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_PriorityMock
     */
    private function getController()
    {
        $view = $this->getMockBuilder('Tickets\Admin\View_Priority')
            ->disableOriginalConstructor()
            ->getMock();

        $lpl = $this->getMockBuilder('Base\Library\Language\SWIFT_LanguagePhraseLinked')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Admin\Controller_PriorityMock', [
            'View' => $view,
            'LanguagePhraseLinked' => $lpl,
        ]);
    }
}

class Controller_PriorityMock extends Controller_Priority
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
