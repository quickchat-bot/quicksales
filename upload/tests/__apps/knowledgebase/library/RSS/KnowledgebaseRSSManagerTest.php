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

namespace Knowledgebase\Library\Rss;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class KnowledgebaseRSSManagerTest
 * @group knowledgebase
 */
class KnowledgebaseRSSManagerTest extends \SWIFT_TestCase
{
    public static $_next = 0;

    /**
     * @return SWIFT_KnowledgebaseRSSManagerMock
     * @throws SWIFT_Exception
     */
    public function getLibrary()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;
            return in_array(self::$_next, [1, 3, 5, 8, 10, 12], true);
        });

        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "kbcategoryid = '2'")) {
                return false;
            }

            if (false !== strpos($x, "kbcategoryid = '3'")) {
                return [
                    'kbcategoryid' => 1,
                    'categorytype' => 3,
                ];
            }

            if (false !== strpos($x, "kbcategoryid = '4'")) {
                return [
                    'kbcategoryid' => 4,
                    'categorytype' => 1,
                    'uservisibilitycustom' => '1',
                ];
            }

            return [
                'kbcategoryid' => 1,
                'categorytype' => 1,
                'uservisibilitycustom' => '1',
            ];
        });

        $mockDb->Record = [
            'articlestatus' => 1,
            'kbarticleid' => 1,
        ];

        SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn(['7' => [1 => [1]]]);

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

        $mockXml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn('1');

        SWIFT::GetInstance()->Settings = $mockSettings;

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        return new SWIFT_KnowledgebaseRSSManagerMock([
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Template' => $mockTpl,
            'XML' => $mockXml,
        ]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getLibrary();
        $this->assertInstanceOf('Knowledgebase\Library\Rss\SWIFT_KnowledgebaseRSSManager', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDispatchReturnsTrue()
    {
        $obj = $this->getLibrary();
        $this->assertFalse($obj->Dispatch(2),
            'Returns false with invalid id');

        $this->assertFalse($obj->Dispatch(3),
            'Returns false with private categorytype');

        $this->assertFalse($obj->Dispatch(4),
            'Returns false with filtered id');

        $this->assertTrue($obj->Dispatch(1),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Dispatch();
    }
}

class SWIFT_KnowledgebaseRSSManagerMock extends SWIFT_KnowledgebaseRSSManager
{
    /**
     * SWIFT_NewsRSSManagerMock constructor.
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
