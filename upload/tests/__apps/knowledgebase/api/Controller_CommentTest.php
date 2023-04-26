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

namespace Knowledgebase\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class Controller_CommentTest
* @group knowledgebase
*/
class Controller_CommentTest extends \SWIFT_TestCase
{
    /**
     * @param array $services
     * @return Controller_CommentMock
     * @throws \SWIFT_Exception
     */
    protected function getController(array $services = [])
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Insert_ID')
            ->willReturn(1);

        $mockDb->method('QueryFetch')
            ->willReturnCallback(function ($x) {
                if (false !== strpos($x, "commentid = '0'") ||
                    false !== strpos($x, "kbarticleid = '2'") ||
                    false !== strpos($x, "userid = '2'")) {
                    return false;
                }

                $arr = [
                    'kbarticleid' => 1,
                    'userid' => 1,
                    'staffid' => 1,
                    'kbcategoryid' => 1,
                    'commentid' => 1,
                    'typeid' => 1,
                    'creatortype' => 1,
                    'creatorid' => 1,
                    'fullname' => 'full name',
                    'email' => 'user@mail.com',
                    'ipaddress' => 'ipaddress',
                    'dateline' => time(),
                    'parentcommentid' => 1,
                    'commentstatus' => 'commentstatus',
                    'useragent' => 'useragent',
                    'referrer' => 'referrer',
                    'parenturl' => 'parenturl',
                    'contents' => 'contents',
                    'seosubject' => 'seosubject',
                ];

                if (false !== strpos($x, "kbarticleid = '3'")) {
                    unset($arr['seosubject']);
                }

                return $arr;
            });

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->mockProperty($mockDb, 'Record', [
            'commentid' => 1,
            'typeid' => 1,
            'creatortype' => 1,
            'creatorid' => 1,
            'fullname' => 'full name',
            'email' => 'user@mail.com',
            'ipaddress' => 'ipaddress',
            'dateline' => time(),
            'parentcommentid' => 1,
            'commentstatus' => 'commentstatus',
            'useragent' => 'useragent',
            'referrer' => 'referrer',
            'parenturl' => 'parenturl',
            'contents' => 'contents',
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();
        $settings->method('Get')
            ->willReturn('1');

        $mgr = $this->getMockBuilder('SWIFT_RESTManager')
            ->disableOriginalConstructor()
            ->getMock();
        $mgr->method('Authenticate')
            ->willReturn(true);

        $svr = $this->getMockBuilder('SWIFT_RESTServer')
            ->disableOriginalConstructor()
            ->getMock();
        $svr->method('GetVariableContainer')
            ->willReturn(['salt' => '1']);

        return new Controller_CommentMock($settings, $mgr, $svr, $mockDb, $services);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Knowledgebase\Api\Controller_Comment', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetListPrintsXml() {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getController(['XML' => $xml]);
        $this->assertTrue($obj->GetList(),
            'Returns true after printing XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetList();
    }

    /**
     * @throws \ReflectionException
     * @throws \SWIFT_Exception
     */
    public function testProtectedMethodThrowsException() {
        $obj = $this->getController();
        $obj->SetIsClassLoaded(false);

        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('ProcessKbComments');
        $method->setAccessible(true);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, false);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetPrintsXml() {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getController(['XML' => $xml]);
        $this->assertTrue($obj->Get(1),
            'Returns true after printing XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Get(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPutDoesNothing() {
        $obj = $this->getController();
        $this->expectOutputString('Put not implemented');
        $this->assertFalse($obj->Put(0),
            'Returns false because method is not implemented');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Put(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteReturnsTrue() {
        $obj = $this->getController();
        $this->assertTrue($obj->Delete(0),
            'Returns true after deleting');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPostPrintsXml() {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        // pass mock XML service
        $obj = $this->getController(['XML' => $xml]);

        $this->assertFalse($obj->Post(),
            'Returns false without contents');

        $_POST['contents'] = 'contents';
        $this->assertFalse($obj->Post(),
            'Returns false without step');

        $_POST['knowledgebasearticleid'] = 2;
        $this->assertFalse($obj->Post(),
            'Returns false without creator type');

        $_POST['creatortype'] = 3;
        $this->assertFalse($obj->Post(),
            'Returns false with non existent step');

        $_POST['knowledgebasearticleid'] = 1;
        $_POST['creatorid'] = 0;
        $_POST['email'] = 'user@mail.com';
        $this->assertFalse($obj->Post(),
            'Returns false with invalid creator type');

        $_POST['creatortype'] = 1; // staff
        $this->assertFalse($obj->Post(),
            'Returns false with invalid creator id');

        $_POST['creatorid'] = 1;
        $_POST['parentcommentid'] = 1;
        $this->assertTrue($obj->Post(),
            'Returns true after printing XML');

        $_POST['creatortype'] = 2; // user
        $_POST['creatorid'] = 2;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid creator id');

        $_POST['creatorid'] = 0;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid creator id');

        $_POST['fullname'] = 'fullname';
        $this->assertTrue($obj->Post(),
            'Returns true after printing XML');

        $_POST['creatorid'] = 1;
        $this->assertTrue($obj->Post(),
            'Returns true after printing XML');

        $_POST['knowledgebasearticleid'] = 3;
        $this->assertTrue($obj->Post(),
            'Returns true after printing XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Post();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testListAllPrintsXml() {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        // pass mock XML service
        $obj = $this->getController(['XML' => $xml]);

        $this->assertTrue($obj->ListAll(),
            'Returns true after printing XML');

        $this->assertTrue($obj->ListAll(1),
            'Returns true after printing XML using id');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ListAll();
    }
}


/**
 * Class Controller_CommentMock
 */
class Controller_CommentMock extends Controller_Comment
{
    public $RESTManager;
    public $RESTServer;

    public function __construct($settings, $mgr, $svr, $db, $services = [])
    {
        $this->Load = new LoaderMock();
        $this->Settings = $settings;
        $this->RESTManager = $mgr;
        $this->RESTServer = $svr;
        $this->Database = $db;
        foreach ($services as $key => $service) {
            $this->$key = $service;
        }
        $this->SetIsClassLoaded(true);
        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}
