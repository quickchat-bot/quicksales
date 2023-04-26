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

namespace Knowledgebase;

use SWIFT;
use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class SetupDatabase_knowledgebaseTest
 * @group knowledgebase
 */
class SetupDatabase_knowledgebaseTest extends \SWIFT_TestCase
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
        $this->assertInstanceOf('Knowledgebase\SWIFT_SetupDatabase_knowledgebase', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPageCountReturnsOne()
    {
        $obj = new SetupDatabaseMock();
        $this->assertEquals($obj->GetPageCount(), SWIFT_SetupDatabase_knowledgebase::PAGE_COUNT);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgradeThrowsException()
    {
        $obj = new SetupDatabaseMock();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Upgrade();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUninstallReturnsTrue()
    {
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
     * @throws \Base\Models\Widget\SWIFT_Widget_Exception
     */
    public function testInstallReturnsTrue()
    {
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

    /**
     * @throws SWIFT_Exception
     */
    public function testInstallSampleDataReturnsTrue()
    {
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

        $mockDb->method('QueryFetch')
            ->willReturn([
                'knowledgebaseitemid' => 1,
                'knowledgebasetype' => 1,
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

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_01_204ReturnsTrue()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false);

        $mockDb->Record = [
          'kbarticleid' => 1,
          'kbarticledataid' => 1,
          'subject' => 'subject',
          'contentstext' => 'contentstext',
        ];

        \SWIFT::GetInstance()->Database = $mockDb;

        $obj = new SetupDatabaseMock();
        $this->mockProperty($obj, 'Database', $mockDb);
        $this->assertTrue($obj->Upgrade_4_01_204());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Upgrade_4_01_204();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpgrade_4_92_3ReturnsTrue()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false);

        $mockDb->Record = [
          'kbarticleid' => 1,
          'subject' => 'subject',
        ];

        \SWIFT::GetInstance()->Database = $mockDb;

        $obj = new SetupDatabaseMock();
        $this->mockProperty($obj, 'Database', $mockDb);
        $this->expectOutputRegex('/Updating articles complete/');
        $this->assertTrue($obj->Upgrade_4_92_3());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Upgrade_4_92_3();
    }
}

class SetupDatabaseMock extends SWIFT_SetupDatabase_knowledgebase
{

    /**
     * SetupDatabaseMock constructor.
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct('knowledgebase');
    }

    protected function LoadModels()
    {
        // do nothing
    }
}
