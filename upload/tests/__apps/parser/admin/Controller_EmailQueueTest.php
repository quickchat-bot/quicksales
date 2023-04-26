<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Knowledgebase\Admin\LoaderMock;
use Parser\Library\EmailQueue\SWIFT_EmailQueueType_Tickets;
use SWIFT_Exception;

/**
 * Class Controller_EmailQueueTest
 * @group parser
 * @group parser-admin
 */
class Controller_EmailQueueTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\Controller_EmailQueue', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->DeleteList([]),
            'Returns false');

        $this->assertTrue($obj->DeleteList([], true),
            'Returns true');

        $this->assertFalse($obj->DeleteList([], true),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEnableListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->EnableList([]),
            'Returns false');

        $this->assertTrue($obj->EnableList([1], true),
            'Returns true');

        $this->assertFalse($obj->EnableList([], true),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDisableListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->DisableList([]),
            'Returns false');

        $this->assertTrue($obj->DisableList([1], true),
            'Returns true');

        $this->assertFalse($obj->DisableList([], true),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Manage(),
            'Returns true');

        $this->assertTrue($obj->Manage(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Manage');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $method = $this->getMethod(Controller_EmailQueueMock::class, 'RunChecks');

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['csrfhash'] = 'csrfhash';
        $_SWIFT->Database->Record = ['email' => 'test@test.com'];
        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['email'] = 'test@test.com';

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['email'] = 'test2@test.com';
        $_POST['customfromname'] = '    ';

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        unset($_POST['customfromname']);
        $_POST['customfromemail'] = 'dummy';

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        unset($_POST['customfromemail']);
        $_POST['fetchtype'] = 'pop3';
        $_POST['host'] = 'pop3.test.com';
        $_POST['username'] = 'testuser';
        $_POST['port'] = '  ';

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['port'] = 465;
        $_POST['prefix'] = '@)$';

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        unset($_POST['prefix']);
        \SWIFT::Set('isdemo', true);

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        \SWIFT::Set('isdemo', false);

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturn('1');

        $_SWIFT->Staff = $mockStaff;

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'staffcache')
                return [
                    1 => ['email' => 'test2@test.com']
                ];
            if ($x == 'templategroupcache')
                return [1 => []];
            if ($x == 'departmentcache')
                return [1 => []];
            if ($x == 'statuscache')
                return [1 => []];
            if ($x == 'prioritycache')
                return [1 => []];
            if ($x == 'tickettypecache')
                return [1 => []];
        };

        $_POST['templategroupid'] = 1;

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['email'] = 'test3@test.com';
        $_POST['type'] = APP_TICKETS;

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['departmentid'] = 1;
        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['tickettypeid'] = 1;

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['ticketstatusid'] = 1;

        $this->assertFalse($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns false');

        $_POST['ticketpriorityid'] = 1;


        $this->assertTrue($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns true');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, SWIFT_UserInterface::MODE_INSERT);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Insert(),
            'Returns true');

        $this->assertTrue($obj->Insert(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Insert');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function test_RenderConfirmationReturnsTrue()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod(Controller_EmailQueueMock::class, '_RenderConfirmation');

        $_POST['type'] = APP_TICKETS;

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => []
            ];
        };

        $this->assertTrue($method->invoke($obj, SWIFT_UserInterface::MODE_EDIT),
            'Returns true');

        $_POST['departmentid'] = 1;
        $_POST['tickettypeid'] = 1;
        $_POST['ticketstatusid'] = 1;
        $_POST['ticketpriorityid'] = 1;

        $this->assertTrue($method->invoke($obj, SWIFT_UserInterface::MODE_INSERT),
            'Returns true');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, SWIFT_UserInterface::MODE_EDIT);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertStepReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->InsertStep(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'InsertStep');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['email'] = 'test2@test.com';
        $_POST['fetchtype'] = 'pipe';
        $_POST['templategroupid'] = 1;
        $_POST['type'] = APP_NEWS;
        $_POST['registrationrequired'] = 1;

        $_SWIFT->Database->method('QueryFetch')
            ->willReturn([
                'emailqueueid' => 1,
                'queuesignatureid' => 1,
                'type' => APP_NEWS
            ]);
        $_SWIFT->Database->Record = ['email' => 'test@test.com'];

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'templategroupcache')
                return [
                    1 => []
                ];
            if ($x == 'staffcache')
                return [1 => ['email' => 'testing@test.com']];
        };

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturn('1', '1', '0', '1');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);

        $_SWIFT->Staff = $mockStaff;

        $this->assertTrue($obj->InsertSubmit(),
            'Returns true');

        $_POST['fetchtype'] = 'pop3';
        $_POST['usequeuesmtp'] = '1';
        $_POST['host'] = 'mail.test.com';
        $_POST['port'] = '465';
        $_POST['username'] = 'testuser';
        $_POST['smtptype'] = 'ssl';
        $_POST['type'] = APP_BACKEND;

        $this->assertTrue($obj->InsertSubmit(),
            'Returns true');

        $this->assertFalse($obj->InsertSubmit(),
            'Returns false');

        $_POST['type'] = 'dummy';

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->InsertSubmit();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertSubmitClassNotLoaded()
    {
        $obj = $this->getMocked();

        $this->assertClassNotLoaded($obj, 'InsertSubmit');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function test_GetMailQueueTypeObjectReturnsEmailQueueType()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod(Controller_EmailQueueMock::class, '_GetMailQueueTypeObject');

        $_POST['type'] = APP_TICKETS;
        $_POST['templategroupid'] = 1;
        $_POST['departmentid'] = 1;
        $_POST['tickettypeid'] = 1;
        $_POST['ticketpriorityid'] = 1;
        $_POST['ticketstatusid'] = 1;
        $_POST['ticketautoresponder'] = 1;

        $this->assertInstanceOf(SWIFT_EmailQueueType_Tickets::class, $method->invoke($obj),
            'Returns email queue type');

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $method->invoke($obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')
            ->willReturn([
                'catchallruleid' => 1,
                'emailqueueid' => 1,
                'fetchtype' => 'pipe',
                'queuesignatureid' => 1,
                'type' => APP_NEWS
            ]);

        $this->assertTrue($obj->Edit(1),
            'Returns true');

        $this->assertTrue($obj->Edit(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Edit', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditThrowsInvalidData()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->Edit('');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditThrowsInvalidData2()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->Edit(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['email'] = 'test2@test.com';
        $_POST['fetchtype'] = 'pipe';
        $_POST['templategroupid'] = 1;
        $_POST['type'] = APP_NEWS;

        $_SWIFT->Database->Record = ['email' => 'test@test.com'];

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'templategroupcache')
                return [
                    1 => []
                ];
            if ($x == 'staffcache')
                return [1 => ['email' => 'testing@test.com']];
        };

        $data = [
            'catchallruleid' => 1,
            'emailqueueid' => 1,
            'fetchtype' => 'pipe',
            'queuesignatureid' => 1,
            'userpassword' => 'dummy',
            'type' => APP_NEWS
        ];

        \SWIFT::GetInstance()->Database->method('QueryFetch')
            ->willReturnCallback(function ($x) use (&$data) {
                return $data;
            });
        \SWIFT::GetInstance()->Database->Record = ['catchallruleid' => 1];

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturn('1', '1', '0', '1');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);

        $_SWIFT->Staff = $mockStaff;

        $this->assertTrue($obj->EditSubmit(1),
            'Returns true');

        $_POST['fetchtype'] = 'pop3';
        $_POST['usequeuesmtp'] = '1';
        $_POST['host'] = 'mail.test.com';
        $_POST['port'] = '465';
        $_POST['username'] = 'testuser';
        $_POST['smtptype'] = 'ssl';
        $_POST['userpassword'] = 'dummy2';

        $this->assertTrue($obj->EditSubmit(1),
            'Returns true');

        $this->assertFalse($obj->EditSubmit(1),
            'Returns false');

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $_POST['type'] = 'dummy';
        $obj->EditSubmit(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitClassNotLoaded()
    {
        $obj = $this->getMocked();

        $this->assertClassNotLoaded($obj, 'EditSubmit', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitThrowsInvalidData()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->EditSubmit('');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEditSubmitThrowsInvalidData2()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->EditSubmit(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testVerifyConnectionReturnsTrue()
    {
        $obj = $this->getMocked();

        $data = [
            0 => 'host=mail.test.com',
            1 => 'port=465',
            2 => 'username=testuser',
            3 => 'userpassword=testpassword',
            4 => 'fetchtype=pop3',
        ];


        $chunks = base64_encode(implode('&', $data));

        // TODO: we need to implement real mocks for these services,
        // right now it just times out and makes the tests take longer
//        $this->assertTrue($obj->VerifyConnection($chunks),
//            'Returns true');
//
//        $data[4] = 'fetchtype=pipe';
//        $chunks = base64_encode(implode('&', $data));
//
//        $this->assertTrue($obj->VerifyConnection($chunks),
//            'Returns true');
//
//        $this->assertTrue($obj->VerifyConnection(''),
//            'Returns true');

        $this->assertClassNotLoaded($obj, 'VerifyConnection', '');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_EmailQueueMock
     */
    private function getMocked()
    {
        $mockView = $this->getMockBuilder(View_EmailQueue::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockView->method('RenderGrid')->willReturn(true);

        $mockView->method('Render')->willReturn(true);

        $inputMock = $this->getMockBuilder(\SWIFT_Input::class)
            ->disableOriginalConstructor()
            ->getMock();

        $inputMock->method('SanitizeForXSS')->willReturn('');

        return $this->getMockObject('Parser\Admin\Controller_EmailQueueMock', ['View' => $mockView, 'Input' => $inputMock]);
    }
}

class Controller_EmailQueueMock extends Controller_EmailQueue
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

