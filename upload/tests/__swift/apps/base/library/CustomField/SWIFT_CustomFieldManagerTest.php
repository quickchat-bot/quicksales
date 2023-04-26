<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Busayo Arotimi <arotimi.busayo@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Base\Library\CustomField;

use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Models\CustomField\SWIFT_CustomFieldGroup;
use LoaderMock;
use SWIFT;
use SWIFT_TestCase;

/**
 * Class SWIFT_CustomFieldManagerTest
 * @package Base\Library\CustomField
 */
class SWIFT_CustomFieldManagerTest extends SWIFT_TestCase
{

    public function testCanDecrypt()
    {
        $testString = 'TestEncrypt';
        $mcryptEncrypt = $this->oldEncrypt($testString);

        $decryptedValue = SWIFT_CustomFieldManager::Decrypt($mcryptEncrypt);

        $this->assertSame($testString, $decryptedValue);
    }

    private function oldEncrypt($_stringValue)
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $value = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, SWIFT::Get('InstallationHash'), $_stringValue, MCRYPT_MODE_ECB, $iv);

        return trim(base64_encode($value));
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testUpdateReturnsTrue()
    {
        $obj = $this->getMocked();

        // $_submissionMode, $_mode, $_groupTypeList, $_checkMode, $_linkTypeID
        self::assertTrue($obj->Update(SWIFT_CustomFieldManager::MODE_POST,
            SWIFT_UserInterface::MODE_EDIT, [SWIFT_CustomFieldGroup::GROUP_STAFFUSERTICKET],
            SWIFT_CustomFieldManager::CHECKMODE_WORKFLOW, 2),
            'Returns true when updating');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_CustomFieldManagerMock
     */
    private function getMocked()
    {
        return $this->getMockObject(SWIFT_CustomFieldManagerMock::class);
    }
}

class SWIFT_CustomFieldManagerMock extends SWIFT_CustomFieldManager
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

