<?php

use PHPUnit\Framework\TestCase;

class SWIFT_LanguageEngineTest extends TestCase
{

	public function testLoadApp()
	{
		$languageCode = 'en-us';
		$expected = include SWIFT_BASEPATH . '/__apps/tickets/locale/en-us/kql_tickets.php';
		$this->assertIsArray($expected);

		$engine = $this->getMockBuilder(SWIFT_LanguageEngine::class)
			->setConstructorArgs([SWIFT_LanguageEngine::TYPE_DB, $languageCode, 0, false])
			->setMethodsExcept(['LoadApp'])
			->getMock();

		$engine->expects(self::exactly(1))
			->method('GetIsClassLoaded')
			->willReturn(true);

		$engine->method('GetLanguageCode')
			->willReturn($languageCode);

		$actual = $engine->LoadApp('kql_tickets', 'tickets', SWIFT_LanguageEngine::TYPE_FILE);
		$this->assertIsArray($actual);
		$this->assertEquals($expected, $actual);
	}

	public function testLoadQueue()
	{
		$languageCode = 'ru';
		$languageId = 0;

		$ruLang = include SWIFT_BASEPATH . '/__swift/locale/ru/ru.php';
		$this->assertIsArray($ruLang);

		$enLang = include SWIFT_BASEPATH . '/__swift/locale/en-us/en-us.php';
		$this->assertIsArray($enLang);

		$expected = array_merge($ruLang, $enLang);
		$this->assertIsArray($expected);

		$engine = $this->getMockBuilder(SWIFT_LanguageEngine::class)
			->setConstructorArgs([SWIFT_LanguageEngine::TYPE_DB, $languageCode, $languageId, false])
			->setMethodsExcept(['LoadQueue', 'Load'])
			->getMock();

		$engine->expects(self::exactly(2))
			->method('GetIsClassLoaded')
			->willReturn(true);

		$engine->method('GetLanguageCode')
			->willReturn($languageCode);

		$reflection = new ReflectionClass(SWIFT_LanguageEngine::class);
		$property = $reflection->getProperty('_sectionQueue');
		$property->setAccessible(true);
		$property->setValue($engine, ['ru', 'en-us']);

		$this->assertTrue($engine->LoadQueue());
		$this->assertEquals($expected, $engine->_phraseCache);
	}

	public function testLoad()
	{
		$languageCode = 'en-us';
		$expected = include SWIFT_BASEPATH . '/__swift/locale/en-us/en-us.php';
		$this->assertIsArray($expected);

		$engine = $this->getMockBuilder(SWIFT_LanguageEngine::class)
			->setConstructorArgs([SWIFT_LanguageEngine::TYPE_DB, $languageCode, 0, false])
			->setMethodsExcept(['Load'])
			->getMock();

		$engine->expects(self::exactly(3))
			->method('GetIsClassLoaded')
			->willReturn(true);

		$engine->method('GetLanguageCode')
			->willReturn($languageCode);

		$actual = $engine->Load('en-us');
		$this->assertIsArray($actual);
		$this->assertEquals($expected, $actual);

		$expected_staff = include SWIFT_BASEPATH . '/__swift/locale/en-us/staff.php';
		$expected_staff_preferences = include SWIFT_BASEPATH . '/__swift/locale/en-us/staff_preferences.php';
		$actual = $engine->Load('staff');
		$this->assertIsArray($actual);
		$this->assertEquals($expected_staff['msgupdatestaff'], $actual['msgupdatestaff']);
		$actual = $engine->Load('staff_preferences');
		$this->assertIsArray($actual);
		$this->assertEquals($expected_staff_preferences['msgupdatestaff'], $actual['msgupdatestaff']);
	}
}
