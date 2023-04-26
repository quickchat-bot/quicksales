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
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_SettingsManagerTest
 * @group parser
 * @group parser-admin
 */
class Controller_SettingsManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\Controller_SettingsManager', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getMocked();

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturn('0', '1', '1');

        \SWIFT::GetInstance()->Staff = $mockStaff;

        $this->assertTrue($obj->Index(),
            'Returns true');

        $this->assertTrue($obj->Index(),
            'Returns true');

        $_POST['pr_sizelimit'] = 1;

        $this->assertTrue($obj->Index(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_SettingsManagerMock
     */
    private function getMocked()
    {
        $settingManagerMock = $this->getMockBuilder(\SWIFT_SettingsManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settingManagerMock->method('Render')->willReturn(true);

        return $this->getMockObject('Parser\Admin\Controller_SettingsManagerMock', ['SettingsManager' => $settingManagerMock]);
    }
}

class Controller_SettingsManagerMock extends Controller_SettingsManager
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

