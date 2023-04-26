<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Banjo Mofesola Paul <banjo.paul@aurea.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Parser\Models\Breakline;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class BreaklineTest
 * @group parser-models
 */
class BreaklineTest extends \SWIFT_TestCase
{
    public static $_record = [];
    public static $_count = 0;

    /**
     * @param int $_breakLineID
     *
     * @return SWIFT_BreaklineMock
     * @throws SWIFT_Breakline_Exception
     */
    public function getModel($_breakLineID = 1)
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, true, false, true, true, false, true, false);
        $mockDb->method('Insert_ID')->willReturnCallback(function () {
            if (isset(static::$databaseCallback['Insert_ID'])) {
                return call_user_func(static::$databaseCallback['Insert_ID']);
            }

            return 1;
        });
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "breaklineid = '0'")) {
                return false;
            }

            return [
                'breaklineid' => 1,
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

        return new SWIFT_BreaklineMock($_breakLineID, [
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Database' => SWIFT::GetInstance()->Database,
        ]);
    }

    /**
     * @throws SWIFT_BreakLine_Exception
     */
    public function testProcessUpdatePoolReturnsTrue()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_BreakLine_Exception
     */
    public function testGetBreaklineIDThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException(SWIFT_Breakline_Exception::class,
            SWIFT_CLASSNOTLOADED);
        $obj->GetBreaklineID();
    }

    /**
     * @throws SWIFT_BreakLine_Exception
     */
    public function testGetDataStoreReturnsArray()
    {
        $obj = $this->getModel();
        $this->assertInternalType('array', $obj->GetDataStore());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException(SWIFT_Breakline_Exception::class,
            SWIFT_CLASSNOTLOADED);
        $obj->GetDataStore();
    }

    /**
     * @throws SWIFT_BreakLine_Exception
     */
    public function testGetPropertyThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException(SWIFT_Breakline_Exception::class,
            SWIFT_INVALIDDATA);
        $obj->GetProperty('invalid');
    }

    /**
     * @throws SWIFT_BreakLine_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getModel();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException(SWIFT_Breakline_Exception::class,
            SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('prop');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyReturnsTrue()
    {
        $obj = $this->getModel();

        $obj->_dataStore['key'] = 'value';
        $this->assertEquals('value', $obj->GetProperty('key'));
    }

    /**
     * @throws SWIFT_BreakLine_Exception
     */
    public function testUpdateThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException(SWIFT_Breakline_Exception::class,
            SWIFT_CLASSNOTLOADED);
        $obj->Update(1, 1, SORT_ASC);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateReturnsTrue()
    {
        $obj = $this->getModel();

        $this->assertTrue($obj->Update('', false, 1));
    }

    /**
     * @throws SWIFT_BreakLine_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertTrue($obj->Delete(),
            'Returns true after deleting');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException(SWIFT_Breakline_Exception::class,
            SWIFT_CLASSNOTLOADED);
        $obj->Delete();
    }

    /**
     * @throws SWIFT_BreakLine_Exception
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
     * @throws SWIFT_Exception
     */
    public function testCreateWithNullInsertID()
    {
        $obj = $this->getModel();

        static::$databaseCallback['Insert_ID'] = function() {
            return null;
        };

        $this->setExpectedException(SWIFT_Breakline_Exception::class);
        $obj->Create('', false, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateReturnsTrue()
    {
        $obj = $this->getModel();

        $this->assertEquals(1, $obj->Create('', false, 1));
    }
}

class SWIFT_BreaklineMock extends SWIFT_Breakline
{
    public $_dataStore;

    /**
     * SWIFT_BreaklineMock constructor.
     *
     * @param int $_breakLineID
     * @param array $services
     *
     * @throws SWIFT_Breakline_Exception
     */
    public function __construct($_breakLineID, array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct($_breakLineID);
    }

    public function Initialize()
    {
        return true;
    }
}

