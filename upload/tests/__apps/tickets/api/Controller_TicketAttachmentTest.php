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

namespace Tickets\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketAttachmentTest
 * @group tickets
 * @group tickets-api
 */
class Controller_TicketAttachmentTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Api\Controller_TicketAttachment', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->GetList(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testListAllReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->ListAll(1),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'attachmentid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertTrue($obj->ListAll(1, 1));

        $this->assertClassNotLoaded($obj, 'ListAll', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Get(1, 1),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'dateline' => 1,
            'filetype' => 1,
            'filesize' => 1,
            'filename' => 1,
            'storefilename' => 1,
            'linktypeid' => 1,
            'contents' => 1,
            'attachmenttype' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, "attachmentid = '2'")) {
                $arr['attachmentid'] = 2;
            }

            if (false !== strpos($x, "attachmentid = '1'")) {
                $arr['attachmentid'] = 1;
            }

            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Get(2, 3));
        $this->assertFalse($obj->Get(2, 2));
        $this->assertTrue($obj->Get(1, 1));

        $this->assertClassNotLoaded($obj, 'Get', 1, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Post(),
            'Returns false without id');

        $_POST['ticketid'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false without post id');

        $_POST['ticketpostid'] = 2;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'attachmentid' => 1,
            'filename' => 'file.txt',
            'filesize' => 1,
            'filetype' => 'file',
            'storefilename' => 'file.txt',
            'attachmenttype' => 0,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, "ticketpostid = '1'")) {
                $arr['ticketpostid'] = 1;
            }
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Post(),
            'Returns false with invalid post id');

        $_POST['ticketpostid'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false without filename');

        $_POST['filename'] = 'file.txt';
        $this->assertFalse($obj->Post(),
            'Returns false without contents');

        $_POST['contents'] = 'contents';
        $this->assertTrue($obj->Post());

        $this->assertClassNotLoaded($obj, 'Post');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Delete(1, 1),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, "attachmentid = '2'")) {
                $arr['attachmentid'] = 2;
            }

            if (false !== strpos($x, "attachmentid = '1'")) {
                $arr['attachmentid'] = 1;
            }

            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Delete(2, 3));
        $this->assertFalse($obj->Delete(2, 2));
        $this->assertTrue($obj->Delete(1, 1));

        $this->assertClassNotLoaded($obj, 'Delete', 1, 1);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketAttachmentMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Api\Controller_TicketAttachmentMock');
    }
}

class Controller_TicketAttachmentMock extends Controller_TicketAttachment
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

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

