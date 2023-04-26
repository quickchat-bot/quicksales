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

namespace {

    // This allow us to configure the behavior of the "global mock"
    global $mockGetMicroTime;
    $mockGetMicroTime = false;
}

namespace Tickets\Console {

    use Knowledgebase\Admin\LoaderMock;
    use SWIFT_Exception;

    global $_test_times;
    $_test_times = [
        0,
        50,
        0,
        3500,
        0,
        9500,
        0,
        50,
        0,
        3500,
        0,
        9500,
    ];

    global $_test_time_idx;
    $_test_time_idx = 0;

    /**
     * Override getmicrotime() in current namespace for testing
     *
     * @return int
     */
    function getmicrotime()
    {
        global $mockGetMicroTime;
        if ($mockGetMicroTime === true) {
            global $_test_times;
            global $_test_time_idx;

            return $_test_times[$_test_time_idx++];
        }

        return call_user_func_array('\getmicrotime', func_get_args());
    }

    /**
     * Class Controller_RebuildIndexTest
     * @group tickets
     * @group tickets-console
     */
    class Controller_RebuildIndexTest extends \SWIFT_TestCase
    {
        public static $_next = 0;
        public static $_prop = [];

        public function setUp()
        {
            parent::setUp();

            global $mockGetMicroTime;
            $mockGetMicroTime = true;
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testConstructorReturnsClassInstance()
        {
            $obj = $this->getMocked();
            $this->assertInstanceOf('Tickets\Console\Controller_RebuildIndex', $obj);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testWorkFlowLinksReturnsTrue()
        {
            $obj = $this->getMocked();

            $SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'ticketworkflowid' => 1,
            ];
            $SWIFT->Database->method('QueryFetch')->willReturn($arr);
            $SWIFT->Database->Record = $arr;

            $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
                ->disableOriginalConstructor()
                ->getMock();
            $mockCache->method('Get')->willReturnCallback(function ($x) {
                if ($x === 'ticketworkflowcache') {
                    return [

                    ];
                }

                return [
                    1 => [
                        1 => [1 => [1]],
                    ],
                ];
            });
            $obj->Cache = $mockCache;
            \SWIFT::GetInstance()->Cache = $mockCache;

            $this->assertTrue($obj->WorkFlowLinks(0),
                'Returns true with permission');

            $this->assertClassNotLoaded($obj, 'WorkFlowLinks', 0);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testCalculateResponseTimeReturnsTrue()
        {
            $obj = $this->getMocked();

            $SWIFT = \SWIFT::GetInstance();

            $mockDb = $this->getMockBuilder('SWIFT_Database')
                ->disableOriginalConstructor()
                ->getMock();

            $mockDb->method('NextRecord')->willReturnCallback(function () {
                self::$_next++;

                $in_array = in_array(self::$_next, [1, 2, 5, 6, 7, 8, 9], true);

                if (in_array(self::$_next, [5, 7], true)) {
                    self::$_prop['creator'] = 1;
                    self::$_prop['dateline'] = self::$_next === 5;
                }

                if (in_array(self::$_next, [6, 8], true)) {
                    self::$_prop['creator'] = 2;
                    self::$_prop['dateline'] = self::$_next === 6;
                }

                if (self::$_next === 9) {
                    self::$_prop['creator'] = 3;
                }

                return $in_array;
            });

            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'ticketworkflowid' => 1,
                'slaplanid' => 1,
                'slaid' => 1,
                'slascheduleid' => 1,
                'slaresponsetime' => 0,
                'averageresponsetimehits' => 0,
                '_criteria' => 0,
                'ruletype' => 1,
                'ticketpostid' => 1,
                'creator' => &self::$_prop['creator'],
                'dateline' => &self::$_prop['dateline'],
            ];
            $mockDb->method('QueryFetch')->willReturn($arr);
            $mockDb->Record = $arr;

            $obj->Database = $mockDb;
            $SWIFT->Database = $mockDb;

            $this->assertTrue($obj->CalculateResponseTime('no'),
                'Returns true with invalid number');

            $this->assertTrue($obj->CalculateResponseTime(0),
                'Returns true with permission');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testPropertiesReturnsTrue()
        {
            $obj = $this->getMocked();

            $SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'trasholddepartmentid' => 1,
                'ticketstatusid' => 1,
                'priorityid' => 1,
                'tickettypeid' => 1,
                'ownerstaffid' => 0,
                'totalreplies' => 0,
                'lastactivity' => 0,
                'totalitems' => &self::$_prop['totalitems'],
            ];
            $mockDb = $this->getMockBuilder('SWIFT_Database')
                ->disableOriginalConstructor()
                ->getMock();
            $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
            $mockDb->method('QueryLimit')->willReturn(0);
            $mockDb->method('QueryFetch')->willReturn($arr);
            $mockDb->Record = $arr;

            $obj->Database = $mockDb;
            $SWIFT->Database = $mockDb;

            static::$_prop['prompt'] = 1;

            $this->assertTrue($obj->Properties(),
                'Returns true without items');

            self::$_prop['totalitems'] = 1;
            $this->assertTrue($obj->Properties(2),
                'Returns true with time = 60');

            $this->assertTrue($obj->Properties(),
                'Returns true with time = 3600');

            $this->assertTrue($obj->Properties(),
                'Returns true with time > 3600');

            $this->assertClassNotLoaded($obj, 'Properties');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testStartReturnsTrue()
        {
            $obj = $this->getMocked();

            static::$_prop['prompt'] = 'q';
            $this->assertTrue($obj->Start(),
                'Returns true if quit');

            static::$_prop['prompt'] = 'confirm';
            $this->assertTrue($obj->Start(),
                'Returns true without posts');

            $SWIFT = \SWIFT::GetInstance();
            $arr = [
                'ticketid' => 1,
                'iswatched' => 0,
                'lastpostid' => 0,
                'departmentid' => 1,
                'flagtype' => 1,
                'isresolved' => 1,
                'totalitems' => 1,
            ];
            $mockDb = $this->getMockBuilder('SWIFT_Database')
                ->disableOriginalConstructor()
                ->getMock();
            $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
            $mockDb->method('QueryLimit')->willReturn(0);
            $mockDb->method('QueryFetch')->willReturn($arr);
            $mockDb->Record = $arr;

            $obj->Database = $mockDb;
            $SWIFT->Database = $mockDb;

            $this->assertTrue($obj->Start(2),
                'Returns true with time = 60');

            $this->assertTrue($obj->Start(0),
                'Returns true');

            $this->assertTrue($obj->Start(),
                'Returns true with time = 3600');

            $this->assertTrue($obj->Start(),
                'Returns true with time > 3600');

            $this->assertClassNotLoaded($obj, 'Start');
        }

        /**
         * @param array $services
         * @return \PHPUnit_Framework_MockObject_MockObject|Controller_RebuildIndexMock
         */
        protected function getMocked(array $services = [])
        {
            $router = $this->getMockBuilder('SWIFT_Router')
                ->disableOriginalConstructor()
                ->getMock();

            $app = $this->getMockBuilder('SWIFT_App')
                ->disableOriginalConstructor()
                ->getMock();
            $app->method('GetName')->willReturn('tickets');
            $router->method('GetApp')->willReturn($app);
            \SWIFT::GetInstance()->Router = $router;

            $console = $this->getMockBuilder('SWIFT_Console')
                ->disableOriginalConstructor()
                ->getMock();
            $console->method('Prompt')->willReturnCallback(function($x) {
                if (false !== strpos($x, 'Please enter a start offset lower than')) {
                    return 0;
                }
                if (false !== strpos($x, 'Enter the number of posts')) {
                    if (!isset(static::$_prop['numposts'])) {
                        static::$_prop['numposts'] = 1;
                        return 0;
                    }

                    return 1;
                }

                if (false !== strpos($x, 'Type "DELETE"')) {
                    if (!isset(static::$_prop['delete'])) {
                        static::$_prop['delete'] = 1;

                        return 'no';
                    }

                    return 'delete';
                }

                if (isset(static::$_prop['prompt'])) {
                    return static::$_prop['prompt'];
                }

                return 'q';
            });

            $obj = $this->getMockObject(Controller_RebuildIndexMock::class, array_merge($services, [
                'Console' => $console,
            ]));
            return $obj;
        }
    }

    class Controller_RebuildIndexMock extends Controller_RebuildIndex
    {
        public $Cache;
        public $Database;

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

}
