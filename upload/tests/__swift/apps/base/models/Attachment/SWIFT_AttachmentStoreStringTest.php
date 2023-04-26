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

namespace Base\Models\Attachment;

use Base\Library\Attachment\SWIFT_Attachment_Exception;
use PHPUnit_Framework_MockObject_MockObject;
use SWIFT_TestCase;

/**
 * Class AttachmentTest
 * @group base
 * @group base_library
 */
class SWIFT_AttachmentStoreStringTest extends SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance(): void
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf(SWIFT_AttachmentStoreString::class, $obj);
    }

    public function testGetSHA1FromDatabaseReturnsString(): void
    {
        $obj = $this->getMocked();
        $this->assertNotEmpty($obj->GetSHA1(SWIFT_Attachment::TYPE_DATABASE));
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|SWIFT_AttachmentStoreStringMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Models\Attachment\SWIFT_AttachmentStoreStringMock');
    }
}

class SWIFT_AttachmentStoreStringMock extends SWIFT_AttachmentStoreString
{
    public function __construct($services = [])
    {
        foreach ($services as $key => $service) {
            $this->$key = $service;
        }
        $this->SetIsClassLoaded(true);
    }

    public function Initialize(): bool
    {
        return true;
    }

    public function GetChunk()
    {
        return '';
    }
}
