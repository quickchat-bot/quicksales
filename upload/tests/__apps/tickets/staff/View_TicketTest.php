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

use Base\Library\CustomField\SWIFT_CustomFieldRendererStaff;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserOrganization;
use Knowledgebase\Admin\LoaderMock;
use SWIFT_CacheStore;
use SWIFT_Database;
use SWIFT_Emoji;
use SWIFT_Exception;
use SWIFT_Input;
use SWIFT_Session;
use SWIFT_Settings;
use SWIFT_TestCase;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * Class View_TicketTest
 * @group tickets
 * @group tickets-staff
 */
class View_TicketTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $_creatortype = 1;

    /**
     * @throws SWIFT_Exception
     */
    public function testGetFlagMenuReturnsHtml()
    {
        $obj = $this->getMocked();
        $mock = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetTicketID')->willReturn(1);
        $this->assertContains('ticketflagmenu', $obj->GetFlagMenu($mock));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTicketDataJsonReturnsJson()
    {
        $obj = $this->getMocked();
        $this->assertNotEmpty($obj->GetTicketDataJSON());

        $this->assertClassNotLoaded($obj, 'GetTicketDataJSON');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderAuditLogReturnsTrue()
    {
        $obj = $this->getMocked();

        $mock = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('GetTicketID')->willReturn(1);
        $mock->method('GetIsClassLoaded')->willReturn(true);
        $this->expectOutputRegex('/gridtabletitlerow/');

        $mockDb = $this->getMockBuilder(SWIFT_Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            if (self::$_next > 4) {
                self::$_creatortype++;
                \SWIFT::GetInstance()->Database->Record = [
                    'actionhash' => self::$_creatortype - 2,
                    'dateline' => 1,
                    'creatortype' => self::$_creatortype - 2,
                    'ticketauditlogid' => 1,
                ];
            }

            return in_array(self::$_next, [1, 2, 3, 4, 6, 7, 8, 9], true);
        });

        \SWIFT::GetInstance()->Database = $mockDb;

        $this->assertTrue($obj->RenderAuditLog($mock),
            'Returns true after printing HTML');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderAuditLog($mock),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderForwardReturnsTrue()
    {
        $obj = $this->getMocked();

        $mock = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('GetTicketID')->willReturn(1);
        $mock->method('GetIsClassLoaded')->willReturn(true);
        $this->expectOutputRegex('/script/');

        $mockDb = $this->getMockBuilder(SWIFT_Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturn([
            'ticketviewid' => 1,
        ]);
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);

        \SWIFT::GetInstance()->Database = $mockDb;

        $session = $this->getMockBuilder(SWIFT_Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $session->method('GetProperty')->willReturn(1);
        \SWIFT::GetInstance()->Session = $session;

        $cache = $this->getMockBuilder(SWIFT_CacheStore::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache->method('Get')->willReturn([
            1 => [
                'ticketviewid' => 1,
                'staffid' => 1,
                'viewscope' => 1,
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $cache;

        $obj->_doRenderDispatch = false;

        $this->assertTrue($obj->RenderForward($mock),
            'Returns true after printing HTML with staff_tcanfollowup = 1');

        $this->assertTrue($obj->RenderForward($mock),
            'Returns true after printing HTML with staff_tcanfollowup = 0');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderForward($mock),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderSplitOrDuplicateReturnsHtml()
    {
        $mockInput = $this->getMockBuilder(SWIFT_Input::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockEmoji = $this->getMockBuilder(SWIFT_Emoji::class)
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getMocked([
            'Input' => $mockInput,
            'Emoji' => $mockEmoji,
        ]);

        $obj->UserInterface->method('End')->willReturn('form');

        $mockDb = $this->getMockBuilder(SWIFT_Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            $arr = [
                'ticketid' => 1,
                'departmentid' => 1,
                'lastpostid' => 0,
                'iswatched' => 0,
                'userid' => 1,
                'subject' => 'subject',
                'fullname' => 'fullname',
                'ticketpostid' => '1',
                'dateline' => '1',
                'isresolved' => '0',
            ];

            if (false !== strpos($x, "ticketid = '2'")) {
                $arr['departmentid'] = 0;
            }

            return $arr;
        });
        $mockDb->Record = [
            'ticketpostid' => '1',
            'fullname' => 'fullname',
            'dateline' => '1',
        ];
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);

        \SWIFT::GetInstance()->Database = $mockDb;

        $this->assertContains('form', $obj->RenderSplitOrDuplicate(1, 1),
            'Returns HTML with mode = split');

        $this->assertContains('form', $obj->RenderSplitOrDuplicate(1, 2),
            'Returns HTML wih other mode');

        $this->assertContains('form', $obj->RenderSplitOrDuplicate(2, 2),
            'Returns HTML wih other mode');

        $this->assertClassNotLoaded($obj, 'RenderSplitOrDuplicate', 1, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderEditReturnsTrue()
    {
        $ctr = $this->getMockBuilder(Controller_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rdr = $this->getMockBuilder(SWIFT_CustomFieldRendererStaff::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ctr->CustomFieldRendererStaff = $rdr;

        $mockEmoji = $this->getMockBuilder(SWIFT_Emoji::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache = $this->getMockBuilder(SWIFT_CacheStore::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache->method('Get')->willReturn([
            0 => [
                'isenabled' => '0',
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $cache;

        $obj = $this->getMocked([
            'Controller' => $ctr,
            'Emoji' => $mockEmoji,
            'Cache' => $cache,
        ]);

        $mock = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('GetTicketID')->willReturn(1);
        $mock->method('GetIsClassLoaded')->willReturn(true);
        $mock->method('GetProperty')->willReturnMap([
            ['ticketslaplanid', '0'],
            ['replyto', 'me@mail.com'],
        ]);
        $this->expectOutputRegex('/script/');

        $mockDb = $this->getMockBuilder(SWIFT_Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->Record = [
            'recipienttype' => 1,
            'ticketrecipientid' => 1,
            'ticketemailid' => 1,
            'email' => 'me@email.com',
        ];

        self::$_next = 0;

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            \SWIFT::GetInstance()->Database->Record = [
                'recipienttype' => self::$_next,
                'ticketrecipientid' => self::$_next,
                'ticketemailid' => 1,
                'email' => 'me@email.com',
            ];

            return in_array(self::$_next, [1, 2, 3, 4, 6], true);
        });

        \SWIFT::GetInstance()->Database = $mockDb;

        $session = $this->getMockBuilder(SWIFT_Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $session->method('GetProperty')->willReturn(1);
        \SWIFT::GetInstance()->Session = $session;

        $this->assertTrue($obj->RenderEdit($mock, 1, 1, 1, 1, 0),
            'Returns true after printing HTML');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderEdit($mock, 1, 1, 1, 1, 0),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderEditPostReturnsTrue()
    {
        $mockEmoji = $this->getMockBuilder(SWIFT_Emoji::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockEmoji->method('Decode')->willReturnOnConsecutiveCalls('<html></html>', '<html></html>', 'text');

        $obj = $this->getMocked([
            'Emoji' => $mockEmoji,
        ]);

        $obj->UserInterface->method('End')->willReturn('form');

        $mock = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetTicketID')->willReturn(1);
        $mock->method('GetIsClassLoaded')->willReturn(true);

        $post = $this->getMockBuilder(SWIFT_TicketPost::class)
            ->disableOriginalConstructor()
            ->getMock();
        $post->method('GetTicketPostID')->willReturn(1);
        $post->method('Get')->willReturn('1');
        $post->method('GetID')->willReturn(1);
        $post->method('GetIsClassLoaded')->willReturn(true);

        $_POST['_isDialog'] = 1;

        $mockDb = $this->getMockBuilder(SWIFT_Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturn([
            'ticketid' => '1',
            'attachmentid' => 1,
            'departmentid' => 1,
            'lastpostid' => 0,
            'iswatched' => 0,
            'hasattachments' => 1,
            'userid' => 1,
            'subject' => 'subject',
            'fullname' => 'fullname',
            'ticketpostid' => '1',
            'dateline' => '1',
            'isresolved' => '0',
            'storefilename' => 'file.txt',
            'attachmenttype' => 0,
        ]);
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false, true, false);
        $mockDb->Record = [
            'linktypeid' => 1,
            'attachmentid' => 1,
            'filename' => 'test.gif',
        ];
        \SWIFT::GetInstance()->Database = $mockDb;

        $mockSettings = $this->getMockBuilder(SWIFT_Settings::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockSettings->method('Get')->willReturnOnConsecutiveCalls(0, 1);
        \SWIFT::GetInstance()->Settings = $mockSettings;

        $this->assertTrue($obj->RenderEditPost($mock, $post),
            'Returns true with t_tinymceeditor = 0');

        $this->assertTrue($obj->RenderEditPost($mock, $post),
            'Returns true with t_tinymceeditor = 1');

        $this->assertTrue($obj->RenderEditPost($mock, $post),
            'Returns true with plain text');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderEditPost($mock, $post),
            'Returns false if class is not loaded');
    }

    public function testRenderHistoryThrowsException()
    {
        $obj = $this->getMocked();
        $this->assertInvalidData($obj, 'RenderHistory', 'invalid');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderHistoryReturnsTrue()
    {
        $mockInput = $this->getMockBuilder(SWIFT_Input::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockEmoji = $this->getMockBuilder(SWIFT_Emoji::class)
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getMocked([
            'Input' => $mockInput,
            'Emoji' => $mockEmoji,
        ]);

        $this->expectOutputRegex('/cellspacing/');

        $mock = $obj->getTicketMock($this);

        $this->assertTrue($obj->RenderHistory($mock));

        $cache = $this->getMockBuilder(SWIFT_CacheStore::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache->method('Get')->willReturn([
            1 => [
                1 => 1,
                'bgcolorcode' => '#ffffff',
                'displayicon' => 'icon.gif',
            ],
        ]);
        \SWIFT::GetInstance()->Cache = $cache;

        $this->assertTrue($obj->RenderHistory($mock));

        $mock2 = $obj->getUserMock($this);
        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->Record = [
            'ticketid' => '1',
            'departmentid' => 1,
            'lastpostid' => 0,
            'iswatched' => 0,
            'userid' => 1,
            'dateline' => '1',
            'isresolved' => '0',
            'ticketstatusid' => '0',
            'priorityid' => '0',
            'tickettypeid' => '0',
            'ticketmaskid' => '0',
            'subject' => 'subject',
        ];

        $this->assertTrue($obj->RenderHistory($mock2));

        $this->assertTrue($obj->RenderHistory([]));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->RenderHistory([]),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetSelectOptionsIncludesRestrictedStatus()
    {
        $cache = $this->getMockBuilder(SWIFT_CacheStore::class)
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
                        'staffvisibilitycustom' => 1,
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

        $mock = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('GetTicketID')->willReturn(1);
        $mock->method('GetIsClassLoaded')->willReturn(true);

        $staff = $this->getMockBuilder(SWIFT_Staff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetProperty')->willReturn(1);
        $staff->method('GetPermission')->willReturn(false);
        $staff->method('GetStaffID')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $staff;

        self::assertContains('restricted', $obj->GetSelectOptions($mock, View_Ticket::TYPE_STATUS, [
            '_exTicketStatusID' => 1,
            'ticketstatusid' => 2,
            'title' => 'restricted status',
        ]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetSelectOptionsReturnsArray()
    {
        $cache = $this->getMockBuilder(SWIFT_CacheStore::class)
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

        $mock = $this->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('GetTicketID')->willReturn(1);
        $mock->method('GetIsClassLoaded')->willReturn(true);

        $staff = $this->getMockBuilder(SWIFT_Staff::class)
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetProperty')->willReturn(1);
        $staff->method('GetPermission')->willReturn(false);
        $staff->method('GetStaffID')->willReturn(1);
        \SWIFT::GetInstance()->Staff = $staff;

        $this->assertInternalType('string', $obj->GetSelectOptions($mock, 1, [
            '_exDepartmentID' => 3,
        ]));

        $this->assertInternalType('string', $obj->GetSelectOptions($mock, 1, [
            '_exDepartmentID' => 1,
            '_exOwnerStaffID' => 1,
            '_exTicketTypeID' => 1,
            '_exTicketStatusID' => 1,
            '_exTicketPriorityID' => 1,
        ]));

        $this->assertInternalType('string', $obj->GetSelectOptions($mock, 2, [
            '_exOwnerStaffID' => 1,
            '_exDepartmentID' => 1,
        ]));
        $this->assertInternalType('string', $obj->GetSelectOptions($mock, 4, [
            '_exTicketTypeID' => 1,
        ]));
        $this->assertInternalType('string', $obj->GetSelectOptions($mock, 3, [
            '_exTicketStatusID' => 1,
        ]));
        $this->assertInternalType('string', $obj->GetSelectOptions($mock, 5, [
            '_exTicketPriorityID' => 1,
        ]));

        $this->assertClassNotLoaded($obj, 'GetSelectOptions', $mock, 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_TicketMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject(View_TicketMock::class, $services);
    }
}

class CustomMock
{
    public function CanAccess()
    {
        return false;
    }

    public function GetTicketID()
    {
        return 1;
    }

    public function GetTicketDisplayID()
    {
        return 1;
    }

    public function GetProperty($p)
    {
        if ($p === 'departmentid') {
            return -1;
        }

        return 1;
    }
}

class View_TicketMock extends View_Ticket
{
    public $_doRenderDispatch = true;
    public $UserInterface;
    public $Cache;
    public $Database;
    public $_renderBillingEntries = true;
    public $_renderNotes = true;

    public function RenderNotes(SWIFT_Ticket $_SWIFT_TicketObject, $_SWIFT_UserObject = null)
    {
        if (!$this->_renderNotes) {
            return '';
        }

        return parent::RenderNotes($_SWIFT_TicketObject, $_SWIFT_UserObject);
    }

    public function RenderBillingEntries($_SWIFT_InputObject)
    {
        if (!$this->_renderBillingEntries) {
            return '';
        }

        return parent::RenderBillingEntries($_SWIFT_InputObject);
    }

    /**
     * @param SWIFT_TestCase $test
     * @param bool $isLoaded
     * @param bool $setProp
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_Ticket
     */
    public function getTicketMock(SWIFT_TestCase $test, $isLoaded = true, $setProp = true)
    {
        $mock = $test->getMockBuilder(SWIFT_Ticket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('GetTicketID')->willReturn(1);
        $mock->method('GetIsClassLoaded')->willReturn($isLoaded);
        $mock->method('RetrieveHistory')->willReturn([0 => new CustomMock(), 1 => $mock]);
        if ($setProp) {
            $mock->method('GetProperty')->willReturn(1);
            $mock->method('Get')->willReturn(1);
        }

        return $mock;
    }

    /**
     * @param SWIFT_TestCase $test
     * @param bool $isLoaded
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_User
     */
    public function getUserMock(SWIFT_TestCase $test, $isLoaded = true)
    {
        $mock2 = $test->getMockBuilder(SWIFT_User::class)
            ->disableOriginalConstructor()
            ->setMethods(['GetUserID', 'GetIsClassLoaded', 'GetEmailList', 'GetProperty', '__destruct'])
            ->getMock();
        $mock2->method('__destruct')->willReturn(1);
        $mock2->method('GetUserID')->willReturn(1);
        $mock2->method('GetIsClassLoaded')->willReturn($isLoaded);
        $mock2->method('GetEmailList')->willReturn(['me@mail.com']);
        $mock2->method('GetProperty')->willReturn(1);

        return $mock2;
    }

    /**
     * @param SWIFT_TestCase $test
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_UserOrganization
     */
    public function getUserOrgMock(SWIFT_TestCase $test)
    {
        $mock2 = $test->getMockBuilder(SWIFT_UserOrganization::class)
            ->disableOriginalConstructor()
            ->setMethods(['GetUserOrganizationID', 'GetIsClassLoaded', 'GetProperty'])
            ->getMock();
        $mock2->method('GetProperty')->willReturn(1);
        $mock2->method('GetUserOrganizationID')->willReturn(1);
        $mock2->method('GetIsClassLoaded')->willReturn(true);

        return $mock2;
    }

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

    public function GetSelectOptions($_SWIFT_TicketObject, $_selectType, $_extendedDataContainer = [])
    {
        return parent::GetSelectOptions($_SWIFT_TicketObject, $_selectType, $_extendedDataContainer);
    }

    public function RenderDispatchTab(
        $_tabType,
        SWIFT_UserInterfaceTab $_TabObject,
        SWIFT_Ticket $_SWIFT_TicketObject = null,
        $_SWIFT_UserObject = null,
        $_ticketWatchContainer = [],
        $_departmentID = false
    ) {
        if (!$this->_doRenderDispatch) {
            return true;
        }

        return parent::RenderDispatchTab($_tabType, $_TabObject, $_SWIFT_TicketObject, $_SWIFT_UserObject,
            $_ticketWatchContainer, $_departmentID);
    }
}

