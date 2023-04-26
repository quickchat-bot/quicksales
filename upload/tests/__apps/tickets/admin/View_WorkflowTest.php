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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_WorkflowTest
 * @group tickets
 */
class View_WorkflowTest extends \SWIFT_TestCase
{
    public static $_next = false;
    public static $_count = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $this->expectOutputRegex('/criteriaStore/');

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next = !self::$_next;

            return self::$_next;
        });
        $mockDb->method('QueryFetch')->willReturn([
            'sortorder' => 1,
        ]);
        $this->mockProperty($mockDb, 'Record', [
            'ticketworkflownotificationid' => 1,
            'staffgroupid' => 1,
            'slaholidayid' => 1,
            'isenabled' => '0',
        ]);

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'departmentapp' => 'tickets',
                'parentdepartmentid' => '0',
            ],
        ]);

        $obj = $this->getMocked([
            'Database' => $mockDb,
            'Cache' => $mockCache,
        ]);

        \SWIFT::GetInstance()->Cache = $mockCache;
        \SWIFT::GetInstance()->Database = $mockDb;

        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode');

        $_POST['rulecriteria'] = 1;

        $mock = $this->getMockBuilder('Tickets\Models\Workflow\SWIFT_TicketWorkflow')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('GetTicketWorkflowID')->willReturn(1);
        $mock->method('GetLinkedStaffGroupIDList')->willReturn([1]);

        $mock->method('GetProperty')->willReturnCallback(function ($x) {
            if (!isset(self::$_count[$x])) {
                self::$_count[$x] = 0;
            }
            self::$_count[$x]++;

            return self::$_count[$x];
        });
        $mock->method('GetActions')->willReturnOnConsecutiveCalls([
            ['name' => 'department'],
            ['name' => 'status'],
            ['name' => 'priority'],
            ['name' => 'owner'],
            ['name' => 'tickettype'],
            ['name' => 'addtags'],
            ['name' => 'removetags'],
            ['name' => 'flagticket'],
            ['name' => 'slaplan'],
            ['name' => 'bayesian'],
            ['name' => 'trash'],
            ['name' => 'addnote'],
        ], []);

        $_POST['slaholidays'] = [1 => 1];
        $this->assertTrue($obj->Render(1, $mock),
            'Returns true in edit mode');

        unset($_POST['slaholidays']);
        $this->assertTrue($obj->Render(1, $mock),
            'Returns true in edit mode');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(1),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderGridReturnsTrue()
    {
        $obj = $this->getMocked();
        $obj->UserInterfaceGrid->method('GetMode')->willReturn(2);
        $this->assertTrue($obj->RenderGrid(),
            'Returns true');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderGrid(),
            'Returns false if class is not loaded');
    }

    public function testGridRenderReturnsArray()
    {
        $obj = $this->getMocked();
        $arr = [
            'isenabled' => '0',
        ];
        $this->assertCount(4, $obj::GridRender($arr), 'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReturnRuleActionStringThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertFalse($obj->ReturnRuleActionString(['name' => 'invalid', 'typeid' => 2]));
        $this->assertClassNotLoaded($obj, 'ReturnRuleActionString', []);
    }

    /**
     * @dataProvider ruleActionProvider
     * @param $arr
     * @param $expected
     * @throws SWIFT_Exception
     */
    public function testReturnRuleActionStringWorks($arr, $expected)
    {
        $obj = $this->getMocked();

        $this->assertContains($expected, $obj->ReturnRuleActionString($arr));
    }

    public function ruleActionProvider()
    {
        return [
            [['name' => 'department', 'typeid' => 1], 'department'],
            [['name' => 'status', 'typeid' => 1], 'status'],
            [['name' => 'priority', 'typeid' => 1], 'priority'],
            [['name' => 'owner', 'typeid' => 1], 'staff'],
            [['name' => 'owner', 'typeid' => -1], 'staff'],
            [['name' => 'tickettype', 'typeid' => 1], 'tickettype'],
            [['name' => 'addtags', 'typeid' => 1, 'typedata' => serialize([])], 'addtags'],
            [['name' => 'removetags', 'typeid' => 1, 'typedata' => serialize([])], 'removetags'],
            [['name' => 'flagticket', 'typeid' => 1], 'flag'],
            [['name' => 'flagticket', 'typeid' => 2], 'nochange'],
            [['name' => 'slaplan', 'typeid' => 1], 'slaplan'],
            [['name' => 'bayesian', 'typeid' => 1], 'bayesian'],
            [['name' => 'trash', 'typeid' => 1], 'trash'],
            [['name' => 'addnote', 'typeid' => 1, 'typedata' => serialize([])], 'addnote'],
        ];
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_WorkflowMock
     */
    private function getMocked(array $services = [])
    {
        $flag = $this->getMockBuilder('Tickets\Library\Flag\SWIFT_TicketFlag')
            ->disableOriginalConstructor()
            ->getMock();
        $flag->method('GetFlagList')->willReturn([
            1 => 'flag',
        ]);

        $cfrw = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldRendererWorkflow')
            ->disableOriginalConstructor()
            ->getMock();

        $ctr = $this->getMockBuilder('Tickets\Admin\Controller_Workflow')
            ->disableOriginalConstructor()
            ->getMock();
        $ctr->CustomFieldRendererWorkflow = $cfrw;
        $ctr->TicketFlag = $flag;

        return $this->getMockObject('Tickets\Admin\View_WorkflowMock', array_merge([
            'Controller' => $ctr,
        ], $services));
    }
}

/**
 * Class View_WorkflowMock
 *
 * @property \PHPUnit_Framework_MockObject_MockObject|\Base\Library\UserInterface\SWIFT_UserInterfaceGrid UserInterfaceGrid
 * @package Tickets\Admin
 */
class View_WorkflowMock extends View_Workflow
{
    public function __construct($services = [])
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

