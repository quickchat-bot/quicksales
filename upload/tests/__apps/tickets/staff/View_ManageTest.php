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
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_ManageTest
 * @group tickets
 * @group tickets-staff
 */
class View_ManageTest extends \SWIFT_TestCase
{
    private $_originalSettings;

    private const OVERDUE_BACKGROUND_COLOR = '#FFECEC';
    private const OVERDUE_BACKGROUND_COLOR_STYLE =
        'background-color: ' . self::OVERDUE_BACKGROUND_COLOR . ' !important;';
    private const DEFAULT_OVERDUE_BACKGROUND_COLOR_STYLE = 'background-color: #FFFFFF !important;';
    private const TIMESTAMP_IN_THE_PAST = DATENOW - 24*60*60;
    private const TIMESTAMP_IN_THE_FUTURE = DATENOW + 24*60*60;

    /**
     * @beforeClass
     */
    protected function setUp(): void
    {
        $this->_originalSettings = \SWIFT::GetInstance()->Settings;
    }

    /**
     * @afterClass
     */
    protected function tearDown()
    {
        \SWIFT::GetInstance()->Settings = $this->_originalSettings;
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderGridReturnsTrue()
    {
        $ctr = $this->getMockBuilder('Tickets\Staff\Controller_Manage')
            ->disableOriginalConstructor()
            ->getMock();
        $grid = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceGrid')
            ->disableOriginalConstructor()
            ->getMock();
        $tag = $this->getMockBuilder('Base\Library\Tag\SWIFT_TagCloud')
            ->disableOriginalConstructor()
            ->getMock();
        $ctr->UserInterfaceGrid = $grid;
        $ctr->TagCloud = $tag;
        $obj = $this->getMocked([
            'Controller' => $ctr,
        ]);

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'ticketviewdepartmentlinkcache' || $x === 'staffgrouplinkcache') {
                return [];
            }

            return [
                1 => [
                    'ticketviewid' => 1,
                    'staffid' => 1,
                    'viewscope' => 1,
                    'viewalltickets' => 0,
                    'viewassigned' => 0,
                    'viewunassigned' => 0,
                    'afterreplyaction' => 1,
                    'title' => 1,
                    'sortby' => 1,
                    'sortorder' => 1,
                    'ticketsperpage' => 1,
                    'autorefresh' => 1,
                    'fields' => [
                        [
                            'ticketviewfieldid' => 1,
                            'fieldtypeid' => 1,
                            'fieldtype' => 2,
                        ],
                    ],
                ],
            ];
        });
        \SWIFT::GetInstance()->Cache = $mockCache;

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $this->expectOutputRegex('/javascript/');

        $this->assertTrue($obj->RenderGrid(1, 1, 1, 1, 1));
        $this->assertTrue($obj->RenderGrid(0, 1, 1, 1, 1));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderGrid(1, 1, 1, 1, 1));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $obj = $this->getMocked();

        \SWIFT::Set('_userOrganizationTicketListCustomFieldMap', [1 => [1]]);
        \SWIFT::Set('_userTicketListCustomFieldMap', [1 => [1]]);
        \SWIFT::Set('massreplyticketidlist', [1 => 1]);
        \SWIFT::Set('tickettreedepartmentid', 1);
        \SWIFT::Set('tickettreelisttype', 1);
        \SWIFT::Set('tickettreestatusid', 1);
        \SWIFT::Set('tickettreetypeid', 1);
        $fieldContainer = [
            'departmentid' => 1,
            'departmenttitle' => 1,
            'duetime' => 1,
            'email' => 1,
            'escalatedtime' => 1,
            'fullname' => 1,
            'hasattachments' => 1,
            'hasbilling' => 1,
            'hasfollowup' => 1,
            'hasnotes' => 1,
            'isescalated' => 1,
            'islinked' => 1,
            'isphonecall' => 1,
            'lastactivity' => 1,
            'lastreplier' => 1,
            'laststaffreplytime' => 1,
            'lastuserreplytime' => 1,
            'lockstaffid' => 1,
            'ownerstaffid' => 1,
            'prioritytitle' => 1,
            'resolutionduedateline' => 1,
            'subject' => 1,
            'ticketid' => 1,
            'ticketstatusid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'ticketstatustitle' => 1,
            'tickettypetitle' => 1,
            'ticketwatcherstaffid' => 1,
            'timeworked' => 1,
            'totalreplies' => 1,
            'usergrouptitle' => 1,
            'userid' => 1,
            'tgroupid' => 1,
            'userorganizationid' => 1,
            'userorganizationname' => 1,
            'flagtype' => 1,
            'slaplanid' => 1,
            'escalationruleid' => 1,
            'bayescategoryid' => 1,
            'emailqueueid' => 1,
        ];
        $this->assertNotEmpty($obj::GridRender($fieldContainer));

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $mockCache->method('Get')->willReturn([
            1 => [
                1 => 1,
            ],
            'displayicon' => 'icon.gif',
            'bgcolorcode' => '#ffffff',
            'list' => [
                1 => [
                    'email' => 'me@mail.com',
                ],
            ],
            22 => [
                'displayicon' => 'icon.gif',
                'bgcolorcode' => '#ffffff',
            ]
        ]);
        \SWIFT::GetInstance()->Cache = $mockCache;

        unset($fieldContainer['hasbilling']);
        $fieldContainer['departmentid'] = 0;
        $fieldContainer['ownerstaffid'] = 2;
        $fieldContainer['emailqueueid'] = 2;
        $fieldContainer['ticketstatusid'] = 22;
        $fieldContainer['priorityid'] = 22;
        $fieldContainer['tickettypeid'] = 22;
        $fieldContainer['flagtype'] = 100;
        $fieldContainer['duetime'] = DATENOW + 100000;
        $fieldContainer['resolutionduedateline'] = DATENOW + 100000;
        $this->assertNotEmpty($obj::GridRender($fieldContainer));

        unset($fieldContainer['resolutionduedateline'], $fieldContainer['duetime'], $fieldContainer['ownerstaffid'], $fieldContainer['tgroupid'], $fieldContainer['tickettypeid'], $fieldContainer['priorityid'], $fieldContainer['ticketstatusid'], $fieldContainer['hasnotes']);
        $fieldContainer['departmentid'] = 10;
        $fieldContainer['emailqueueid'] = 1;
        $fieldContainer['hasbilling'] = 1;
        $fieldContainer['lockstaffid'] = 2;
        $fieldContainer['lockdateline'] = DATENOW + 100000;
        $this->assertNotEmpty($obj::GridRender($fieldContainer));
    }

    /**
     * @dataProvider provideDataForGridRenderOverdueBackgroundColorStyleTest
     */
    public function testGridRenderShouldReturnOverdueBackgroundColorStyleIfAndOnlyIfTicketIsOverdue(
        int $replyDueTime,
        int $resolutionDueTime,
        ?string $expectedStyle): void
    {
        // Arrange
        $mockedSettings = $this->createMock(\SWIFT_Settings::class);
        $mockedSettings->method('Get')
            ->willReturnCallback(function($key) {
                if ($key == 't_overduecolor') {
                    return self::OVERDUE_BACKGROUND_COLOR;
                } else {
                    return 0;
                }
            });
        \SWIFT::GetInstance()->Settings = $mockedSettings;

        // Act
        $ticketData = View_Manage::GridRender([
            'duetime' => $replyDueTime,
            'resolutionduedateline' => $resolutionDueTime
        ]);

        // Assert
        $this->assertEquals($expectedStyle, $ticketData[':']);
    }

    public function testGridRenderShouldReturnOverdueBackgroundColorStyleIfAndOnlyIfTicketIsOverdueAndBackgroundBlank(){
        // Arrange
        $mockedSettings = $this->createMock(\SWIFT_Settings::class);

        \SWIFT::GetInstance()->Settings = $mockedSettings;

        // Act
        $ticketData = View_Manage::GridRender([
            'duetime' => self::TIMESTAMP_IN_THE_PAST,
            'resolutionduedateline' => self::TIMESTAMP_IN_THE_FUTURE
        ]);

        // Assert
        $this->assertEquals(self::DEFAULT_OVERDUE_BACKGROUND_COLOR_STYLE, $ticketData[':']);
    }

    public function provideDataForGridRenderOverdueBackgroundColorStyleTest(): array {
        return [
            [self::TIMESTAMP_IN_THE_FUTURE, self::TIMESTAMP_IN_THE_FUTURE, null],
            [self::TIMESTAMP_IN_THE_PAST, self::TIMESTAMP_IN_THE_FUTURE, self::OVERDUE_BACKGROUND_COLOR_STYLE],
            [self::TIMESTAMP_IN_THE_FUTURE, self::TIMESTAMP_IN_THE_PAST, self::OVERDUE_BACKGROUND_COLOR_STYLE],
            [self::TIMESTAMP_IN_THE_PAST, self::TIMESTAMP_IN_THE_PAST, self::OVERDUE_BACKGROUND_COLOR_STYLE]
        ];
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getMocked();

        $_POST['itemid'] = [1];
        $this->assertTrue($obj->RenderMassReply());

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderMassReply());
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_ManageMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\View_ManageMock', $services);
    }
}

class View_ManageMock extends View_Manage
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

