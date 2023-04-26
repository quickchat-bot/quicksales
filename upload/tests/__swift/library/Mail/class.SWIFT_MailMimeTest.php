<?php
/**
 * ###############################################
 *
 * Kayako Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */


/**
 * Class MailMimeTest
 * @group mailmime
 */
class SWIFT_MailMimeTest extends SWIFT_TestCase
{
    public function setUp()
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new RuntimeException($errstr . " on line " . $errline . " in file " . $errfile);
        });
    }

    public function tearDown() {
        restore_error_handler();
    }

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('SWIFT_MailMime', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDecodeReturnsValidRecipients()
    {
        $obj = $this->getMocked();
        $output = $obj->Decode();
        $this->assertNotNull($output);
        $this->assertEquals('garcia&sons@maint.xo.local', $output->recipientAddresses[0]);
        $this->assertEquals('werner.garcia+o\'bannon@crossover.com', $output->recipientAddresses[1]);

        $this->assertClassNotLoaded($obj, 'Decode');
    }

    public function providerAddress()
    {
        return [
            ['user@kayako.com', ['dest@kayako.com', 'user@kayako.com']],
            ['user@kayako.com, user2@kayako.com', ['dest@kayako.com', 'user@kayako.com', 'user2@kayako.com']],
            ['"user1" <user@kayako.com>, user2@kayako.com', ['dest@kayako.com', 'user@kayako.com', 'user2@kayako.com']],
            [
                '"abc_hsggsttwueiiwehj.fdfwrt@kjsywe7.com" <abc_hsggsttwueiiwehj.fdfwrt@kjsywe7.com>, "xyz_hsggsttwueiiwehj.fdfwrt@kjsywe7.com" <xyz_hsggsttwueiiwehj.fdfwrt@kjsywe7.com>, "ooo_hsggsttwueiiwehj.fdfwrt@kjsywe7.com" <ooo_hsggsttwueiiwehj.fdfwrt@kjsywe7.com>, "uyt_hsggsttwueiiwehj.fdfwrt@kjsywe7.com" <uyt_hsggsttwueiiwehj.fdfwrt@kjsywe7.com>',
                ['dest@kayako.com', 'abc_hsggsttwueiiwehj.fdfwrt@kjsywe7.com', 'xyz_hsggsttwueiiwehj.fdfwrt@kjsywe7.com', 'ooo_hsggsttwueiiwehj.fdfwrt@kjsywe7.com', 'uyt_hsggsttwueiiwehj.fdfwrt@kjsywe7.com']
            ]
        ];
    }

    /**
     * @dataProvider providerAddress
     * @param $input
     * @param $expected
     */
    public function testDecodeReturnsValidCCRecipients($input, $expected)
    {
        $simpleEmail = <<<EMAIL
MIME-Version: 1.0
From: test@kayako.com
To: dest@kayako.com
Cc: {$input}
Subject: UT Sample
Content-Type: text/plain; charset="UTF-8"

Simple test
EMAIL;
        /**
         * @var SWIFT_MailMimeMock
         */
        $mockObj = $this->getMockBuilder(SWIFT_MailMIME::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['Decode'])
            ->getMock();

        $reflection = new ReflectionClass(SWIFT_MailMIME::class);

        $_emailData = $reflection->getProperty('_emailData');
        $_emailData->setAccessible(true);
        $_emailData->setValue($mockObj, $simpleEmail);

        $mimePolicy = new SWIFT_MailMIMEDecodePolicy();
        $_MimePolicy = $reflection->getProperty('MimePolicy');
        $_MimePolicy->setAccessible(true);
        $_MimePolicy->setValue($mockObj, $mimePolicy);

        $_MIME = $reflection->getProperty('MIME');
        $_MIME->setAccessible(true);
        $_MIME->setValue($mockObj, new Mail_mimeDecode($simpleEmail, $mimePolicy));

        $_RFC822 = $reflection->getProperty('RFC822');
        $_RFC822->setAccessible(true);
        $_RFC822->setValue($mockObj, new Mail_RFC822Extended());

        $_output = $reflection->getProperty('Output');
        $_output->setAccessible(true);
        $_output->setValue($mockObj, new stdClass());

        $mockObj->expects(self::exactly(5))
            ->method('GetIsClassLoaded')
            ->willReturn(true);

        $mockObj->expects(self::once())
            ->method('GetEmailData')
            ->willReturn($simpleEmail);

        $mockLanguage = $this->getMockBuilder(SWIFT_LanguageEngine::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockLanguage->expects(self::once())
            ->method('Get')
            ->willReturn('charset');
        $_Language = $reflection->getProperty('Language');
        $_Language->setAccessible(true);
        $_Language->setValue($mockObj, $mockLanguage);

        $actual = $mockObj->Decode();
        $this->assertNotNull($actual);
        $this->assertEquals($expected, $actual->recipientAddresses);
    }

    public function testDecodeDonotThrowCountErrorOnDecode()
    {
        $expected = ['dest@kayako.com'];
        //the to param is set for undisclosed-recipients which is a valid case for mailboxes but invalid email address
        $simpleEmail = <<<EMAIL
        MIME-Version: 1.0
        From: test@kayako.com
        Subject: UT Sample
        Content-Type: text/plain; charset="UTF-8"
        Bcc: dest@kayako.com
        To: undisclosed-recipients:;
        Simple test
        EMAIL;
        /**
         * @var SWIFT_MailMimeMock
         */
        $mockObj = $this->getMockBuilder(SWIFT_MailMIME::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['Decode'])
            ->getMock();

        $reflection = new ReflectionClass(SWIFT_MailMIME::class);

        $_emailData = $reflection->getProperty('_emailData');
        $_emailData->setAccessible(true);
        $_emailData->setValue($mockObj, $simpleEmail);

        $mimePolicy = new SWIFT_MailMIMEDecodePolicy();
        $_MimePolicy = $reflection->getProperty('MimePolicy');
        $_MimePolicy->setAccessible(true);
        $_MimePolicy->setValue($mockObj, $mimePolicy);

        $_MIME = $reflection->getProperty('MIME');
        $_MIME->setAccessible(true);
        $_MIME->setValue($mockObj, new Mail_mimeDecode($simpleEmail, $mimePolicy));

        $_RFC822 = $reflection->getProperty('RFC822');
        $_RFC822->setAccessible(true);
        $_RFC822->setValue($mockObj, new Mail_RFC822Extended());

        $_output = $reflection->getProperty('Output');
        $_output->setAccessible(true);
        $_output->setValue($mockObj, new stdClass());

        $mockObj->expects(self::exactly(5))
            ->method('GetIsClassLoaded')
            ->willReturn(true);

        $mockObj->expects(self::once())
            ->method('GetEmailData')
            ->willReturn($simpleEmail);

        $mockLanguage = $this->getMockBuilder(SWIFT_LanguageEngine::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockLanguage->expects(self::once())
            ->method('Get')
            ->willReturn('charset');
        $_Language = $reflection->getProperty('Language');
        $_Language->setAccessible(true);
        $_Language->setValue($mockObj, $mockLanguage);

        $actual = $mockObj->Decode();
        $this->assertEquals($expected, $actual->bccRecipientAddresses);
    }


    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_MailMimeMock
     */
    public function getMocked(array $services = [])
    {
        return $this->getMockObject('SWIFT_MailMimeMock', $services);
    }
}

class SWIFT_MailMimeMock extends SWIFT_MailMime
{
    public $_maildata;

    public function __construct($services = [])
    {
        $this->_maildata = file_get_contents(__DIR__ . '/maildata.txt');

        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct($this->_maildata);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}
