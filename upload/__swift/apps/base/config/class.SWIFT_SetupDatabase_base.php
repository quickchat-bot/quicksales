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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Base;

use SWIFT;
use SWIFT_App;
use SWIFT_Cron;
use SWIFT_Exception;
use Base\Models\Language\SWIFT_Language;
use Base\Library\Language\SWIFT_LanguageManager;
use Base\Models\PolicyLink\SWIFT_PolicyLink;
use Base\Library\SearchEngine\SWIFT_SearchEngine;
use SWIFT_SetupDatabase;
use SWIFT_SetupDatabaseIndex;
use SWIFT_SetupDatabaseInsertSQL;
use SWIFT_SetupDatabaseSQL;
use SWIFT_SetupDatabaseTable;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffGroup;
use Base\Models\Staff\SWIFT_StaffGroupSettings;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserGroup;
use Base\Models\User\SWIFT_UserNoteManager;
use Base\Models\User\SWIFT_UserOrganization;
use Base\Models\User\SWIFT_UserProfileImage;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The Main Installer
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDatabase_base extends SWIFT_SetupDatabase
{
    // Core Constants
    const PAGE_COUNT = 1;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct(APP_BASE);
    }

    /**
     * Loads the table into the container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadTables()
    {
        // ======= SIGNATURES =======
        $this->AddTable('signatures', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "signatures", "signatureid I PRIMARY AUTO NOTNULL,
                                                                dateline I DEFAULT '0' NOTNULL,
                                                                staffid I DEFAULT '0' NOTNULL,
                                                                signature X"));
        $this->AddIndex('signatures', new SWIFT_SetupDatabaseIndex("signatures1", TABLE_PREFIX . "signatures", "staffid"));

        // ======= TEMPLATEDATA =======
        $this->AddTable('templatedata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "templatedata", "templatedataid I PRIMARY AUTO NOTNULL,
                                                                templateid I DEFAULT '0' NOTNULL,
                                                                contents XL,
                                                                contentsdefault XL"));
        $this->AddIndex('templatedata', new SWIFT_SetupDatabaseIndex("templatedata1", TABLE_PREFIX . "templatedata", "templateid"));

        // ======= COMMENTDATA =======
        $this->AddTable('commentdata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "commentdata", "commentdataid I PRIMARY AUTO NOTNULL,
                                                                commentid I DEFAULT '0' NOTNULL,
                                                                contents X2"));
        $this->AddIndex('commentdata', new SWIFT_SetupDatabaseIndex("commentdata1", TABLE_PREFIX . "commentdata", "commentid"));


        // ======= CUSTOMFIELDOPTIONLINKS =======
        $this->AddTable('customfieldoptionlinks', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "customfieldoptionlinks", "customfieldoptionlinkid I PRIMARY AUTO NOTNULL,
                                                                customfieldid I DEFAULT '0' NOTNULL,
                                                                customfieldoptionid I DEFAULT '0' NOTNULL"));
        $this->AddIndex('customfieldoptionlinks', new SWIFT_SetupDatabaseIndex("customfieldoptionlinks1", TABLE_PREFIX . "customfieldoptionlinks", "customfieldid"));
        $this->AddIndex('customfieldoptionlinks', new SWIFT_SetupDatabaseIndex("customfieldoptionlinks2", TABLE_PREFIX . "customfieldoptionlinks", "customfieldoptionid"));

        // ======= USERNOTEDATA =======
        $this->AddTable('usernotedata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "usernotedata", "usernotedataid I PRIMARY AUTO NOTNULL,
                                                                usernoteid I DEFAULT '0' NOTNULL,
                                                                notecontents XL"));
        $this->AddIndex('usernotedata', new SWIFT_SetupDatabaseIndex("usernotedata1", TABLE_PREFIX . "usernotedata", "usernoteid"));

        for ($_index = 1; $_index <= 10; $_index++) {
            // ======= GEOIPISP =======
            $this->AddTable('geoipisp' . $_index, new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "geoipisp" . $_index, "ipfrom I8 DEFAULT '0' NOTNULL,
                                                                    ipto I8 DEFAULT '0' NOTNULL,
                                                                    isp C(255) DEFAULT '' NOTNULL"));
            $this->AddIndex('geoipisp' . $_index, new SWIFT_SetupDatabaseIndex("geoipisp1", TABLE_PREFIX . "geoipisp" . $_index, "ipto"));

            // ======= GEOIPORGANIZATION =======
            $this->AddTable('geoiporganization' . $_index, new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "geoiporganization" . $_index, "ipfrom I8 DEFAULT '0' NOTNULL,
                                                                    ipto I8 DEFAULT '0' NOTNULL,
                                                                    organization C(255) DEFAULT '' NOTNULL"));
            $this->AddIndex('geoiporganization' . $_index, new SWIFT_SetupDatabaseIndex("geoiporganization1", TABLE_PREFIX . "geoiporganization" . $_index, "ipto"));

            // ======= GEOIPCITYBLOCKS =======
            $this->AddTable('geoipcityblocks' . $_index, new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "geoipcityblocks" . $_index, "ipfrom I8 DEFAULT '0' NOTNULL,
                                                                    ipto I8 DEFAULT '0' NOTNULL,
                                                                    blockid I DEFAULT '0' NOTNULL"));
            $this->AddIndex('geoipcityblocks' . $_index, new SWIFT_SetupDatabaseIndex("geoipcityblocks1", TABLE_PREFIX . "geoipcityblocks" . $_index, "ipto"));
            $this->AddIndex('geoipcityblocks' . $_index, new SWIFT_SetupDatabaseIndex("geoipcityblocks2", TABLE_PREFIX . "geoipcityblocks" . $_index, "blockid"));

            // ======= GEOIPNETSPEED =======
            $this->AddTable('geoipnetspeed' . $_index, new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "geoipnetspeed" . $_index, "ipfrom I8 DEFAULT '0' NOTNULL,
                                                                    ipto I8 DEFAULT '0' NOTNULL,
                                                                    netspeed C(100) DEFAULT '' NOTNULL"));
            $this->AddIndex('geoipnetspeed' . $_index, new SWIFT_SetupDatabaseIndex("geoipnetspeed1", TABLE_PREFIX . "geoipnetspeed" . $_index, "ipto"));
        }

        // ======= GEOIPCITIES =======
        $this->AddTable('geoipcities', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "geoipcities", "blockid I PRIMARY DEFAULT '0' NOTNULL,
                                                                country C(5) DEFAULT '' NOTNULL,
                                                                region C(100) DEFAULT '' NOTNULL,
                                                                city C(255) DEFAULT '' NOTNULL,
                                                                postalcode C(100) DEFAULT '' NOTNULL,
                                                                latitude C(100) DEFAULT '' NOTNULL,
                                                                longitude C(100) DEFAULT '' NOTNULL,
                                                                metrocode C(100) DEFAULT '' NOTNULL,
                                                                areacode C(100) DEFAULT '' NOTNULL"));

        // ======= ATTACHMENTCHUNKS =======
        $this->AddTable('attachmentchunks', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "attachmentchunks", "chunkid I PRIMARY AUTO NOTNULL,
                                                                        attachmentid I DEFAULT '0' NOTNULL,
                                                                        contents X2,
                                                                        notbase64 I2 DEFAULT '0' NOTNULL"));
        $this->AddIndex('attachmentchunks', new SWIFT_SetupDatabaseIndex("attachmentchunks1", TABLE_PREFIX . "attachmentchunks", "attachmentid"));

        // ======= SEARCHINDEX ======
        $this->AddTable('searchindex', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "searchindex", "objid INT NOTNULL,
                                                        subobjid INT DEFAULT '0',
                                                        type SMALLINT,
                                                        ft TEXT"), self::TABLETYPE_MYISAM); // FULLTEXT index (created in Install())
        $this->AddIndex('searchindex', new SWIFT_SetupDatabaseIndex("searchindex1", TABLE_PREFIX . "searchindex", "objid"));       // Searching by id
        $this->AddIndex('searchindex', new SWIFT_SetupDatabaseIndex("searchindex2", TABLE_PREFIX . "searchindex", "type, objid"));       // Searching by type
        $this->AddIndex('searchindex', new SWIFT_SetupDatabaseIndex("searchindex3", TABLE_PREFIX . "searchindex", "objid, type")); // Searching for object by type
        $this->AddIndex('searchindex', new SWIFT_SetupDatabaseIndex("searchindex4", TABLE_PREFIX . "searchindex", "objid, subobjid, type")); // Searching for object + sub-object by type

        /**
         * ---------------------------------------------
         * NOTIFICATIONS
         * ---------------------------------------------
         */

        // ======= NOTIFICATIONCRITERIA =======
        $this->AddTable('notificationcriteria', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "notificationcriteria", "notificationcriteriaid I PRIMARY AUTO NOTNULL,
                                                                notificationruleid I DEFAULT '0' NOTNULL,
                                                                name C(200) DEFAULT '' NOTNULL,
                                                                ruleop I2 DEFAULT '0' NOTNULL,
                                                                rulematch C(255) DEFAULT '' NOTNULL,
                                                                rulematchtype I2 DEFAULT '0' NOTNULL"));
        $this->AddIndex('notificationcriteria', new SWIFT_SetupDatabaseIndex("notificationcriteria1", TABLE_PREFIX . "notificationcriteria", "notificationruleid"));

        // ======= NOTIFICATIONACTIONS =======
        $this->AddTable('notificationactions', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "notificationactions", "notificationactionid I PRIMARY AUTO NOTNULL,
                                                                notificationruleid I DEFAULT '0' NOTNULL,
                                                                actiontype I2 DEFAULT '0' NOTNULL,
                                                                contents C(255) DEFAULT '' NOTNULL"));
        $this->AddIndex('notificationactions', new SWIFT_SetupDatabaseIndex("notificationactions1", TABLE_PREFIX . "notificationactions", "notificationruleid"));

        return true;

    }

    /**
     * Get the Page Count for Execution
     *
     * @author Varun Shoor
     * @return int
     */
    public function GetPageCount()
    {
        return self::PAGE_COUNT;
    }

    /**
     * Function that does the heavy execution
     *
     * @author Varun Shoor
     * @param int $_pageIndex The Page Index
     * @return bool "true" on Success, "false" otherwise
     */
    public function Install($_pageIndex)
    {
        parent::Install($_pageIndex);

        $_SWIFT_UserGroup_guest = SWIFT_UserGroup::Create($this->Language->Get('coreguest'), SWIFT_UserGroup::TYPE_GUEST, true);
        $_SWIFT_UserGroup_registered = SWIFT_UserGroup::Create($this->Language->Get('coreregistered'), SWIFT_UserGroup::TYPE_REGISTERED, true);
        $_userGroupID_guest = $_SWIFT_UserGroup_guest->GetUserGroupID();
        $_userGroupID_registered = $_SWIFT_UserGroup_registered->GetUserGroupID();

        $this->Insert(new SWIFT_SetupDatabaseInsertSQL(TABLE_PREFIX . "templategroups", array('languageid' => '1', 'guestusergroupid' => (int)($_userGroupID_guest), 'regusergroupid' => (int)($_userGroupID_registered), 'title' => 'Default', 'description' => $this->Language->Get('coredeftgroup'), 'companyname' => $_POST["companyname"], 'ismaster' => '1', 'grouppassword' => '', 'restrictgroups' => '0', 'isdefault' => '1', 'loginshareid' => 0, 'loginapi_appid' => 0, 'ticketstatusid' => '1', 'priorityid' => '1', 'tickettypeid' => '1', 'tickets_promptpriority' => '1', 'departmentid' => '1', 'isenabled' => '1')));

        $this->ExecuteQueue();

        if (strtolower(DB_TYPE) == 'mysql' || strtolower(DB_TYPE) == 'mysqli') {
            $this->Query(new SWIFT_SetupDatabaseSQL("ALTER TABLE " . TABLE_PREFIX . "attachmentchunks CHANGE contents contents MEDIUMBLOB DEFAULT '' NOT NULL;"));
            $this->Query(new SWIFT_SetupDatabaseSQL("CREATE FULLTEXT INDEX fulltextsearch on " . TABLE_PREFIX . "searchindex (ft);"));
            self::UpgradeEngine();
        }

        // ======= STAFFGROPUPS =======
        $_StaffGroupObject_Admin = SWIFT_StaffGroup::Insert($this->Language->Get('coreadministrator'), true);
        $_StaffGroupObject_Staff = SWIFT_StaffGroup::Insert($this->Language->Get('corestaff'), false);

        // ======= STAFF =======
        $_SWIFT_StaffObject = SWIFT_Staff::Create($_POST['firstname'], $_POST['lastname'], '', $_POST['username'], $_POST['password'], $_StaffGroupObject_Admin->GetStaffGroupID(), $_POST['email'], '', '', false, true, '');

        // ======= USER & ORGANIZATION =======
        $_SWIFT_UserOrganizationObject = SWIFT_UserOrganization::Create($_POST['companyname'], SWIFT_UserOrganization::TYPE_RESTRICTED, array());
        SWIFT_User::Create($_userGroupID_registered, $_SWIFT_UserOrganizationObject->GetUserOrganizationID(), SWIFT_User::SALUTATION_NONE,
            $_POST['firstname'] . ' ' . $_POST['lastname'], '', '', true, SWIFT_User::ROLE_MANAGER, array($_POST['email']),
            $_POST['password'], 0, '', false, 0, 0, 0, false, true);

        // ======= WIDGET =======
        SWIFT_Widget::Create('PHRASE:widgethome', 'home', APP_BASE, '/Core/Default/Index', '', '{$themepath}icon_widget_home_small.png', 0, true, false, true, true, SWIFT_Widget::VISIBLE_ALL, false);
        SWIFT_Widget::Create('PHRASE:widgetregister', 'register', APP_BASE, '/Base/UserRegistration/Register', '{$themepath}icon_widget_register.svg', '{$themepath}icon_widget_register_small.png', 1, false, true, true, true, SWIFT_Widget::VISIBLE_GUESTS, false);

        // ======= CRON =======
        SWIFT_Cron::Create('cronhourlycleanup', 'Base', 'BaseHourly', 'Cleanup', -1, 0, 0, true);
        SWIFT_Cron::Create('crondailycleanup', 'Base', 'BaseDaily', 'Cleanup', 0, 0, -1, true);

        $this->InstallSampleData($_SWIFT_StaffObject, $_userGroupID_registered);

        return true;
    }

    /**
     * @author Saloni Dhall <saloni.dhall@opencart.com.vn>
     * @author Utsav Handa <utsav.handa@opencart.com.vn>
     *
     * @param SWIFT_Staff $_Staff
     * @param int $_userGroupID_registered
     *
     * @return bool
     */
    public function InstallSampleData($_Staff, $_userGroupID_registered)
    {
        if (!defined('INSTALL_SAMPLE_DATA') || INSTALL_SAMPLE_DATA != true) {
            return false;
        }

        // Create an user organization
        $_DemoUserOrganization = SWIFT_UserOrganization::Create($this->Language->Get('sample_userorganisationname'), SWIFT_UserOrganization::TYPE_RESTRICTED, array($this->Language->Get('sample_emaildomainfilters')),
            $this->Language->Get('sample_address'), $this->Language->Get('sample_city'), $this->Language->Get('sample_state'), $this->Language->Get('sample_postalcode'));

        //Create a tag on user organization
        SWIFT_Tag::Process(SWIFT_TagLink::TYPE_USERORGANIZATION, $_DemoUserOrganization->GetUserOrganizationID(), array($this->Language->Get('sample_tag')), 0);

        // Create a user
        $_User = SWIFT_User::Create($_userGroupID_registered, $_DemoUserOrganization->GetUserOrganizationID(), SWIFT_User::SALUTATION_NONE, $this->Language->Get('sample_userfullname'),
            '', '', true, SWIFT_User::ROLE_USER, array($this->Language->Get('sample_useremailaddress')), (string)mt_rand(), 0, '', false, 0, 0, 0, false, true);
        // Create a demo user tag
        SWIFT_Tag::Process(SWIFT_TagLink::TYPE_USER, $_User->GetUserID(), array($this->Language->Get('sample_tag')), 0);

        // Create a user note
        if ($_User instanceof SWIFT_User && $_User->GetIsClassLoaded()) {
            SWIFT_UserNoteManager::Create(SWIFT_UserNoteManager::LINKTYPE_USER, $_User->GetProperty('userid'), $_Staff->GetStaffID(), $_Staff->GetProperty('fullname'), $this->Language->Get('sample_usernotecontents'));
        }

        // Adding userprofileimage for an user
        SWIFT_UserProfileImage::Create($_User->GetProperty('userid'), 'jpg', '/9j/4AAQSkZJRgABAQAAAQABAAD//gA+Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2OTApLCBkZWZhdWx0IHF1YWxpdHkK/9sAQwAIBgYHBgUIBwcHCQkICgwUDQwLCwwZEhMPFB0aHx4dGhwcICQuJyAiLCMcHCg3KSwwMTQ0NB8nOT04MjwuMzQy/9sAQwEJCQkMCwwYDQ0YMiEcITIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy/8AAEQgAZABkAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A9r1q9Fnp00mRuCnArxvQtJbWfFEt5dqWihO7nux5r0LxJcJNe+Sp7Yf2qlotpHbxHyxy7FifXNcdWfvHbh4K1zpLSNUQADAA4rRjRSOazoSQKvofl5pRYVU7mD4k8PRX1uZYlxKnIPrXP+QZbSKRx84+Uk+or0BxuQgVyt7aeXLcQD5VmG9D6NXPiIbNGtGbkrPoc1dRtD+9QbXHetTTNYEiZbqPvCoLScXQktrhAlzHwwI+8PWsqeNrC98xP9Uxww9DXH1sbNK2p6BZ6om/yywwcck4rXRlz8pyN39+vMRcyxOChJXqp/pXc+HtTN9AFLAup+YHr9a9DCVr+4zjr07e8joKY4Ur8/T3p2aZIqsPn6Cu9nIMM8IOPNTj3oqu9wY3KxwRlex3qM0U7jPNdW1HfHd3LPs81vLDHsD1P5V0mmhUgibplRx6V4Z4m8XpqNzaw2Lv9igcMcAgyNmt6DxfMYwWa/h/7ZEgfka4ZRkuh3UZLY9pSdQcE1dSdSvWvGbHxkyS/NfiZSeUbIYfgea9C0zUmu7VZRkhhkUoya3RtKlGa0Z032mNBycE1n6mgubfKY8xeVrg/EXiyKwuGt2vFSb+4pyw/AVgweLwJCZLjUGA7BSP5kUm5zurCVGMNbnYXEQvH8+Bgl9HwQf4vY1k6hcuynzFIbowxgg1zmoeJpPNW4sbK+Vh1c7W3D3ANQQ/EbT7uQ2l/EyS52+YVxg+47VzulJa2vYuUorqb1he+ZuhY8j7ua39H1NrK+STBBB+ceorkAipchlbMcg+Ur61cjuzKu7diWLhvU471GzujN6qx7Ra3SXEakMu487Q2eKnYBlIYAg9jXMeDtRF1YCF8GSPofUV0c0hSNmVSzdgK9aE+aKZ5048rsRmAdooyPcUVHbzTiICSJ2bPJ4GaKsk+PVtJrtrHywSJCEGPXdg12moeD71r3c9x5cTcheQD+NMaxns9aCWghCqwmWOSPcA/Q4IIIzXa2Or+JigQ6VYzKBwQ5H86wlNuzR6FGlvc5m18DXJt8reJKAOVMZI/WnaJ4N8SX+m6o+na9LbQQSPHFBub94R1Gc8DPFdVqN/4pltPJFlZWSSfKXVmdlHqBwM11nhvT0sNFFrHkBVHJ6k9yfc1UZX3LdKyPN/D3hlb3QxPaOYpCAJCRltw+9n3zUV34BimkLNdhDtwRsyWPrmu0ubPUNHvZrjRFhkjmffNBPkAMepUj17inrqPiOdgF0W1Y5xu87A/lU6p6FuN1qjiYPh5NbQGWO4nCDJO4YH4DrXl+u27Q+ILyNAX8thuOO9fQOpnxdJAyCOwtVYY3DdIw/kK4m88N29jZeUxMtxcS7pZH+9IxOSTUOp7N3eopUXUStoi5b2rLoltO5IDxqdx7H3pbfLSCUcNnDD3rdgthHo8cTjKhApB6f5zVTTrQefLH2zwD24rjaWrIejsdN4QBjmR1yATjFd/nK8iuP0K0eFYyg+6p3fU10aXMoyphdiOScjArvw6tA4qz94mFrEudoKg84DGioWvXU4NrMfoKK6LmR4XqQWDWIZexBXP411un6ksNupUDp1rkNbZHCMOHU7vrWzpVs93bgRsuCMjNcEW0evh5pR1NK/1K5uYnlhXd5YyAO9V9L8aXEMBiuIHWUDHI4NZ934mtdDc2l5Zys7HaGUfKaktdetWYltMn2nvszitUmdSg57I2bTWtRuA6TWibZG4ZXyQPcdqutqNxpUwDsTCejEdPrWYniaO36aRdED/ZAqD/hMbTW3e1gsLrzR1DJwPxpWaHKElujorjWFuIt27PtXLuw1DX4IyMpGC5Hv2qeGzuQj7l2KDjBPSqemfJrkjEjrt/Kuaq2yJNKGh0cKxiOSCU+p59DWRpztFflJD8wk2n39DV/WGCMGQ43DisGS5ZVSc/eB5/Cp6HD1O8s9SETCN12x7sBq3tqspxt2Hno3NeeXNxJ9njmjf5GHboa63QtaNxapBOwWRU3Bs/eH+NdWHqfZZzVodTXQW6LtYsrDqAWxRViNmZAS6En0FFdZgfPesu4fzYudhHHrWj4W1VQyAnABwR7dqfBpctzBPNKNsOOprjZr6TStT3wA+SOCD3rz4p3O+nKx6J4hs1uQGKKQDkZHFRaZe3FpDsBlU5HUBx+GelXtA1C21zTh+8Bbb61sQaIjsCHGPpXTGbWlj0aNZKPLKKaMa7e51FtoDKG+8zkfoBWppWnpZRCNBlmOSSOtbMOjxRYy1QXd1BaK6oV3kY60qkm1bYJ1+ZcsUkjL1K6KK0a4wO9cLpesI/iO4ti3X5lPvWh4m12OCF0iO6VuwrzXT55V1eWYN+8HzfrWChdM56k7Kx7FdXBms+OSvQ1lo3nQjtkkEVX0rUFnhwTwRU1ocSyI38LcfiKyjqjmluM07UZYRNbTgtFkkA+nt9K6TQruNri3IbeA/A+vFc9Ika3GSMBu47Gr+mWy2gjbk7XBBH93vVQfvCmro9ai2+WCqgA9gKKpWt0TbqaK9FSVjhaOBuLdl02K0Rsswy2f51574o09LdwuPnPZfeu81jUPsciFBlnUEH2rlLqM6vqanO5U6/WuJvU6oq2pyGj67c6DdF4skA/MnqK9K07x3b3MSvv2HGSDwa8+1nQZbHzZ5Rt3EkD+lO8ORxX42OAD6VfPpdHXSbPTZ/GpeIrF1x94msC41O6vSVh3c9XNOg0NYhuxuHarws9gwqgD2FZudzotc5W/sxHA7uxeTHU+tYWiaa9zPcz7flRcmus1yPZEVzz/AEqfwtZh9IlZV5kbAoi2kc9ZGPpzvbzBOcEDNaWo34sLdZz95yBj1q3a6T59zLMeIk6sfQVzniKT7Vch48+SqbUHtms433Zm9Wa4vhdwBkbJxkGus8OXS7VadQy4wQa8w02WSGMcng49iK7XTLhvKREzzz9KSfLLyFKLseh2FwxtQFBIUkZ+lFRaSFTT0C3ae+VGc0V02kzm0OTghTU7cJc5PlSqqkcHB7VHb28VrqM6woFEWQooorn6I26s5rxYzSX8ascheQPqOaxLGJbaRWiypZwaKKf2WbU90ej6a5kt13c8Zq2wGCcUUVJ3LY4zxKTwOzNg1veFkVbKFAPl4NFFVHY465B4kkeB1tIjtiMWSB3rAlt45LcKw4HSiis5PYiHUZDaxLbodvVia6fQIkLScdEOP0oorOW4yGW5lhmdEcgBqKKK603Y52f/2Q==');

        // Create a demo staffgroup team observers
        $_StaffGroupObject_Observers = SWIFT_StaffGroup::Insert($this->Language->Get('sample_staffteamtitle'), false);

        // Settings permissions for user as an Observer
        // (Observer does not have permission to update or delete ticket posts)
        $_permissionContainer = array('staff_tcanviewtickets' => 1, 'staff_tcaninsertticket' => 0, 'staff_tcanupateticket' => 0, 'staff_tcandeleteticket' => 0, 'staff_tcanviewbilling' => 1, 'staff_tcaninsertbilling' => 0,
            'staff_tcanupatebilling' => 0, 'staff_tcandeletebilling' => 0, 'staff_tcanfollowup' => 0, 'staff_tcandeletefollowup' => 0, 'staff_tcanviewticketnotes' => 1, 'staff_tcaninsertticketnote' => 0,
            'staff_tcanupateticketnote' => 0, 'staff_tcandeleteticketnote' => 0, 'staff_tcanview_views' => 1, 'staff_tcaninsertview' => 0, 'staff_tcanupateview' => 0, 'staff_tcandeleteview' => 0,
            'staff_tcanviewfilters' => 1, 'staff_tcaninsertfilter' => 0, 'staff_tcanupdatefilter' => 0, 'staff_tcandeletefilters' => 0, 'staff_tcanviewmacro' => 1, 'staff_tcaninsertmacro' => 0,
            'staff_tcanupdatemacro' => 0, 'staff_tcandeletemacro' => 0, 'staff_tcanviewrecurrence' => 1, 'staff_tcaninsertrecurrence' => 0, 'staff_tcanupdaterecurrence' => 0,
            'staff_tcandeleterecurrence' => 0, 'staff_tcanforward' => 0, 'staff_tcanreply' => 0, 'staff_tcanrelease' => 0, 'staff_tcanworkflow' => 0, 'staff_tcanviewauditlog' => 1,
            'staff_tcansaveasdraft' => 0, 'staff_tcanmarkasspam' => 0, 'staff_tcantrashticket' => 0, 'staff_tcansearch' => 0, 'staff_lscanviewchat' => 1, 'staff_lscanupdatechat' => 0,
            'staff_lscandeletechat' => 0, 'admin_lscaninsertchatnote' => 0, 'staff_lscanupdatechatnote' => 0, 'staff_lscandeletechatnote' => 0, 'staff_lscanviewmessages' => 1,
            'staff_lscanupdatemessages' => 0, 'staff_lscandeletemessages' => 0, 'staff_lscanviewcalls' => 1, 'staff_lscandeletecalls' => 0, 'admin_lscanviewcanned' => 1,
            'admin_lscaninsertcanned' => 0, 'admin_lscanupdatecanned' => 0, 'admin_lscandeletecanned' => 0, 'winapp_lrcaninsertban' => 0, 'ls_canobserve' => 1, 'staff_rrestrict' => 1,
            'staff_rcanviewcategories' => 1, 'staff_rcaninsertcategory' => 0, 'staff_rcanupdatecategory' => 0, 'staff_rcandeletecategory' => 0, 'staff_rcanviewreports' => 1,
            'staff_rcaninsertreport' => 0, 'staff_rcanupdatereport' => 0, 'staff_rcandeletereport' => 0, 'staff_rcanviewschedules' => 1, 'staff_rcaninsertschedule' => 0,
            'staff_rcanupdateschedule' => 0, 'staff_rcandeleteschedule' => 0, 'staff_trcanviewcategories' => 1, 'staff_trcaninsertcategory' => 0, 'staff_trcanupdatecategory' => 0,
            'staff_trcandeletecategory' => 0, 'staff_trcanviewsteps' => 1, 'staff_trcanmanagesteps' => 0, 'staff_trcaninsertstep' => 0, 'staff_trcanupdatestep' => 0, 'staff_trcandeletestep' => 0,
            'staff_trcaninsertpublishedsteps' => 0, 'staff_newscanpublicinsert' => 0, 'staff_nwcanviewitems' => 1, 'staff_nwcanmanageitems' => 0, 'staff_nwcaninsertitem' => 0, 'staff_nwcanupdateitem' => 0,
            'staff_nwcandeleteitem' => 0, 'staff_nwcanviewsubscribers' => 1, 'staff_nwcaninsertsubscriber' => 0, 'staff_nwcanupdatesubscriber' => 0, 'staff_nwcandeletesubscriber' => 0,
            'staff_nwcanviewcategories' => 1, 'staff_nwcaninsertcategory' => 0, 'staff_nwcanupdatecategory' => 0, 'staff_nwcandeletecategory' => 0, 'staff_kbcanviewarticles' => 1,
            'staff_kbcanmanagearticles' => 0, 'staff_kbcaninsertarticle' => 0, 'staff_kbcanupdatearticle' => 0, 'staff_kbcandeletearticle' => 0, 'staff_kbcaninsertpublishedarticles' => 0,
            'staff_kbcanviewcategories' => 1, 'staff_kbcaninsertcategory' => 0, 'staff_kbcanupdatecategory' => 0, 'staff_kbcandeletecategory' => 0, 'staff_profile' => 0, 'staff_changepassword' => 0,
            'staff_loginasuser' => 0, 'staff_canviewusers' => 1, 'staff_caninsertuser' => 0, 'staff_canupateuser' => 0, 'staff_candeleteuser' => 0, 'staff_canviewuserorganizations' => 1,
            'staff_caninsertuserorganization' => 0, 'staff_canupateuserorganization' => 0, 'staff_candeleteuserorganization' => 0, 'staff_canviewusernotes' => 1, 'staff_caninsertusernote' => 0,
            'staff_canupateusernote' => 0, 'staff_candeleteusernote' => 0, 'staff_canviewratings' => 1, 'staff_canupdateratings' => 0, 'staff_canupdatetags' => 0, 'staff_canviewnotifications' => 1,
            'staff_caninsertnotification' => 0, 'staff_canupatenotification' => 0, 'staff_candeletenotification' => 0, 'staff_canviewcomments' => 1, 'staff_canupatecomments' => 0,
            'staff_candeletecomments' => 0, 'admin_canviewstaff' => 1, 'admin_caninsertstaff' => 1, 'admin_caneditstaff' => 1, 'admin_candeletestaff' => 1, 'admin_canviewstaffgroup' => 1,
            'admin_caninsertstaffgroup' => 1, 'admin_caneditstaffgroup' => 1, 'admin_candeletestaffgroup' => 1, 'admin_canviewdepartments' => 1, 'admin_caninsertdepartment' => 1,
            'admin_caneditdepartment' => 1, 'admin_candeletedepartment' => 1, 'admin_canviewusergroups' => 1, 'admin_caninsertusergroup' => 1, 'admin_canupdateusergroup' => 1,
            'admin_candeleteusergroups' => 1, 'admin_canupdatesettings' => 1, 'admin_canmanagerestapi' => 1, 'admin_canmanagetaggenerator' => 1, 'admin_tmpcanviewgroups' => 1,
            'admin_tmpcaninsertgroup' => 1, 'admin_tmpcanupdategroup' => 1, 'admin_tmpcandeletegroup' => 1, 'admin_tmpcanviewtemplates' => 1, 'admin_tmpcaninserttemplate' => 1,
            'admin_tmpcanupdatetemplate' => 1, 'admin_tmpcanrestoretemplates' => 1, 'admin_tmpcanrundiagnostics' => 1, 'admin_tmpcanrunimportexport' => 1, 'admin_tmpcansearchtemplates' => 1,
            'admin_tmpcanpersonalize' => 1, 'admin_canviewlanguages' => 1, 'admin_caninsertlanguage' => 1, 'admin_canupdatelanguage' => 1, 'admin_candeletelanguage' => 1, 'admin_canviewphrases' => 1,
            'admin_caninsertphrase' => 1, 'admin_canupdatephrase' => 1, 'admin_candeletephrase' => 1, 'admin_canrebuildgeoip' => 1, 'admin_canviewcfgroups' => 1, 'admin_caninsertcfgroup' => 1,
            'admin_canupdatecfgroup' => 1, 'admin_candeletecfgroup' => 1, 'admin_canviewcfields' => 1, 'admin_caninsertcustomfield' => 1, 'admin_canupdatecustomfield' => 1,
            'admin_candeletecustomfield' => 1, 'admin_canviewratings' => 1, 'admin_caninsertrating' => 1, 'admin_canupdaterating' => 1, 'admin_candeleterating' => 1,
            'admin_canviewscheduledtasks' => 1, 'admin_canupdatescheduledtasks' => 1, 'admin_candeletescheduledtasklogs' => 1, 'admin_canviewwidgets' => 1, 'admin_caninsertwidget' => 1,
            'admin_canupdatewidget' => 1, 'admin_candeletewidgets' => 1, 'admin_canrestorelanguage' => 1, 'admin_canimportphrases' => 1, 'admin_canexportphrases' => 1,
            'admin_canrestorephrases' => 1, 'admin_canrunlanguagediagnostics' => 1, 'admin_canviewloginlog' => 1, 'admin_canviewactivitylog' => 1, 'admin_canviewerrorlog' => 1,
            'admin_canrundiagnostics' => 1, 'admin_canviewdatabase' => 1, 'admin_canrunimport' => 1, 'admin_tcanpurgeattachments' => 1, 'admin_tcanrunmoveattachments' => 1,
            'admin_canmanageapps' => 1, 'admin_lrcanviewskills' => 1, 'admin_lrcaninsertskill' => 1, 'admin_lrcanupdateskill' => 1, 'admin_lrcandeleteskill' => 1, 'admin_lrcanviewrules' => 1,
            'admin_lrcaninsertrule' => 1, 'admin_lrcanupdaterule' => 1, 'admin_lrcandeleterule' => 1, 'admin_lrcanviewvisitorgroups' => 1, 'admin_lrcaninsertvisitorgroup' => 1,
            'admin_lrcanupdatevisitorgroup' => 1, 'admin_lrcandeletevisitorgroup' => 1, 'admin_lrcanviewbans' => 1, 'admin_lrcaninsertban' => 1, 'admin_lrcanupdateban' => 1,
            'admin_lrcandeleteban' => 1, 'admin_lrcanviewrouting' => 1, 'admin_lrcanupdaterouting' => 1, 'admin_lrcanviewonlinestaff' => 1, 'admin_lrcandisconnectstaff' => 1,
            'admin_mpcanviewqueues' => 1, 'admin_mpcaninsertqueue' => 1, 'admin_mpcanupdatequeue' => 1, 'admin_mpcandeletequeue' => 1, 'admin_mpcanviewparserlogs' => 1,
            'admin_mpcandeleteparserlogs' => 1, 'admin_mpcanviewrules' => 1, 'admin_mpcaninsertrule' => 1, 'admin_mpcanupdaterule' => 1, 'admin_mpcandeleterule' => 1,
            'admin_mpcanviewbreaklines' => 1, 'admin_mpcaninsertbreakline' => 1, 'admin_mpcanupdatebreakline' => 1, 'admin_mpcandeletebreaklines' => 1, 'admin_mpcanviewbans' => 1,
            'admin_mpcaninsertban' => 1, 'admin_mpcanupdateban' => 1, 'admin_mpcandeletebans' => 1, 'admin_canviewcatchall' => 1, 'admin_mpcaninsertcatchall' => 1, 'admin_mpcanupdatecatchall' => 1,
            'admin_candeletecatchall' => 1, 'admin_mpcanviewloopblockages' => 1, 'admin_mpcandeleteloopblockages' => 1, 'admin_mpcanviewlooprules' => 1, 'admin_mpcaninsertlooprule' => 1,
            'admin_mpcanupdatelooprule' => 1, 'admin_mpcandeletelooprule' => 1, 'admin_tcanviewworkflows' => 1, 'admin_tcaninsertworkflow' => 1, 'admin_tcanupdateworkflow' => 1,
            'admin_tcandeleteworkflows' => 1, 'admin_tcanviewstatus' => 1, 'admin_tcaninsertstatus' => 1, 'admin_tcanupdatestatus' => 1, 'admin_tcandeletestatus' => 1, 'admin_tcanviewtypes' => 1,
            'admin_tcaninserttype' => 1, 'admin_tcanupdatetype' => 1, 'admin_tcandeletetypes' => 1, 'admin_tcanviewpriorities' => 1, 'admin_tcaninsertpriority' => 1, 'admin_tcanupdatepriority' => 1,
            'admin_tcandeletepriority' => 1, 'admin_tcanviewlinks' => 1, 'admin_tcaninsertlink' => 1, 'admin_tcanupdatelink' => 1, 'admin_tcandeletelinks' => 1, 'admin_tcanviewfiletypes' => 1,
            'admin_tcaninsertfiletype' => 1, 'admin_tcanupdatefiletype' => 1, 'admin_tcandeletefiletypes' => 1, 'admin_tcanviewbayescategories' => 1, 'admin_tcaninsertbayescategory' => 1,
            'admin_tcanupdatebayescategory' => 1, 'admin_tcandeletebayescategories' => 1, 'admin_tcanviewautoclose' => 1, 'admin_tcaninsertautoclose' => 1, 'admin_tcanupdateautoclose' => 1,
            'admin_tcandeleteautoclose' => 1, 'admin_tcanviewslaplans' => 1, 'admin_tcaninsertslaplan' => 1, 'admin_tcanupdateslaplan' => 1, 'admin_tcandeleteslaplans' => 1, 'admin_tcanviewslaschedules' => 1,
            'admin_tcaninsertslaschedules' => 1, 'admin_tcanupdateslaschedules' => 1, 'admin_tcandeleteslaschedules' => 1, 'admin_tcanviewslaholidays' => 1, 'admin_tcaninsertslaholidays' => 1,
            'admin_tcanupdateslaholidays' => 1, 'admin_tcandeleteslaholidays' => 1, 'admin_tcanviewescalations' => 1, 'admin_tcaninsertescalations' => 1, 'admin_tcanupdateescalations' => 1,
            'admin_tcandeleteescalations' => 1, 'admin_tcanrunmaintenance' => 1, 'admin_tcanrunbayesdiagnostics' => 1, 'admin_tcanimpexslaholidays' => 1, 'admin_nwcanupdatesubscriber' => 1);

        $_SWIFT_StaffGroupSettingsObject = new SWIFT_StaffGroupSettings($_StaffGroupObject_Observers->GetStaffGroupID());
        $_SWIFT_StaffGroupSettingsObject->ReprocessGroupSettings($_permissionContainer);

        return true;
    }

    /**
     * Uninstalls the app
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Uninstall()
    {
        parent::Uninstall();

        return true;
    }

    /**
     * Upgrades the app to the latest version
     *
     * @author Varun Shoor
     * @param bool $_isForced (OPTIONAL)
     * @param string $_forceVersion
     * @return array|bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \ReflectionException
     */
    public function Upgrade($_isForced = false, $_forceVersion = '')
    {
        self::UpgradeEngine();
        return parent::Upgrade($_isForced);
    }

    /**
     * Upgrade from 4.01.191
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_01_191()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        // Cleanup old templatedata entries: http://dev.opencart.com.vn/browse/SWIFT-1132
        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "templatedata WHERE templateid <> ALL (SELECT templateid from " . TABLE_PREFIX . "templates)");

        return true;
    }

    /**
     * Upgrade from 4.01.176
     *
     * @author Jamie Edwards
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_01_176()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
            return false;
        }

        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "searchindex ADD FULLTEXT (ft)");

        return true;
    }

    /**
     * Upgrade from 4.01.326
     *
     * @author Parminder Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_01_326()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
            return false;
        }

        // SWIFT-1636: All outgoing mail is X-priority 1 after 4.01.325 upgrade
        $_newValue = 3;    // Normal
        if ($this->Settings->Get('cpu_maildefaultpriority') == 'High') {
            $_newValue = 1;
        } else if ($this->Settings->Get('cpu_maildefaultpriority') == 'Low') {
            $_newValue = 5;
        }

        $this->Settings->UpdateKey('settings', 'cpu_maildefaultpriority', $_newValue);

        return true;
    }

    /**
     * Upgrade from 4.01.342
     * Fixes the non existant signatures for staff
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_01_342()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
            return false;
        }

        $_staffContainer = $_staffIDList = array();

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff");
        while ($this->Database->NextRecord()) {
            $_staffContainer[$this->Database->Record['staffid']] = $this->Database->Record;

            $_staffIDList[] = $this->Database->Record['staffid'];
        }

        $_signatureStaffIDList = array();
        $this->Database->Query("SELECT staffid FROM " . TABLE_PREFIX . "signatures");
        while ($this->Database->NextRecord()) {
            $_signatureStaffIDList[] = $this->Database->Record['staffid'];
        }

        foreach ($_staffContainer as $_staffID => $_staff) {
            if (in_array($_staffID, $_signatureStaffIDList)) {
                continue;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'signatures', array('dateline' => DATENOW, 'staffid' => (int)($_staffID), 'signature' => ''), 'INSERT');
        }

        SWIFT_Staff::RebuildCache();

        return true;
    }

    /**
     * Upgrade from 4.40.1148
     * Move Ticket rating setting to global rating setting
     *
     * @author Parminder Singh
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_40_1149()
    {

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
            return false;
        }

        $_staffGroupSettingsContainer = array();
        $this->Database->Query("SELECT staffgroupid, value FROM " . TABLE_PREFIX . "staffgroupsettings WHERE name LIKE 'staff_tcanratings'");
        while ($this->Database->NextRecord()) {
            $_staffGroupSettingsContainer[$this->Database->Record['staffgroupid']] = $this->Database->Record;
        }

        if (!count($_staffGroupSettingsContainer)) {
            return true;
        }

        $_staffGroupIDList = array();
        $this->Database->Query("SELECT staffgroupid FROM " . TABLE_PREFIX . "staffgroup");
        while ($this->Database->NextRecord()) {
            $_staffGroupIDList[] = $this->Database->Record['staffgroupid'];
        }

        foreach ($_staffGroupSettingsContainer as $_staffGroupID => $_staffGroupSettings) {
            if (!in_array($_staffGroupID, $_staffGroupIDList)) {
                continue;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'staffgroupsettings', array('staffgroupid' => $_staffGroupSettings['staffgroupid'], 'name' => 'staff_canviewratings', 'value' => $_staffGroupSettings['value']), 'INSERT');
            $this->Database->AutoExecute(TABLE_PREFIX . 'staffgroupsettings', array('staffgroupid' => $_staffGroupSettings['staffgroupid'], 'name' => 'staff_canupdateratings', 'value' => $_staffGroupSettings['value']), 'INSERT');
        }

        // Now delete the entry
        $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "staffgroupsettings WHERE name LIKE 'staff_tcanratings'");

        SWIFT_StaffGroup::RebuildCache();

        return true;
    }

    /**
     * Upgrade from 4.64.1.5058
     *
     * @author Nidhi Gupta
     * @return bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_65_0_5820()
    {

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Add Search Engine Type in searchindex table
        $_searchEngineTypes = array(SWIFT_SearchEngine::TYPE_CHAT, SWIFT_SearchEngine::TYPE_DOWNLOADS, SWIFT_SearchEngine::TYPE_KNOWLEDGEBASE,
            SWIFT_SearchEngine::TYPE_NEWS, SWIFT_SearchEngine::TYPE_TICKET, SWIFT_SearchEngine::TYPE_TROUBLESHOOTER);

        // Add SEARCH TYPE Identifier
        foreach ($_searchEngineTypes as $_searchEngineType) {
            $this->Database->Query("UPDATE " . TABLE_PREFIX . "searchindex SET ft = CONCAT(ft, ' __SWIFTSEARCHENGINETYPE" . $_searchEngineType . "') WHERE type = " . $_searchEngineType);
        }

        return true;
    }

    /**
     * Upgrade for 4.60
     *
     * @author Utsav Handa
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_60_0000()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * Update Widgets DefaultIcon for Troubleshooter & Register
         * which don't have custom icons
         */
        $this->Database->QueryFetch("UPDATE " . TABLE_PREFIX . "widgets SET defaulticon='{\$themepath}icon_widget_register.png' WHERE widgetname='register' AND defaultsmallicon != '' ");
        $this->Database->QueryFetch("UPDATE " . TABLE_PREFIX . "widgets SET defaulticon='{\$themepath}icon_widget_troubleshooter.png' WHERE widgetname='troubleshooter' AND defaultsmallicon != '' ");

        return true;
    }

    /**
     * @author Nidhi Gupta <nidhi.gupta@opencart.com.vn>, Ravi Sharma <ravi.sharma@opencart.com.vn>
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Upgrade_4_70_0000()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_appName = $this->GetAppName();
        $_SWIFT_AppObject = SWIFT_App::Get($_appName);

        foreach (glob($_SWIFT_AppObject->GetDirectory() . DIRECTORY_SEPARATOR . SWIFT_CONFIG_DIRECTORY . DIRECTORY_SEPARATOR . 'language-*.xml') as $_languageFile) {
            $_SWIFT_LanguageManagerObject = new SWIFT_LanguageManager();
            $_SWIFT_LanguageManagerObject->Import($_languageFile, false, false, false, false);
        }

        // Renaming Viet Nam to Vietnam
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "userorganizations SET country='Vietnam' WHERE country='Viet Nam'");

        return true;
    }

    /**
     * Upgrade database engine for searchindex
     *
     * @author Nidhi Gupta <nidhi.gupta@opencart.com.vn>
     *
     * @return bool
     * @throws SWIFT_Exception
     */
    public function UpgradeEngine()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $_version = $this->Database->QueryFetch("SELECT SUBSTRING_INDEX(version(),'-',2) as version");
        $_engine = $this->Database->QueryFetch("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES where TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '" . TABLE_PREFIX . "searchindex'");

        if ($_version['version'] >= '5.6' && strtolower($_engine['ENGINE']) == 'myisam') {
            return $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "searchindex ENGINE = InnoDB");
        }

        return true;
    }

    /**
     * Upgrade from 4.73.3
     *
     * @author Mansi Wason <mansi.wason@opencart.com.vn>
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Upgrade_4_74_0000()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "sessions MODIFY sessionid VARCHAR(255)");

        return true;
    }

    /**
     * Upgrade from 4.74.0
     *
     * @author Mansi Wason <mansi.wason@opencart.com.vn>
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Upgrade_4_74_0001()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "chatobjects MODIFY chatsessionid VARCHAR(255)");
        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "chatobjects MODIFY visitorsessionid VARCHAR(255)");
        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "visitordata MODIFY visitorsessionid VARCHAR(255)");
        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "visitorfootprints MODIFY sessionid VARCHAR(255)");
        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "visitorpulls MODIFY visitorsessionid VARCHAR(255)");
        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "visitorpulls2 MODIFY visitorsessionid VARCHAR(255)");
        $this->Database->Query("ALTER TABLE " . TABLE_PREFIX . "searchstores MODIFY sessionid VARCHAR(255)");

        return true;
    }

    /**
     * Upgrade for 4.79
     *
     * @author Mansi Wason <mansi.wason@opencart.com.vn>
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Upgrade_4_79_0000()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->QueryFetch("UPDATE " . TABLE_PREFIX . "settings SET data='15' WHERE vkey='pr_procno'");

        return true;
    }

    /**
     * Upgrade for 4.80.1
     *
     * @author Ankit Saini
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_90_0000()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        /**
         * Update Widgets DefaultIcon default widgets
         * which don't have custom icons
         */
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "widgets SET defaulticon='{\$themepath}icon_widget_register.svg' WHERE widgetname='register' AND defaultsmallicon != '' ");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "widgets SET defaulticon='{\$themepath}icon_widget_knowledgebase.svg' WHERE widgetname='knowledgebase' AND defaultsmallicon != '' ");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "widgets SET defaulticon='{\$themepath}icon_widget_news.svg' WHERE widgetname='news' AND defaultsmallicon != '' ");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "widgets SET defaulticon='{\$themepath}icon_widget_troubleshooter.svg' WHERE widgetname='troubleshooter' AND defaultsmallicon != '' ");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "widgets SET defaulticon='{\$themepath}icon_widget_viewticket.svg' WHERE widgetname='viewtickets' AND defaultsmallicon != '' ");
        $this->Database->Query("UPDATE " . TABLE_PREFIX . "widgets SET defaulticon='{\$themepath}icon_widget_submitticket.svg' WHERE widgetname='submitticket' AND defaultsmallicon != '' ");

        return true;
    }

    /**
     * Upgrade for 4.91
     *
     * @author Mansi Wason <mansi.wason@opencart.com.vn>
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Upgrade_4_91_0000()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->QueryFetch("UPDATE " . TABLE_PREFIX . "settings SET data='1' WHERE vkey='t_ccaptcha'");

        return true;
    }

    /**
     * Upgrade from 4.91
     *
     * @author Arotimi Busayo
     * @return bool "true" on Success,
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Upgrade_4_92_0()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        //Get Default Master Language_ID (en-us)
        $_languageID = SWIFT_Language::GetMasterLanguageIDList()[0];
        SWIFT_PolicyLink::Create($_languageID, 'https://www.opencart.com.vn/about/privacy', 1);
        return true;
    }

    /**
     * Upgrade from 4.92.6
     *
     * @author Werner Garcia
     * @return bool
     * @throws SWIFT_Exception
     */
    public function Upgrade_4_92_7()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->Database->Query('INSERT IGNORE INTO ' . TABLE_PREFIX . 'userorganizationlinks (userid, userorganizationid)
SELECT userid, userorganizationid from ' . TABLE_PREFIX . 'users where userid <> 0 and userorganizationid <> 0');

        return true;
    }

    /**
     * Upgrade from 4.93.06
     *
     * @author Werner Garcia
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception if error in database Query (catched internally)
     */
    public function Upgrade_4_93_08()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_storeFilenames = [];
        $this->Database->Query("SELECT attachmentid, storefilename FROM " . TABLE_PREFIX . "attachments WHERE sha1='' OR sha1 IS NULL");
        while ($this->Database->NextRecord()) {
            $_storeFilenames[$this->Database->Record['attachmentid']] = $this->Database->Record['storefilename'];
        }

        foreach ($_storeFilenames as $_attachmentID => $_storefilename) {
            $_filename = SWIFT::Get('swiftpath') . SWIFT_BASEDIRECTORY . '/' . SWIFT_FILESDIRECTORY . '/' . $_storefilename;
            if (file_exists($_filename)) {
                $this->Database->Query('UPDATE ' . TABLE_PREFIX . "attachments SET sha1='" . sha1_file($_filename) . "' WHERE attachmentid=" . $_attachmentID);
            }
        }

        return true;
    }
}
