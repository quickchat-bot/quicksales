<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Console;

use Base\Console\ConsoleMock;
use Knowledgebase\Admin\LoaderMock;
use Parser\Library\MailParser\SWIFT_MailParser;
use SWIFT_Exception;

/**
 * Class Controller_ParseTest
 * @group parser
 * @group parser-console
 */
class Controller_ParseTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Console\Controller_Parse', $obj);
    }

    public function setUp()
    {
        parent::setUp();

        $mockRouter = $this->getMockBuilder('SWIFT_Router')
            ->disableOriginalConstructor()
            ->getMock();

        $mockApp = $this->getMockBuilder('SWIFT_App')
            ->disableOriginalConstructor()
            ->getMock();

        $mockApp->method('GetName')->willReturn(APP_PARSER);

        $mockRouter->method('GetApp')->willReturn($mockApp);

        \SWIFT::GetInstance()->Router = $mockRouter;
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getMocked();

        $testFileName = __DIR__ . '/testemail.txt';

        $f = fopen($testFileName, 'w');
        fputs($f, 'test');
        fclose($f);


        $mailParserMock = $this->getMockBuilder(SWIFT_MailParser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mailParserMock->method('Process')->willReturn(true);

        $obj->MailParser = $mailParserMock;

        $this->assertTrue($obj->Index('', $testFileName),
            'Returns true');


        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'queuecache')
                return [
                    'pointer' => [1],
                    'list' => [
                        1 => [
                            'emailqueueid' => 1,
                            'fetchtype' => 'pipe',
                            'email' => 'test@test.com',
                            'type' => APP_NEWS
                        ]
                    ]
                ];
        };

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['queuesignatureid' => 1, 'emailqueueid' => 1]);

        $this->assertTrue($obj->Index('test@test.com', $testFileName),
            'Returns true');

        unlink($testFileName);

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testFileReturnsTrue()
    {
        $obj = $this->getMocked();


        $this->assertFalse($obj->File('dummy'),
            'Returns false');

        $testFileName = __DIR__ . '/testemail.txt';

        $f = fopen($testFileName, 'w');
        fputs($f, 'test');
        fclose($f);


        $mailParserMock = $this->getMockBuilder(SWIFT_MailParser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mailParserMock->method('Process')->willReturn(true);

        $obj->MailParser = $mailParserMock;

        $this->assertTrue($obj->File($testFileName),
            'Returns true');

        unlink($testFileName);


        $this->assertClassNotLoaded($obj, 'File', $testFileName);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_ParseMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Console\Controller_ParseMock', ['Console' => new ConsoleMock()]);
    }
}

class Controller_ParseMock extends Controller_Parse
{
    public $MailParser;

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

