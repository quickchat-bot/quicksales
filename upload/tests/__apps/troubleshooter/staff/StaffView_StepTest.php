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

use Base\Library\Comment\SWIFT_CommentManager;
use SWIFT_Controller;

/**
 * Class StaffView_StepTest
 * @group troubleshooter
 */
class StaffView_StepTest extends \SWIFT_TestCase
{
    /**
     * @return View_Step
     */
    public function getView()
    {
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            '1' => [
                'type' => 'private',
                'fullname' => 'fullname',
                'departmentapp' => 'tickets',
                'parentdepartmentid' => '2',
            ],
            '2' => [
                'type' => 'public',
                'fullname' => 'fullname',
                'departmentapp' => 'tickets',
                'parentdepartmentid' => '0',
            ]
        ]);

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->mockProperty($mockDb, 'Record', [
            'troubleshootercategoryid' => 1,
            'title' => 'title',
            'categorytype' => 1,
        ]);

        $mockGrid = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceGrid')
            ->disableOriginalConstructor()
            ->setMethods([
                'SetMode',
                'GetMode',
                'BuildSQLSearch',
                'SetSearchStoreOptions',
                'SetQuery',
                'AddField',
                'SetRenderCallback',
                'AddMassAction',
                'Render',
                'GetRenderData',
                'Display',
            ])
            ->getMock();

        $mockGrid->method('GetMode')->willReturn(2);

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->setMethods([
                'Start',
                'End',
                'SetDialogOptions',
                'AddTab',
                'AddNavigationBox',
            ])
            ->getMock();

        $mockTab = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods([
                'Select',
                'RowHTML',
                'SetTabCounter',
                'SetColumnWidth',
                'Text',
            ])
            ->getMock();

        $mockInt->method('AddTab')->willReturn($mockTab);

        $mockTb = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceToolbar')
            ->disableOriginalConstructor()
            ->setMethods(['AddButton'])
            ->getMock();

        $this->mockProperty($mockTab, 'Toolbar', $mockTb);
        $this->mockProperty($mockInt, 'Toolbar', $mockTb);

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            return '%s';
        });

        $mockMgr = $this->getMockBuilder('Base\Library\Comment\SWIFT_CommentManager')
            ->disableOriginalConstructor()
            ->setMethods(['LoadStaffCP'])
            ->getMock();

        \SWIFT::GetInstance()->Language = $mockLang;
        \SWIFT::GetInstance()->Database = $mockDb;
        \SWIFT::GetInstance()->Cache = $mockCache;

        $services = [
            'Cache' => $mockCache,
            'Database' => $mockDb,
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
            'UserInterfaceGrid' => $mockGrid,
            'Controller' => new ControllerMock($mockMgr),
        ];

        return new View_StepMock($services);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderQuickFilterTreeReturnsHtml()
    {
        $obj = $this->getView();
        $this->assertContains('<ul class="swifttree">',
            $obj->RenderQuickFilterTree(),
            'Method returns HTML code');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->RenderQuickFilterTree();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderNewStepDialogReturnsTrue()
    {
        $obj = $this->getView();
        $this->assertTrue($obj->RenderNewStepDialog(),
            'Returns true after rendering');

        $this->assertTrue($obj->RenderNewStepDialog(),
            'Returns true after rendering and there are no records');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderNewStepDialog(),
            'Returns false if class is not loaded');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $obj = $this->getView();
        $fieldContainer = [
            'staffid' => 1,
            'subject' => 'subject',
            'stepstatus' => 2,
            'staffname' => 'staffname',
        ];
        $result = $obj::GridRender($fieldContainer);
        $this->assertArrayHasKey('troubleshootersteps.staffid', $result);

        $this->assertEquals('fullname', $result['troubleshootersteps.staffid'],
            'If staffid is provided, the name is returned from cache');

        unset($fieldContainer['staffid']);
        $result = $obj::GridRender($fieldContainer);

        $this->assertEquals('staffname', $result['troubleshootersteps.staffid'],
            'Returns provided staff name');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderInfoBoxReturnsTrue()
    {
        $obj = $this->getView();

        $mockStep = $this->getMockBuilder('Troubleshooter\Models\Step\SWIFT_TroubleshooterStep')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStep->method('GetProperty')->willReturn(1);

        $this->assertTrue($obj->RenderInfoBox($mockStep),
            'Returns true after rendering');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->RenderInfoBox($mockStep);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderViewStepsReturnsTrue()
    {
        $obj = $this->getView();
        $dataContainer = [
            '_extendedTitle' => 'title',
            '_troubleshooterStepHasAttachments' => '1',
            '_troubleshooterStepCount' => '1',
            '_troubleshooterStepAllowComments' => '1',
            '_attachmentContainer' => [
                '1' => [
                    'link' => 'link',
                    'icon' => 'icon',
                    'name' => 'name',
                    'size' => '1',
                ],
            ],
            '_troubleshooterStepContainer' => [
                '1' => [
                    'troubleshooterstepid' => '1',
                    'subject' => 'subject',
                ],
            ],
        ];
        $this->assertTrue($obj->RenderViewSteps($dataContainer),
            'Returns true after rendering');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderViewSteps($dataContainer),
            'Returns false if class is not loaded');
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderTabsReturnsTrue()
    {
        $obj = $this->getView();

        $this->assertTrue($obj->RenderTabs(),
            'Returns true after rendering');

        $_POST['_searchQuery'] = 'search';
        $this->assertTrue($obj->RenderTabs(),
            'Returns true after rendering and search string is provided');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->RenderTabs();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getView();

        $mockStep = $this->getMockBuilder('Troubleshooter\Models\Step\SWIFT_TroubleshooterStep')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStep->method('GetProperty')->willReturn(1);
        $mockStep->method('GetTroubleshooterStepID')->willReturn(1);

        $this->assertTrue($obj->Render(1, $mockStep, 1, 1),
            'Returns true after rendering in edit mode');

        $mockStep = $this->getMockBuilder('Troubleshooter\Models\Step\SWIFT_TroubleshooterStep')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStep->method('GetProperty')->willReturn(2);
        $this->assertTrue($obj->Render(1, $mockStep, 1, 1),
            'Returns true after rendering in edit mode and stepstatus is draft');

        $this->assertTrue($obj->Render(2, $mockStep, 1, 1),
            'Returns true after rendering in insert mode');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(0),
            'Returns false if class is not loaded');
    }
}

class ControllerMock extends SWIFT_Controller
{
    public $CommentManager;

    /**
     * ControllerMock constructor.
     * @param \Base\Library\Comment\SWIFT_CommentManager $mgr
     */
    public function __construct(SWIFT_CommentManager $mgr)
    {
        $this->CommentManager = $mgr;
    }
}

class View_StepMock extends View_Step
{
    /**
     * View_StepMock constructor.
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
