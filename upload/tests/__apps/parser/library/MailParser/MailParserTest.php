<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Banjo Mofesola Paul <banjo.paul@aurea.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Library\MailParser;

use Knowledgebase\Admin\LoaderMock;
use Parser\Library\Loop\SWIFT_LoopChecker;
use Parser\Models\EmailQueue\SWIFT_EmailQueue;
use Parser\Models\EmailQueue\SWIFT_EmailQueuePipe;
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;

/**
 * Class MailParserTest
 * @group parser-library
 */
class MailParserTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Library\MailParser\SWIFT_MailParser', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetForceReprocessing()
    {
        $obj = $this->getMocked();

        $this->assertNull($obj->SetForceReprocessing(false));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetRawEmailDataThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'SetRawEmailData', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetRawEmailDataInvalidData()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $obj->SetRawEmailData('');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetRawEmailDataReturnsString()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_STRING, $obj->GetRawEmailData());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetRawEmailData');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLogDebugReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->LogDebug(''));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'LogDebug', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcess()
    {
        $obj = $this->getMocked();

        $_flipQueueCache = false;
        static::$databaseCallback['CacheGet'] = function ($x) use(&$_flipQueueCache) {
            switch ($x) {
                case 'breaklinecache':
                    return [
                        ['isregexp' => '1', 'breakline' => '/\-/'],
                        ['isregexp' => '0', 'breakline' => '-'],
                    ];
                case 'queuecache':
                    return $_flipQueueCache? [
                        'pipe' => [1,1],
                        'pointer' => ['reply-to@email.com' => '1'],
                        'list' => ['1' => ['isenabled' => 1, 'type' => APP_PARSER]],
                    ] : [ '1' => [ '1' => [1] ] ];
                default:
                    return [ '1' => [ '1' => [1] ] ];
            }
        };

        $count = 0;
        static::$nextRecordType = \SWIFT_TestCase::NEXT_RECORD_RETURN_CALLBACK;
        static::$databaseCallback['NextRecord'] = function () use (&$count) {
            $count++;
            \SWIFT::GetInstance()->Database->Record['staffid'] = 1;
            \SWIFT::GetInstance()->Database->Record['emailqueueid'] = 1;
            \SWIFT::GetInstance()->Database->Record['email'] = 'email@address.com';
            \SWIFT::GetInstance()->Database->Record['userpassword'] = 'cXUKb5GFraI/dEXavA9RwEbMtabj9gqbxHuIkQ==';
            \SWIFT::GetInstance()->Database->Record['host'] = 'localhost';
            \SWIFT::GetInstance()->Database->Record['port'] = '143';
            \SWIFT::GetInstance()->Database->Record['username'] = 'username';
            \SWIFT::GetInstance()->Database->Record['fetchtype'] = SWIFT_EmailQueue::FETCH_IMAP;
            return $count % 2;
        };

        $this->assertFalse($obj->Process());
        $this->assertFalse($obj->Process(true, new SWIFT_EmailQueuePipe(new \SWIFT_DataID(1))));

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x == 'pr_contentpriority') {
                return 'text';
            }
            return 1;
        };
        $this->assertFalse($obj->Process(true, new SWIFT_EmailQueuePipe(new \SWIFT_DataID(1))));

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x == 'pr_contentpriority') {
                return 'html';
            }
            return 1;
        };
        $this->assertFalse($obj->Process(true, new SWIFT_EmailQueuePipe(new \SWIFT_DataID(1))));

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x == 'pr_contentpriority') {
                return 'html';
            }
            if ($x == 't_tinymceeditor') {
                return 0;
            }
            return 1;
        };
        $this->assertFalse($obj->Process(true, new SWIFT_EmailQueuePipe(new \SWIFT_DataID(1))), 'Returns false if tinymce editor is disabled');

        $_flipQueueCache = true;
        $this->assertFalse($obj->Process(true, new SWIFT_EmailQueuePipe(new \SWIFT_DataID(1))));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'Process');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testMultiplePipeCheck()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->MultiplePipeCheck([]));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'MultiplePipeCheck', []);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetEmailQueueThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetEmailQueue', []);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessBreaklines()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['CacheGet'] = function ($x) {
            switch ($x) {
                case 'breaklinecache':
                    return 1;
                default:
                    return [ '1' => [ '1' => [1] ] ];
            }
        };
        $this->assertEquals('content', $obj->ProcessBreaklines('content'));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'ProcessBreaklines', 'content');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testParserException()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Parser_exception::class);
        throw new SWIFT_Parser_exception('message');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_MailParserMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Library\MailParser\SWIFT_MailParserMock');
    }
}

class SWIFT_MailParserMock extends SWIFT_MailParser
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'emailqueueid' => 1,
            'tgroupid' => 1,
            'type' => APP_BACKEND,
            'departmentid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'ticketstatusid' => 1,
            'ticketautoresponder' => 1,
            'queuesignatureid' => 1,
            'fetchtype' => 'imap',
            'contents' => 'contents',
            'email' => 'email@address.com',
            'fromemail' => 'email@address.com',
            'total' => 1
        ]);

        parent::__construct('rawEmailData');
    }

    public function SetRawEmailData($_rawEmailData)
    {
        return parent::SetRawEmailData($_rawEmailData); // TODO: Change the autogenerated stub
    }

    public function LogDebug($_debugMessage)
    {
        return parent::LogDebug($_debugMessage); // TODO: Change the autogenerated stub
    }

    public function MultiplePipeCheck($_recipientList)
    {
        return parent::MultiplePipeCheck($_recipientList); // TODO: Change the autogenerated stub
    }

    public function ProcessBreaklines($_emailContents)
    {
        return parent::ProcessBreaklines($_emailContents); // TODO: Change the autogenerated stub
    }

    public function GetEmailQueue($_recipientList)
    {
        return parent::GetEmailQueue($_recipientList); // TODO: Change the autogenerated stub
    }

    public function Initialize()
    {
        // override
        $_mailStructure = new \stdClass();
        $_mailStructure->fromEmail = 'from@email.com';
        $_mailStructure->fromName = 'fromName';
        $_mailStructure->replyto = 'reply-to@email.com';
        $_mailStructure->returnAddress = 'reply-to@email.com';
        $_mailStructure->returnAddressEmail = 'reply-to@email.com';
        $_mailStructure->returnAddressName = 'returnAddressName';
        $_mailStructure->replytoName = 'replytoName';
        $_mailStructure->replytoEmail = 'reply-to@email.com';
        $_mailStructure->recipientAddresses = [ 'recipient@address.com', 'support+a.user.123@acme.com' ];
        $_mailStructure->bccRecipientAddresses = [ 'bcc@address.com' ];
        $_mailStructure->toEmail = 'to-email@address.com';
        $_mailStructure->toName = 'toName';
        $_mailStructure->subject = 'subject';
        $_mailStructure->deliveredTo = 'to-email@address.com';
        $_mailStructure->toEmailList = [ 'to-email@address.com' ];
        $_mailStructure->text = 'text';
        $_mailStructure->textCharset = 'UTF8';
        $_mailStructure->htmlCharset = 'UTF8';
        $_mailStructure->inReplyTo = 'inReplyTo';
        $_mailStructure->headers = [
            'date' => 'UTC date',
            'delivered-to' => 'delivered@email.com',
            'message-id' => 1,
        ];
        $_mailStructure->attachments = [
            [ 'data' => '', 'size' => 1, 'filename' => 'filename', 'extension' => 'txt', 'contenttype' => 'text/plain' ]
        ];

        $this->MailMime = new \SWIFT_MailMIME('emailData');
        $this->MailParserEmail = new SWIFT_MailParserEmail($_mailStructure);
        $this->LoopChecker = new SWIFT_LoopChecker();
        return true;
    }
}

