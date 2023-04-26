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
 * Class View_MacroCategoryTest
 * @group tickets
 * @group tickets-staff
 */
class View_MacroCategoryTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false);

        $obj = $this->getMocked([
            'Database' => $mockDb,
        ]);

        $cat = $this->getMockBuilder('Tickets\Models\Macro\SWIFT_MacroCategory')
            ->disableOriginalConstructor()
            ->getMock();

        $cat->method('GetProperty')->willReturnCallback(function ($x) {
            if (strtolower(substr($x, -2)) === 'id') {
                return '1';
            }

            return $x;
        });

        $_POST['_isDialog'] = 1;

        $obj->Database->Record['staffgroupid'] = 1;

        $this->assertTrue($obj->Render(1, $cat, 1));
        $this->assertTrue($obj->Render(2, $cat));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(1));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderTabsReturnsTrue() {
        $obj = $this->getMocked();

        $this->assertTrue($obj->RenderTabs());
        $_POST['_searchQuery'] = 'q';
        $_POST['_sortBy'] = 'q';
        $this->assertTrue($obj->RenderTabs());
        $this->assertClassNotLoaded($obj, 'RenderTabs');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray() {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj::GridRender([
            'categorytitle' => 1,
            'lastusage' => 1,
        ]));
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_MacroCategoryMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\View_MacroCategoryMock', $services);
    }
}

class View_MacroCategoryMock extends View_MacroCategory
{
    public $Database;

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

