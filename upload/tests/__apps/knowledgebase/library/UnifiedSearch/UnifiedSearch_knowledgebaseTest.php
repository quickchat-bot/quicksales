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

namespace Knowledgebase\Library\UnifiedSearch;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Interface;
use Base\Models\Staff\SWIFT_Staff;

/**
 * Class UnifiedSearch_knowledgebaseTest
 * @group knowledgebase
 */
class UnifiedSearch_knowledgebaseTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $_record = [
        'kbarticleid' => 1,
        'categorytitle' => 'categorytitle',
        'kbarticlecontents' => 'query',
    ];

    /**
     * @return SWIFT_UnifiedSearch_knowledgebaseMock
     * @throws SWIFT_Exception
     */
    public function getLibrary()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;
            if (self::$_next === 20) {
                unset(self::$_record['categorytitle']);
            }

            return in_array(self::$_next, [1, 3, 5, 8, 10, 15, 17, 19, 20, 22, 25, 27, 30, 33], true);
        });

        $mockDb->Record = &self::$_record;

        SWIFT::GetInstance()->Database = $mockDb;

        SWIFT::GetInstance()->Load = new LoaderMock();

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([]);

        SWIFT::GetInstance()->Cache = $mockCache;

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockXml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn('1');

        SWIFT::GetInstance()->Settings = $mockSettings;

        SWIFT::GetInstance()->Cache = $mockCache;


        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetPermission')
            ->willReturnOnConsecutiveCalls('0', '1');
        $mockStaff->method('GetProperty')->willReturn(1);

        $mockConv = $this->getMockBuilder('SWIFT_StringHTMLToText')
            ->disableOriginalConstructor()
            ->getMock();

        $mockHili = $this->getMockBuilder('SWIFT_StringHighlighter')
            ->disableOriginalConstructor()
            ->getMock();

        $mockHili->method('GetHighlightedRange')->willReturn(['-']);

        return new SWIFT_UnifiedSearch_knowledgebaseMock([
            'StringHTMLToText' => $mockConv,
            'StringHighlighter' => $mockHili,
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Template' => $mockTpl,
            'Database' => $mockDb,
            'XML' => $mockXml,
        ], 'query', SWIFT_Interface::INTERFACE_TESTS, $mockStaff, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchReturnsArray()
    {
        $obj = $this->getLibrary();
        $this->assertInternalType('array', $obj->Search());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Search();
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testSearchArticlesReturnsArray()
    {
        $obj = $this->getLibrary();
        $ref = new \ReflectionClass($obj);
        $method = $ref->getMethod('SearchArticles');
        $method->setAccessible(true);

        $method->invoke($obj); // advance permissions

        $this->assertInternalType('array', $method->invoke($obj));

        unset(self::$_record['kbarticlecontents']);
        $this->assertInternalType('array', $method->invoke($obj),
            'Returns array without query');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws \SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testSearchCategoriesReturnsArray()
    {
        $obj = $this->getLibrary();
        $ref = new \ReflectionClass($obj);
        $method = $ref->getMethod('SearchCategories');
        $method->setAccessible(true);

        $method->invoke($obj); // advance permissions

        $this->assertInternalType('array', $method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }
}

class SWIFT_UnifiedSearch_knowledgebaseMock extends SWIFT_UnifiedSearch_knowledgebase
{
    /**
     * SWIFT_UnifiedSearch_knowledgebaseMock constructor.
     * @param array $services
     * @param $_query
     * @param $_interfaceType
     * @param SWIFT_Staff $_SWIFT_StaffObject
     * @param $_maxResults
     * @throws \SWIFT_Exception
     */
    public function __construct(
        array $services,
        $_query,
        $_interfaceType,
        SWIFT_Staff $_SWIFT_StaffObject,
        $_maxResults
    ) {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct($_query, $_interfaceType, $_SWIFT_StaffObject, $_maxResults);
    }

    public function Initialize()
    {
        return true;
    }

    public static function HasQuery($_haystack, $_needle) {
        return true;
    }
}
