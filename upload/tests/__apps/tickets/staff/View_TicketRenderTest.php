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

/**
 * Class View_ViewTest
 * @group tickets
 * @group tickets-staff
 */
class View_TicketRenderTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $_prop = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReleaseReturnsTrue()
    {
        $mockInput = $this->getMockBuilder('SWIFT_Input')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();

        $ctr = $this->getMockBuilder('Tickets\Staff\Controller_Ticket')
            ->disableOriginalConstructor()
            ->getMock();

        $rdr = $this->getMockBuilder('Base\Library\CustomField\SWIFT_CustomFieldRendererStaff')
            ->disableOriginalConstructor()
            ->getMock();

        $ctr->CustomFieldRendererStaff = $rdr;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'escalationpaths')) {
                static::$_prop['escalationpaths'] = 0;
            }
            self::$_next = 0;
        });
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            if (isset(static::$_prop['escalationpaths'])) {
                static::$_prop['escalationpaths']++;
                if (static::$_prop['escalationpaths'] === 3) {
                    unset(static::$_prop['escalationpaths']);

                    return false;
                }

                \SWIFT::GetInstance()->Database->Record['escalationpathid'] = static::$_prop['escalationpaths'];

                return true;
            }

            return self::$_next % 2;
        });
        $mockDb->method('Insert_ID')->willReturn(1);
        static::$_prop['ticketrecurrenceid'] = 0;
        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'totalrecords' => 1,
            'islinked' => 1,
            'ticketmaskid' => 1,
            'ownerstaffid' => 1,
            'userid' => 0,
            'subject' => 'subject',
            'dateline' => &static::$_prop['dateline'],
            'staffid' => '1',
            'ticketviewid' => '1',
            'ticketrecurrenceid' => &static::$_prop['ticketrecurrenceid'],
            'totalitems' => '2',
        ]);

        $mockDb->Record = [
            'ticketid' => 1,
            'priorityid' => 1,
            'ticketstatusid' => 1,
            'chainhash' => 1,
            'ticketlinkchainid' => 1,
            'ticketpostid' => 1,
            'creator' => 1,
            'staffid' => 1,
            'userid' => 1,
            'fullname' => 'fullname',
            'ticketviewid' => '1',
            'ticketmaskid' => '1',
            'userprofileimageid' => '1',
            'staffprofileimageid' => '1',
            'userdesignation' => 'mr',
        ];

        $obj = $this->getMocked([
            'Database' => $mockDb,
            'Controller' => $ctr,
            'Input' => $mockInput,
            'Emoji' => $mockEmoji,
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetIsClassLoaded')->willReturn(true);
        static::$_prop['GetStaffID'] = 1;
        $staff->method('GetStaffID')->willReturnCallback(function () {
            return static::$_prop['GetStaffID'];
        });
        $staff->method('GetDepartmentPermission')->willReturn('1');
        $staff->method('GetProperty')->willReturn('1');
        $staff->method('GetPermission')->willReturn('1');
        \SWIFT::GetInstance()->Staff = $staff;

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('GetLanguageCode')->willReturnOnConsecutiveCalls('sv', 'ru', 'fr', 'pt', 'sv', 'ru');
        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            if (false !== strpos($x, 'log')) {
                return '%s ';
            }

            return $x;
        });
        \SWIFT::GetInstance()->Language = $mockLang;

        $mockTicket = $obj->getTicketMock($this, true, false);
        $userOrg = $obj->getUserOrgMock($this);
        $mockTicket->method('GetUserOrganizationObject')->willReturn($userOrg);
        $mockTicket->method('GetTimeTrackCount')->willReturn(2);
        $mockTicket->method('GetTicketPostCount')->willReturn(2);
        $mockTicket->method('GetHistoryCount')->willReturn(2);
        static::$_prop['filename'] = 'file.txt';
        $mockTicket->method('GetAttachmentContainer')->willReturn([
            1 => [
                1 => [
                    'filename' => &static::$_prop['filename'],
                    'filesize' => 1,
                    'filetype' => 'file',
                    'storefilename' => 'file.txt',
                    'attachmenttype' => 0,
                ],
            ],
        ]);
        $mockTicket->method('GetLinks')->willReturn([
            1 => [
                1 => $mockTicket,
                2 => [],
            ],
            2 => [],
        ]);
        $mockTicket->method('Get')->willReturnCallback(function ($x) {
            if (!isset(static::$_prop[$x])) {
                static::$_prop[$x] = 1;
            }

            if ($x === 'recurrencefromticketid') {
                static::$_prop['ticketrecurrenceid'] = 1;
            }

            return static::$_prop[$x];
        });
        $mockTicket->method('GetProperty')->willReturnCallback(function ($x) {
            if (!isset(static::$_prop[$x])) {
                static::$_prop[$x] = 1;
            }

            return static::$_prop[$x];
        });
        $mockUser = $obj->getUserMock($this);

        $mockPost = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_TicketPost')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPost->method('GetIsClassLoaded')->willReturn(true);
        $mockPost->method('GetTicketPostID')->willReturn('1');
        $mockPost->method('GetProperty')->willReturnCallback(function ($x) {
            $x = 'post' . $x;
            if (!isset(static::$_prop[$x])) {
                static::$_prop[$x] = 1;
            }

            return static::$_prop[$x];
        });

        $this->expectOutputRegex('/script/');

        $obj->_doRenderDispatch = false;

        $_variableContainer = [
            '_ticketPostOffset' => 0,
            '_ticketPostLimitCount' => 0,
            '_userImageUserIDList' => [1],
            '_staffImageUserIDList' => [1],
            '_ticketPostContainer' => [
                1 => $mockPost,
            ],
        ];

        $cache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        static::$_prop['parentdepartmentid'] = 1;
        $cache->method('Get')->willReturn(
            [
                1 => [
                    1 => 1,
                    'bgcolorcode' => '#ffffff',
                    'designation' => 'mr',
                    'parentdepartmentid' => &static::$_prop['parentdepartmentid'],
                ],
            ]
        );
        $obj->Cache = $cache;

        $this->assertTrue($obj->RenderTicket($mockTicket, $mockUser, '', 1, 1, 1, 0, $_variableContainer));

        static::$_prop['ticketslaplanid'] = 22;
        static::$_prop['flagtype'] = 22;
        static::$_prop['postcreator'] = 2;
        static::$_prop['firstpostid'] = 2;
        static::$_prop['postisthirdparty'] = 2;
        $mockUser2 = $obj->getUserMock($this, false);
        $obj->_renderNotes = false;
        $this->assertTrue($obj->RenderTicket($mockTicket, $mockUser2, '', 1, 1, 1, 0, $_variableContainer, 'reply'));

        static::$_prop['userid'] = 0;
        static::$_prop['filename'] = 'file.ttt';
        static::$_prop['postcreator'] = 3;
        $this->assertTrue($obj->RenderTicket($mockTicket, $mockUser, '', 1, 1, 1, 0, $_variableContainer, 'forward'));

        static::$_prop['postcreator'] = 4;
        $this->assertTrue($obj->RenderTicket($mockTicket, $mockUser, '', 1, 1, 1, 0, $_variableContainer, 'followup'));

        static::$_prop['postcreator'] = 5;
        static::$_prop['ownerstaffid'] = 0;
        static::$_prop['GetStaffID'] = 3;
        static::$_prop['dateline'] = DATENOW + 1000000;
        $this->assertTrue($obj->RenderTicket($mockTicket, $mockUser, '', 1, 1, 1, 0, $_variableContainer, 'billing'));

        static::$_prop['postisthirdparty'] = 2;
        static::$_prop['postcreator'] = 1;
        static::$_prop['departmentid'] = 0;
        static::$_prop['ticketstatusid'] = 2;
        static::$_prop['priorityid'] = 2;
        static::$_prop['tickettypeid'] = 2;
        static::$_prop['ownerstaffid'] = 2;
        static::$_prop['parentdepartmentid'] = 0;
        static::$_prop['duetime'] = DATENOW + 1000;
        static::$_prop['resolutionduedateline'] = DATENOW + 1000;
        $this->assertTrue($obj->RenderTicket($mockTicket, $mockUser, '', 1, 1, 1, 0, $_variableContainer, 'release'));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderTicket($mockTicket, $mockUser, '', 1, 1, 1, 0, []),
            'Returns false if class is not loaded');
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
