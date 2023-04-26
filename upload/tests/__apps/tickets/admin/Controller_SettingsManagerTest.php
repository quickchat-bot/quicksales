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

namespace Tickets\Admin;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
* Class Controller_SettingsManagerTest
* @group tickets
*/
class Controller_SettingsManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_SettingsManager', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $_POST['t_ticketview'] = 100;
        /** @var Controller_SettingsManager $obj */
        $obj = $this->getController();
        $this->assertTrue($obj->Index());
        $this->assertTrue($obj->Index());
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->Index();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testBayesianReturnsTrue()
    {
        /** @var Controller_SettingsManager $obj */
        $obj = $this->getController();
        $this->assertTrue($obj->Bayesian());
        $this->assertTrue($obj->Bayesian());
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->Bayesian();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSlaReturnsTrue()
    {
        /** @var Controller_SettingsManager $obj */
        $obj = $this->getController();
        $this->assertTrue($obj->SLA());
        $this->assertTrue($obj->SLA());
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->SLA();
    }

    /**
     * @return mixed
     */
    private function getController()
    {
        $mgr = $this->getMockBuilder('SWIFT_SettingsManager')
            ->disableOriginalConstructor()
            ->getMock();
        return $this->getMockObject('Tickets\Admin\Controller_SettingsManagerMock', [
            'SettingsManager' => $mgr,
        ]);
    }
}

class Controller_SettingsManagerMock extends Controller_SettingsManager
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

