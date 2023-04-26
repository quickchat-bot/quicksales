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

namespace Troubleshooter\Library\UnifiedSearch;

use SWIFT_Exception;

/**
 * Class UnifiedSearch_troubleshooterTest
 * @group troubleshooter
 */
class UnifiedSearch_troubleshooterTest extends \SWIFT_TestCase
{
    /**
     * @return SWIFT_UnifiedSearch_troubleshooter
     * @throws SWIFT_Exception
     */
    private function getUnifiedSearch()
    {
        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false, true, false);

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('1', '0');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetProperty')->willReturn(1);

        \SWIFT::GetInstance()->Database = $mockDb;
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $obj = new SWIFT_UnifiedSearch_troubleshooter('select 1', \SWIFT_Interface::INTERFACE_TESTS,
            $mockStaff, 1);

        $this->mockProperty($obj, 'Language', $mockLang);
        $this->mockProperty($obj, 'Database', $mockDb);

        return $obj;
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchReturnsArray()
    {
        $obj = $this->getUnifiedSearch();

        $this->assertInternalType('array', $obj->Search());

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $obj->Search();
    }

    /**
     * @throws \ReflectionException
     * @throws SWIFT_Exception
     */
    public function testSearchCategoriesReturnsArray() {
        $obj = $this->getUnifiedSearch();

        // SearchCategories is private. make it testable
        $reflectionClass = new \ReflectionClass($obj);
        $method = $reflectionClass->getMethod('SearchCategories');
        $method->setAccessible(true);

        $this->assertInternalType('array', $method->invoke($obj));

        $this->assertCount(0, $method->invoke($obj),
            'Returns empty array with staff_trcanviewcategories = 0');

        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->SetIsClassLoaded(false);
        $method->invoke($obj);
    }
}
