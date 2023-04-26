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
 * Class View_AutoCloseTest
 * @group tickets
 */
class View_AutoCloseTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $this->expectOutputRegex('/desc_articketstatusid/');

        $obj = $this->getMocked();

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'markasresolved' => '0',
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode without POST and markasresolved = 0');

        ///////////////

        $_POST['rulecriteria'] = 1;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false);
        $mockDb->method('QueryFetch')->willReturn([
            'sortorder' => 1,
        ]);

        $obj = $this->getMocked([
            'Database' => $mockDb,
        ]);

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                'markasresolved' => '1',
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode');

        $mock = $this->getMockBuilder('Tickets\Models\AutoClose\SWIFT_AutoCloseRule')
            ->disableOriginalConstructor()
            ->getMock();

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
        $this->assertCount(5, $obj::GridRender($arr), 'Returns array');
        $arr = [
            'targetticketstatusid' => 1,
        ];
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [1 => 1],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;
        $this->assertCount(5, $obj::GridRender($arr), 'Returns array');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_AutoCloseMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Admin\View_AutoCloseMock', $services);
    }
}

/**
 * Class View_AutoCloseMock
 *
 * @property \PHPUnit_Framework_MockObject_MockObject|\Base\Library\UserInterface\SWIFT_UserInterfaceGrid UserInterfaceGrid
 * @package Tickets\Admin
 */
class View_AutoCloseMock extends View_AutoClose
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

