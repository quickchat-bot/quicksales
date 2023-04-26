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

namespace Knowledgebase\Admin;

use SWIFT_Exception;

/**
 * Class Controller_MaintenanceTest
 * @group knowledgebase
 */
class Controller_MaintenanceTest extends \SWIFT_TestCase
{
    /**
     * @return Controller_MaintenanceMock
     * @throws SWIFT_Exception
     */
    public function getController() {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);

        $mockDb->Record = [
          'kbarticleid' => 1,
          'kbarticledataid' => 1,
          'subject' => 'subject',
          'contentstext' => 'contentstext',
        ];

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')
            ->willReturnOnConsecutiveCalls('0', '1');

        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Staff = $mockStaff;
        $_SWIFT->Database = $mockDb;

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockView = $this->getMockBuilder('Knowledgebase\Admin\View_Maintenance')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_MaintenanceMock($mockLang, $mockView, $mockDb);
        $obj->SetIsClassLoaded(true);
        $this->mockProperty($obj, 'UserInterface', $mockInt);

        return $obj;
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = new Controller_Maintenance();
        $this->assertInstanceOf('Knowledgebase\Admin\Controller_Maintenance', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->Index(),
            'Returns true with admin_tcanrunmaintenance = 0');

        $this->assertTrue($obj->Index(),
            'Returns true with admin_tcanrunmaintenance = 1');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $obj->Index();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testReIndexReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertTrue($obj->ReIndex(0, 'no'));

        $this->assertTrue($obj->ReIndex(1, 1, 0, 1, 0));
        $this->assertTrue($obj->ReIndex(1, 2, 0, 1, 0));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->ReIndex(0);
    }
}

class Controller_MaintenanceMock extends Controller_Maintenance
{
    protected $SettingsManager;

    public function __construct($lang, $view, $db)
    {
        $this->Load = new LoaderMock();
        $this->Language = $lang;
        $this->View = $view;
        $this->Database = $db;

        parent::__construct();
    }

    public function Initialize()
    {
        return true;
    }
}
