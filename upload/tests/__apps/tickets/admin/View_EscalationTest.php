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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_EscalationTest
 * @group tickets
 */
class View_EscalationTest extends \SWIFT_TestCase
{
    public static $_next = false;
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $flag = $this->getMockBuilder('Tickets\Library\Flag\SWIFT_TicketFlag')
            ->disableOriginalConstructor()
            ->getMock();
        $flag->method('GetFlagList')->willReturn([
            1 => 'flag',
        ]);

        $db = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getMocked([
            'TicketFlag' => $flag,
            'Database' => $db,
        ]);

        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode');

        /////////////////

        $db = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('NextRecord')->willReturnCallback(function() {
            self::$_next = !self::$_next;

            return self::$_next;
        });
        $this->mockProperty($db, 'Record', [
           'isenabled' => '0',
        ]);

        $obj = $this->getMocked([
            'TicketFlag' => $flag,
            'Database' => $db,
        ]);

        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode');

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

        \SWIFT::GetInstance()->Cache = $mockCache;

        $mock = $this->getMockBuilder('Tickets\Models\Escalation\SWIFT_EscalationRule')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetProperty')->willReturn(1);
        $this->assertTrue($obj->Render(1, $mock),
            'Returns true in edit mode');

        $mock = $this->getMockBuilder('Tickets\Models\Escalation\SWIFT_EscalationRule')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetProperty')->willReturn(2);
        $this->assertTrue($obj->Render(1, $mock),
            'Returns true in edit mode with TYPE_RESOLUTIONDUE');

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

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $obj = $this->getMocked();
        $arr = [];
        $this->assertCount(4, $obj::GridRender($arr), 'Returns array');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_EscalationMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Admin\View_EscalationMock', $services);
    }
}

/**
 * Class View_EscalationMock
 *
 * @property \PHPUnit_Framework_MockObject_MockObject|\Base\Library\UserInterface\SWIFT_UserInterfaceGrid UserInterfaceGrid
 * @package Tickets\Admin
 */
class View_EscalationMock extends View_Escalation
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

