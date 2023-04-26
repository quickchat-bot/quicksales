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
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Base\Admin;

use Knowledgebase\Admin\LoaderMock;
use PHPUnit_Framework_MockObject_MockObject;
use SWIFT;
use SWIFT_TestCase;

/**
 * Class Controller_LanguageTest
 * @group base
 * @group base_admin
 */
class Controller_LanguageTest extends SWIFT_TestCase
{
    public static $_next = 0;
    public static $_code = 'ar';

    public function testConstructorReturnsClassInstance(): void
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf(Controller_Language::class, $obj);
    }

    public function testInsertSubmitReturnsFalse()
    {
        $obj = $this->getMocked();

        $mockView = $this->getMockBuilder('SWIFT_View')
            ->disableOriginalConstructor()
            ->setMethods(['Render'])
            ->getMock();

        $this->mockProperty($obj, 'View', $mockView);

        $this->assertFalse($obj->InsertSubmit(),
            'Returns false with invalid language code');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->InsertSubmit(),
            'Returns false if class is not loaded');
    }

    public function testEditSubmitReturnsFalse()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->EditSubmit(0),
            'Returns false with invalid id');

        $mockView = $this->getMockBuilder('SWIFT_View')
            ->disableOriginalConstructor()
            ->setMethods(['Render'])
            ->getMock();

        $this->mockProperty($obj, 'View', $mockView);

        $codes = ['ar', 'en-us', 'en-gb'];
        $SWIFT = SWIFT::GetInstance();
        $SWIFT->Database->method('QueryFetch')->willReturn([
            'languageid' => 1,
            'languagecode' => 'en-us',
        ]);
        $SWIFT->Database->method('Query')->willReturnCallback(function ($x) use ($codes) {
            self::$_code = $codes[self::$_next];
            self::$_next++;

            return true;
        });
        $SWIFT->Database->Record = [
            'languageid' => 1,
            'languagecode' => 'ar',
        ];

        $_POST['languagecode'] = &self::$_code;
        $this->assertFalse($obj->EditSubmit(1),
            'Returns false with invalid language code');

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->EditSubmit(1),
            'Returns false if class is not loaded');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Controller_LanguageMock
     */
    private function getMocked()
    {
        return $this->getMockObject(Controller_LanguageMock::class);
    }
}

class Controller_LanguageMock extends Controller_Language
{
    public static $_runChecks = false;

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct();
    }

    protected function RunChecks($_mode) {
        if (static::$_runChecks) {
            return parent::RunChecks($_mode);
        }

        return true;
    }
}

