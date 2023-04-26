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

namespace News\Models\Subscriber;

use News\Admin\LoaderMock;
use SWIFT;
use SWIFT_Exception;

/**
 * Class SWIFT_NewsSubscriberTest
 * @group news
 */
class SWIFT_NewsSubscriberTest extends \SWIFT_TestCase
{
    /**
     * @param int $_newsSubscriberID
     * @return SWIFT_NewsSubscriberMock
     * @throws SWIFT_Subscriber_Exception
     */
    public function getModel($_newsSubscriberID = 1)
    {
        $mockDb = $this->getMockBuilder('SWIFT_Database')
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('NextRecord')->willReturnOnConsecutiveCalls(true, false, true, false);
        $mockDb->method('Insert_ID')->willReturn(1);
        $mockDb->method('Escape')->willReturnArgument(0);
        $mockDb->method('QueryFetch')->willReturnCallback(function ($x) {
            if (false !== strpos($x, "newssubscriberid = '0'") ||
                false !== strpos($x, 'userid = 2') ||
                false !== strpos($x, "email = 'me2@email.com'")) {
                return false;
            }

            if (false !== strpos($x, 'userid = 3')) {
                return [
                    'newssubscriberid' => 3,
                    'userid' => 3,
                    'usergroupid' => 0,
                    'email' => 'me2@email.com',
                ];
            }

            if (false !== strpos($x, 'userid = 4') ||
                false !== strpos($x, "email = 'me3@email.com'")) {
                return [
                    'usergroupid' => 0,
                    'email' => 'me2@email.com',
                ];
            }

            return [
                'newssubscriberid' => 1,
                'userid' => 1,
                'usergroupid' => 0,
                'email' => 'me@email.com',
            ];
        });

        $mockDb->Record = [
            'email' => 'me@email.com',
            'linktypeid' => 1,
        ];

        SWIFT::GetInstance()->Database = $mockDb;

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCache->method('Get')->willReturn([1=>[1]]);

        SWIFT::GetInstance()->Cache = $mockCache;

        SWIFT::GetInstance()->Load = new LoaderMock();

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('Get')->willReturnArgument(0);

        $mockSettings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSettings->method('Get')->willReturnOnConsecutiveCalls('1', '0');

        $mockMail = $this->getMockBuilder('SWIFT_Mail')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
            ->disableOriginalConstructor()
            ->getMock();

        return new SWIFT_NewsSubscriberMock($_newsSubscriberID, [
            'Mail' => $mockMail,
            'Template' => $mockTpl,
            'Language' => $mockLang,
            'Settings' => $mockSettings,
            'Database' => SWIFT::GetInstance()->Database,
        ]);
    }

    /**
     * @throws SWIFT_Exception
     * @throws SWIFT_Subscriber_Exception
     */
    public function testGetNewsSubscriberIDThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Subscriber\SWIFT_Subscriber_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetNewsSubscriberID();
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testGetDataStoreReturnsArray()
    {
        $obj = $this->getModel();
        $this->assertInternalType('array', $obj->GetDataStore());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Subscriber\SWIFT_Subscriber_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetDataStore();
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testGetPropertyThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\Subscriber\SWIFT_Subscriber_Exception',
            SWIFT_INVALIDDATA);
        $obj->GetProperty('invalid');
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testGetPropertyThrowsException()
    {
        $obj = $this->getModel();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Subscriber\SWIFT_Subscriber_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->GetProperty('prop');
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testCreateThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\Subscriber\SWIFT_Subscriber_Exception',
            SWIFT_INVALIDDATA);
        $obj::Create('', false);
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testCreateReturnsId()
    {
        $obj = $this->getModel();
        $this->assertEquals(1, $obj::Create('email', false));
        $this->assertEquals(1, $obj::Create('email', true));
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testUpdateThrowsInvalidDataException()
    {
        $obj = $this->getModel();
        $this->setExpectedException('News\Models\Subscriber\SWIFT_Subscriber_Exception', SWIFT_INVALIDDATA);
        $obj->Update('');
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testUpdateThrowsException()
    {
        $obj = $this->getModel();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Subscriber\SWIFT_Subscriber_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->Update('');
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testDeleteThrowsException()
    {
        $obj = $this->getModel();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('News\Models\Subscriber\SWIFT_Subscriber_Exception',
            SWIFT_CLASSNOTLOADED);
        $obj->Delete();
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testDeleteListReturnsFalse()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::DeleteList([]),
            'Returns false if empty array');
    }

    /**
     * @throws SWIFT_Exception
     * @throws SWIFT_Subscriber_Exception
     */
    public function testDispatchValidationEmailThrowsException()
    {
        $obj = $this->getModel();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->DispatchValidationEmail('');
    }

    /**
     * @throws SWIFT_Exception
     * @throws SWIFT_Subscriber_Exception
     */
    public function testDispatchWelcomeEmailThrowsException()
    {
        $obj = $this->getModel();

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->DispatchWelcomeEmail();
    }

    /**
     * @throws SWIFT_Exception
     * @throws SWIFT_Subscriber_Exception
     */
    public function testMarkAsValidatedReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertTrue($obj->MarkAsValidated());

        $obj->SetIsClassLoaded(false);
        $this->setExpectedException('SWIFT_Exception', SWIFT_CLASSNOTLOADED);
        $obj->MarkAsValidated();
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testExportReturnsTrue()
    {
        $obj = $this->getModel();
        $this->expectOutputRegex('/subscribers/');
        $this->assertTrue($obj::Export(1));
        $_SERVER['HTTP_USER_AGENT'] = 'MS MSIE';
        $this->assertTrue($obj::Export(0));
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testImportReturnsNumber()
    {
        $obj = $this->getModel();
        $this->assertEquals(0, $obj::Import(' ,'));
        $this->assertEquals(1, $obj::Import('me@email.com,me2@email.com'));
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testDeleteOnEmailReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::DeleteOnEmail(''),
            'Returns false with empty email');

        $this->assertTrue($obj::DeleteOnEmail('me@email.com'),
            'Returns true after deleting');
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testDeleteOnUSerIDReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::DeleteOnUserID(0),
            'Returns false with empty id');

        $this->assertTrue($obj::DeleteOnUserID(1),
            'Returns true after deleting');
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testIsSubscribedOnUserIDReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::IsSubscribedOnUserID(0),
            'Returns false with empty id');

        $this->assertFalse($obj::IsSubscribedOnUserID(2),
            'Returns false with invalid id');

        $this->assertTrue($obj::IsSubscribedOnUserID(1),
            'Returns true with valid id');
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testSubscribeReturnsTrue()
    {
        $obj = $this->getModel();
        $this->assertFalse($obj::Subscribe(0, []),
            'Returns false with empty id');

        $this->assertTrue($obj::Subscribe(3, [1]),
            'Returns true with valid id');

        $this->assertTrue($obj::Subscribe(2, ['me@email.com']),
            'Returns true with valid id');

        $this->assertTrue($obj::Subscribe(4, ['me2@email.com']),
            'Returns true with valid id');
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testUnsubscribeReturnsTrue()
    {
        $obj = $this->getModel();

        $this->assertTrue($obj::UnSubscribe(1, [1]),
            'Returns true after unsubscribing');

        $this->assertTrue($obj::UnSubscribe(2, ['me@email.com']),
            'Returns true after unsubscribing');

        $this->assertFalse($obj::UnSubscribe(2, []),
            'Returns false with empty list');
    }

    /**
     * @throws SWIFT_Subscriber_Exception
     */
    public function testRetreiveSubscriberOnUserReturnsObject()
    {
        $obj = $this->getModel();

        $this->assertNotNull($obj::RetreiveSubscriberOnUser(1),
            'Returns object');

        $this->assertFalse($obj::RetreiveSubscriberOnUser(2, 'me3@email.com'),
            'Returns false with invalid id');
    }
}

class SWIFT_NewsSubscriberMock extends SWIFT_NewsSubscriber
{
    /**
     * @var SWIFT_NewsSubscriberMock
     */
    private static $_instance;

    /**
     * SWIFT_NewsSubscriberMock constructor.
     * @param $_newsSubscriberID
     * @param array $services
     * @throws SWIFT_Subscriber_Exception
     */
    public function __construct($_newsSubscriberID, array $services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $prop => $service) {
            $this->$prop = $service;
        }

        parent::__construct($_newsSubscriberID);

        if (self::$_instance === null) {
            self::$_instance = $this;
        }
    }

    public function Initialize()
    {
        return true;
    }

    /**
     * Overriden to return this mock instance
     *
     * @param int $_newsSubscriberID
     * @return SWIFT_NewsSubscriber|SWIFT_NewsSubscriberMock
     */
    public static function GetInstance($_newsSubscriberID)
    {
        self::$_instance->SetIsClassLoaded(true);

        return self::$_instance;
    }

    public static function IsSubscribed($_emailAddress) {
        if ($_emailAddress === 'me3@email.com') {
            return true;
        }

        return parent::IsSubscribed($_emailAddress);
    }
}
