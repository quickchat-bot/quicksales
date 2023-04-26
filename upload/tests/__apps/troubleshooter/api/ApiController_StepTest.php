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

namespace Troubleshooter\Api;

use Troubleshooter\Staff\LoaderMock;

/**
 * Class ApiController_StepTest
 * @group troubleshooter
 */
class ApiController_StepTest extends \SWIFT_TestCase
{
    private static $_next = false;

    public function setUp()
    {
        parent::setUp();

        // reset test data
        unset($_POST);
    }

    /**
     * @return Controller_Step
     * @throws \SWIFT_Exception
     */
    public function getController()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Insert_ID')->willReturn(1);

        $mockDb->method('NextRecord')
            ->willReturnCallback(function () {
                self::$_next = !self::$_next;

                return self::$_next;
            });

        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "staff.staffid = '2'")) {
                return false;
            }

            return [
                'troubleshootercategoryid' => 1,
                'troubleshooterstepid' => 1,
                'staffid' => '1',
                'fullname' => 'fullname',
                'title' => 'title',
                'subject' => 'subject',
                'contents' => 'contents',
                'categorytype' => '1',
                'displayorder' => '0',
                'allowcomments' => '1',
                'redirecttickets' => '1',
                'redirectdepartmentid' => '1',
                'tickettypeid' => '1',
                'priorityid' => '1',
                'ticketsubject' => 'subject',
                'uservisibilitycustom' => '1',
                'staffvisibilitycustom' => '1',
            ];
        });

        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false === strpos($x, "swtroubleshootersteps.troubleshooterstepid  = '1'") &&
                false === strpos($x, "linktypeid = '1'") &&
                false === strpos($x, 'childtroubleshooterstepid IN') &&
                false === strpos($x, 'troubleshooterstepid IN') &&
                false === strpos($x, "parenttroubleshooterstepid = '1'")) {
                self::$_next = true;
            }

            return true;
        });

        $this->mockProperty($mockDb, 'Record', [
            'troubleshootercategoryid' => 1,
            'troubleshooterstepid' => 1,
            'childtroubleshooterstepid' => 1,
            'title' => 'title',
            'hasattachments' => '1',
            'parentstepidlist' => '1',
            'attachmentid' => '1',
            'filename' => 'file.txt',
        ]);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls(1, 0);
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetProperty')->willReturnArgument(1);

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->method('GetIsClassLoaded')->willReturn(true);
        $mockSession->method('GetProperty')->willReturnArgument(0);
        $mockSession->method('GetSessionID')->willReturn(1);

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceClient')
            ->disableOriginalConstructor()
            ->setMethods([
                'DisplayError',
                'Header',
                'Footer',
                'Error',
                'CheckFields',
                'AddNavigationBox',
            ])
            ->getMock();

        $mockView = $this->getMockBuilder('SWIFT_View')
            ->disableOriginalConstructor()
            ->setMethods([
                'RenderGrid',
                'Render',
                'RenderViewAll',
                'RenderQuickFilterTree',
                'RenderTabs',
                'RenderNewStepDialog',
                'RenderInfoBox',
                'RenderViewSteps',
            ])
            ->getMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            return ($x === 'charset') ? 'UTF-8' : '%s';
        });

        \SWIFT::GetInstance()->Database = $mockDb;
        \SWIFT::GetInstance()->Staff = $mockStaff;
        \SWIFT::GetInstance()->Session = $mockSession;
        \SWIFT::GetInstance()->Language = $mockLang;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')
            ->willReturnOnConsecutiveCalls([1 => [1]], false);

        \SWIFT::GetInstance()->Cache = $mockCache;

        $cmgrMock = $this->getMockBuilder('Base\Library\Comment\SWIFT_CommentManager')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['ProcessPOSTStaff'])
            ->getMock();

        $mgr = $this->getMockBuilder('SWIFT_RESTManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mgr->method('Authenticate')->willReturn(true);

        $svr = $this->getMockBuilder('SWIFT_RESTServer')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['DispatchStatus', 'GetVariableContainer', 'Get'])
            ->getMock();

        $svr->method('GetVariableContainer')->willReturn(['salt' => 'salt']);
        $svr->method('Get')->willReturnArgument(0);

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('Get')->willReturn('1');

        $mockXml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_StepMock([
            'XML' => $mockXml,
            'Settings' => $settings,
            'RESTManager' => $mgr,
            'RESTServer' => $svr,
            'Database' => $mockDb,
            'UserInterface' => $mockInt,
            'View' => $mockView,
            'Language' => $mockLang,
            'Cache' => $mockCache,
            'CommentManager' => $cmgrMock,
        ]);

        return $obj;
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Troubleshooter\Api\Controller_Step', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->GetList(),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetList();
    }

    /**
     * @throws \SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testProcessTroubleshooterStepsReturnsTrue()
    {
        $obj = $this->getController();

        $ref = new \ReflectionClass($obj);
        $method = $ref->getMethod('ProcessTroubleshooterSteps');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Get(1),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Get(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Post(),
            'Returns false without categoryid');

        $_POST['categoryid'] = '1';
        $this->assertFalse($obj->Post(),
            'Returns false without subject');

        $_POST['subject'] = 'subject';
        $this->assertFalse($obj->Post(),
            'Returns false without contents');

        $_POST['contents'] = 'contents';
        $this->assertFalse($obj->Post(),
            'Returns false without staffid');

        $_POST['staffid'] = '2';
        $_POST['displayorder'] = '1';
        $_POST['allowcomments'] = '1';
        $_POST['enableticketredirection'] = '1';
        $_POST['redirectdepartmentid'] = '1';
        $_POST['tickettypeid'] = '1';
        $_POST['ticketpriorityid'] = '1';
        $_POST['ticketsubject'] = 'subject';
        $_POST['stepstatus'] = '1';
        $_POST['parentstepidlist'] = '1';

        $this->assertFalse($obj->Post(),
            'Returns false with invalid staff id');

        $_POST['staffid'] = '1';
        $this->assertTrue($obj->Post(),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Post();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPutReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Put(0),
            'Returns false with invalid ID');

        $this->assertFalse($obj->Put(1),
            'Returns false with empty editedstaffid');

        $_POST['editedstaffid'] = '2';
        $_POST['subject'] = '';
        $this->assertFalse($obj->Put(1),
            'Returns false with empty contents');

        $_POST['subject'] = 'subject';
        $_POST['contents'] = '';
        $this->assertFalse($obj->Put(1),
            'Returns false with empty subject');

        $_POST['contents'] = 'contents';
        $_POST['displayorder'] = '1';
        $_POST['allowcomments'] = '1';
        $_POST['enableticketredirection'] = '1';
        $_POST['redirectdepartmentid'] = '1';
        $_POST['tickettypeid'] = '1';
        $_POST['ticketpriorityid'] = '1';
        $_POST['ticketsubject'] = 'subject';
        $_POST['stepstatus'] = '1';
        $_POST['parentstepidlist'] = '1';
        $this->assertFalse($obj->Put(1),
            'Returns false with invalid staff id');

        $_POST['editedstaffid'] = '1';
        $this->assertTrue($obj->Put(1),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Put(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Delete(0),
            'Returns false with invalid ID');

        $this->assertTrue($obj->Delete(1),
            'Returns true after deleting');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete(0);
    }
}

/**
 * Class Controller_StepMock
 * @package Troubleshooter\Api
 */
class Controller_StepMock extends Controller_Step
{
    /**
     * Controller_StepMock constructor.
     * @param array $services
     * @throws \SWIFT_Exception
     */
    public function __construct(array $services = [])
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
