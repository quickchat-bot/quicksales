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

namespace News\Api;

use SWIFT;
use News\Admin\LoaderMock;

/**
 * Class Controller_SubscriberTest
 * @group news
 */
class Controller_SubscriberTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        // reset test data
        unset($_POST);
    }

    /**
     * @param array $services
     * @return Controller_SubscriberMock
     */
    protected function getController(array $services = [])
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Insert_ID')->willReturnOnConsecutiveCalls(1, 1, 1, 0);
        $mockDb->method('AutoExecute')->willReturn(1);

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false, true, false, true, false);

        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "newssubscriberid = '0'")) {
                return false;
            }

            return [
                'newssubscriberid' => 1,
                'userid' => 1,
                'usergroupid' => 1,
                'email' => 'me@email.com',
            ];
        });

        $this->mockProperty($mockDb, 'Record', [
            'newssubscriberid' => 1,
        ]);

        SWIFT::GetInstance()->Database = $mockDb;

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('Get')->willReturn('1');

        $mgr = $this->getMockBuilder('SWIFT_RESTManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mgr->method('Authenticate')->willReturn(true);

        $svr = $this->getMockBuilder('SWIFT_RESTServer')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['DispatchStatus', 'GetVariableContainer', 'Get'])
            ->getMock();

        $svr->method('GetVariableContainer')->willReturn(['salt' => 'salt']);
        $svr->method('Get')->willReturnArgument(0);

        $lang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $lang->method('Get')->willReturnArgument(0);

        $mockXml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturn([1 => [1]]);

        SWIFT::GetInstance()->Cache = $mockCache;

        $services = array_merge($services, [
            'XML' => $mockXml,
            'Cache' => $mockCache,
        ]);

        return new Controller_SubscriberMock($settings, $mgr, $svr, $lang, $mockDb, $services);
    }

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('News\Api\Controller_Subscriber', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->GetList(),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetList();
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessNewsSubscribersReturnsTrue()
    {
        $obj = $this->getController();

        $ref = new \ReflectionClass($obj);
        $method = $ref->getMethod('ProcessNewsSubscribers');
        $method->setAccessible(true);

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Get(1),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Get(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Post(),
            'Returns false without email');

        $_POST['email'] = 'email';
        $this->assertFalse($obj->Post(),
            'Returns false with invalid email');

        $_POST['email'] = 'me@email.com';
        $_POST['isvalidated'] = 1;

        $this->assertTrue($obj->Post(),
            'Returns true after rendering XML');

        $this->assertFalse($obj->Post(),
            'Returns false if subscriber cannot be created');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Post();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPutReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Put(0),
            'Returns false with invalid ID');

        $_POST['email'] = 'email';
        $this->assertFalse($obj->Put(1),
            'Returns false with invalid email');

        $_POST['email'] = 'me@email.com';
        $this->assertTrue($obj->Put(1),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Put(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Delete(0),
            'Returns true after deleting');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete(0);
    }
}

/**
 * Class Controller_CommentMock
 * @package Troubleshooter\Api
 */
class Controller_SubscriberMock extends Controller_Subscriber
{
    public $RESTManager;
    public $RESTServer;

    public function __construct($settings, $mgr, $svr, $lang, $db, $services = [])
    {
        $this->Load = new LoaderMock();
        $this->Settings = $settings;
        $this->RESTManager = $mgr;
        $this->RESTServer = $svr;
        $this->Language = $lang;
        $this->Database = $db;
        foreach ($services as $key => $service) {
            $this->$key = $service;
        }
        $this->SetIsClassLoaded(true);
        $this->sendEmails = false;
        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}
