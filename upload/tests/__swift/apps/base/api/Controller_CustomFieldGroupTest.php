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

namespace Base\Api;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_CustomFieldGroupTest
 * @group base
 * @group base-api
 */
class Controller_CustomFieldGroupTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf(Controller_CustomFieldGroup::class, $obj);
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
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj->Get(1),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Get', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessCustomFieldGroupsClassNotLoaded()
    {
        $obj = $this->getMocked();

        $method = $this->getMethod('Base\Api\Controller_CustomFieldGroupMock', 'ProcessCustomFieldGroups');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);

        $obj->SetIsClassLoaded(false);

        $method->invoke($obj);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_CustomFieldGroupMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Api\Controller_CustomFieldGroupMock');
    }
}

class Controller_CustomFieldGroupMock extends Controller_CustomFieldGroup
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

