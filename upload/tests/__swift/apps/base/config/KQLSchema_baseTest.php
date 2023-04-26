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

namespace Base;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class KQLSchema_baseTest
 * @group base
 */
class KQLSchema_baseTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\SWIFT_KQLSchema_base', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetSchemaReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertArraySubset(['departments' => [], 'taglinks' => []], $obj->GetSchema(),
            'Returns array with keys');

        $this->assertClassNotLoaded($obj, 'GetSchema');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetClausesReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertArraySubset(['SELECT' => [], 'LIMIT' => []], $obj->GetClauses(),
            'Returns array with keys');

        $this->assertClassNotLoaded($obj, 'GetClauses');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetOperatorsReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertArraySubset(['+' => [], '=' => []], $obj->GetOperators(),
            'Returns array with keys');

        $this->assertClassNotLoaded($obj, 'GetOperators');
    }


    /**
     * @throws SWIFT_Exception
     */
    public function testGetFunctionsReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertArraySubset(['COUNT' => [], 'SUM' => []], $obj->GetFunctions(),
            'Returns array with keys');

        $this->assertClassNotLoaded($obj, 'GetFunctions');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetSelectorsReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertArraySubset(['MINUTE' => [], 'YEAR' => []], $obj->GetSelectors(),
            'Returns array with keys');

        $this->assertClassNotLoaded($obj, 'GetSelectors');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPreModifiersReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertArraySubset(['DISTINCT' => [], 'INTERVAL' => []], $obj->GetPreModifiers(),
            'Returns array with keys');

        $this->assertClassNotLoaded($obj, 'GetPreModifiers');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetPostModifiersReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertArraySubset(['AS' => [], 'DESC' => []], $obj->GetPostModifiers(),
            'Returns array with keys');

        $this->assertClassNotLoaded($obj, 'GetPostModifiers');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetIdentifiersReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertArraySubset(['NULL' => [], 'FALSE' => []], $obj->GetIdentifiers(),
            'Returns array with keys');

        $this->assertClassNotLoaded($obj, 'GetIdentifiers');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetVariablesReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertArraySubset(['_STAFF' => [], '_NOW' => []], $obj->GetVariables(),
            'Returns array with keys');

        $this->assertClassNotLoaded($obj, 'GetVariables');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_KQLSchema_baseMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\SWIFT_KQLSchema_baseMock');
    }
}

class SWIFT_KQLSchema_baseMock extends SWIFT_KQLSchema_base
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

