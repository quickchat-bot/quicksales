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
use SWIFT_Exception;
use SWIFT_TestCase;

/**
 * Class AttachmentTest
 * @group base
 * @group base_library
 */
class SWIFT_AttachmentStoreFileTest extends SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance(): void
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf(SWIFT_AttachmentStoreFile::class, $obj);
    }

    /**
     * @throws SWIFT_Attachment_Exception
     */
    public function testGetSHA1ThrowsException(): void
    {
        $obj = $this->getMocked();
        $obj->_isLoaded = false;
        $this->expectException(SWIFT_Attachment_Exception::class);
        $obj->GetSHA1(1);
    }

    /**
     * @throws SWIFT_Attachment_Exception
     */
    public function testGetSHA1FromFileReturnsString(): void
    {
        $obj = $this->getMocked();
        $this->assertEquals('', $obj->GetSHA1(SWIFT_Attachment::TYPE_FILE));
    }

    /**
     * @throws SWIFT_Attachment_Exception
     */
    public function testGetSHA1FromDatabaseReturnsString(): void
    {
        $obj = $this->getMocked();
        $this->assertNotEquals('', $obj->GetSHA1(SWIFT_Attachment::TYPE_DATABASE));
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|SWIFT_AttachmentStoreFileMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Models\Attachment\SWIFT_AttachmentStoreFileMock');
    }
}

class SWIFT_AttachmentStoreFileMock extends SWIFT_AttachmentStoreFile
{
    public $_isLoaded = true;

    public function __construct($services = [])
    {
        foreach ($services as $key => $service) {
            $this->$key = $service;
        }
    }

    public function __destruct()
    {
        // ignore
    }

    public function GetIsClassLoaded(): bool
    {
        return $this->_isLoaded;
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
