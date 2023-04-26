<?php
namespace Base\Models\Staff;

class SWIFT_StaffTest extends \SWIFT_TestCase
{
	public function providerUpdatePreferences()
	{
		return [
			[
				['FirstName', 'LastName', 'test@opencart.com.vn', '', '', 'Greeting', 'UTC', true],
				['FirstName', 'LastName', 'FirstName LastName', 'test@opencart.com.vn', '', 'Greeting', 'UTC', true],
			],
			[
				['<a href="http://www.opencart.com.vn">FirstName</a>', '<a href="http://www.opencart.com.vn">LastName</a>', 'test@opencart.com.vn', '', '', '<a href="http://www.opencart.com.vn">Greeting</a>', 'UTC', true],
				['FirstName', 'LastName', 'FirstName LastName', 'test@opencart.com.vn', '', 'Greeting', 'UTC', true],
			],
			[
				['<a href="http://www.opencart.com.vn">FirstName', '<a href="http://www.opencart.com.vn">LastName', 'test@opencart.com.vn', '', '', '<a href="http://www.opencart.com.vn">Greeting', 'UTC', true],
				['FirstName', 'LastName', 'FirstName LastName', 'test@opencart.com.vn', '', 'Greeting', 'UTC', true],
			],
		];
	}

	/**
	 * @dataProvider providerUpdatePreferences
	 * @param $input
	 * @param $expected
	 */
	public function testUpdatePreferences($input, $expected)
	{
		$obj = $this->getMockBuilder(SWIFT_StaffProxy::class)
			->disableOriginalConstructor()
			->setMethods(['UpdatePool', 'GetIsClassLoaded'])
			->getMock();

		$dbMock = $this->getMockBuilder(\SWIFT_Database::class)
			->disableOriginalConstructor()
			->getMock();

		$dbMock->expects($this->once())
			->method('AutoExecute')
			->willReturn(true)
			->with(
				$this->equalTo('swsignatures'),
				$this->equalTo(['signature' => '']),
				$this->equalTo('UPDATE')
			);
		$obj->method('GetIsClassLoaded')->willReturn(true);
		$obj->setDatabase($dbMock);

		$obj->expects($this->exactly(8))
			->method('UpdatePool')
			->withConsecutive(
				[$this->equalTo('firstname'), $this->equalTo($expected[0])],
				[$this->equalTo('lastname'), $this->equalTo($expected[1])],
				[$this->equalTo('fullname'), $this->equalTo($expected[2])],
				[$this->equalTo('email'), $this->equalTo($expected[3])],
				[$this->equalTo('mobilenumber'), $this->equalTo($expected[4])],
				[$this->equalTo('greeting'), $this->equalTo($expected[5])],
				[$this->equalTo('timezonephp'), $this->equalTo($expected[6])],
				[$this->equalTo('enabledst'), $this->equalTo($expected[7])],
			);

		$obj->UpdatePreferences($input[0], $input[1], $input[2], $input[3], $input[4], $input[5], $input[6], $input[7]);
	}
}

class SWIFT_StaffProxy extends SWIFT_Staff
{
	/**
	 * For testing we should be able to set the Database private object
	 * @param $db
	 */
	public function setDatabase($db)
	{
		$this->Database = $db;
	}
}
