<?php
/**
 * ###############################################
 *
 * Kayako Classic
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

namespace News\Library\Subscriber;

use News\Admin\LoaderMock;
use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;

/**
 * Class SWIFT_NewsSubscriberDispatchTest
 * @group news
 */
class SWIFT_NewsSubscriberDispatchTest extends \SWIFT_TestCase
{
    public static $_count = [];
    private $_originalDb;

    /**
     * @beforeClass
     */
    protected function setUpBeforeAll(): void
    {
        $this->_originalDb = \SWIFT::GetInstance()->Database;
    }

    /**
     * @afterClass
     */
    protected function tearDownAfterAll()
    {
        \SWIFT::GetInstance()->Database = $this->_originalDb;
    }

    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false, false, false, true, false, true, false);

        $this->mockProperty($mockDb, 'Record', [
            'email' => 'me@email.com',
            'toemail' => 'me@email.com',
            'fromemail' => 'me@email.com',
        ]);

        SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([]);

        SWIFT::GetInstance()->Cache = $mockCache;

        SWIFT::GetInstance()->Load = new LoaderMock();

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturnCallback(function ($x) {
            if (!isset(self::$_count[$x])) {
                self::$_count[$x] = 0;
            }

            self::$_count[$x]++;

            if ($x === 'cpu_enablemailqueue') {
                return self::$_count[$x] === 2;
            }

            return '1';
        });

        SWIFT::GetInstance()->Settings = $mockSettings;
    }

    /**
     * @param bool $_isLoaded
     * @return SWIFT_NewsSubscriberDispatchMock
     * @throws \SWIFT_Exception
     */
    public function getLibrary($_isLoaded = true)
    {
        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockXml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTpl->method('Get')->willReturn('1');

        $mockItem = $this->getMockBuilder('News\Models\NewsItem\SWIFT_NewsItem')
            ->disableOriginalConstructor()
            ->getMock();

        $mockItem->method('GetIsClassLoaded')->willReturn($_isLoaded);
        $mockItem->method('GetProperty')->willReturn(1);

        $mockConv = $this->getMockBuilder('SWIFT_StringHTMLToText')
            ->disableOriginalConstructor()
            ->getMock();

        $mockMail = $this->getMockBuilder('SWIFT_Mail')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockMail->method('GetInstance')->willReturn($mockMail);

        return new SWIFT_NewsSubscriberDispatchMock($mockItem, [
            'StringHTMLToText' => $mockConv,
            'Database' => SWIFT::GetInstance()->Database,
            'Language' => $mockLang,
            'Settings' => SWIFT::GetInstance()->Settings,
            'Template' => $mockTpl,
            'XML' => $mockXml,
            'Mail' => $mockMail,
        ]);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getLibrary();
        $this->assertInstanceOf('News\Library\Subscriber\SWIFT_NewsSubscriberDispatch', $obj);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CREATEFAILED);
        $this->getLibrary(false);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testSendReturnsTrue()
    {
        $obj = $this->getLibrary();
        $this->assertTrue($obj->Send('subject', 'from', 'me@email.com', true, false, true, false),
            'Returs true with cpu_enablemailqueue = 0');

        $this->assertFalse($obj->Send('subject', 'from', 'me@email.com', true, false, true, false),
            'Returs false without emails');

        $this->assertTrue($obj->Send('subject', 'from', 'me@email.com', false, false, false, false),
            'Returs true with cpu_enablemailqueue = 1');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Send('subject', 'from', 'me@email.com', true, [1], true, false);
    }

    /**
     * @dataProvider sendShouldMakeCorrectSubscribersQueryToDbIfNewsVisibilityRestrictedTestDataProvider
     */
    public function testSendShouldMakeCorrectSubscribersQueryToDbIfNewsVisibilityRestrictedToSpecificUserGroups(
        int $newsType,
        array $allowedUserGroupsIds,
        string $expectedInValues)
    {
        // Arrange
        $mockedDB = $this->createMock(\SWIFT_Database::class);
        $mockedDB
            ->expects($this->at(0))
            ->method('Query')
            ->with('SELECT * FROM ' . TABLE_PREFIX . 'newssubscribers WHERE usergroupid IN (' . $expectedInValues .
                ") AND isvalidated = '1'");
        SWIFT::GetInstance()->Database = $mockedDB;

        $mockedNewsItem = $this->createMock(SWIFT_NewsItem::class);
        $mockedNewsItem
            ->method('GetIsClassLoaded')
            ->willReturn(true);
        $mockedNewsItem
            ->method('GetProperty')
            ->will($this->returnCallback(function(string $propertyName) use ($newsType) {
                if ($propertyName === 'newstype') {
                    return $newsType;
                } else {
                    return false;
                }
            }));
        $newsSubscriberDispatch = new SWIFT_NewsSubscriberDispatch($mockedNewsItem);

        // Act
        $result = $newsSubscriberDispatch->Send(
            'KAYAKOC-21178', 'John Doe', 'john.doe@example.com', true, $allowedUserGroupsIds, false, []);

        // Assert
        $this->assertFalse($result);
    }

    public function sendShouldMakeCorrectSubscribersQueryToDbIfNewsVisibilityRestrictedTestDataProvider(): array
    {
        $testData = [];
        foreach ([SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PUBLIC] as $newsType) {
            $testData[] = [$newsType, [], "'0'"];
            $testData[] = [$newsType, [1], "'1'"];
            $testData[] = [$newsType, [1, 2], "'1','2'"];
        }
        return $testData;
    }
}

class SWIFT_NewsSubscriberDispatchMock extends SWIFT_NewsSubscriberDispatch
{
    /**
     * SWIFT_NewsSubscriberDispatchMock constructor.
     * @param array $services
     * @param SWIFT_NewsItem $_SWIFT_NewsItemObject
     * @throws \SWIFT_Exception
     */
    public function __construct($_SWIFT_NewsItemObject, array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct($_SWIFT_NewsItemObject);
    }

    public function Initialize()
    {
        return true;
    }
}
