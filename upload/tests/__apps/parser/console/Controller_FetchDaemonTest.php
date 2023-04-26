<?php
/**
 * ###############################################
 *
 * Kayako Classic
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

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_FetchDaemonTest
 * @group parser
 * @group parser-console
 */
class Controller_FetchDaemonTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Console\Controller_FetchDaemon', $obj);
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

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'queuecache')
                return [
                    'pointer' => [1],
                    'list' => [
                        1 => [
                            'fetchtype' => 'imap',
                            'isenabled' => 1
                        ]
                    ]
                ];
        };

        $this->expectOutputRegex('/\{.*\}/');

        $this->assertTrue($obj->Index(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_FetchDaemonMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Console\Controller_FetchDaemonMock');
    }
}

class Controller_FetchDaemonMock extends Controller_FetchDaemon
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

