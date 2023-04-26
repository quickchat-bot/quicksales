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

namespace Tickets\Library\AutoClose;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class AutoCloseManagerTest
 * @group tickets
 * @group tickets-lib3
 */
class AutoCloseManagerTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testExecutePendingReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['CacheGet'] = function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            return [
                1 => [
                    1 => [1 => [1]],
                    'markasresolved' => 1,
                ],
                3 => [
                    'markasresolved' => 0,
                ],
            ];
        };

        static::$_prop['autocloserulecache'] = [
            1 => [
                'autocloseruleid' => 1,
                'targetticketstatusid' => 1,
                'isenabled' => 1,
                'title' => 1,
                'sendpendingnotification' => 0,
                'inactivitythreshold' => 0,
                '_criteria' => [
                    ['ticketstatusid', 1, 1, 1],
                    ['ticketstatusid', 1, 1, 2],
                    ['departmentid', 1, 1, 1],
                    ['departmentid', 1, 1, 2],
                    ['priorityid', 1, 1, 1],
                    ['priorityid', 1, 1, 2],
                    ['tickettypeid', 1, 1, 1],
                    ['tickettypeid', 1, 1, 2],
                    ['other', 1, 1, 1],
                    ['other', 1, 1, 2],
                ],
            ],
            2 => [
                'autocloseruleid' => 2,
                'targetticketstatusid' => 2,
                'isenabled' => 0,
                '_criteria' => [],
            ],
            3 => [
                'autocloseruleid' => 3,
                'targetticketstatusid' => 3,
                'isenabled' => 1,
                '_criteria' => [],
            ],
        ];

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            '_criteria' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->expectOutputRegex('/Pending/');

        $this->assertTrue($obj::ExecutePending());

        static::$_prop['autocloserulecache'] = [
            1 => [
                'autocloseruleid' => 1,
                'targetticketstatusid' => 1,
                'isenabled' => 1,
                'title' => 1,
                'sendpendingnotification' => 0,
                'inactivitythreshold' => 0,
                '_criteria' => [
                    ['other', 1, 1, 1],
                    ['other', 1, 1, 2],
                ],
            ],
        ];
        $this->assertTrue($obj::ExecutePending());

        $this->setNextRecordLimit(0);
        $this->assertTrue($obj::ExecutePending());

        static::$_prop['autocloserulecache'] = [];
        $this->assertFalse($obj::ExecutePending());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExecuteClosureReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$databaseCallback['CacheGet'] = function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            return [
                1 => [
                    1 => [1 => [1]],
                    'markasresolved' => 1,
                ],
                3 => [
                    'markasresolved' => 0,
                ],
            ];
        };

        static::$_prop['autocloserulecache'] = [
            1 => [
                'autocloseruleid' => 1,
                'targetticketstatusid' => 1,
                'isenabled' => 1,
                'title' => 1,
                'sendpendingnotification' => 0,
                'closurethreshold' => 0,
                '_criteria' => [
                    ['ticketstatusid', 1, 1, 1],
                    ['ticketstatusid', 1, 1, 2],
                    ['departmentid', 1, 1, 1],
                    ['departmentid', 1, 1, 2],
                    ['priorityid', 1, 1, 1],
                    ['priorityid', 1, 1, 2],
                    ['tickettypeid', 1, 1, 1],
                    ['tickettypeid', 1, 1, 2],
                    ['other', 1, 1, 1],
                    ['other', 1, 1, 2],
                ],
            ],
            2 => [
                'autocloseruleid' => 2,
                'targetticketstatusid' => 2,
                'isenabled' => 0,
                '_criteria' => [],
            ],
            3 => [
                'autocloseruleid' => 3,
                'targetticketstatusid' => 3,
                'isenabled' => 1,
                '_criteria' => [],
            ],
        ];

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            '_criteria' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->expectOutputRegex('/Closed/');

        $this->assertTrue($obj::ExecuteClosure());

        $this->setNextRecordLimit(0);
        $this->assertTrue($obj::ExecuteClosure());

        static::$_prop['autocloserulecache'] = [];
        $this->assertFalse($obj::ExecuteClosure());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_AutoCloseManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\AutoClose\SWIFT_AutoCloseManagerMock');
    }
}

class SWIFT_AutoCloseManagerMock extends SWIFT_AutoCloseManager
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

