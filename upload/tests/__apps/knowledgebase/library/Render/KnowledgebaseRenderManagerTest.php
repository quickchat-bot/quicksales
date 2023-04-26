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

namespace Knowledgebase\Library\Render;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
* Class KnowledgebaseRenderManagerTest
* @group knowledgebase
*/
class KnowledgebaseRenderManagerTest extends \SWIFT_TestCase
{
    public static $_count = [];

    /**
     * @return SWIFT_KnowledgebaseRenderManagerMock
     * @throws SWIFT_Exception
     */
    public function getLibrary()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);

        $this->mockProperty($mockDb, 'Record', [
            'kbarticleid' => 1,
            'totalitems' => 1,
            'kbcategoryid' => 1,
        ]);

        SWIFT::GetInstance()->Database = $mockDb;

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetProperty')->willReturn(1);

        SWIFT::GetInstance()->Staff = $mockStaff;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([]);

        SWIFT::GetInstance()->Cache = $mockCache;

        SWIFT::GetInstance()->Load = new LoaderMock();

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

        $mockSettings->method('Get')->willReturn('1');

        SWIFT::GetInstance()->Settings = $mockSettings;

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        return new SWIFT_KnowledgebaseRenderManagerMock([
            'Cache' => SWIFT::GetInstance()->Cache,
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Database' => $mockDb,
            'Template' => $mockTpl,
        ]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getLibrary();
        $this->assertInstanceOf('Knowledgebase\Library\Render\SWIFT_KnowledgebaseRenderManager', $obj);
    }

    /**
     * @throws SWIFT_Exception
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
     * @throws SWIFT_Exception
     */
    public function testRenderViewKnowledgebaseTreeReturnsHtml()
    {
        $obj = $this->getLibrary();
        $this->assertContains('<ul class="swifttree">', $obj->RenderViewKnowledgebaseTree());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RenderViewKnowledgebaseTree();
    }

    public function testRenderTreeOrder()
    {
        $services = $this->getMockServices();
        $services['Database']->method('Query')
            ->will($this->returnCallback(
                function($query) {
                    $this->assertTrue(1 === preg_match('/.*ORDER BY displayorder ASC$/', $query));
                }
            ));

        $render = new SWIFT_KnowledgebaseRenderManager();
        $render->RenderTree();
    }
}

class SWIFT_KnowledgebaseRenderManagerMock extends SWIFT_KnowledgebaseRenderManager
{
    /**
     * SWIFT_KnowledgebaseRenderManagerMock constructor.
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
