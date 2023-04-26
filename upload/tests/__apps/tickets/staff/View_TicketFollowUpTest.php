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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_ViewTest
 * @group tickets
 * @group tickets-staff
 */
class View_TicketFollowUpTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderFollowUpReturnsTrue()
    {
        $_SWIFT = \SWIFT::GetInstance();
        $obj = $this->getMocked();

        $mock = $obj->getTicketMock($this);
        $mock2 = $obj->getUserMock($this);

        $this->expectOutputRegex('/script/');

        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $cache->method('Get')->willReturn(
            [
                1 => [1 => 1, 'bgcolorcode' => '#ffffff'],
            ]
        );
        $obj->Cache = $cache;

        // advance permission
        $_SWIFT->Staff->GetPermission('staff_canupdatetags');

        $_SWIFT->Database->Record = [
            'ticketfollowupid' => 1,
            'departmentid' => 1,
            'dochangeproperties' => 1,
            'doforward' => 1,
            'donote' => 1,
            'doreply' => 1,
            'executiondateline' => 1,
            'forwardemailto' => 1,
            'notetype' => 1,
            'ownerstaffid' => 1,
            'priorityid' => 1,
            'staffid' => 1,
            'ticketstatusid' => 1,
            'tickettypeid' => 1,
        ];

        $this->assertTrue($obj->RenderFollowUp($mock, $mock2));

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();
        $settings->method('Get')->willReturn('0');
        $_SWIFT->Settings = $settings;

        $this->assertTrue($obj->RenderFollowUp($mock, $mock2),
            'Returns true with t_tinymceeditor = 0');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderFollowUp($mock, $mock2),
            'Returns false if class is not loaded');
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderFollowUpEntriesReturnsHtml() {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('RenderFollowUpEntries');
        $method->setAccessible(true);

        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Database->Record = [
            'ticketfollowupid' => 1,
            'departmentid' => 1,
            'dochangeproperties' => 1,
            'doforward' => 1,
            'donote' => 1,
            'doreply' => 1,
            'executiondateline' => 1,
            'forwardemailto' => 1,
            'notetype' => 'ticket',
            'ownerstaffid' => 0,
            'priorityid' => 1,
            'staffid' => 1,
            'ticketstatusid' => 1,
            'tickettypeid' => 1,
        ];

        $mock = $obj->getTicketMock($this);
        $this->assertContains('javascript', $method->invoke($obj, $mock));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $mock);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|View_TicketMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Staff\View_TicketMock');
    }
}
