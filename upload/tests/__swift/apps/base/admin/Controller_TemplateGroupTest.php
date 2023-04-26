<?php
/**
 * ###############################################
 *
 * Kayako Classic
 * _______________________________________________
 *
 * @author        Iunir Iakupov <iunir.iakupov@aurea.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Base\Admin;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Admin\Controller_TemplateGroup;
use SWIFT;
use SWIFT_TestCase;
use Knowledgebase\Admin\LoaderMock;

/**
 * Class Controller_TemplateGroupTest
 */
class Controller_TemplateGroupTest extends SWIFT_TestCase
{
    public function testRunChecksWithPasswordProtectionEnabled()
    {
        $givenTemplateGroupIdDefaultValue = 1;
        $isPasswordWasEnabled = false; //when adding permission for password protection
        $services = $this->getMockServices();
        $method = new \ReflectionMethod(Controller_TemplateGroup::class, 'RunChecks');
        $method->setAccessible(true);
        $_SWIFT = SWIFT::GetInstance();

        $_POST = [
            'csrfhash' => 'csrfhash',
            'title' => 'Default',
            'companyname' =>  'Jenkins',
            'languageid' => 1,
            'groupusername' => 'Username',
            'enablepassword' => 1,
            'password' => ''
        ];

        $userInterface = $this->createMock(SWIFT_UserInterface::class);
        $userInterface->method('CheckFields')
            ->willReturn(true);
        $userInterface->method('Error')
            ->willReturn(true);

        $controller = $this->getController();
        //when inserting record with enabling password without actually providing the password it should validate and return false
        $actual = $method->invoke($controller, SWIFT_UserInterface::MODE_INSERT, $givenTemplateGroupIdDefaultValue, $isPasswordWasEnabled);
        $this->assertFalse($actual);
    }

    /**
     * @return Controller_TemplateGroup
     */
    private function getController()
    {
        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceClient')
            ->disableOriginalConstructor()
            ->setMethods(['DisplayError', 'Header', 'Footer', 'Error', 'CheckFields', 'AddNavigationBox'])
            ->getMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_TemplateGroupMock([
            'UserInterface' => $mockInt,
            'Language' => $mockLang
        ]);

        return $obj;
    }
}
class Controller_TemplateGroupMock extends Controller_TemplateGroup
{
    public function __construct($services)
    {
        $this->Load = new LoaderMock();

        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }

        parent::__construct();
    }

    public function Initialize()
    {
        return true;
    }
}