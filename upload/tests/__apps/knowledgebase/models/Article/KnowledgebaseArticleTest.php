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

namespace {

    // This allow us to configure the behavior of the "global mock"
    global $mockIsUploadedFile;
    $mockIsUploadedFile = false;
}

namespace Knowledgebase\Models\Article {

    use Knowledgebase\Admin\LoaderMock;
    use SWIFT;
    use SWIFT_Data;
    use SWIFT_Exception;

    function is_uploaded_file($f)
    {
        global $mockIsUploadedFile;
        if ($mockIsUploadedFile === true) {
            return true;
        }

        return call_user_func_array('\is_uploaded_file', func_get_args());
    }

    /**
     * Class KnowledgebaseArticleTest
     * @group knowledgebase
     */
    class KnowledgebaseArticleTest extends \SWIFT_TestCase
    {
        private static $_next = 0;

        public function setUp()
        {
            parent::setUp();

            global $mockIsUploadedFile;
            $mockIsUploadedFile = true;
        }

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

            $mockDB->method('NextRecord')->willReturnCallback(function () {
                self::$_next++;

                return in_array(self::$_next, [
                    1,
                    15,
                    // retrievefulltext
                    21,
                    24,
                    // ProcessPostAttachments
                    32,
                    // RetrieveIRS
                    40,
                    42,
                    45,
                    47,
                ], true);
            });
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
         */
        public function testDestructCallsDestructor()
        {
            $obj = $this->getModel();
            $this->assertNotNull($obj);
            $obj->__destruct();
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testConstructorReturnsClassInstance()
        {
            $obj = $this->getModel();
            $this->assertInstanceOf('Knowledgebase\Models\Article\SWIFT_KnowledgebaseArticle', $obj);

            $this->setExpectedException('SWIFT_Exception',
                'Failed to load the Knowledgebase Article Object');
            $this->getModel(false);
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testProcessUpdatePoolReturnsFalse()
        {
            $obj = $this->getModel();
            $obj->SetUpdatePool([]);
            $this->assertFalse($obj->ProcessUpdatePool());

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $obj->ProcessUpdatePool();
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testGetPropertyReturnsValue()
        {
            $obj = $this->getModel();
            $this->assertEquals(1, $obj->GetProperty('kbarticleid'));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $obj->GetProperty('key');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testGetDataStoreReturnsArray()
        {
            $obj = $this->getModel();
            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $obj->GetDataStore();
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testCleanSeoSubjectReturnsValue()
        {
            $obj = $this->getModel();
            $this->assertEquals('seosubject', $obj::cleanSeoSubject('seosubject_', '', 100));
            $this->assertNotEmpty($obj::cleanSeoSubject(''));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testCheckPageUniqueSeoSubjectReturnsValue()
        {
            $obj = $this->getModel();
            $this->assertEquals(1, $obj::checkPageUniqueSeoSubject('parent'));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testUpdateReturnsTrue()
        {
            $obj = $this->getModel();
            $this->assertTrue($obj->Update(1, 'subject', 'seosubject1', 'contents'));

            $obj->SetData([
                'seosubject' => null,
            ]);
            $this->assertTrue($obj->Update(2, 'subject', 'seosubject1', 'contents'));

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $obj->Update(1, 'subject', 'seosubject', 'contents');
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDeleteReturnsTrue()
        {
            $obj = $this->getModel();
            $this->assertTrue($obj->Delete());

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('Knowledgebase\Models\Article\SWIFT_Article_Exception',
                SWIFT_CLASSNOTLOADED);
            $obj->Delete();
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testDeleteListReturnsFalse()
        {
            $obj = $this->getModel();
            $this->assertFalse($obj::DeleteList([]));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testRetrieveReturnsArray()
        {
            $obj = $this->getModel();
            $this->assertEmpty($obj::Retrieve([]));

            $mockSettings = $this->getMockBuilder('SWIFT_Settings')
                ->disableOriginalConstructor()
                ->getMock();
            $mockSettings->method('Get')->willReturn('2');
            SWIFT::GetInstance()->Settings = $mockSettings;
            SWIFT::GetInstance()->Database->Record = [
                'kbarticleid' => 1,
                'contentstext' => 'contentstext',
                'articlestatus' => 1,
            ];
            $this->assertInternalType('array', $obj::Retrieve([1]));
        }

        /**
         * @throws \SWIFT_Exception
         */
        public function testGetDefaultSortFieldReturnsValue()
        {
            $obj = $this->getModel();
            $mockSettings = $this->getMockBuilder('SWIFT_Settings')
                ->disableOriginalConstructor()
                ->getMock();
            $mockSettings->method('Get')->willReturnOnConsecutiveCalls('4', '5', '6', '7');
            \SWIFT::GetInstance()->Settings = $mockSettings;

            $this->assertEquals('editeddateline', $obj::GetDefaultSortField());
            $this->assertEquals('views', $obj::GetDefaultSortField());
            $this->assertEquals('articlerating', $obj::GetDefaultSortField());

            $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
            $obj::GetDefaultSortField();
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testRetrieveFilterReturnsArray()
        {
            $obj = $this->getModel();
            $this->assertEmpty($obj::RetrieveFilter(1, []));
            SWIFT::GetInstance()->Database->NextRecord(); // advance
            $this->assertInternalType('array', $obj::RetrieveFilter(1, [1]));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testRetrieveStoreReturnsArray()
        {
            $obj = $this->getModel();
            $this->assertInternalType('array', $obj->RetrieveStore());
            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $obj->RetrieveStore();
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testMarkAsNotHelpfulReturnsTrue()
        {
            $obj = $this->getModel();
            $this->assertTrue($obj->MarkAsNotHelpful());
            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
            $obj->MarkAsNotHelpful();
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testGetWordCountReturnsNumber()
        {
            $obj = $this->getModel();
            $this->assertEquals(1, $obj::GetWordCount(['array'], 'array'));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testReturnSanitizedTextReturnsText()
        {
            $obj = $this->getModel();
            $this->assertEquals('array', $obj::ReturnSanitizedText('array'));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testGetStopWordContainerReturnsArray()
        {
            $obj = $this->getModel();
            $this->assertInternalType('array', $obj::GetStopWordContainer());
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testShouldIndexWordReturnsBoolean()
        {
            $obj = $this->getModel();
            $this->assertTrue($obj::ShouldIndexWord('&#1;'));
            $this->assertFalse($obj::ShouldIndexWord('ord'));
            $this->assertFalse($obj::ShouldIndexWord('&amp'));
            $this->assertFalse($obj::ShouldIndexWord('123456'));
            $this->assertFalse($obj::ShouldIndexWord('-----'));
        }

        public function statusProvider()
        {
            return [
                [1, 'astatus_published'],
                [2, 'astatus_draft'],
                [3, 'astatus_pendingapproval'],
            ];
        }

        /**
         * @dataProvider statusProvider
         * @param $val
         * @param $expect
         * @throws SWIFT_Exception
         */
        public function testGetStatusLabelReturnsValue($val, $expect)
        {
            $obj = $this->getModel();
            $this->assertEquals($expect, $obj::GetStatusLabel($val));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testRetrieveFullTextReturnsArray()
        {
            $obj = $this->getModel();
            $this->assertInternalType('array', $obj::RetrieveFullText('query', 1));
            $this->assertEmpty($obj::RetrieveFullText('query', 1));
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testProcessPostAttachmentsReturnsBoolean()
        {
            $obj = $this->getModel();
            $_POST['_existingAttachmentIDList'] = [1];
            $this->assertFalse($obj->ProcessPostAttachments());

            $tmpFile = tempnam(sys_get_temp_dir(), 'swift');

            $_FILES['kbattachments'] = [
                'name' => ['', 'file.txt'],
                'size' => ['0', '1'],
                'type' => ['', 'text/plain'],
                'tmp_name' => ['', $tmpFile],
            ];
            SWIFT::GetInstance()->Database->Record = [
                'attachmentid' => 2,
                'filename' => 'file.txt',
            ];

            @file_put_contents($tmpFile, '1');
            $this->assertTrue($obj->ProcessPostAttachments());
            @unlink($tmpFile);

            $obj->SetIsClassLoaded(false);
            $this->setExpectedException('Tickets\Library\Ticket\SWIFT_Ticket_Exception', SWIFT_CLASSNOTLOADED);
            $obj->ProcessPostAttachments();
        }

        /**
         * @throws SWIFT_Exception
         */
        public function testRetrieveIrsReturnsArray()
        {
            $obj = $this->getModel();
            $this->assertEmpty($obj::RetrieveIRS('query', 1));
            SWIFT::GetInstance()->Database->Record = [
                'articlestatus' => 1,
                'kbarticleid' => 1,
            ];
            $this->assertInternalType('array', $obj::RetrieveIRS('query', 1));
        }
    }

    class ArticleMock extends SWIFT_KnowledgebaseArticle
    {
        private $_getPool;
        public static $_unique = 0;

        public function __construct(SWIFT_Data $_SWIFT_DataObject, $pool = [1])
        {
            $this->Load = new LoaderMock();
            parent::__construct($_SWIFT_DataObject);

            $this->_getPool = $pool;
        }

        public function __destruct()
        {
            // prevent exception to be thrown when destroying the object and it's not loaded
            $this->SetIsClassLoaded(true);
            parent::__destruct();
        }

        public function SetUpdatePool($_pool)
        {
            $this->_getPool = $_pool;
        }

        public function GetUpdatePool()
        {
            return $this->_getPool;
        }

        public function ProcessUpdatePool()
        {
            if (empty($this->_dataStore)) {
                return true;
            }
            return parent::ProcessUpdatePool();
        }

        public function SetData($_data)
        {
            $this->_dataStore = array_merge($this->_dataStore, $_data);
        }

        public static function checkPageUniqueSeoSubject($seosubject)
        {

            if ($seosubject === 'parent') {
                return parent::checkPageUniqueSeoSubject($seosubject);
            }

            static::$_unique++;

            if (static::$_unique === 2) {
                static::$_unique = 0;
            }

            return static::$_unique;
        }
    }
}
