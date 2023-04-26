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

namespace Base\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_CustomFieldTest
 * @group base
 * @group base-api
 */
class Controller_CustomFieldTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Api\Controller_CustomField', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetListReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->GetList(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'GetList');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testListOptionsReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->ListOptions(1),
            'Returns true');

        $this->assertFalse($obj->ListOptions(''),
            'Returns false');

        $this->assertClassNotLoaded($obj, 'ListOptions', 1);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_CustomFieldMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Api\Controller_CustomFieldMock');
    }
}

class Controller_CustomFieldMock extends Controller_CustomField
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
