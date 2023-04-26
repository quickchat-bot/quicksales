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
use Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle;
use SWIFT;
use SWIFT_DataID;
use SWIFT_Exception;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * Class View_ArticleTest
 * @group knowledgebase
 */
class View_ArticleTest extends \SWIFT_TestCase
{
    public static $_next = false;
    public static $_skip = false;

    public function setUp()
    {
        parent::setUp();

        unset($_POST);
    }

    /**
     * @return View_Article
     * @throws SWIFT_Exception
     */
    public function getView()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "kbcategoryid = '3'")) {
                return false;
            }

            if (false !== strpos($x, "kbcategoryid = '4'")) {
                return [
                    'categorytype' => 2,
                    'kbcategoryid' => 1,
                ];
            }

            return [
                'departmentid' => 1,
                'lastpostid' => 1,
                'iswatched' => '1',
                'ticketid' => 1,
                'parentkbcategoryid' => 0,
                'staffvisibilitycustom' => 1,
                'categorytype' => 1,
                'kbcategoryid' => 1,
                'kbarticleid' => 1,
                'author' => 'author',
                'creator' => 2,
                'creatorid' => 1,
                'dateline' => 1,
                'editedstaffid' => 1,
                'isedited' => '1',
                'editeddateline' => 1,
                'articlerating' => 1,
            ];
        });

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next = !self::$_next;
            return !self::$_skip && self::$_next;
        });

        $this->mockProperty($mockDb, 'Record', [
            'kbarticleid' => 1,
            'attachmentid' => 1,
        ]);

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->setMethods(['Start', 'End', 'AppendHTML', 'AddTab', 'IsAjax', 'AddNavigationBox'])
            ->getMock();

        $mockTab = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
            ->disableOriginalConstructor()
            ->setMethods(['Text', 'Radio', 'RowHTML', 'SetTabCounter', 'LoadToolbar', 'SetColumnWidth',
                'CheckBoxContainerList', 'HTMLEditor', 'Title', 'YesNo'])
            ->getMock();

        $mockInt->method('IsAjax')->willReturn(false);
        $mockInt->method('AddTab')->willReturn($mockTab);

        $mockTb = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceToolbar')
            ->disableOriginalConstructor()
            ->setMethods(['AddButton'])
            ->getMock();

        $this->mockProperty($mockInt, 'Toolbar', $mockTb);
        $this->mockProperty($mockTab, 'Toolbar', $mockTb);

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
                'SetNewLinkViewport',
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
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);

        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();
        $mockEmoji->method('decode')->willReturnArgument(0);
        $mockEmoji->method('encode')->willReturnArgument(0);

        SWIFT::GetInstance()->Load = new LoaderMock();
        SWIFT::GetInstance()->Cache = $mockCache;
        SWIFT::GetInstance()->Staff = $mockStaff;
        SWIFT::GetInstance()->Language = $mockLang;
        SWIFT::GetInstance()->Database = $mockDb;

        $obj = new View_ArticleMock([
            'Cache' => $mockCache,
            'Database' => $mockDb,
            'UserInterface' => $mockInt,
            'UserInterfaceGrid' => $mockGrid,
            'Language' => $mockLang,
            'Emoji' => $mockEmoji,
        ]);

        return $obj;
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderThrowsException()
    {
        $obj = $this->getView();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $item = new SWIFT_TicketPost(new \SWIFT_DataStore([
            'ticketpostid' => 2,
            'ticketid' => 2,
        ]));
        $obj->Render(2, null, false, $item, 1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getView();

        $item = new SWIFT_TicketPost(new \SWIFT_DataStore([
            'ticketpostid' => 1,
            'ticketid' => 1,
            'contents' => 'contents',
        ]));

        $arr = [
            'kbarticleid' => 1,
            'articlestatus' => 2,
            'hasattachments' => '1',
            'subject' => 1,
            'seosubject' => 1,
            'contents' => 1,
            'isfeatured' => 1,
            'allowcomments' => 1,
        ];
        $art = new SWIFT_KnowledgebaseArticle(new \SWIFT_DataStore($arr));

        $this->assertTrue($obj->Render(1, $art, 1, $item, 1),
            'Returns true in edit mode and articlestatus = draft');

        $arr['articlestatus'] = 1;
        $art = new SWIFT_KnowledgebaseArticle(new \SWIFT_DataStore($arr));

        $this->assertTrue($obj->Render(1, $art, false, $item, 1),
            'Returns true in edit mode and articlestatus = published');

        unset($arr['seosubject'], $arr['contents']);
        $art = new SWIFT_KnowledgebaseArticle(new \SWIFT_DataStore($arr));

        $this->assertTrue($obj->Render(1, $art, false, $item, 1),
            'Returns true in edit mode and articlestatus = published');

        $this->assertTrue($obj->Render(2, null, false, $item, 1),
            'Returns true in insert mode');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(0),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderGridReturnsTrue()
    {
        $obj = $this->getView();

        $this->assertTrue($obj->RenderGrid(false, 2),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderGrid(),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $obj = $this->getView();

        $dataStore['articlestatus'] = 2;
        $dataStore['creator'] = 2;
        $dataStore['creatorid'] = 1;
        $dataStore['editeddateline'] = 1;

        $this->assertInternalType('array', $obj::GridRender($dataStore),
            'Returns array with creator = staff');

        $dataStore['creator'] = 1;
        $this->assertInternalType('array', $obj::GridRender($dataStore),
            'Returns array with creator = user');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderInfoBoxReturnsTrue()
    {
        $obj = $this->getView();

        $item = new SWIFT_KnowledgebaseArticle(new \SWIFT_DataID(1));
        $this->assertTrue($obj->RenderInfoBox($item),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderInfoBox($item);
    }

    /**
     * @throws SWIFT_Exception
     * @throws \Knowledgebase\Models\Category\SWIFT_Category_Exception
     */
    public function testGetCategoriesOnGroupReturnsArray()
    {
        $obj = $this->getView();
        $this->assertFalse($obj->GetCategoriesOnGroup(3),
            'Returns false with invalid category');
        $this->assertFalse($obj->GetCategoriesOnGroup(4),
            'Returns false with categorytype public');
        $this->assertFalse($obj->GetCategoriesOnGroup(1),
            'Returns false with filtered category');
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'fullname' => 'fullname',
            ],
            5 => [
                1 => [1],
            ],
        ]);
        SWIFT::GetInstance()->Cache = $mockCache;
        self::$_skip = true;
        $this->assertNotEmpty($obj->GetCategoriesOnGroup(1));
    }
}

class View_ArticleMock extends View_Article
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

    public function GetCategoriesOnGroup($_knowledgebaseCategoryID = 0)
    {
        if ($_knowledgebaseCategoryID === 2) {
            return [];
        }

        return parent::GetCategoriesOnGroup($_knowledgebaseCategoryID);
    }
}
