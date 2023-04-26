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

namespace Troubleshooter\Staff;

/**
 * Class StaffController_StepTest
 * @group troubleshooter
 */
class StaffController_StepTest extends \SWIFT_TestCase
{
    private static $_queries = [];
    private static $_perms = [];
    private static $_numQueries = 0;
    private static $_numPerms = 0;

    public function setUp()
    {
        parent::setUp();

        // reset test data
        unset($_POST);
        self::$_queries = [];
        self::$_perms = [];
    }

    /**
     * @param array $db
     * @return Controller_Step
     */
    public function getController(array $db = [])
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDb->method('Insert_ID')->willReturn(1);

        // generate custom data for all test cases
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) use ($db) {
            $key = hash('md5', $x);
            if (!isset(self::$_queries[$key])) {
                self::$_queries[$key] = [0, $x];
            }
            ++self::$_queries[$key][0];

            $count = 2;

            if (strpos($x, "troubleshooterstepid = '1'") !== false ||
                strpos($x, "troubleshootercategoryid = '1'") !== false) {
                $count = 3;
            }

            $result = array_merge([
                'troubleshootercategoryid' => 1,
                'troubleshooterstepid' => 1,
                'title' => 'title',
                'subject' => 'subject',
                'description' => 'description',
                'stepstatus' => 1,
            ], $db);

            if (self::$_queries[$key][0] % $count === 0 ||
                strpos($x, "troubleshooterstepid = '2'") !== false) {
                $result = false;
            }

            ++self::$_numQueries;

            return $result;
        });

        $this->mockProperty($mockDb, 'Record', [
            'troubleshootercategoryid' => 1,
            'troubleshooterstepid' => 1,
            'subject' => 'subject',
            'filename' => 'file.txt',
        ]);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnCallback(function ($x) {
            if (!isset(self::$_perms[$x])) {
                self::$_perms[$x] = 0;
            }

            $result = self::$_perms[$x] % 2 === 0 ? '1' : '0';

            if ($x === 'staff_trcaninsertpublishedsteps') {
                $result = 1;
            }

            ++self::$_numPerms;

            self::$_perms[$x]++;

            return $result;
        });

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
            ->willReturnOnConsecutiveCalls([1 => 1], false);

        \SWIFT::GetInstance()->Cache = $mockCache;

        $cmgrMock = $this->getMockBuilder('Base\Library\Comment\SWIFT_CommentManager')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['ProcessPOSTStaff'])
            ->getMock();

        $obj = new Controller_StepMock([
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
        $obj = new Controller_Step();
        $this->assertInstanceOf('Troubleshooter\Staff\Controller_Step', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testLoadDisplayDataReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertTrue($obj->_LoadDisplayData(),
            'Returns true after rendering');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->_LoadDisplayData();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testIndexReturnsManage()
    {
        $obj = $this->getController();
        $this->assertEquals('manage', $obj->Index(),
            'Renders manage and returns');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Index();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertTrue($obj->Delete(0),
            'Returns true after deleting');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertTrue($obj->Manage(),
            'Returns true after rendering');

        $_POST['itemid'] = '0';
        $this->assertTrue($obj->Manage(0),
            'Returns true after rendering');

        $this->assertTrue($obj->Manage(0, 'no'),
            'Returns true after rendering');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Manage();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteListWorks()
    {
        $obj = $this->getController();

        $this->assertTrue($obj::DeleteList([0], true),
            'Returns true after deleting with staff_trcandeletestep = 1');

        $this->assertFalse($obj::DeleteList([0], true),
            'Returns false after rendering with staff_trcandeletestep = 0');

        unset($_POST['csrfhash']);
        $this->assertFalse($obj::DeleteList([0], false),
            'Returns false if csrfhash is not provided');
    }

    /**
     * @throws \ReflectionException
     * @throws \SWIFT_Exception
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getController();

        // runchecks is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('RunChecks');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false if csrfhash is not provided');

        $_POST['csrfhash'] = 'csrfhash';

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false if POST data is not provided');

        $_POST['subject'] = 'subject';
        $_POST['stepcontents_htmlcontents'] = 'contents';
        $_POST['parentstepidlist'] = [0];

        \SWIFT::Set('isdemo', true);
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with demo mode');

        \SWIFT::Set('isdemo', false);

        // call and advance counter
        \SWIFT::GetInstance()->Staff->GetPermission('staff_trcaninsertstep');
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false with insert mode and staff_trcaninsertstep = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testInsertDialogReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertTrue($obj->InsertDialog(),
            'Returns true after rendering and staff_trcaninsertstep = 1');

        $this->assertFalse($obj->InsertDialog(),
            'Returns false when staff_trcaninsertstep = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->InsertDialog();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testInsertThrowsExceptionWithInvalidId()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->Insert();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $obj = $this->getController();

        $_POST['troubleshootercategoryid'] = 1;

        $this->assertTrue($obj->Insert(1, 1),
            'Returns true after rendering with staff_trcaninsertstep = 1');

        $this->assertTrue($obj->Insert(1, 1),
            'Returns true after rendering with staff_trcaninsertstep = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Insert();
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderConfirmationReturnsTrue()
    {
        $obj = $this->getController();

        // _RenderConfirmation is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('_RenderConfirmation');
        $method->setAccessible(true);

        $_POST['subject'] = 'subject';
        $this->assertTrue($method->invoke($obj, 1),
            'Returns true after rendering');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testInsertSubmitReturnsFalse()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->InsertSubmit(),
            'Returns false after rendering');

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['troubleshootercategoryid'] = 1;
        $_POST['subject'] = 'subject';
        $_POST['redirecttickets'] = '1';
        $_POST['stepcontents_htmlcontents'] = 'contents';
        $_POST['parentstepidlist'] = [0];

        $this->assertTrue($obj->InsertSubmit(),
            'Returns true after rendering if checks pass');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->InsertSubmit();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testEditThrowsExceptionWithInvalidId()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->Edit(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testEditThrowsExceptionWithWrongId()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->Edit(2);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testEditReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Edit(1),
            'Returns true after rendering with staff_trcanupdatestep = 1');

        $this->assertTrue($obj->Edit(3),
            'Returns true after rendering with staff_trcanupdatestep = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Edit(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testEditSubmitThrowsExceptionWithInvalidId()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->EditSubmit(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testEditSubmitReturnsTrueWithDraftStatus()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->EditSubmit(1),
            'Returns false after rendering and checks fail');

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['subject'] = 'subject';
        $_POST['stepcontents_htmlcontents'] = 'contents';
        $_POST['parentstepidlist'] = [0];
        $_POST['redirecttickets'] = '1';
        $this->assertTrue($obj->EditSubmit(1, -1),
            'Returns true after rendering and checks pass with draft status');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->EditSubmit(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testEditSubmitReturnsTrueWithPublishedStatus()
    {
        $obj = $this->getController();

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['subject'] = 'subject';
        $_POST['stepcontents_htmlcontents'] = 'contents';
        $_POST['parentstepidlist'] = [0];

        $this->assertTrue($obj->EditSubmit(3, 1),
            'Returns true after rendering and checks pass with published status');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTroubleshooterStepIDListReturnsArray()
    {
        $obj = $this->getController();

        // _GetTroubleshooterStepIDList is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('_GetTroubleshooterStepIDList');
        $method->setAccessible(true);

        $this->assertEquals([], $method->invoke($obj),
            'Returns empty array');

        $_POST['parentstepidlist'] = [1];
        $this->assertEquals($_POST['parentstepidlist'], $method->invoke($obj),
            'Returns array with just one element');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testQuickFilterThrowsExceptionWhenNotLoaded()
    {
        $obj = $this->getController();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->QuickFilter(0, 0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testQuickFilterThrowsExceptionWithInvalidFilter()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->QuickFilter('date', 'invalid');
    }

    /**
     * @dataProvider filterProvider
     * @param string $type Filter type
     * @param string $value Filter value
     * @throws \SWIFT_Exception
     */
    public function testQuickFilterReturnsTrue($type, $value)
    {
        $obj = $this->getController();
        $this->assertTrue($obj->QuickFilter($type, $value),
            'Returns true with valid filter');
    }

    public function filterProvider()
    {
        return [
            ['category', '1'],
            ['date', 'today'],
            ['date', 'yesterday'],
            ['date', 'l7'],
            ['date', 'l30'],
            ['date', 'l180'],
            ['date', 'l365'],
            ['other', '0'],
        ];
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewStepsThrowsAccessDeniedException()
    {
        $obj = $this->getController([
            'categorytype' => 2
        ]);
        $this->setExpectedException('SWIFT_Exception', 'Access Denied');
        $obj->ViewSteps(1, 1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewStepsThrowsExceptionWithInvalidCategoryId()
    {
        $obj = $this->getController([
            'views' => 1,
            'categorytype' => 1,
            'staffvisibilitycustom' => 1,
            'uservisibilitycustom' => 1,
        ]);
        $this->setExpectedException('SWIFT_Exception', 'Invalid Step Category');
        $obj->ViewSteps(2, 3);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewStepsReturnsTrue()
    {
        $obj = $this->getController([
            'views' => 1,
            'categorytype' => 1,
            'staffvisibilitycustom' => 1,
            'uservisibilitycustom' => 1,
        ]);

        $_POST['nexttroubleshooterstepid'] = 1;
        $_POST['troubleshooterstephistory'] = '1:2:3';
        $_POST['isback'] = 1;
        $this->assertFalse($obj->ViewSteps(0),
            'Returns false with invalid category id');

        $_POST['troubleshooterstephistory'] = '1:2';
        $this->assertFalse($obj->ViewSteps(0),
            'Returns false with invalid category id and 2 steps');

        $_POST['isback'] = 0;
        unset($_POST['nexttroubleshooterstepid']);
        $this->assertFalse($obj->ViewSteps(0),
            'Returns false with invalid category id and not isBack');

        unset($_POST['troubleshooterstephistory']);
        $this->assertTrue($obj->ViewSteps(1),
            'Returns true after rendering without step');

        $this->assertTrue($obj->ViewSteps(1, 0, '1'),
            'Returns true after rendering and 1 step');

        $this->assertFalse($obj->ViewSteps(3, 2),
            'Returns false with invalid step id');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ViewSteps(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewStepsReturnsTrueWithRedirectTickets()
    {
        $obj = $this->getController([
            'views' => 1,
            'categorytype' => 1,
            'staffvisibilitycustom' => 1,
            'uservisibilitycustom' => 1,
            'redirecttickets' => 1,
            'redirectdepartmentid' => 1,
            'ticketsubject' => 'subject',
            'contents' => 'contents',
        ]);

        $_POST['isback'] = 0;

        $this->assertTrue($obj->ViewSteps(1, 1),
            'Returns true with valid step id');

        $this->assertTrue($obj->ViewSteps(1, 1),
            'Returns true with valid step id and cache');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewStepsReturnsTrueWithoutRedirectTickets()
    {
        $obj = $this->getController([
            'views' => 1,
            'categorytype' => 1,
            'staffvisibilitycustom' => 1,
            'uservisibilitycustom' => 1,
            'redirecttickets' => 0,
            'subject' => 'subject',
            'contents' => 'contents',
            'hasattachments' => 1,
            'allowcomments' => 'allowcomments',
        ]);

        $_POST['isback'] = 0;
        $_POST['comments'] = 'comments';

        $this->assertTrue($obj->ViewSteps(1, 1),
            'Returns true with valid step id');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetAttachmentThrowsExceptionWithInvalidCategoryId()
    {
        $obj = $this->getController([
            'views' => 1,
            'categorytype' => 1,
            'staffvisibilitycustom' => 1,
            'uservisibilitycustom' => 1,
            'redirecttickets' => 0,
            'subject' => 'subject',
            'contents' => 'contents',
            'hasattachments' => 1,
            'attachmentid' => 1,
            'linktype' => 0,
        ]);
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->GetAttachment(1, 1, 1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetAttachmentThrowsAccessDeniedException()
    {
        $obj = $this->getController([
            'views' => 1,
            'categorytype' => 2,
            'staffvisibilitycustom' => 1,
            'uservisibilitycustom' => 1,
            'redirecttickets' => 0,
            'subject' => 'subject',
            'contents' => 'contents',
            'hasattachments' => 1,
            'attachmentid' => 1,
            'linktype' => 0,
        ]);
        $this->setExpectedException('SWIFT_Exception', 'Access Denied');
        $obj->GetAttachment(1, 1, 1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetAttachmentReturnsTrue()
    {
        $obj = $this->getController([
            'views' => 1,
            'categorytype' => 1,
            'staffvisibilitycustom' => 1,
            'uservisibilitycustom' => 1,
            'redirecttickets' => 0,
            'subject' => 'subject',
            'contents' => 'contents',
            'hasattachments' => 1,
            'attachmentid' => 1,
            'linktype' => 6,
            'linktypeid' => 1,
            'allowcomments' => 'allowcomments',
            'filename' => 'file.txt',
            'storefilename' => 'store.txt',
        ]);

        $this->assertFalse($obj->GetAttachment(0, 0, 0),
            'Returns false with invalid category id');

        $this->assertFalse($obj->GetAttachment(1, 0, 0),
            'Returns false with invalid step id');

        $this->assertTrue($obj->GetAttachment(1, 1, 1),
            'Returns true with valid data');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetAttachment(0, 0, 0);
    }
}

/**
 * Class Controller_StepMock
 * @package Troubleshooter\Staff
 */
class Controller_StepMock extends Controller_Step
{

    /**
     * Controller_StepMock constructor.
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);
    }
}
