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

namespace News\Models\NewsItem;

use News\Admin\LoaderMock;
use SWIFT;

/**
 * Class SWIFT_NewsItemTest
 * @group news
 */
class SWIFT_NewsItemTest extends \SWIFT_TestCase
{
    public static $_record;
    public static $_cache;
    public static $_count;
    public static $_max;

    /**
     * @param int $_newsItemID
     * @param mixed $customData
     * @return SWIFT_NewsItemMock
     * @throws SWIFT_NewsItem_Exception
     */
    public function getModel($_newsItemID = 1, $customData = false)
    {
        if ($customData) {
            self::$_record = $customData;
        } else {
            self::$_record = [
                'staffid' => 1,
                'newscategoryid' => 1,
                'fullname' => 1,
                'author' => 1,
                'newsitemid' => 1,
                'newsstatus' => 2,
                'totalitems' => 1,
            ];
        }

        self::$_cache = [
            'visibilitytype' => 'public',
            '1' => '1',
        ];

        self::$_count = 0;
        self::$_max = 2;

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnCallback(function () {
            self::$_count++;

            return self::$_count < self::$_max;
        });
        $mockDb->method('QueryLimit')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT newsitems.*, newsitemdata.contents')) {
                self::$_count = 0;
                self::$_max = 3;
            }

            return true;
        });
        $mockDb->method('Insert_ID')->willReturnOnConsecutiveCalls(1, false);
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "newsitemid = '0'")) {
                return false;
            }

            return self::$_record;
        });

        $mockDb->Record = &self::$_record;

        SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            1 => &self::$_cache,
        ]);

        SWIFT::GetInstance()->Cache = $mockCache;

        SWIFT::GetInstance()->Load = new LoaderMock();

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

        $mockSettings->method('Get')->willReturnOnConsecutiveCalls('1', '0');

        $dispatcher = $this->getMockBuilder('News\Library\Subscriber\SWIFT_NewsSubscriberDispatch')
            ->disableOriginalConstructor()
            ->getMock();

        return new SWIFT_NewsItemMock($_newsItemID, $dispatcher, [
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Cache' => $mockCache,
            'Database' => SWIFT::GetInstance()->Database,
        ]);
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testDestructCallsDestructor()
    {
        $obj = $this->getModel();
        $this->assertNotNull($obj);
        $obj->__destruct();
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testProcessUpdatePoolReturnsTrue()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->ProcessUpdatePool(),
            'Returns false if class is not loaded');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testGetNewsItemIDThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\NewsItem\SWIFT_NewsItem_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetNewsItemID();
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testGetDataStoreReturnsArray()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\NewsItem\SWIFT_NewsItem_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetDataStore();
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testGetPropertyThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\NewsItem\SWIFT_NewsItem_Exception',
            SWIFT_INVALIDDATA . ': invalid');
        $obj->GetProperty('invalid');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getModel();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\NewsItem\SWIFT_NewsItem_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('prop');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testIsValidNewsTypeReturnsFalse()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::IsValidNewsType(0),
            'Returns false if type is invalid');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testIsValidNewsStatusReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertTrue($obj::IsValidNewsStatus(1),
            'Returns true if type is valid');

        $this->assertFalse($obj::IsValidNewsStatus(0),
            'Returns false if type is invalid');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testCreateThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\NewsItem\SWIFT_NewsItem_Exception',
            SWIFT_INVALIDDATA);
        $obj::Create(0, '', '', '', '', '', '');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testCreateThrowsCreateFailedException()
    {
        $obj = $this->getModel();
        SWIFT::GetInstance()->Database->Insert_ID(); // advance pointer
        $this->setExpectedException('News\Models\NewsItem\SWIFT_NewsItem_Exception',
            SWIFT_CREATEFAILED);
        $obj::Create(1, 'public', 'author', 'me@email.com', 'subject', 'description', 'content');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testUpdateThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->Update('', '', '');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testUpdateThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\NewsItem\SWIFT_NewsItem_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->Update('', '', '');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testUpdateReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertTrue($obj->Update('subject', 'description', 'contents',
            false, false, true, false, [], false, [], 'emailsubject'),
            'Returns true after updating');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testUpdateStatusThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->UpdateStatus('');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testUpdateContentsThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj->UpdateContents('', '', '');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testUpdateContentsThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\NewsItem\SWIFT_NewsItem_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->UpdateContents('', '', '');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testDeleteReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertTrue($obj->Delete(),
            'Returns true after deleting');

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\NewsItem\SWIFT_NewsItem_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->Delete();
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::DeleteList([]),
            'Returns false if empty array');

        SWIFT::GetInstance()->Database->NextRecord(); //advance pointer
        $this->assertFalse($obj::DeleteList([0]),
            'Returns false with invalid array');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     */
    public function testGetStrippedDayReturnsSame()
    {
        $obj = $this->getModel();
        $this->assertEquals('1', $obj::GetStrippedDay('1'));
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testGetNewsTypeLabelThrowsException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        $obj::GetNewsTypeLabel(0);
    }

    public function getNewsTypeLabels()
    {
        return [
            [1, 'global'],
            [2, 'public'],
            [3, 'private'],
        ];
    }

    /**
     * @dataProvider getNewsTypeLabels
     * @param $type
     * @param $expected
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testGetNewsTypeLabelReturnsValue($type, $expected)
    {
        $obj = $this->getModel();
        $this->assertEquals($expected, $obj::GetNewsTypeLabel($type));
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testGetLinkedUserGroupIDListThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetLinkedUserGroupIDList();
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testGetLinkedStaffGroupIDListThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->GetLinkedStaffGroupIDList();
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testRetrieveReturnsArray()
    {
        $obj = $this->getModel();

        $this->assertInternalType('array',
            $obj::Retrieve(0, 0, false, false, [0]));

        self::$_record = [
            'author' => '1',
        ];

        $this->assertInternalType('array',
            $obj::Retrieve(0, 0, false, false, [0]));
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testNewsItemContentsWithTableReturnsPurified()
    {
        $contentWithTable = '<p><em>This is a sample news, let\'s have it checked out. </em><em></em></p><p><em>This '
            .'is a sample news, let\'s have it checked out. </em></p><p><em>This is a sample news, let\'s have it '
            .'checked out. </em></p><p><em>This is a sample news, let\'s have it checked out. </em></p><table border='
            .'"1" style="border-collapse: collapse; width: 53.6372%; height: 36px;"><tbody><tr style="height: 18px;">'
            .'<td style="width: 50%; height: 18px;"><em>TD1</em></td><td style="width: 50%; height:';

       $purifiedOnTruncated = '<p><em>This is a sample news, let\'s have it checked out. </em></p><p><em>'
            .'This is a sample news, let\'s have it checked out. </em></p><p><em>This is a sample news, let\'s have it'
            .' checked out. </em></p><p><em>This is a sample news, let\'s have it checked out. </em></p><table border="1" '
            .'style="border-collapse:collapse;width:53.6372%;height:36px;"><tr style="height:18px;"><td style="width:50%;'
            .'height:18px;"><em>TD1</em></td><td style="width:50%;"></td></tr></table>';

       $records = [
            'subject' => 1,
            'author' => 1,
            'contents' => $contentWithTable,
            'dateline' => 1,
            'newsitemid' => 1,
            'newsstatus' => 2,
        ];

        $newsItemModel = $this->getModel(1, $records);
        $result = $newsItemModel::Retrieve(0, 0, false, false, [0])[1]['contents'];
        $this->assertSame($purifiedOnTruncated, $result);
    }

    public function testGetLeadParagraphPreserveInlineImages() {
        $contents = file_get_contents(__DIR__ . '/newsdata.txt');

        $result = SWIFT_NewsItem::GetLeadParagraph($contents, 500);
        $this->assertContains('<img src="data:image/png;base64', $result);
    }

    public function testGetLeadParagraphPreserveHtmlTags() {
        $contents = '<p><em>This is a sample news, let\'s have it checked out. </em></p><p><em>'
            .'This is a sample news, let\'s have it checked out. </em></p><p><em>This is a sample news, let\'s have it'
            .' checked out. </em></p><p><em>This is a sample news, let\'s have it checked out. </em></p><table border="1" '
            .'style="border-collapse:collapse;width:53.6372%;height:36px;"><tr style="height:18px;"><td style="width:50%;'
            .'height:18px;"><em>TD1</em></td><td style="width:50%;"></td></tr></table>';
        $result = SWIFT_NewsItem::GetLeadParagraph($contents, 20);
        $this->assertEquals('<p><em>This is a</em></p>', $result);
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testRetrieveCategoryCountReturnsArray()
    {
        $obj = $this->getModel();

        $this->assertInternalType('array',
            $obj::RetrieveCategoryCount(false, [0]),
            'Returns array with empty typelist');

        self::$_count = 0;
        self::$_max = 2;

        $this->assertInternalType('array',
            $obj::RetrieveCategoryCount([0], [0]),
            'Returns array with type public');

        self::$_cache = [
            'visibilitytype' => 'private',
            '1' => '1',
        ];

        self::$_count = 0;
        self::$_max = 2;

        $this->assertInternalType('array',
            $obj::RetrieveCategoryCount([0], [0]),
            'Returns array with type private');
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testRetrieveStoreReturnsArray()
    {
        $obj = $this->getModel();
        $this->assertInternalType('array', $obj->RetrieveStore());

        self::$_cache = [
            'visibilitytype' => 'private',
            'fullname' => '1',
            '1' => [1],
        ];
        $obj->SetProperty('staffid', null);

        $this->assertInternalType('array', $obj->RetrieveStore());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->RetrieveStore();
    }

    /**
     * @throws SWIFT_NewsItem_Exception
     * @throws \SWIFT_Exception
     */
    public function testRetrieveCountReturnsNumber()
    {
        $obj = $this->getModel();
        $this->assertEquals(1, $obj::RetrieveCount());
    }
}

class SWIFT_NewsItemMock extends SWIFT_NewsItem
{
    /**
     * SWIFT_NewsItemMock constructor.
     * @param $_newsItemID
     * @param $dispatcher
     * @param array $services
     * @throws SWIFT_NewsItem_Exception
     */
    public function __construct($_newsItemID, $dispatcher, array $services = [])
    {
        $this->Load = new LoaderMock();
        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }
        parent::__construct($_newsItemID);
        $this->NewsSubscriberDispatch = $dispatcher;
    }

    public function Initialize()
    {
        return true;
    }
}
