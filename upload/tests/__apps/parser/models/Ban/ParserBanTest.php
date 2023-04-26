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

namespace Parser\Models\Ban;

use Knowledgebase\Admin\LoaderMock;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;

/**
 * Class ParserBanTest
 * @group parser-models
 */
class ParserBanTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();

        $this->assertInstanceOf('Parser\Models\Ban\SWIFT_ParserBan', $obj);
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
    public function testGetParserBanIDReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_INT, $obj->GetParserBanID(),
            'Returns int');

        $obj->SetIsClassLoaded(false);

        $this->expectException(SWIFT_Ban_Exception::class);
        $obj->GetParserBanID();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLoadDataReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->LoadData(1),
            'Returns true');
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

        $this->setExpectedException(SWIFT_Ban_Exception::class);
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
    public function testCreateWithEmptyEmail()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Ban_Exception::class);
        $obj->Create('');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateWithNullInsertID()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['Insert_ID'] = function() {
            return null;
        };

        $this->setExpectedException(SWIFT_Ban_Exception::class);
        $obj->Create('email');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertEquals(1, $obj->Create('email'));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateFromListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->CreateFromList(null));
        $this->assertTrue($obj->CreateFromList(['email1','']));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['NextRecord'] = function() use ($obj) {
            $obj->Database->Record = ['parserbanid' => 1];
            return true;
        };

        $this->assertTrue($obj->Update('email'));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Update', ['email']);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateDuplicateRecord()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Ban_Exception::class);
        $this->assertTrue($obj->Update('email'));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateEmptyBanEmail()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Ban_Exception::class);
        $this->assertTrue($obj->Update(''));
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
    public function testIsBannedReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->IsBanned(''));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIsBannedReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->IsBanned('email@address.com'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_ParserBanMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Models\Ban\SWIFT_ParserBanMock');
    }
}

class SWIFT_ParserBanMock extends SWIFT_ParserBan
{
    public $Database;
    public $_updatePool;
    public $_dataStore;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['parserbanid' => 1]);

        parent::__construct(1);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

