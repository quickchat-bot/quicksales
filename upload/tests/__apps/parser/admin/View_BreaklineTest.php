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
use Parser\Models\Breakline\SWIFT_Breakline;
use SWIFT_Exception;

/**
 * Class View_BreaklineTest
 * @group parser
 * @group parser-admin
 */
class View_BreaklineTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\View_Breakline', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getMocked();

        $breaklineMock = $this->getMockBuilder(SWIFT_Breakline::class)
            ->disableOriginalConstructor()
            ->getMock();

        $breaklineMock->method('GetBreaklineID')->willReturn(1);

        $_POST['_isDialog'] = 1;

        $this->assertTrue($obj->Render(SWIFT_UserInterface::MODE_EDIT, $breaklineMock),
            'Returns true');

        $this->assertTrue($obj->Render(SWIFT_UserInterface::MODE_INSERT),
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

        $this->assertTrue(is_array($obj->GridRender([])),
            'Returns array');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|View_BreaklineMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Admin\View_BreaklineMock');
    }
}

class View_BreaklineMock extends View_Breakline
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

