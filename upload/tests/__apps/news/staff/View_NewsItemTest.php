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

namespace News\Staff;

use News\Admin\LoaderMock;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;

/**
 * Class View_NewsItemTest
 * @group news
 */
class View_NewsItemTest extends \SWIFT_TestCase
{
    public static $_next = false;
    public static $_count = 0;
    private $mockDb;

    public function setUp()
    {
        parent::setUp();

        $this->mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDb->method('QueryLimit')->willReturnCallback(function ($x) {
            self::$_count++;
        });

        $this->mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "newsitemid = '2'")) {
                return [
                    'newsitemid' => 1,
                    'edited' => '1',
                    'expiry' => '0',
                    'start' => '0',
                    'editedstaffid' => 1,
                    'staffid' => 1,
                    'dateline' => 1,
                    'editeddateline' => 1,
                    'syncdateline' => 1,
                    'issynced' => '1',
                    'author' => 'author',
                ];
            }

            return [
                'newsitemid' => 1,
                'newsstatus' => 1,
                'start' => 1,
                'expiry' => 2,
                'totalitems' => 3,
                'allowcomments' => 1,
                'uservisibilitycustom' => 1,
                'staffvisibilitycustom' => 1,
                'subject' => 'subject',
                'emailsubject' => 'emailsubject',
                'contents' => 'contents',
            ];
        });

        $this->mockDb->method('NextRecord')->willReturnCallback(function () {
            if (self::$_count === 2) {
                return false;
            }

            self::$_next = !self::$_next;
            return self::$_next;
        });

        $this->mockProperty($this->mockDb, 'Record', [
            'staffgroupid' => 1,
            'usergroupid' => 1,
            'newscategoryid' => 1,
        ]);
    }

    /**
     * @return View_NewsItem
     */
    public function getView()
    {
        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['Start', 'End', 'AppendHTML', 'AddTab', 'IsAjax', 'SetDialogOptions', 'AddNavigationBox'])
            ->getMock();

        $mockTab = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['Text', 'Radio', 'RowHTML', 'SetColumnWidth'])
            ->getMock();

        $mockInt->method('IsAjax')->willReturn(false);
        $mockInt->method('AddTab')->willReturn($mockTab);

        $mockTb = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceToolbar')
            ->disableOriginalConstructor()
            ->setMethods(['AddButton'])
            ->getMock();

        $this->mockProperty($mockInt, 'Toolbar', $mockTb);

        $mockGrid = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceGrid')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods([
                'SetQuery',
                'AddField',
                'SetRenderCallback',
                'AddMassAction',
                'SetNewLink',
                'Render',
                'Display',
                'GetMode',
                'BuildSQLSearch',
                'SetSearchQuery',
            ])
            ->getMock();

        $mockGrid->method('GetMode')->willReturn(2);
        $mockGrid->method('BuildSQLSearch')->willReturnArgument(0);

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            return $x;
        });

        SWIFT::GetInstance()->Language = $mockLang;
        SWIFT::GetInstance()->Database = $this->mockDb;

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('1', '0');

        SWIFT::GetInstance()->Staff = $mockStaff;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturnOnConsecutiveCalls(['1' => ['fullname' => 'fullname']], []);

        SWIFT::GetInstance()->Cache = $mockCache;

        $mockComm = $this->getMockBuilder('Base\Library\Comment\SWIFT_CommentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn('0');
        SWIFT::GetInstance()->Settings = $mockSettings;

        $services = [
            'Database' => $this->mockDb,
            'UserInterfaceGrid' => $mockGrid,
            'Language' => $mockLang,
            'CommentManager' => $mockComm,
            'Settings' => $mockSettings,
        ];

        $obj = new View_NewsItemMock($services);

        $obj->UserInterface = $mockInt;

        return $obj;
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getView();

        $_POST['newstype'] = 1;
        $this->assertTrue($obj->Render(2),
            'Returns true after render with insert mode');

        $_POST['newstype'] = 2;
        $newsitemObject = new SWIFT_NewsItem(1);
        $this->mockProperty($newsitemObject, 'Database', $this->mockDb);
        $this->assertTrue($obj->Render(1, $newsitemObject),
            'Returns true after render with edit mode and newsitem type is private');

        $_POST['newstype'] = 3;
        $newsitemObject = new SWIFT_NewsItem(1);
        $this->mockProperty($newsitemObject, 'Database', $this->mockDb);
        $this->assertTrue($obj->Render(1, $newsitemObject),
            'Returns true after render with edit mode and newsitem type is private');

        $newsitemObject = new SWIFT_NewsItem(1);
        $this->mockProperty($newsitemObject, 'Database', $this->mockDb);
        $this->mockProperty($newsitemObject, '_dataStore', [
            'newsitemid' => 1,
            'newsstatus' => 2,
            'start' => 0,
            'expiry' => 0,
            'allowcomments' => 1,
            'uservisibilitycustom' => 1,
            'staffvisibilitycustom' => 1,
            'subject' => 'subject',
            'emailsubject' => 'emailsubject',
            'contents' => 'contents',
        ]);
        $this->assertTrue($obj->Render(1, $newsitemObject),
            'Returns true after render with edit mode and newsitem type is private');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(0),
            'Returns false if class is not loaded');
    }

    public function testRenderGridReturnsTrue()
    {
        $obj = $this->getView();

        $this->assertTrue($obj->RenderGrid(),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderGrid(),
            'Returns false if class is not loaded');
    }

    public function testGridRenderReturnsArray()
    {
        $dataStore['newsitemtype'] = 3;
        $dataStore['newstype'] = 1;
        $dataStore['visibilitytype'] = 'private';
        $dataStore['newsstatus'] = 1;
        $dataStore['staffid'] = 1;
        $obj = $this->getView();
        $actual = $obj::GridRender($dataStore);
        $this->assertInternalType('array', $actual,
            'The static method should return an array');
        $actual = $obj::GridRender($dataStore);
        $this->assertInternalType('array', $actual,
            'The static method should return an array');
    }

    public function testRenderInsertNewsDialogReturnsTrue()
    {
        $obj = $this->getView();

        $this->assertTrue($obj->RenderInsertNewsDialog(),
            'Returns true after render');

        $this->assertTrue($obj->RenderInsertNewsDialog(),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderInsertNewsDialog(),
            'Returns false if class is not loaded');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderInfoBoxReturnsTrue()
    {
        $obj = $this->getView();

        $newsitemObject = new SWIFT_NewsItem(2);
        $this->assertTrue($obj->RenderInfoBox($newsitemObject),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderInfoBox($newsitemObject);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderViewItemReturnsTrue()
    {
        $obj = $this->getView();

        $newsitemObject = new SWIFT_NewsItem(1);

        $this->assertTrue($obj->RenderViewItem($newsitemObject),
            'Returns true after render');

        $this->assertTrue($obj->RenderViewItem($newsitemObject),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderViewItem($newsitemObject),
            'Returns false if class is not loaded');
    }

    public function testRenderViewAllReturnsTrue()
    {
        $obj = $this->getView();

        $this->assertTrue($obj->RenderViewAll(1, 1),
            'Returns true after render');

        $this->assertTrue($obj->RenderViewAll(3, 1),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderViewAll(1, 1),
            'Returns false if class is not loaded');
    }
}

class View_NewsItemMock extends View_NewsItem
{
    public $Controller;
    public $UserInterface;

    public function __construct($services)
    {
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }

        parent::__construct();
        $this->Load = new LoaderMock();

        $this->Controller = $this;
    }
}
