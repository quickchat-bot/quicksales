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

namespace Parser\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Knowledgebase\Admin\LoaderMock;
use Parser\Models\CatchAll\SWIFT_CatchAllRule;
use SWIFT_Exception;

/**
 * Class View_CatchAllTest
 * @group parser
 * @group parser-admin
 */
class View_CatchAllTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\View_CatchAll', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getMocked();

        $catchAllMock = $this->getMockBuilder(SWIFT_CatchAllRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $catchAllMock->method('GetCatchAllRuleID')->willReturn(1);

        $_POST['_isDialog'] = 1;

        $this->assertTrue($obj->Render(SWIFT_UserInterface::MODE_EDIT, $catchAllMock),
            'Returns true');

        $this->assertTrue($obj->Render(SWIFT_UserInterface::MODE_INSERT),
            'Returns true');

        $obj->SetIsClassLoaded(false);

        $this->assertFalse($obj->Render(SWIFT_UserInterface::MODE_INSERT),
            'Returns false');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderGridReturnsTrue()
    {
        $obj = $this->getMocked();

        $obj->UserInterfaceGrid->method('GetMode')->willReturn(SWIFT_UserInterfaceGrid::MODE_SEARCH);

        $this->assertTrue($obj->RenderGrid(),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'RenderGrid');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGridRenderReturnsArray()
    {
        $obj = $this->getMocked();

        $fieldContainer = [
            'emailqueueid' => 1
        ];

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'queuecache')
                return [
                    'list' => [
                        1 => []
                    ]
                ];
        };

        $this->assertTrue(is_array($obj->GridRender($fieldContainer)),
            'Returns array');

        $fieldContainer = [
            'emailqueueid' => 2
        ];

        $this->assertTrue(is_array($obj->GridRender($fieldContainer)),
            'Returns array');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|View_CatchAllMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Admin\View_CatchAllMock');
    }
}

class View_CatchAllMock extends View_CatchAll
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

