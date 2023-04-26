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

namespace News\Models\Category;

use News\Admin\LoaderMock;
use SWIFT;

/**
 * Class SWIFT_NewsCategoryLinkTest
 * @group news
 */
class SWIFT_NewsCategoryLinkTest extends \SWIFT_TestCase
{
    /**
     * @param int $_newsCategoryLinkID
     * @return SWIFT_NewsCategoryLinkMock
     * @throws SWIFT_Category_Exception
     */
    public function getModel($_newsCategoryLinkID = 1)
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "newscategorylinkid = '0'")) {
                return false;
            }

            return [
                'newscategorylinkid' => 1,
            ];
        });

        SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([]);

        SWIFT::GetInstance()->Cache = $mockCache;

        SWIFT::GetInstance()->Load = new LoaderMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturnOnConsecutiveCalls('1', '0');

        return new SWIFT_NewsCategoryLinkMock($_newsCategoryLinkID, [
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Database' => SWIFT::GetInstance()->Database,
        ]);
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testConstructorThrowsException()
    {
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            'Failed to load News Category Link ID: 0');
        $this->getModel(0);
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getModel();
        $this->assertInstanceOf('News\Models\Category\SWIFT_NewsCategoryLink', $obj);

        $obj->__destruct();
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testProcessUpdatePoolReturnsTrue()
    {
        $obj = $this->getModel();
        $obj->UpdatePool('key', 'val');
        $this->assertTrue($obj->ProcessUpdatePool(),
            'Returns true after updating');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testGetNewsCategoryLinkIDThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetNewsCategoryLinkID();
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testGetDataStoreReturnsArray()
    {
        $obj = $this->getModel();
        $this->assertInternalType('array', $obj->GetDataStore());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetDataStore();
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_INVALIDDATA);
        $obj->GetProperty('invalid');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testGetPropertyReturnsValue()
    {
        $obj = $this->getModel();
        $this->assertEquals('1', $obj->GetProperty('newscategorylinkid'));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('prop');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testCreateThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_INVALIDDATA);
        $obj::Create(0, 0);
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testCreateThrowsCreateFailedException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_CREATEFAILED);
        $obj::Create(1, 1);
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertTrue($obj->Delete(),
            'Returns true after deleting');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->Delete();
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::DeleteList([]),
            'Returns false if empty array');

        SWIFT::GetInstance()->Database->NextRecord(); //advance pointer
        $this->assertFalse($obj::DeleteList([0]),
            'Returns false with invalid array');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testDeleteOnNewsCategoryReturnsFalse()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::DeleteOnNewsCategory([]),
            'Returns false if empty array');

        SWIFT::GetInstance()->Database->NextRecord(); //advance pointer
        $this->assertFalse($obj::DeleteOnNewsCategory([0]),
            'Returns false with invalid array');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testDeleteOnNewsItemReturnsFalse()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::DeleteOnNewsItem([]),
            'Returns false if empty array');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testRetrieveOnNewsItemReturnsEmptyArray()
    {
        $obj = $this->getModel();
        $this->assertEmpty($obj::RetrieveOnNewsItem([]));
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testRetrieveOnNewsCategoryReturnsEmptyArray()
    {
        $obj = $this->getModel();
        $this->assertEmpty($obj::RetrieveOnNewsCategory([]));
    }
}

class SWIFT_NewsCategoryLinkMock extends SWIFT_NewsCategoryLink
{
    /**
     * SWIFT_NewsCategoryLinkMock constructor.
     * @param $_newsCategoryLinkID
     * @param array $services
     * @throws SWIFT_Category_Exception
     */
    public function __construct($_newsCategoryLinkID, array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct($_newsCategoryLinkID);
    }

    public function Initialize()
    {
        return true;
    }
}
