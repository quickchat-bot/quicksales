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

namespace Base\Console;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_LegacyInstanceTest
 * @group base
 * @group base-console
 */
class Controller_LegacyInstanceTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Console\Controller_LegacyInstance', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testChangeProductURLReturnsTrue()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn(['{"settings":{"general_producturl":"http://test.com"}}']);

        $this->assertTrue($obj->ChangeProductURL('https://example.com'),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'ChangeProductURL', 'test');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_LegacyInstanceMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Console\Controller_LegacyInstanceMock');
    }
}

class Controller_LegacyInstanceMock extends Controller_LegacyInstance
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

