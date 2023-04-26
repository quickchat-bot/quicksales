<?php
/**
 * ###############################################
 *
 * Archiver App for Kayako
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       archiver
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       https://github.com/trilogy-group/kayako-classic-archiver/blob/master/LICENSE
 * @link          https://github.com/trilogy-group/kayako-classic-archiver
 *
 * ###############################################
 */

namespace Archiver\Admin;

use PDO;
use SWIFT;
use SWIFT_Database;
use SWIFT_Exception;

/**
 * Class Controller_ManagerTest
 *
 * @group archiver
 * @group tickets-lib1
 */
class Controller_ManagerTest extends \SWIFT_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('GetPDOObject')->willReturn(new PDOMock(new PDOStatementMock(0, 0, [], [])));

        $mockDb->method('NextRecord')
            ->willReturnOnConsecutiveCalls(true, false);

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturn('eu');

        SWIFT::GetInstance()->Settings = $mockSettings;
        SWIFT::GetInstance()->Database = $mockDb;
    }

    public function testConstructorReturnsControllerInstance()
    {
        $obj = new Controller_Manager();
        $this->assertInstanceOf('\Archiver\Admin\Controller_Manager', $obj);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testIndexShowsSearchForm()
    {
        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockView = $this->getMockBuilder('\Archiver\Admin\View_Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_Manager();
        $this->mockProperty($obj, 'UserInterface', $mockInt);
        $this->mockProperty($obj, 'View', $mockView);

        $this->assertTrue($obj->Index());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $obj->Index();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testTrashShowsSearchForm()
    {
        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockView = $this->getMockBuilder('\Archiver\Admin\View_Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_Manager();
        $this->mockProperty($obj, 'UserInterface', $mockInt);
        $this->mockProperty($obj, 'View', $mockView);

        $this->assertTrue($obj->Trash());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $obj->Trash();

    }

    /**
     * @throws SWIFT_Exception
     */
    public function testSearchShowsResults()
    {
        $_REQUEST = [
            'ar_start_date' => '01/01/1970',
            'ar_end_date' => '31/12/2100',
            'ar_page_size' => 20,
            'ar_is_trash' => 0,
            'ar_email' => 'test@email.com',
        ];

        $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockView = $this->getMockBuilder('\Archiver\Admin\View_Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_Manager();
        $this->mockProperty($obj, 'UserInterface', $mockInt);
        $this->mockProperty($obj, 'View', $mockView);

        $this->assertTrue($obj->Search());

        $_REQUEST['ar_start_date'] = 'blah';
        $this->assertFalse($obj->Search());

        $_REQUEST['ar_start_date'] = '01/01/2100';
        $this->assertFalse($obj->Search());

        unset($_REQUEST['ar_start_date']);
        $this->assertFalse($obj->Search());

        $_REQUEST['ar_start_date'] = '01/01/1970';
        $_REQUEST['ar_end_date'] = 'blah';
        $this->assertFalse($obj->Search());

        $_REQUEST['ar_start_date'] = '01/01/2010';
        $_REQUEST['ar_end_date'] = '01/01/2000';
        $this->assertFalse($obj->Search());

        unset($_REQUEST['ar_end_date']);
        $this->assertFalse($obj->Search());

        $_REQUEST['ar_start_date'] = '01/01/1970';
        $_REQUEST['ar_end_date'] = '01/01/2100';
        $_REQUEST['ar_email'] = 'blah';
        $this->assertFalse($obj->Search());

        unset($_REQUEST['ar_email']);
        $_REQUEST['ar_page_size'] = '0';
        $this->assertFalse($obj->Search());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $obj->Search();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExportAllWorks()
    {
        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Controller_Manager();
        $this->mockProperty($obj, 'Template', $mockTpl);
        unset($_REQUEST['ar_end_date']);

        $mockStmt = new PDOStatementMock(2, 3, [
            [1, 'a', null],
            [2, 'b', null],
            null,
        ], [
            ['native_type' => 'LONG'],
            ['native_type' => 'VAR_STRING'],
            ['native_type' => 'VAR_STRING']
        ]);

        $mockConn = new PDOMock($mockStmt);

        Controller_Manager::SetConn($mockConn);

        $_REQUEST['itemid'] = [1, 2];

        $this->assertTrue($obj->ExportAll());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $obj->ExportAll();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testExportListExportsSelectedTickets()
    {
        $ids = [1, 2];
        $_REQUEST = [
            'ar_start_date' => '01/01/1970',
            'ar_end_date' => '31/12/2100',
            'ar_page_size' => 20,
            'ar_is_trash' => 0,
            'ar_email' => 'test@email.com',
        ];
        $this->assertTrue(Controller_Manager::ExportList($ids));
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteAllWorks()
    {
        $obj = new Controller_Manager();
        $obj::SetConn(new PDOMock(new PDOStatementMock(0, 0, [], [])));

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCache->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'staffcache') {
                return [
                    1 => [
                        'staffgroupid' => '1',
                        'groupassigns' => '1',
                        'isenabled' => '1',
                    ],
                ];
            }

            return [
                1 => [
                    1 => [1 => [1]],
                ],
            ];
        });

        \SWIFT::GetInstance()->Cache = $mockCache;

        $this->assertEquals(0, $obj->DeleteAll());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $obj->DeleteAll();
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testEmptyTrashWorks()
    {
        $obj = new Controller_Manager();
        $obj::SetConn(new PDOMock(new PDOStatementMock(0, 0, [], [])));
        $_REQUEST['ar_is_trash'] = 1;
        $this->assertEquals(0, $obj->DeleteAll());
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testDeleteListExportsSelectedTickets()
    {
        $ids = [0];
        Controller_Manager::SetConn(new PDOMock(new PDOStatementMock(0, 0, [], [])));
        $this->assertEquals(0, Controller_Manager::DeleteList($ids));
    }

    public static $_nextRecord = 0;
    public static $_linkid = 1;
    public static $_userid = 2;
    public static $_orgid = 3;
    public static $_email = 'user1@email.com';

    public static function NextRecordMock()
    {
        self::$_linkid += 3;
        self::$_orgid += 3;

        self::$_nextRecord++;
        if (self::$_nextRecord === 4) {
            self::$_nextRecord = 0;
        }

        $_emails = [
            'user1@email.com',
            'user1@email.com',
            'user2@email.com',
            'user2@email.com',
        ];
        self::$_email = $_emails[self::$_nextRecord];

        self::$_userid = self::$_nextRecord === 2 ? null : self::$_orgid - 1;

        return self::$_nextRecord;
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testAjaxSearchReturnsEmail()
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')
            ->willReturnCallback([$this, 'NextRecordMock']);

        /** @var SWIFT_Database $mockDb */
        $mockDb->Record = [
            'linktypeid' => &self::$_linkid,
            'userid' => &self::$_userid,
            'userorganizationid' => &self::$_orgid,
            'email' => &self::$_email,
            'fullname' => 'full name',
            'organizationname' => 'xo',
        ];

        $obj = new Controller_Manager();
        $this->mockProperty($obj, 'Database', $mockDb);

        $this->assertFalse($obj->AjaxSearch());

        $_POST['q'] = 'user';

        $this->expectOutputString(
            "full name (xo)<br/>user1@email.com|full name|user1@email.com\n" .
            "full name (xo)<br/>user1@email.com|full name|user1@email.com\n");
        $this->assertTrue($obj->AjaxSearch());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception');
        $obj->AjaxSearch();
    }
}

class PDOMock extends PDO
{
    /**
     * @var PDOStatementMock
     */
    private $stmt;

    public function __construct(PDOStatementMock $stmt)
    {
        $this->stmt = $stmt;
    }

    public function query($statement, $mode = PDO::ATTR_DEFAULT_FETCH_MODE, $arg3 = null, array $ctorargs = array())
    {
        return $this->stmt;
    }

    public function getAttribute($attribute)
    {
        return $attribute;
    }

    public function setAttribute($attribute, $value)
    {
        // do nothing
    }

    public function exec($statement)
    {
        // do nothing
    }
}

class PDOStatementMock extends \PDOStatement
{
    private $_fetchColumn;
    public $_columnCount;
    /**
     * @var array
     */
    private $_fetch;

    /**
     * @var array
     */
    private $_getColumnMeta;

    public function __construct($_fetchColumn, $_columnCount, array $_fetch, array $_getColumnMeta)
    {
        $this->_fetchColumn = $_fetchColumn;
        $this->_columnCount = $_columnCount;
        $this->_fetch = $_fetch;
        $this->_getColumnMeta = $_getColumnMeta;
    }

    public function fetchColumn($column_number = 0)
    {
        return $this->_fetchColumn;
    }

    /**
     * @return mixed
     */
    public function columnCount()
    {
        return $this->_columnCount;
    }

    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        return array_shift($this->_fetch);
    }

    public function getColumnMeta($column)
    {
        return array_shift($this->_getColumnMeta);
    }
}
