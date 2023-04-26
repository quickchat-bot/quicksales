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

namespace Tickets\Library\Macro;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class MacroManagerTest
 * @group tickets
 * @group tickets-lib4
 */
class MacroManagerTest extends \SWIFT_TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testGetSubMacroCategoryOptionsReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetSubMacroCategoryOptions');

        $obj::$_macroCategoryCache = [
            '_macroCategoryParentMap' => [
                1 => [
                    1 => [
                        'macrocategoryid' => 2,
                        'title' => 'title',
                        'categorytype' => 1,
                        'staffid' => 1,
                    ],
                    2 => [
                        'macrocategoryid' => 3,
                        'title' => 'title',
                        'categorytype' => 1,
                        'staffid' => 1,
                    ],
                ],
                2 => [
                    [],
                ],
            ],
        ];
        $this->assertNotEmpty($method->invoke($obj, 2, 1, [], 0, 3));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetMacroCategoryTreeReturnsHtml()
    {
        $obj = $this->getMocked();

        $obj::$_macroCategoryCache = false;
        $obj::$_macroRepliesCache = [
            '_replyParentMap' => [
                [1]
            ],
        ];
        $this->assertContains('swifttree', $obj::GetMacroCategoryTree());
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetSubMacroCategoryTreeReturnsHtml()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetSubMacroCategoryTree');

        $obj::$_macroRepliesCache = [
            '_replyParentMap' => [
                2 => [
                    [],
                ],
            ],
        ];
        $obj::$_macroCategoryCache = [
            '_macroCategoryParentMap' => [
                1 => [
                    1 => [
                        'macrocategoryid' => 2,
                        'title' => 'title',
                        'categorytype' => 1,
                        'staffid' => 1,
                    ],
                    2 => [
                        'macrocategoryid' => 3,
                        'title' => 'title',
                        'categorytype' => 1,
                        'staffid' => 1,
                    ],
                ],
                2 => [
                    [],
                ],
            ],
        ];
        $this->assertContains('span', $method->invoke($obj, 2, 1, ''));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_MacroManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\Macro\SWIFT_MacroManagerMock');
    }
}

class SWIFT_MacroManagerMock extends SWIFT_MacroManager
{
    public static $_macroCategoryCache = [];
    public static $_macroRepliesCache = [];

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

