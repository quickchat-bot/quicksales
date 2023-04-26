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

namespace Tickets\Client;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_SurveyTest
 * @group tickets
 * @group tickets-client
 */
class Controller_SurveyTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Client\Controller_Survey', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $mockInput = $this->getMockBuilder('SWIFT_Input')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEmoji = $this->getMockBuilder('SWIFT_Emoji')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getMocked([
            'Input' => $mockInput,
            'Emoji' => $mockEmoji,
        ]);

        $this->assertFalse($obj->Index(),
            'Returns false without id');

        $this->assertFalse($obj->Index(1, 'hash'),
            'Returns false with invalid id');

        static::$_prop['userid'] = 0;

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'userid' => &static::$_prop['userid'],
            'usergroupid' => 1,
            'useremailid' => 1,
            'ratingid' => 1,
            'typeid' => 1,
            'linktype' => 1,
            'linktypeid' => 1,
            'ticketmaskid' => 0,
            'lastactivity' => 0,
            'email' => 'me@mail.com',
            'replyto' => 'me2@mail.com',
            'fullname' => 'fullname',
            'subject' => 'subject',
            'tickethash' => 'hash',
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, "userid = '1'")) {
                static::$_prop['userid'] = 1;
            }

            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $_SWIFT->Database->Record3 = [
            'ratingvisibility' => 'public',
            'ratingid' => 1,
            'departmentid' => 1,
            'iseditable' => 0,
        ];

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $tgroup = $this->getMockBuilder('Base\Models\Template\SWIFT_TemplateGroup')
            ->disableOriginalConstructor()
            ->getMock();
        $tgroup->method('GetProperty')->willReturn(1);
        $_SWIFT->TemplateGroup = $tgroup;

        $this->assertTrue($obj->Index(1, 'hash'));

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    'departmentapp' => 'tickets',
                    'parentdepartmentid' => &static::$_prop['parentdepartmentid'],
                    'departmenttype' => 'public',
                ],
            ];
        };

        static::$_prop['parentdepartmentid'] = 0;
        $this->assertTrue($obj->Index(1, 'hash'));

        static::$_prop['parentdepartmentid'] = 1;
        $this->assertTrue($obj->Index(1, 'hash'));

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSurveySubmitReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->SurveySubmit(1, 'hash'),
            'Returns false with invalid id');

        static::$_prop['userid'] = 0;

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'userid' => &static::$_prop['userid'],
            'isresolved' => 1,
            'tickethash' => 'hash',
            'userdesignation' => '',
            'salutation' => '',
            'usergroupid' => 1,
            'useremailid' => 1,
            'ratingid' => 1,
            'typeid' => 1,
            'linktype' => 1,
            'ticketslaplanid' => 0,
            'slaplanid' => 0,
            'ticketpostid' => 1,
            'ticketmaskid' => 0,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'lastactivity' => 0,
            'iseditable' => 0,
            'email' => 'me@mail.com',
            'replyto' => 'me2@mail.com',
            'fullname' => 'fullname',
            'subject' => 'subject',
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, "userid = '1'")) {
                static::$_prop['userid'] = 1;
            }
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->SurveySubmit(1, 'hash'),
            'Returns false with invalid user');

        $_SWIFT->Database->Record3 = [
            'ratingvisibility' => 'public',
            'ratingid' => 1,
            'departmentid' => 1,
            'iseditable' => &static::$_prop['iseditable'],
        ];

        $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);

        static::$databaseCallback['NextRecord'] = function () {
            if (isset(static::$_prop['stop'])) {
                return false;
            }
            return static::$nextRecordCount % 2;
        };

        static::$databaseCallback['Query'] = function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                static::$_prop['stop'] = true;
            }
        };

        static::$_prop['userid'] = 1;
        static::$_prop['iseditable'] = 0;
        $this->assertTrue($obj->SurveySubmit(1, 'hash'));

        $_POST['rating'][1] = 1;
        static::$_prop['iseditable'] = 1;
        $_POST['replycontents'] = 'replycontents';
        $this->assertTrue($obj->SurveySubmit(1, 'hash'));

        unset($_POST['rating']);
        $this->assertTrue($obj->SurveySubmit(1, 'hash'));

        $this->assertClassNotLoaded($obj, 'SurveySubmit', 1, 'hash');
    }

    /**
     * @throws \ReflectionException
     */
    public function test_GetTicketObjectReturnsObject()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_GetTicketObject');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 0, 'hash'));
        $this->assertFalse($method->invoke($obj, 'no', 'hash'));

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'userid' => 1,
            'email' => 'me@mail.com',
            'isresolved' => 1,
            'tickethash' => 'hash',
        ];
        static::$_prop['c'] = 2;
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, 'oldticketid')) {
                static::$_prop['c']--;
                return ['ticketid' => static::$_prop['c']];
            }

            return $arr;
        });

        $_SWIFT->Database->Record = $arr;

        $this->assertInstanceOf('Tickets\Models\Ticket\SWIFT_Ticket', $method->invoke($obj, 1, 'hash'));

        $_SWIFT->User = $this->getMockBuilder('Base\Models\User\SWIFT_User')
            ->disableOriginalConstructor()
            ->getMock();
        $_SWIFT->User->method('GetIsClassLoaded')->willReturnOnConsecutiveCalls(false, true);
        $_SWIFT->User->method('GetEmailList')->willReturn(['me@mail.com']);

        $this->assertFalse($method->invoke($obj, 2, 'nohash'));

        static::$_prop['c'] = 2;
        $this->assertInstanceOf('Tickets\Models\Ticket\SWIFT_Ticket', $method->invoke($obj, 2, 'nohash'));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, 'hash');
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_SurveyMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Client\Controller_SurveyMock', $services);
    }
}

class Controller_SurveyMock extends Controller_Survey
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

