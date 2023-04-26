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
 * Class View_SLATest
 * @group tickets
 */
class View_SLATest extends \SWIFT_TestCase
{
    public static $_next = false;

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $this->expectOutputRegex('/QueueFunction/');

        $obj = $this->getMocked();

        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode without POST and markasresolved = 0');

        ///////////////

        $_POST['rulecriteria'] = 1;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $mockDb->method('NextRecord')->willReturnCallback(function() {
            self::$_next = !self::$_next;

            return self::$_next;
        });
        $mockDb->method('QueryFetch')->willReturn([
            'sortorder' => 1,
        ]);
        $this->mockProperty($mockDb, 'Record', [
            'slaholidayid' => 1,
        ]);

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'departmentapp' => 'tickets',
            ],
        ]);

        $obj = $this->getMocked([
            'Database' => $mockDb,
            'Cache' => $mockCache,
        ]);

        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode');

        $mock = $this->getMockBuilder('Tickets\Models\SLA\SWIFT_SLA')
            ->disableOriginalConstructor()
            ->getMock();

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
        $arr = [];
        $this->assertCount(5, $obj::GridRender($arr), 'Returns array');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_SLAMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Admin\View_SLAMock', $services);
    }
}

/**
 * Class View_SLAMock
 *
 * @property \PHPUnit_Framework_MockObject_MockObject|\Base\Library\UserInterface\SWIFT_UserInterfaceGrid UserInterfaceGrid
 * @package Tickets\Admin
 */
class View_SLAMock extends View_SLA
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

