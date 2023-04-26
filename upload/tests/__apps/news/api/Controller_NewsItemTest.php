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

namespace News\Api;

use SWIFT;
use News\Admin\LoaderMock;

/**
 * Class Controller_NewsItemTest
 * @group news
 */
class Controller_NewsItemTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        // reset test data
        unset($_POST);
    }

    /**
     * @param array $services
     * @return Controller_NewsItemMock
     */
    protected function getController(array $services = [])
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('AutoExecute')->willReturn(1);

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false, true, false, true, false, true, false, true, false);

        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'cronid')) {
                return false;
            }

            if (false !== strpos($x, "newsitemid = '2'") ||
                false !== strpos($x, "staffid = '2'") ||
                false !== strpos($x, "userid = '2'")) {
                return false;
            }

            if (false !== strpos($x, "staffid = '1'")) {
                return [
                    'staffid' => 1,
                    'fullname' => 'fullname',
                    'email' => 'me@email.com',
                ];
            }

            if (false !== strpos($x, "userid = '1'")) {
                return [
                    'userid' => 1,
                    'fullname' => 'fullname',
                ];
            }

            return [
                'newsitemid' => 1,
                'userid' => 1,
                'usergroupid' => 1,
                'visibilitytype' => 1,
                'subject' => 'subject',
                'email' => 'me@email.com',
                'contents' => 'contents',
                'newsstatus' => '1',
                'emailsubject' => 'emailsubject',
                'allowcomments' => '1',
                'uservisibilitycustom' => '1',
                'staffvisibilitycustom' => '1',
                'start' => '1',
                'expiry' => '2',
            ];
        });

        $this->mockProperty($mockDb, 'Record', [
            'newsitemid' => 1,
            'newscategoryid' => 1,
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

        return new Controller_NewsItemMock($settings, $mgr, $svr, $lang, $mockDb, $services);
    }

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('News\Api\Controller_NewsItem', $obj);
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
    public function testProcessNewsItemsReturnsTrue()
    {
        $obj = $this->getController();

        $ref = new \ReflectionClass($obj);
        $method = $ref->getMethod('ProcessNewsItems');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj),
            'Returns true without id');

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with id');

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
    public function testListAllReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->ListAll(1),
            'Returns true after rendering XML');

        $this->assertTrue($obj->ListAll(1),
            'Returns true after rendering XML');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ListAll(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getController();

        $_POST['newstype'] = 2;
        $this->assertFalse($obj->Post(),
            'Returns false with newstype public');

        $_POST['newstype'] = 1;
        $_POST['newsstatus'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false without staffid');

        $_POST['staffid'] = 2;
        $this->assertFalse($obj->Post(),
            'Returns false without subject');

        $_POST['subject'] = 'subject';
        $this->assertFalse($obj->Post(),
            'Returns false without contents');

        $_POST['contents'] = 'contents';
        $this->assertFalse($obj->Post(),
            'Returns false with invalid staffid');

        $_POST['staffid'] = 1;
        $_POST['fromname'] = 'fromname';
        $_POST['email'] = 'me@email.com';
        $_POST['customemailsubject'] = 'customemailsubject';
        $_POST['sendemail'] = 1;
        $_POST['allowcomments'] = '0';
        $_POST['uservisibilitycustom'] = '1';
        $_POST['staffvisibilitycustom'] = '1';
        $_POST['start'] = '1';
        $_POST['expiry'] = '2';
        $_POST['newscategoryidlist'] = '1';
        $_POST['usergroupidlist'] = '1';
        $_POST['staffgroupidlist'] = '1';

        $this->assertTrue($obj->Post(),
            'Returns true after rendering XML');

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
            'Returns false without editedstaffid');

        $_POST['editedstaffid'] = 2;
        $this->assertFalse($obj->Put(2),
            'Returns false without subject');

        $_POST['subject'] = ' ';
        $this->assertFalse($obj->Put(1),
            'Returns false without subject');

        $_POST['subject'] = 'subject';
        $_POST['contents'] = ' ';
        $this->assertFalse($obj->Put(1),
            'Returns false without contents');

        $_POST['contents'] = 'contents';
        $_POST['newsstatus'] = '1';
        $this->assertFalse($obj->Put(1),
            'Returns false with invalid staff id');

        $_POST['editedstaffid'] = 1;
        $_POST['fromname'] = 'fromname';
        $_POST['email'] = 'me@email.com';
        $_POST['customemailsubject'] = 'customemailsubject';
        $_POST['sendemail'] = 1;
        $_POST['allowcomments'] = '1';
        $_POST['uservisibilitycustom'] = '1';
        $_POST['staffvisibilitycustom'] = '1';
        $_POST['start'] = '1';
        $_POST['expiry'] = '2';
        $_POST['newscategoryidlist'] = '1';
        $_POST['usergroupidlist'] = '1';
        $_POST['staffgroupidlist'] = '1';
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

        $this->assertFalse($obj->Delete(2),
            'Returns false with invalid id');

        $this->assertTrue($obj->Delete(1),
            'Returns true after deleting');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete(0);
    }
}

/**
 * Class Controller_NewsItemMock
 * @package Troubleshooter\Api
 */
class Controller_NewsItemMock extends Controller_NewsItem
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
        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}
