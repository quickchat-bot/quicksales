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

namespace Tickets\Library\Search;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class TicketSearchManagerTest
 * @group tickets
 * @group tickets-search
 */
class TicketSearchManagerTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchTicketIDReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertEmpty($obj::SearchTicketID(''));

        $this->setNextRecordNoLimit();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => &static::$_prop['ticketid'],
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (isset(static::$_prop['d'])) {
                unset(static::$_prop['c']);
                $arr['ticketid'] = 1;
            }
            if (isset(static::$_prop['c'])) {
                static::$_prop['d'] = 1;
                $arr['ticketid'] = 0;
            }
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;
        $this->assertEmpty($obj::SearchTicketID('1', false, [1]));

        static::$_prop['ticketid'] = 1;
        $this->assertNotEmpty($obj::SearchTicketID('1', false, [1]));

        static::$_prop['c'] = 1;
        $this->assertNotEmpty($obj::SearchTicketID('1', false, [1]));

        $this->assertNotEmpty($obj::SearchTicketID('no', false, [1]));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveOnOwnerReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();
        // advnace permission
        $_SWIFT->Staff->GetPermission('staff_tcanviewunassign');
        $this->assertEmpty($obj::RetrieveOnOwner($_SWIFT->Staff, 2));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchCreatorReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertEmpty($obj::SearchCreator(''));

        $this->setNextRecordNoLimit();
        static::$databaseCallback['QueryLimit'] = function ($x) {
            if (false !== strpos($x, 'SELECT ticketid FROM')) {
                // increase counter
                static::$nextRecordCount++;
            }
        };

        \SWIFT::GetInstance()->Database->Record['ticketid'] = 1;

        $this->assertNotEmpty($obj::SearchCreator('q'));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchEmailReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertEmpty($obj::SearchEmail(''));

        $this->setNextRecordNoLimit();
        static::$databaseCallback['QueryLimit'] = function ($x) {
            if (false !== strpos($x, 'SELECT ticketid FROM swtickets')) {
                // increase counter
                static::$nextRecordCount++;
            }
            if (false !== strpos($x, 'SELECT ticketid FROM swticketposts')) {
                // increase counter
                static::$nextRecordCount = 0;
            }
        };

        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Database->Record['ticketid'] = 1;

        $this->assertNotEmpty($obj::SearchEmail('q', $_SWIFT->Staff));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchSubjectReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $this->assertEmpty($obj::SearchSubject(''));

        $this->assertNotEmpty($obj::SearchSubject('q', $_SWIFT->Staff));

        $this->setNextRecordNoLimit();
        static::$databaseCallback['QueryLimit'] = function ($x) {
            if (false !== strpos($x, 'SELECT ticketid FROM swtickets')) {
                // increase counter
                // static::$nextRecordCount++;
            }
            if (false !== strpos($x, 'SELECT ticketid FROM swticketposts')) {
                // increase counter
                static::$nextRecordCount = 0;
            }
        };

        $_SWIFT->Database->Record['ticketid'] = 1;

        $this->assertNotEmpty($obj::SearchSubject('q', $_SWIFT->Staff));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchFullNameReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertEmpty($obj::SearchFullName(''));

        $this->setNextRecordNoLimit();
        static::$databaseCallback['QueryLimit'] = function ($x) {
            if (false !== strpos($x, 'SELECT ticketid FROM swtickets')) {
                // increase counter
                static::$nextRecordCount++;
            }
            if (false !== strpos($x, 'SELECT ticketid FROM swticketposts')) {
                // increase counter
                static::$nextRecordCount = 0;
            }
        };

        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Database->Record['ticketid'] = 1;

        $this->assertNotEmpty($obj::SearchFullName('q', $_SWIFT->Staff));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testQuickSearchReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        $_SWIFT->Staff->method('GetAssignedDepartments')->willReturn([1]);

        $_SWIFT->Database->Record['ticketid'] = 1;

        $this->setNextRecordNoLimit();

        static::$databaseCallback['QueryLimit'] = function ($x) {
            if (false !== strpos($x, 'SELECT ticketid FROM swtickets')) {
                // increase counter
                static::$nextRecordCount++;
            }
            if (false !== strpos($x, 'SELECT ticketid FROM swticketposts')) {
                // increase counter
                static::$nextRecordCount = 0;
            }
        };

        static::$databaseCallback['Query'] = function ($x) {
            if (false !== strpos($x, 'SELECT ticketid FROM swtickets')) {
                // increase counter
                static::$nextRecordCount = 0;
                \SWIFT::GetInstance()->Database->Record['ticketid']++;
            }
        };

        $this->assertNotEmpty($obj::QuickSearch('q', $_SWIFT->Staff, 1));

        $this->assertNotEmpty($obj::QuickSearch('q', false, 1));
    }

    public function testSearchRulesReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->setNextRecordNoLimit();
        \SWIFT::GetInstance()->Database->Record = [
            'subobjid' => 2,
            'objid' => 1,
            'ticketid' => 1,
        ];

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x === 't_cthirdparty') {
                return 0;
            }

            return 1;
        };

        static::$databaseCallback['NextRecord'] = function () {
            \SWIFT::GetInstance()->Database->Record['ticketid']++;

            return static::$nextRecordCount % 2;
        };

        $criteria = [
            ['due', '', 1],
            ['due', '', 0],
            ['duerange', '', 0],
            ['message', '', 1],
            ['messagelike', '', 0],
            ['user', '', 0],
            ['userorganization', '', 0],
            ['usergroup', '', 0],
            ['ticketnotes', '', 0],
            ['fullname', '', 0],
            ['tag', '', 0],
            ['email', '', 0],
            ['owner', '', -1],
            ['customfield__', '', 0],
        ];
        $this->assertNotEmpty($obj::SearchRules($criteria, 1, \SWIFT::GetInstance()->Staff));
    }

    /**
     * @throws \ReflectionException
     */
    public function testParseTicketJoinIDListReturnsArray()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('ParseTicketJoinIDList');
        $method->setAccessible(true);

        $this->assertNotEmpty($method->invoke($obj, [[1]], 1));

        $this->assertNotEmpty($method->invoke($obj, [[1]], 2));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSupportCenterSearchReturnsAdday()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'searchstoreid' => 1,
            'dataid' => 1,
            'staffid' => 1,
            'ticketrecipientid' => 1,
            'ticketemailid' => 1,
            'recipienttype' => 1,
            'email' => 'me@mail.com',
            'creator' => 1,
            'ticketpostid' => 1,
            'fullname' => 1,
            'contents' => 'contents 1',
            'userid' => 1,
            'creationmode' => '1',
            'subject' => 'subject 1',
            'emailto' => 'me@mail.com',
            'ishtml' => 1,
            'isthirdparty' => '0',
            'issurveycomment' => 0,
            'dateline' => 0,
            'isprivate' => 0,
            'ticketmaskid' => 0,
            'lastactivity' => 0,
            'attachmentid' => 1,
            'notbase64' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$databaseCallback['NextRecord'] = function () {
            if (static::$nextRecordCount === 10) {
                \SWIFT::GetInstance()->Database->Record['isthirdparty'] = 1;
                \SWIFT::GetInstance()->Database->Record['ticketpostid']++;
                static::$nextRecordCount++;
            }
        };

        $this->setNextRecordNoLimit();

        $this->assertNotEmpty($obj::SupportCenterSearch('1'));

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x === 't_cthirdparty') {
                return 0;
            }

            return 1;
        };

        $this->assertNotEmpty($obj::SupportCenterSearch(1));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetSearchTagsReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->setNextRecordLimit(0);
        $this->assertEmpty($obj::GetSearchTags('q'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketSearchManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\Search\SWIFT_TicketSearchManagerMock');
    }
}

class SWIFT_TicketSearchManagerMock extends SWIFT_TicketSearchManager
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

