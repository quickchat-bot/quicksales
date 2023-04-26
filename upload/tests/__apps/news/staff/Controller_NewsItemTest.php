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

namespace News\Staff;

use News\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_NewsItemTest
 * @group news
 */
class Controller_NewsItemTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        unset($_POST);
    }

    /**
     * @return Controller_NewsItem
     */
    public function getController()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('Escape')->willReturnArgument(0);
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "email = 'me2@email.com'")) {
                return false;
            }

            return [
                'newsitemid' => 1,
                'newstype' => 1,
                'subject' => 'subject',
            ];
        });

        $this->mockProperty($mockDb, 'Record', [
            'newsitemid' => 1,
        ]);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('1', '0', '1');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetProperty')->willReturnArgument(1);

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->method('GetIsClassLoaded')->willReturn(true);
        $mockSession->method('GetProperty')->willReturnArgument(0);
        $mockSession->method('GetSessionID')->willReturn(1);

        \SWIFT::GetInstance()->Load = new LoaderMock();
        \SWIFT::GetInstance()->Database = $mockDb;
        \SWIFT::GetInstance()->Staff = $mockStaff;
        \SWIFT::GetInstance()->Session = $mockSession;

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceClient')
            ->disableOriginalConstructor()
            ->setMethods(['DisplayError', 'Header', 'Footer', 'Error', 'CheckFields', 'AddNavigationBox'])
            ->getMock();

        $mockView = $this->getMockBuilder('SWIFT_View')
            ->disableOriginalConstructor()
            ->setMethods(['RenderGrid', 'Render', 'RenderViewAll', 'RenderInsertNewsDialog', 'RenderInfoBox', 'RenderViewItem'])
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

        $mockRender = $this->getMockBuilder('News\Library\Render\SWIFT_NewsRenderManager')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_NewsItemMock([
            'Database' => $mockDb,
            'UserInterface' => $mockInt,
            'View' => $mockView,
            'Language' => $mockLang,
            'NewsRenderManager' => $mockRender,
        ]);

        return $obj;
    }

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('News\Staff\Controller_NewsItem', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLoadDisplayReturnsTrue()
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
    public function testLoadDisplayDataForViewNewsReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertTrue($obj->_LoadDisplayDataForViewNews());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->_LoadDisplayDataForViewNews();
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
     * @throws SWIFT_Exception
     */
    public function testInsertDialogReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->InsertDialog(),
            'Returns true after rendering with staff_nwcaninsertitem = 1');

        $this->assertFalse($obj->InsertDialog(),
            'Returns false after rendering with staff_nwcaninsertitem = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->InsertDialog();
    }

    /**
     * @throws \ReflectionException
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

        $_POST['newscontents_htmlcontents'] = 'contents';
        $_POST['subject'] = 'subject';
        $_POST['newstype'] = 1;
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
            'Returns false when staff_nwcaninsertitem = 0 in edit mode');

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with valid subscriber id');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 2);
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
     * @throws SWIFT_Exception
     */
    public function testInsertReturnsTrue()
    {
        $obj = $this->getController();

        $_POST['newstype'] = 1;
        $this->assertTrue($obj->Insert(),
            'Returns true after rendering with staff_nwcaninsertitem = 1');

        $this->assertTrue($obj->Insert(),
            'Returns true after rendering with staff_nwcaninsertitem = 0');

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
            'newsitemid' => 0,
            'fullname' => 'fullname',
            'email' => 'email',
        ]);
        $mockDb->method('Insert_ID')->willReturn(1);

        $this->mockProperty($obj, 'Database', $mockDb);

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['newscontents_htmlcontents'] = 'contents';
        $_POST['subject'] = 'subject';
        $_POST['customemailsubject'] = 'subject';
        $_POST['fromname'] = 'name';
        $_POST['fromemail'] = 'me2@email.com';
        $_POST['newstype'] = 1;
        $_POST['sendemail'] = false;
        $_POST['uservisibilitycustom'] = 1;
        $_POST['allowcomments'] = 1;
        $_POST['staffvisibilitycustom'] = 1;
        $_POST['expiry'] = 1;
        $this->assertTrue($obj->InsertSubmit(true),
            'Returns true if RunChecks passes');

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
            'Returns true after rendering with staff_nwcanupdateitem = 1');

        $this->assertTrue($obj->Edit(1),
            'Returns true after rendering with staff_nwcanupdateitem = 0');

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
            'newsitemid' => 0,
            'fullname' => 'fullname',
            'email' => 'email',
        ]);
        $mockDb->method('Insert_ID')->willReturn(1);

        $this->mockProperty($obj, 'Database', $mockDb);

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['newscontents_htmlcontents'] = 'contents';
        $_POST['subject'] = 'subject';
        $_POST['customemailsubject'] = 'subject';
        $_POST['fromname'] = 'name';
        $_POST['fromemail'] = 'me2@email.com';
        $_POST['newstype'] = 1;
        $_POST['sendemail'] = false;
        $_POST['uservisibilitycustom'] = 1;
        $_POST['allowcomments'] = 1;
        $_POST['staffvisibilitycustom'] = 1;
        $_POST['expiry'] = 1;
        $this->assertTrue($obj->EditSubmit(1, true),
            'Returns true if RunChecks passes');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->EditSubmit(1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetStaffGroupIDListReturnsArray()
    {
        $obj = $this->getController();

        // method is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('_GetStaffGroupIDList');
        $method->setAccessible(true);

        $this->assertEmpty($method->invoke($obj),
            'Returns empty array if id list is not provided');

        $_POST['staffgroupidlist'] = ['1'];
        $this->assertNotEmpty($method->invoke($obj), 'Returns array');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetUserGroupIDListReturnsArray()
    {
        $obj = $this->getController();

        // method is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('_GetUserGroupIDList');
        $method->setAccessible(true);

        $this->assertEmpty($method->invoke($obj),
            'Returns empty array if id list is not provided');

        $_POST['usergroupidlist'] = ['1'];
        $this->assertNotEmpty($method->invoke($obj), 'Returns array');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetNewsCategoryIDListReturnsArray()
    {
        $obj = $this->getController();

        // method is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('_GetNewsCategoryIDList');
        $method->setAccessible(true);

        $_POST['newscategoryidlist'] = ['1'];
        $this->assertNotEmpty($method->invoke($obj), 'Returns array');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewAllReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->ViewAll(),
            'Returns true after rendering with staff_nwcanviewitems = 1');

        $this->assertTrue($obj->ViewAll(),
            'Returns true after rendering with staff_nwcanviewitems = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ViewAll();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewItemThrowsExceptionWithInvalidId()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->ViewItem(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testViewItemReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->ViewItem(1),
            'Returns true after rendering with staff_nwcanviewitems = 1');

        $this->assertTrue($obj->ViewItem(1),
            'Returns true after rendering with staff_nwcanviewitems = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ViewItem(1);
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
     * @throws \SWIFT_Exception
     */
    public function testQuickFilterThrowsExceptionWithInvalidTypeFilter()
    {
        $obj = $this->getController();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->QuickFilter('type', 'invalid');
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
            ['type', 'public'],
            ['type', 'private'],
            ['type', 'global'],
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
}

class Controller_NewsItemMock extends Controller_NewsItem
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
