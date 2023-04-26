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

namespace Base\Console;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_ImportTest
 * @group base
 * @group base-console
 */
class Controller_ImportTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Console\Controller_Import', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testVersion3ReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Version3(),
            'Returns false');

        $obj->Console->prompt = true;

        $_SWIFT = \SWIFT::GetInstance();

        $_SWIFT->Database->method('QueryFetch')->willReturn([
            'tgroupid' => 1,
            'languageid' => 1,
            'usergroupid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'departmentid' => 1,
            'tickettypeid' => 1,
        ]);

        $_SWIFT->Database->Record = [
            'templateid' => 1,
            'kbcategoryid' => 1,
            'ticketid' => 1,
            'iswatched' => 1,
            'ticketmaskid' => 1,
            'trasholddepartmentid' => 1,
            'ticketstatusid' => 1,
            'departmentid' => 1,
            'ownerstaffid' => 1,
            'priorityid' => 1,
            'tickettypeid' => 1,
            'ticketdraftid' => 1,
            'totalreplies' => 1,
            'lastactivity' => 0,
            'isresolved' => 0,
            'lastpostid' => 0,
            ];

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x === 'kb_parcount') {
                return '0';
            }

            return 1;
        };

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        $this->assertTrue($obj->Version3(10),
            'Returns true');

        // TODO: this test needs to be correctly handled, right now it just
        // stays in a loop until phpunit makes the test time out, which in turn
        // increases the execution time of all the tests
//        $this->assertTrue($obj->Version3(),
//            'Returns true');

        $this->assertClassNotLoaded($obj, 'Version3');
    }

    public function testVersion3LimitedReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Version3(),
            'Returns false');

        $obj->Console->prompt = true;

        $_SWIFT = \SWIFT::GetInstance();

        $_SWIFT->Database->method('QueryFetch')->willReturn([
            'tgroupid' => 1,
            'languageid' => 1,
            'usergroupid' => 1,
            'ticketstatusid' => 1,
            'priorityid' => 1,
            'departmentid' => 1,
            'tickettypeid' => 1,
        ]);

        $_SWIFT->Database->Record = [
            'templateid' => 1,
            'kbcategoryid' => 1,
            'ticketid' => 1,
            'iswatched' => 1,
            'ticketmaskid' => 1,
            'trasholddepartmentid' => 1,
            'ticketstatusid' => 1,
            'departmentid' => 1,
            'ownerstaffid' => 1,
            'priorityid' => 1,
            'tickettypeid' => 1,
            'ticketdraftid' => 1,
            'totalreplies' => 1,
            'lastactivity' => 0,
            'isresolved' => 0
        ];

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if ($x == 'kb_parcount')
                return '0';
        };

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

//        $this->assertTrue($obj->Version3(10),
//            'Returns true');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_ImportMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Console\Controller_ImportMock', [
            'Console' => new ConsoleMock()
        ]);
    }
}

class Controller_ImportMock extends Controller_Import
{
    public $Console;

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

