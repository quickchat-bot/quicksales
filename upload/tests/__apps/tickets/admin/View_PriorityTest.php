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
 * Class View_PriorityTest
 * @group tickets
 */
class View_PriorityTest extends \SWIFT_TestCase
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

        $this->assertTrue($obj->Render(2),
            'Returns true in insert mode');

        $mock = $this->getMockBuilder('Tickets\Models\Priority\SWIFT_TicketPriority')
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
        $this->assertCount(2, $obj::GridRender($arr), 'Returns array');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_PriorityMock
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

        return $this->getMockObject('Tickets\Admin\View_PriorityMock', array_merge($services, [
            'Controller' => $ctr,
        ]));
    }
}

/**
 * Class View_PriorityMock
 *
 * @property \PHPUnit_Framework_MockObject_MockObject|\Base\Library\UserInterface\SWIFT_UserInterfaceGrid UserInterfaceGrid
 * @package Tickets\Admin
 */
class View_PriorityMock extends View_Priority
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

