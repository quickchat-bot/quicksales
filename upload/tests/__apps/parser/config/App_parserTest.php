<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
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

namespace Parser;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class App_parserTest
 * @group parser
 * @group parser-config
 */
class App_parserTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\SWIFT_App_parser', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testInitializeReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Initialize(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Initialize');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_App_parserMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\SWIFT_App_parserMock');
    }
}

class SWIFT_App_parserMock extends SWIFT_App_parser
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct(APP_PARSER);
    }
}

