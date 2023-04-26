<?php

class FunctionsTests extends SWIFT_TestCase
{
	public function strptimeProvider()
	{
		return [
			['2020-04-30 14:20:27', '%Z', false],
			['Invalid string', '%Y-%m-%d %H:%M:%S', false],
			['2020-04-30 14:20:27', '%Y-%m-%d %H:%M:%S', ['tm_sec' => 27, 'tm_min' => 20, 'tm_hour' => 14, 'tm_mday' => 30, 'tm_mon' => 3, 'tm_year' => 120]],
			['2020-04-30', '%Y-%m-%d', ['tm_sec' => 0, 'tm_min' => 0, 'tm_hour' => 0, 'tm_mday' => 30, 'tm_mon' => 3, 'tm_year' => 120]],
			['14:20', '%H:%M', ['tm_sec' => 0, 'tm_min' => 20, 'tm_hour' => 14, 'tm_mday' => 0, 'tm_mon' => 0, 'tm_year' => 0]],
			['2020', '%Y', ['tm_sec' => 0, 'tm_min' => 0, 'tm_hour' => 0, 'tm_mday' => 0, 'tm_mon' => 0, 'tm_year' => 120]],
			['04', '%m', ['tm_sec' => 0, 'tm_min' => 0, 'tm_hour' => 0, 'tm_mday' => 0, 'tm_mon' => 3, 'tm_year' => 0]],
			['30', '%d', ['tm_sec' => 0, 'tm_min' => 0, 'tm_hour' => 0, 'tm_mday' => 30, 'tm_mon' => 0, 'tm_year' => 0]],
			['14', '%H', ['tm_sec' => 0, 'tm_min' => 0, 'tm_hour' => 14, 'tm_mday' => 0, 'tm_mon' => 0, 'tm_year' => 0]],
			['20', '%M', ['tm_sec' => 0, 'tm_min' => 20, 'tm_hour' => 0, 'tm_mday' => 0, 'tm_mon' => 0, 'tm_year' => 0]],
			['27', '%S', ['tm_sec' => 27, 'tm_min' => 0, 'tm_hour' => 0, 'tm_mday' => 0, 'tm_mon' => 0, 'tm_year' => 0]],
		];
	}

	/**
	 * @param $date
	 * @param $format
	 * @param $expected
	 * @dataProvider strptimeProvider
	 */
	public function testStrptime($date, $format, $expected)
	{
		$actual = windows_strptime($date, $format);
		$this->assertEquals($expected, $actual);
	}
}
