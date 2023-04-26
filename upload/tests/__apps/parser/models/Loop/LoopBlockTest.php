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
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Models\Loop;

use Knowledgebase\Admin\LoaderMock;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;

/**
 * Class LoopBlockTest
 * @group parser-models
 */
class LoopBlockTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Models\Loop\SWIFT_LoopBlock', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessUpdatePoolReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false');

        $obj->_updatePool = ['key' => 'value'];

        $this->assertTrue($obj->ProcessUpdatePool(),
            'Returns true');


        $obj->SetIsClassLoaded(false);

        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetLoopBlockIDReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_INT, $obj->GetLoopBlockID(),
            'Returns int');

        $obj->SetIsClassLoaded(false);

        $this->expectException(SWIFT_Loop_Exception::class);
        $obj->GetLoopBlockID();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDataStoreReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertEquals($obj->_dataStore, $obj->GetDataStore(),
            'Returns _dataStore');

        $obj->SetIsClassLoaded(false);

        $this->assertClassNotLoaded($obj, 'GetDataStore');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->_dataStore['key'] = 'value';
        $this->assertEquals('value', $obj->GetProperty('key'));

        $this->setExpectedException(SWIFT_Loop_Exception::class);
        $obj->GetProperty('no_key');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyClassNotLoaded()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetProperty', ['key']);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsException()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Loop_Exception::class, SWIFT_INVALIDDATA);
        $obj->Create('', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateCompletely()
    {
        $obj = $this->getMocked();

        $obj->Create('address', 1);

        static::$databaseCallback['Insert_ID'] = function() {
            return null;
        };

        $this->setExpectedException(SWIFT_Loop_Exception::class, SWIFT_CREATEFAILED);
        $obj->Create('address', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Delete');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCheckIfAddressIsBlockedReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->CheckIfAddressIsBlocked('email'));

        $this->setExpectedException(SWIFT_Exception::class);
        $obj->CheckIfAddressIsBlocked('');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->DeleteList('non_array'));

        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () {
            return false;
        };

        $this->assertFalse($obj->DeleteList([2]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteObsoleteCompletely()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->DeleteObsolete());

        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () {
            return false;
        };

        $this->assertFalse($obj->DeleteObsolete());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteOnAddressListCompletely()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->DeleteOnAddressList([2]));
        $this->assertFalse($obj->DeleteOnAddressList('non_array'));

        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () {
            return false;
        };

        $this->assertFalse($obj->DeleteOnAddressList([2]));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_LoopBlockMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Models\Loop\SWIFT_LoopBlockMock');
    }
}

class SWIFT_LoopBlockMock extends SWIFT_LoopBlock
{
    public $_updatePool;
    public $_dataStore;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'parserloopblockid' => 1,
            'totalitems' => 1,
        ]);

        parent::__construct(1);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

