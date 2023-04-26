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

namespace Tickets\Staffapi;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_PushTest
 * @group tickets
 * @group tickets-staffapi
 */
class Controller_PushTest extends \SWIFT_TestCase
{
    public static $prop = [];

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Staffapi\Controller_Push', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $SWIFT = \SWIFT::GetInstance();

        $obj = $this->getMocked();

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnCallback(function ($x) {
            if (!isset(static::$prop[$x])) {
                static::$prop[$x] = 0;
            } else {
                if (static::$prop[$x] === 0) {
                    static::$prop[$x] = 1;
                }
            }

            return static::$prop[$x];
        });
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetAssignedDepartments')->willReturn([1]);
        $mockStaff->method('GetProperty')->willReturnCallback(function ($x) {
            if (strtolower(substr($x, -2)) === 'id') {
                return '1';
            }

            return $x;
        });

        $SWIFT->Staff = $mockStaff;

        static::$prop['departmentid'] = 1;
        static::$prop['userid'] = 1;
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => &static::$prop['departmentid'],
            'tickettypeid' => 1,
            'priorityid' => 1,
            'ticketmaskid' => 0,
            'tgroupid' => 1,
            'regusergroupid' => 1,
            'usergroupid' => 1,
            'userid' => &static::$prop['userid'],
            'userdesignation' => '',
            'salutation' => '',
            'fullname' => 'fullname',
            'email' => 'me@mail.com',
            'oldeditemailaddress' => 'me@mail.com',
            'replyto' => '',
            'ticketslaplanid' => '0',
            'slaplanid' => '0',
            'firstresponsetime' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'ticketpostid' => 1,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'subject' => 'subject',
            'emailqueueid' => '0',
            'trasholddepartmentid' => 0,
            'ticketstatusid' => 1,
            'isresolved' => 1,
            'dateline' => 0,
            'userorganizationid' => 0,
            'languageid' => 1,
            'flagtype' => 1,
            'title' => 'title',
            'ownerstaffid' => '1',

            'tickettimetrackid' => '1',
            'tickettimetracknoteid' => 1,
            'timeworked' => 0,
            'timebilled' => 0,
            'wasreopened' => 0,
            'bayescategoryid' => 0,

            'attachmentid' => 1,
            'filename' => 'file.txt',
            'storefilename' => 'file.txt',
            'attachmenttype' => 1,
            'filesize' => 1,
            'filetype' => 'file',
            'linktype' => 1,
        ];
        $SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, 'userid')) {
                static::$prop['userid'] = 1;
            }

            return $arr;
        });
        $SWIFT->Database->Record = $arr;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturnCallback(function ($x) {
            if (strpos($x, 'en-us') === 0) {
                return [
                    'log_newticket' => '%d %s',
                    'watcherprefix' => '%s %s',
                    'activitytrashticket' => '%s %s %s %s',
                    'log_newreply' => '%d',
                    'notification_department' => '1',
                    'notification_resolutiondue' => '1',
                    'notification_due' => '1',
                    'notification_flag' => '1',
                ];
            }

            return [
                1 => [
                    'departmentapp' => 'tickets',
                    'parentdepartmentid' => '0',
                    'languagecode' => 'en-us',
                    'staffid' => '1',
                    'fullname' => 'fullname',
                    'staffgroupid' => '1',
                    'groupassigns' => '1',
                    'isenabled' => '1',
                ],
            ];
        });

        $SWIFT->Cache = $mockCache;
        $obj->Cache = $mockCache;

        \SWIFT::Set('loopcontrol', true);

        $this->expectOutputRegex('/.*/');

        $xml = <<<XML
<?xml version="1.0"?>
<kayako_staffapi>
    <create staffapiid="">
        <subject>Test Ticket 1</subject>
        <fullname>Lee R</fullname>
        <email>lee.r@deusmachine.com</email>
        <reply>
            <contents>test</contents>
            <attachment filename="file.txt" md5="md5">contents</attachment>
        </reply>

        <!-- Begin Ticket Properties -->
        <departmentid>9</departmentid>
        <ticketstatusid>4</ticketstatusid>
        <ticketpriorityid>44</ticketpriorityid>
        <tickettypeid>10</tickettypeid>
        <emailqueueid>0</emailqueueid>

        <!-- Begin Creator Info -->
        <creator>other</creator>
        <staffid>35</staffid>

        <!-- Begin Other Info-->
        <type>sendmail</type>
        <phonenumber>8778406027</phonenumber>
        <sendautoresponder>0</sendautoresponder>
        <flagtype>0</flagtype>
        <tickettype>phone</tickettype>
        <watch>2</watch>
        <ccto>me@mail.com</ccto>
        <ccto>me</ccto>
        <bccto>metoo</bccto>
        <bccto>metoo@mail.com</bccto>
        <resolutiondue>0</resolutiondue>
        <replydue>0</replydue>
        <tags></tags>
        <note notecolor="1" type="type">contents</note>
        <billing worker="1" notecolor="1" timeworked="0" timebillable="0" workdate="0" billdate="0">contents</billing>
    </create>
    <modify ticketid="1">
        <subject>Test Ticket 1</subject>
        <fullname>Lee R</fullname>
        <email>lee.r@deusmachine.com</email>
        <reply>
            <contents>test</contents>
            <attachment filename="file.txt" md5="md5">contents</attachment>
        </reply>

        <!-- Begin Ticket Properties -->
        <departmentid>1</departmentid>
        <ticketstatusid>2</ticketstatusid>
        <ticketpriorityid>2</ticketpriorityid>
        <tickettypeid>2</tickettypeid>
        <emailqueueid>0</emailqueueid>

        <!-- Begin Creator Info -->
        <creator>staff</creator>
        <staffid>1</staffid>
        <ownerstaffid>2</ownerstaffid>

        <!-- Begin Other Info-->
        <type>1</type>
        <phonenumber>8778406027</phonenumber>
        <sendautoresponder>0</sendautoresponder>
        <flagtype>1</flagtype>
        <tickettype>phone</tickettype>
        <ccto>me@mail.com</ccto>
        <ccto>me</ccto>
        <bccto>metoo</bccto>
        <bccto>metoo@mail.com</bccto>
        <resolutiondue>0</resolutiondue>
        <replydue>0</replydue>
        <tags></tags>
        <note notecolor="1" type="type">contents</note>
        <billing worker="1" notecolor="1" timeworked="0" timebillable="0" workdate="0" billdate="0">contents</billing>
    </modify>
    <delete ticketid="1">
    </delete>
</kayako_staffapi>
XML;
        $xmlObj = simplexml_load_string($xml);
        $_POST['payload'] = $xmlObj->asXML();

        $this->assertTrue($obj->Index(),
            'Returns true without permission');

        // permission
        $this->assertTrue($obj->Index(),
            'Returns true without departmentid');

        static::$prop['departmentid'] = 0;
        $xmlObj->modify[0]->watch = '0';
        $xmlObj->modify[0]->tags = ' a';
        $this->assertTrue($obj->Index(),
            'Returns true without departmentid');

        // test modify with department
        $xmlObj->modify[0]->departmentid = '2';
        $xmlObj->modify[0]->ticketstatusid = '1';
        $xmlObj->modify[0]->ticketpriorityid = '1';
        $xmlObj->modify[0]->tickettypeid = '1';
        $xmlObj->modify[0]->ownerstaffid = '1';
        $xmlObj->modify[0]->resolutiondue = '1';
        $xmlObj->modify[0]->replydue = '1';
        $xmlObj->modify[0]->watch = '1';
        $_POST['payload'] = $xmlObj->asXML();
        $this->assertTrue($obj->Index());

        // test modify exception
        $xmlObj->modify[0]->attributes()[0] = '0';
        $_POST['payload'] = $xmlObj->asXML();
        $this->assertTrue($obj->Index());

        // test delete exception
        $xmlObj->delete[0]->attributes()[0] = '0';
        $_POST['payload'] = $xmlObj->asXML();
        $this->assertTrue($obj->Index());

        $xmlObj->create[0]->userid = '0';
        $xmlObj->create[0]->creator = 'staff';
        $xmlObj->create[0]->departmentid = 1;
        $xmlObj->create[0]->watch = 0;
        $_POST['payload'] = $xmlObj->asXML();
        $this->assertTrue($obj->Index(),
            'Returns true without ticketstatusid');

        $xmlObj->create[0]->type = 'phone';
        $xmlObj->create[0]->ticketstatusid = 1;
        $_POST['payload'] = $xmlObj->asXML();
        $this->assertTrue($obj->Index(),
            'Returns true without ticketpriorityid');

        $xmlObj->create[0]->ticketpriorityid = 1;
        $_POST['payload'] = $xmlObj->asXML();
        $this->assertTrue($obj->Index(),
            'Returns true without tickettypeid');

        $xmlObj->create[0]->tickettypeid = 1;
        $xmlObj->create[0]->flagtype = 2;
        $xmlObj->create[0]->tags = 'tag1 tag2 '. chr(2);
        $xmlObj->create[0]->ownerstaffid = 1;
        $xmlObj->create[0]->resolutiondue = 1;
        $xmlObj->create[0]->replydue = 1;
        $xmlObj->create[0]->watch = 1;
        $_POST['payload'] = $xmlObj->asXML();
        $this->assertTrue($obj->Index(),
            'Returns true with invalid ownerstaffid');

        $xmlObj->create[0]->ownerstaffid = 2;
        $_POST['payload'] = $xmlObj->asXML();
        $this->assertTrue($obj->Index(),
            'Returns true without emailqueueid');

        static::$prop['userid'] = 1;
        $xmlObj->create[0]->type = 'sendmail';
        $xmlObj->create[0]->userid = 1;
        $xmlObj->create[0]->ownerstaffid = 1;
        $_POST['payload'] = $xmlObj->asXML();
        $this->assertTrue($obj->Index(),
            'Returns true ');

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetDispatchFromEmailReturnsEmail()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_GetDispatchFromEmail');
        $method->setAccessible(true);

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            'list' => [
                [],
                [
                    'departmentid' => '1',
                    'customfromemail' => 'me@mail.com',
                ],
                [
                    'departmentid' => '1',
                    'email' => 'metoo@mail.com',
                ],
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertContains('mail.com', $method->invoke($obj, 1));
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessCreateWatcherReturnsTrue()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('processCreateWatcher');
        $method->setAccessible(true);

        $_SWIFT = \SWIFT::GetInstance();

        $sm = '';
        $xml = new \SimpleXMLElement('<xml><watch></watch></xml>');
        $mock = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetTicketID')->willReturn(1);
        $this->assertEmpty($method->invoke($obj, $_SWIFT, $sm, $xml, $mock));
        $this->assertNotEmpty($method->invoke($obj, $_SWIFT, $sm, $xml, $mock));
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessWatcherReturnsTrue()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('processWatcher');
        $method->setAccessible(true);

        $_SWIFT = \SWIFT::GetInstance();

        $sm = '';
        $xml = new \SimpleXMLElement('<xml><watch></watch></xml>');
        $mock = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetTicketID')->willReturn(1);
        $this->assertNotEmpty($method->invoke($obj, $_SWIFT, $sm, $xml, $mock));
        $this->assertNotEmpty($method->invoke($obj, $_SWIFT, $sm, $xml, $mock));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetUserObjectReturnsTrue()
    {
        $SWIFT = \SWIFT::GetInstance();
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('getUserObject');
        $method->setAccessible(true);

        $SWIFT->Database->method('QueryFetch')->willReturn([
            'tgroupid' => 1,
            'regusergroupid' => 1,
            'userid' => 1,
            'usergroupid' => 1,
            'linktype' => 1,
            'linktypeid' => 1,
            'useremailid' => 1,
            'email' => 'me@mail.com',
        ]);

        $SWIFT->Database->Record = [
            'email' => 'me@mail.com',
            'useremailid' => 1,
        ];

        \SWIFT::Set('loopcontrol', true);

        $mock = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetProperty')->willReturnMap([
            ['userid', 0],
            ['fullname', 'fullname'],
            ['email', 'me@mail.com'],
        ]);
        $this->assertNotEmpty($method->invoke($obj, $mock));
    }

    /**
     * @throws \ReflectionException
     */
    public function testgetUserObjectForCreateReturnsTrue()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('getUserObjectForCreate');
        $method->setAccessible(true);

        $SWIFT = \SWIFT::GetInstance();

        $SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "userid = '0'")) {
                static::$prop['userid'] = 1;

                return [
                    'tgroupid' => 1,
                    'regusergroupid' => 1,
                    'userid' => 0,
                    'usergroupid' => 1,
                    'linktype' => 1,
                    'linktypeid' => 1,
                    'useremailid' => 1,
                    'email' => 'me@mail.com',
                ];
            }

            return [
                'tgroupid' => 1,
                'regusergroupid' => 1,
                'userid' => &static::$prop['userid'],
                'usergroupid' => 1,
                'linktype' => 1,
                'linktypeid' => 1,
                'useremailid' => 1,
                'email' => 'me@mail.com',
            ];
        });

        $SWIFT->Database->Record = [
            'email' => 'me@mail.com',
            'useremailid' => 1,
        ];

        list($_fullName, $_email, $_phoneNumber, $_creator) = ['name', 'me@mail.com', '1', 'user'];

        static::$prop['userid'] = 1;
        $xml = new \SimpleXMLElement('<xml><watch></watch></xml>');
        $this->assertNotEmpty($method->invoke($obj, $xml, $_fullName, $_email, $_phoneNumber, $_creator));

        static::$prop['userid'] = 0;
        $xml = new \SimpleXMLElement('<xml><userid>0</userid></xml>');
        $this->assertNotEmpty($method->invoke($obj, $xml, $_fullName, $_email, $_phoneNumber, $_creator));
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_PushMock
     */
    private function getMocked(array $services = [])
    {
        $int = $this->getMockBuilder('SWIFT_Interface')
            ->disableOriginalConstructor()
            ->getMock();
        $int->method('GetIsClassLoaded')->willReturn(true);
        $int->method('GetInterface')->willReturn(\SWIFT_Interface::INTERFACE_TESTS);
        $obj = $this->getMockObject('Tickets\Staffapi\Controller_PushMock', array_merge($services, [
            'Interface' => $int,
        ]));

        return $obj;
    }
}

class Controller_PushMock extends Controller_Push
{
    public $Cache;

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

