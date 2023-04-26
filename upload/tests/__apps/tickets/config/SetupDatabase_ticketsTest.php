<?php
/**
 * ###############################################
 *
 * Kayako Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Tickets;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class SetupDatabase_ticketsTest
 * @group tickets
 * @group tickets-config
 */
class SetupDatabase_ticketsTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\SWIFT_SetupDatabase_tickets', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLoadTablesReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->LoadTables(),
            'Returns true with permission');
    }

    public function testGetPageCountReturnsNumber()
    {
        $obj = $this->getMocked();

        $this->assertNotEquals(0, $obj->GetPageCount());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInstallReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $ado = $this->getMockBuilder('ADOConnection')
            ->disableOriginalConstructor()
            ->setMethods(['Execute'])
            ->getMock();

        $dict = $this->getMockBuilder('ADODB_DataDict')
            ->disableOriginalConstructor()
            ->setMethods(['CreateTableSQL', 'ExecuteSQLArray', 'CreateIndexSQL'])
            ->getMock();

        $ado->method('Execute')->willReturn(true);
        $dict->method('CreateTableSQL')->willReturn('sql');
        $dict->method('CreateIndexSQL')->willReturn('sql');
        $dict->method('ExecuteSQLArray')->willReturn([1]);

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('QueryLimit')->willReturn(true);
        $mockDb->method('QueryFetchAll')->willReturn([]);
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false, true, false, true, false);

        $mockDb->method('GetADODBObject')->willReturn($ado);
        $mockDb->method('GetADODBDictionaryObject')->willReturn($dict);

        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'parentdepartmentid' => 0,
            'flagtype' => 1,
            'isresolved' => 1,
            'staffid' => 1,
            'ticketviewid' => 1,
            'slaholidayid' => 1,
            'slaplanid' => 1,
            'slaid' => 1,
            'ruletype' => 1,
            'ticketworkflowid' => 1,
            'customfieldgroupid' => 1,
            'customfieldid' => 1,
            'grouptype' => 1,
            'fieldtype' => 1,
            'autocloseruleid' => 1,
            '_criteria' => [1],
            'isenabled' => 1,
            'targetticketstatusid' => 1,
            'markasresolved' => 1,
            'inactivitythreshold' => 0,
            'title' => 1,
            'sendpendingnotification' => 0,
            'closurethreshold' => 0,
            'userid' => 1,
            'userdesignation' => '',
            'salutation' => '',
            'fullname' => 'fullname',
            'ticketslaplanid' => '0',
            'firstresponsetime' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'ticketpostid' => 1,
            'staffgroupid' => 1,
            'duetime' => 0,
            'resolutionduedateline' => 0,
        ];
        $mockDb->method('QueryFetch')->willReturn($arr);
        $mockDb->Record = $arr;
        $_SWIFT->Database = $mockDb;
        $obj->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'departmentcache') {
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    2 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    3 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                    4 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                ];
            }

            if ($x === 'staffcache') {
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                    ],
                    2 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '0',
                    ],
                ];
            }

            if ($x === 'groupassigncache') {
                return [
                    1 => [
                        1 => 1,
                        3 => 3,
                    ],
                ];
            }

            if ($x === 'tickettypecache' || $x === 'statuscache') {
                return [
                    1 => [
                        1 => 1,
                        'departmentid' => 2,
                    ],
                    2 => [
                        'departmentid' => 0,
                    ],
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

        $this->assertTrue($obj->Install());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInstallSampleDataReturnsTrue()
    {
        $obj = $this->getMocked();

        if (!defined('INSTALL_SAMPLE_DATA')) {
            define(INSTALL_SAMPLE_DATA, true);
        }

        $dept = $this->getMockBuilder('Base\Models\Department\SWIFT_Department')
            ->disableOriginalConstructor()
            ->getMock();

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'parentdepartmentid' => 0,
            'customfieldgroupid' => 1,
            'customfieldid' => 1,
            'grouptype' => 1,
            'fieldtype' => 1,
            'userid' => 1,
            'userdesignation' => '',
            'salutation' => '',
            'fullname' => 'fullname',
            'ticketslaplanid' => '0',
            'slaplanid' => '0',
            'firstresponsetime' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'ticketpostid' => 1,
            'staffgroupid' => 1,
            'duetime' => 0,
            'resolutionduedateline' => 0,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturn($arr);
        $_SWIFT->Database->Record = $arr;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'departmentcache') {
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    2 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    3 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                    4 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                ];
            }

            if ($x === 'staffcache') {
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                    ],
                    2 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '0',
                    ],
                ];
            }

            if ($x === 'groupassigncache') {
                return [
                    1 => [
                        1 => 1,
                        3 => 3,
                    ],
                ];
            }

            if ($x === 'tickettypecache' || $x === 'statuscache') {
                return [
                    1 => [
                        1 => 1,
                        'departmentid' => 2,
                    ],
                    2 => [
                        'departmentid' => 0,
                    ],
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

        $this->assertTrue($obj->InstallSampleData(1, $dept, $staff, [1], [1], 1, 1, 1));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUninstallReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $ado = $this->getMockBuilder('ADOConnection')
            ->disableOriginalConstructor()
            ->setMethods(['Execute'])
            ->getMock();

        $dict = $this->getMockBuilder('ADODB_DataDict')
            ->disableOriginalConstructor()
            ->setMethods(['DropTableSQL', 'ExecuteSQLArray'])
            ->getMock();

        $ado->method('Execute')->willReturn(true);
        $dict->method('DropTableSQL')->willReturn([1]);
        $dict->method('ExecuteSQLArray')->willReturn([1]);

        $_SWIFT->Database->method('GetADODBObject')->willReturn($ado);
        $_SWIFT->Database->method('GetADODBDictionaryObject')->willReturn($dict);

        $this->assertTrue($obj->Uninstall());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgradeReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Upgrade());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_00_911ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $this->assertTrue($obj->Upgrade_4_00_911(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_00_911');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_00_932ReturnsTrue()
    {
        $obj = $this->getMocked();

        static::$nextRecordLimit = 2;

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketstatusid' => 1,
            'markasresolved' => 2,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturn($arr);
        $_SWIFT->Database->Record = $arr;

        static::$databaseCallback['NextRecord'] = function () use ($_SWIFT) {
            $_SWIFT->Database->Record['markasresolved']--;
        };

        $this->assertTrue($obj->Upgrade_4_00_932(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_00_932');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_01_191ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $this->assertTrue($obj->Upgrade_4_01_191(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_01_191');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_01_218ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $this->assertTrue($obj->Upgrade_4_01_218(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_01_218');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_50_1637ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $this->assertTrue($obj->Upgrade_4_50_1637(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_50_1637');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_60_0_3972ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $this->assertTrue($obj->Upgrade_4_60_0_3972(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_60_0_3972');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_90_0000ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $this->assertTrue($obj->Upgrade_4_90_0000(),
            'Returns true with permission');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_90_0000');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_SetupDatabase_ticketsMock
     */
    private function getMocked()
    {
        $mock = new ManagerMock();
        return $this->getMockObject('Tickets\SWIFT_SetupDatabase_ticketsMock', [
            'TemplateManager' => $mock,
            'LanguageManager' => $mock,
            'SettingsManager' => $mock,
        ]);
    }
}

class ManagerMock {
    public function DeleteOnApp() {
        return true;
    }
}

class SWIFT_SetupDatabase_ticketsMock extends SWIFT_SetupDatabase_tickets
{
    public $Database;
    public $Cache;

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

