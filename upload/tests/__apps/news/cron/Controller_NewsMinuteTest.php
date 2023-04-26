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

namespace News\Cron;

use News\Admin\LoaderMock;
use SWIFT;

/**
 * Class Controller_NewsMinuteTest
 * @group news
 */
class Controller_NewsMinuteTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryFetch')->willReturn([
            'cronid' => 1,
            'name' => 'name',
        ]);

        SWIFT::GetInstance()->Database = $mockDb;
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testSyncReturnsTrue()
    {
        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn('1');

        $mockMgr = $this->getMockBuilder('News\Library\Sync\SWIFT_NewsSyncManager')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_NewsMinuteMock([
            'Settings' => $mockSettings,
            'NewsSyncManager' => $mockMgr,
        ]);

        SWIFT::Set('iscron', false);

        $obj->SetIsClassLoaded(true);
        $this->assertTrue($obj->Sync());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Sync();
    }
}

class Controller_NewsMinuteMock extends Controller_NewsMinute
{
    /**
     * Controller_NewsMinuteMock constructor.
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct();
    }

    public function Initialize()
    {
        return true;
    }
}
