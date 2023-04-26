<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Models\EmailQueue;

use Knowledgebase\Admin\LoaderMock;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_DataID;
use SWIFT_DataStore;
use SWIFT_Exception;

/**
 * Class EmailQueueTest
 * @group parser-models
 */
class EmailQueueTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Models\EmailQueue\SWIFT_EmailQueue', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessUpdatePoolReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false');

        $obj->SetIsClassLoaded(false);

        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetEmailQueueIDReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_INT, $obj->GetEmailQueueID());

        $obj->SetIsClassLoaded(false);

        $this->expectException(SWIFT_EmailQueue_Exception::class);
        $obj->GetEmailQueueID();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->_dataStore['key'] = 'value';
        $this->assertEquals('value', $obj->GetProperty('key'));

        $this->setExpectedException(SWIFT_EmailQueue_Exception::class);
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
    public function testIsValidFetchTypeReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->IsValidFetchType('invalid_fetch_type'));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Delete());

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
    public function testEmailQueueExistsWithEmailReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->EmailQueueExistsWithEmail('email'));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveIsSuccessful()
    {
        $obj = $this->getMocked();

        $this->assertInstanceOf(SWIFT_EmailQueue::class, $obj->Retrieve(1));
    }
    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveStoreIsSuccessful()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->RetrieveStore([]));
        $this->assertInstanceOf(SWIFT_EmailQueue::class, $obj->RetrieveStore(SWIFT_EmailQueueMock::$arr));
        $this->assertInstanceOf(SWIFT_EmailQueue::class, $obj->RetrieveStore(array_merge(
            SWIFT_EmailQueueMock::$arr, ['fetchtype' => SWIFT_EmailQueue::FETCH_PIPE])));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateFetchTypeReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->UpdateFetchType('imap'));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'UpdateFetchType', 'imap');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateFetchTypeThrowsException()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Exception::class);
        $obj->UpdateFetchType('unknown');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetSignatureReturnsSomething()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj->GetSignature());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetSignature');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEnableListReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['NextRecord'] = function() {
            \SWIFT::GetInstance()->Database->Record = [
                'isenabled' => false,
                'fetchtype' => 'imap',
                'email' => 'email',
                'emailqueueid' => 1
            ];
            return false;
        };

        $this->assertTrue($obj->EnableList( [1] ));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEnableListReturnsFalse()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['NextRecord'] = function() {
            \SWIFT::GetInstance()->Database->Record = [
                'isenabled' => '1'
            ];
            return false;
        };

        $this->assertFalse($obj->EnableList('non_array'));
        $this->assertFalse($obj->EnableList( [1] ));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDisableListReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['NextRecord'] = function() {
            \SWIFT::GetInstance()->Database->Record = [
                'isenabled' => true,
                'fetchtype' => 'imap',
                'email' => 'email',
                'emailqueueid' => 1
            ];
            return false;
        };

        $this->assertTrue($obj->DisableList( [1] ));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDisableListReturnsFalse()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['NextRecord'] = function() {
            \SWIFT::GetInstance()->Database->Record = [
                'isenabled' => false
            ];
            return false;
        };

        $this->assertFalse($obj->DisableList('non_array'));
        $this->assertFalse($obj->DisableList( [1] ));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIsValidQueuePrefixReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertInternalType('numeric', $obj->IsValidQueuePrefix(''));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetIMAPArgumentIsOk()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj->GetIMAPArgument(SWIFT_EmailQueue::FETCH_IMAP));
        $this->assertNotEmpty($obj->GetIMAPArgument(SWIFT_EmailQueue::FETCH_IMAPSSL));
        $this->assertNotEmpty($obj->GetIMAPArgument(SWIFT_EmailQueue::FETCH_IMAPTLS));
        $this->assertNotEmpty($obj->GetIMAPArgument(SWIFT_EmailQueue::FETCH_POP3SSL));
        $this->assertNotEmpty($obj->GetIMAPArgument(SWIFT_EmailQueue::FETCH_POP3TLS));
        $this->assertNotEmpty($obj->GetIMAPArgument(SWIFT_EmailQueue::FETCH_POP3));

        $this->setExpectedException(SWIFT_EmailQueue_Exception::class);
        $obj->GetIMAPArgument('unknown');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveEmailofAllEmailQueuesReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->RetrieveEmailofAllEmailQueues());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_EmailQueueMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Models\EmailQueue\SWIFT_EmailQueueMock');
    }
}

class SWIFT_EmailQueueMock extends SWIFT_EmailQueue
{
    public $_dataStore;
    static $arr = [
        'emailqueueid' => 1,
        'tgroupid' => 1,
        'type' => APP_BACKEND,
        'departmentid' => 1,
        'tickettypeid' => 1,
        'priorityid' => 1,
        'ticketstatusid' => 1,
        'ticketautoresponder' => 1,
        'queuesignatureid' => 1,
        'fetchtype' => 'imap',
        'contents' => 'contents',
    ];
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(static::$arr);

        parent::__construct(new SWIFT_DataStore(static::$arr));
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

