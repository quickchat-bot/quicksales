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
* Class View_ScheduleTest
* @group tickets
*/
class View_ScheduleTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $_POST['_isDialog'] = 1;

        $obj = $this->getView();
        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode');

        $mock = $this->getMockBuilder('Tickets\Models\SLA\SWIFT_SLASchedule')
            ->disableOriginalConstructor()
            ->getMock();

        $_POST['sladay']['sunday'] = 1;
        $_POST['dayHourOpen']['sunday'] = [1];
        $_POST['dayMinuteOpen']['sunday'] = [1];
        $_POST['dayHourClose']['sunday'] = [1];
        $_POST['dayMinuteClose']['sunday'] = [1];
        $_POST['rowId']['sunday'] = [1];
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
        $obj = $this->getView();
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
        $obj = $this->getView();
        $arr = ['extension' => 'gif'];
        $this->assertCount(4, $obj::GridRender($arr), 'Returns array');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|View_ScheduleMock
     */
    private function getView()
    {
        return $this->getMockObject('Tickets\Admin\View_ScheduleMock');
    }
}

class View_ScheduleMock extends View_Schedule
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

