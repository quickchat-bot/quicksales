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

namespace Base\Console;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class Controller_AttachmentsTest
 * @group base
 * @group base-console
 */
class Controller_AttachmentsTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf('Base\Console\Controller_Attachments', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenameReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();

        static::$nextRecordType = static::NEXT_RECORD_QUERY_RESET;

        $_SWIFT->Database->Record = ['attachmentid' => 1, 'storefilename' => 'testfile'];

        $this->assertTrue($obj->Rename(),
            'Returns true');

        $f = fopen('./__swift/files/testfile', 'w');
        fputs($f, 'test');

        $this->assertTrue($obj->Rename(),
            'Returns true');

        fclose($f);

        if(file_exists('./__swift/files/testfile'))
            unlink('./__swift/files/testfile');

        $this->assertClassNotLoaded($obj, 'Rename');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_AttachmentsMock
     */
    private function getMocked()
    {
        $consoleMock = new ConsoleMock();
        return $this->getMockObject('Base\Console\Controller_AttachmentsMock', [
            'Console' => $consoleMock
        ]);
    }
}

class ConsoleMock
{
    public $prompt = false;

    function WriteLine()
    {
        return true;
    }

    function Green()
    {
        return true;
    }

    function Yellow()
    {
        return true;
    }

    function Red()
    {
        return true;
    }

    function Message()
    {
        return true;
    }

    function Prompt($x)
    {
        if (!$this->prompt)
            return true;

        if (preg_match('/.*Database\sHost.*/', $x))
            return DB_HOSTNAME;

        if (preg_match('/.*Database\sName.*/', $x))
            return DB_NAME;

        if (preg_match('/.*Database\sPort.*/', $x))
            return '';

        if (preg_match('/.*Database\sSocket.*/', $x))
            return '';

        if (preg_match('/.*Database\sUsername.*/', $x))
            return DB_USERNAME;

        if (preg_match('/.*Database\sPassword.*/', $x))
            return DB_PASSWORD;


        return true;
    }
}

class Controller_AttachmentsMock extends Controller_Attachments
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

