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

namespace Base\Library\HTML;

use HTMLPurifier_Config;
use Knowledgebase\Admin\LoaderMock;
use ReflectionException;
use SWIFT;
use SWIFT_Exception;
use SWIFT_TestCase;

/**
 * Class HTMLPurifierTest
 * @group library
 */
class HTMLPurifierTest extends SWIFT_TestCase
{
    public static $_next = 0;

    /**
     * @return SWIFT_HTMLPurifierMock
     * @throws SWIFT_Exception
     */
    public function getMocked(): SWIFT_HTMLPurifierMock
    {
        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn('1');

        SWIFT::GetInstance()->Settings = $mockSettings;

        return new SWIFT_HTMLPurifierMock([
            'Settings' => $mockSettings,
        ]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance(): void
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf(SWIFT_HTMLPurifier::class, $obj);
    }

    /**
     * @throws ReflectionException
     * @throws SWIFT_Exception
     */
    public function testPurifierReturnsValidConfig(): void
    {
        $obj    = $this->getMocked();
        $method = $this->getMethod(SWIFT_HTMLPurifier::class, 'getHtmlPurifierConfig');
        /** @var HTMLPurifier_Config $config */
        $config = $method->invoke($obj);
        $this->assertNotNull($config, 'Returns config');
        $this->assertTrue($config->get('CSS.AllowTricky'), 'Configuration setting is available');
        $html     = '<div style="display:inline-block;">My Div</div>';
        $filtered = $obj->Purify($html);
        $this->assertEquals($html, $filtered, 'Returns true for correct filtering');
    }
}

class SWIFT_HTMLPurifierMock extends SWIFT_HTMLPurifier
{
    /**
     * SWIFT_HTMLPurifierMock constructor.
     * @param array $services
     * @throws SWIFT_Exception
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct();
    }

    public function Initialize(): bool
    {
        return true;
    }
}
