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

/**
 * Class Controller_SettingsManagerTest
 * @group knowledgebase
 */
class Controller_SettingsManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws \SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = new Controller_SettingsManager();
        $this->assertInstanceOf('Knowledgebase\Admin\Controller_SettingsManager', $obj);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testIndexReturnsTrue()
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

        $obj = new Controller_SettingsManagerMock($mockLang);
        $obj->SetIsClassLoaded(true);
        $this->mockProperty($obj, 'UserInterface', $mockInt);
        $this->mockProperty($obj, 'SettingsManager', $mockMgr);

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

    public function __construct($lang)
    {
        $this->Load = new LoaderMock();
        $this->Language = $lang;

        parent::__construct();
    }

    public function Initialize()
    {
        return true;
    }
}

class LoaderMock
{
    public $Load;
    protected $obj;

    /**
     * LoaderMock constructor.
     * @param Object|null $obj loader class
     */
    public function __construct($obj = null)
    {
        $this->obj = $obj;
    }

    public function __destruct()
    {
        $this->obj = null;
    }

    public function Library($_libraryName, $_arguments = array(), $_initiateInstance = true, $_customAppName = false, $_appName = '')
    {
        return $_libraryName;
    }

    public function Manage()
    {
        return true;
    }

    public function Method($_methodName = '')
    {
        return true;
    }

    public function Index()
    {
        return true;
    }

    public function Insert()
    {
        return true;
    }

    public function Edit()
    {
        return true;
    }

    public function View($_viewName = '')
    {
        return true;
    }

    public function Model($_modelName = '', $_arguments = array(), $_initiateInstance = true, $_customAppName = false, $appName = '')
    {
        return true;
    }

    public function Redirect()
    {
        return true;
    }

    public function InsertTicket()
    {
        return true;
    }

    public function NewTicket()
    {
        return true;
    }

    public function Search()
    {
        return true;
    }

    public function NewTicketForm()
    {
        return true;
    }

    public function RenderForm()
    {
        return true;
    }

    /**
     * @param $_hasAttachments
     * @return array
     */
    public function CheckForValidAttachments($_hasAttachments)
    {
        if ($this->obj !== null && method_exists($this->obj, 'CheckForValidAttachments')) {
            return $this->obj->CheckForValidAttachments($_hasAttachments);
        }

        return $this->obj->_getAttachmentsReturnValue ?: [false, ['error']];
    }

    public function Controller($_controllerName = '', $_customApp = '')
    {
        $this->Load = $this;

        return $this;
    }
}
