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
class KnowledgebaseArticleExceptionsTest extends \SWIFT_TestCase
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
     */
    public function testGetKnowledgebaseArticleIdThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetKnowledgebaseArticleID();
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
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->GetProperty('key');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsException()
    {
        $obj = $this->getModel();

        $this->setExpectedException('Knowledgebase\Models\Article\SWIFT_Article_Exception', SWIFT_INVALIDDATA);
        $obj::Create(1, 1, 1, '', '', '', '', '');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIncrementViewsThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->IncrementViews();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testUpdateStatusThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->UpdateStatus(1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testMarkAsHelpfulThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->MarkAsHelpful();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testGetStatusLabelThrowsException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::GetStatusLabel(4);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsCreateFailedException()
    {
        $obj = $this->getModel();
        $db = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('Insert_ID')->willReturn(0); //advance id

        SWIFT::GetInstance()->Database = $db;

        $this->setExpectedException('Knowledgebase\Models\Article\SWIFT_Article_Exception',
            SWIFT_CREATEFAILED);
        $obj::Create(1, 1, 1, 'me@email.com', 1, 'subject', '', 'contents');
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testCreateThrowsAnotherCreateFailedException()
    {
        $obj = $this->getModel();
        $db = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();
        $db->method('Insert_ID')->willReturnOnConsecutiveCalls(1, 0); //advance id

        SWIFT::GetInstance()->Database = $db;

        $this->setExpectedException('Knowledgebase\Models\Article\SWIFT_Article_Exception',
            SWIFT_CREATEFAILED);
        $obj::Create(1, 1, 1, 'me@email.com', 1, 'subject', '', 'contents');
    }
}
