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
class View_TicketBoxesTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderWorkflowBoxReturnsTrue()
    {
        $obj = $this->getMocked();
        $mock = $obj->getTicketMock($this);

        $this->assertFalse($obj->RenderWorkflowBox($mock, []),
            'Returns false without LINKTYPE_WORKFLOW');

        $this->assertFalse($obj->RenderWorkflowBox($mock, []),
            'Returns falsw with staff_tcanworkflow = 0');

        \SWIFT::GetInstance()->Staff->method('GetPermission')->willReturn(1);

        $this->assertTrue($obj->RenderWorkflowBox($mock, [
            1 => [
                [
                    'linktypeid' => 1,
                ],
            ],
        ]));

        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $cache->method('Get')->willReturn(
            [
                1 => [1 => 1, 'staffvisibilitycustom' => 1],
            ]
        );
        $obj->Cache = $cache;

        $this->assertTrue($obj->RenderWorkflowBox($mock, [
            1 => [
                [
                    'linktypeid' => 0,
                ],
                [
                    'linktypeid' => 1,
                ],
            ],
        ]));

        $this->assertClassNotLoaded($obj, 'RenderWorkflowBox', $mock, []);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderParticipantBoxReturnsTrue()
    {
        $obj = $this->getMocked();
        $mock = $obj->getTicketMock($this);

        $this->assertFalse($obj->RenderParticipantBox($mock, []));

        $this->assertTrue($obj->RenderParticipantBox($mock, [1, 2, 3]));

        $this->assertClassNotLoaded($obj, 'RenderParticipantBox', $mock, []);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderInfoBoxReturnsTrue()
    {
        $obj = $this->getMocked();
        $mock = $obj->getTicketMock($this);
        $mock2 = $obj->getUserMock($this);
        $mock3 = $obj->getUserOrgMock($this);

        $this->assertTrue($obj->RenderInfoBox($mock, $mock2, $mock3));

        $this->assertClassNotLoaded($obj, 'RenderInfoBox', $mock, $mock2, $mock3);
    }

    /**
     * @throws SWIFT_Exception
     * @throws \Base\Library\Tag\SWIFT_Tag_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     */
    public function testRenderReleaseReturnsTrue()
    {
        $obj = $this->getMocked();

        $mock = $obj->getTicketMock($this);
        $mock2 = $obj->getUserMock($this);

        $this->expectOutputRegex('/relbillingtimebillable/');

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
        \SWIFT::GetInstance()->Staff->GetPermission('staff_canupdatetags');

        $this->assertTrue($obj->RenderRelease($mock, $mock2));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderRelease($mock, $mock2),
            'Returns false if class is not loaded');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|View_TicketMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Staff\View_TicketMock');
    }
}
