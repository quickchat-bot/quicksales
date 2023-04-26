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

namespace Knowledgebase\Models\Article;

use SWIFT;
use SWIFT_DataStore;
use SWIFT_Exception;

/**
 * Class KnowledgebaseArticleTest
 * @group knowledgebase
 */
class KnowledgebaseArticlePrivatesTest extends \SWIFT_TestCase
{
    /**
     * @param bool $loaded
     * @param bool|array $pool
     * @return ArticleMock
     * @throws SWIFT_Exception
     */
    public function getModel($loaded = true, $pool = [1])
    {
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            '1' => [
                'displayorder' => 0,
            ],
        ]);

        $mockDB = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDB->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $mockDB->method('AutoExecute')->willReturn(true);
        $mockDB->method('QueryLimit')->willReturn(true);
        $mockDB->method('QueryFetch')->willReturn([
            'kbarticleid' => 1,
            'linktype' => '1',
            'staffvisibilitycustom' => '1',
            'totalitems' => 1,
            'seosubject' => 'seosubject',
            'creator' => 2,
            'creatorid' => 1,
            'ratingcount' => 1,
            'ratinghits' => 1,
            'attachmentid' => 1,
            'storefilename' => '',
            'attachmenttype' => 1,
        ]);
        $mockDB->method('Insert_ID')->willReturn(1);

        \SWIFT::GetInstance()->Database = $mockDB;
        \SWIFT::GetInstance()->Cache = $mockCache;

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

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSettings->method('Get')->willReturnOnConsecutiveCalls('1', '0', '1');
        \SWIFT::GetInstance()->Settings = $mockSettings;

        $data = new \SWIFT_DataID(1);
        $data->SetIsClassLoaded($loaded);
        $obj = new ArticleMock($data, $pool);
        $this->mockProperty($obj, 'Database', $mockDB);

        return $obj;
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataThrowsException()
    {
        $obj = $this->getModel();
        $data = new SWIFT_DataStore([]);
        $data->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('LoadData');
        $method->setAccessible(true);
        $method->invoke($obj, $data);
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testLoadDataReturnsTrue()
    {
        $obj = $this->getModel();
        $data = new SWIFT_DataStore(['kbarticleid' => 1]);
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('LoadData');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($obj, $data));
        $data = new SWIFT_DataStore([]);
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $method->invoke($obj, $data);
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testParseRetrieveArticlesReturnsArray()
    {
        $obj = $this->getModel();
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('ParseRetrieveArticles');
        $method->setAccessible(true);
        $this->assertEmpty($method->invoke($obj, []));
        SWIFT::GetInstance()->Database->Record = [
            'articlestatus' => 2,
        ];
        $this->assertInternalType('array', $method->invoke($obj, [1], 3, false, false, 0, 'editeddateline'),
            'Returns array with filter = no');
        SWIFT::GetInstance()->Database->Record = [
            'kbarticleid' => 1,
            'contentstext' => 'contentstext',
            'articlestatus' => 1,
        ];
        $this->assertInternalType('array', $method->invoke($obj, [3], 2),
            'Returns array with filter = only');
    }

    /**
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function testCalculateArticleRatingReturnsTrue()
    {
        $obj = $this->getModel();
        $mock = new \ReflectionClass($obj);
        $method = $mock->getMethod('CalculateArticleRating');
        $method->setAccessible(true);

        $obj->SetData(['ratingcount' => 1, 'ratinghits' => 2]);
        $this->assertTrue($method->invoke($obj));

        $obj->SetData(['ratingcount' => 2, 'ratinghits' => 2]);
        $this->assertTrue($method->invoke($obj));

        $obj->SetData(['ratingcount' => 3, 'ratinghits' => 2]);
        $this->assertTrue($method->invoke($obj));

        $obj->SetData(['ratingcount' => 4, 'ratinghits' => 2]);
        $this->assertTrue($method->invoke($obj));

        $obj->SetData(['ratingcount' => 5, 'ratinghits' => 2]);
        $this->assertTrue($method->invoke($obj));

        $obj->SetData(['ratingcount' => 6, 'ratinghits' => 2]);
        $this->assertTrue($method->invoke($obj));

        $obj->SetData(['ratingcount' => 7, 'ratinghits' => 2]);
        $this->assertTrue($method->invoke($obj));

        $obj->SetData(['ratingcount' => 8, 'ratinghits' => 2]);
        $this->assertTrue($method->invoke($obj));

        $obj->SetData(['ratingcount' => 9, 'ratinghits' => 2]);
        $this->assertTrue($method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }
}
