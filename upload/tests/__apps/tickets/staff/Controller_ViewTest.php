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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_ViewTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_ViewTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Staff\Controller_View', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj::DeleteList([1]),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';
        $this->assertTrue($obj::DeleteList([1]));

        $this->assertFalse($obj::DeleteList([1]),
            'Returns false without permission');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListShowsAlert()
    {
        $_SWIFT = \SWIFT::GetInstance();
        $obj = $this->getMocked();

        $_SWIFT->Database->Record = [
            'ticketviewid' => 1,
            'ismaster' => 1,
            'title' => 'title',
        ];
        $_POST['csrfhash'] = 'csrfhash';
        $this->assertTrue($obj::DeleteList([1]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete(1));

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Manage(),
            'Returns true with staff_tcanviewmacro = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true with staff_tcanviewmacro = 0');

        $this->assertClassNotLoaded($obj, 'Manage');
    }

    /**
     * @throws \ReflectionException
     * @throws SWIFT_Exception
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('RunChecks');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without title');

        $_POST['title'] = 'title';
        $_POST['ticketsperpage'] = 0;
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false with tickets
 * @group tickets-staffperpage = 0');

        $_POST['ticketsperpage'] = 1;
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false without viewtype');

        $_POST['viewtype'] = [
            'viewunassigned',
            'viewassigned',
            'alltickets',
        ];

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false without viewfields');

        $_POST['viewfields'] = [1 => [1]];
        \SWIFT::Set('isdemo', true);
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false if is demo');

        \SWIFT::Set('isdemo', false);
        /// advance permission
        \SWIFT::GetInstance()->Staff->GetPermission('staff_tcaninsertview');
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false without permission');

        \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn('1');
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
        $obj = $this->getMocked();

        $this->assertTrue($obj->Insert(),
            'Returns true with staff_tcaninsertfilter = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true with staff_tcaninsertfilter = 0');

        $this->assertClassNotLoaded($obj, 'Insert');
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderConfirmationReturnsTrue()
    {
        $obj = $this->getMocked();

        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_RenderConfirmation');
        $method->setAccessible(true);

        $filter = $this->getMockBuilder('Tickets\Models\Filter\SWIFT_TicketFilter')
            ->disableOriginalConstructor()
            ->getMock();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketviewid' => 1,
            'title' => 'title',
        ]);

        $_POST['viewscope'] = 1;
        $_POST['title'] = 'title';
        $_POST['viewtype'] = [
            'viewunassigned',
            'viewassigned',
            'alltickets',
        ];
        $this->assertTrue($method->invoke($obj, 1, $filter));

        $_POST['parentcategoryid'] = 0;
        $this->assertTrue($method->invoke($obj, 2, $filter));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, $filter);
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessPostVariablesReturnsArray()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_ProcessPOSTVariables');
        $method->setAccessible(true);

        $_POST['viewtype'] = [
            'viewunassigned',
            'viewassigned',
            'alltickets',
        ];

        $_POST['viewfields'] = [1 => [1]];
        $_POST['linkdepartmentid'] = [1];
        $_POST['filtertickettypeid'] = [1];
        $_POST['filterticketstatusid'] = [1];
        $_POST['filterticketpriorityid'] = [1];
        $_POST['filterdepartmentid'] = [1];
        $this->assertNotEmpty($method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketpostid' => 1,
            'ticketviewid' => 1,
            'title' => 'title',
        ]);

        $obj->doRunChecks = true;

        $_POST['viewtype'] = [
            'viewunassigned',
            'viewassigned',
            'alltickets',
        ];
        $_POST['viewfields'] = [1 => 1];
        $_POST['linkdepartmentid'] = [1];
        $_POST['filtertickettypeid'] = [1];
        $_POST['filterticketstatusid'] = [1];
        $_POST['filterticketpriorityid'] = [1];
        $_POST['filterdepartmentid'] = [1];

        $_POST['title'] = 1;
        $_POST['viewscope'] = 1;
        $_POST['sortby'] = 1;
        $_POST['sortorder'] = 1;
        $_POST['ticketsperpage'] = 1;
        $_POST['autorefresh'] = 1;
        $_POST['setasowner'] = 1;
        $_POST['defaultstatusonreply'] = 1;
        $_POST['afterreplyaction'] = 1;

        $this->assertTrue($obj->InsertSubmit());

        $obj->doRunChecks = false;

        $this->assertFalse($obj->InsertSubmit());

        $this->assertClassNotLoaded($obj, 'InsertSubmit');
    }

    public function testEditThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'Edit', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketviewid' => 1,
            'staffid' => 1,
            'viewscope' => 1,
            'title' => 'title',
        ]);

        $this->assertTrue($obj->Edit(1),
            'Returns true with staff_tcanupdatemacro = 1');

        $this->assertTrue($obj->Edit(1),
            'Returns true with staff_tcanupdatemacro = 0');

        $this->assertClassNotLoaded($obj, 'Edit', 1);
    }

    public function testEditSubmitThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertInvalidData($obj, 'EditSubmit', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketviewid' => 1,
            'staffid' => 1,
            'viewscope' => 1,
            'title' => 'title',
        ]);

        $_POST['viewtype'] = [
            'viewunassigned',
            'viewassigned',
            'alltickets',
        ];
        $_POST['viewfields'] = [1 => 1];
        $_POST['linkdepartmentid'] = [1];
        $_POST['filtertickettypeid'] = [1];
        $_POST['filterticketstatusid'] = [1];
        $_POST['filterticketpriorityid'] = [1];
        $_POST['filterdepartmentid'] = [1];

        $_POST['title'] = 1;
        $_POST['viewscope'] = 1;
        $_POST['sortby'] = 1;
        $_POST['sortorder'] = 1;
        $_POST['ticketsperpage'] = 1;
        $_POST['autorefresh'] = 1;
        $_POST['setasowner'] = 1;
        $_POST['defaultstatusonreply'] = 1;
        $_POST['afterreplyaction'] = 1;

        $obj->doRunChecks = true;
        $this->assertTrue($obj->EditSubmit(1));

        $obj->doRunChecks = false;

        $this->assertFalse($obj->EditSubmit(1),
            'Returns false if checks fail');

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_ViewMock
     */
    private function getMocked(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Staff\View_View')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Staff\Controller_ViewMock', array_merge($services, [
            'View' => $view,
        ]));
    }
}

class Controller_ViewMock extends Controller_View
{
    public $doRunChecks = -1;
    public $Database;

    protected function RunChecks($_mode, $_ticketFileTypeID = 0)
    {
        return $this->doRunChecks === -1 ? parent::RunChecks($_mode, $_ticketFileTypeID) : $this->doRunChecks;
    }

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

