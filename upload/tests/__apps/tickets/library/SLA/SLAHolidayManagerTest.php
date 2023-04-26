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

namespace {

    // This allow us to configure the behavior of the "global mock"
    global $mockIsUploadedFile;
    $mockIsUploadedFile = false;
}

namespace Tickets\Library\SLA {

    use Knowledgebase\Admin\LoaderMock;
    use SWIFT_Exception;

    function file_get_contents($f)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            return $f;
        }

        return call_user_func_array('\file_get_contents', func_get_args());
    }

    function file_exists($f)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            return substr($f, -3) === 'xml';
        }

        return call_user_func_array('\file_exists', func_get_args());
    }

    /**
     * Class SLAHolidayManagerTest
     * @group tickets
     * @group tickets-lib4
     */
    class SLAHolidayManagerTest extends \SWIFT_TestCase
    {
        public function setUp()
        {
            parent::setUp();

            global $mockIsUploadedFile;
            $mockIsUploadedFile = true;
        }

        public function testConstructorReturnsClassInstance()
        {
            $obj = $this->getMocked();
            $this->assertInstanceOf(SWIFT_SLAHolidayManager::class, $obj);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testExportReturnsTrue()
        {
            $obj = $this->getMocked();

            $_SERVER['HTTP_USER_AGENT'] = ' MSIE';
            $this->assertTrue($obj->Export('title', 'author', ''));

            $_SERVER['HTTP_USER_AGENT'] = 'other';
            $this->assertTrue($obj->Export('title', 'author', ''));

            $this->assertClassNotLoaded($obj, 'Export', '', '', '');
        }

        public function testImportThrowsException()
        {
            $obj = $this->getMocked();

            $this->assertInvalidData($obj, 'Import', 'f.txt');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testImportReturnsTrue()
        {
            $obj = $this->getMocked();

            $this->assertFalse($obj->Import('file.xml'));

            $obj->XML->method('XMLToTree')->willReturnCallback(function ($x) {
                if ($x === 'file1.xml') {
                    return [
                        'swiftholidays' => [['children' => []]],
                    ];
                }

                if ($x === 'file2.xml') {
                    return [
                        'swiftholidays' => [
                            [
                                'children' => [
                                    'title' => [['values' => [1]]],
                                    'author' => [['values' => [1]]],
                                    'version' => [['values' => [1]]],
                                ],
                            ],
                        ],
                    ];
                }

                if ($x === 'file3.xml') {
                    return [
                        'swiftholidays' => [
                            [
                                'children' => [
                                    'title' => [['values' => [1]]],
                                    'author' => [['values' => [1]]],
                                    'version' => [['values' => [1]]],
                                    'holiday' => [[]],
                                ],
                            ],
                        ],
                    ];
                }

                return [];
            });
            $this->assertFalse($obj->Import('file1.xml'));
            $this->assertFalse($obj->Import('file2.xml'));
            $this->assertEmpty($obj->Import('file3.xml'));

            $this->assertClassNotLoaded($obj, 'Import', 'f.txt');
        }

        /**
         * @param array $services
         * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_SLAHolidayManagerMock
         */
        private function getMocked(array $services = [])
        {
            $xml = $this->getMockBuilder('SWIFT_XML')
                ->disableOriginalConstructor()
                ->getMock();
            return $this->getMockObject(SWIFT_SLAHolidayManagerMock::class, array_merge([
                'XML' => $xml,
            ], $services));
        }
    }

    class SWIFT_SLAHolidayManagerMock extends SWIFT_SLAHolidayManager
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
}
