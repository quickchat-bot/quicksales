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

namespace News;

use SWIFT;
use News\Admin\LoaderMock;

/**
 * Class SetupDatabase_newsTest
 * @group news
 */
class SetupDatabase_newsTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        SWIFT::GetInstance()->Load = new LoaderMock();
    }

    public function testConstructorReturnsClassInstance()
    {
        $obj = new SetupDatabaseMock();
        $this->assertInstanceOf('News\SWIFT_SetupDatabase_news', $obj);
    }

    public function testPageCountReturnsOne()
    {
        $obj = new SetupDatabaseMock();
        $this->assertEquals($obj->GetPageCount(), SWIFT_SetupDatabase_news::PAGE_COUNT);
    }

    public function testUpgradeThrowsException() {
        $obj = new SetupDatabaseMock();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Upgrade();
    }

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

    public function testInstallReturnsTrue() {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturn([
            'newsitemid' => 1,
            'newstype' => 1,
            'contents' => 'contents',
        ]);

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')
            ->willReturnArgument(0);

        $mockDb->method('Insert_ID')
            ->willReturn(1);

        \SWIFT::GetInstance()->Database = $mockDb;

        $obj = new SetupDatabaseMock();
        $this->mockProperty($obj, 'Language', $mockLang);
        $this->assertTrue($obj->Install(-1));
    }

    public function testInstallSampleDataReturnsTrue() {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')
            ->willReturnArgument(0);

        $mockDb->method('Insert_ID')
            ->willReturn(1);

        $mockDb->method('QueryFetch')
            ->willReturn([
                'newsitemid' => 1,
                'newstype' => 1,
                'contents' => 'contents',
            ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $obj = new SetupDatabaseMock();
        $this->mockProperty($obj, 'Language', $mockLang);

        if (function_exists('uopz_undefine')) {
            uopz_undefine('INSTALL_SAMPLE_DATA');
            $this->assertFalse($obj->InstallSampleData());
        }
        define('INSTALL_SAMPLE_DATA', true);
        $this->assertTrue($obj->InstallSampleData());
    }
}

class SetupDatabaseMock extends SWIFT_SetupDatabase_news {
    protected function LoadModels() {
        // do nothing
    }
}
