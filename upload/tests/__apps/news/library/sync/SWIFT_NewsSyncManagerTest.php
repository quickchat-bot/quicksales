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

namespace News\Library\Sync;

use News\Admin\LoaderMock;
use SWIFT;

/**
 * Class SWIFT_NewsSyncManagerTest
 * @group news
 */
class SWIFT_NewsSyncManagerTest extends \SWIFT_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('QueryFetch')->willReturn([
            'newsitemid' => 1,
            'newstype' => 1,
            'contents' => 'contents',
        ]);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false);
        $this->mockProperty($mockDb, 'Record', [
           'syncguidhash' => '1e0ca5b1252f1f6b1e0ac91be7e7219e',
        ]);

        SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([]);

        SWIFT::GetInstance()->Cache = $mockCache;

        SWIFT::GetInstance()->Load = new LoaderMock();
    }

    public function getLibrary()
    {
        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockXml = $this->getMockBuilder('SWIFT_XML')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturnOnConsecutiveCalls('1', '0');

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        return new SWIFT_NewsSyncManagerMock([
            'Database' => SWIFT::GetInstance()->Database,
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Template' => $mockTpl,
            'XML' => $mockXml,
        ]);
    }

    /**
     * @throws \SWIFT_Exception
     */
    public function testSyncReturnsTrue()
    {
        $obj = $this->getLibrary();
        $nullDevice = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'NUL' : '/dev/null';
        $this->assertFalse($obj->Sync($nullDevice, 1),
            'Returns false without feed content');

        $this->assertFalse($obj->Sync(__DIR__.'/data/empty.xml', 1),
            'Returns false without channel');

        $this->assertFalse($obj->Sync(__DIR__.'/data/no_items.xml', 1),
            'Returns false without items');

        $this->assertTrue($obj->Sync(__DIR__.'/data/single_item.xml', 3),
            'Returns true with valid news items');

        $this->assertTrue($obj->Sync(__DIR__.'/data/with_encoded.xml', 3),
            'Returns true with valid news items');

        $this->assertTrue($obj->Sync(__DIR__.'/data/with_dc.xml', 3),
            'Returns true with valid news items');

        $this->assertTrue($obj->Sync(__DIR__.'/data/no_description.xml', 3),
            'Returns true with valid news items');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->Sync(__FILE__, 1);
    }
}

class SWIFT_NewsSyncManagerMock extends SWIFT_NewsSyncManager
{
    /**
     * SWIFT_NewsSyncManagerMock constructor.
     * @param array $services
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

    protected function ParseXMLFeed($feed_XML) {
        if ((int)$feed_XML->channel->item === 1) {
            return false;
        }

        return parent::ParseXMLFeed($feed_XML);
    }
}
