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

namespace Troubleshooter\Staff;

use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;

/**
 * Class StaffView_CategoryTest
 * @group troubleshooter
 */
class StaffView_CategoryTest extends \SWIFT_TestCase
{
    public static $_next = false;
    private $mockDb;

    public function setUp()
    {
        parent::setUp();

        $this->mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDb->method('QueryFetch')->willReturnOnConsecutiveCalls([
            'troubleshootercategoryid' => 1,
        ], false);

        $this->mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next = !self::$_next;
            return self::$_next;
        });

        $this->mockProperty($this->mockDb, 'Record', [
            'staffgroupid' => 1,
            'usergroupid' => 1,
        ]);
    }

    /**
     * @return View_Category
     * @throws \SWIFT_Exception
     */
    public function getView()
    {
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
                'SetSearchQuery'
            ])
            ->getMock();

        $mockGrid->method('GetMode')->willReturn(2);
        $mockGrid->method('BuildSQLSearch')->willReturnArgument(0);

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturn('%s');

        \SWIFT::GetInstance()->Language = $mockLang;
        \SWIFT::GetInstance()->Database = $this->mockDb;

        $obj = new View_Category();
        $this->mockProperty($obj, 'Load', new LoaderMock());
        $this->mockProperty($obj, 'Database', $this->mockDb);
        $this->mockProperty($obj, 'UserInterface', $mockInt);
        $this->mockProperty($obj, 'UserInterfaceGrid', $mockGrid);
        $this->mockProperty($obj, 'Language', $mockLang);

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

        $dataStore = [
            'troubleshootercategoryid' => 1,
            'title' => 'title',
            'description' => 'description',
            'categorytype' => 2,
            'displayorder' => 1,
            'uservisibilitycustom' => 1,
            'staffvisibilitycustom' => 1,
        ];

        $categoryObject = new SWIFT_TroubleshooterCategory(new \SWIFT_DataStore($dataStore));
        $this->mockProperty($categoryObject, 'Database', $this->mockDb);

        $this->assertTrue($obj->Render(1, $categoryObject),
            'Returns true after render with edit mode and category type is public');

        $dataStore['categorytype'] = 3;
        $categoryObject = new SWIFT_TroubleshooterCategory(new \SWIFT_DataStore($dataStore));
        $this->mockProperty($categoryObject, 'Database', $this->mockDb);

        $this->assertTrue($obj->Render(1, $categoryObject),
            'Returns true after render with edit mode and category type is private');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(0),
            'Returns false if class is not loaded');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderGridReturnsTrue()
    {
        $obj = $this->getView();

        $this->assertTrue($obj->RenderGrid(),
            'Returns true after render');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderGrid(),
            'Returns false if class is not loaded');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $dataStore['categorytype'] = 3;
        $obj = $this->getView();
        $actual = $obj::GridRender($dataStore);
        $this->assertInternalType('array', $actual,
            'The static method should return an array');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderViewAllReturnsTrue()
    {
        $obj = $this->getView();

        $this->assertTrue($obj->RenderViewAll(''),
            'Returns true after render with empty container');

        $this->assertTrue($obj->RenderViewAll([1 => [
            'description' => 'description',
            'title' => 'title',
            'views' => 'views',
        ]]),
            'Returns true after render with valid container');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderViewAll([]),
            'Returns false if class is not loaded');
    }
}
