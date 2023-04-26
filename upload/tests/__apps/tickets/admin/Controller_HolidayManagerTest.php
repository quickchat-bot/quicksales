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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
* Class Controller_HolidayManagerTest
* @group tickets
*/
class Controller_HolidayManagerTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_HolidayManager', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getController();
        $view = $this->getMockBuilder('Tickets\Admin\View_HolidayManager')
            ->disableOriginalConstructor()
            ->getMock();
        $obj->View = $view;

        $this->assertTrue($obj->Index(),
            'Returns true with admin_tcanimpexslaholidays = 1');

        $this->assertTrue($obj->Index(),
            'Returns true with admin_tcanimpexslaholidays = 0');

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExportReturnsTrue()
    {
        $obj = $this->getController();
        $arr = [];

        SWIFT::Set('isdemo', true);
        $this->assertFalse($obj->Export($arr),
            'Returns false if is demo');

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('0', '1', '1');
        SWIFT::GetInstance()->Staff = $mockStaff;

        SWIFT::Set('isdemo', false);
        $this->assertFalse($obj->Export($arr),
            'Returns false with admin_tcanimpexslaholidays = 0');

        $this->assertTrue($obj->Export($arr),
            'Returns true with admin_tcanimpexslaholidays = 1');

        $arr['_exportFileName'] = 'file';
        $this->assertTrue($obj->Export($arr),
            'Returns true with file provided');

        $this->assertClassNotLoaded($obj, 'Export', $arr);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testImportReturnsTrue()
    {
        $obj = $this->getController();

        $this->assertFalse($obj->Import(),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';
        SWIFT::Set('isdemo', true);

        $this->assertFalse($obj->Import(),
            'Returns false if is demo');

        SWIFT::Set('isdemo', false);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('0', '1', '1');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        SWIFT::GetInstance()->Staff = $mockStaff;

        $this->assertFalse($obj->Import(),
            'Returns false with admin_tcanimpexslaholidays = 0');

        $this->assertTrue($obj->Import(),
            'Returns true with admin_tcanimpexslaholidays = 1');

        $file = __DIR__ . 'test.txt';
        file_put_contents($file, 'test');
        $_FILES['slaholidayfile']['tmp_name'] = $file;
        $this->assertTrue($obj->Import(),
            'Returns true with file and import result empty');
        $this->assertTrue($obj->Import(),
            'Returns true with file and import result not empty');
        unlink($file);

        $this->assertClassNotLoaded($obj, 'Import');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_HolidayManagerMock
     */
    private function getController()
    {
        $mgr = $this->getMockBuilder('Tickets\Library\SLA\SWIFT_SLAHolidayManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mgr->method('Import')->willReturnOnConsecutiveCalls(false, [1]);

        return $this->getMockObject('Tickets\Admin\Controller_HolidayManagerMock', [
            'SLAHolidayManager' => $mgr,
        ]);
    }
}

class Controller_HolidayManagerMock extends Controller_HolidayManager
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

