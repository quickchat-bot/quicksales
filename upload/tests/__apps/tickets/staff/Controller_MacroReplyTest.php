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
 * Class Controller_MacroReplyTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_MacroReplyTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Staff\Controller_MacroReply', $obj);
    }

    public function testLoadDisplayDataReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertClassNotLoaded($obj, '_LoadDisplayData');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Insert(),
            'Returns true with staff_tcaninsertmacro = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true with staff_tcaninsertmacro = 0');

        $this->assertClassNotLoaded($obj, 'Insert');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertTicketReturnsTrue()
    {
        $obj = $this->getMocked();

        $post = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_TicketPost')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertTrue($obj->InsertTicket($post, 1, '', 1, 1, 1),
            'Returns true with staff_tcaninsertmacro = 1');

        $this->assertTrue($obj->InsertTicket($post, 1, '', 1, 1, 1),
            'Returns true with staff_tcaninsertmacro = 0');

        $this->assertClassNotLoaded($obj, 'InsertTicket', $post, 1, '', 1, 1, 1);
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

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'macrocategoryid' => 1,
            'title' => 'title',
        ]);

        $_POST['macrocategoryid'] = 1;
        $this->assertTrue($method->invoke($obj, 1));

        $_POST['macrocategoryid'] = 0;
        $this->assertTrue($method->invoke($obj, 2));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
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

        $_POST['macrocategoryid'] = 1;
        $_POST['title'] = 1;
        $_POST['replycontents'] = 1;
        $_POST['departmentid'] = 1;
        $_POST['ownerstaffid'] = 1;
        $_POST['tickettypeid'] = 1;
        $_POST['ticketstatusid'] = 1;
        $_POST['ticketpriorityid'] = 1;

        $_POST['tredir_ticketid'] = 1;
        $this->assertTrue($obj->InsertSubmit());
        $_POST['tredir_listtype'] = 'viewticket';
        $this->assertTrue($obj->InsertSubmit());
        $_POST['tredir_addkb'] = 1;
        $_POST['tredir_ticketpostid'] = 1;
        $this->assertTrue($obj->InsertSubmit());

        $_POST['tredir_ticketid'] = 0;
        $this->assertTrue($obj->InsertSubmit());

        $obj->doRunChecks = false;

        $this->assertFalse($obj->InsertSubmit());

        $_POST['tredir_ticketpostid'] = 1;
        $this->assertFalse($obj->InsertSubmit());

        unset($_POST['tredir_ticketpostid']);
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
            'macroreplyid' => 1,
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
            'macroreplyid' => 1,
            'macroreplydataid' => 1,
            'macrocategoryid' => 1,
            'title' => 'title',
        ]);

        $_POST['macrocategoryid'] = 1;
        $_POST['title'] = 1;
        $_POST['replycontents'] = 1;
        $_POST['departmentid'] = 1;
        $_POST['ownerstaffid'] = 1;
        $_POST['tickettypeid'] = 1;
        $_POST['ticketstatusid'] = 1;
        $_POST['ticketpriorityid'] = 1;

        $obj->doRunChecks = true;
        $this->assertTrue($obj->EditSubmit(1));

        $obj->doRunChecks = false;

        $this->assertFalse($obj->EditSubmit(1),
            'Returns false if checks fail');

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
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
        $_POST['macrocategoryid'] = 1;
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without replycontents');

        $_POST['replycontents'] = 'replycontents';
        \SWIFT::Set('isdemo', true);
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false in demo mode');

        \SWIFT::Set('isdemo', false);

        // advance permission
        \SWIFT::GetInstance()->Staff->GetPermission('admin_lscaninsertmacro');
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false without permission');

        \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn('1');

        $this->assertTrue($method->invoke($obj, 2));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_MacroReplyMock
     */
    private function getMocked(array $services = [])
    {
        $view = $this->getMockBuilder('Tickets\Staff\View_MacroReply')
            ->disableOriginalConstructor()
            ->getMock();

        return $this->getMockObject('Tickets\Staff\Controller_MacroReplyMock', array_merge($services, [
            'View' => $view,
        ]));
    }
}

class Controller_MacroReplyMock extends Controller_MacroReply
{
    public $doRunChecks = -1;


    protected function RunChecks($_mode)
    {
        return $this->doRunChecks === -1 ? parent::RunChecks($_mode) : $this->doRunChecks;
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
