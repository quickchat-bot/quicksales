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
 * Class Controller_MacroTest
 * @group tickets
 * @group tickets-staff
 */
class Controller_MacroTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    public static $_prop = [];

    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Tickets\Staff\Controller_Macro', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->expectOutputRegex('/contents/');

        $this->assertFalse($obj->Get(0),
            'Returns false with invalid id');

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'macroreplyid' => 1,
            'macrocategoryid' => &static::$_prop['macrocategoryid'],
            'totalhits' => 0,
            'categorytype' => 0,
            'staffid' => 1,
            'ownerstaffid' => -2,
        ]);

        static::$_prop['macrocategoryid'] = 0;
        $this->assertTrue($obj->Get(1));

        static::$_prop['macrocategoryid'] = 1;
        $this->assertTrue($obj->Get(1));

        $this->assertClassNotLoaded($obj, 'Get', 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetLookupReturnsTrue()
    {
        $mockInput = $this->getMockBuilder('SWIFT_Input')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = $this->getMocked([
            'Input' => $mockInput,
        ]);

        $this->expectOutputRegex('/img/');

        $this->assertFalse($obj->GetLookup(),
            'Returns false without POST');

        $_POST['q'] = 'search';
        $this->assertTrue($obj->GetLookup());

        $this->assertClassNotLoaded($obj, 'GetLookup');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetMenuReturnsTrue()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            if (self::$_next === 6) {
                \SWIFT::GetInstance()->Database->Record['macrocategoryid'] = 3;
            }

            return in_array(self::$_next, [1, 3, 5, 6], true);
        });

        $mockDb->Record = [
            'categorytype' => 0,
            'staffid' => 1,
            'parentcategoryid' => 1,
            'macrocategoryid' => 2,
        ];

        $obj = $this->getMocked([
            'Database' => $mockDb,
        ]);

        \SWIFT::GetInstance()->Database = $mockDb;

        $this->expectOutputRegex('/href/');

        $this->assertTrue($obj->GetMenu());

        $this->assertClassNotLoaded($obj, 'GetMenu');
    }

    /**
     * @throws \ReflectionException
     */
    public function testRenderMenuReturnsTrue()
    {
        $obj = $this->getMocked();
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod('RenderMenu');
        $method->setAccessible(true);

        $this->assertEmpty($method->invoke($obj, []));

        $this->assertContains('href', $method->invoke($obj, [
            'subcategories' => [
                1 => [],
            ],
            'replies' => [
                1 => ['subject' => 'subject'],
            ],
        ]));

        $this->assertContains('ul', $method->invoke($obj, [1]));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, []);
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_MacroMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\Controller_MacroMock', $services);
    }
}

class Controller_MacroMock extends Controller_Macro
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

