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

use PHPUnit_Framework_MockObject_MockObject;
use SWIFT_Exception;
use SWIFT_TestCase;

/**
 * Class AttachmentTest
 * @group base
 * @group base_library
 */
class SWIFT_AttachmentTest extends SWIFT_TestCase
{
    public function testConstructorReturnsClassInstance(): void
    {
        $obj = $this->getMocked();
        $this->assertInstanceOf(SWIFT_Attachment::class, $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveBySha1ReturnsArray(): void
    {
        $obj = $this->getMocked();
        $this->assertNotEmpty($obj::RetrieveBySha1('sha'),
            'Returns array');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveBySha1ReturnsEmptyArray(): void
    {
        $obj = $this->getMocked();
        $this->assertEmpty($obj::RetrieveBySha1(''),
            'Returns empty array');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|SWIFT_AttachmentMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Base\Models\Attachment\SWIFT_AttachmentMock');
    }

	public function attachmentsProvider()
	{
		$dbRow1 = [
				'attachmentid' => '1',
				'linktype' => '1',
				'donloaditemid' => '1',
				'downloaditemid' => '0',
				'filename', 'java.jpg',
				'filesize' => '70053',
				'filetype' => 'image/jpeg',
				'dateline' => '1587168440',
				'attachmenttype' => '2',
				'storefilename' => 'attach_dcbu7gmjkfd1agmqlk8xngmuopz3wgmy',
				'contentid' => 'ii_k93pzhgk0',
				'sha1' => '08957e75c8feca4ce73a3d56ebdf8cd7f63e3d46'
			];

		return [
			['08957e75c8feca4ce73a3d56ebdf8cd7f63e3d46', null, [false], [], 'SELECT * FROM swattachments WHERE sha1 = ?', []],
			['08957e75c8feca4ce73a3d56ebdf8cd7f63e3d46', null, [true, false], $dbRow1, 'SELECT * FROM swattachments WHERE sha1 = ?',[1 => $dbRow1]],
			['08957e75c8feca4ce73a3d56ebdf8cd7f63e3d46', 1, [false], [], 'SELECT * FROM swattachments WHERE sha1 = ? AND ticketid = ?', []],
			['08957e75c8feca4ce73a3d56ebdf8cd7f63e3d46', 1, [true, false], $dbRow1, 'SELECT * FROM swattachments WHERE sha1 = ? AND ticketid = ?',[1 => $dbRow1]],
		];
	}

	/**
	 * @param $sha1
	 * @param $ticketid
	 * @param $queryReturn
	 * @param $expected
	 * @dataProvider attachmentsProvider
	 * @throws SWIFT_Exception
	 */
    public function testRetrieveBySha1($sha1, $ticketid, $queryReturn, $record, $expectedQuery, $expected)
    {
		$mockDB = $this->createMock(\SWIFT_Database::class);
		$mockDB->method('Query')
			->will($this->returnCallback(function($query) use ($expectedQuery) {
				// Validate the generated query
				$this->assertEquals($expectedQuery, $query);
			}));

		$mockDB->method('NextRecord')
			->willReturnOnConsecutiveCalls(...$queryReturn);

		$mockDB->Record = $record;

		$_SWIFT = \SWIFT::GetInstance();
		$_SWIFT->Database = $mockDB;

		$actual = SWIFT_Attachment::RetrieveBySha1($sha1, $ticketid);
		$this->assertEquals($expected, $actual);
    }
}

class SWIFT_AttachmentMock extends SWIFT_Attachment
{
    public function __construct($services = [])
    {
        foreach ($services as $key => $service) {
            $this->$key = $service;
        }
    }
}
