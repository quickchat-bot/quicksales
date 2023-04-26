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

namespace Tickets\Library\Escalation;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class EscalationRuleManagerTest
 * @group tickets
 * @group tickets-lib3
 */
class EscalationRuleManagerTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Library\Escalation\SWIFT_EscalationRuleManager', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRunReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'slaplanid' => &static::$_prop['slaplanid'],
            'resolutionduedateline' => 1,
            'duetime' => 1,
            'ownerstaffid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'tickettypeid' => 1,
            'ruletype' => 1,
            'isenabled' => 1,
            'title' => 1,
            'lastpostid' => 1,
            'ticketpostid' => 1,
            'userid' => 0,
            'staffid' => 1,
            'creator' => 1,
            'tgroupid' => 1,
            'subject' => 1,
            'fullname' => 1,
            'phoneno' => 1,
            'dateline' => 0,
            'lastactivity' => 0,
            'userorganizationid' => 0,
            'hasratings' => 0,
            'emailqueueid' => 0,
            'hasnotes' => 0,
            'isthirdparty' => 1,
            'ticketmaskid' => 0,
            'ticketslaplanid' => 0,
            'escalationlevelcount' => 0,
            'notificationtype' => &static::$_prop['notificationtype'],
            'email' => 'me@mail.com',
            'escalationruleid' => &static::$_prop['escalationruleid'],
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->setNextRecordNoLimit();

        \SWIFT::Set('loopcontrol', true);

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => [1 => 1],
                    'slaplanid' => 1,
                    'staffid' => 1,
                    'ruletype' => 1,
                    'departmentid' => 1,
                    'ticketstatusid' => 1,
                    'priorityid' => 1,
                    'tickettypeid' => 1,
                    'flagtype' => 1,
                    'addtags' => 0,
                    'removetags' => 0,
                    'newslaplanid' => 1,
                    'escalationruleid' => 1,
                ],
            ];
        };

        static::$_prop['slaplanid'] = 2;
        $this->assertTrue($obj->Run());

        static::$_prop['slaplanid'] = 1;
        static::$_prop['escalationruleid'] = 1;
        $this->assertTrue($obj->Run());

        static::$_prop['slaplanid'] = 1;
        static::$_prop['escalationruleid'] = 2;
        static::$_prop['notificationtype'] = 'user';
        $this->assertTrue($obj->Run());

        static::$_prop['notificationtype'] = 'userorganization';
        static::$_prop['escalationruleid'] = 2;
        $this->assertTrue($obj->Run());

        static::$_prop['notificationtype'] = 'staff';
        static::$_prop['escalationruleid'] = 2;
        $this->assertTrue($obj->Run());

        static::$_prop['notificationtype'] = 'team';
        static::$_prop['escalationruleid'] = 2;
        $this->assertTrue($obj->Run());

        static::$_prop['notificationtype'] = 'department';
        static::$_prop['escalationruleid'] = 2;
        $this->assertTrue($obj->Run());

        static::$_prop['notificationtype'] = 'other';
        static::$_prop['escalationruleid'] = 2;
        $this->assertTrue($obj->Run());
    }

    /**
     * @throws \ReflectionException
     */
    public function testRetrieveEscalationRuleObjectsThrowsException()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'RetrieveEscalationRuleObjects');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, $ticket);
    }

    /**
     * @throws \ReflectionException
     */
    public function testRetrieveEscalationRuleObjectsReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'RetrieveEscalationRuleObjects');

        $ticket = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_Ticket')
            ->disableOriginalConstructor()
            ->getMock();
        $ticket->method('GetTicketID')->willReturn(1);
        $ticket->method('GetIsClassLoaded')->willReturn(true);
        $ticket->method('GetProperty')->willReturnCallback(function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }

            return 1;
        });

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => [1 => 1],
                    'slaplanid' => 1,
                    'ruletype' => &static::$_prop['ruletype'],
                    'escalationruleid' => 1,
                ],
            ];
        };

        static::$_prop['ruletype'] = 1;
        $this->assertNotEmpty($method->invoke($obj, $ticket));

        static::$_prop['ruletype'] = 3;
        static::$_prop['resolutionduedateline'] = 1;
        $this->assertNotEmpty($method->invoke($obj, $ticket));

        static::$_prop['ruletype'] = 2;
        static::$_prop['resolutionduedateline'] = 1;
        $this->assertNotEmpty($method->invoke($obj, $ticket));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, $ticket);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_EscalationRuleManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\Escalation\SWIFT_EscalationRuleManagerMock');
    }
}

class SWIFT_EscalationRuleManagerMock extends SWIFT_EscalationRuleManager
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

