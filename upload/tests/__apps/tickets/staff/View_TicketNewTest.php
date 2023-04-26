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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;
use SWIFT_Settings;

/**
 * Class View_ViewTest
 * @group tickets
 * @group tickets-staff
 */
class View_TicketNewTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderNewTicketDialogReturnsTrue()
    {
        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $cache->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'departmentcache') {
                return [
                    1 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    2 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '0',
                    ],
                    3 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                    4 => [
                        'departmentapp' => 'tickets',
                        'parentdepartmentid' => '1',
                        'departmenttype' => false,
                    ],
                ];
            }

            if ($x === 'staffcache') {
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                    ],
                    2 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '0',
                    ],
                ];
            }

            if ($x === 'groupassigncache') {
                return [
                    1 => [
                        1 => 1,
                        3 => 3,
                    ],
                ];
            }

            if ($x === 'tickettypecache' || $x === 'statuscache') {
                return [
                    1 => [
                        1 => 1,
                    ],
                    2 => [
                        'departmentid' => 2,
                    ],
                ];
            }

            return [
                1 => [
                    1 => [1 => [1]],
                ],
            ];
        });

        $obj = $this->getMocked([
            'Cache' => $cache,
        ]);

        \SWIFT::GetInstance()->Cache = $cache;

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1, 3]);

        $this->assertTrue($obj->RenderNewTicketDialog(1));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderNewTicketDialog(1),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderNewTicketReturnsTrue()
    {
        $obj = $this->getMocked();

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetAssignedDepartments')->willReturn([1]);
        \SWIFT::GetInstance()->Staff = $staff;

        \SWIFT::GetInstance()->Language->method('GetLanguageCode')->willReturnOnConsecutiveCalls('sv', 'ru');

        $this->expectOutputRegex('/<script>/');

        $obj->_doRenderDispatch = false;

        $this->assertTrue($obj->RenderNewTicket(3, 1),
            'Returns true with type = email');

        $this->assertTrue($obj->RenderNewTicket(1, 1),
            'Returns true with other type');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderNewTicket(1, 1),
            'Returns false if class is not loaded');
    }

    public function testTinyMCE()
    {
	    $obj = $this->getMocked();
	    $_swift = \SWIFT::GetInstance();

	    $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
		    ->disableOriginalConstructor()
		    ->getMock();
	    $staff->method('GetPermission')->willReturn(1);
	    $staff->method('GetAssignedDepartments')->willReturn([1]);
	    $_swift->Staff = $staff;
	    $_swift->Language->method('GetLanguageCode')->willReturn('en');

	    $settings = $this->getMockBuilder('SWIFT_Settings')
		    ->disableOriginalConstructor()
		    ->getMock();

	    $obj->_doRenderDispatch = false;

		foreach (['https://localhost', 'https://localhost/'] as $testDomain) {
			$settings->method('Get')->willReturnCallback(function ($x) use ($testDomain) {
				if ($x == 'general_producturl')
					return $testDomain;
				return 1;
			});

			\SWIFT::GetInstance()->Settings = $settings;

			ob_start();
			$obj->RenderNewTicket(3, 1);
			$output = ob_get_clean();
			$this->assertTrue(strpos($output, 'https://localhost/__swift/apps/base/javascript/__global/thirdparty/TinyMCE/tinymce.min.js') !== false);
			$this->assertTrue(strpos($output, 'https://localhost/__swift/apps/base/javascript/__global/thirdparty/TinyMCE/') !== false);
        }
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_TicketMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\View_TicketMock', $services);
    }
}
