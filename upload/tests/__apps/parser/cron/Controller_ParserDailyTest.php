<?php
/**
 * ###############################################
 *
 * Kayako Classic
 * _______________________________________________
 *
 * @author        Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Parser\Cron;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_ParserDailyTest
 * @group parser
 * @group parser-cron
 */
class Controller_ParserDailyTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Cron\Controller_ParserDaily', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCleanupReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['cronid' => 1, 'name' => 'test']);

        $this->assertTrue($obj->Cleanup(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Cleanup');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_ParserDailyMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Cron\Controller_ParserDailyMock');
    }
}

class Controller_ParserDailyMock extends Controller_ParserDaily
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

