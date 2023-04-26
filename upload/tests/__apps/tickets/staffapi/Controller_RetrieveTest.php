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

namespace Tickets\Staffapi;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_RetrieveTest
 * @group tickets
 * @group tickets-staffapi
 */
class Controller_RetrieveTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Staffapi\Controller_Retrieve', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketmaskid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $p = [
            'departmentid' => 1,
            'wantticketdata' => 1,
            'sortby' => 'type',
            'limit' => 1,
            'start' => 1,
            'filterid' => 1,
            'ticketid' => 1,
            'ownerid' => 1,
            'statusid' => 1,
            'sortorder' => 'asc',
        ];
        foreach ($p as $k => $v) {
            $_POST[$k] = $v;
        }

        $this->assertTrue($obj->Index(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexWithoutTicketIDReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $arr = [
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketmaskid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $p = [
            'departmentid' => 1,
            'wantticketdata' => 1,
            'sortby' => 'type',
            'limit' => 1,
            'start' => 1,
            'filterid' => 1,
            'ownerid' => 1,
            'statusid' => 1,
            'sortorder' => 'asc',
        ];
        foreach ($p as $k => $v) {
            $_POST[$k] = $v;
        }

        $this->assertTrue($obj->Index(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDataReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Data(),
            'Returns false without POST');

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketmaskid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $p = [
            'departmentid' => 1,
            'wantticketdata' => 0,
            'sortby' => 'type',
            'limit' => 1,
            'start' => 1,
            'filterid' => 1,
            'ticketid' => 1,
            'ownerid' => 1,
            'wantattachmentdata' => 1,
            'wantpostsonly' => 1,
            'statusid' => 1,
            'sortorder' => 'asc',
        ];
        foreach ($p as $k => $v) {
            $_POST[$k] = $v;
        }

        $this->assertTrue($obj->Data(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Data');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'ticketmaskid' => 1,
        ];
        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn($arr);
        \SWIFT::GetInstance()->Database->Record = $arr;

        $p = [
            'limit' => 1,
            'start' => 1,
            'ticketid' => 1,
            'ownerid' => 1,
            'departmentid' => 1,
            'statusid' => 1,
            'query' => 'q',
            'subject' => 1,
            'contents' => 1,
            'author' => 1,
            'email' => 1,
            'fullname' => 1,
            'notes' => 1,
            'usergroup' => 1,
            'userorganization' => 1,
            'user' => 1,
            'tags' => 1,
        ];
        foreach ($p as $k => $v) {
            $_POST[$k] = $v;
        }

        $this->assertTrue($obj->Search(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Search');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testAttachmentReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Attachment(),
            'Returns false without POST');

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturnOnConsecutiveCalls([], [1], [1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,

            'attachmentid' => 1,
            'filename' => 'file.txt',
            'storefilename' => 'file.txt',
            'attachmenttype' => 1,
            'filesize' => 1,
            'filetype' => 'file',
            'linktype' => &static::$_prop['linktype'],
        ]);

        $_POST['ticketid'] = 1;
        $_POST['attachmentid'] = 1;
        $this->assertFalse($obj->Attachment(),
            'Returns false without access');

        static::$_prop['linktype'] = 2;
        $this->assertFalse($obj->Attachment(),
            'Returns false without permission');

        $this->expectOutputRegex('/./');

        static::$_prop['linktype'] = 1;
        $this->assertTrue($obj->Attachment(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Attachment');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_RetrieveMock
     */
    private function getMocked(array $services = [])
    {
        $int = $this->getMockBuilder('SWIFT_Interface')
            ->disableOriginalConstructor()
            ->getMock();
        $int->method('GetIsClassLoaded')->willReturn(true);
        $int->method('GetInterface')->willReturn(\SWIFT_Interface::INTERFACE_TESTS);
        $obj = $this->getMockObject('Tickets\Staffapi\Controller_RetrieveMock', array_merge($services, [
            'Interface' => $int,
        ]));

        return $obj;
    }
}

class Controller_RetrieveMock extends Controller_Retrieve
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

