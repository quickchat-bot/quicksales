<?php
/**
 * ###############################################
 *
 * Archiver App for Kayako
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       archiver
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       https://github.com/trilogy-group/kayako-classic-archiver/blob/master/LICENSE
 * @link          https://github.com/trilogy-group/kayako-classic-archiver
 *
 * ###############################################
 */

namespace Archiver\Admin;

use SWIFT;
use SWIFT_Exception;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;

/**
 * Class View_ManagerTest
 * @group archiver
 */
class View_ManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsViewInstance()
    {
        $view = new View_Manager();
        $this->assertInstanceOf('\Archiver\Admin\View_Manager', $view);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderSearchFormReturnsTrue()
    {
        $view = new View_Manager();
        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTb = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceToolbar')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockProperty($mockInt, 'Toolbar', $mockTb);

        $mockTab = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
            ->disableOriginalConstructor()
            ->setMethods(['RowHTML'])
            ->getMock();

        $mockTab->method('RowHTML')->willReturnSelf();

        $mockInt->method('AddTab')->willReturn($mockTab);

        $this->mockProperty($view, 'UserInterface', $mockInt);

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockProperty($view, 'Template', $mockTpl);

        $this->assertTrue($view->RenderSearchForm());

        $view->SetIsClassLoaded(false);
        $this->assertFalse($view->RenderSearchForm());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderTrashFormReturnsTrue()
    {
        $view = new View_Manager();
        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTb = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceToolbar')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTab = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
            ->disableOriginalConstructor()
            ->setMethods(['RowHTML'])
            ->getMock();

        $mockTab->method('RowHTML')->willReturnSelf();

        $mockInt->method('AddTab')->willReturn($mockTab);

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockProperty($mockInt, 'Toolbar', $mockTb);
        $this->mockProperty($view, 'UserInterface', $mockInt);
        $this->mockProperty($view, 'Template', $mockTpl);

        $this->assertTrue($view->RenderTrashForm());

        $view->SetIsClassLoaded(false);
        $this->assertFalse($view->RenderTrashForm());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderSearchGridReturnsTrue()
    {
        $view = new View_Manager();
        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $mockInt->method('GetMode')->willReturn(SWIFT_UserInterfaceGrid::MODE_SEARCH);

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $view->Load = new LoaderMock($view);

        $this->mockProperty($view, 'UserInterfaceGrid', $mockInt);
        $this->mockProperty($view, 'Template', $mockTpl);

        $this->expectOutputString('<script>location.href = \'http://\';</script>');
        $this->assertTrue($view->RenderSearchGrid('', '', '', '', 0, 0, false));

        $view->SetIsClassLoaded(false);
        $this->assertFalse($view->RenderSearchGrid('', '', '', '', 0, 0, false));
    }

}

class LoaderMock {

    /**
     * LoaderMock constructor.
     * @param View_Manager $view
     */
    public function __construct($view) {
        SWIFT::Set('export_ready', 'http://');
        $view::SearchGridRender(['dateline'=>0]);
    }

    public function Library() {

    }
}
