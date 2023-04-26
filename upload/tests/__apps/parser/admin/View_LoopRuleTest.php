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
use Parser\Models\Loop\SWIFT_LoopRule;
use SWIFT_Exception;

/**
 * Class View_LoopRuleTest
 * @group parser
 * @group parser-admin
 */
class View_LoopRuleTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\View_LoopRule', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getMocked();

        $loopRuleMock = $this->getMockBuilder(SWIFT_LoopRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loopRuleMock->method('GetLoopRuleID')->willReturn(1);

        $_POST['_isDialog'] = 1;

        $this->assertTrue($obj->Render(SWIFT_UserInterface::MODE_EDIT, $loopRuleMock),
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
     * @return \PHPUnit_Framework_MockObject_MockObject|View_LoopRuleMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Admin\View_LoopRuleMock');
    }
}

class View_LoopRuleMock extends View_LoopRule
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

