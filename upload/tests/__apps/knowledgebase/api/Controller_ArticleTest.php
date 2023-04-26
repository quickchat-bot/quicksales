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
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Knowledgebase\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_ArticleTest
 * @group knowledgebase
 */
class Controller_ArticleTest extends \SWIFT_TestCase
{
    public static $_count = 0;

    public function setUp()
    {
        parent::setUp();

        // reset test data
        unset($_POST);
    }

    /**
     * @param array $services
     * @return Controller_ArticleMock
     * @throws \SWIFT_Exception
     */
    protected function getController(array $services = [])
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Insert_ID')->willReturn(1);

        $mockDb->method('QueryFetch')
            ->willReturnCallback(function ($x) {
                if (false !== strpos($x, "attachmentid = '0'") ||
                    false !== strpos($x, "kbarticleid = '2'") ||
                    false !== strpos($x, "staffid = '2'")) {
                    return false;
                }

                return [
                    'staffid' => 1,
                    'kbarticleid' => 1,
                    'attachmentid' => 1,
                    'linktypeid' => 1,
                    'filename' => 'file.txt',
                    'filesize' => 1,
                    'filetype' => 'file',
                    'fullname' => 'fullname',
                    'subject' => 'subject',
                    'seosubject' => 'seosubject',
                    'contents' => 'contents',
                    'email' => 'me@email.com',
                    'dateline' => time(),
                    'storefilename' => 'file.txt',
                    'attachmenttype' => 0,
                ];
            });

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false, true, false, true, false, true, false);

        $this->mockProperty($mockDb, 'Record', [
            'attachmentid' => 1,
            'linktypeid' => 1,
            'filename' => 'file.txt',
            'hasattachments' => '1',
            'articlestatus' => 1,
            'filesize' => 1,
            'filetype' => 'file',
            'subject' => 'subject',
            'dateline' => time(),
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $lang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $lang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            return $x;
        });
        \SWIFT::GetInstance()->Language = $lang;

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();
        $settings->method('Get')
            ->willReturn('1');
        \SWIFT::GetInstance()->Settings = $settings;

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

        return new Controller_ArticleMock($settings, $mgr, $svr, $mockDb, $services);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Knowledgebase\Api\Controller_Article', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetListPrintsXml()
    {
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
    public function testProtectedMethodThrowsException()
    {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getController(['XML' => $xml]);

        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('ProcessKnowledgebaseArticles');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj, 1));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, false);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testListAllPrintsXml()
    {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        // pass mock XML service
        $obj = $this->getController(['XML' => $xml]);

        $this->assertTrue($obj->ListAll(1),
            'Returns true after printing XML using id');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ListAll(1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetPrintsXml()
    {
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
    public function testPostPrintsXml()
    {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $cache->method('Get')->willReturn(['1' => [1]]);

        // pass mock XML service
        $obj = $this->getController(['XML' => $xml, 'Cache' => $cache]);

        $this->assertFalse($obj->Post(),
            'Returns false without subject');

        $_POST['subject'] = 'subject';
        $this->assertFalse($obj->Post(),
            'Returns false without contents');

        $_POST['contents'] = 'contents';
        $this->assertFalse($obj->Post(),
            'Returns false without creator id');

        $_POST['creatorid'] = 2;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid creator id');

        $_POST['creatorid'] = 1;
        $_POST['articlestatus'] = 1;
        $_POST['seosubject'] = 'seosubject';
        $_POST['isfeatured'] = '1';
        $_POST['allowcomments'] = '1';
        $_POST['categoryid'] = '1';
        $this->assertTrue($obj->Post(),
            'Returns true after printing XML');

        $_POST['articlestatus'] = 2;
        unset($_POST['categoryid']);
        $this->assertTrue($obj->Post(),
            'Returns true with articlestatus=draft');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Post();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPutPrintsXml()
    {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $cache->method('Get')->willReturn(['1' => [1]]);

        // pass mock XML service
        $obj = $this->getController(['XML' => $xml, 'Cache' => $cache]);

        $this->assertFalse($obj->Put(1),
            'Returns false without editedstaffid');

        $_POST['editedstaffid'] = '2';
        $this->assertFalse($obj->Put(0),
            'Returns false with invalid id');

        $_POST['subject'] = ' ';
        $this->assertFalse($obj->Put(1),
            'Returns false with empty subject');

        $_POST['subject'] = 'subject';
        $_POST['seosubject'] = 'seosubject';
        $_POST['contents'] = ' ';
        $this->assertFalse($obj->Put(1),
            'Returns false with empty contents');

        $_POST['contents'] = 'contents';
        $this->assertFalse($obj->Put(1),
            'Returns false with invalid staffid');

        $_POST['editedstaffid'] = '1';
        $_POST['isfeatured'] = '1';
        $_POST['allowcomments'] = '1';
        $_POST['categoryid'] = '1';
        $_POST['articlestatus'] = '1';
        $this->assertTrue($obj->Put(1),
            'Returns true after printing XML');

        $_POST['articlestatus'] = 2;
        unset($_POST['categoryid']);
        $this->assertTrue($obj->Put(1),
            'Returns true with articlestatus=draft');

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

        $this->assertFalse($obj->Delete(0),
            'Returns false with invalid id');

        $this->assertTrue($obj->Delete(1),
            'Returns true after deleting');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetArticleCountReturnsTrue()
    {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getController(['XML' => $xml]);

        $this->assertFalse($obj->GetArticleCount(0),
            'Returns false with invalid id');

        $this->assertTrue($obj->GetArticleCount(1),
            'Returns true');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetArticleCount(0);
    }
}

/**
 * Class Controller_ArticleMock
 */
class Controller_ArticleMock extends Controller_Article
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
