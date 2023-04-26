<?php
/**
 * ###############################################
 *
 * Kayako Classic
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

namespace Knowledgebase\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * Class Controller_ArticleTest
 * @group knowledgebase
 */
class Controller_ArticleTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $_skip = false;

    public function setUp()
    {
        parent::setUp();

        unset($_POST);
        unset($_FILES);
    }

    /**
     * @return Controller_Article
     * @throws SWIFT_Exception
     */
    public function getController()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            return !self::$_skip && self::$_next % 2 === 0;
        });
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('Escape')->willReturnArgument(0);
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "email = 'me2@email.com'")) {
                return false;
            }

            return [
                'attachmentid' => 1,
                'storefilename' => '',
                'attachmenttype' => 1,
                'ticketpostid' => 1,
                'kbarticleid' => 1,
                'newstype' => 1,
                'subject' => 'subject',
                'seosubject' => 0,
                'articlestatus' => 1,
            ];
        });

        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "parentkbcategoryid = '0'")) {
                self::$_skip = true;
                return false;
            }

            self::$_skip = false;
            return true;
        });

        $this->mockProperty($mockDb, 'Record', [
            'kbarticleid' => 1,
            'staffid' => 1,
            'staffvisibilitycustom' => 1,
            'attachmentid' => 1,
        ]);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('1', '0', '1');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetProperty')->willReturn(1);

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->method('GetIsClassLoaded')->willReturn(true);
        $mockSession->method('GetProperty')->willReturnArgument(0);
        $mockSession->method('GetSessionID')->willReturn(1);

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn(1);

        \SWIFT::GetInstance()->Load = new LoaderMock();
        \SWIFT::GetInstance()->Settings = $mockSettings;
        \SWIFT::GetInstance()->Database = $mockDb;
        \SWIFT::GetInstance()->Staff = $mockStaff;
        \SWIFT::GetInstance()->Session = $mockSession;

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceClient')
            ->disableOriginalConstructor()
            ->setMethods(['DisplayError', 'Header', 'Footer', 'Error', 'CheckFields', 'AddNavigationBox'])
            ->getMock();

        $mockView = $this->getMockBuilder('SWIFT_View')
            ->disableOriginalConstructor()
            ->setMethods([
                'RenderGrid',
                'Render',
                'RenderViewAll',
                'RenderInsertKnowledgebaseDialog',
                'RenderInfoBox',
                'RenderViewItem',
            ])
            ->getMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            return $x;
        });

        \SWIFT::GetInstance()->Language = $mockLang;

        $mockRender = $this->getMockBuilder('Knowledgebase\Library\Render\SWIFT_KnowledgebaseRenderManager')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_ArticleMock([
            'Database' => $mockDb,
            'UserInterface' => $mockInt,
            'View' => $mockView,
            'Language' => $mockLang,
            'KnowledgebaseRenderManager' => $mockRender,
        ]);

        return $obj;
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Knowledgebase\Staff\Controller_Article', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLoadDisplayDataReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertTrue($obj->_LoadDisplayData());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->_LoadDisplayData();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Delete(2),
            'Returns true after delete');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete(0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListWorks()
    {
        $obj = $this->getController();

        SWIFT::GetInstance()->Staff->GetPermission('perm'); // advance
        $this->assertFalse($obj::DeleteList([], true),
            'Returns false after rendering with staff_nwcandeleteitem = 0');

        unset($_POST['csrfhash']);
        $this->assertFalse($obj::DeleteList([], false),
            'Returns false if csrfhash is not provided');

        $this->assertTrue($obj::DeleteList([1], true));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testManageReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Manage(),
            'Returns true after rendering with staff_nwcanmanageitems = 1');

        $this->assertTrue($obj->Manage(),
            'Returns true after rendering with staff_nwcanmanageitems = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Manage();
    }

    /**
     * @throws \ReflectionException
     * @throws SWIFT_Exception
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getController();

        // runchecks is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('RunChecks');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false if csrfhash is not provided');

        $_POST['csrfhash'] = 'csrfhash';
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false if data is not provided');

        $_POST['articlecontents_htmlcontents'] = 'contents';
        $_POST['subject'] = 'subject';
        $_POST['kbcategoryidlist'] = [1];
        \SWIFT::Set('isdemo', true);
        $this->assertFalse($method->invoke($obj, 2),
            'Returns false if demo mode is enabled');

        \SWIFT::Set('isdemo', false);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('0', '1');

        \SWIFT::GetInstance()->Staff = $mockStaff;

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false when staff_kbcaninsertarticle = 0 in edit mode');

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with valid subscriber id');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 2);
    }

//    /**
//     * @throws \SWIFT_Exception
//     */
//    public function testInsertTicketThrowsExceptionWithInvalidId()
//    {
//        $obj = $this->getController();
//        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
//        $obj->InsertTicket();
//    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertTicketReturnsTrue()
    {
        $obj = $this->getController();
        $ticket = new SWIFT_TicketPost(new \SWIFT_DataID(1));
        $_POST['newstype'] = 1;
        $this->assertTrue($obj->InsertTicket($ticket, 1, 1, 1, 1, 1),
            'Returns true after rendering with staff_kbcaninsertarticle = 1');

        $this->assertTrue($obj->InsertTicket($ticket, 1, 1, 1, 1, 1),
            'Returns true after rendering with staff_kbcaninsertarticle = 0');

        $old = SWIFT_App::$_installedApps;
        unset(SWIFT_App::$_installedApps['tickets']);
        $this->assertFalse($obj->InsertTicket($ticket, 1, 1, 1, 1, 1));
        SWIFT_App::$_installedApps = $old;

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->InsertTicket($ticket, 1, 1, 1, 1, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $obj = $this->getController();

        $_POST['newstype'] = 1;
        $this->assertTrue($obj->Insert(),
            'Returns true after rendering with staff_kbcaninsertarticle = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true after rendering with staff_kbcaninsertarticle = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Insert();
    }

    /**
     * @throws \ReflectionException
     * @throws SWIFT_Exception
     */
    public function testRenderConfirmationReturnsTrue()
    {
        $obj = $this->getController();

        // _RenderConfirmation is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('_RenderConfirmation');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true without subscribertype');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testInsertSubmitReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->InsertSubmit(),
            'Returns false if RunChecks fails');

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturn([
            'kbarticleid' => 0,
            'fullname' => 'fullname',
            'email' => 'email',
        ]);
        $mockDb->method('Insert_ID')->willReturn(1);

        $this->mockProperty($obj, 'Database', $mockDb);

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['articlecontents_htmlcontents'] = 'contents';
        $_POST['subject'] = 'subject';
        $_POST['seosubject'] = 'seosubject';
        $_POST['kbcategoryidlist'] = [1];
        $_POST['isfeatured'] = 1;
        $_POST['allowcomments'] = 1;
        $_POST['tredir_ticketid'] = 1;
        $_POST['tredir_listtype'] = 'viewticket';
        $_POST['tredir_departmentid'] = 1;
        $_POST['tredir_ticketstatusid'] = 1;
        $_POST['tredir_tickettypeid'] = 1;

        $tmpFile = tempnam(sys_get_temp_dir(), 'swift');

        $_FILES['kbattachments'] = [
            'name' => ['', 'file.txt'],
            'size' => ['0', '1'],
            'type' => ['', 'text/plain'],
            'tmp_name' => ['', $tmpFile],
        ];
        @file_put_contents($tmpFile, '1');
        $this->assertTrue($obj->InsertSubmit(false),
            'Returns true with viewticket');

        $_POST['tredir_listtype'] = '';
        $this->assertTrue($obj->InsertSubmit(false),
            'Returns true without viewticket');

        $_POST['tredir_ticketid'] = '0';
        $this->assertTrue($obj->InsertSubmit(true),
            'Returns true with tredir_ticketid = 0');
        @unlink($tmpFile);
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
    public function testEditReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Edit(1),
            'Returns true after rendering with staff_kbcanupdatearticle = 1');

        SWIFT::GetInstance()->Database->Record = [
            'kbarticleid' => 1,
            'staffid' => 1,
            'linktypeid' => 1,
            'toassignid' => 1,
            'staffgroupid' => 1,
            'staffvisibilitycustom' => 1,
        ];

        $this->assertTrue($obj->Edit(1),
            'Returns true after rendering with staff_kbcanupdatearticle = 0');

        $this->assertTrue($obj->Edit(1),
            'Returns true after rendering with staff_kbcanupdatearticle = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Edit(1);
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
    public function testEditSubmitReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->EditSubmit(1),
            'Returns false if RunChecks fails');

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturn([
            'kbarticleid' => 0,
            'fullname' => 'fullname',
            'email' => 'email',
            'seosubject' => 'seosubject',
            'articlestatus' => 1,
        ]);
        $mockDb->method('Insert_ID')->willReturn(1);

        $this->mockProperty($obj, 'Database', $mockDb);

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['articlecontents_htmlcontents'] = 'contents';
        $_POST['subject'] = 'subject';
        $_POST['seosubject'] = 0;
        $_POST['kbcategoryidlist'] = [1];
        $_POST['isfeatured'] = 1;
        $_POST['allowcomments'] = 1;
        $_POST['tredir_ticketid'] = 1;
        $_POST['tredir_listtype'] = 'viewticket';
        $_POST['tredir_departmentid'] = 1;
        $_POST['tredir_ticketstatusid'] = 1;
        $_POST['tredir_tickettypeid'] = 1;

        $tmpFile = tempnam(sys_get_temp_dir(), 'swift');

        $_FILES['kbattachments'] = [
            'name' => ['', 'file.txt'],
            'size' => ['0', '1'],
            'type' => ['', 'text/plain'],
            'tmp_name' => ['', $tmpFile],
        ];

        @file_put_contents($tmpFile, '1');
        $this->assertTrue($obj->EditSubmit(1, '1'),
            'Returns true with staff_kbcaninsertpublishedarticles = 0');
        $this->assertTrue($obj->EditSubmit(1, '1'),
            'Returns true with staff_kbcaninsertpublishedarticles = 1');

        $this->assertTrue($obj->EditSubmit(1, -1),
            'Returns true if not markaspublished');
        @unlink($tmpFile);

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->EditSubmit(1);
    }

    /**
     * @throws \ReflectionException
     * @throws SWIFT_Exception
     */
    public function testGetKnowledgebaseCategoryIdListReturnsArray()
    {
        $obj = $this->getController();

        // method is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('_GetKnowledgebaseCategoryIDList');
        $method->setAccessible(true);

        $this->assertEmpty($method->invoke($obj), 'Returns array');

        $_POST['kbcategoryidlist'] = ['1'];
        $this->assertNotEmpty($method->invoke($obj), 'Returns array');

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
    public function testQuickFilterThrowsExceptionWithInvalidDateFilter()
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
            ['other', 'other'],
        ];
    }
}

class Controller_ArticleMock extends Controller_Article
{
    public function __construct($services)
    {
        $this->Load = new LoaderMock();

        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }

        parent::__construct();
    }

    public function Initialize()
    {
        return true;
    }
}
