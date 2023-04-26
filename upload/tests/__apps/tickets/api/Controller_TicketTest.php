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

namespace Tickets\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketTest
 * @group tickets
 * @group tickets-api
 */
class Controller_TicketTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Api\Controller_Ticket', $obj);
    }

    public function testIsValidSortFieldReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj::IsValidSortField('userid'),
            'Returns true without errors');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->GetList(),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testListAllReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['email'] = 'me@mail.com';
        $this->assertTrue($obj->ListAll('1'),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'ListAll', '1');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Get(1),
            'Returns false without permission');

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
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertTrue($obj->Get(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Get', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Delete(1),
            'Returns false without permission');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketmaskid' => 0,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertTrue($obj->Delete(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Delete', 1);
    }

    public function testRetrieveIDListFromCSVReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertEquals([0, 1], $obj::RetrieveIDListFromCSV('0,1'));

        $this->assertEquals([0], $obj::RetrieveIDListFromCSV('no'));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTicketCountReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['email'] = 'me@mail.com';
        $this->assertTrue($obj->GetTicketCount('1'),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'GetTicketCount', '1');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessTicketRelatedDataReturnsArray()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => 1,
                    'departmentid' => 1,
                    'markasresolved' => 0,
                ],
            ];
        };

        $this->assertNotEmpty($obj->ProcessTicketRelatedData('1'),
            'Returns true without errors');

        $this->assertNotEmpty($obj->ProcessTicketRelatedData('1', '1', '1', '1'),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'ProcessTicketRelatedData', '1');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPutReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Put(1),
            'Returns false without permission');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketmaskid' => 0,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'ticketstatusid' => 1,
            'ownerstaffid' => 1,
            'tgroupid' => 1,
            'fullname' => 'fullname',
            'subject' => 'subject',
            'replyto' => '',
            'email' => 'me@mail.com',
            'userid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                0 => [],
                2 => [
                    'title' => 'no',
                ],
                1 => [
                    1 => 1,
                    'regusergroupid' => '1',
                    'departmentapp' => 'tickets',
                    'languagecode' => 'en-us',
                ],
            ];
        };

        $_POST['departmentid'] = 1;
        $_POST['ticketstatusid'] = 1;
        $_POST['ticketpriorityid'] = 1;
        $_POST['tickettypeid'] = 1;
        $_POST['ownerstaffid'] = 1;
        $_POST['fullname'] = 1;
        $_POST['email'] = 'me@mail.com';
        $_POST['subject'] = 1;
        $_POST['userid'] = 1;
        $_POST['templategroup'] = 0;
        $this->assertTrue($obj->Put(1),
            'Returns true with permission');

        $_POST['templategroup'] = 'no';
        $this->assertTrue($obj->Put(1),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Put', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getMocked();

        $post = [
            'subject',
            'fullname',
            'email',
            'contents',
            'departmentid',
            'ticketstatusid',
            'ticketpriorityid',
            'tickettypeid',
            'userid',
            'staffid',
        ];
        foreach ($post as $p) {
            $this->assertFalse($obj->Post(),
                'Returns false without ' . $p);

            $_POST[$p] = $p;
        }

        $this->assertFalse($obj->Post(),
            'Returns false with invalid departmentid');

        $_POST['departmentid'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid app');

        static::$databaseCallback['CacheGet'] = function ($x) {
            if($x == 'languagecache')
                return [1 => ['languagecode' => 'en-us']];

            if($x == 'templategroupcache')
                return [1 => ['languageid' => 1, '']];


            return [
                2 => [
                    'title' => 'templategroup',
                ],
                1 => [
                    1 => 1,
                    'departmentapp' => 'tickets',
                    'languagecode' => 'en-us',
                ],
                'list' => [
                    1 => [
                        'email' => 'me@mail.com',
                        'departmentid' => '1',
                        'isenabled' => '1',
                    ],
                ],
            ];
        };

        $this->assertFalse($obj->Post(),
            'Returns false with invalid ticketstatusid');

        $_POST['ticketstatusid'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid ticketpriorityid');

        $_POST['ticketpriorityid'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid tickettypeid');

        $_POST['tickettypeid'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid staffid');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'tgroupid' => 1,
            'userid' => 1,
            'staffid' => 1,
            'userdesignation' => '',
            'salutation' => '',
            'fullname' => 'fullname',
            'ticketslaplanid' => 0,
            'slaplanid' => 0,
            'ticketpostid' => 1,
            'averageresponsetimehits' => 0,
            'firstresponsetime' => 0,
            'totalreplies' => 0,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'ticketmaskid' => 0,
            'emailqueueid' => 0,
            'dateline' => 0,
            'languageid' => 1,
            'languageengineid' => 1,
            'tickettypeid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'replyto' => '',
            'useremailid' => 1,
            'isthirdparty' => 0,
            'creator' => 0,
            'isprivate' => 0,
            'contents' => '<html>contents</html>',
            'ishtml' => 1,
            'userorganizationid' => 0,
            'guestusergroupid' => 0,
            'hasattachments' => 0,
            'subject' => 'subject',
            'email' => 'me@mail.com',
            'regusergroupid' => 1,
            'usergroupid' => 1,
            'linktype' => 1,
            'linktypeid' => '1',
            'title' => 'title',
            'ownerstaffid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);

        static::$databaseCallback['NextRecord'] = function () {
            if (isset(static::$_prop['stop'])) {
                return false;
            }
            return static::$nextRecordCount % 2;
        };

        static::$databaseCallback['Query'] = function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                static::$_prop['stop'] = true;
            }
        };

        \SWIFT::Set('loopcontrol', true);

        $_POST['staffid'] = 1;
        $_POST['emailqueueid'] = 1;
        $_POST['autouserid'] = 1;
        $_POST['type'] = 'phone';
        $_POST['email'] = 'me2@mail.com';
        $_POST['fullname'] = 'fullname';
        $this->assertTrue($obj->Post(),
            'Returns false');

        unset($_POST['autouserid']);
        $_POST['userid'] = 1;
        $_POST['templategroup'] = 'templategroup';
        $this->assertFalse($obj->Post(),
            'Returns false with invalid email');

        $_POST['templategroup'] = 1;
        $_POST['email'] = 'me@mail.com';
        $this->assertTrue($obj->Post());

        unset($_POST['userid']);
        $_POST['staffid'] = 1;
        $_POST['type'] = 'ticket';
        $_POST['ownerstaffid'] = 1;
        $this->assertTrue($obj->Post());

        $this->assertClassNotLoaded($obj, 'Post');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateUserAutoThrowsException()
    {
        $obj = $this->getMocked();
        $this->setExpectedException(\SWIFT_Exception::class);
        $_POST['fullname'] = 'fullname';
        $_POST['email'] = 'test@mail.com';
        $obj->createUserAuto(0, $_POST['fullname'], $_POST['email']);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateUserAutoReturnsInt()
    {
        $obj = $this->getMocked();
        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'averageresponsetimehits' => 1,
            'averageslaresponsetime' => 1,
            'departmentid' => 1,
            'email' => 'email@email.com',
            'emailqueueid' => 1,
            'fullname' => 'fullname',
            'isresolved' => false,
            'iswatched' => false,
            'languageid' => 1,
            'lastpostid' => 1,
            'overduehrs' => 0,
            'priorityid' => 1,
            'queuesignatureid' => 1,
            'registrationrequired' => false,
            'regusergroupid' => 1,
            'replyto' => 'reply-to@email.com',
            'ruletype' => 1,
            'salutation' => 'hello',
            'slaplanid' => 1,
            'slascheduleid' => 1,
            'tgroupid' => 1,
            'ticketautoresponder' => false,
            'ticketid' => 1,
            'ticketpostid' => 1,
            'ticketslaplanid' => 1,
            'ticketstatusid' => 1,
            'tickettypeid' => 1,
            'title' => 'title',
            'totalreplies' => 1,
            'userdesignation' => 1,
            'useremailid' => 1,
            'usergroupid' => 1,
            'userid' => 1,
            'linktype' => 1,
            'linktypeid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;
        $_POST['fullname'] = 'fullname';
        $_POST['email'] = 'test@mail.com';
        $this->assertEquals(1, $obj->createUserAuto(1, $_POST['fullname'], $_POST['email']),
            'Returns ID without errors');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketMock
     */
    private function getMocked()
    {
        $mgr = $this->getMockBuilder('Tickets\Library\API\SWIFT_TicketAPIManager')
            ->disableOriginalConstructor()
            ->getMock();

        $rest = $this->getMockBuilder('SWIFT_RESTServer')
            ->disableOriginalConstructor()
            ->getMock();

        $rest->method('GetVariableContainer')->willReturn(['salt' => 'salt']);
        $rest->method('Get')->willReturnArgument(0);

        return $this->getMockObject('Tickets\Api\Controller_TicketMock', [
            'RESTServer' => $rest,
            'TicketAPIManager' => $mgr,
        ]);
    }
}

class Controller_TicketMock extends Controller_Ticket
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

