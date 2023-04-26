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

namespace Knowledgebase\Console {

    use Knowledgebase\Admin\LoaderMock;
    use SWIFT;
    use SWIFT_Exception;

    global $_test_times;
    $_test_times = [
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
     * @group knowledgebase
     */
    class Controller_RebuildIndexTest extends \SWIFT_TestCase
    {
        public function setUp()
        {
            parent::setUp();

            global $mockGetMicroTime;
            $mockGetMicroTime = true;
        }

        /**
         * @return Controller_RebuildIndexMock
         * @throws SWIFT_Exception
         */
        public function getController()
        {
            $mockDb = $this->getMockBuilder('SWIFT_Database')
                ->disableOriginalConstructor()
                ->getMock();

            $mockDb->method('QueryLimit')->willReturn(false);
            $mockDb->method('NextRecord')
                ->willReturnOnConsecutiveCalls(true, false);
            $mockDb->method('QueryFetch')
                ->willReturnOnConsecutiveCalls(false,
                    ['totalitems' => 1],
                    ['totalitems' => 1]);

            $this->mockProperty($mockDb, 'Record', [
                'kbarticleid' => '1',
                'kbarticledataid' => '1',
                'subject' => 'subject',
                'contentstext' => 'contentstext',
            ]);

            SWIFT::GetInstance()->Database = $mockDb;

            $mockConsole = $this->getMockBuilder('SWIFT_Console')
                ->disableOriginalConstructor()
                ->getMock();

            $mockConsole->method('Prompt')
                ->willReturnOnConsecutiveCalls('q', 'confirm',
                    'confirm', 0, 1, 'q', 'delete',
                    'confirm', 0, 1, ''
                );

            SWIFT::GetInstance()->Console = $mockConsole;

            $mockRouter = $this->getMockBuilder('SWIFT_Router')
                ->disableOriginalConstructor()
                ->getMock();

            $mockApp = $this->getMockBuilder('SWIFT_App')
                ->disableOriginalConstructor()
                ->getMock();

            $mockRouter->method('GetApp')->willReturn($mockApp);

            SWIFT::GetInstance()->Router = $mockRouter;

            return new Controller_RebuildIndexMock([
                'Database' => $mockDb,
                'Console' => $mockConsole,
            ]);
        }

        /**
         * @throws \SWIFT_Exception
         */
        public function testStartReturnsTrue()
        {
            $obj = $this->getController();

            $this->assertTrue($obj->Start(0),
                'Returns true without confirmation');

            $this->assertTrue($obj->Start(0),
                'Returns true if there are no items');

            $this->assertTrue($obj->Start(2),
                'Returns true with delete confirmation');

            $this->assertTrue($obj->Start(2),
                'Returns true without delete confirmation');

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $this->assertFalse($obj->Start(1));
        }
    }

    class Controller_RebuildIndexMock extends Controller_RebuildIndex
    {
        /**
         * Controller_RebuildIndexMock constructor.
         * @param array $services
         * @throws SWIFT_Exception
         */
        public function __construct(array $services = [])
        {
            $this->Load = new LoaderMock();

            foreach ($services as $prop => $service) {
                $this->$prop = $service;
            }

            parent::__construct(false);
        }

        public function Initialize()
        {
            return true;
        }
    }
}