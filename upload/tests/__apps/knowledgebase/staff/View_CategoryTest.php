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
use Knowledgebase\Models\Category\SWIFT_KnowledgebaseCategory;
use SWIFT;
use SWIFT_Exception;

/**
 * Class View_CategoryTest
 * @group knowledgebase
 */
class View_CategoryTest extends \SWIFT_TestCase
{
    public static $_next = false;

    public function setUp()
    {
        parent::setUp();

        unset($_POST);
    }

    /**
     * @return View_Category
     * @throws SWIFT_Exception
     */
    public function getView()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturn([
            'kbcategoryid' => 1,
            'visibilitytype' => 'private',
            'categorytitle' => 'title',
            'title' => 1,
            'categorytype' => 4,
            'parentkbcategoryid' => 1,
            'displayorder' => 1,
            'uservisibilitycustom' => 1,
            'staffvisibilitycustom' => 1,
            'ispublished' => 1,
            'allowcomments' => 1,
            'allowrating' => 1,
            'articlesortorder' => 1,
        ]);

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next = !self::$_next;
            return self::$_next;
        });

        $this->mockProperty($mockDb, 'Record', [
            'staffgroupid' => 1,
            'usergroupid' => 1,
        ]);

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->setMethods(['Start', 'End', 'AppendHTML', 'AddTab', 'IsAjax'])
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

        $mockCache->method('Get')->willReturn([1 => [1 => 1]]);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStaff->method('GetProperty')->willReturn(1);

        SWIFT::GetInstance()->Cache = $mockCache;
        SWIFT::GetInstance()->Staff = $mockStaff;
        SWIFT::GetInstance()->Language = $mockLang;
        SWIFT::GetInstance()->Database = $mockDb;

        $obj = new View_CategoryMock([
            'Database' => $mockDb,
            'UserInterface' => $mockInt,
            'UserInterfaceGrid' => $mockGrid,
            'Language' => $mockLang,
        ]);

        return $obj;
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getView();

        $this->assertTrue($obj->Render(2),
            'Returns true after render with insert mode');

        $categoryObject = new SWIFT_KnowledgebaseCategory(new \SWIFT_DataID(1));

        $this->assertTrue($obj->Render(1, $categoryObject, 1),
            'Returns true after render with edit mode and category type is global');

        $store = [
            'kbcategoryid' => 1,
            'categorytype' => 2,
            'title' => 2,
            'parentkbcategoryid' => 1,
            'displayorder' => 1,
            'uservisibilitycustom' => 1,
            'staffvisibilitycustom' => 1,
            'ispublished' => 1,
            'allowcomments' => 1,
            'allowrating' => 1,
            'articlesortorder' => 2,
        ];
        $categoryObject = new SWIFT_KnowledgebaseCategory(new \SWIFT_DataStore($store));
        $this->assertTrue($obj->Render(1, $categoryObject),
            'Returns true after render with edit mode and category type is public');

        $store['categorytype'] = 3;
        $store['articlesortorder'] = 3;
        $categoryObject = new SWIFT_KnowledgebaseCategory(new \SWIFT_DataStore($store));
        $this->assertTrue($obj->Render(1, $categoryObject),
            'Returns true after render with edit mode and category type is private');

        $store['articlesortorder'] = 4;
        $categoryObject = new SWIFT_KnowledgebaseCategory(new \SWIFT_DataStore($store));
        $this->assertTrue($obj->Render(1, $categoryObject),
            'Returns true after render with edit mode and sort by creation date');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(0),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderTabsReturnsTrue()
    {
        $obj = $this->getView();

        $this->assertTrue($obj->RenderTabs(),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderTabs();
    }
}

class View_CategoryMock extends View_Category
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
}
