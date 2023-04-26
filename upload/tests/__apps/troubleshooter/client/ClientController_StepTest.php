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

namespace Troubleshooter\Client;

use SWIFT_Exception;

/**
 * Class ClientController_StepTest
 * @group troubleshooter
 */
class ClientController_StepTest extends \SWIFT_TestCase
{
    private static $_queries = [];
    private static $_perms = [];
    private static $_numQueries = 0;
    private static $_numWidgets = 0;

    public function setUp()
    {
        parent::setUp();

        // reset test data
        unset($_POST);
        self::$_queries = [];
        self::$_perms = [];
        \SWIFT::Set('usergroupid', 1);
    }

    /**
     * @param array $db
     * @return Controller_Step
     * @throws \SWIFT_Exception
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

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'widgetcache') {
                self::$_numWidgets++;

                if (self::$_numWidgets % 2 === 0) {
                    return [];
                }
            }

            return [
                0 => [
                    'appname' => 'troubleshooter',
                    'isenabled' => 1,
                ],
                9 => [
                    1 => [1],
                ],
            ];
        });

        \SWIFT::GetInstance()->Cache = $mockCache;
        \SWIFT::GetInstance()->Database = $mockDb;
        \SWIFT::GetInstance()->Staff = $mockStaff;
        \SWIFT::GetInstance()->Session = $mockSession;
        \SWIFT::GetInstance()->Language = $mockLang;

        $cmgrMock = $this->getMockBuilder('Base\Library\Comment\SWIFT_CommentManager')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['ProcessPOSTStaff', 'LoadSupportCenter'])
            ->getMock();

        $mockInput = $this->getMockBuilder('SWIFT_Input')
            ->disableOriginalConstructor()
            ->getMock();

        $mockInput->method('SanitizeForXSS')->willReturnArgument(9);

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->setMethods(['Assign', 'Render'])
            ->getMock();

        $obj = new Controller_StepMock([
            'Template' => $mockTpl,
            'Input' => $mockInput,
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
        $this->assertInstanceOf('Troubleshooter\Client\Controller_Step', $obj);

        $obj2 = $obj->__construct();
        $this->assertNull($obj2,
            'Instance is null if widget is not enabled');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewStepsThrowsAccessDeniedException()
    {
        $obj = $this->getController([
            'categorytype' => 2,
            'staffvisibilitycustom' => '1',
            'uservisibilitycustom' => '1',
        ]);
        \SWIFT::Set('usergroupid', 3);
        $this->setExpectedException('SWIFT_Exception', 'Access Denied');
        $obj->View(1, 1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewReturnsTrue()
    {
        $obj = $this->getController([
            'views' => '0',
            'categorytype' => '1',
            'staffvisibilitycustom' => '1',
            'uservisibilitycustom' => '1',
            'redirecttickets' => '1',
            'redirectdepartmentid' => '9',
            'ticketsubject' => 'subject',
            'tickettypeid' => '1',
            'priorityid' => '1',
            'contents' => 'contents',
        ]);

        $this->assertFalse($obj->View(),
            'Returns false without a category id');

        $_POST['nexttroubleshooterstepid'] = 1;
        $_POST['troubleshooterstephistory'] = '1:2:3';
        $_POST['isback'] = '1';

        $this->assertTrue($obj->View(1),
            'Returns false with invalid category object');

        $_POST['troubleshooterstephistory'] = '1:2';
        $this->assertTrue($obj->View(1),
            'Returns true with valid category object');

        unset($_POST['nexttroubleshooterstepid']);
        $_POST['isback'] = '0';
        $this->assertFalse($obj->View(1),
            'Returns false with isback = 0');

        unset($_POST['troubleshooterstephistory']);
        $this->assertTrue($obj->View(1, 0, null),
            'Returns true without history');

        $this->assertTrue($obj->View(1, 1),
            'Returns true with valid step id');

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->View();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewReturnsTrueWithoutRedirectId()
    {
        $obj = $this->getController([
            'views' => '0',
            'categorytype' => '1',
            'staffvisibilitycustom' => '1',
            'uservisibilitycustom' => '1',
            'redirecttickets' => '1',
            'redirectdepartmentid' => '1',
            'ticketsubject' => 'subject',
            'tickettypeid' => '1',
            'priorityid' => '1',
            'contents' => 'contents',
        ]);

        $_POST['isback'] = '0';

        $this->assertTrue($obj->View(1, 1),
            'Returns true with valid step id');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewReturnsTrueWithoutRedirects()
    {
        $obj = $this->getController([
            'views' => '0',
            'categorytype' => '1',
            'staffvisibilitycustom' => '1',
            'uservisibilitycustom' => '1',
            'redirecttickets' => '0',
            'ticketsubject' => 'subject',
            'tickettypeid' => '1',
            'priorityid' => '1',
            'contents' => 'contents',
            'hasattachments' => '1',
            'allowcomments' => '1',
            'title' => 'title',
        ]);

        $_POST['isback'] = '0';

        $this->assertTrue($obj->View(1, 1),
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
        \SWIFT::Set('usergroupid', 3);
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
 * @package Troubleshooter\Client
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
