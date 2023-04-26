<?php

namespace Base\Models\Language;

use PHPUnit\Framework\TestCase;

final class SWIFT_LanguageTest extends TestCase
{
    private $_originalDb;

    /**
     * @beforeClass
     */
    protected function setUp(): void
    {
        $this->_originalDb = \SWIFT::GetInstance()->Database;
    }

    /**
     * @afterClass
     */
    protected function tearDown()
    {
        \SWIFT::GetInstance()->Database = $this->_originalDb;
    }

    public function testGetDefaultLanguageIdShouldReturnZeroIfFetchQueryReturnsFalse(): void {
        $this->runGetDefaultLanguageIdTest(false, 0);
    }

    private function runGetDefaultLanguageIdTest($dbQueryFetchResult, $expectedDefaultLanguageId): void {
        // Arrange
        $mockedDb = $this->createMock(\SWIFT_Database::class);
        $mockedDb->method('QueryFetch')
            ->with(SWIFT_Language::GET_DEFAULT_LANGUAGE_ID_QUERY)
            ->willReturn($dbQueryFetchResult);
        \SWIFT::GetInstance()->Database = $mockedDb;

        // Act
        $actualDefaultLanguageId = SWIFT_Language::GetDefaultLanguageId();

        // Assert
        $this->assertEquals($expectedDefaultLanguageId, $actualDefaultLanguageId);
    }

    public function testGetDefaultLanguageIdShouldReturnZeroIfFetchQueryReturnsArrayMissingLanguageIdKey(): void {
        $this->runGetDefaultLanguageIdTest([], 0);
    }

    public function testGetDefaultLanguageIdShouldReturnValueForKeyLanguageIdInArrayReturnedByFetchQuery(): void {
        $defaultLanguageId = 1;
        $this->runGetDefaultLanguageIdTest([SWIFT_Language::LANGUAGE_ID_COL_NAME => $defaultLanguageId],
            $defaultLanguageId);
    }
}
