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

namespace Base\Admin;

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\Staff\SWIFT_Staff;
use Base\Staff\Controller_User;
use SWIFT;
use SWIFT_TestCase;

/**
 * Class Controller_LanguageTest
 * @group base
 * @group base_admin
 */
class Controller_UserTest extends SWIFT_TestCase
{
    public function providerOrganizationPermissions()
    {
        return [
            [
                [
                    ['staff_canupdateuserorganization', '1'],
                    ['staff_caninsertuserorganization', '1'],
                    ['staff_canviewusers', '1'],
                    ['ajaxsearch', '1'],
                    ['quickinsert', '1'],
                    ['quickinsertsubmit', '1']
                ],
	            0,
                true
            ],
	        [
		        [
			        ['staff_canupdateuserorganization', '1'],
			        ['staff_caninsertuserorganization', '1'],
			        ['staff_canviewusers', '1'],
			        ['ajaxsearch', '1'],
			        ['quickinsert', '1'],
			        ['quickinsertsubmit', '1']
		        ],
		        1,
		        true
	        ],
            [
                [
                    ['staff_canupdateuserorganization', '0'],
                    ['staff_caninsertuserorganization', '1'],
                    ['staff_canviewusers', '1'],
                    ['ajaxsearch', '1'],
                    ['quickinsert', '1'],
                    ['quickinsertsubmit', '1']
                ],
	            0,
                false
            ],
	        [
		        [
			        ['staff_canupdateuserorganization', '0'],
			        ['staff_caninsertuserorganization', '1'],
			        ['staff_canviewusers', '1'],
			        ['ajaxsearch', '1'],
			        ['quickinsert', '1'],
			        ['quickinsertsubmit', '1']
		        ],
		        1,
		        false
	        ],
            [
                [
                    ['staff_canupdateuserorganization', '1'],
                    ['staff_caninsertuserorganization', '0'],
                    ['staff_canviewusers', '1'],
                    ['ajaxsearch', '1'],
                    ['quickinsert', '1'],
                    ['quickinsertsubmit', '1']
                ],
	            0,
                false
            ],
	        [
		        [
			        ['staff_canupdateuserorganization', '1'],
			        ['staff_caninsertuserorganization', '0'],
			        ['staff_canviewusers', '1'],
			        ['ajaxsearch', '1'],
			        ['quickinsert', '1'],
			        ['quickinsertsubmit', '1']
		        ],
		        1,
		        false
	        ],
        ];
    }

    /**
     * @dataProvider providerOrganizationPermissions
     * @param $permissions
     * @param $expected
     */
    public function testOrganizationPermissions($permissions, $userId, $expected)
    {
        $services = $this->getMockServices();
        $method = new \ReflectionMethod(Controller_User::class, 'RunChecks');
        $method->setAccessible(true);

        $staff = $this->createMock(SWIFT_Staff::class);

        $staff->method('GetPermission')
            ->will($this->returnValueMap([
                ['staff_canupdateuserorganization', '1'],
                ['staff_caninsertuserorganization', '1'],
                ['staff_canviewusers', '1'],
                ['ajaxsearch', '1'],
                ['quickinsert', '1'],
                ['quickinsertsubmit', '1']
            ]));

        $_SWIFT = SWIFT::GetInstance();
        $_SWIFT->Staff = $staff;

        $_POST = [
            'csrfhash' => 'csrfhash',
            'fullname' => 'Fake Name',
            'usergroupid' => 1,
            'taginput_emails' => 'fake@kayako.com',
            'taginput_organization' => 'fakeorg'
        ];

        $userInterface = $this->createMock(SWIFT_UserInterface::class);
        $userInterface->method('CheckFields')
            ->willReturn(true);

        $customFieldManager = $this->createMock(SWIFT_CustomFieldManager::class);
        $customFieldManager->method('Check')
            ->willReturn([true]);

        $controller = new Controller_User();
        $controller->CustomFieldManager = $customFieldManager;

        $attUserInterface = new \ReflectionProperty(Controller_User::class, 'UserInterface');
        $attUserInterface->setAccessible(true);
        $attUserInterface->setValue($controller, $userInterface);

        $actual = $method->invoke($controller, SWIFT_UserInterface::MODE_EDIT, $userId);
        $this->assertTrue($actual);
    }
}
