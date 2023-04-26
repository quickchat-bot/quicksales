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
 * Class View_TypeTest
 * @group tickets
 */
class View_TypeTest extends \SWIFT_TestCase
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
            'usergroupid' => 1,
        ]);
        $mockDb->Record = [
            'usergroupid' => 1,
        ];

        $obj = $this->getMocked([
            'Database' => $mockDb,
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
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode');

        $mock = $this->getMockBuilder('Tickets\Models\Type\SWIFT_TicketType')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('GetLinkedUserGroupIDList')->willReturn([1]);

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
     * @return \PHPUnit_Framework_MockObject_MockObject|View_TypeMock
     */
    private function getMocked(array $services = [])
    {
        $ctr = $this->getMockBuilder('Tickets\Admin\Controller_Priority')
            ->disableOriginalConstructor()
            ->getMock();
        $lpl = $this->getMockBuilder('Base\Library\Language\SWIFT_LanguagePhraseLinked')
            ->disableOriginalConstructor()
            ->getMock();
        $ctr->LanguagePhraseLinked = $lpl;

        return $this->getMockObject('Tickets\Admin\View_TypeMock', array_merge($services, [
            'Controller' => $ctr,
        ]));
    }
}

/**
 * Class View_TypeMock
 *
 * @property \PHPUnit_Framework_MockObject_MockObject|\Base\Library\UserInterface\SWIFT_UserInterfaceGrid UserInterfaceGrid
 * @package Tickets\Admin
 */
class View_TypeMock extends View_Type
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

