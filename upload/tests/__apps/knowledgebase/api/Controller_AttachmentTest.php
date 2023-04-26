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
* Class Controller_AttachmentTest
* @group knowledgebase
*/
class Controller_AttachmentTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        // reset test data
        unset($_POST);
    }

    /**
     * @param array $services
     * @return Controller_AttachmentMock
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
                if (false !== strpos($x, "attachmentid = '0'") ||
                    false !== strpos($x, "kbarticleid = '2'")) {
                    return false;
                }

                return [
                    'kbarticleid' => 1,
                    'attachmentid' => 1,
                    'linktypeid' => 1,
                    'filename' => 'file.txt',
                    'filesize' => 1,
                    'filetype' => 'file',
                    'dateline' => time(),
                    'storefilename' => 'file.txt',
                    'attachmenttype' => 0
                ];
            });

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->mockProperty($mockDb, 'Record', [
            'attachmentid' => 1,
            'linktypeid' => 1,
            'filename' => 'file.txt',
            'filesize' => 1,
            'filetype' => 'file',
            'dateline' => time(),
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

        return new Controller_AttachmentMock($settings, $mgr, $svr, $mockDb, $services);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Knowledgebase\Api\Controller_Attachment', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetListReturnsFalse()
    {
        $obj = $this->getController();
        $this->assertFalse($obj->GetList());
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetList();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testListAllPrintsXml()
    {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getController(['XML' => $xml]);

        // test without attachmentId
        $this->assertFalse($obj->ListAll(0),
            'Returns false with invalida data');

        // test with attachmentId
        $this->assertTrue($obj->ListAll(1, 1),
            'Returns true with valid data');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ListAll(0);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testGetPrintsXml()
    {
        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        // pass mock XML service
        $obj = $this->getController(['XML' => $xml]);

        $this->assertFalse($obj->Get(0, 0),
            'Returns false with invalid data');

        $this->assertFalse($obj->Get(2, 1),
            'Returns false with non existent object');

        $this->assertFalse($obj->Get(1, 0),
            'Returns false with invalid attachment');

        $this->assertFalse($obj->Get(3, 1),
            'Returns false with invalid attachment link');

        $this->assertTrue($obj->Get(1, 1),
            'Returns true with valid data');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Get(1, 1);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testPostPrintsXml() {

        $mockSet = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockSet->method('Get')->willReturn(1);

        \SWIFT::GetInstance()->Settings = $mockSet;

        $xml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        // pass mock XML service
        $obj = $this->getController(['XML' => $xml]);

        $this->assertFalse($obj->Post(),
            'Returns false without valid POST data');

        $_POST['kbarticleid'] = 2;
        $this->assertFalse($obj->Post(),
            'Returns false with non existent step');

        $_POST['kbarticleid'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false without filename');

        $_POST['filename'] = 'file.txt';
        $this->assertFalse($obj->Post(),
            'Returns false without contents');

        $_POST['contents'] = 'contents';
        $this->assertFalse($obj->Post(),
            'Returns false with valid POST data');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Post();
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testDeleteReturnsTrue() {
        $obj = $this->getController();

        $this->assertFalse($obj->Delete(0, 0),
            'Returns false with invalid data');

        $this->assertFalse($obj->Delete(2, 1),
            'Returns false with non existent object');

        $this->assertFalse($obj->Delete(1, 0),
            'Returns false with invalid attachment');

        $this->assertFalse($obj->Delete(3, 1),
            'Returns false with invalid attachment link');

        $this->assertTrue($obj->Delete(1, 1),
            'Returns true with valid data');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Delete(1, 1);
    }
}

/**
 * Class Controller_AttachmentMock
 */
class Controller_AttachmentMock extends Controller_Attachment
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
