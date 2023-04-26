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

namespace Troubleshooter;

use SWIFT;
use SWIFT_Exception;
use Troubleshooter\Admin\LoaderMock;

/**
 * Class SetupDatabase_troubleshooterTest
 * @group troubleshooter
 */
class SetupDatabase_troubleshooterTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        SWIFT::GetInstance()->Load = new LoaderMock();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = new SetupDatabaseMock();
        $this->assertInstanceOf('Troubleshooter\SWIFT_SetupDatabase_troubleshooter', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPageCountReturnsOne()
    {
        $obj = new SetupDatabaseMock();
        $this->assertEquals($obj->GetPageCount(), SWIFT_SetupDatabase_troubleshooter::PAGE_COUNT);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgradeThrowsException() {
        $obj = new SetupDatabaseMock();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Upgrade();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUninstallReturnsTrue() {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockAdo = $this->getMockBuilder('ADODBDictionary')
            ->disableOriginalConstructor()
            ->setMethods(['DropTableSQL', 'ExecuteSQLArray'])
            ->getMock();

        $mockAdo->method('DropTableSQL')
            ->willReturn([]);

        $mockAdo->method('ExecuteSQLArray')
            ->willReturn(false);

        $mockDb->method('GetADODBDictionaryObject')
            ->willReturn($mockAdo);

        \SWIFT::GetInstance()->Database = $mockDb;

        $obj = new SetupDatabaseMock();
        $this->assertTrue($obj->Uninstall());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInstallReturnsTrue() {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')
            ->willReturnArgument(0);

        $mockDb->method('Insert_ID')
            ->willReturn(true);

        \SWIFT::GetInstance()->Database = $mockDb;

        $obj = new SetupDatabaseMock();
        $this->mockProperty($obj, 'Language', $mockLang);
        $this->assertTrue($obj->Install(-1));
    }

}

class SetupDatabaseMock extends SWIFT_SetupDatabase_troubleshooter {
    protected function LoadModels() {
        // do nothing
    }
}
