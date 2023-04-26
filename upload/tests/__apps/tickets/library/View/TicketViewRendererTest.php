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

namespace Tickets\Library\View;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;
use Tickets\Models\View\SWIFT_TicketView;
use Tickets\Models\View\SWIFT_TicketViewLink;

/**
 * Class TicketViewRendererTest
 * @group tickets-view
 */
class TicketViewRendererTest extends \SWIFT_TestCase
{
    protected function getCacheMock(&$values)
    {
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturnCallback(function ($x) use (&$values) {
            if (array_key_exists($x, $values))
                return $values[$x];

            if ($x === 'staffcache')
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                    ],
                ];

            return [
                1 => [
                    1 => [1 => [1]],
                ],
            ];
        });

        return $mockCache;
    }

    public function testGetDefaultTicketViewObjectReturnsTicketViewObject()
    {
        $obj = $this->getMocked();

        $cacheData = [
            'ticketviewcache' => [1 => [
                'ticketviewid' => 1,
                'staffid' => 1,
                'viewscope' => 1
            ]],
            'staffticketpropertiescache' => [
                1 => ['ticketviewid' => 1]
            ]
        ];

        $mockCache = $this->getCacheMock($cacheData);
        $obj->Cache = $mockCache;
        $swift = \SWIFT::GetInstance();
        $swift->Cache = $mockCache;

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->method('GetSessionID')->willReturn(1);
        $mockSession->method('GetIsClassLoaded')->willReturn(true);
        $mockSession->method('GetProperty')->with('ticketviewid')->will(
            $this->onConsecutiveCalls(1, null, 1)
        );

        $swift->Session = $mockSession;

        $this->assertInstanceOf('Tickets\Models\View\SWIFT_TicketView', $obj->GetDefaultTicketViewObject(),
            'ticketviewid found');

        $this->assertInstanceOf('Tickets\Models\View\SWIFT_TicketView', $obj->GetDefaultTicketViewObject(),
            'ticketviewid not found');

        $cacheData['ticketviewcache'][1]['staffid'] = 3;
        $cacheData['ticketviewcache'][1]['viewscope'] = 3;
        $cacheData['ticketviewcache'][1]['ismaster'] = 1;

        $this->assertInstanceOf('Tickets\Models\View\SWIFT_TicketView', $obj->GetDefaultTicketViewObject(),
            'master view found');

        $cacheData['ticketviewcache'][1]['ismaster'] = 0;
        $this->assertInvalidData($obj, 'GetDefaultTicketViewObject');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetNextPreviousTicketIDReturnsID()
    {
        $obj = $this->getMocked();

        $ticketObjMock = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();

        $ticketObjMock->method('GetTicketID')->will(
            $this->onConsecutiveCalls(
                '', 1, 1, '',
                '', 1, 1, ''
            )
        );

        $ticketObjMock->method('GetIsClassLoaded')->will(self::onConsecutiveCalls(true, true, true, false));


        $data = [
            'ticketviewcache' => [
                1 => [
                    'ticketviewid' => 1,
                    'staffid' => 1,
                    'viewscope' => 2,
                    'ismaster' => '1',
                    'fields' => [
                        'key' => ['ticketviewfieldid' => 1]
                    ]
                ]
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturn([1, 2, 3]);

        $arr = [
            'searchstoreid' => 1
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturn($arr);
        $_SWIFT->Database->Record = $arr;

        static::$nextRecordType = static::NEXT_RECORD_CUSTOM_LIMIT;
        static::$nextRecordLimit = 4;

        $this->assertTrue(is_numeric((int)$obj->GetNextPreviousTicketID($ticketObjMock, 'next', 'mytickets')),
            'Returns ticket id');

        static::$nextRecordCount = 0;

        $this->assertTrue(is_numeric((int)$obj->GetNextPreviousTicketID($ticketObjMock, 'previous', 'unassigned')),
            'Returns ticket id');

        $_SWIFT->Database->Record['dataid'] = 1;
        $this->assertTrue(is_numeric((int)$obj->GetNextPreviousTicketID($ticketObjMock, 'next', 'mytickets')),
            'Returns ticket id');

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->GetNextPreviousTicketID($ticketObjMock);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testParseVariablesReturnsArray()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketviewcache' => [
                1 => [
                    'ticketviewid' => 1,
                    'staffid' => 1,
                    'viewscope' => 2,
                    'ismaster' => '1'
                ]
            ],
            'departmentcache' => [

            ],
            'tickettypecache' => [
                1 => [
                    'departmentid' => 1
                ]
            ],
            'statuscache' => [
                1 => [
                    'departmentid' => 1
                ]
            ],
            'prioritycache' => [
            ],
            'staffcache' => [

            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $_SWIFT->Staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $staffPerms = ['staff_tcanviewall' => '0', 'staff_tcanviewunassign' => '1'];

        $_SWIFT->Staff->method('GetPermission')->willReturnCallback(function ($x) use (&$staffPerms) {
            return $staffPerms[$x];
        });

        $_SWIFT->Staff->method('GetAssignedDepartments')->will($this->onConsecutiveCalls([1, 2, 3], [1, 2, 3], ['0'], ['0'], ['0']));

        $method = $this->getMethod('Tickets\Library\View\SWIFT_TicketViewRendererMock', 'ParseVariables');

        $ticketViewObjMock = $this->getMockBuilder('Tickets\Models\View\SWIFT_TicketView')
            ->disableOriginalConstructor()
            ->getMock();

        $perms = ['viewassigned' => '1', 'viewalltickets' => '1', 'viewunassigned' => '1'];

        $ticketViewObjMock->method('GetProperty')->willReturnCallback(function ($x) use (&$perms) {
            return $perms[$x];
        });


        $ticketViewLinkObjMock = $this->getMockBuilder('Tickets\Models\View\SWIFT_TicketViewLink')
            ->disableOriginalConstructor()
            ->getMock();

        $ticketViewLinkObjMock->method('GetProperty')->willReturn(1);

        $_ticketViewLinksContainer = [
            SWIFT_TicketViewLink::LINK_FILTERDEPARTMENT => [
                'type' => [
                    $ticketViewLinkObjMock
                ]
            ]
        ];

        $_POST['_searchQuery'] = 'test';

        $this->assertTrue(is_array($method->invokeArgs(null, [
            $ticketViewObjMock,
            $_ticketViewLinksContainer,
            1,
            1,
            false,
            1
        ])),
            'Returns array');

        $this->assertTrue(is_array($method->invokeArgs(null, [
            $ticketViewObjMock,
            $_ticketViewLinksContainer,
            -1,
            -1,
            1,
            -1
        ])),
            'Returns array');

        $perms['viewalltickets'] = '0';

        $this->assertTrue(is_array($method->invokeArgs(null, [
            $ticketViewObjMock,
            [],
            0,
            -1,
            false,
            -1
        ])),
            'Returns array');

        $perms['viewalltickets'] = '1';
        $staffPerms['staff_tcanviewall'] = '1';
        $staffPerms['staff_tcanviewunassign'] = '0';

        $this->assertTrue(is_array($method->invokeArgs(null, [
            $ticketViewObjMock,
            [],
            0,
            -1,
            false,
            -1
        ])),
            'Returns array');

        $perms['viewalltickets'] = $perms['viewassigned'] = $perms['viewunassigned'] = '0';

        $this->assertTrue(is_array($method->invokeArgs(null, [
            $ticketViewObjMock,
            [],
            0,
            -1,
            false,
            -1
        ])),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTicketViewObjectReturnsTicketViewObject()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketviewcache' => [
                1 => [
                    'ticketviewid' => 1,
                    'staffid' => 1,
                    'viewscope' => 2,
                    'ismaster' => '1'
                ]
            ],
            'ticketviewdepartmentlinkcache' => [
                1 => [1]
            ],
            'staffcache' => [
                1 => [
                    'staffgroupid' => 1
                ]
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $this->assertInstanceOf('Tickets\Models\View\SWIFT_TicketView', $obj->GetTicketViewObject(1));

        \SWIFT::Set('forceViewChange', true);
        $this->assertInstanceOf('Tickets\Models\View\SWIFT_TicketView', $obj->GetTicketViewObject(1));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketviewcache' => [
                1 => [
                    'ticketviewid' => 1,
                    'staffid' => 1,
                    'title' => 'Test View',
                    'sortby' => 'c_30',
                    'sortorder' => '1',
                    'ticketsperpage' => 10,
                    'autorefresh' => 1,
                    'viewscope' => 1,
                    'viewalltickets' => 1,
                    'fields' => [
                        'key' => ['ticketviewfieldid' => 1, 'fieldtypeid' => 1, 'fieldtype' => '2', 'sort' => 'sort'],
                        'c_30' => ['ticketviewfieldid' => 1, 'fieldtypeid' => 30, 'fieldtype' => '2', 'sort' => 'sort']
                    ]
                ],
                2 => [
                    'ticketviewid' => 2,
                    'staffid' => 1,
                    'viewscope' => 2
                ]
            ],
            'customfieldidcache' => [
                'ticketcustomfieldidlist' => [30, 40, 50]
            ],
            'customfieldmapcache' => [
                30 => ['title' => 'test1'],
                40 => ['title' => 'test2'],
                50 => ['title' => 'test3'],
            ],
            'staffcache' => [
                1 => [
                    'staffgroupid' => 5
                ]
            ],
            'departmentcache' => [
                1 => ['departmentid' => 1]
            ],
            'tickettypecache' => [
                1 => [
                    'departmentid' => 1
                ]
            ],
            'statuscache' => [
                1 => [
                    'departmentid' => 1
                ]
            ],
            'prioritycache' => [
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $userInterfaceObjectMock = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $userInterfaceObjectMock->method('GetIsClassLoaded')->willReturn(true);

        $userInterfaceGridObjectMock = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $userInterfaceGridObjectMock->method('SetMassActionPanel')->willReturn(true);
        $userInterfaceGridObjectMock->method('GetMode')->willReturn(\Base\Library\UserInterface\SWIFT_UserInterfaceGrid::MODE_SEARCH);

        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturn([1, 2, 3]);

        $_SWIFT->UserInterface = $userInterfaceObjectMock;
        $_SWIFT->UserInterface->method('IsAjax')->will($this->onConsecutiveCalls(true, false));

        $this->expectOutputRegex('/<script.*/');
        $obj->Render($userInterfaceObjectMock, $userInterfaceGridObjectMock);

        $this->assertTrue($obj->Render($userInterfaceObjectMock, $userInterfaceGridObjectMock),
            'Returns true');

        $data['ticketviewcache'][1]['sortby'] = 'test';

        $this->assertTrue($obj->Render($userInterfaceObjectMock, $userInterfaceGridObjectMock),
            'Returns true');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderMassActionPanelReturnsTrue()
    {
        $obj = $this->getMocked();

        $data = [
            'staffticketpropertiescache' => [

                1 => [
                    SWIFT_TicketViewPropertyManager::PROPERTY_DEPARTMENT => [
                        1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []
                    ],
                    SWIFT_TicketViewPropertyManager::PROPERTY_STAFF => [
                        1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []
                    ],
                    SWIFT_TicketViewPropertyManager::PROPERTY_TYPE => [
                        1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []
                    ],
                    SWIFT_TicketViewPropertyManager::PROPERTY_STATUS => [
                        1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => [], 8 => []
                    ],
                    SWIFT_TicketViewPropertyManager::PROPERTY_PRIORITY => [
                        1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []
                    ],
                    SWIFT_TicketViewPropertyManager::PROPERTY_BAYES => [
                        1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []
                    ],
                    SWIFT_TicketViewPropertyManager::PROPERTY_LINK => [
                        1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []
                    ],
                    SWIFT_TicketViewPropertyManager::PROPERTY_FLAG => [
                        1 => [], 2 => [], 7 => [], 4 => [], 5 => [], 6 => []
                    ]
                ]
            ],
            'bayesiancategorycache' => [
                1 => ['category' => 'cat'],
                2 => ['category' => 'cat'],
                3 => ['category' => 'cat'],
                4 => ['category' => 'cat'],
                5 => ['category' => 'cat'],
                6 => ['category' => 'cat'],
            ],
            'ticketlinktypecache' => [
                1 => ['linktypetitle' => 'title'],
                2 => ['linktypetitle' => 'title'],
                3 => ['linktypetitle' => 'title'],
                4 => ['linktypetitle' => 'title'],
                5 => ['linktypetitle' => 'title'],
                6 => ['linktypetitle' => 'title'],
            ],
            'staffcache' => [
                1 => ['staffgroupid' => 5],
                2 => ['staffgroupid' => 5],
                3 => ['staffgroupid' => 5],
                4 => ['staffgroupid' => 5, 'isenabled' => 0],
                5 => ['staffgroupid' => 5],
                6 => ['staffgroupid' => 5],
                7 => ['staffgroupid' => 5],
            ],
            'departmentcache' => [
                1 => ['departmentid' => 1, 'parentdepartmentid' => 0, 'departmentapp' => APP_TICKETS],
                2 => ['departmentid' => 2, 'parentdepartmentid' => 0, 'departmentapp' => APP_TICKETS],
                3 => ['departmentid' => 3, 'parentdepartmentid' => 0, 'departmentapp' => APP_TICKETS],
                4 => ['departmentid' => 4, 'parentdepartmentid' => 1, 'departmentapp' => APP_TICKETS],
                5 => ['departmentid' => 5, 'parentdepartmentid' => 0, 'departmentapp' => APP_TICKETS],
                6 => ['departmentid' => 6, 'parentdepartmentid' => 0, 'departmentapp' => APP_TICKETS],
                7 => ['departmentid' => 7, 'parentdepartmentid' => 0, 'departmentapp' => APP_TICKETS],
            ],
            'tickettypecache' => [
                1 => ['departmentid' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                2 => ['departmentid' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                3 => ['departmentid' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                4 => ['departmentid' => 1, 'displayicon' => 'icon', 'title' => 'title'],
                5 => ['departmentid' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                6 => ['departmentid' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                7 => ['departmentid' => 0, 'displayicon' => 'icon', 'title' => 'title'],
            ],
            'statuscache' => [
                1 => ['departmentid' => 0, 'staffvisibilitycustom' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                2 => ['departmentid' => 0, 'staffvisibilitycustom' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                3 => ['departmentid' => 0, 'staffvisibilitycustom' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                4 => ['departmentid' => 1, 'staffvisibilitycustom' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                5 => ['departmentid' => 0, 'staffvisibilitycustom' => 1, 'displayicon' => 'icon', 'title' => 'title'],
                6 => ['departmentid' => 0, 'staffvisibilitycustom' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                7 => ['departmentid' => 0, 'staffvisibilitycustom' => 0, 'displayicon' => 'icon', 'title' => 'title'],
                8 => ['departmentid' => 0, 'staffvisibilitycustom' => 0, 'displayicon' => 'icon', 'title' => 'title'],
            ],
            'prioritycache' => [
                1 => ['displayicon' => 'icon'],
                2 => ['displayicon' => 'icon'],
                3 => ['displayicon' => 'icon'],
                4 => ['displayicon' => 'icon'],
                5 => ['displayicon' => 'icon'],
                6 => ['displayicon' => 'icon'],
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;


        $userInterfaceObjectMock = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $userInterfaceObjectMock->method('GetIsClassLoaded')->willReturn(true);

        $method = $this->getMethod('Tickets\Library\View\SWIFT_TicketViewRendererMock', 'RenderMassActionPanel');

        $this->assertTrue(is_string($method->invokeArgs($obj, [$userInterfaceObjectMock])),
            'Returns html string');

        $this->assertTrue(is_string($method->invokeArgs($obj, [$userInterfaceObjectMock])),
            'Returns html string');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGridItemCallbackReturnsTrue()
    {
        $obj = $this->getMocked();

        $uiGridFieldObject = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceGridField')
            ->disableOriginalConstructor()
            ->getMock();

        $uiGridFieldObject->method('GetIsClassLoaded')->will($this->onConsecutiveCalls(false, true, true));

        $uiGridObject = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceGrid')
            ->disableOriginalConstructor()
            ->getMock();

        $uiGridObject->method('GetSortFieldObject')->willReturn($uiGridFieldObject);
        $uiGridObject->method('GetRecordsPerPage')->willReturn(10);
        $uiGridObject->method('GetOffset')->willReturn(0);

        $query = '';

        $this->assertFalse($obj->GridItemCallback($uiGridObject, $query),
            'Returns false');

        static::$nextRecordType = static::NEXT_RECORD_NO_LIMIT;

        $this->assertTrue(is_array($obj->GridItemCallback($uiGridObject, $query)),
            'Returns array');

        $this->assertTrue(is_array($obj->GridItemCallback($uiGridObject, $query, true)),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetCustomFieldValuesReturnsString()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod('Tickets\Library\View\SWIFT_TicketViewRendererMock', 'GetCustomFieldValues');

        $customFieldsValues = [
            'fieldvalue' => '1',
            'customfieldid' => 1,
            'uniquehash' => 'testing',
            'typeid' => 1,
            'isserialized' => 1,
            'fieldtype' => \Base\Models\CustomField\SWIFT_CustomField::TYPE_FILE
        ];

        $this->assertTrue(is_string($method->invokeArgs($obj, [$customFieldsValues])),
            'Returns string value');

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['fileid' => 2, 'subdirectory' => '', 'filename' => '', 'originalfilename' => '']);
        $this->assertTrue(is_string($method->invokeArgs($obj, [$customFieldsValues])),
            'Returns string value');

        $customFieldsValues['fieldtype'] = \Base\Models\CustomField\SWIFT_CustomField::TYPE_SELECTLINKED;
        $this->assertTrue(is_string($method->invokeArgs($obj, [$customFieldsValues])),
            'Returns string value');

        $customFieldsValues['fieldtype'] = \Base\Models\CustomField\SWIFT_CustomField::TYPE_PASSWORD;
        $this->assertTrue(is_string($method->invokeArgs($obj, [$customFieldsValues])),
            'Returns string value');

        $customFieldsValues['fieldtype'] = \Base\Models\CustomField\SWIFT_CustomField::TYPE_SELECTMULTIPLE;
        $this->assertTrue(is_string($method->invokeArgs($obj, [$customFieldsValues])),
            'Returns string value');

        $customFieldsValues['fieldtype'] = \Base\Models\CustomField\SWIFT_CustomField::TYPE_CHECKBOX;
        $this->assertTrue(is_string($method->invokeArgs($obj, [$customFieldsValues])),
            'Returns string value');

        $customFieldsValues['fieldtype'] = \Base\Models\CustomField\SWIFT_CustomField::TYPE_RADIO;
        $this->assertTrue(is_string($method->invokeArgs($obj, [$customFieldsValues])),
            'Returns string value');

        $customFieldsValues['fieldtype'] = \Base\Models\CustomField\SWIFT_CustomField::TYPE_SELECT;
        $this->assertTrue(is_string($method->invokeArgs($obj, [$customFieldsValues])),
            'Returns string value');

        $customFieldsValues['fieldtype'] = \Base\Models\CustomField\SWIFT_CustomField::TYPE_DATE;
        $customFieldsValues['fieldvalue'] = '2018-10-8';
        $this->assertTrue(is_string($method->invokeArgs($obj, [$customFieldsValues])),
            'Returns string value');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetConcatenatedCustomFieldOptionsReturnsTrue()
    {
        $obj = $this->getMocked();

        $data = [
            'customfieldmapcache' => [
                1 => [
                    '_options' => [
                        1 => ['optionvalue' => '1'],
                        2 => ['optionvalue' => '1', 'parentcustomfieldoptionid' => '1'],
                    ]
                ]
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $method = $this->getMethod('Tickets\Library\View\SWIFT_TicketViewRendererMock', 'GetConcatenatedCustomFieldOptions');

        $customFieldOptionIDs = [];

        $this->assertEmpty($method->invokeArgs($obj, [$customFieldOptionIDs]),
            'Returns empty string');

        $customFieldOptionIDs = [
            'isserialized' => '1',
            'isencrypted' => '1',
            'fieldvalue' => '1',
            'customfieldid' => 1
        ];

        $this->assertInternalType('string', $method->invokeArgs($obj, [$customFieldOptionIDs]), 'Returns html string');

        $customFieldOptionIDs['isencrypted'] = '0';

        $this->assertInternalType('string', $method->invokeArgs($obj, [$customFieldOptionIDs]), 'Returns html string');

        $customFieldOptionIDs['isserialized'] = '0';
        $customFieldOptionIDs['fieldvalue'] = [2, [2 => 2]];

        $this->assertInternalType('string', $method->invokeArgs($obj, [$customFieldOptionIDs]), 'Returns html string');

        $customFieldOptionIDs['fieldvalue'] = 1;

        $this->assertInternalType('string', $method->invokeArgs($obj, [$customFieldOptionIDs]), 'Returns html string');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDispatchMenuOutputsString()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketviewcache' => [
                1 => [
                    'ticketviewid' => 1,
                    'staffid' => 1,
                    'viewscope' => 1
                ],
                2 => [
                    'ticketviewid' => 2,
                    'staffid' => 1,
                    'viewscope' => 2
                ]
            ],
            'staffcache' => [
                1 => [
                    'staffgroupid' => 5
                ]
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $this->expectOutputRegex('/^<ul.*/');
        $obj->DispatchMenu();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testChangeViewReturnsTrue()
    {
        $obj = $this->getMocked();

        $ticketObjectMock = $this->getMockBuilder('Tickets\Models\View\SWIFT_TicketView')
            ->disableOriginalConstructor()
            ->getMock();

        $ticketObjectMock->method('GetIsClassLoaded')->will($this->onConsecutiveCalls(true, false));
        $ticketObjectMock->method('GetTicketViewID')->willReturn(1);

        $this->assertTrue($obj->ChangeView($ticketObjectMock),
            'Returns true with permission');

        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->ChangeView($ticketObjectMock);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderTreeReturnsString()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketcountcache' => [
                'ownerstaff' => [
                    1 => ['totalunresolveditems' => 5]
                ],
                'unassigned' => [
                    1 => ['totalunresolveditems' => 5]
                ],
                'departments' => [
                    0 => [
                        'totalitems' => 5
                    ],
                    1 => [
                        'totalitems' => 10,
                        'totalunresolveditems' => 5,
                        'ticketstatus' => [
                            2 => [
                                'lastactivity' => time(),
                                'totalitems' => 5
                            ]
                        ],
                        'lastactivity' => time()
                    ]
                ]
            ],
            'departmentcache' => [
                1 => ['departmentid' => 1, 'departmentapp' => APP_TICKETS, 'parentdepartmentid' => '0',
                    'subdepartments' =>
                        [
                            2 => ['departmentid' => 2, 'uservisibilitycustom' => '0'],
                            4 => ['departmentid' => 4, 'uservisibilitycustom' => '0']
                        ]],
                2 => ['departmentid' => 2, 'departmentapp' => APP_TICKETS, 'parentdepartmentid' => 1],
                3 => ['departmentid' => 3, 'departmentapp' => APP_TICKETS, 'parentdepartmentid' => '0'],
                4 => ['departmentid' => 4, 'departmentapp' => APP_TICKETS, 'parentdepartmentid' => 1],
            ],
            'statuscache' => [
                1 => ['departmentid' => 10],
                2 => ['departmentid' => 0],
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturn([1, 2]);

        $this->assertTrue(is_string($obj->RenderTree()),
            'Returns html string');

        $this->assertTrue(is_string($obj->RenderTree('', 0)),
            'Returns html string');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderTreeDepartmentReturnsArray()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketcountcache' => [
                'departments' => [
                    1 => [
                        'departmentid' => 1,
                        'totalitems' => 10,
                        'totalunresolveditems' => 5,
                        'ticketstatus' => [
                            2 => [
                                'lastactivity' => time(),
                                'totalitems' => 5
                            ]
                        ],
                        'lastactivity' => time()
                    ],
                    2 => [
                        'departmentid' => 2,
                        'totalitems' => 10,
                        'totalunresolveditems' => '0',
                        'ticketstatus' => [
                            2 => [
                                'lastactivity' => time(),
                                'totalitems' => '0'
                            ]
                        ],
                        'lastactivity' => time()
                    ]
                ]
            ],
            'departmentcache' => [
                1 => ['departmentapp' => APP_TICKETS],
                2 => ['departmentapp' => APP_TICKETS],
                3 => ['departmentapp' => APP_TICKETS],
            ],
            'statuscache' => [
                1 => ['departmentid' => 10, 'displaycount' => 1],
                2 => ['departmentid' => 0, 'displaycount' => 1],
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $departmentContainer = [
            'departmentid' => 1
        ];

        $this->assertTrue(is_array($obj->RenderTreeDepartment($departmentContainer)),
            'Returns array');

        $departmentContainer = [
            'departmentid' => 2
        ];
        $this->assertTrue(is_array($obj->RenderTreeDepartment($departmentContainer)),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetTicketStatusIDListReturnsTrue()
    {
        $obj = $this->getMocked();

        $data = [
            'statuscache' => [
                1 => ['departmentid' => 0, 'markasresolved' => 0]
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $this->assertTrue(is_array($obj->GetTicketStatusIDList()),
            'Returns array');

        $_POST['_searchQuery'] = 'test data';

        $this->assertTrue(is_array($obj->GetTicketStatusIDList()),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetMyTicketsCounterReturnsTrue()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketcountcache' => [
                'ownerstaff' => [
                    1 => ['totalunresolveditems' => 5]
                ]
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $this->assertTrue(is_array($obj->GetMyTicketsCounter()),
            'Returns Array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetUnassignedCounterReturnsArray()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketcountcache' => [
                'unassigned' => [
                    1 => ['totalunresolveditems' => 5]
                ]
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $this->assertTrue(is_array($obj->GetUnassignedCounter()),
            'Returns Array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveOverdueContainerReturnsTrue()
    {
        $obj = $this->getMocked();
        $data = [
            'ticketcountcache' => [
                'ticketstatus' => [
                    3 => 6
                ],
                'departments' => [
                    1 => []
                ]
            ],
            'statuscache' => [
                1 => ['departmentid' => '10'],
                2 => ['departmentid' => '3', 'markasresolved' => '1'],
                3 => ['departmentid' => '0', 'markasresolved' => '0'],
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $_SWIFT->Database->method('QueryFetch')->willReturn(['totalitems' => 5]);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStaff->method('GetIsClassLoaded')->will($this->onConsecutiveCalls(true, true, false));
        $mockStaff->method('GetPermission')->will($this->onConsecutiveCalls('0', '1', '1', '0'));
        $_SWIFT->Staff = $mockStaff;

        $this->assertTrue(is_array($obj->RetrieveOverdueContainer()),
            'Returns array');

        $this->assertTrue(is_array($obj->RetrieveOverdueContainer()),
            'Returns array');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RetrieveOverdueContainer();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDashboardDepartmentProgressReturnsArray()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketcountcache' => [
                'departments' => [
                    1 => ['totalunresolveditems' => 5],
                    2 => ['totalunresolveditems' => 5],
                    3 => ['totalunresolveditems' => 5],
                ]
            ],
            'departmentcache' => [
                1 => ['departmentid' => 1, 'departmentapp' => APP_TICKETS, 'parentdepartmentid' => '0',
                    'subdepartments' =>
                        [
                            2 => ['departmentid' => 2, 'uservisibilitycustom' => '0'],
                            4 => ['departmentid' => 4, 'uservisibilitycustom' => '0']
                        ]],
                2 => ['departmentid' => 2, 'departmentapp' => APP_TICKETS, 'parentdepartmentid' => 1],
                3 => ['departmentid' => 3, 'departmentapp' => APP_TICKETS, 'parentdepartmentid' => '0'],
                4 => ['departmentid' => 4, 'departmentapp' => APP_TICKETS, 'parentdepartmentid' => 1],
                5 => ['departmentid' => 5, 'departmentapp' => APP_TICKETS, 'parentdepartmentid' => '0'],
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturn([1, 2, 3]);

        $this->assertTrue(is_array($obj->GetDashboardDepartmentProgress()),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDashboardStatusProgressReturnsArray()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketcountcache' => [
                'ticketstatus' => [
                    3 => 6
                ],
                'departments' => [
                    0 => [],
                    1 => [
                        'ticketstatus' => [
                            3 => ['totalitems' => 5]
                        ]
                    ]
                ]
            ],
            'statuscache' => [
                1 => ['departmentid' => '10'],
                2 => ['departmentid' => '3', 'markasresolved' => '1'],
                3 => ['departmentid' => '0', 'markasresolved' => '0', 'statusbgcolor' => 'red'],
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturn([1, 2, 3]);

        $this->assertTrue(is_array($obj->GetDashboardStatusProgress()),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDashboardOwnerProgressReturnsArray()
    {
        $obj = $this->getMocked();

        $data = [
            'ticketcountcache' => [
                'ownerstaff' => [[], 1 => []],
                'unassigned' => [
                    1 => ['totalunresolveditems' => 5]
                ],
                'departments' => [
                    0 => [],
                    1 => ['ownerstaff' => [1 => ['totalunresolveditems' => 5]]]
                ]
            ],
            'staffcache' => [
                1 => [
                    'fullname' => 'Test User',
                    'staffgroupid' => 1
                ],
                2 => []
            ],
            'staffassigncache' => [
                1 => [1, 2, 3]
            ],
            'departmentcache' => [
                1 => ['departmentapp' => APP_TICKETS]
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturn([1, 2, 3]);

        $this->assertTrue(is_array($obj->GetDashboardOwnerProgress()),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDashboardTypeProgressReturnsArray()
    {
        $obj = $this->getMocked();

        $data = [
            'tickettypecache' => [
                1 => ['title' => 'Test', 'departmentid' => 0],
                2 => ['title' => 'Test', 'departmentid' => 0],
                3 => ['title' => 'Test', 'departmentid' => 1000000]
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $arr = [
            'tickettypeid' => 1,
            'totalitems' => 4
        ];
        $_SWIFT->Database->method('Query')->willReturn($arr);
        $_SWIFT->Database->Record = $arr;

        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturn([1, 2, 3]);

        $this->assertTrue(is_array($obj->GetDashboardTypeProgress()),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetDashboardPriorityProgressReturnsArray()
    {
        $obj = $this->getMocked();

        $data = [
            'prioritycache' => [
                1 => ['title' => 'Test', 'bgcolorcode' => 'blue'],
                2 => ['title' => 'Test 2']
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $_SWIFT = \SWIFT::GetInstance();

        $obj->Cache = $cacheMock;
        $_SWIFT->Cache = $cacheMock;

        $arr = [
            'priorityid' => 1,
            'totalitems' => 4
        ];
        $_SWIFT->Database->method('Query')->willReturn($arr);
        $_SWIFT->Database->Record = $arr;

        $this->assertTrue(is_array($obj->GetDashboardPriorityProgress()),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveCustomFieldIDListReturnsArray()
    {
        $obj = $this->getMocked();

        $data = [
            'customfieldidcache' => [
                'ticketcustomfieldidlist' => [1],
                'usercustomfieldidlist' => [1],
                'userorganizationcustomfieldidlist' => [1],
            ]
        ];

        $cacheMock = $this->getCacheMock($data);

        $obj->Cache = $cacheMock;
        \SWIFT::GetInstance()->Cache = $cacheMock;

        $this->assertTrue(is_array($obj->RetrieveCustomFieldIDList()),
            'Returns array');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketViewRendererMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\View\SWIFT_TicketViewRendererMock');
    }
}

class SWIFT_TicketViewRendererMock extends SWIFT_TicketViewRenderer
{
    public $Cache;

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

