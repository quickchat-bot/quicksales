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

namespace Knowledgebase\Staff;

use Knowledgebase\Admin\LoaderMock;
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;

/**
 * Class View_ViewKnowledgebaseTest
 * @group knowledgebase
 */
class View_ViewKnowledgebaseTest extends \SWIFT_TestCase
{
    public static $_next = false;
    public static $_skip = false;
    public static $_count = [];

    public function setUp()
    {
        parent::setUp();

        unset($_POST);
    }

    /**
     * @return View_ViewKnowledgebase
     * @throws SWIFT_Exception
     */
    public function getView()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT kbcategories.kbcategoryid, kbcategories.totalarticles')) {
                self::$_skip = true;
            }

            return true;
        });

        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "kbcategoryid = '2'")) {
                return false;
            }

            if (false !== strpos($x, "kbcategoryid = '3'")) {
                return [
                    'kbcategoryid' => '3',
                    'categorytype' => '2',
                ];
            }

            return [
                'categorytype' => 1,
                'kbarticleid' => 1,
                'author' => 'author',
                'creator' => 2,
                'creatorid' => 1,
                'dateline' => 1,
                'editedstaffid' => 1,
                'isedited' => '1',
                'editeddateline' => 1,
                'hasattachments' => '1',
                'kbcategoryid' => '1',
                'staffvisibilitycustom' => '1',
            ];
        });

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next = !self::$_next;
            return !self::$_skip && self::$_next;
        });

        $this->mockProperty($mockDb, 'Record', [
            'articles' => [
                ['subject' => 'subject'],
            ],
            'totalarticles' => 1,
            'subject' => 'title',
            'title' => 'title',
            'kbarticleid' => 1,
            'jumptorow' => true,
            'filename' => 'file.txt',
            'articlestatus' => '1',
            'contentstext' => 'content',
        ]);

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['Start', 'End', 'AppendHTML', 'AddTab', 'IsAjax', 'AddNavigationBox'])
            ->getMock();

        $mockTab = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
            ->disableOriginalConstructor()
            ->setMethods(['Text', 'Radio', 'RowHTML'])
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

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            1 => [
                'fullname' => 'fullname',
            ],
        ]);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStaff->method('GetProperty')->willReturn(1);
        $mockStaff->method('GetPermission')->willReturn(1);

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSettings->method('Get')->willReturnCallback(function ($x) {
            if (!isset(self::$_count[$x])) {
                self::$_count[$x] = 0;
            }
            self::$_count[$x]++;

            if ($x === 'kb_categorycolumns' && in_array(self::$_count[$x], [2, 7], true)) {
                return 0;
            }

            if ($x === 'kb_categorycolumns' && in_array(self::$_count[$x], [3, 4], true)) {
                return 2;
            }

            return 1;
        });

        SWIFT::GetInstance()->Settings = $mockSettings;
        SWIFT::GetInstance()->Cache = $mockCache;
        SWIFT::GetInstance()->Staff = $mockStaff;
        SWIFT::GetInstance()->Language = $mockLang;
        SWIFT::GetInstance()->Database = $mockDb;

        $mockMgr = $this->getMockBuilder('Base\Library\Comment\SWIFT_CommentManager')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new View_ViewKnowledgebaseMock([
            'Settings' => $mockSettings,
            'Cache' => $mockCache,
            'Database' => $mockDb,
            'UserInterface' => $mockInt,
            'UserInterfaceGrid' => $mockGrid,
            'Language' => $mockLang,
            'CommentManager' => $mockMgr,
        ]);

        return $obj;
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderInfoBoxReturnsTrue()
    {
        $obj = $this->getView();

        $item = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID(2));
        $this->assertTrue($obj->RenderInfoBox($item),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderInfoBox($item);
    }

    /**
     * @throws SWIFT_Exception
     * @throws \Knowledgebase\Models\Article\SWIFT_Article_Exception
     * @throws \Knowledgebase\Models\Category\SWIFT_Category_Exception
     */
    public function testRenderViewArticleThrowsInvalidDataException()
    {
        $obj = $this->getView();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->RenderViewArticle([0, 0]);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderViewArticleReturnsTrue()
    {
        $obj = $this->getView();

        $item = new SWIFT_KnowledgebaseArticle(new SWIFT_DataID(1));
        $cat = new SWIFT_KnowledgebaseCategory(new SWIFT_DataID(1));

        $container = [$item, $cat, 1, 1];
        $this->assertTrue($obj->RenderViewArticle($container),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderViewArticle([]),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     * @throws \Knowledgebase\Models\Category\SWIFT_Category_Exception
     * @throws \Base\Library\Staff\SWIFT_Staff_Exception
     */
    public function testRenderViewAllReturnsTrue()
    {
        $obj = $this->getView();

        $this->assertFalse($obj->RenderViewAll(2),
            'Returns false with invalid id');

        $this->assertFalse($obj->RenderViewAll(3),
            'Returns false with type public');

        $this->assertFalse($obj->RenderViewAll(1),
            'Returns false with filtered id');

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            5 => [1 => [1]],
        ]);
        SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj->RenderViewAll(1),
            'Returns true after render');

        self::$_skip = true;
        $this->assertTrue($obj->RenderViewAll(),
            'Returns true after render');

        self::$_skip = false;
        $this->assertTrue($obj->RenderViewAll(),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderViewAll(1),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetRatingReturnsHtml()
    {
        $obj = $this->getView();
        $this->assertContains('<div class="kbrating">', $obj->GetRating([], true, 1));

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->GetRating([], true, 1);
    }
}

class View_ViewKnowledgebaseMock extends View_ViewKnowledgebase
{
    public $Controller;

    public function __construct($services)
    {
        $this->Load = new LoaderMock();

        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }

        parent::__construct();

        $this->Controller = $this;
    }

    public function Initialize()
    {
        return true;
    }

    public function _ProcessArticleRating()
    {
        return ['_hasNotRated' => 1, '_articleRating' => 1];
    }
}
