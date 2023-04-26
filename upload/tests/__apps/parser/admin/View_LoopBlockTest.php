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
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_LoopBlockTest
 * @group parser
 * @group parser-admin
 */
class View_LoopBlockTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\View_LoopBlock', $obj);
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
     * @return \PHPUnit_Framework_MockObject_MockObject|View_LoopBlockMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Admin\View_LoopBlockMock');
    }
}

class View_LoopBlockMock extends View_LoopBlock
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

