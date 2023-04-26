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

namespace Tickets\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_TicketPostTest
 * @group tickets
 * @group tickets-api
 */
class Controller_TicketPostTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Api\Controller_TicketPost', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->GetList(),
            'Returns true without errors');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testListAllReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->ListAll(1),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'ticketpostid' => 1,
            'hasattachments' => 1,
            'attachmentid' => 1,
            'filename' => 'file.txt',
            'filesize' => 1,
            'filetype' => 'file',
            'storefilename' => 'file.txt',
            'attachmenttype' => 0,
        ];
        static::$databaseCallback['Query'] = function ($x) use ($_SWIFT) {
            if (false !== strpos($x, "ticketid = '2'")) {
                $_SWIFT->Database->Record['attachmentid'] = 0;
            } else {
                $_SWIFT->Database->Record['attachmentid'] = 1;
            }
        };

        $_SWIFT->Database->Record = $arr;

        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($_SWIFT) {
            return $_SWIFT->Database->Record;
        });

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x === 't_postorder') {
                return 'desc';
            }
        };

        $this->setNextRecordType(self::NEXT_RECORD_NO_LIMIT);

        $this->assertFalse($obj->ListAll(2, 1),
            'Returns false without attachment');

        $this->assertTrue($obj->ListAll(1, 1));

        $this->assertClassNotLoaded($obj, 'ListAll', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Get(1, 1),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, "ticketpostid = '2'")) {
                $arr['ticketpostid'] = 2;
            }

            if (false !== strpos($x, "ticketpostid = '1'")) {
                $arr['ticketpostid'] = 1;
            }

            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Get(2, 3));
        $this->assertFalse($obj->Get(2, 2));
        $this->assertTrue($obj->Get(1, 1));

        $this->assertClassNotLoaded($obj, 'Get', 1, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Delete(1, 1),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, "ticketpostid = '2'")) {
                $arr['ticketpostid'] = 2;
            }

            if (false !== strpos($x, "ticketpostid = '1'")) {
                $arr['ticketpostid'] = 1;
            }

            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertFalse($obj->Delete(2, 3));
        $this->assertFalse($obj->Delete(2, 2));
        $this->assertTrue($obj->Delete(1, 1));

        $this->assertClassNotLoaded($obj, 'Delete', 1, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testPostReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Post(),
            'Returns false without POST');

        $_POST['ticketid'] = 1;
        $this->assertFalse($obj->Post(),
            'Returns false without contents');

        $_POST['contents'] = '<html>contents</html>';
        $this->assertFalse($obj->Post(),
            'Returns false without staffid');

        $_POST['staffid'] = 2;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid id');

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'tickettimetrackid' => 1,
            'timeworked' => 0,
            'timebilled' => 0,
            'ticketslaplanid' => 0,
            'slaplanid' => 0,
            'firstresponsetime' => 0,
            'ticketpostid' => 1,
            'averageresponsetimehits' => 0,
            'totalreplies' => 0,
            'duetime' => 0,
            'resolutionduedateline' => 0,
            'dateline' => 0,
            'languageid' => 1,
            'languageengineid' => 1,
            'tgroupid' => 1,
            'tickettypeid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'replyto' => '',
            'ticketmaskid' => 0,
            'useremailid' => 1,
            'isthirdparty' => 0,
            'creator' => 0,
            'isprivate' => 0,
            'contents' => '<html>contents</html>',
            'ishtml' => 1,

            'attachmentid' => 1,
            'filename' => 'file.txt',
            'filesize' => 1,
            'filetype' => 'file',
            'storefilename' => 'file.txt',
            'attachmenttype' => 0,

            // staff properties
            'staffid' => 0,
            'fullname' => 'fullname',

            // user properties
            'userid' => 0,

            'subject' => 'subject',
            'emailqueueid' => '0',
            'email' => 'me@mail.com',
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            if (false !== strpos($x, "userid = '1'")) {
                $arr['userid'] = 1;
            }
            if (false !== strpos($x, "staffid = '1'")) {
                $arr['staffid'] = 1;
            }
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $_POST['filename1'] = 'file.txt';
        $_POST['filecontent1'] = base64_encode('content');
        $this->assertFalse($obj->Post(),
            'Returns false with invalid staffid');

        $_POST['userid'] = 2;
        $this->assertFalse($obj->Post(),
            'Returns false with invalid userid');

        static::$databaseCallback['CacheGet'] = function ($x) {
            return [
                1 => [
                    1 => 1,
                    'regusergroupid' => '1',
                    'languageid' => '1',
                    'departmentapp' => 'tickets',
                    'languagecode' => 'en-us',
                ],
            ];
        };

        $_POST['staffid'] = 1;
        unset($_POST['userid']);
        $this->assertTrue($obj->Post());

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

        $_POST['userid'] = 1;
        $this->assertTrue($obj->Post());

        $this->assertClassNotLoaded($obj, 'Post');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_TicketPostMock
     */
    private function getMocked()
    {
        $rest = $this->getMockBuilder('SWIFT_RESTServer')
            ->disableOriginalConstructor()
            ->getMock();

        $rest->method('GetVariableContainer')->willReturn(['salt' => 'salt']);
        $rest->method('Get')->willReturnArgument(0);

        return $this->getMockObject('Tickets\Api\Controller_TicketPostMock', [
            'RESTServer' => $rest,
        ]);
    }
}

class Controller_TicketPostMock extends Controller_TicketPost
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

