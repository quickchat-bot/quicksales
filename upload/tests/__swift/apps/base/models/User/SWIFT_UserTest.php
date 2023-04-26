<?php

namespace Base\Models\User;

use SWIFT_TestCase;

class SWIFT_UserTest extends SWIFT_TestCase
{
    public function createProvider()
    {
        return [
            [1, 1, 0, 'Test 1', '1st', '1234567890', 1, SWIFT_User::ROLE_USER, ['ut-test1@opencart.com.vn'], 'password', 0, 'GMT', 1, 0, time(), time(), 1, 1, 0],
            [1, 1, 0, 'Test 1', '1st', '1234567890', 1, SWIFT_User::ROLE_USER, ['ut-test2@opencart.com.vn'], 'password', 0, 'GMT', 1, 0, time(), time(), 0, 1, 0],
            [1, 1, 0, 'Test 1', '1st', '1234567890', 1, SWIFT_User::ROLE_USER, ['ut-test3@opencart.com.vn'], 'password', 1, 'GMT', 1, 1, time(), time(), 1, 1, 1],
            [1, 1, 0, 'Test 1', '1st', '1234567890', 1, SWIFT_User::ROLE_USER, ['ut-test4@opencart.com.vn'], 'password', 0, '', 1, 0, time(), time(), 0, 1, 0],
            [1, 1, 0, 'Test 1', '1st', '1234567890', 1, SWIFT_User::ROLE_USER, ['ut-test5@opencart.com.vn'], null, 0, 'GMT', 1, 0, time(), time(), 0, 1, 0],
            [1, 1, 0, 'Test 1', '1st', '1234567890', 1, SWIFT_User::ROLE_USER, ['ut-test6@opencart.com.vn'], 'password', 0, 'GMT', 1, 0, '', time(), 0, 1, 0],
            [1, 1, 0, 'Test 1', '1st', '1234567890', 1, SWIFT_User::ROLE_USER, ['ut-test8@opencart.com.vn'], 'password', 0, 'GMT', 1, 0, time(), time(), 0, 1, 0],
            [1, 1, 0, 'Test 1', '', '1234567890', 1, SWIFT_User::ROLE_USER, ['ut-test9@opencart.com.vn'], 'password', 0, 'GMT', 1, 0, time(), time(), 0, 1, 0],
            [1, 1, 0, 'Test 1', '', '', 1, SWIFT_User::ROLE_USER, ['ut-test10@opencart.com.vn'], 'password', 0, 'GMT', 1, 0, time(), time(), 0, 1, 0],
            [1, 1, 0, 'Test 1', '', '', 1, SWIFT_User::ROLE_USER, ['ut-test11@opencart.com.vn'], 'password', 0, '', 1, 0, time(), time(), 0, 1, 0],
            [1, 1, 0, 'Test 1', '', '', 1, SWIFT_User::ROLE_USER, ['ut-test12@opencart.com.vn'], 'password', 0, '', 1, 0, null, time(), 0, 1, 0],
        ];
    }

    public function createExceptionProvider()
    {
        return [
            [1, 1, 0, 'Test 1', '1st', '1234567890', 1, SWIFT_User::ROLE_USER, ['ut-test7@opencart.com.vn'], 'password', 0, 'GMT', 1, 0, time(), '', 0, 1, 0],
            [0, 1, '', 'Test 1', '', '', 1, SWIFT_User::ROLE_USER, ['ut-test100@opencart.com.vn'], 'password', 0, '', 1, 0, null, null, 0, 1, 0],
            [0, 0, '', 'Test 1', '', '', 1, SWIFT_User::ROLE_USER, ['ut-test101@opencart.com.vn'], 'password', 0, '', 1, 0, null, null, 0, 1, 0],
            [1, 1, 0, 'Test 1', '', '', 1, SWIFT_User::ROLE_USER, ['ut-test13@opencart.com.vn'], 'password', 0, '', 1, 0, null, null, 0, 1, 0],
            [1, 1, 0, 'Test 1', '', '', 1, SWIFT_User::ROLE_MANAGER, ['ut-test14@opencart.com.vn'], 'password', 0, '', 1, 0, null, null, 0, 1, 0],
            [1, 1, 0, 'Test 1', '', '', 1, 0, ['ut-test14@opencart.com.vn'], 'password', 0, '', 1, 0, null, null, 0, 1, 0],
        ];
    }

    /**
     * @dataProvider createProvider
     *
     * @param $_userGroupID
     * @param $_userOrganizationID
     * @param $_salutation
     * @param $_fullName
     * @param $_userDesignation
     * @param $_phone
     * @param $_isEnabled
     * @param $_userRole
     * @param $_emailContainer
     * @param $_userPassword
     * @param $_languageID
     * @param $_timeZonePHP
     * @param $_enableDST
     * @param $_slaPlanID
     * @param $_slaExpiry
     * @param $_userExpiry
     * @param $_sendWelcomeEmail
     * @param $_isValidated
     * @param $_calculateGeoIP
     * @throws \SWIFT_Exception
     */
    public function testCreate($_userGroupID, $_userOrganizationID, $_salutation, $_fullName, $_userDesignation, $_phone, $_isEnabled, $_userRole,
                               $_emailContainer, $_userPassword, $_languageID, $_timeZonePHP, $_enableDST, $_slaPlanID,
                               $_slaExpiry, $_userExpiry, $_sendWelcomeEmail, $_isValidated, $_calculateGeoIP)
    {
        $cache = $this->createMock(\SWIFT_CacheStore::class);
        $cache->method('Get')
            ->will(self::returnValueMap([
                ['languagecache', [['languagecode' => 1, 'isdefault' => 1]]],
                ['templategroupcache', [['regusergroupid' => 1, 'tgroupid' => 1]]]
            ]));

        $db = $this->createMock(\SWIFT_Database::class);
        $db->method('QueryFetch')
            ->will($this->returnValueMap([
                ["SELECT usergroupid FROM swusergroups WHERE usergroupid = '1'", 3, false, ['usergroupid' => 1,]],
                ["SELECT * FROM swusers WHERE userid = '1'", 3, false, ['userid' => 1]],
                ["SELECT * FROM swuseremails WHERE useremailid = '1'", 3, false, ['useremailid' => 1, 'linktype' => 1]],
            ]));
        $db->method('NextRecord')
            ->will($this->onConsecutiveCalls(
                [['useremailid' => 1, 'email' => $_emailContainer[0]]],
                false));
        $db->method('Insert_ID')
            ->willReturn(1);

        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Cache = $cache;
        $_SWIFT->Database = $db;

        $actual = SWIFT_User::Create($_userGroupID, $_userOrganizationID, $_salutation, $_fullName, $_userDesignation, $_phone, $_isEnabled, $_userRole,
            $_emailContainer, $_userPassword, $_languageID, $_timeZonePHP, $_enableDST, $_slaPlanID,
            $_slaExpiry, $_userExpiry, $_sendWelcomeEmail, $_isValidated, $_calculateGeoIP);

        $this->assertInstanceOf(SWIFT_User::class, $actual);
    }

    /**
     * @dataProvider createExceptionProvider
     *
     * @param $_userGroupID
     * @param $_userOrganizationID
     * @param $_salutation
     * @param $_fullName
     * @param $_userDesignation
     * @param $_phone
     * @param $_isEnabled
     * @param $_userRole
     * @param $_emailContainer
     * @param $_userPassword
     * @param $_languageID
     * @param $_timeZonePHP
     * @param $_enableDST
     * @param $_slaPlanID
     * @param $_slaExpiry
     * @param $_userExpiry
     * @param $_sendWelcomeEmail
     * @param $_isValidated
     * @param $_calculateGeoIP
     * @throws \SWIFT_Exception
     */
    public function testCreateException($_userGroupID, $_userOrganizationID, $_salutation, $_fullName, $_userDesignation, $_phone, $_isEnabled, $_userRole,
                               $_emailContainer, $_userPassword, $_languageID, $_timeZonePHP, $_enableDST, $_slaPlanID,
                               $_slaExpiry, $_userExpiry, $_sendWelcomeEmail, $_isValidated, $_calculateGeoIP)
    {
        $this->expectException(\SWIFT_Exception::class);
        $actual = SWIFT_User::Create($_userGroupID, $_userOrganizationID, $_salutation, $_fullName, $_userDesignation, $_phone, $_isEnabled, $_userRole,
            $_emailContainer, $_userPassword, $_languageID, $_timeZonePHP, $_enableDST, $_slaPlanID,
            $_slaExpiry, $_userExpiry, $_sendWelcomeEmail, $_isValidated, $_calculateGeoIP);
    }
}
