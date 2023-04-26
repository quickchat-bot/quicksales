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

namespace Tickets\Library\EmailParser;

use LoaderMock;
use SWIFT;
use SWIFT_Exception;
use Base\Models\Template\SWIFT_TemplateGroup;
use Base\Library\Notification\SWIFT_NotificationManager;
use Tickets\Models\Ticket\SWIFT_Ticket;

/**
 * Class TicketEmailParserTest
 * @group tickets
 * @group tickets-lib3
 */
class TicketEmailParserTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    public function setUp()
    {
        parent::setUp();

        static::$_prop = [];
    }

    public function testConstructorReturnsClassInstance()
    {

        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Library\EmailParser\SWIFT_TicketEmailParser', $obj);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetMailParserThrowsException()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'SetMailParser');

        $parser = $this->getMockBuilder('Parser\Library\MailParser\SWIFT_MailParser')
            ->disableOriginalConstructor()
            ->getMock();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, $parser);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetMailParserReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'SetMailParser');

        $parser = $this->getMockBuilder('Parser\Library\MailParser\SWIFT_MailParser')
            ->disableOriginalConstructor()
            ->getMock();
        $parser->method('GetIsClassLoaded')->willReturn(true);
        $this->assertTrue($method->invoke($obj, $parser));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $parser);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetParserRuleManagerThrowsException()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'SetParserRuleManager');

        $parser = $this->getMockBuilder('Parser\Library\Rule\SWIFT_ParserRuleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, $parser);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetParserRuleManagerReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'SetParserRuleManager');

        $parser = $this->getMockBuilder('Parser\Library\Rule\SWIFT_ParserRuleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $parser->method('GetIsClassLoaded')->willReturn(true);
        $this->assertTrue($method->invoke($obj, $parser));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $parser);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetEmailQueueThrowsException()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'SetEmailQueue');

        $parser = $this->getMockBuilder('Parser\Models\EmailQueue\SWIFT_EmailQueue')
            ->disableOriginalConstructor()
            ->getMock();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, $parser);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetEmailQueueReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'SetEmailQueue');

        $parser = $this->getMockBuilder('Parser\Models\EmailQueue\SWIFT_EmailQueue')
            ->disableOriginalConstructor()
            ->getMock();
        $parser->method('GetIsClassLoaded')->willReturn(true);
        $this->assertTrue($method->invoke($obj, $parser));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $parser);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetMailParserEmailObjectThrowsException()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'SetMailParserEmailObject');

        $parser = $this->getMockBuilder('Parser\Library\MailParser\SWIFT_MailParserEmail')
            ->disableOriginalConstructor()
            ->getMock();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, $parser);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetMailParserEmailObjectReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'SetMailParserEmailObject');

        $parser = $this->getMockBuilder('Parser\Library\MailParser\SWIFT_MailParserEmail')
            ->disableOriginalConstructor()
            ->getMock();
        $parser->method('GetIsClassLoaded')->willReturn(true);
        $this->assertTrue($method->invoke($obj, $parser));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $parser);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetMailParserEmailObjectReturnsObject()
    {
        $obj = $this->getMocked();

        $this->assertInstanceOf('Parser\Library\MailParser\SWIFT_MailParserEmail', $obj->GetMailParserEmailObject());

        $this->assertClassNotLoaded($obj, 'GetMailParserEmailObject');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetEmailQueueReturnsObject()
    {
        $obj = $this->getMocked();

        $this->assertInstanceOf('Parser\Models\EmailQueue\SWIFT_EmailQueue', $obj->GetEmailQueue());

        $this->assertClassNotLoaded($obj, 'GetEmailQueue');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetParserRuleManagerReturnsObject()
    {
        $obj = $this->getMocked();

        $this->assertInstanceOf('Parser\Library\Rule\SWIFT_ParserRuleManager', $obj->GetParserRuleManager());

        $this->assertClassNotLoaded($obj, 'GetParserRuleManager');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetMailParserReturnsObject()
    {
        $obj = $this->getMocked();

        $this->assertInstanceOf('Parser\Library\MailParser\SWIFT_MailParser', $obj->GetMailParser());

        $this->assertClassNotLoaded($obj, 'GetMailParser');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function setRunTestInSeparateProcessReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Process(),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'Process');
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessRecipientsReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'ProcessRecipients');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetProperty')->willReturnCallback(function ($x) {
            if ($x === 'email') {
                return 'me@mail.com';
            }

            return 1;
        });

        $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);
        static::$databaseCallback['Query'] = function ($x) {
            if (false !== strpos($x, 'swticketrecipients')) {
                static::$_prop['recipientid'] = 1;
            }
            if (false !== strpos($x, 'ticketemailid')) {
                static::$_prop['emailid'] = 1;
            }
        };
        static::$databaseCallback['NextRecord'] = function () {
            if (isset(static::$_prop['recipientid'])) {
                static::$_prop['recipientid']++;

                \SWIFT::GetInstance()->Database->Record = [
                    'recipienttype' => static::$_prop['recipientid'],
                    'ticketemailid' => static::$_prop['recipientid'],
                    'ticketrecipientid' => static::$_prop['recipientid'],
                ];

                if (static::$_prop['recipientid'] >= 4) {
                    unset(static::$_prop['recipientid']);
                    return false;
                }

                return true;
            }

            if (isset(static::$_prop['emailid'])) {
                static::$_prop['emailid']++;

                \SWIFT::GetInstance()->Database->Record = [
                    'ticketemailid' => static::$_prop['emailid'],
                    'email' => 'me' . static::$_prop['emailid'] . '@mail.com',
                ];

                if (static::$_prop['emailid'] >= 4) {
                    unset(static::$_prop['emailid']);
                    return false;
                }

                return true;
            }

            return static::$nextRecordCount % 2;
        };

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x === 'queuecache') {
                return [
                    'pointer' => [1 => 1],
                ];
            }

            return [
                1 => [1 => [1]],
            ];
        };

        SWIFT::Set('loopcontrol', false);

        $this->assertNotEmpty($method->invoke($obj, $ticket));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $ticket);
    }

    /**
     * @throws \ReflectionException
     * @throws SWIFT_Exception
     */
    public function testParseTicketIdReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'ParseTicketID');

        static::$_prop['GetToEmailSuffix'] = 't.pwd.2';

        $mailParserEmail = $obj->GetMailParserEmailObject();
        $this->assertNotEmpty($method->invoke($obj, 'Subject', $mailParserEmail));

        static::$_prop['GetToEmailSuffix'] = 'a.pwd.2';
        $this->assertNotEmpty($method->invoke($obj, 'Subject', $mailParserEmail));

        $this->assertNotEmpty($method->invoke($obj, '[KAYAKO #ABC-123-12345]: Subject', $mailParserEmail));
        $this->assertNotEmpty($method->invoke($obj, '[#ABC-123-12345]: Subject', $mailParserEmail));
        $this->assertNotEmpty($method->invoke($obj, '[KAYAKO #ABC-12345]: Subject', $mailParserEmail));
        $this->assertNotEmpty($method->invoke($obj, '[#ABC-12345]: Subject', $mailParserEmail));
        $this->assertNotEmpty($method->invoke($obj, '[ABC-12345]: Subject', $mailParserEmail));
        $this->assertNotEmpty($method->invoke($obj, '[KAYAKO #12345]: Subject', $mailParserEmail));
        $this->assertNotEmpty($method->invoke($obj, '[#3412]: Subject', $mailParserEmail));
        $this->assertNotEmpty($method->invoke($obj, '[PREFIX #3412]: Subject', $mailParserEmail));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 'subject', $mailParserEmail);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCleanupSubjectReturnsString()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'CleanupSubject');

        $this->assertNotEmpty($method->invoke($obj, 'subject'));

        $this->assertNotEmpty($method->invoke($obj, '[a]:b'));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 'subject');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessThrowsException()
    {
        $obj = $this->getMocked();

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
            'linktypeid' => 1,
            'staffid' => 1,
            'tickethash' => 'hash',
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, 'ticketmaskid = ')) {
                $arr['ticketid'] = 0;
            }

            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$_prop['ParserEmailProperty'] = [
            'ignoreemail' => 1,
        ];
        $this->setExpectedException('SWIFT_Exception');
        static::$_prop['GetSubject'] = '[KAYAKO !12345]: Subject';
        $obj->Process();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => &static::$_prop['isresolved'],
            'tgroupid' => 1,
            'userid' => 1,
            'linktypeid' => 1,
            'tickethash' => 'hash',
            'isenabled' => &static::$_prop['isenabled'],
            'isvalidated' => '1',

            'fullname' => 1,
            'ticketpostid' => 1,
            'userdesignation' => 1,
            'salutation' => 1,
            'ticketslaplanid' => 1,
            'slaplanid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
            'firstresponsetime' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'ticketmaskid' => 0,
            'subject' => 'subject',
            'emailqueueid' => '0',
            'trasholddepartmentid' => 0,
            'ticketstatusid' => 1,

            'tickettimetrackid' => 1,
            'timeworked' => 0,
            'timebilled' => 0,

            'dateline' => 0,
            'languageid' => 1,
            'languageengineid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'replyto' => '',
            'useremailid' => 1,
            'isthirdparty' => 0,
            'creator' => 0,
            'isprivate' => 0,
            'contents' => '<html>contents</html>',
            'ishtml' => 1,
            'email' => 'me@mail.com',

            'title' => 'title',
            'overduehrs' => 1,
            'slascheduleid' => 1,
            'slaexpirytimeline' => 1,
            'staffid' => 1,
            'contentid' => 1,
            'regusergroupid' => 1,
            'userorganizationid' => 1,
            'organizationname' => 1,
            'guestusergroupid' => 1,
            'hasattachments' => 1,

            'attachmentid' => 1,
            'filename' => 'file.txt',
            'filesize' => 1,
            'filetype' => 'file',
            'storefilename' => 'file.txt',
            'attachmenttype' => 0,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, 'ticketmaskid = ')) {
                $arr['ticketid'] = 0;
            }

            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$_prop['isresolved'] = 0;

        static::$_prop['GetToEmailSuffix'] = 't.pwd.2';
        $this->assertFalse($obj->Process(),
            'Returns true without errors');

        static::$_prop['GetSubject'] = '[KAYAKO #ABC-123-12345]: Subject';
        static::$_prop['EmailQueueProperty'] = [
            'tgroupid' => 0,
        ];
        $this->assertFalse($obj->Process());

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => 1,
                    'departmentapp' => 'tickets',
                    'staffid' => 1,
                    'tgroupid' => 1,
                    'ticketpostid' => 1,
                    'slaid' => 1,
                    'slaplanid' => 1,
                    'fullname' => 1,
                    'email' => 'me@mail.com',
                    'extension' => 'txt',
                    'txt' => [
                        'acceptsupportcenter' => 1,
                        'acceptmailparser' => 1,
                        'maxsize' => 1,
                    ],
                    'acceptmailparser' => 1,
                    'maxsize' => 1,
                    'languageid' => '1',
                    'regusergroupid' => '1',
                    'languagecode' => 'en-us',
                ],
            ];
        };

        static::$_prop['isenabled'] = 1;
        static::$_prop['GetSubject'] = '[KAYAKO ~12345]: Subject';
        static::$_prop['ParserEmailProperty'] = [
            'noticketreply' => 0,
            'isprivate' => 0,
        ];
        $this->assertTrue($obj->Process());

        \SWIFT::Set('loopcontrol', true);

        static::$_prop['GetSubject'] = '[KAYAKO !12345]: Subject';
        $this->assertTrue($obj->Process(), 'Returns true with staffreply');

        static::$_prop['isresolved'] = 1;
        static::$_prop['ParserEmailProperty'] = [
            'noticketreply' => 0,
            'isprivate' => 0,
            'noautoresponder' => 0,
        ];
        $this->assertTrue($obj->Process(), 'Returns true with staffreply');

        $this->assertClassNotLoaded($obj, 'Process');
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetTicketIdContainerReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'getTicketIdContainer');

        unset(static::$_prop['GetSubject'], static::$_prop['GetToEmailSuffix']);

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
        $this->assertNotEmpty($method->invoke($obj));
    }

    /**
     * @throws \ReflectionException
     */
    public function testCheckMergedStatusReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'checkMergedStatus');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'userid' => 1,
        ];
        static::$_prop['c'] = 2;
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, 'oldticketmaskid') ||
                false !== strpos($x, 'oldticketid')) {
                static::$_prop['c']--;
                return ['ticketid' => static::$_prop['c']];
            }

            if (isset(static::$_prop['failticket'])) {
                $arr['ticketid'] = 0;
            }

            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn(true);

        $this->assertNotEmpty($method->invoke($obj, $ticket, []));
        $this->assertNotEmpty($method->invoke($obj, false, ['mask' => 1]));

        static::$_prop['c'] = 2;
        static::$_prop['failticket'] = 1;
        $this->assertNotEmpty($method->invoke($obj, false, ['id' => 1]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testCheckStaffReplyThrowsException()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'checkStaffReply');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn(true);

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'staffid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $_ticketIDContainer = [
            'mask' => 1,
            'id' => 1,
            'isalert' => true,
            'isthirdparty' => false,
        ];

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x === 't_pstaffreply') {
                return 0;
            }
            return 1;
        };

        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, $ticket, $_ticketIDContainer, 1, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCheckStaffReplyReturnsArrayForCustomerEmail()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'checkStaffReply');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn(true);

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

        $_ticketIDContainer = [
            'mask' => 1,
            'id' => 1,
            'isalert' => true,
            'isthirdparty' => false,
        ];

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x === 't_pstaffreply') {
                return 0;
            }
            return 1;
        };
        $this->assertNotEmpty($method->invoke($obj, $ticket, $_ticketIDContainer, 1, false));
    }

    /**
     * @throws \ReflectionException
     */
    public function testCheckStaffReplyReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'checkStaffReply');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'staffid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $_ticketIDContainer = [
            'mask' => 1,
            'id' => 1,
            'isalert' => true,
            'isthirdparty' => false,
        ];
        $this->assertNotEmpty($method->invoke($obj, $ticket, $_ticketIDContainer, 1, 1));
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessUserReplyThrowsException()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processUserReply');

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
            'linktypeid' => 1,
            'isenabled' => 0,
            'fullname' => 1,
            'ticketpostid' => 1,
            'slaplanid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $_ticketIDContainer = [
            'mask' => 1,
            'id' => 1,
            'isalert' => true,
            'isthirdparty' => false,
        ];

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();

        $tgroup = $this->getMockBuilder('SWIFT_TemplateGroup')
            ->disableOriginalConstructor()
            ->getMock();

        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, $ticket, $tgroup, $_ticketIDContainer, DATENOW);
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessUserReplyThrowsIgnoreEmailException()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processUserReply');

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
            'linktypeid' => 0,
            'isenabled' => 0,
            'fullname' => 1,
            'ticketpostid' => 1,
            'slaplanid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $_ticketIDContainer = [
            'mask' => 1,
            'id' => 1,
            'isalert' => true,
            'isthirdparty' => false,
        ];

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();

        $tgroup = $this->getMockBuilder('SWIFT_TemplateGroup')
            ->disableOriginalConstructor()
            ->getMock();

        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, $ticket, $tgroup, $_ticketIDContainer, DATENOW);
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessUserReplyReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processUserReply');

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
            'linktypeid' => 1,
            'isenabled' => 1,
            'fullname' => 1,
            'ticketpostid' => 1,
            'slaplanid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
            'ticketrecipientid' => &static::$_prop['ticketrecipientid'],
            'ticketemailid' => &static::$_prop['ticketrecipientid'],
            'recipienttype' => &static::$_prop['ticketrecipientid'],
            'email' => 'me@mail.com',
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$_prop['ticketrecipientid'] = 1;

        $_ticketIDContainer = [
            'mask' => 1,
            'id' => 1,
            'isalert' => true,
            'isthirdparty' => false,
        ];

        $ticket = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetTicketID')->willReturn(1);
        $ticket->method('GetProperty')->willReturn(1);

        $noti = $this->getMockBuilder(SWIFT_NotificationManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->NotificationManager = $noti;

        $tgroup = $this->getMockBuilder(SWIFT_TemplateGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tgroup->method('GetIsClassLoaded')->willReturn(true);
        $tgroup->method('GetProperty')->willReturn(true);

        $this->assertNotNull($method->invoke($obj, $ticket, $tgroup, $_ticketIDContainer, DATENOW));

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x === 't_autorecip') {
                return 0;
            }

            return 1;
        };

        $this->setNextRecordNoLimit();

        $this->assertNotNull($method->invoke($obj, $ticket, $tgroup, $_ticketIDContainer, DATENOW));

        static::$_prop['ticketrecipientid'] = 3;
        $this->assertNotNull($method->invoke($obj, $ticket, $tgroup, $_ticketIDContainer, DATENOW));
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessStaffReplyReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processStaffReply');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetProperty')->willReturn(1);
        $ticket->method('GetTicketID')->willReturn(1);

        $noti = $this->getMockBuilder('Base\Library\Notification\SWIFT_NotificationManager')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->NotificationManager = $noti;

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'slaplanid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
            'ticketpostid' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $staffCache = [
            1 => [
                1 => [1 => 1],
                'staffid' => 1,
                'ticketpostid' => 1,
                'slaid' => 1,
                'slaplanid' => 1,
                'fullname' => 1,
                'email' => 'me@mail.com',
            ],
        ];

        $this->assertNotEmpty($method->invoke($obj, $staffCache, 1, $ticket, DATENOW));
    }

    /**
     * @throws \ReflectionException
     */
    public function testDoCreateUserReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'doCreateUser');

        $user = $this->getMockBuilder('Base\Models\User\SWIFT_User')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('GetIsClassLoaded')->willReturn(true);

        $this->assertTrue($method->invoke($obj, $user));

        $user->method('GetProperty')->willReturn('0');
        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, $user);
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessUserNotRegisteredReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processUserNotRegistered');

        $user = $this->getMockBuilder('Base\Models\User\SWIFT_User')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('GetIsClassLoaded')->willReturn(true);

        $this->assertTrue($method->invoke($obj, $user));

        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, false);
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessUserNotValidatedThrowsException()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processUserNotValidated');

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x === 'u_enablesveri') {
                return 0;
            }

            return 1;
        };

        $user = $this->getMockBuilder('Base\Models\User\SWIFT_User')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('GetIsClassLoaded')->willReturn(true);
        $user->method('GetProperty')->willReturnCallback(function ($x) {
            if ($x === 'isvalidated') {
                return 0;
            }
            return 1;
        });

        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, $user);
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessUserNotValidatedReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processUserNotValidated');

        $user = $this->getMockBuilder('Base\Models\User\SWIFT_User')
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('GetIsClassLoaded')->willReturn(true);

        $this->assertTrue($method->invoke($obj, $user));

        $user->method('GetProperty')->willReturnCallback(function ($x) {
            if ($x === 'isvalidated') {
                return 0;
            }
            return 1;
        });

        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, $user);
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessNewTicketReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processNewTicket');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'userid' => 1,
            'userorganizationid' => 0,
            'duetime' => 1,
            'isresolved' => &static::$_prop['isresolved'],
            'ticketviewid' => 1,
            'resolutionduedateline' => 0,
            'hasdraft' => 0,
            'userdesignation' => '',
            'salutation' => '',
            'replyto' => '',
            'fullname' => 'fullname',
            'email' => 'me@mail.com',
            'emailqueueid' => 0,
            'ticketmaskid' => 0,
            'tgroupid' => 1,
            'ticketslaplanid' => 0,
            'slaplanid' => 1,
            'slaid' => 1,
            'firstresponsetime' => 0,
            'ticketpostid' => 1,
            'averageresponsetimehits' => 0,
            'dateline' => 0,
            'totalreplies' => 0,
            'searchstoreid' => 1,
            'trasholddepartmentid' => 0,
            'ticketstatusid' => 0,
            'ownerstaffid' => 1,
            'priorityid' => 1,
            'tickettypeid' => 1,
            'flagtype' => 1,
            'creator' => 1,
            'subject' => 1,
            'ruletype' => 1,
            'wasreopened' => 0,
            'slaexpirytimeline' => 0,
            'bayescategoryid' => 0,
            'charset' => 'UTF-8',
            'linktypeid' => 1,
            'lastactivity' => 0,

            'slarulecriteriaid' => 1,
            'slascheduleid' => 1,
            'title' => 'title',
            'name' => 'name',
            'ruleop' => 1,
            'rulematchtype' => 1,
            'rulematch' => 1,

            'overduehrs' => 1,
            'resolutionduehrs' => 1,

            'lastreplier' => 0,
            'isenabled' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => 1,
                    'departmentapp' => 'tickets',
                ],
            ];
        };

        static::$_prop['isresolved'] = 0;
        $this->assertNotEmpty($method->invoke($obj, 1, 1, 1, 1, 1, 1, DATENOW, false));
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessAttachmentsReturnsZeroForUnknownFile()
    {
        $obj = $this->getMockedWithAttachmentWithoutExtension();
        $method = $this->getMethod($obj, 'processAttachments');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetTicketID')->willReturn(1);

        $post = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_TicketPost')
            ->disableOriginalConstructor()
            ->getMock();
        $post->method('GetIsClassLoaded')->willReturn(true);
        $post->method('GetTicketPostID')->willReturn(1);

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => 1,
                    'extension' => 'pdf',
                    'pdf' => [
                        'acceptsupportcenter' => 1,
                        'acceptmailparser' => 1,
                        'maxsize' => 1,
                    ],
                    'acceptmailparser' => 1,
                    'maxsize' => 1,
                ],
            ];
        };

        $SWIFT = SWIFT::GetInstance();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'linktypeid' => 1,
            'contentid' => 1,

            'attachmentid' => 1,
            'filename' => 'file_bin',
            'filesize' => 1,
            'filetype' => 'file',
            'storefilename' => 'file_bin',
            'attachmenttype' => 0,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertEquals(0, $method->invoke($obj, $SWIFT, $ticket, $post, [1]));

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x === 'tickets_resattachments') {
                return 0;
            }
            return 1;
        };

        $this->assertEquals(2, $method->invoke($obj, $SWIFT, $ticket, $post, [1]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessAttachmentsReturnsCount()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processAttachments');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetTicketID')->willReturn(1);

        $post = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_TicketPost')
            ->disableOriginalConstructor()
            ->getMock();
        $post->method('GetIsClassLoaded')->willReturn(true);
        $post->method('GetTicketPostID')->willReturn(1);

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => 1,
                    'extension' => 'txt',
                    'txt' => [
                        'acceptsupportcenter' => 1,
                        'acceptmailparser' => 1,
                        'maxsize' => 1,
                    ],
                    'acceptmailparser' => 1,
                    'maxsize' => 1,
                ],
            ];
        };

        $SWIFT = SWIFT::GetInstance();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'linktypeid' => 1,
            'contentid' => 1,

            'attachmentid' => 1,
            'filename' => 'file.txt',
            'filesize' => 1,
            'filetype' => 'file',
            'storefilename' => 'file.txt',
            'attachmenttype' => 0,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertEquals(1, $method->invoke($obj, $SWIFT, $ticket, $post, [1]));
    }

    public function providerProcessAttachmentsExisting()
    {
        return [
            ['<p><img src="cid:testing" /></p>', '<p><img src="cid:testing2" /></p>'],
            ['<p><img src=" cid:testing" /></p>', '<p><img src="cid:testing2" /></p>'],
            ['<p><img src="cid:testing " /></p>', '<p><img src="cid:testing2" /></p>'],
            ['<p><img src=" cid:testing " /></p>', '<p><img src="cid:testing2" /></p>'],
            ['<p><img src="cid :testing" /></p>', '<p><img src="cid:testing2" /></p>'],
            ['<p><img src="cid: testing" /></p>', '<p><img src="cid:testing2" /></p>'],
            ['<p><img src="    cid  :      testing  " /></p>', '<p><img src="cid:testing2" /></p>'],
        ];
    }

    /**
     * @dataProvider providerProcessAttachmentsExisting
     * @throws \ReflectionException
     */
    public function testProcessAttachmentsExisting($input, $expected)
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processAttachments');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetTicketID')->willReturn(1);

        $post = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_TicketPost')
            ->disableOriginalConstructor()
            ->setMethods(['GetIsClassLoaded', 'GetTicketPostID'])
            ->getMock();
        $post->method('GetIsClassLoaded')->willReturn(true);
        $post->method('GetTicketPostID')->willReturn(1);
        $post->UpdatePool('contents', $input);

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => 1,
                    'extension' => 'txt',
                    'txt' => [
                        'acceptsupportcenter' => 1,
                        'acceptmailparser' => 1,
                        'maxsize' => 1,
                    ],
                    'acceptmailparser' => 1,
                    'maxsize' => 1,
                ],
            ];
        };

        $SWIFT = SWIFT::GetInstance();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'linktypeid' => 1,
            'contentid' => 'testing2',

            'attachmentid' => 1,
            'filename' => 'test.jpg',
            'filesize' => 1,
            'filetype' => 'image/jpeg',
            'storefilename' => 'test.jpg',
            'attachmenttype' => 2,
            'sha1' => '356a192b7913b04c54574d18c28d46e6395428ab'
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertEquals(1, $method->invoke($obj, $SWIFT, $ticket, $post, [1]));
        $this->assertEquals($expected, $post->Get('contents'));
    }

    public static function exProvider()
    {
        return [
            [1, 1, 1, 0],
            [1, 1, 0, 1],
            [1, 0, 1, 1],
            [0, 1, 1, 1],
        ];
    }

    /**
     * @param $ticketstatusid
     * @param $priorityid
     * @param $tickettypeid
     * @param $departmentid
     * @throws \ReflectionException
     * @dataProvider exProvider
     */
    public function testProcessRepliesThrowsDepartmentException(
        $ticketstatusid,
        $priorityid,
        $tickettypeid,
        $departmentid
    ) {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processReplies');

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
            'linktypeid' => 1,
            'isenabled' => 1,
            'isvalidated' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $mgr = $this->getMockBuilder('SWIFT_NotificationManager')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->setMethods(['GetIsClassLoaded', 'GetTicketID', 'GetProperty'])
            ->getMock();
        $ticket->NotificationManager = $mgr;
        $ticket->method('GetProperty')->willReturn(1);
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetTicketID')->willReturn(1);

        $tgroup = $this->getMockBuilder('SWIFT_TemplateGroup')
            ->disableOriginalConstructor()
            ->setMethods(['GetIsClassLoaded', 'GetProperty'])
            ->getMock();
        $tgroup->method('GetIsClassLoaded')->willReturn(true);
        $tgroup->method('GetProperty')->willReturn(1);

        static::$_prop['EmailQueueProperty'] = [
            'ticketstatusid' => $ticketstatusid,
            'priorityid' => $priorityid,
            'tickettypeid' => $tickettypeid,
            'departmentid' => $departmentid,
        ];
        $this->setExpectedException('SWIFT_Exception');
        $method->invoke($obj, $ticket, true, true, $tgroup, [1], DATENOW, 1, true, true);
    }

    /**
     * @throws \ReflectionException
     */
    public function testProcessRepliesReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'processReplies');

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
            'linktypeid' => 1,
            'isenabled' => 1,
            'fullname' => 1,
            'ticketpostid' => 1,
            'isvalidated' => 1,
            'userdesignation' => 1,
            'salutation' => 1,
            'ticketslaplanid' => 1,
            'slaplanid' => 1,
            '_criteria' => 1,
            'ruletype' => 1,
            'firstresponsetime' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'ticketmaskid' => 0,
            'subject' => 'subject',
            'emailqueueid' => '0',
            'trasholddepartmentid' => 0,
            'ticketstatusid' => 1,

            'tickettimetrackid' => 1,
            'timeworked' => 0,
            'timebilled' => 0,

            'dateline' => 0,
            'languageid' => 1,
            'languageengineid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'replyto' => '',
            'useremailid' => 1,
            'isthirdparty' => 0,
            'creator' => 0,
            'isprivate' => 0,
            'contents' => '<html>contents</html>',
            'ishtml' => 1,
            'email' => 'me@mail.com',

            'staffid' => 1,
            'userorganizationid' => 1,
            'organizationname' => 'test org',
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => 1,
                    'departmentapp' => 'tickets',
                    'staffid' => 1,
                    'ticketpostid' => 1,
                    'slaid' => 1,
                    'slaplanid' => 1,
                    'fullname' => 1,
                    'email' => 'me@mail.com',
                ],
            ];
        };

        $mgr = $this->getMockBuilder('SWIFT_NotificationManager')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['SetEvent', 'SetPrivate'])
            ->getMock();
        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->NotificationManager = $mgr;
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetTicketID')->willReturn(1);

        $tgroup = $this->getMockBuilder('Base\Models\Template\SWIFT_TemplateGroup')
            ->disableOriginalConstructor()
            ->getMock();
        $tgroup->method('GetIsClassLoaded')->willReturn(true);
        $tgroup->method('GetProperty')->willReturn(1);

        $this->assertNotEmpty($method->invoke($obj, $ticket, false, false, $tgroup, [1], DATENOW, 1, true, true));

        $this->assertNotEmpty($method->invoke($obj, $ticket, true, false, $tgroup, [1], DATENOW, 1, true, true));

        $this->assertNotEmpty($method->invoke($obj, $ticket, true, true, $tgroup, [1], DATENOW, 1, true, true));
    }

    /**
     * @param bool $isLoaded
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketEmailParserMock
     */
    private function getMocked($isLoaded = true)
    {
        $parserEmail = $this->getMockBuilder('Parser\Library\MailParser\SWIFT_MailParserEmail')
            ->disableOriginalConstructor()
            ->getMock();
        $parserEmail->method('GetToEmailSuffix')->willReturnCallback(function () {
            if (isset(static::$_prop['GetToEmailSuffix'])) {
                return static::$_prop['GetToEmailSuffix'];
            }

            return '';
        });

        $parserEmail->method('GetIsClassLoaded')->willReturn($isLoaded);
        $parserEmail->method('GetRecipients')->willReturn([1]);
        $parserEmail->method('GetSubject')->willReturnCallback(function () {
            if (isset(static::$_prop['GetSubject'])) {
                return static::$_prop['GetSubject'];
            }

            return '[] : subject';
        });
        $parserEmail->method('GetFromEmail')->willReturn('me@mail.com');
        $parserEmail->method('GetFromName')->willReturn('from');
        $parserEmail->method('GetFinalContentIsHTML')->willReturn(false);
        $parserEmail->method('GetAttachments')->willReturn([
            [
                'filesize' => 1,
                'size' => 1,
                'data' => 1,
                'filetype' => 'file',
                'contenttype' => 'text/plain',
                'storefilename' => 'file.txt',
                'attachmenttype' => 0,
	            'contentid' => 'testing'
            ],
            [
                'data' => '',
            ],
            [
                'filename' => 'file2.txt',
                'extension' => 'txt',
                'filesize' => 1024,
                'size' => 1024,
                'data' => 1,
                'filetype' => 'file2',
                'contenttype' => 'text/plain',
                'storefilename' => 'file2.txt',
                'attachmenttype' => 0,
	            'contentid' => 'testing_2'
            ],
        ]);
        $parserEmail->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop['ParserEmailProperty'])) {
                foreach (static::$_prop['ParserEmailProperty'] as $k => $v) {
                    if ($k === $x) {
                        return $v;
                    }
                }
            }

            if ($x === 'ignoreemail') {
                return 0;
            }

            return 1;
        });

        $queue = $this->getMockBuilder('Parser\Models\EmailQueue\SWIFT_EmailQueue')
            ->disableOriginalConstructor()
            ->getMock();
        $queue->method('GetIsClassLoaded')->willReturn($isLoaded);
        $queue->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop['EmailQueueProperty'])) {
                foreach (static::$_prop['EmailQueueProperty'] as $k => $v) {
                    if ($k === $x) {
                        return $v;
                    }
                }
            }

            return 1;
        });
        $queue->method('GetEmailQueueID')->willReturn(1);

        $parser = $this->getMockBuilder('Parser\Library\MailParser\SWIFT_MailParser')
            ->disableOriginalConstructor()
            ->getMock();
        $parser->method('GetIsClassLoaded')->willReturn($isLoaded);

        $ruler = $this->getMockBuilder('Parser\Library\Rule\SWIFT_ParserRuleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $ruler->method('GetIsClassLoaded')->willReturn($isLoaded);

        $mail = $this->getMockBuilder('SWIFT_Mail')
            ->disableOriginalConstructor()
            ->getMock();

        $tpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();
        $tpl->method('Get')->willReturn(1);

        return $this->getMockObject('Tickets\Library\EmailParser\SWIFT_TicketEmailParserMock',
            [
                'Mail' => $mail,
                'Template' => $tpl,
                'MailParserEmail' => $parserEmail,
                'EmailQueue' => $queue,
                'MailParser' => $parser,
                'ParserRuleManager' => $ruler,
            ]);
    }

    /**
     * @param bool $isLoaded
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketEmailParserMock
     */
    private function getMockedWithAttachmentWithoutExtension($isLoaded = true)
    {
        $parserEmail = $this->getMockBuilder('Parser\Library\MailParser\SWIFT_MailParserEmail')
            ->disableOriginalConstructor()
            ->getMock();
        $parserEmail->method('GetToEmailSuffix')->willReturnCallback(function () {
            if (isset(static::$_prop['GetToEmailSuffix'])) {
                return static::$_prop['GetToEmailSuffix'];
            }

            return '';
        });

        $parserEmail->method('GetIsClassLoaded')->willReturn($isLoaded);
        $parserEmail->method('GetRecipients')->willReturn([1]);
        $parserEmail->method('GetSubject')->willReturnCallback(function () {
            if (isset(static::$_prop['GetSubject'])) {
                return static::$_prop['GetSubject'];
            }

            return '[] : subject';
        });
        $parserEmail->method('GetFromEmail')->willReturn('me@mail.com');
        $parserEmail->method('GetFromName')->willReturn('from');
        $parserEmail->method('GetFinalContentIsHTML')->willReturn(false);
        $parserEmail->method('GetAttachments')->willReturn([
            [
                'filesize' => 1,
                'size' => 1,
                'data' => 1,
                'filetype' => 'file',
                'contenttype' => 'text/plain',
                'storefilename' => 'file',
                'attachmenttype' => 0,
                'contentid' => 'testing'
            ],
            [
                'data' => '',
            ],
            [
                'filename' => 'file2',
                'extension' => 'txt',
                'filesize' => 1024,
                'size' => 1024,
                'data' => 1,
                'filetype' => 'file2',
                'contenttype' => 'text/plain',
                'storefilename' => 'file2',
                'attachmenttype' => 0,
                'contentid' => 'testing_2'
            ],
        ]);
        $parserEmail->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop['ParserEmailProperty'])) {
                foreach (static::$_prop['ParserEmailProperty'] as $k => $v) {
                    if ($k === $x) {
                        return $v;
                    }
                }
            }

            if ($x === 'ignoreemail') {
                return 0;
            }

            return 1;
        });

        $queue = $this->getMockBuilder('Parser\Models\EmailQueue\SWIFT_EmailQueue')
            ->disableOriginalConstructor()
            ->getMock();
        $queue->method('GetIsClassLoaded')->willReturn($isLoaded);
        $queue->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop['EmailQueueProperty'])) {
                foreach (static::$_prop['EmailQueueProperty'] as $k => $v) {
                    if ($k === $x) {
                        return $v;
                    }
                }
            }

            return 1;
        });
        $queue->method('GetEmailQueueID')->willReturn(1);

        $parser = $this->getMockBuilder('Parser\Library\MailParser\SWIFT_MailParser')
            ->disableOriginalConstructor()
            ->getMock();
        $parser->method('GetIsClassLoaded')->willReturn($isLoaded);

        $ruler = $this->getMockBuilder('Parser\Library\Rule\SWIFT_ParserRuleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $ruler->method('GetIsClassLoaded')->willReturn($isLoaded);

        $mail = $this->getMockBuilder('SWIFT_Mail')
            ->disableOriginalConstructor()
            ->getMock();

        $tpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();
        $tpl->method('Get')->willReturn(1);

        return $this->getMockObject('Tickets\Library\EmailParser\SWIFT_TicketEmailParserMock',
            [
                'Mail' => $mail,
                'Template' => $tpl,
                'MailParserEmail' => $parserEmail,
                'EmailQueue' => $queue,
                'MailParser' => $parser,
                'ParserRuleManager' => $ruler,
            ]);
    }
}

class SWIFT_TicketEmailParserMock extends SWIFT_TicketEmailParser
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct($this->MailParserEmail, $this->EmailQueue,
            $this->MailParser, $this->ParserRuleManager);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

