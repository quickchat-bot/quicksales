<?php
/**
 * ###############################################
 *
 * Kayako Classic
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
use PHPUnit\Framework\Constraint\IsType;
use SWIFT_Exception;

/**
 * Class MailParserEmailTest
 * @group parser-library
 */
class MailParserEmailTest extends \SWIFT_TestCase
{
    private $_mailStructureHtml = false;

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Library\MailParser\SWIFT_MailParserEmail', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessMailStructureReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['CacheGet'] = function ($x) use (&$obj) {
            switch ($x) {
                case 'queuecache':
                    return $obj->_queueCacheResponse;
                default:
                    return [ '1' => [ '1' => [1] ] ];
            }
        };

        $this->assertTrue($obj->ProcessMailStructure($obj->_xMailStructure));

        // FOR COVERAGE

        unset($obj->_xMailStructure->fromEmail);
        unset($obj->_xMailStructure->toEmail);
        $obj->_xMailStructure->subject = [ 'subject' ];
        $obj->_xMailStructure->returnAddress = [ 'reply-to@email.com' ];
        $obj->_xMailStructure->textSize = 4;
        $obj->_xMailStructure->htmlSize = 4;
        $obj->_xMailStructure->headers['date'] = [ 'dateHeaders' ];
        $obj->_xMailStructure->headers['message-id'] = [ 'message-id' ];
        $this->assertTrue($obj->ProcessMailStructure($obj->_xMailStructure));

        static::$databaseCallback['SettingsGet'] = function ($x) {
            return $this->SettingsGetHook($x);
        };

        unset($obj->_xMailStructure->headers['delivered-to']);
        $this->assertTrue($obj->ProcessMailStructure($obj->_xMailStructure));

        $this->_mailStructureHtml = true;
        $obj->_xMailStructure->toEmail = 'support+a.user.123@acme.com';
        $obj->_xMailStructure->subject = '';
        $this->assertTrue($obj->ProcessMailStructure($obj->_xMailStructure));

        $this->_mailStructureHtml = false;
        $obj->_xMailStructure->text = '';
        $this->assertTrue($obj->ProcessMailStructure($obj->_xMailStructure));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'ProcessMailStructure', $obj->_xMailStructure);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testToString()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_STRING, $obj->__toString());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, '__toString');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetBayesianCategoryThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetBayesianCategory');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetBayesianCategoryReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->SetBayesianCategory(1));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'SetBayesianCategory', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetFromNameReturnsString()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_STRING, $obj->GetFromName());

        $obj->SetReplyToName('');
        $obj->SetFromName('');
        $this->assertInternalType(IsType::TYPE_STRING, $obj->GetFromName());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetFromName');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetFromEmailThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetReplyToEmail('');
        $this->assertInternalType(IsType::TYPE_STRING, $obj->GetFromEmail());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetFromEmail');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetOriginalFromEmailReturnsString()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_STRING, $obj->GetOriginalFromEmail());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetOriginalFromEmail');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetToNameThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetToName');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetToEmailThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetToEmail');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetToEmailSuffixThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetToEmailSuffix');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReplyToNameThrowsExcepion()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetReplyToName');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReplyToEmailThrowsExcepion()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetReplyToEmail');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetSubjectThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetSubject');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetSubject()
    {
        $obj = $this->getMocked();

        $this->assertNull($obj->SetSubject('subject'));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'SetSubject', 'subject');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnAddressThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetReturnAddress');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnAddressNameThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetReturnAddressName');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnAddressEmailThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetReturnAddressEmail');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetRecipientsThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetRecipients');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetAttachmentsThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetAttachments');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetInReplyToThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetInReplyTo');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTextThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetText');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTextCharsetThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetTextCharset');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTextSizeThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetTextSize');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetHTMLThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetHTML');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetHTMLSizeThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetHTMLSize');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetHTMLCharsetThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetHTMLCharset');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetFinalContentsThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetFinalContents');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetFinalContentSizeThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetFinalContentSize');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetFinalContentCharsetThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetFinalContentCharset');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetFinalContentIsHTMLThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetFinalContentIsHTML');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPropertyContainerThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetPropertyContainer');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetProperty()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->GetProperty('invalid'));

        $obj->SetProperty('name', 'value');
        $this->assertEquals('value', $obj->GetProperty('name'));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetProperty', 'name');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetPropertyInvalidData()
    {
        $obj = $this->getMocked();

        $this->setExpectedException(SWIFT_Exception::class, SWIFT_INVALIDDATA);
        $this->assertTrue($obj->SetProperty('', ''));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSetPropertyThrowsException()
    {
        $obj = $this->getMocked();

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'SetProperty', '', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessContentTypeReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_ARRAY, $obj->ProcessContentType('data;string'));

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'ProcessContentType', 'data;string');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDateThrowsException()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj->GetDate());

        $obj->SetDateTime(1);
        $this->assertInternalType(IsType::TYPE_INT, $obj->GetDate());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetDate');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetToEmailListReturnsString()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_STRING, $obj->GetToEmailList());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetToEmailList');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetMessageIDReturnsString()
    {
        $obj = $this->getMocked();

        $this->assertInternalType(IsType::TYPE_STRING, $obj->GetMessageID());

        $obj->SetIsClassLoaded(false);
        $this->assertClassNotLoaded($obj, 'GetMessageID');
    }

    public function SettingsGetHook($x) {
        switch ($x) {
            case 't_cleanmailsubjects':
                return '1';
            case 'pr_contentpriority':
                return $this->_mailStructureHtml ? 'html' : 'text';
            default:
                $ret = $this->RecallSettingsGet($x);
                static::$databaseCallback['SettingsGet'] = function ($x) {
                    return $this->SettingsGetHook($x);
                };
                return $ret;
        }
    }

    private function RecallSettingsGet($x) {
        unset(static::$databaseCallback['SettingsGet']);
        return \SWIFT::GetInstance()->Settings->Get($x);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_MailParserEmailMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Library\MailParser\SWIFT_MailParserEmailMock');
    }
}

class SWIFT_MailParserEmailMock extends SWIFT_MailParserEmail
{
    public $_xMailStructure;
    public $_queueCacheResponse;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

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

        $this->_xMailStructure = $_mailStructure;

        parent::__construct($_mailStructure);
    }

    public function SpawnInstance($_mailStructure) {
        return parent::__construct($_mailStructure);
    }

    public function ProcessMailStructure($_mailStructure)
    {
        return parent::ProcessMailStructure($_mailStructure); // TODO: Change the autogenerated stub
    }

    public function ProcessContentType($_dataString)
    {
        return parent::ProcessContentType($_dataString); // TODO: Change the autogenerated stub
    }

    public function SetFromName($_fromName) {
        $this->_fromName = $_fromName;
    }

    public function SetReplyToName($_replyToName) {
        $this->_replyToName = $_replyToName;
    }

    public function SetReplyToEmail($_replyToEmail) {
        $this->_replyToEmail = $_replyToEmail;
    }

    public function SetDateTime($_dateTime) {
        $this->_dateTime = $_dateTime;
    }

    public function Initialize()
    {
        // override
        $this->_queueCacheResponse = [
            'pointer' => [
                'recipient@address.com' => 1
            ]
        ];
        return true;
    }
}

