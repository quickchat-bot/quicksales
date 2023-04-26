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
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Parser;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class KQLSchema_parserTest
 * @group parser
 * @group parser-config
 */
class KQLSchema_parserTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\SWIFT_KQLSchema_parser', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetSchemaReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertTrue(is_array($obj->GetSchema()),
            'Returns array');

        $this->assertClassNotLoaded($obj, 'GetSchema');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_KQLSchema_parserMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\SWIFT_KQLSchema_parserMock');
    }
}

class SWIFT_KQLSchema_parserMock extends SWIFT_KQLSchema_parser
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

