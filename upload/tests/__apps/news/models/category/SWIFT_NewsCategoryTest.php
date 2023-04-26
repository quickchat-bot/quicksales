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

namespace News\Models\Category;

use News\Admin\LoaderMock;
use SWIFT;

/**
 * Class SWIFT_NewsCategoryTest
 * @group news
 */
class SWIFT_NewsCategoryTest extends \SWIFT_TestCase
{
    public static $_record = [];
    public static $_count = 0;

    /**
     * @param int $_newsCategoryID
     * @return SWIFT_NewsCategoryMock
     * @throws SWIFT_Category_Exception
     */
    public function getModel($_newsCategoryID = 1)
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, true, false, true, true, false, true, false);
        $mockDb->method('Insert_ID')->willReturnOnConsecutiveCalls(false, 1, 1);
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if(false !== strpos($x, "titlehash IN ('c81e728d9d4c2f636f067f89cc14862c'")) {
                self::$_record = [
                  'titlehash' => 'c81e728d9d4c2f636f067f89cc14862c',
                  'newscategoryid' => '1',
                ];
            }

            return true;
        });
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "newscategoryid = '0'")) {
                return false;
            }

            return [
                'newscategoryid' => 1,
            ];
        });

        $mockDb->Record = &self::$_record;

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

        return new SWIFT_NewsCategoryMock($_newsCategoryID, [
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Database' => SWIFT::GetInstance()->Database,
        ]);
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testProcessUpdatePoolReturnsTrue()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testGetNewsCategoryIDThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetNewsCategoryID();
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
    public function testGetPropertyThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_INVALIDDATA);
        $obj->GetProperty('invalid');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getModel();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('prop');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testIsValidVisibilityTypeReturnsFalse()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::IsValidVisibilityType('invalid'),
            'Returns false if type is invalid');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testCreateThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_INVALIDDATA);
        $obj::Create('', '');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testCreateThrowsCreateFailedException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_CREATEFAILED);
        $obj::Create('title', 'public');
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testUpdateThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->Update(0, 0);
    }

    /**
     * @throws SWIFT_Category_Exception
     */
    public function testUpdateThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Category\SWIFT_Category_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->Update(1, 1);
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
        SWIFT::GetInstance()->Database->NextRecord(); //advance pointer
        $this->assertFalse($obj::DeleteList([0]),
            'Returns false with invalid array');
    }

    /**
     * @throws SWIFT_Category_Exception
     * @throws \SWIFT_Exception
     */
    public function testCreateOrUpdateFromSyncReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::CreateOrUpdateFromSync([], 0, ''));
//        SWIFT::GetInstance()->Database->Insert_ID(); // advance pointer
        $this->assertTrue($obj::CreateOrUpdateFromSync([2], 1, ''));
    }
}

class SWIFT_NewsCategoryMock extends SWIFT_NewsCategory
{
    /**
     * SWIFT_NewsCategoryMock constructor.
     * @param $_newsCategoryID
     * @param array $services
     * @throws SWIFT_Category_Exception
     */
    public function __construct($_newsCategoryID, array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct($_newsCategoryID);
    }

    public function Initialize()
    {
        return true;
    }
}
