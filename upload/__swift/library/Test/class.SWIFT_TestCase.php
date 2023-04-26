<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

use Base\Library\Notification\SWIFT_NotificationManager;
use Base\Models\User\SWIFT_User;

/**
 * The SWIFT Test Case Class
 *
 * @author Varun Shoor
 */
abstract class SWIFT_TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var array stores a list of mocked services */
    protected $mockServices = null;

    // Database NextRecord handling stuff
    const NEXT_RECORD_CUSTOM_LIMIT = 1;
    const NEXT_RECORD_NO_LIMIT = 2;
    const NEXT_RECORD_QUERY_RESET = 3;
    const NEXT_RECORD_RETURN_CALLBACK = 4;

    public static $nextRecordCount = 0;
    public static $nextRecordLimit = 1;
    public static $nextRecordType = self::NEXT_RECORD_CUSTOM_LIMIT;

    public static $databaseCallback = [];

    protected function setNextRecordLimit($_nextRecordLimit)
    {
        if (static::$nextRecordType === self::NEXT_RECORD_CUSTOM_LIMIT) {
            static::$nextRecordLimit = $_nextRecordLimit;
        }
    }

    protected function setNextRecordNoLimit()
    {
        static::$nextRecordCount = 0;
        static::$nextRecordLimit = 1;
        static::$nextRecordType = self::NEXT_RECORD_NO_LIMIT;
    }

    protected function setNextRecordType($_nextRecordType)
    {
        if ($_nextRecordType >= self::NEXT_RECORD_CUSTOM_LIMIT &&
            $_nextRecordType <= self::NEXT_RECORD_RETURN_CALLBACK) {
            static::$nextRecordType = $_nextRecordType;
        }
    }

    protected function setUp()
    {
        unset($_POST, $_REQUEST, $_FILES);

        static::$nextRecordCount = 0;
        static::$nextRecordLimit = 1;
        static::$nextRecordType = self::NEXT_RECORD_CUSTOM_LIMIT;
        static::$databaseCallback = [];
    }

    protected function tearDown()
    {
        // speed things up by deleting objects in memory
        $refl = new ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() &&
                (0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit\\') ||
                    0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_'))) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }

    /**
     * @param object $object The object whose property is to be mocked
     * @param string $propertyName The property name
     * @param mixed $value The property value
     */
    public function mockProperty($object, $propertyName, $value)
    {
        try {
            $reflectionClass = new \ReflectionClass($object);
            $property = $reflectionClass->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue($object, $value);
            $property->setAccessible(false);
        } catch (ReflectionException $e) {
        }
    }

    /**
     * Gets a Reflection method and sets it accessible
     *
     * @param string $className
     * @param string $methodName
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected function getMethod($className, $methodName)
    {
        $method = new \ReflectionMethod($className, $methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Helper method to assert if a class not loaded exception is thrown
     *
     * @param object $obj Object on which to call $methodName
     * @param string $methodName Name of the method to call
     * @param mixed $arg,... unlimited OPTIONAL number of arguments to pass to method
     */
    protected function assertClassNotLoaded($obj, $methodName, $arg = null)
    {
        if (!method_exists($obj, 'SetIsClassLoaded')) {
            return;
        }
        $args = func_get_args();
        $obj->SetIsClassLoaded(false);
        $this->setExpectedException(\SWIFT_Exception::class);
        call_user_func_array([$obj, $methodName], array_splice($args, 2));
    }

    /**
     * Helper method to assert if invalid data exception is thrown
     *
     * @param object $obj Object on which to call $methodName
     * @param string $methodName Name of the method to call
     * @param mixed $arg,... unlimited OPTIONAL number of arguments to pass to method
     */
    protected function assertInvalidData($obj, $methodName, $arg = null)
    {
        $args = func_get_args();
        $this->setExpectedException('SWIFT_Exception', SWIFT_INVALIDDATA);
        call_user_func_array([$obj, $methodName], array_splice($args, 2));
    }

    /**
     * Generates and stores a list of commonly used services
     *
     * @return array
     */
    protected function getMockServices()
    {
        if ($this->mockServices !== null) {
            return $this->mockServices;
        }

        $mockDb = $this->getMockBuilder(SWIFT_Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockDb->method('AutoExecute')->willReturn(true);
        $mockDb->method('Insert_ID')->willReturnCallback(function () {
            if (isset(static::$databaseCallback['Insert_ID'])) {
                return call_user_func(static::$databaseCallback['Insert_ID']);
            }

            return 1;
        });
        $mockDb->method('QueryLimit')->willReturnCallback(function ($x) {
            if (static::$nextRecordType === static::NEXT_RECORD_QUERY_RESET) {
                static::$nextRecordCount = 0;
            }

            if (isset(static::$databaseCallback['QueryLimit'])) {
                call_user_func(static::$databaseCallback['QueryLimit'], $x);
            }

            return true;
        });
        $mockDb->method('Query')->willReturnCallback(function ($x) {
            if (false !== strpos($x, 'SELECT customfieldid, fieldtype, customfieldgroupid from')) {
                static::$databaseCallback['stop'] = true;
            }

            if (static::$nextRecordType === static::NEXT_RECORD_QUERY_RESET) {
                static::$nextRecordCount = 0;
            }

            if (isset(static::$databaseCallback['Query'])) {
                call_user_func(static::$databaseCallback['Query'], $x);
            }
        });
        $mockDb->method('QueryFetchAll')->willReturn([]);
        $mockDb->method('NextRecord')->willReturnCallback(function () {
            static::$nextRecordCount++;

            if (isset(static::$databaseCallback['stop'])) {
                unset(static::$databaseCallback['stop']);
                return false;
            }

            if (isset(static::$databaseCallback['NextRecord'])) {
                $ret = call_user_func(static::$databaseCallback['NextRecord']);

                if (static::$nextRecordType === static::NEXT_RECORD_RETURN_CALLBACK) {
                    return $ret;
                }
            }

            if (static::$nextRecordType === static::NEXT_RECORD_CUSTOM_LIMIT) {
                return static::$nextRecordCount <= static::$nextRecordLimit;
            }

            if (static::$nextRecordType === static::NEXT_RECORD_NO_LIMIT ||
                static::$nextRecordType === static::NEXT_RECORD_QUERY_RESET) {
                return static::$nextRecordCount % 2;
            }

            return false;
        });

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('Get')->willReturnCallback(function ($x) {
            if (isset(static::$databaseCallback['SettingsGet'])) {
                return call_user_func(static::$databaseCallback['SettingsGet'], $x);
            }

            if ($x === 't_eticketid') {
                return 'seq';
            }

            if ($x === 'tb_maxwordlength') {
                return '100';
            }

            if ($x === 'security_captchatype') {
                return 'recaptcha';
            }

            if (in_array($x, [
                'user_dispatchregemail',
                't_slaresets',
                'cpu_enablesmtp',
            ], true)) {
                // don't send welcome email
                // don't run clearOverdue on shutdown
                return '0';
            }

            if (in_array($x, [
                'cpu_enablemailqueue',
            ], true)) {
                return 1;
            }

            if (false !== strpos($x, 'mail')) {
                return 'me@mail.com';
            }

            if (isset(static::$databaseCallback['SettingsGet'])) {
                return call_user_func(static::$databaseCallback['SettingsGet'], $x);
            }

            return '1';
        });

        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturnCallback(function ($x) {
            if (isset(static::$databaseCallback['CacheGet'])) {
                return call_user_func(static::$databaseCallback['CacheGet'], $x);
            }

            return [1 => [1 => [1]]];
        });

        $mockLang = $this->getMockBuilder('SWIFT_LanguageEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $mockLang->method('GetLinked')->willReturnCallback(function ($x) {
            if (isset(static::$databaseCallback['GetLinked'])) {
                return call_user_func(static::$databaseCallback['GetLinked'], $x);
            }

            return 1;
        });
        $mockLang->method('Get')->willReturnCallback(function ($x) {
            if ($x === 'charset') {
                return 'UTF-8';
            }

            if (false !== strpos($x, 'log')) {
                return '%s ';
            }

            return $x;
        });

        $mgr = $this->getMockBuilder(SWIFT_NotificationManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockUser = $this->getMockBuilder(SWIFT_User::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods(['GetIsClassLoaded', 'GetUserID', 'GetEmailList', 'GetProperty', 'Get', '__destruct'])
            ->getMock();
        $this->mockProperty($mockUser, 'NotificationManager', $mgr);
        $mockUser->method('__destruct')->willReturn(true);
        $mockUser->method('GetIsClassLoaded')->willReturn(true);
        $mockUser->method('GetUserID')->willReturn(1);
        $mockUser->method('GetEmailList')->willReturn(['me@mail.com']);
        $mockUser->method('GetProperty')->willReturnArgument(0);
        $mockUser->method('Get')->willReturnCallback(function ($x) {
            if (isset(static::$databaseCallback['UserGet'])) {
                return call_user_func(static::$databaseCallback['UserGet'], $x);
            }

            if (substr($x, -2) === 'id') {
                return 1;
            }

            return $x;
        });

        $mockStaff = $this->getMockBuilder('Base\Models\Staff\SWIFT_Staff')
            ->disableOriginalConstructor()
            ->getMock();

        $mockStaff->method('GetPermission')->willReturnOnConsecutiveCalls('1', '0');
        $mockStaff->method('GetIsClassLoaded')->willReturn(true);
        $mockStaff->method('GetStaffID')->willReturn(1);
        $mockStaff->method('GetProperty')->willReturnCallback(function ($x) {
            if (strtolower(substr($x, -2)) === 'id') {
                return '1';
            }

            if ($x === 'timezonephp') {
                return 'UTC';
            }

            return $x;
        });

        $mockSession = $this->getMockBuilder('SWIFT_Session')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSession->method('GetSessionID')->willReturn(1);
        $mockSession->method('GetIsClassLoaded')->willReturn(true);
        $mockSession->method('GetProperty')->willReturnCallback(function ($x) {
            if (strtolower(substr($x, -2)) === 'id') {
                return '1';
            }

            if (false !== strpos($x, 'mail')) {
                return 'me@mail.com';
            }

            return $x;
        });

        $this->mockProperty(\SWIFT::GetInstance(), 'Load', new LoaderMock());
        $this->mockProperty(\SWIFT::GetInstance(), 'Staff', $mockStaff);
        $this->mockProperty(\SWIFT::GetInstance(), 'User', $mockUser);
        $this->mockProperty(\SWIFT::GetInstance(), 'Session', $mockSession);
        $this->mockProperty(\SWIFT::GetInstance(), 'Database', $mockDb);
        $this->mockProperty(\SWIFT::GetInstance(), 'Cache', $mockCache);
        $this->mockProperty(\SWIFT::GetInstance(), 'Settings', $settings);
        $this->mockProperty(\SWIFT::GetInstance(), 'Language', $mockLang);

        return $this->mockServices = [
            'Staff' => $mockStaff,
            'Session' => $mockSession,
            'Database' => $mockDb,
            'Cache' => $mockCache,
            'Settings' => $settings,
            'Language' => $mockLang,
        ];
    }

    /**
     * This method creates a new instance of a class and assigns a set of mocked
     * services as properties
     *
     * @param string $objectMock The name of the class to create an instance from
     * @param array $_services A list of services to mock
     * @return mixed
     */
    protected function getMockObject($objectMock, array $_services = [])
    {
        $this->getMockServices();
        $services = [];

        if (is_subclass_of($objectMock, 'SWIFT_Controller') ||
            is_subclass_of($objectMock, 'SWIFT_View')) {
            $mockView = $this->getMockBuilder('SWIFT_View')
                ->disableOriginalConstructor()
                ->getMock();

            $mockInt = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel')
                ->disableOriginalConstructor()
                ->getMock();

            $mockGrid = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceGrid')
                ->disableOriginalConstructor()
                ->getMock();
            $mockGrid->method('GetSearchQueryString')->willReturn('query');
            $mockGrid->method('GetMode')->willReturn(2);

            if (is_subclass_of($objectMock, 'SWIFT_View')) {
                $mockTab = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceTab')
                    ->disableOriginalConstructor()
                    ->getMock();

                $mockTb = $this->getMockBuilder('Base\Library\UserInterface\SWIFT_UserInterfaceToolbar')
                    ->disableOriginalConstructor()
                    ->getMock();

                $this->mockProperty($mockInt, 'Toolbar', $mockTb);
                $this->mockProperty($mockTab, 'Toolbar', $mockTb);

                $mockInt->method('AddTab')->willReturn($mockTab);
                $mockInt->method('GetIsClassLoaded')->willReturn(true);
                $this->mockProperty($mockTab, 'UserInterface', $mockInt);
            }

            $mockTpl = $this->getMockBuilder('SWIFT_TemplateEngine')
                ->disableOriginalConstructor()
                ->getMock();

            $this->mockProperty(\SWIFT::GetInstance(), 'UserInterface', $mockInt);

            $services['Template'] = $mockTpl;
            $services['UserInterface'] = $mockInt;
            $services['UserInterfaceGrid'] = $mockGrid;
            $services['View'] = $mockView;
        }

        if (is_subclass_of($objectMock, 'Controller_api') ||
            is_subclass_of($objectMock, 'Controller_staffapi')) {
            $mgr = $this->getMockBuilder('SWIFT_RESTManager')
                ->disableOriginalConstructor()
                ->getMock();

            $mgr->method('Authenticate')
                ->willReturn(true);

            $svr = $this->getMockBuilder('SWIFT_RESTServer')
                ->disableOriginalConstructor()
                ->getMock();

            $svr->method('GetVariableContainer')
                ->willReturn(['salt' => '1']);

            $xml = $this->getMockBuilder('SWIFT_XML')
                ->disableOriginalConstructor()
                ->getMock();

            $services['XML'] = $xml;
            $services['RESTManager'] = $mgr;
            $services['RESTServer'] = $svr;
        }


        return new $objectMock(array_merge([
            'Cache' => $this->mockServices['Cache'],
            'Language' => $this->mockServices['Language'],
            'Settings' => $this->mockServices['Settings'],
            'Database' => $this->mockServices['Database'],
        ], $services, $_services));
    }

    /**
     * Wrapper to make it easier to migrate to PHPUnit 6
     *
     * @author Douglas Yau <douglas.yau@crossover.com>
     * @param mixed $class type of exception to expect
     * @param null $message not used, kept for compatibility
     * @param null $code
     */
    public function setExpectedException($class, $message = null, $code = null)
    {
        $this->expectException($class);
    }
}

class LoaderMock
{
    public $Load;
    protected $obj;

    /**
     * LoaderMock constructor.
     * @param Object|null $obj loader class
     */
    public function __construct($obj = null)
    {
        $this->obj = $obj;
    }

    public function __destruct()
    {
        $this->obj = null;
    }

    public function Library($_libraryName, $_arguments = array(), $_initiateInstance = true, $_customAppName = false, $_appName = '')
    {
        return $_libraryName;
    }

    public function Manage()
    {
        return true;
    }

    public function Method($_methodName = '')
    {
        return true;
    }

    public function Index()
    {
        return true;
    }

    public function Insert()
    {
        return true;
    }

    public function Edit()
    {
        return true;
    }

    public function View($_viewName = '')
    {
        return true;
    }

    public function Model($_modelName = '', $_arguments = array(), $_initiateInstance = true, $_customAppName = false, $appName = '')
    {
        return true;
    }

    public function Redirect()
    {
        return true;
    }

    public function InsertTicket()
    {
        return true;
    }

    public function NewTicket()
    {
        return true;
    }

    public function Search()
    {
        return true;
    }

    public function NewTicketForm()
    {
        return true;
    }

    public function RenderForm()
    {
        return true;
    }

    /**
     * @param int $_hasAttachments
     * @return array
     */
    public function CheckForValidAttachments($_hasAttachments)
    {
        if ($this->obj !== null && method_exists($this->obj, 'CheckForValidAttachments')) {
            return $this->obj->CheckForValidAttachments($_hasAttachments);
        }

        return $this->obj->_getAttachmentsReturnValue ?: [false, ['error']];
    }

    public function Controller($_controllerName = '', $_customApp = '')
    {
        $this->Load = $this;

        return $this;
    }
}
