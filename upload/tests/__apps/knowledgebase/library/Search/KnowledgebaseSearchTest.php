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

namespace Knowledgebase\Library\Search;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class KnowledgebaseSearchTest
 * @group knowledgebase
 */
class KnowledgebaseSearchTest extends \SWIFT_TestCase
{
    public static $_next = 0;

    /**
     * @return SWIFT_KnowledgebaseSearch
     * @throws SWIFT_Exception
     */
    private function getSearch()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryLimit')->willReturn(true);

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            return in_array(self::$_next, [1, 3, 5, 8, 10], true);
        });

        $mockDb->Record = [
            'kbarticleid' => 1,
            'kbcategoryid' => 1,
            'objid' => 1,
            'articlestatus' => '1',
        ];

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('1', '0');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetProperty')->willReturn(1);

        \SWIFT::GetInstance()->Database = $mockDb;
        \SWIFT::GetInstance()->Staff = $mockStaff;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn(['7' => [1 => [1]]]);

        SWIFT::GetInstance()->Cache = $mockCache;

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            return $x;
        });
        \SWIFT::GetInstance()->Language = $mockLang;

        $obj = new SWIFT_KnowledgebaseSearch();

        $this->mockProperty($obj, 'Database', $mockDb);

        return $obj;
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchReturnsArray()
    {
        $obj = $this->getSearch();
        $this->assertEmpty($obj::Search('', 0));
        $this->assertInternalType('array', $obj::Search('q', 1));
    }
}
