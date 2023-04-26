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

namespace News\Library\Render;

use News\Admin\LoaderMock;
use SWIFT;

/**
 * Class SWIFT_NewsRenderManagerTest
 * @group news
 */
class SWIFT_NewsRenderManagerTest extends \SWIFT_TestCase
{
    public static $_count = [];

    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);

        $this->mockProperty($mockDb, 'Record', [
            'newscategoryid' => 1,
            'totalitems' => 1,
        ]);

        SWIFT::GetInstance()->Database = $mockDb;

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetProperty')->willReturnArgument(1);

        SWIFT::GetInstance()->Staff = $mockStaff;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturnOnConsecutiveCalls([
            '1' => [
                'newsitemcount' => '1',
            ]
        ]);

        SWIFT::GetInstance()->Cache = $mockCache;

        SWIFT::GetInstance()->Load = new LoaderMock();
    }

    public function getLibrary()
    {
        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            return $x;
        });
        \SWIFT::GetInstance()->Language = $mockLang;

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturnCallback(function ($x) {
            if (!isset(self::$_count[$x])) {
                self::$_count[$x] = 0;
            }

            self::$_count[$x]++;

            if ($x === 'nw_enablestaffdashboard' && self::$_count[$x] > 1) {
                return '1';
            }

            return '0';
        });

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        return new SWIFT_NewsRenderManagerMock([
            'Cache' => SWIFT::GetInstance()->Cache,
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Template' => $mockTpl,
        ]);
    }

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getLibrary();
        $this->assertInstanceOf('News\Library\Render\SWIFT_NewsRenderManager', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderTreeReturnsHtml()
    {
        $obj = $this->getLibrary();
        $this->assertContains('<ul class="swifttree">', $obj->RenderTree());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderTree();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderViewNewsTreeReturnsHtml()
    {
        $obj = $this->getLibrary();
        $this->assertContains('<ul class="swifttree">', $obj->RenderViewNewsTree());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderViewNewsTree();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testRenderWelcomeTabReturnsHtml()
    {
        $obj = $this->getLibrary();

        $this->assertEmpty($obj->RenderWelcomeTab(),
            'Returns empty string with nw_enablestaffdashboard = 0');

        $this->assertContains('containercontenttable', $obj->RenderWelcomeTab(),
            'Returns HTML for several items');

        $this->assertContains('<div class="dashboardmsg">', $obj->RenderWelcomeTab(),
            'Returns HTML for no items');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderWelcomeTab();
    }
}

class SWIFT_NewsRenderManagerMock extends SWIFT_NewsRenderManager
{
    /**
     * SWIFT_NewsRenderManagerMock constructor.
     * @param array $services
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
