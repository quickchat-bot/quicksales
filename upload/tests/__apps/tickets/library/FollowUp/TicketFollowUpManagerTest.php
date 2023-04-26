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

namespace Tickets\Library\FollowUp;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class TicketFollowUpManagerTest
 * @group tickets
 * @group tickets-lib4
 */
class TicketFollowUpManagerTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testExecutePendingReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->setNextRecordLimit(0);
        $this->assertFalse($obj::ExecutePending(), 'Returns false without records');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'staffid' => 1,
            'ticketfollowupid' => 1,
            'dochangeproperties' => 1,
            'ownerstaffid' => 1,
            'ticketstatusid' => 1,
            'tickettypeid' => 1,
            'priorityid' => 1,
            'dochangeduedateline' => 1,
            'duedateline' => 1,
            'resolutionduedateline' => 1,
            'timeworked' => 1,
            'timebillable' => 1,
            'donote' => 1,
            'replycontents' => 1,
            'doforward' => 1,
            'forwardcontents' => 1,
            'forwardemailto' => 'me@mail.com',
            'subject' => 1,
            'duetime' => 1,
            'fullname' => 1,
            'tickettimetrackid' => 1,
            'timebilled' => 1,
            'userid' => 1,
            'ticketnotes' => 1,
            'notecolor' => 1,
            'notetype' => 1,
            'userorganizationid' => 1,
            'doreply' => 1,
            'emailqueueid' => 0,
            'email' => 'me@mail.com',
            'userdesignation' => '',
            'organizationname' => '',
            'salutation' => '',
            'organizationnameticketslaplanid' => '',
            'ticketslaplanid' => 0,
            'slaplanid' => 0,
            'firstresponsetime' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'ticketpostid' => 1,
            'ticketmaskid' => 0,
            'trasholddepartmentid' => 0,
            'tgroupid' => 1,
            'dateline' => 0,
            'isthirdparty' => 0,
            'isprivate' => 0,
            'creator' => 1,
            'contents' => 1,
            'languageid' => 1,
            'languageengineid' => 1,
            'ishtml' => 0,
            'replyto' => 0,
            'ticketnotificationid' => 1,
            'lastactivity' => 0,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        static::$databaseCallback['CacheGet'] = function ($x) {
            if (strpos($x, 'en-us') === 0) {
                return [
                    'log_newticket' => '%d %s',
                    'watcherprefix' => '%s %s',
                    'activitytrashticket' => '%s %s %s %s',
                    'log_newreply' => '%d',
                    'notification_department' => '1',
                    'notification_resolutiondue' => '1',
                    'notification_due' => '1',
                    'notification_flag' => '1',
                ];
            }

            return [
                1 => [
                    1 => 1,
                    'languageid' => '1',
                    'regusergroupid' => '1',
                    'departmentapp' => 'tickets',
                    'languagecode' => 'en-us',
                    'tgroupid' => '1',
                ],
            ];
        };

        \SWIFT::Set('loopcontrol', true);

        $this->setNextRecordNoLimit();

        $this->assertTrue($obj::ExecutePending());

        static::$databaseCallback['QueryLimit'] = function ($x) {
            if (false !== strpos($x, 'tickets WHERE ticketid IN (')) {
                static::$_prop['ticketid'] = 3;
            }
        };
        static::$databaseCallback['NextRecord'] = function () {
            if (isset(static::$_prop['ticketid'])) {
                 \SWIFT::GetInstance()->Database->Record['ticketid'] = static::$_prop['ticketid'];
            }
        };
        $this->assertTrue($obj::ExecutePending());

        unset(static::$_prop['ticketid']);
        static::$databaseCallback['QueryLimit'] = function ($x) {
            if (false !== strpos($x, 'staff WHERE staffid IN (')) {
                static::$_prop['staffid'] = 3;
            }
        };
        static::$databaseCallback['NextRecord'] = function () {
            if (isset(static::$_prop['staffid'])) {
                \SWIFT::GetInstance()->Database->Record['staffid'] = static::$_prop['staffid'];
            }
        };
        $this->assertTrue($obj::ExecutePending());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_TicketFollowUpManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\FollowUp\SWIFT_TicketFollowUpManagerMock');
    }
}

class SWIFT_TicketFollowUpManagerMock extends SWIFT_TicketFollowUpManager
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

