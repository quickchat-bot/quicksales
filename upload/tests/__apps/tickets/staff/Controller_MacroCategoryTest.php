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
use Tickets\Models\Macro\SWIFT_MacroCategory;

/**
 * Class Controller_MacroCategoryTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_MacroCategoryTest extends \SWIFT_TestCase
{
    public static $_next = 0;

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Staff\Controller_MacroCategory', $obj);
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
    public function testDeleteReplyListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj::DeleteReplyList([1]),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';
        $this->assertTrue($obj::DeleteReplyList([1]));

        $this->assertFalse($obj::DeleteReplyList([1]),
            'Returns false without permission');
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
    public function testDeleteReplyReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->DeleteReply(1));

        $this->assertClassNotLoaded($obj, 'DeleteReply', 1);
    }

    public function testLoadDisplayDataReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertClassNotLoaded($obj, '_LoadDisplayData');
    }

    public function testIndexReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Index());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Manage(1, 'no'),
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
        $_POST['parentcategoryid'] = 1;
        $_POST['categorytype'] = 1;
        \SWIFT::Set('isdemo', true);
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false in demo mode');
        \SWIFT::Set('isdemo', false);

        // advance permission
        \SWIFT::GetInstance()->Staff->GetPermission('admin_lscaninsertmacro');
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false without permission');

        \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn('1');

        $mock = $this->getMockBuilder('Tickets\Models\Macro\SWIFT_MacroCategory')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('GetIsClassLoaded')->willReturn(true);
        $this->assertTrue($method->invoke($obj, 2, $mock));

        $mock->method('GetMacroCategoryID')->willReturn(1);
        $this->assertFalse($method->invoke($obj, 2, $mock));

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
            'macrocategoryid' => 1,
            'title' => 'title',
        ]);

        $_POST['parentcategoryid'] = 1;
        $this->assertTrue($method->invoke($obj, 1, $filter));

        $_POST['parentcategoryid'] = 0;
        $this->assertTrue($method->invoke($obj, 2, $filter));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, $filter);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketpostid' => 1,
            'macrocategoryid' => 1,
            'title' => 'title',
        ]);

        $obj->doRunChecks = true;

        $_POST['criteriaoptions'] = 1;
        $_POST['filtertype'] = 1;
        $_POST['restrictstaffgroupid'] = 1;
        $_POST['rulecriteria'] = [1 => ['title', '=', 'title']];
        $_POST['title'] = 'title';
        $_POST['parentcategoryid'] = 1;

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
            'macrocategoryid' => 1,
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
            'macrocategoryid' => 1,
            'title' => 'title',
        ]);

        $_POST['criteriaoptions'] = 1;
        $_POST['filtertype'] = 1;
        $_POST['restrictstaffgroupid'] = 1;
        $_POST['rulecriteria'] = [1 => ['title', '=', 'title']];
        $_POST['title'] = 'title';
        $_POST['parentcategoryid'] = 1;

        $obj->doRunChecks = true;
        $this->assertTrue($obj->EditSubmit(1));

        $obj->doRunChecks = false;

        $this->assertFalse($obj->EditSubmit(1),
            'Returns false if checks fail');

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testQuickReplyFilterReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->Database->Record['macroreplyid'] = 1;
        $this->assertTrue($obj->QuickReplyFilter('category', 0));
        $this->assertTrue($obj->QuickReplyFilter('category', 1));
        $this->assertTrue($obj->QuickReplyFilter('other', 0));

        $this->assertClassNotLoaded($obj, 'QuickReplyFilter', '', 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_MacroCategoryMock
     */
    private function getMocked(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Staff\View_MacroCategory')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Staff\Controller_MacroCategoryMock', array_merge($services, [
            'View' => $view,
        ]));
    }
}

class Controller_MacroCategoryMock extends Controller_MacroCategory
{
    public $doRunChecks = -1;
    public $Database;

    protected function RunChecks($_mode, SWIFT_MacroCategory $_SWIFT_MacroCategoryObject = null)
    {
        return $this->doRunChecks === -1 ? parent::RunChecks($_mode, $_SWIFT_MacroCategoryObject) : $this->doRunChecks;
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

