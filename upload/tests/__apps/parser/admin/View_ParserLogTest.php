<?php
/**
 * ###############################################
 *
 * Kayako Classic
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

use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Knowledgebase\Admin\LoaderMock;
use Parser\Models\Log\SWIFT_ParserLog;
use SWIFT_Exception;

/**
 * Class View_ParserLogTest
 * @group parser
 * @group parser-admin
 */
class View_ParserLogTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Parser\Admin\View_ParserLog', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $obj = $this->getMocked();

        $parserLogMock = $this->getMockBuilder(SWIFT_ParserLog::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parserLogMock->method('GetParserLogID')->willReturn(1);
        $id = '1';
        $parserLogMock->method('GetProperty')->willReturnCallback(function ($x) use (&$id) {
            if ($x == 'emailqueueid')
                return $id;
        });

        static::$databaseCallback['CacheGet'] = function ($x) {
            if ($x == 'queuecache')
                return [
                    'list' => [
                        1 => []
                    ]
                ];
        };

        $this->assertTrue($obj->Render($parserLogMock),
            'Returns true');

        $id = '2';

        $this->assertTrue($obj->Render($parserLogMock),
            'Returns true');

        $this->assertClassNotLoaded($obj, 'Render', $parserLogMock);
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

        $this->assertTrue(is_array($obj->GridRender([])),
            'Returns array');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|View_ParserLogMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Parser\Admin\View_ParserLogMock');
    }
}

class View_ParserLogMock extends View_ParserLog
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

