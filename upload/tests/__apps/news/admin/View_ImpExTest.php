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

namespace News\Admin;

/**
 * Class View_ImpExTest
 * @group news
 */
class View_ImpExTest extends \SWIFT_TestCase
{
    public function getView()
    {
        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockView = $this->getMockBuilder('News\Admin\View_ImpEx')
            ->disableOriginalConstructor()
            ->getMock();

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTab = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTb = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceToolbar')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockProperty($mockTab, 'Toolbar', $mockTb);

        $mockInt->method('AddTab')->willReturn($mockTab);

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            [
                'title' => 'title',
                'tgroupid' => '1',
            ]
        ]);

        return new View_ImpExMock([
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
            'View' => $mockView,
            'Cache' => $mockCache,
        ]);
    }

    public function testRenderImpExTrue()
    {
        $obj = $this->getView();
        $this->assertTrue($obj->RenderImpEx(true));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderImpEx());
    }
}

class View_ImpExMock extends View_ImpEx
{
    /**
     * View_ImpExMock constructor.
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();

        parent::__construct();

        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
    }
}
