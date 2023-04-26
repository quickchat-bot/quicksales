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

namespace Knowledgebase\Admin;

use SWIFT_Exception;

/**
 * Class View_MaintenanceTest
 * @group knowledgebase
 */
class View_MaintenanceTest extends \SWIFT_TestCase
{
    /**
     * @return View_MaintenanceMock
     * @throws SWIFT_Exception
     */
    public function getView()
    {
        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTab = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTb = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceToolbar')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockProperty($mockInt, 'Toolbar', $mockTb);
        $this->mockProperty($mockTab, 'Toolbar', $mockTb);

        $mockInt->method('AddTab')->willReturn($mockTab);
        $this->mockProperty($mockTab, 'UserInterface', $mockInt);

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            [
                'title' => 'title',
                'tgroupid' => '1',
            ]
        ]);

        return new View_MaintenanceMock([
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
        ]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getView();
        $this->assertTrue($obj->Render());

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReIndexDataDisplaysHtml()
    {
        $obj = $this->getView();
        $this->expectOutputRegex('/<table cellpadding="0"/');
        $obj->RenderReIndexData(100, '', 0, 0, 0, 0);

        $this->expectOutputRegex('/<table cellpadding="0"/');
        $obj->RenderReIndexData(100, 'http://', 0, 0, 0, 0);

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderReIndexData(0, '', 0, 0, 0, 0);
    }
}

class View_MaintenanceMock extends View_Maintenance
{
    /**
     * View_MaintenanceMock constructor.
     * @param array $services
     * @throws SWIFT_Exception
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }

        parent::__construct();
    }

    public function Initialize()
    {
        return true;
    }
}
