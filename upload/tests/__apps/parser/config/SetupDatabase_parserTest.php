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
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Parser;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class SetupDatabase_parserTest
 * @group parser
 * @group parser-config
 */
class SetupDatabase_parserTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\SWIFT_SetupDatabase_parser', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testLoadTablesReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->LoadTables(), 'Returns true');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPageCountReturnsInt()
    {
        $obj = $this->getMocked();

        $this->assertEquals(1, $obj->GetPageCount(), 'Returns int');
    }

    protected function getMockDB()
    {
        $ado = $this->getMockBuilder('ADOConnection')
            ->disableOriginalConstructor()
            ->setMethods(['Execute'])
            ->getMock();
        $dict = $this->getMockBuilder('ADODB_DataDict')
            ->disableOriginalConstructor()
            ->setMethods(['CreateTableSQL', 'DropTableSQL', 'ExecuteSQLArray', 'CreateIndexSQL'])
            ->getMock();
        $ado->method('Execute')->willReturn(true);
        $dict->method('CreateTableSQL')->willReturn('sql');
        $dict->method('DropTableSQL')->willReturn([]);
        $dict->method('CreateIndexSQL')->willReturn('sql');
        $dict->method('ExecuteSQLArray')->willReturn(1);
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

        $record = ['parserruleid' => 1, 'matchtype' => 1];

        $mockDb->method('QueryFetch')->willReturn($record);
        $mockDb->Record = $record;

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

        $this->assertTrue($obj->Install(1), 'Returns true');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInstallSampleDataReturnsTrue()
    {
        $obj = $this->getMocked();

        if (function_exists('uopz_undefine')) {
            uopz_undefine('INSTALL_SAMPLE_DATA');
            $this->assertFalse($obj->InstallSampleData(), 'Returns false');
        }

        define('INSTALL_SAMPLE_DATA', true);
        $_POST['producturl'] = 'http://test.opencart.com.vn';

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['emailqueueid' => 1, 'queuesignatureid' => 1, 'type' => APP_NEWS]);

        $this->assertTrue($obj->InstallSampleData(), 'Returns true');
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

        $this->assertTrue($obj->Uninstall(), 'Returns true');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgradeReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Upgrade(), 'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_00_911ReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->Record = ['name' => 'test', 'typedata' => 'test'];

        $this->assertTrue($obj->Upgrade_4_00_911(), 'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_00_911');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_01_341ReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->Record = ['parserruleid' => 1];

        $this->assertTrue($obj->Upgrade_4_01_341(), 'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_01_341');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_92_4ReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->Record = ['userpassword' => 'test'];

        $this->assertTrue($obj->Upgrade_4_92_4(), 'Returns true');

        $this->assertClassNotLoaded($obj, 'Upgrade_4_92_4');
    }

    /**
     *
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_97_6ReturnsTrue()
    {
        $obj = $this->getMocked();
        \SWIFT::GetInstance()->Database->Record = ['table_name' => 'mailmessageid'];
        $this->assertTrue($obj->Upgrade_4_97_6(), 'Returns true');
        $this->assertClassNotLoaded($obj, 'Upgrade_4_97_6');
    }

	/**
	 *
	 * @throws SWIFT_Exception
	 */
	public function testUpgrade_4_98_4ReturnsTrue()
	{
		$obj = $this->getMocked();
		\SWIFT::GetInstance()->Database->Record = ['COLUMN_NAME' => 'actionmsgparams'];
		$this->assertTrue($obj->Upgrade_4_98_4(), 'Returns true');
		$this->assertClassNotLoaded($obj, 'Upgrade_4_98_4');
	}

    /**
     *
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_98_6ReturnsTrue()
    {
        $obj = $this->getMocked();
        \SWIFT::GetInstance()->Database->Record = ['table_name' => 'perflog'];
        $this->assertTrue($obj->Upgrade_4_98_6(), 'Returns true');
        $this->assertClassNotLoaded($obj, 'Upgrade_4_98_6');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_SetupDatabase_parserMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\SWIFT_SetupDatabase_parserMock');
    }
}

class SWIFT_SetupDatabase_parserMock extends SWIFT_SetupDatabase_parser
{
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
