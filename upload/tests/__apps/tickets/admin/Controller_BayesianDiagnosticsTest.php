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
use SWIFT;
use SWIFT_Exception;

/**
 * Class Controller_BayesianDiagnosticsTest
 * @group tickets
 */
class Controller_BayesianDiagnosticsTest extends \SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getController();
        $this->assertInstanceOf('Tickets\Admin\Controller_BayesianDiagnostics', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexReturnsTrue()
    {
        $obj = $this->getController();
        $view = $this->getMockBuilder('Tickets\Admin\View_BayesianDiagnostics')
            ->disableOriginalConstructor()
            ->getMock();
        $obj->View = $view;

        $this->assertTrue($obj->Index(),
            'Returns true with admin_tcanrunbayesdiagnostics = 1');

        $this->assertTrue($obj->Index(),
            'Returns true with admin_tcanrunbayesdiagnostics = 0');

        $this->assertClassNotLoaded($obj, 'Index');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCheckProbabilityReturnsTrue()
    {
        $view = $this->getMockBuilder('Tickets\Admin\View_BayesianDiagnostics')
            ->disableOriginalConstructor()
            ->getMock();
        $bayesian = $this->getMockBuilder('Tickets\Library\Bayesian\SWIFT_Bayesian')
            ->disableOriginalConstructor()
            ->getMock();
        $obj = $this->getController([
            'View' => $view,
            'Bayesian' => $bayesian,
        ]);

        $this->assertFalse($obj->CheckProbability(),
            'Returns false if runchecks is false');

        $_POST['csrfhash'] = 'csrfhash';
        $_POST['probabilitytext'] = 'text';

        $this->assertTrue($obj->CheckProbability());

        $this->assertClassNotLoaded($obj, 'CheckProbability');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testProcessDataReturnsTrue()
    {
        $bayesian = $this->getMockBuilder('Tickets\Library\Bayesian\SWIFT_Bayesian')
            ->disableOriginalConstructor()
            ->getMock();
        $obj = $this->getController([
            'Bayesian' => $bayesian,
        ]);

        $this->assertFalse($obj->ProcessData(),
            'Returns false if runchecks is false');

        $_POST['type'] = 2;
        $_POST['csrfhash'] = 'csrfhash';
        $_POST['bayescategoryid'] = 1;
        $_POST['bayesiantext'] = 1;

        $db = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('Insert_ID')->willReturn(1);
        $db->method('QueryFetch')->willReturn([
            'bayescategoryid' => 1,
            'category' => 1,
        ]);
        SWIFT::GetInstance()->Database = $db;
        $this->assertTrue($obj->ProcessData());
        $_POST['type'] = 1;
        $staff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $staff->method('GetPermission')->willReturn(1);
        $staff->method('GetIsClassLoaded')->willReturn(true);
        SWIFT::GetInstance()->Staff = $staff;
        $this->assertTrue($obj->ProcessData());

        $this->assertClassNotLoaded($obj, 'ProcessData');
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderConfirmationReturnsTrue()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('_RenderConfirmation');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($obj, 1, 1),
            'Returns true in insert mode');

        $this->assertTrue($method->invoke($obj, 2, 1),
            'Returns true in edit mode');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testRunChecksReturnsTrue()
    {
        $obj = $this->getController();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('RunChecks');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false without csrfhash');

        $_POST['csrfhash'] = 'csrfhash';

        $this->assertFalse($method->invoke($obj, 1),
            'Returns false in training mode and empty POST');

        $_POST['bayescategoryid'] = 1;
        $_POST['type'] = 1;
        $_POST['bayesiantext'] = 1;

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false in probability mode and empty POST');

        $_POST['probabilitytext'] = 'text';
        SWIFT::Set('isdemo', true);

        $this->assertFalse($method->invoke($obj, 2),
            'Returns false in demo mode');

        SWIFT::Set('isdemo', false);

        $this->assertTrue($method->invoke($obj, 1),
            'Returns true with admin_tcanrunbayesdiagnostics = 1');

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();
        $mockStaff->method('GetPermission')->willReturn('0');
        SWIFT::GetInstance()->Staff = $mockStaff;
        $this->assertFalse($method->invoke($obj, 1),
            'Returns false with admin_tcanrunbayesdiagnostics = 0');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_BayesianDiagnosticsMock
     */
    private function getController(array $services = [])
    {
        return $this->getMockObject('Tickets\Admin\Controller_BayesianDiagnosticsMock', $services);
    }
}

class Controller_BayesianDiagnosticsMock extends Controller_BayesianDiagnostics
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

