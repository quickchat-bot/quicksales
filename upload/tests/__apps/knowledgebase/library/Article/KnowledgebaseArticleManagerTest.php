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

namespace Knowledgebase\Library\Article;

use Knowledgebase\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class KnowledgebaseArticleManagerTest
 * @group knowledgebase
 */
class KnowledgebaseArticleManagerTest extends \SWIFT_TestCase
{
    public static $_next = 0;
    private $obj;
    public static $_record = [
        'articlestatus' => 1,
        'kbarticleid' => 1,
        'kbcategoryid' => 1,
        'linktypeid' => 1,
        'allowcomments' => 0,
        'allowrating' => 0,
        'uservisibilitycustom' => 1,
        'staffvisibilitycustom' => 1,
        'categorytype' => 1,
        'parentkbcategoryid' => 1,
    ];

    /**
     * @throws SWIFT_Exception
     */
    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_next++;

            if (self::$_next === 7 || self::$_next === 19) {
                self::$_record['linktypeid'] = '0';
            } else {
                self::$_record['linktypeid'] = '1';
            }

            if (self::$_next === 17 || self::$_next === 29) {
                self::$_record['categorytype'] = 4;
            } else {
                self::$_record['categorytype'] = 1;
            }

            return in_array(self::$_next, [1, 2, 6, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29], true);
        });

        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "kbarticleid = '2'")) {
                return false;
            }

            $arr = [
                'kbarticleid' => 1,
                'articlestatus' => 1,
                'allowcomments' => 0,
                'allowrating' => 0,
                'uservisibilitycustom' => 1,
                'staffvisibilitycustom' => 1,
                'categorytype' => 1,
                'kbcategoryid' => 1,
            ];

            if (false !== strpos($x, "kbarticleid = '3'")) {
                $arr['articlestatus'] = 2;
            }

            if (false !== strpos($x, "kbcategoryid = '2'")) {
                $arr['kbcategoryid'] = 2;
            }

            return $arr;
        });

        $mockDb->Record = &self::$_record;

        SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            '7' => [1 => [1]],
            '5' => [1 => [1]],
        ]);

        SWIFT::GetInstance()->Cache = $mockCache;

        SWIFT::GetInstance()->Load = new LoaderMock();

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetProperty')->willReturn('1');

        SWIFT::GetInstance()->Staff = $mockStaff;

        $this->obj = new SWIFT_KnowledgebaseArticleManagerMock([]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRetrieveBySeoSubjectReturnsFalse()
    {
        $this->assertFalse($this->obj->RetrieveBySeoSubject('subject'),
            'Returns false on empty results');
        $this->assertFalse($this->obj->RetrieveBySeoSubject('subject'),
            'Returns false on second run');
    }

    /**
     * @throws SWIFT_Exception
     * @depends testRetrieveBySeoSubjectReturnsFalse
     */
    public function testRetrieveForUserReturnsArray()
    {
        $this->assertFalse($this->obj->RetrieveForUser(2),
            'Returns false if article not found');
        $this->assertFalse($this->obj->RetrieveForUser(3),
            'Returns false if articlestatus != published');
        $this->assertInternalType('array', $this->obj->RetrieveForUser(4, 2),
            'Returns array');
        $this->assertInternalType('array', $this->obj->RetrieveForUser(1, 3),
            'Returns array with invalid categoryid');
        $this->assertInternalType('array', $this->obj->RetrieveForUser(1, 3),
            'Returns array with categorytype = inherit');
    }

    /**
     * @throws SWIFT_Exception
     * @depends testRetrieveForUserReturnsArray
     */
    public function testRetrieveForStaffReturnsArray()
    {
        $this->assertFalse($this->obj->RetrieveForStaff(2),
            'Returns false if article not found');
        $this->assertFalse($this->obj->RetrieveForStaff(3),
            'Returns false if articlestatus != published');
        $this->assertInternalType('array', $this->obj->RetrieveForStaff(4, 2),
            'Returns array');
        $this->assertInternalType('array', $this->obj->RetrieveForStaff(1, 3),
            'Returns array with invalid categoryid');
        $this->assertInternalType('array', $this->obj->RetrieveForStaff(1, 3),
            'Returns array with categorytype = inherit');
        $this->assertFalse($this->obj->RetrieveForStaff(1),
            'Returns false without categories');
    }
}

class SWIFT_KnowledgebaseArticleManagerMock extends SWIFT_KnowledgebaseArticleManager
{
    /**
     * SWIFT_KnowledgebaseArticleManagerMock constructor.
     * @param array $services
     * @throws SWIFT_Exception
     */
    public function __construct(array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct();
    }

    public function Initialize()
    {
        return true;
    }
}
