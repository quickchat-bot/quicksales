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
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace News\Admin;

use SWIFT;

/**
 * Class Controller_ImpExTest
 * @group news
 */
class Controller_ImpExTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDb->method('QueryFetch')->willReturn([
            'newssubscriberid' => 1,
        ]);

        $this->mockProperty($mockDb, 'Record', [
            'email' => 'me@mail.com',
            'linktypeid' => '1',
        ]);

        SWIFT::GetInstance()->Database = $mockDb;

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetProperty')->willReturnArgument(0);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetPermission')
            ->willReturnOnConsecutiveCalls('0', '1', '0', '1');

        SWIFT::GetInstance()->Staff = $mockStaff;

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->method('GetIsClassLoaded')->willReturn(true);
        $mockSession->method('GetProperty')->willReturnArgument(0);

        SWIFT::GetInstance()->Session = $mockSession;
    }

    public function getController()
    {
        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockView = $this->getMockBuilder('News\Admin\View_ImpEx')
            ->disableOriginalConstructor()
            ->getMock();

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        return new Controller_ImpExMock([
            'Language' => $mockLang,
            'UserInterface' => $mockInt,
            'View' => $mockView,
        ]);
    }

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('News\Admin\Controller_ImpEx', $obj);
    }

    public function testManageReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertTrue($obj->Manage(true));
        $this->assertTrue($obj->Manage('n'));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Manage());
    }

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
        $_POST['emails'] = 'me2@mail.com';
        $this->assertFalse($obj->Import(),
            'Returns false with admin_nwcanupdatesubscriber = 0');

        $this->assertTrue($obj->Import(),
            'Returns true with admin_nwcanupdatesubscriber = 1');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Import());
    }

    public function testExportReturnsTrue()
    {
        $obj = $this->getController();
        $this->assertFalse($obj->Export(1),
            'Returns false with admin_nwcanupdatesubscriber = 0');

        $this->expectOutputRegex('/.*me@mail.com.*/');
        $this->assertTrue($obj->Export(1),
            'Returns true with admin_nwcanupdatesubscriber = 1');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Export(1));
    }
}

class Controller_ImpExMock extends Controller_ImpEx
{
    /**
     * Controller_ImpExMock constructor.
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();

        parent::__construct();

        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
    }
}
