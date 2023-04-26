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

namespace News\Library\UnifiedSearch;

use News\Admin\LoaderMock;
use SWIFT;
use SWIFT_Interface;
use Base\Models\Staff\SWIFT_Staff;

/**
 * Class SWIFT_UnifiedSearch_newsTest
 * @group news
 */
class SWIFT_UnifiedSearch_newsTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false);

        $this->mockProperty($mockDb, 'Record', [
            'newsitemid' => 1,
        ]);

        SWIFT::GetInstance()->Database = $mockDb;

        SWIFT::GetInstance()->Load = new LoaderMock();

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([]);

        SWIFT::GetInstance()->Cache = $mockCache;
    }

    /**
     * @return SWIFT_UnifiedSearch_newsMock
     * @throws \SWIFT_Exception
     */
    public function getLibrary()
    {
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

        $mockSettings->method('Get')->willReturnOnConsecutiveCalls('1', '0');

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

        return new SWIFT_UnifiedSearch_newsMock([
            'StringHTMLToText' => $mockConv,
            'StringHighlighter' => $mockHili,
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Template' => $mockTpl,
            'Database' => SWIFT::GetInstance()->Database,
            'XML' => $mockXml,
        ], 'query', SWIFT_Interface::INTERFACE_TESTS, $mockStaff, 1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getLibrary();
        $this->assertInstanceOf('News\Library\UnifiedSearch\SWIFT_UnifiedSearch_news', $obj);
    }

    /**
     * @throws \SWIFT_Exception
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
     * @throws \SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testSearchNewsReturnsArray()
    {
        $obj = $this->getLibrary();
        $ref = new \ReflectionClass($obj);
        $method = $ref->getMethod('SearchNews');
        $method->setAccessible(true);

        $method->invoke($obj); // advance permissions

        $this->assertInternalType('array', $method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }
}

class SWIFT_UnifiedSearch_newsMock extends SWIFT_UnifiedSearch_news
{
    /**
     * SWIFT_UnifiedSearch_newsMock constructor.
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
