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

namespace Base;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class KQLSchema_baseTest
 * @group base
 */
class SetupDatabase_baseTest extends \SWIFT_TestCase
{
    public static $_count = 0;

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\SWIFT_SetupDatabase_base', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLoadTablesReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->LoadTables(),
            'Returns true');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPageCountReturnsNumber()
    {
        $obj = $this->getMocked();

        $this->assertEquals(SWIFT_SetupDatabase_baseMock::PAGE_COUNT, $obj->GetPageCount(),
            'Returns number');
    }

    protected function getMockDB()
    {
        $ado = $this->getMockBuilder('ADOConnection')
            ->disableOriginalConstructor()
            ->setMethods(['Execute', 'AutoExecute'])
            ->getMock();

        $dict = $this->getMockBuilder('ADODB_DataDict')
            ->disableOriginalConstructor()
            ->setMethods(['CreateTableSQL', 'ExecuteSQLArray', 'CreateIndexSQL', 'DropTableSQL'])
            ->getMock();

        $ado->method('Execute')->willReturn(true);
        $ado->method('AutoExecute')->willReturn(true);
        $dict->method('CreateTableSQL')->willReturn('sql');
        $dict->method('CreateIndexSQL')->willReturn('sql');
        $dict->method('ExecuteSQLArray')->willReturn([1]);
        $dict->method('DropTableSQL')->willReturn(['sql']);

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

        $mockDb->method('QueryFetch')->willReturnCallback(static function ($x) {
            $arr = [
                'usergroupid'        => 1,
                'staffgroupid'       => 1,
                'staffid'            => 1,
                'userorganizationid' => 1,
                'userid'             => 1,
                'useremailid'        => 1,
                'linktype'           => 1,
                'fullname'           => 'fullname',
            ];

            static::$_count++;
            if (in_array(static::$_count, [18, 19], true)) {
                $arr['linktype'] = 2;
            }

            return $arr;
        });

        return $mockDb;
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInstallReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $mockDb = $this->getMockDB();

        $_SWIFT->Database = $mockDb;
        $obj->Database = $mockDb;

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            if (false !== strpos($x, 'log')) {
                return '%s ';
            }

            if ($x === 'sample_useremailaddress')
                return 'test@test.com';

            return $x;
        });

        $_SWIFT->Language = $mockLang;
        $obj->Language = $mockLang;

        $_POST['firstname'] = 'Test';
        $_POST['lastname'] = 'Test';
        $_POST['username'] = 'Test';
        $_POST['password'] = 'Test';
        $_POST['companyname'] = 'Test';
        $_POST['email'] = 'test@test.com';


        $this->assertTrue($obj->Install(-1),
            'Returns true');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInstallSampleDataReturnsBool()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        if (function_exists('uopz_undefine')) {
            uopz_undefine('INSTALL_SAMPLE_DATA');

            $this->assertFalse($obj->InstallSampleData($_SWIFT->Staff, 1),
                'Returns false');
        }

        $count = 0;
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use (&$count) {
            $count++;

            if ($count <= 2)
                return [
                    'userorganizationid' => 1,
                    'useremailid' => 1,
                    'linktype' => 2,
                ];

            return [
                'usergroupid' => 1,
                'userorganizationid' => 1,
                'userid' => 1,
                'staffgroupid' => 1,
                'useremailid' => 1,
                'linktype' => 1,
            ];
        });

        define('INSTALL_SAMPLE_DATA', true);

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            if (false !== strpos($x, 'log')) {
                return '%s ';
            }

            if ($x === 'sample_useremailaddress')
                return 'test@test.com';

            return $x;
        });

        $_SWIFT->Language = $mockLang;
        $obj->Language = $mockLang;


        $this->assertTrue($obj->InstallSampleData($_SWIFT->Staff, 1),
            'Returns true');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUninstallReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $mockDb = $this->getMockDB();
        $_SWIFT->Database = $mockDb;
        $obj->Database = $mockDb;

        $this->assertTrue($obj->Uninstall(),
            'Returns true');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgradeReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Upgrade(),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_01_191ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Upgrade_4_01_191(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_01_191');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_01_176ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Upgrade_4_01_176(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_01_176');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_01_326ReturnsTrue()
    {
        $obj = $this->getMocked();

        $val = 'Low';

        self::$databaseCallback['SettingsGet'] = function ($x) use (&$val) {
            return $val;
        };

        $this->assertTrue($obj->Upgrade_4_01_326(),
            'Returns true');

        $val = 'High';

        $this->assertTrue($obj->Upgrade_4_01_326(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_01_326');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_01_342ReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $_SWIFT->Database->Record = [
            'staffid' => 1
        ];


        $this->assertTrue($obj->Upgrade_4_01_342(),
            'Returns true');

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        $this->assertTrue($obj->Upgrade_4_01_342(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_01_342');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_40_1149ReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $_SWIFT->Database->Record = [
            'staffgroupid' => 1
        ];

        $this->assertTrue($obj->Upgrade_4_40_1149(),
            'Returns true');

        static::$nextRecordType = static::NEXT_RECORD_NO_LIMIT;
        static::$nextRecordCount = 1;

        $this->assertTrue($obj->Upgrade_4_40_1149(),
            'Returns true');

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        $this->assertTrue($obj->Upgrade_4_40_1149(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_40_1149');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_65_0_5820ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Upgrade_4_65_0_5820(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_65_0_5820');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_60_0000ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Upgrade_4_60_0000(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_60_0000');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_70_0000ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Upgrade_4_70_0000(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_70_0000');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgradeEngineReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $this->assertTrue($obj->UpgradeEngine(),
            'Returns true');

        $_SWIFT->Database->method('QueryFetch')->willReturn(['version' => '5.7', 'ENGINE' => 'myisam']);

        $this->assertNull($obj->UpgradeEngine(),
            'Returns null');

        $this->assertClassNotLoaded($obj, 'UpgradeEngine');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_74_0000ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Upgrade_4_74_0000(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_74_0000');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_74_0001ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Upgrade_4_74_0001(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_74_0001');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_79_0000ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Upgrade_4_79_0000(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_79_0000');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_90_0000ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Upgrade_4_90_0000(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_90_0000');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_91_0000ReturnsTrue()
    {
        $obj = $this->getMocked();


        $this->assertTrue($obj->Upgrade_4_91_0000(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_91_0000');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_92_0ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Upgrade_4_92_0(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_92_0');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_SetupDatabase_baseMock
     */
    private function getMocked()
    {
        $mock = new ManagerMock;
        return $this->getMockObject('Base\SWIFT_SetupDatabase_baseMock', [
            'TemplateManager' => $mock,
            'LanguageManager' => $mock,
            'SettingsManager' => $mock,
        ]);
    }
}

class ManagerMock
{
    public function DeleteOnApp()
    {
        return true;
    }
}

class  SWIFT_SetupDatabase_baseMock extends SWIFT_SetupDatabase_base
{
    public $Database;
    public $Language;

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
