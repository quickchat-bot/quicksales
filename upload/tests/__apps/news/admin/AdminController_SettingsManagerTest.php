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

namespace News\Admin;

/**
 * Class AdminController_SettingsManagerTest
 * @group news
 */
class AdminController_SettingsManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws \SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = new Controller_SettingsManager();
        $this->assertInstanceOf('News\Admin\Controller_SettingsManager', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testIndexShowsView()
    {
        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff
            ->expects($this->exactly(2))
            ->method('GetPermission')
            ->willReturnOnConsecutiveCalls('0', '1');

        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Staff = $mockStaff;

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockMgr = $this->getMockBuilder('SWIFT_SettingsManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockMgr->method('Render')->willReturn(true);

        $obj = new Controller_SettingsManagerMock($mockLang, $mockMgr);
        $obj->SetIsClassLoaded(true);
        $this->mockProperty($obj, 'UserInterface', $mockInt);

        // will display error if permission = 0
        $this->assertTrue($obj->Index());

        // will display correctly if permission = 1
        $this->assertTrue($obj->Index());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $obj->Index();
    }
}

class Controller_SettingsManagerMock extends Controller_SettingsManager
{
    protected $SettingsManager;

    public function __construct($lang, $mgr)
    {
        $this->Load = new LoaderMock();
        $this->Language = $lang;
        $this->SettingsManager = $mgr;
    }
}

class LoaderMock
{
    public $Load;

    public function Library($name)
    {
        // empty
    }

    public function Model($name)
    {
        // empty
    }

    public function Manage()
    {
        // empty
    }

    public function Method()
    {
        // empty
    }

    public function Message()
    {
        // empty
    }

    public function Index()
    {
        // empty
    }

    public function Insert()
    {
        // empty
    }

    public function Edit()
    {
        // empty
    }

    public function Controller()
    {
        $this->Load = $this;

        return $this;
    }
}
