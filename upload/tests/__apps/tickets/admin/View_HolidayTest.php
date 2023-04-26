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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_HolidayTest
 * @group tickets
 */
class View_HolidayTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $_POST['_isDialog'] = 1;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false);
        $mockDb->method('QueryFetch')->willReturn([
            'slaplanid' => 1,
        ]);
        $mockDb->Record = [
            'slaplanid' => 1,
        ];

        $obj = $this->getMocked([
            'Database' => $mockDb,
        ]);

        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode');

        $mock = $this->getMockBuilder('Tickets\Models\SLA\SWIFT_SLAHoliday')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('GetSLAPlanIDList')->willReturn([1]);

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

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $obj = $this->getMocked();
        $arr = [];
        $this->assertCount(3, $obj::GridRender($arr), 'Returns array');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_HolidayMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Admin\View_HolidayMock', $services);
    }
}

/**
 * Class View_HolidayMock
 *
 * @property \PHPUnit_Framework_MockObject_MockObject|\Base\Library\UserInterface\SWIFT_UserInterfaceGrid UserInterfaceGrid
 * @package Tickets\Admin
 */
class View_HolidayMock extends View_Holiday
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

