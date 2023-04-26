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
* Class View_LinkTest
* @group tickets
*/
class View_LinkTest extends \SWIFT_TestCase
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

        $mock = $this->getMockBuilder('Tickets\Models\Link\SWIFT_TicketLinkType')
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
        $arr = [];
        $this->assertCount(2, $obj::GridRender($arr), 'Returns array');
    }

    /**
     * @return View_LinkMock
     */
    private function getView()
    {
        return $this->getMockObject('Tickets\Admin\View_LinkMock');
    }
}

/**
 * Class View_LinkMock
 *
 * @property \PHPUnit_Framework_MockObject_MockObject|\Base\Library\UserInterface\SWIFT_UserInterfaceGrid UserInterfaceGrid
 * @package Tickets\Admin
 */
class View_LinkMock extends View_Link
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

