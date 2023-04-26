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

namespace Tickets\Library\Bayesian;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class BayesianTest
 * @group tickets
 * @group tickets-lib3
 */
class BayesianTest extends \SWIFT_TestCase
{
    public static $_prop = [];

    /**
     * @throws SWIFT_Exception
     */
    public function testTrainReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Train(1, 1, 'q'));

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'word' => 'rain',
            'bayeswordid' => '0',
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->setNextRecordNoLimit();

        $this->assertTrue($obj->Train(1, 1, 'is november rain'));

        $this->assertClassNotLoaded($obj, 'Train', 1, 1, 'q');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUntrainReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj->Untrain(1, 1, 'q'));

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'word' => 'rain',
            'bayeswordid' => '-1',
            'wordcount' => '1',
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->setNextRecordNoLimit();

        static::$databaseCallback['Query'] = function ($x) {
            if (false !== strpos($x, 'bayeswordsfreqs WHERE bayeswordid')) {
                static::$_prop['freq'] = 1;
            }
            if (false !== strpos($x, 'bayeswords WHERE word')) {
                \SWIFT::GetInstance()->Database->Record['bayeswordid']++;
            }
        };

        $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);
        static::$databaseCallback['NextRecord'] = function () {
            if (isset(static::$_prop['freq'])) {
                static::$_prop['freq']++;
                \SWIFT::GetInstance()->Database->Record['bayeswordid'] = static::$_prop['freq'];
                \SWIFT::GetInstance()->Database->Record['wordcount'] = 4 - static::$_prop['freq'];
                if (static::$_prop['freq'] === 3) {
                    unset(static::$_prop['freq']);
                }
                return true;
            }

            return static::$nextRecordCount % 2;
        };
        static::$databaseCallback['Insert_ID'] = function () {
            $bayeswordid = \SWIFT::GetInstance()->Database->Record['bayeswordid'];
            if ($bayeswordid >= 0) {
                $bayeswordid++;
                \SWIFT::GetInstance()->Database->Record['bayeswordid'] = $bayeswordid;

                return $bayeswordid;
            }

            return 1;
        };

        $this->assertTrue($obj->Untrain(1, 1, 'is november rain day bye bye'));

        $this->assertClassNotLoaded($obj, 'Untrain', 1, 1, 'q');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetReturnsTrue()
    {
        $obj = $this->getMocked();

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'word' => 'rain',
            'bayeswordid' => '-1',
            'wordcount' => '1',
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->setNextRecordNoLimit();

        static::$databaseCallback['Query'] = function ($x) {
            if (false !== strpos($x, 'bayeswordsfreqs WHERE bayeswordid')) {
                static::$_prop['freq'] = 1;
            }
            if (false !== strpos($x, 'bayeswords WHERE word')) {
                \SWIFT::GetInstance()->Database->Record['bayeswordid']++;
            }
        };

        $this->setNextRecordType(static::NEXT_RECORD_RETURN_CALLBACK);
        static::$databaseCallback['NextRecord'] = function () {
            if (isset(static::$_prop['freq'])) {
                static::$_prop['freq']++;
                \SWIFT::GetInstance()->Database->Record['bayeswordid'] = static::$_prop['freq'];
                \SWIFT::GetInstance()->Database->Record['wordcount'] = 4 - static::$_prop['freq'];
                if (static::$_prop['freq'] === 3) {
                    unset(static::$_prop['freq']);
                }
                return true;
            }

            return static::$nextRecordCount % 2;
        };
        static::$databaseCallback['Insert_ID'] = function () {
            $bayeswordid = \SWIFT::GetInstance()->Database->Record['bayeswordid'];
            if ($bayeswordid >= 0) {
                $bayeswordid++;
                \SWIFT::GetInstance()->Database->Record['bayeswordid'] = $bayeswordid;

                return $bayeswordid;
            }

            return 1;
        };

        $this->assertNotEmpty($obj->Get('is november rain day bye bye'));

        $this->assertFalse($obj->Get('q'));

        $obj::$_bayesCacheContainer = [md5('q') => false];
        $this->assertFalse($obj->Get('q'));

        $this->assertClassNotLoaded($obj, 'Get', 'q');
    }

    /**
     * @throws \ReflectionException
     */
    public function testUpdateProbabilitiesReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'UpdateProbabilities');

        $this->setNextRecordNoLimit();
        $this->assertTrue($method->invoke($obj));

        $_SWIFT = \SWIFT::GetInstance();
        $arr = [
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
            'flagtype' => 1,
            'isresolved' => 1,
            'bayescategoryid' => 1,
            'totalwords' => 1,
        ];
        $_SWIFT->Database->method('QueryFetch')->willReturnCallback(function ($x) use ($arr) {
            return $arr;
        });
        $_SWIFT->Database->Record = $arr;

        $this->assertTrue($method->invoke($obj));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCombineWordProbabilityReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'CombineWordProbability');

        $this->assertNotEmpty($method->invoke($obj, [
            0 => 1,
            1 => 1,
            2 => 1,
            3 => 1,
        ], [
            1 => [
                1 => 1,
                2 => 0,
            ],
            2 => [
                1 => 0,
                3 => 1,
                2 => 0,
            ],
        ]));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, [1], [1 => [1 => 1]]);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetWordProbabilityReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetWordProbability');

        $this->assertNotEmpty($method->invoke($obj, 't', 1, [
            1 => [
                'wordcount' => 1,
                'bayescategoryid' => 1,
                'categoryweight' => 1,
            ],
        ], [
            1 => [
                1 => [
                    'wordcount' => 1,
                ],
            ],
        ]));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 't', 1, [1], [1]);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetCategoriesReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj->GetCategories());

        $obj->_cacheCategories = [];
        $this->assertNotEmpty($obj->GetCategories());

        $this->assertClassNotLoaded($obj, 'GetCategories');
    }

    /**
     * @throws \ReflectionException
     */
    public function testUnlinkWordToCategoryReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'UnlinkWordToCategory');

        $this->assertTrue($method->invoke($obj, 1, 1));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testLinkWordToCategoryReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'LinkWordToCategory');

        $this->assertTrue($method->invoke($obj, 1, 1));

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('AutoExecute')->willReturnCallback(function () {
            throw new SWIFT_Exception('error');
        });
        $this->mockProperty($obj, 'Database', $mockDb);
        $this->assertFalse($method->invoke($obj, 1, 1));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testUpdateWordToCategoryLinkReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'UpdateWordToCategoryLink');

        $this->assertTrue($method->invoke($obj, 1, 1, 1));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, 1, 1, 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetWordCategoryLinkReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetWordCategoryLink');

        $this->assertNotEmpty($method->invoke($obj, [1], 1));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, [1], 1);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetWordCategoryLinkGroupedReturnsTrue()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetWordCategoryLinkGrouped');

        $this->assertNotEmpty($method->invoke($obj, [1], [1]));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, [1], [1]);
    }

    public function testGetTokenCounterReturnsOne()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj::GetTokenCounter([1 => 1]));
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetWordIDListReturnsArray()
    {
        $obj = $this->getMocked();
        $method = $this->getMethod($obj, 'GetWordIDList');

        $this->assertEmpty($method->invoke($obj, []));

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('AutoExecute')->willReturnCallback(function () {
            throw new SWIFT_Exception('error');
        });
        $this->mockProperty($obj, 'Database', $mockDb);

        $this->assertEmpty($method->invoke($obj, [1]));

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $method->invoke($obj, [1]);
    }

    public function testTokenizeReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj::Tokenize('test'));
        $message = str_repeat('long_string', 255);
        $this->assertEmpty($obj::Tokenize($message));
    }

    public function testShouldIndexWordReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertTrue($obj::ShouldIndexWord('&#123;'));

        static::$databaseCallback['SettingsGet'] = function ($x) {
            if (isset(static::$_prop[$x])) {
                return static::$_prop[$x];
            }
            if ($x === 'tb_minwordlength') {
                return 2;
            }

            if ($x === 'tb_minwordlength') {
                return 2;
            }

            if ($x === 'tb_maxwordlength') {
                return 100;
            }

            if ($x === 'tb_minnumberlength') {
                return 5;
            }

            return 1;
        };
        $this->assertFalse($obj::ShouldIndexWord('w'));

        $this->assertFalse($obj::ShouldIndexWord('out'));

        $this->assertFalse($obj::ShouldIndexWord('123'));

        $this->assertTrue($obj::ShouldIndexWord('123456'));

        $this->assertFalse($obj::ShouldIndexWord('%%%%'));

        static::$_prop['tb_indexnumbers'] = 0;
        $this->assertFalse($obj::ShouldIndexWord('123'));
    }

    public function testReturnSanitizedTextReturnsString()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj::ReturnSanitizedText('w'));
    }

    public function testHasUnicodeCharsReturnsTrue()
    {
        $obj = $this->getMocked();

        $this->assertFalse($obj::HasUnicodeChars('w'));

        $this->assertTrue($obj::HasUnicodeChars(chr(128) . chr(129)));
    }

    public function testUTF8ToUnicodeReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj::UTF8ToUnicode('w'));

        $this->assertNotEmpty($obj::UTF8ToUnicode(chr(128) . chr(129)));
    }

    public function testUnicodeToEntitiesReturnsString()
    {
        $obj = $this->getMocked();

        $this->assertEquals('', $obj::UnicodeToEntities(''));
        $this->assertEquals('a', $obj::UnicodeToEntities([ord('a')]));
    }

    public function testGetStopWordContainerReturnsArray()
    {
        $obj = $this->getMocked();

        $this->assertNotEmpty($obj::GetStopWordContainer());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SWIFT_BayesianMock
     */
    private function getMocked()
    {
        return $this->getMockObject('Tickets\Library\Bayesian\SWIFT_BayesianMock');
    }
}

class SWIFT_BayesianMock extends SWIFT_Bayesian
{
    public static $_bayesCacheContainer = [];
    public $_cacheCategories = false;

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

