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

namespace Base\Models\Attachment;

use PHPUnit_Framework_MockObject_MockObject;
use ReflectionException;
use SWIFT_TestCase;

/**
 * Class AttachmentTest
 * @group base
 * @group base_library
 */
class SWIFT_AttachmentStoreTest extends SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance(): void
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf(SWIFT_AttachmentStore::class, $obj);
    }

    /**
     * @throws ReflectionException
     */
    public function testWriteChunksReturnsString(): void
    {
        $obj    = $this->getMocked();
        $method = $this->getMethod($obj, 'WriteChunks');

        $this->assertContains('swift_', $method->invoke($obj));
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|SWIFT_AttachmentStoreMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Models\Attachment\SWIFT_AttachmentStoreMock');
    }
}

class SWIFT_AttachmentStoreMock extends SWIFT_AttachmentStore
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

    public function GetChunk(): string
    {
        return '';
    }

    public function Reset(): void
    {
        // do nothing
    }

    public function GetSHA1($_attachmentType): void
    {
        // TODO: Implement GetSHA1() method.
    }
}
