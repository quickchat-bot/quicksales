<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Knowledgebase\Admin\LoaderMock;
use Parser\Models\Rule\SWIFT_ParserRule;
use SWIFT_Exception;
use Tickets\Library\Flag\SWIFT_TicketFlag;

/**
 * Class View_RuleTest
 * @group parser
 * @group parser-admin
 */
class View_RuleTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\View_Rule', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getMocked();

        $parserRuleMock = $this->getMockBuilder(SWIFT_ParserRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parserRuleMock->method('GetParserRuleID')->willReturn(1);

        $props = [
            'ruletype' => 2
        ];

        $parserRuleMock->method('GetProperty')->willReturnCallback(function ($x) use (&$props) {
            return $props[$x] ?? $x;
        });

        $_POST['departmentid'] = '1';
        $_POST['addtags'] = ['tag1'];
        $_POST['removetags'] = ['tag1'];

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'sortorder' => 10
        ]);

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'departmentcache')
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ]
                ];

            if ($x == 'staffcache')
                return [1 => []];

            if ($x == 'tickettypecache')
                return [1 => ['departmentid' => '0']];

            if ($x == 'statuscache')
                return [1 => ['departmentid' => '0']];

            if ($x == 'prioritycache')
                return [1 => []];

            if ($x == 'slaplancache')
                return [1 => ['isenabled' => '0']];
        };

        $this->expectOutputRegex('/.*/');

        $this->assertTrue($obj->Render(SWIFT_UserInterface::MODE_EDIT, $parserRuleMock),
            'Returns true');

        $_POST['rulecriteria'] = [[1, 2, 3]];

        $this->assertTrue($obj->Render(SWIFT_UserInterface::MODE_INSERT, $parserRuleMock),
            'Returns true');

        $obj->SetIsClassLoaded(false);

        $this->assertFalse($obj->Render(SWIFT_UserInterface::MODE_INSERT),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderGridReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->UserInterfaceGrid->method('GetMode')->willReturn(SWIFT_UserInterfaceGrid::MODE_SEARCH);

        $this->assertTrue($obj->RenderGrid(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'RenderGrid');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertTrue(is_array($obj->GridRender(['isenabled' => '0'])),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderRuleActionReturnsString()
    {
        $obj = $this->getMocked();

        $actions = [];

        $this->assertFalse($obj->RenderRuleAction($actions),
            'Returns false');

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'departmentcache')
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ]
                ];

            if ($x == 'staffcache')
                return [1 => []];

            if ($x == 'tickettypecache')
                return [1 => ['departmentid' => '0']];

            if ($x == 'statuscache')
                return [1 => ['departmentid' => '0']];

            if ($x == 'prioritycache')
                return [1 => []];

            if ($x == 'slaplancache')
                return [1 => ['isenabled' => '0']];

            if ($x == 'prioritycache')
                return [1 => []];
        };

        $actions = ['name' => 'reply'];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'forward'];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'ignore'];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'noautoresponder'];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'noalertrules'];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'noticket'];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'department', 'typeid' => '1'];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'status', 'typeid' => 1];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'tickettype', 'typeid' => 1];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'priority', 'typeid' => 1];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'owner', 'typeid' => 1];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'flagticket', 'typeid' => 'test'];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'movetotrash', 'typeid' => 1];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'slaplan', 'typeid' => 1];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'addnote'];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'addtags', 'typedata' => json_encode(['tag1'])];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');

        $actions = ['name' => 'removetags', 'typedata' => json_encode(['tag1'])];
        $this->assertTrue(is_string($obj->RenderRuleAction($actions)),
            'Returns string');


        $this->assertClassNotLoaded($obj, 'RenderRuleAction', $actions);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|View_RuleMock
     */
    private function getMocked()
    {
        $ticketFlagMock = $this->getMockBuilder(SWIFT_TicketFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ticketFlagMock->method('GetFlagList')->willReturn(['test' => 'test']);

        return $this->getMockObject('Parser\Admin\View_RuleMock', ['TicketFlag' => $ticketFlagMock]);
    }
}

class View_RuleMock extends View_Rule
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

