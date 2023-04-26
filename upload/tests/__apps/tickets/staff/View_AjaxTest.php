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

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_AjaxTest
 * @group tickets
 * @group tickets-staff
 */
class View_AjaxTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderPostLocksReturnsTrue()
    {
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturn([2 => [1 => [1]]]);

        $obj = $this->getMocked([
            'Cache' => $mockCache,
        ]);
        $this->assertTrue($obj->RenderPostLocks([]));

        $this->expectOutputRegex('/ticketpostlockcontainer/');
        $this->assertTrue($obj->RenderPostLocks([
            1 => [
                'staffid' => 2,
                'contents' => 'contents',
            ],
            2 => []
        ]));
        $this->assertClassNotLoaded($obj, 'RenderPostLocks', []);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_AjaxMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\View_AjaxMock', $services);
    }
}

class View_AjaxMock extends View_Ajax
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

