<?php
namespace LiveChat\Staff;

use Base\Library\Rules\SWIFT_Rules;
use Base\Models\Staff\SWIFT_Staff;
use LiveChat\Models\Chat\SWIFT_ChatSearch;
use SWIFT;
use SWIFT_TestCase;

class Controller_ChatHistoryTest extends SWIFT_TestCase
{
    public function providerSearchSubmit()
    {
        return [
            [ // Test equals type of search
                [
                    'criteriaoptions' => SWIFT_ChatSearch::RULE_MATCHALL,
                    'rulecriteria' => [
                        [SWIFT_ChatSearch::CHATSEARCH_DEPARTMENT, SWIFT_ChatSearch::OP_EQUAL, '1'] // Use conversation contains yyy
                    ]
                ],
                "SELECT chatobjects.chatobjectid FROM swchatobjects AS chatobjects
                WHERE (chatobjects.departmentid = '1')
                ORDER BY chatobjects.dateline ASC"
            ],
            [ // Test full text search
                [
                    'criteriaoptions' => SWIFT_ChatSearch::RULE_MATCHALL,
                    'rulecriteria' => [
                        [SWIFT_ChatSearch::CHATSEARCH_CONVERSATIONNGRAM, SWIFT_ChatSearch::OP_CONTAINS, 'test'] // Use conversation contains yyy
                    ]
                ],
                "SELECT chatobjects.chatobjectid FROM swchatobjects AS chatobjects
                WHERE (chatobjectid IN (SELECT objid FROM swsearchindex WHERE (type = '3' AND MATCH (ft) AGAINST (' +(test)  +(__SWIFTSEARCHENGINETYPE3)' IN BOOLEAN MODE))))
                ORDER BY chatobjects.dateline ASC"
            ],
            [ // Test SQL like search
                [
                    'criteriaoptions' => SWIFT_ChatSearch::RULE_MATCHALL,
                    'rulecriteria' => [
                        [SWIFT_ChatSearch::CHATSEARCH_CONVERSATIONSQL, SWIFT_ChatSearch::OP_CONTAINS, 'test'] // Use conversation contains yyy
                    ]
                ],
                "SELECT chatobjects.chatobjectid FROM swchatobjects AS chatobjects
                WHERE (chatobjectid IN (SELECT chatobjects.chatobjectid FROM swchatobjects AS chatobjects
                            LEFT JOIN swchattextdata AS chattextdata ON (chatobjects.chatobjectid = chattextdata.chatobjectid)
                            WHERE (chattextdata.contents LIKE '%test%')))
                ORDER BY chatobjects.dateline ASC"
            ],
            [ // Test equals and full text search
                [
                    'criteriaoptions' => SWIFT_ChatSearch::RULE_MATCHALL,
                    'rulecriteria' => [
                        [SWIFT_ChatSearch::CHATSEARCH_DEPARTMENT, SWIFT_ChatSearch::OP_EQUAL, '1'],
                        [SWIFT_ChatSearch::CHATSEARCH_CONVERSATIONNGRAM, SWIFT_ChatSearch::OP_CONTAINS, 'test'] // Use conversation contains yyy
                    ]
                ],
                "SELECT chatobjects.chatobjectid FROM swchatobjects AS chatobjects
                WHERE (chatobjects.departmentid = '1' AND chatobjectid IN (SELECT objid FROM swsearchindex WHERE (type = '3' AND MATCH (ft) AGAINST (' +(test)  +(__SWIFTSEARCHENGINETYPE3)' IN BOOLEAN MODE))))
                ORDER BY chatobjects.dateline ASC"
            ],
            [ // Test equals or full text search
                [
                    'criteriaoptions' => SWIFT_Rules::RULE_MATCHANY,
                    'rulecriteria' => [
                        [SWIFT_ChatSearch::CHATSEARCH_DEPARTMENT, SWIFT_ChatSearch::OP_EQUAL, '1'],
                        [SWIFT_ChatSearch::CHATSEARCH_CONVERSATIONNGRAM, SWIFT_ChatSearch::OP_CONTAINS, 'test'] // Use conversation contains yyy
                    ]
                ],
                "SELECT chatobjects.chatobjectid FROM swchatobjects AS chatobjects
                WHERE (chatobjects.departmentid = '1' OR chatobjectid IN (SELECT objid FROM swsearchindex WHERE (type = '3' AND MATCH (ft) AGAINST (' +(test)  +(__SWIFTSEARCHENGINETYPE3)' IN BOOLEAN MODE))))
                ORDER BY chatobjects.dateline ASC"
            ],
            [ // Test equals and SQL like search
                [
                    'criteriaoptions' => SWIFT_ChatSearch::RULE_MATCHALL,
                    'rulecriteria' => [
                        [SWIFT_ChatSearch::CHATSEARCH_DEPARTMENT, SWIFT_ChatSearch::OP_EQUAL, '1'],
                        [SWIFT_ChatSearch::CHATSEARCH_CONVERSATIONSQL, SWIFT_ChatSearch::OP_CONTAINS, 'test'] // Use conversation contains yyy
                    ]
                ],
                "SELECT chatobjects.chatobjectid FROM swchatobjects AS chatobjects
                WHERE (chatobjects.departmentid = '1' AND chatobjectid IN (SELECT chatobjects.chatobjectid FROM swchatobjects AS chatobjects
                            LEFT JOIN swchattextdata AS chattextdata ON (chatobjects.chatobjectid = chattextdata.chatobjectid)
                            WHERE (chattextdata.contents LIKE '%test%')))
                ORDER BY chatobjects.dateline ASC"
            ],
            [ // Test equals or full text search
                [
                    'criteriaoptions' => SWIFT_Rules::RULE_MATCHANY,
                    'rulecriteria' => [
                        [SWIFT_ChatSearch::CHATSEARCH_DEPARTMENT, SWIFT_ChatSearch::OP_EQUAL, '1'],
                        [SWIFT_ChatSearch::CHATSEARCH_CONVERSATIONSQL, SWIFT_ChatSearch::OP_CONTAINS, 'test'] // Use conversation contains yyy
                    ]
                ],
                "SELECT chatobjects.chatobjectid FROM swchatobjects AS chatobjects
                WHERE (chatobjects.departmentid = '1' OR chatobjectid IN (SELECT chatobjects.chatobjectid FROM swchatobjects AS chatobjects
                            LEFT JOIN swchattextdata AS chattextdata ON (chatobjects.chatobjectid = chattextdata.chatobjectid)
                            WHERE (chattextdata.contents LIKE '%test%')))
                ORDER BY chatobjects.dateline ASC"
            ],
        ];
    }

    /**
     * @dataProvider providerSearchSubmit
     */
    public function testSearchSubmit($post, $expectedQuery)
    {
        $_SWIFT = $this->getMockServices();

        $_SWIFT['Staff']->method('GetID')
            ->willReturn(1);

        $_SWIFT['Session']->method('GetSessionID')
            ->willReturn(1);

        $_SWIFT['Database']->method('Escape')
            ->will($this->returnCallback(
                function($query) {
                    $db = new \SWIFT_Database();
                    return $db->Escape($query);
                }));

        $_POST = $post;

        $controller = new Controller_ChatHistoryMock();

        $controller->Database = $this->createMock(\SWIFT_Database::class);
        $controller->Database
            ->method('QueryLimit')
            ->will($this->returnCallback(
                function($query, $limit) use ($expectedQuery) {
                    $this->assertEquals($expectedQuery, $query);
                }));

        $controller->Language = $this->createMock(\SWIFT_LanguageEngine::class);
        $controller->Language
            ->method('Get')
            ->willReturn('Test');

        $controller->Load = $this->createMock(Controller_ChatHistory::class);
        $controller->Load
            ->method('Manage')
            ->willReturn(true);

        $controller->dummyManage = true;

        $controller->SearchSubmit();
    }
}

class Controller_ChatHistoryMock extends Controller_ChatHistory
{
    public $Database;
    public $Language;

    public function __construct()
    {
        // Disable the constructor
        return;
    }

    public function GetIsClassLoaded()
    {
        return true;
    }
}
