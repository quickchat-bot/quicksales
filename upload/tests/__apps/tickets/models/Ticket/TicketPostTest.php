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

namespace Tickets\Models\Ticket;

use Base\Library\Notification\SWIFT_NotificationManager;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;
use LoaderMock;
use SWIFT_Exception;

/**
* Class TicketPostTest
* @group tickets
*/
class TicketPostTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $this->getMockServices();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\SWIFT_Database $mockDb */
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'ticketpostid' => 1,
        ]);
        $data = new \SWIFT_DataStore([
            'ticketpostid' => 1,
        ]);
        $obj = $this->getMockObject('Tickets\Models\Ticket\SWIFT_TicketPostMock', [
            'Data' => $data,
        ]);
        $this->assertInstanceOf('Tickets\Models\Ticket\SWIFT_TicketPost', $obj);
    }

    public function getDisplayContentsProvider()
    {
        return [
            ["<iframe src=\"data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==\"></iframe>", false, ''],
            ["<p>&lt;iframe src=\"data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==\"&gt;&lt;/iframe&gt;</p>", true, '&lt;p&gt;&lt;iframe src=&quot;data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==&quot;&gt;&lt;/iframe&gt;&lt;/p&gt;'],
            ["<div>Text</div>", true, '&lt;div&gt;Text&lt;/div&gt;'],
            ["&lt;div&gt;Text&lt;/div&gt;", false, '<div>Text</div>'],
            ["<p>&lt;iframe src=\"data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==\"&gt;&lt;/iframe&gt;</p>", false, ''],
            ["<div>Text</div>", false, '<div>Text</div>'],
            ["&lt;div&gt;Text&lt;/div&gt;", false, '<div>Text</div>'],
            ["<embed>Text</embed>", false, 'Text'],
            ["&lt;embed&gt;Text&lt;/embed&gt;", false, 'Text'],
            ["this
is plain
text", false, 'this<br />
is plain<br />
text'],
        ];
    }

    /**
     * @dataProvider getDisplayContentsProvider
     * @param $_contents
     * @param $_parseEntities
     * @param $expected
     * @throws SWIFT_Exception
     */
    public function testGetDisplayContents($_contents, $_parseEntities, $expected)
    {
        $this->getMockServices();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\SWIFT_Database $mockDb */
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'ticketpostid' => 1,
            'ticketid' => 1,
            'iswatched' => 0,
            'hasattachments' => 0,
        ]);
        $data = new \SWIFT_DataStore([
            'ticketpostid' => 1,
            'contents' => $_contents,
            'ishtml' => true,
            'ticketid' => 1,
        ]);

        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();
        $mockEmoji->method('decode')->willReturnArgument(0);
        $mockEmoji->method('encode')->willReturnArgument(0);

        $settings = $this->createMock(\SWIFT_Settings::class);
        $settings->method('GetBool')->willReturn(true);
        $settings->method('Get')->willReturnCallback(function ($x) use ($_parseEntities) {
            if ($x === 't_chtml' && $_parseEntities) {
                return 'entities';
            } else {
                return 'html';
            }
            return 1;
        });
        \SWIFT::GetInstance()->Settings = $settings;

        /** @var SWIFT_TicketPostMock|\PHPUnit_Framework_MockObject_MockObject $obj */
        $obj = $this->getMockObject('Tickets\Models\Ticket\SWIFT_TicketPostMock', [
            'Data' => $data,
            'Emoji' => $mockEmoji,
            'Settings' => $settings,
        ]);

        $this->assertEquals($expected, $obj->GetDisplayContents());
    }

    /**
     * @dataProvider getParsedContentsProvider
     * @param $_contents
     * @param $_settingValue
     * @param $_isContentHTML
     * @param $_overrideAllowableTags
     * @param $expected
     * @throws SWIFT_Exception
     */
    public function testGetParsedContents($_contents, $_settingValue, $_isContentHTML, $_overrideAllowableTags, $expected)
    {
        $settings = $this->createMock(\SWIFT_Settings::class);
        $settings->method('GetBool')
            ->willReturn(true);
        \SWIFT::GetInstance()->Settings = $settings;

        $actual = SWIFT_TicketPost::GetParsedContents($_contents, $_settingValue, $_isContentHTML, $_overrideAllowableTags);
        $this->assertEquals($expected, $actual);
    }

    public function getParsedContentsProvider()
    {
        return [
            ['<p>Some Text<br><b>Line 2</b></p>', 'strip', true, '', '<p>Some Text<br />Line 2</p>'],
            ['<p>Some Text<br><b>Line 2</b></p>', 'entities', true, '', '&lt;p&gt;Some Text&lt;br&gt;&lt;b&gt;Line 2&lt;/b&gt;&lt;/p&gt;'],
            ['<p>Some Text<br><b>Line 2</b></p>', 'html', true, '', '<p>Some Text<br /><b>Line 2</b></p>'],

            ["<iframe src=\"data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==\"></iframe>", "strip", true, '', ''],
            ["<iframe src=\"data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==\"></iframe>", "entities", true, '', '&lt;iframe src=&quot;data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==&quot;&gt;&lt;/iframe&gt;'],
            ["<iframe src=\"data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==\"></iframe>", "html", true, '', ''],
            ["<p>&lt;iframe src=\"data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==\"&gt;&lt;/iframe&gt;</p>", "strip", true, '', ''],
            ["<p>&lt;iframe src=\"data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==\"&gt;&lt;/iframe&gt;</p>", "entities", true, '', '&lt;p&gt;&lt;iframe src=&quot;data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==&quot;&gt;&lt;/iframe&gt;&lt;/p&gt;'],
            ["<p>&lt;iframe src=\"data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==\"&gt;&lt;/iframe&gt;</p>", "html", true, '', ''],

            ["<div>Text</div>", "strip", true, '', 'Text'],
            ["<div>Text</div>", "entities", true, '', '&lt;div&gt;Text&lt;/div&gt;'],
            ["<div>Text</div>", "html", true, '', '<div>Text</div>'],
            ["&lt;div&gt;Text&lt;/div&gt;", "strip", true, '', 'Text'],
            ["&lt;div&gt;Text&lt;/div&gt;", "entities", true, '', '&lt;div&gt;Text&lt;/div&gt;'],
            ["&lt;div&gt;Text&lt;/div&gt;", "html", true, '', '<div>Text</div>'],

            ["<embed>Text</embed>", "strip", true, '', 'Text'],
            ["<embed>Text</embed>", "entities", true, '', '&lt;embed&gt;Text&lt;/embed&gt;'],
            ["<embed>Text</embed>", "html", true, '', 'Text'],
            ["&lt;embed&gt;Text&lt;/embed&gt;", "strip", true, '', 'Text'],
            ["&lt;embed&gt;Text&lt;/embed&gt;", "entities", true, '', '&lt;embed&gt;Text&lt;/embed&gt;'],
            ["&lt;embed&gt;Text&lt;/embed&gt;", "html", true, '', 'Text'],
        ];
    }

    /**
     * @dataProvider htmlProvider
     */
    public function testTicketPostHtmlDetection($contents, $signature, $expected)
    {
        $insertedContent = '';
        $this->getMockServices();
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('Insert_ID')
            ->willReturn(1);
        $mockDb->method('QueryFetch')
            ->willReturn([
                'ticketpostid' => 1,
                'ticketid' => 1
            ]);
        $mockDb->method('AutoExecute')
            ->willReturnCallback(function($table, $params, $mode) use (&$insertedContent) {
                if ($table === TABLE_PREFIX . 'ticketposts' && $mode === 'INSERT') {
                    $insertedContent = $params['contents'];
                }
            });

        $ticket = $this->createMock(SWIFT_Ticket::class);
        $ticket->method('GetIsClassLoaded')
            ->willReturn(true);
        $ticket->method('GetTicketID')
            ->willReturn(1);
        $ticket->method('GetSignature')
            ->willReturnCallback(function($isHtml, $staff) use ($ticket) {
                $method = new \ReflectionMethod(SWIFT_Ticket::class, 'GetSignature');
                return $method->invoke($ticket, $isHtml, $staff);
            });
        $ticket->method('UpdateAverageSLAResponseTime')
            ->willReturn(null);

        $ticket->NotificationManager = $this->createMock(SWIFT_NotificationManager::class);

        $staff = $this->createMock(SWIFT_Staff::class);
        $staff->method('GetIsClassLoaded')
            ->willReturn(true);

        $subject = 'Test HTML Detection';

        $staff->method('GetProperty')
            ->will($this->returnValueMap([
                ['signature', $signature],
                ['fullname', 'Unit Testing'],
                ['email', 'ut@opencart.com.vn']
            ]));

        $actual = SWIFT_TicketPost::CreateStaff($ticket, $staff, SWIFT_Ticket::CREATIONMODE_STAFFAPI, $contents,
            $subject, true);

        $this->assertInstanceOf(SWIFT_TicketPost::class, $actual);
        $this->assertEquals($expected, $insertedContent);
    }

    public function htmlProvider()
    {
        return [
            'Simple lines' => [
                'content' => "Line 1\n",
                'signature' => "Signature\n",
                'expected' => "Line 1\n\n\nSignature\n"
            ],
            'Multiple lines, simple EOL' => [
                'content' => "Line 1\nLine 2\n",
                'signature' => "Signature\n",
                'expected' => "Line 1\nLine 2\n\n\nSignature\n"
            ],
            'Multiple lines, html content and simple signature with br' => [
                'content' => "Line 1<br />Line 2<br />",
                'signature' => "Signature\n",
                'expected' => "Line 1<br />Line 2<br />\n\nSignature<br />\n"
            ],
            'Multiple lines, html content and html signature with <br>' => [
                'content' => "Line 1<br />Line 2<br />",
                'signature' => "Signature<br />",
                'expected' => "Line 1<br />Line 2<br />\n\nSignature<br />"
            ],
            'Multiple lines, html' => [
                'content' => "Line 1<br />Line 2<br />",
                'signature' => "Signature<br />",
                'expected' => "Line 1<br />Line 2<br />\n\nSignature<br />"
            ],
        ];
    }

    public function providerDisplayContentsImage()
    {
        return [
            ['<p><img src="cid:contentid" /></p>', "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n<html><body><p><img src=\"%s/Tickets/Ticket/GetAttachment/1/1\"></p></html>\n"],
            ['<p><img src="cid:nonexistant" /></p>', "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n<html><body><p><img src=\"cid:nonexistant\"></p></html>\n"],
        ];
    }

    /**
     * @dataProvider providerDisplayContentsImage
     * @param $content
     * @param $expected
     * @throws SWIFT_Exception
     */
    public function testDisplayContentsImage($content, $expected)
    {
        $this->getMockServices();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\SWIFT_Database $mockDb */
        $mockDb = $this->mockServices['Database'];
        $mockDb->method('QueryFetch')->willReturn([
            'ticketpostid' => 1,
            'ticketid' => 1,
            'iswatched' => 0,
            'hasattachments' => 1,
        ]);
        $mockDb->expects($this->exactly(2))
            ->method('NextRecord')
            ->willReturnOnConsecutiveCalls([true, false]);
        $mockDb->Record = [
            'attachmentid' => 1,
            'linktype' => 1,
            'linktypeid' => 1,
            'downloaditemid' => 0,
            'ticketid' => 1,
            'filename' => 'test.jpg',
            'filesize' => 1024,
            'filetype' => 'image/jpeg',
            'dateline' => 1587168440,
            'attachmenttype' => 2,
            'storefilename' => 'attach_dcbu7gmjkfd1agmqlk8xngmuopz3wgmy',
            'contentid' => 'contentid',
            'sha1' => '4a40dc0dba463954ca096e8ec7a743a23f3475c2'
        ];

        $data = new \SWIFT_DataStore([
            'ticketpostid' => 1,
            'contents' => $content,
            'ishtml' => true,
            'ticketid' => 1,
        ]);

        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();
        $mockEmoji->method('decode')->willReturnArgument(0);
        $mockEmoji->method('encode')->willReturnArgument(0);

        $settings = $this->createMock(\SWIFT_Settings::class);
        $settings->method('GetBool')->willReturn(true);

        \SWIFT::GetInstance()->Settings = $settings;

        /** @var SWIFT_TicketPostMock|\PHPUnit_Framework_MockObject_MockObject $obj */
        $obj = $this->getMockObject('Tickets\Models\Ticket\SWIFT_TicketPostMock', [
            'Data' => $data,
            'Emoji' => $mockEmoji,
            'Settings' => $settings,
        ]);

        $actual = $obj->GetDisplayContents();
        $this->assertEquals(sprintf($expected, \SWIFT::Get('basename')), $actual);
    }

    public function testSmartReply()
    {
        // This strings test for SQL injection
        $messages = [
            "DBMS Banner: {{custom_field[\"AND'1'='2'UNION SELECT '0 UNION SELECT @@version#', 1, '0 UNION SELECT 3'#\"]}}
            Database Name: {{custom_field[\"AND'1'='2'UNION SELECT '0 UNION SELECT database()#', 1, '0 UNION SELECT 3'#\"]}}
            Database user: {{custom_field[\"AND'1'='2'UNION SELECT '0 UNION SELECT user()#', 1, '0 UNION SELECT 3'#\"]}}",
            "{{custom_field[\"AND'1'='2'UNION SELECT '0 UNION SELECT 0x4f3a33393a2253776966744d61696c65725f5472616e73706f72745f53656e646d61696c5472616e73706f7274223a333a7b733a31303a22002a005f627566666572223b4f3a33373a2253776966744d61696c65725f4279746553747265616d5f46696c654279746553747265616d223a343a7b733a34343a220053776966744d61696c65725f4279746553747265616d5f46696c654279746553747265616d005f70617468223b733a32333a225f5f73776966742f66696c65732f5f5243455f2e706870223b733a34343a220053776966744d61696c65725f4279746553747265616d5f46696c654279746553747265616d005f6d6f6465223b733a333a22772b62223b733a36323a220053776966744d61696c65725f4279746553747265616d5f416273747261637446696c74657261626c65496e70757453747265616d005f66696c74657273223b613a303a7b7d733a36363a220053776966744d61696c65725f4279746553747265616d5f416273747261637446696c74657261626c65496e70757453747265616d005f7772697465427566666572223b733a32313a223c3f70687020706870696e666f28293b3f3e0a2f2f223b7d733a31313a22002a005f73746172746564223b623a313b733a31393a22002a005f6576656e7444697370617463686572223b4f3a34303a2253776966744d61696c65725f4576656e74735f53696d706c654576656e7444697370617463686572223a303a7b7d7d0a,1#', 4,'0 UNION SELECT 1'#\"]}}"
        ];

        $user = $this->createMock(SWIFT_User::class);

        $ticket = $this->createMock(SWIFT_Ticket::class);
        $ticket->method('GetProperty')
            ->willReturn(1);

        $ticket->method('GetUserObject')
            ->willReturn($user);

        foreach ($messages as $message) {
            $response = SWIFT_TicketPost::SmartReply($ticket, $message);
            $this->assertEquals($message, $response);
        }
    }

    public function testSmartReplyWithCurlyBraces()
    {
        // This strings test for SQL injection
        $messages = '<p>Some <a href="%7B%7Bforename%7D%7D">Test</a>';
        $expected = '<p>Some <a href="N/A">Test</a>';

        $user = $this->createMock(SWIFT_User::class);

        $ticket = $this->createMock(SWIFT_Ticket::class);
        $ticket->method('GetProperty')
            ->willReturn(1);

        $ticket->method('GetUserObject')
            ->willReturn($user);

        $response = SWIFT_TicketPost::SmartReply($ticket, $messages);
        $this->assertEquals($expected, $response);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testAddLineBreaksIfTextReturnsArray() {

        $settings = $this->createMock(\SWIFT_Settings::class);
        $settings->method('GetBool')->willReturn(false);
        $settings->method('Get')->willReturn('text');
        \SWIFT::GetInstance()->Settings = $settings;

        $_contents = "content with <b>html</b>\nbye";
        ['contents' => $_contents, 'ishtml' => $_isHTML] = SWIFT_TicketPost::addLineBreaksIfText($_contents, true, \SWIFT::GetInstance());
        self::assertEquals("content with <b>html</b>\nbye", $_contents);

        $_contents = "content without html\nbye";
        ['contents' => $_contents, 'ishtml' => $_isHTML] = SWIFT_TicketPost::addLineBreaksIfText($_contents, true, \SWIFT::GetInstance());
        self::assertEquals("content without html\nbye", $_contents);

        $_contents = "content\nwith newline\nwhen is mail";
        ['contents' => $_contents, 'ishtml' => $_isHTML] = SWIFT_TicketPost::addLineBreaksIfText($_contents, true, \SWIFT::GetInstance(), true);
        self::assertEquals("content<br />\nwith newline<br />\nwhen is mail", $_contents);

        $_contents = "content\nwith multiple \n\n newlines when is mail";
        ['contents' => $_contents, 'ishtml' => $_isHTML] = SWIFT_TicketPost::addLineBreaksIfText($_contents, true, \SWIFT::GetInstance(), true);
        self::assertEquals("content<br />\nwith multiple <br />\n<br />\n newlines when is mail", $_contents);
    }
}

class SWIFT_TicketPostMock extends SWIFT_TicketPost
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct($this->Data);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

