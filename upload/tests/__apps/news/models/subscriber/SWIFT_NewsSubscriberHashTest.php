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

namespace News\Models\Subscriber;

use News\Admin\LoaderMock;
use ReflectionClass;
use ReflectionException;
use SWIFT;
use SWIFT_Data;
use SWIFT_DataStore;
use SWIFT_Exception;

/**
 * Class SWIFT_NewsSubscriberHashTest
 * @group news
 */
class SWIFT_NewsSubscriberHashTest extends \SWIFT_TestCase
{
    /**
     * @param int $_newsSubscriberID
     * @return SWIFT_NewsSubscriberHashMock
     * @throws SWIFT_Exception
     */
    public function getModel($_newsSubscriberID = 1)
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "newssubscriberid = '2'")) {
                return false;
            }

            return [
                'newssubscriberhashid' => 1,
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

        if ($_newsSubscriberID === 0) {
            $_SWIFT_DataObject = new \SWIFT_DataID(1);
            $_SWIFT_DataObject->SetIsClassLoaded(false);
        } else {
            $_SWIFT_DataObject = new \SWIFT_DataID($_newsSubscriberID);
        }

        return new SWIFT_NewsSubscriberHashMock($_SWIFT_DataObject, [
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Database' => SWIFT::GetInstance()->Database,
        ]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorThrowsException()
    {
        $this->setExpectedException('SWIFT_Exception',
            'Failed to load News Subscriber Hash Object');
        $this->getModel(0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getModel();
        $this->assertInstanceOf('News\Models\Subscriber\SWIFT_NewsSubscriberHash', $obj);

        $obj->__destruct();
    }

    /**
     * @throws SWIFT_Exception
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
     * @throws SWIFT_Exception
     */
    public function testGetNewsSubscriberHashIDThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetNewsSubscriberHashID();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDataStoreReturnsArray()
    {
        $obj = $this->getModel();
        $this->assertInternalType('array', $obj->GetDataStore());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetDataStore();
    }

    /**
     * @throws SWIFT_Exception
     * @throws ReflectionException
     */
    public function testLoadDataThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $class = new ReflectionClass($obj);
        $method = $class->getMethod('LoadData');
        $method->setAccessible(true);

        $_data = new DataMock();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, $_data);
    }

    /**
     * @throws SWIFT_Exception
     * @throws ReflectionException
     */
    public function testLoadDataThrowsException()
    {
        $obj = $this->getModel();
        $class = new ReflectionClass($obj);
        $method = $class->getMethod('LoadData');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($obj, new SWIFT_DataStore([
            'newssubscriberhashid' => 1,
        ])));

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, new SWIFT_DataStore([]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->GetProperty('invalid');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getModel();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('prop');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->assertNotNull($obj::Create(1));

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::Create(0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::DeleteList([]),
            'Returns false if empty array');
    }
}

class SWIFT_NewsSubscriberHashMock extends SWIFT_NewsSubscriberHash
{
    /**
     * SWIFT_NewsSubscriberHashMock constructor.
     * @param SWIFT_Data $_SWIFT_DataObject
     * @param array $services
     * @throws \SWIFT_Exception
     */
    public function __construct(SWIFT_Data $_SWIFT_DataObject, array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct($_SWIFT_DataObject);
    }

    public function Initialize()
    {
        return true;
    }
}

class DataMock extends SWIFT_Data
{
    public function __construct()
    {
        // do nothing
    }
}
