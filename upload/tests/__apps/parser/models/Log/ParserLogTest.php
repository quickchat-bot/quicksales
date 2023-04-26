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

namespace Parser\Models\Log;

use Knowledgebase\Admin\LoaderMock;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;

/**
 * Class ParserLogTest
 * @group parser-models
 */
class ParserLogTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Models\Log\SWIFT_ParserLog', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetGetParserLogIDReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_INT, $obj->GetParserLogID(),
            'Returns int');

        $obj->SetIsClassLoaded(false);

        $this->expectException(SWIFT_Log_Exception::class);
        $obj->GetParserLogID();
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

        $this->setExpectedException(SWIFT_Log_Exception::class);
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
    public function testIsValidLogTypeReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->IsValidLogType('unknown_type'));
        $this->assertTrue($obj->IsValidLogType(SWIFT_ParserLog::TYPE_SUCCESS));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsException()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Log_Exception::class);
        $obj->Create('', 1, 0, '', '', '', 0, '', '', 0);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateReturnsInt()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['SettingsGet'] = function ($x) {
            switch ($x) {
                case 'pr_maxlogsize':
                    return -1; // negative on purpose, to drive coverage
                    break;
                case 'pr_enablelog_notification':
                    return '1';
                    break;
            }
            return null;
        };

        $this->assertInternalType(IsType::TYPE_INT, $obj->Create(SWIFT_ParserLog::TYPE_FAILURE, 1, SWIFT_ParserLog::TYPE_FAILURE,
            '', '', '', 0, '', '', 0));

        static::$databaseCallback['Insert_ID'] = function () {
            return null;
        };

        $this->setExpectedException(SWIFT_Log_Exception::class);
        $obj->Create(SWIFT_ParserLog::TYPE_FAILURE, 1, SWIFT_ParserLog::TYPE_FAILURE,
            '', '', '', 0, '', '', 0);
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
    public function testCleanUpReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['SettingsGet'] = function ($x) {
            switch ($x) {
                case 'pr_logchurndays':
                    return 1;
                    break;
            }
            return null;
        };

        static::$databaseCallback['NextRecord'] = function () {
            \SWIFT::GetInstance()->Database->Record['parserlogid'] = 1;
            return false;
        };

        $this->assertTrue($obj->CleanUp());

        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () {
            return false;
        };

        $this->assertFalse($obj->CleanUp());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDashboardContainerReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->GetDashboardContainer());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIsMessageIDExistReturnsSomething()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj->IsMessageIDExist(1));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveMessageIDReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->RetrieveMessageID(1));
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
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_ParserLogMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Models\Log\SWIFT_ParserLogMock');
    }
}

class SWIFT_ParserLogMock extends SWIFT_ParserLog
{
    public $_dataStore;
    public $_updatePool;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'parserlogid' => 1,
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

