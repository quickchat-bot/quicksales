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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace LiveChat;

use Base\Models\Department\SWIFT_Department;
use Base\Models\Rating\SWIFT_Rating;
use Base\Models\Staff\SWIFT_Staff;
use Base\Models\Staff\SWIFT_StaffAssign;
use Base\Models\Staff\SWIFT_StaffGroupAssign;
use Base\Models\Tag\SWIFT_Tag;
use Base\Models\Tag\SWIFT_TagLink;
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Canned\SWIFT_CannedCategory;
use LiveChat\Models\Canned\SWIFT_CannedResponse;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Models\Skill\SWIFT_ChatSkill;
use SWIFT_Cron;
use LiveChat\Models\Message\SWIFT_Message;
use LiveChat\Models\Message\SWIFT_MessageManager;
use SWIFT_SetupDatabase;
use SWIFT_SetupDatabaseIndex;
use SWIFT_SetupDatabaseSQL;
use SWIFT_SetupDatabaseTable;
use LiveChat\Models\Group\SWIFT_VisitorGroup;
use LiveChat\Models\Rule\SWIFT_VisitorRule;

/**
 * The Main Installer
 *
 * @author Varun Shoor
 */
class SWIFT_SetupDatabase_livechat extends SWIFT_SetupDatabase
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
        parent::__construct(APP_LIVECHAT);
    }

    /**
     * Loads the table into the container
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadTables()
    {
        // ======= VISITORPULLS =======
        $this->AddTable('visitorpulls', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "visitorpulls", "visitorsessionid C(255) PRIMARY NOTNULL,
                                                                staffid I DEFAULT '0' NOTNULL,
                                                                dateline I DEFAULT '0' NOTNULL"));
        $this->AddIndex('visitorpulls', new SWIFT_SetupDatabaseIndex("visitorpulls1", TABLE_PREFIX . "visitorpulls", "staffid"));
        // ======= VISITORPULLS =======
        $this->AddTable('visitorpulls2', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "visitorpulls2", "visitorsessionid C(255) PRIMARY NOTNULL,
                                                                staffid I DEFAULT '0' NOTNULL,
                                                                dateline I DEFAULT '0' NOTNULL"));
        $this->AddIndex('visitorpulls2', new SWIFT_SetupDatabaseIndex("visitorpulls21", TABLE_PREFIX . "visitorpulls2", "staffid"));

        // ======= CHATDATA =======
        $this->AddTable('chatdata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "chatdata", "chatdataid I PRIMARY AUTO NOTNULL,
                                                                chatobjectid I DEFAULT '0' NOTNULL,
                                                                contents X2"));
        $this->AddIndex('chatdata', new SWIFT_SetupDatabaseIndex("cobjid", TABLE_PREFIX . "chatdata", "chatobjectid"));

        // ======= MESSAGEQUEUE =======
        $this->AddTable('messagequeue', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "messagequeue", "messagequeueid I PRIMARY AUTO NOTNULL,
                                                                chatobjectid I DEFAULT '0' NOTNULL,
                                                                chatchildid C(32) DEFAULT '' NOTNULL,
                                                                staffid I DEFAULT '0' NOTNULL,
                                                                dateline I DEFAULT '0' NOTNULL,
                                                                name C(150) DEFAULT '' NOTNULL,
                                                                contents X2,
                                                                msgtype C(15) DEFAULT '' NOTNULL,
                                                                guid C(150) DEFAULT '' NOTNULL,
                                                                submittype I2 DEFAULT '0' NOTNULL"));
        $this->AddIndex('messagequeue', new SWIFT_SetupDatabaseIndex("messagequeue1", TABLE_PREFIX . "messagequeue", "chatobjectid, chatchildid"));
        $this->AddIndex('messagequeue', new SWIFT_SetupDatabaseIndex("messagequeue2", TABLE_PREFIX . "messagequeue", "dateline"));
        $this->AddIndex('messagequeue', new SWIFT_SetupDatabaseIndex("messagequeue3", TABLE_PREFIX . "messagequeue", "chatobjectid, staffid"));
        $this->AddIndex('messagequeue', new SWIFT_SetupDatabaseIndex("messagequeue4", TABLE_PREFIX . "messagequeue", "guid"));

        // ======= MESSAGEDATA =======
        $this->AddTable('messagedata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "messagedata", "messagedataid I PRIMARY AUTO NOTNULL,
                                                                messageid I DEFAULT '0' NOTNULL,
                                                                contenttype I2 DEFAULT '0' NOTNULL,
                                                                contents X2"));
        $this->AddIndex('messagedata', new SWIFT_SetupDatabaseIndex("messagedata1", TABLE_PREFIX . "messagedata", "messageid, contenttype"));
        $this->AddIndex('messagedata', new SWIFT_SetupDatabaseIndex("messagedata2", TABLE_PREFIX . "messagedata", "contenttype, messageid"));

        // ======= VISITORNOTEDATA =======
        $this->AddTable('visitornotedata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "visitornotedata", "visitornotedataid I PRIMARY AUTO NOTNULL,
                                                                visitornoteid I DEFAULT '0' NOTNULL,
                                                                contents X2"));
        $this->AddIndex('visitornotedata', new SWIFT_SetupDatabaseIndex("visitornotedata", TABLE_PREFIX . "visitornotedata", "visitornoteid"));

        // ======= CANNEDRESPONSEDATA =======
        $this->AddTable('cannedresponsedata', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "cannedresponsedata", "cannedresponsedataid I PRIMARY AUTO NOTNULL,
                                                                cannedresponseid I DEFAULT '0' NOTNULL,
                                                                contents X2"));
        $this->AddIndex('cannedresponsedata', new SWIFT_SetupDatabaseIndex("cannedresponsedata1", TABLE_PREFIX . "cannedresponsedata", "cannedresponseid"));

        // ======= NEW IN v4.00.00 =======
        $this->AddTable('chatskilllinks', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "chatskilllinks", "chatskilllinkid I PRIMARY AUTO NOTNULL,
                                                                chatskillid I DEFAULT '0' NOTNULL,
                                                                staffid I DEFAULT '0' NOTNULL"));
        $this->AddIndex('chatskilllinks', new SWIFT_SetupDatabaseIndex("chatskilllinks1", TABLE_PREFIX . "chatskilllinks", "chatskillid"));
        $this->AddIndex('chatskilllinks', new SWIFT_SetupDatabaseIndex("chatskilllinks2", TABLE_PREFIX . "chatskilllinks", "staffid"));

        // ======= VISITORRULECRITERIA =======
        $this->AddTable('visitorrulecriteria', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "visitorrulecriteria", "visitorrulecriteriaid I PRIMARY AUTO NOTNULL,
                                                                visitorruleid I DEFAULT '0' NOTNULL,
                                                                name C(100) DEFAULT '' NOTNULL,
                                                                ruleop I2 DEFAULT '0' NOTNULL,
                                                                rulematch C(255) DEFAULT '' NOTNULL"));
        $this->AddIndex('visitorrulecriteria', new SWIFT_SetupDatabaseIndex("visitorrulecriteria1", TABLE_PREFIX . "visitorrulecriteria", "visitorruleid"));

        // ======= VISITORRULEACTIONS =======
        $this->AddTable('visitorruleactions', new SWIFT_SetupDatabaseTable(TABLE_PREFIX . "visitorruleactions", "visitorruleactionid I PRIMARY AUTO NOTNULL,
                                                                visitorruleid I DEFAULT '0' NOTNULL,
                                                                actiontype C(100) DEFAULT '' NOTNULL,
                                                                actionname C(100) DEFAULT '' NOTNULL,
                                                                actionvalue C(255) DEFAULT '' NOTNULL"));
        $this->AddIndex('visitorruleactions', new SWIFT_SetupDatabaseIndex("visitorruleactions1", TABLE_PREFIX . "visitorruleactions", "visitorruleid"));

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

        if (strtolower(DB_TYPE) == 'mysql' || strtolower(DB_TYPE) == 'mysqli') {
            $this->Query(new SWIFT_SetupDatabaseSQL("ALTER TABLE " . TABLE_PREFIX . "visitorfootprints TYPE = HEAP"));
            $this->Query(new SWIFT_SetupDatabaseSQL("ALTER TABLE " . TABLE_PREFIX . "visitorpulls TYPE = HEAP"));
            $this->Query(new SWIFT_SetupDatabaseSQL("ALTER TABLE " . TABLE_PREFIX . "chatchilds TYPE = HEAP"));
        }

        // ======= DEPARTMENTS =======
        $_Department = SWIFT_Department::Insert($this->Language->Get('coregeneral'), APP_LIVECHAT, SWIFT_PUBLIC, 0, 0, false, array());
        $this->Database->AutoExecute(TABLE_PREFIX . 'templategroups', array('departmentid_livechat' => $_Department->GetDepartmentID()),
            'UPDATE', "1 = 1");

        $_staffIDList = $_staffGroupIDList = array();
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff");
        while ($this->Database->NextRecord()) {
            $_staffIDList[] = $this->Database->Record['staffid'];
        }

        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staffgroup");
        while ($this->Database->NextRecord()) {
            $_staffGroupIDList[] = $this->Database->Record['staffgroupid'];
        }

        SWIFT_StaffAssign::AssignDepartmentList($_Department, $_staffIDList);
        SWIFT_StaffGroupAssign::AssignDepartmentList($_Department, $_staffGroupIDList);

        // ======= CRON =======
        SWIFT_Cron::Create('livechat', 'LiveChat', 'LiveChatMinute', 'Indexing', '0', '10', '0', true);

        /**
         * ---------------------------------------------
         * CUSTOM DATA
         * ---------------------------------------------
         */
        $_SWIFT_VisitorGroupObject = SWIFT_VisitorGroup::Insert($this->Language->Get('lc_searchenginevisitors'), '#d8f1ff');

        $_chatSkillID = SWIFT_ChatSkill::Insert($this->Language->Get('lc_skillsales'), '', $_staffIDList);
        $_chatSkillID = SWIFT_ChatSkill::Insert($this->Language->Get('lc_skillsupport'), '', $_staffIDList);
        $_chatSkillID = SWIFT_ChatSkill::Insert($this->Language->Get('lc_skillspanish'), '', $_staffIDList);

        $this->InstallSampleData($_Department);

        $this->ExecuteQueue();

        return true;
    }

    /**
     * @author Saloni Dhall <saloni.dhall@kayako.com>
     * @author Utsav Handa <utsav.handa@kayako.com>
     *
     * @param SWIFT_Department $_Department
     *
     * @return bool
     */
    public function InstallSampleData($_Department)
    {
        if (!defined('INSTALL_SAMPLE_DATA') || INSTALL_SAMPLE_DATA != true) {
            return false;
        }

        // Create a live chat
        $_staffContainer = SWIFT_Staff::RetrieveOnEmail($_POST['email']);

        $_chatObjectID = $this->Database->AutoExecute(TABLE_PREFIX . 'chatobjects', array('visitorsessionid' => '', 'dateline' => DATENOW - 86400, 'lastpostactivity' => (DATENOW - 86400 + 130), 'waittime' => 32,
            'userpostactivity' => '0', 'staffpostactivity' => '0', 'userid' => 2, 'userfullname' => ReturnNone($this->Language->Get('sample_userfullname')),
            'transfertoid' => '0', 'subject' => ReturnNone($this->Language->Get('sample_livechatsubject')),
            'staffid' => (int)($_staffContainer['staffid']), 'staffname' => ReturnNone($_staffContainer['fullname']),
            'chatstatus' => SWIFT_Chat::CHAT_ENDED, 'transferfromid' => '0', 'useremail' => ReturnNone($this->Language->Get('sample_useremailaddress')),
            'transferstatus' => '0', 'transfertimeline' => '0', 'roundrobintimeline' => DATENOW, 'roundrobinhits' => '0',
            'departmentid' => (int)($_Department->GetDepartmentID()), 'departmenttitle' => ReturnNone($_Department->GetProperty('title')),
            'chattype' => SWIFT_Chat::CHATTYPE_CLIENT, 'ipaddress' => '1.1.1.1.1', 'isproactive' => '0',
            'chatobjectmaskid' => GenerateUniqueMask(false), 'chatskillid' => '0', 'creatorstaffid' => (int)($_staffContainer['staffid']),
            'tgroupid' => '0', 'isphone' => '0', 'phonenumber' => '0'),
            'INSERT');

        // Appending chatdata
        $this->Database->AutoExecute(TABLE_PREFIX . 'chatdata', array('chatobjectid' => (int)($this->Database->Insert_ID()),
            'contents' => sprintf('a:12:{i:0;a:7:{s:4:"type";i:3;s:4:"name";s:0:"";s:7:"message";s:54:"<strong>Your Question:</strong> Checking out live chat";s:6:"base64";b:0;s:10:"submittype";i:3;s:10:"actiontype";s:9:"systemmsg";s:8:"dateline";i:1392880063;}i:1;a:7:{s:4:"type";i:3;s:4:"name";s:0:"";s:7:"message";s:62:"Please wait and one of our operators will be with you shortly.";s:6:"base64";b:0;s:10:"submittype";i:3;s:10:"actiontype";s:9:"systemmsg";s:8:"dateline";i:1392880063;}i:2;a:7:{s:4:"type";i:3;s:4:"name";s:0:"";s:7:"message";s:%d:"You are now chatting with %s - General";s:6:"base64";b:0;s:10:"submittype";i:3;s:10:"actiontype";s:9:"systemmsg";s:8:"dateline";i:1392880083;}i:3;a:7:{s:4:"type";i:2;s:4:"name";s:%d:"%s";s:7:"message";s:36:"SGkgdGhlcmUgLSBob3cgY2FuIEkgaGVscD8=";s:6:"base64";b:1;s:10:"submittype";i:1;s:10:"actiontype";s:7:"message";s:8:"dateline";i:1392880088;}i:4;a:7:{s:4:"type";i:1;s:4:"name";s:11:"Phoebe Todd";s:7:"message";s:44:"SSdtIGp1c3QgdGVzdGluZyBvdXIgbGl2ZSBjaGF0Lg==";s:6:"base64";b:1;s:10:"submittype";i:2;s:10:"actiontype";s:7:"message";s:8:"dateline";i:1392880095;}i:5;a:7:{s:4:"type";i:2;s:4:"name";s:%d:"%s";s:7:"message";s:32:"U3VyZSwgcGxlYXNlIGdvIGFoZWFkLg==";s:6:"base64";b:1;s:10:"submittype";i:1;s:10:"actiontype";s:7:"message";s:8:"dateline";i:1392880103;}i:6;a:7:{s:4:"type";i:2;s:4:"name";s:%d:"%s";s:7:"message";s:216:"RnVubnkgdG8gdGhpbmsgdGhhdCB3ZSdyZSBqdXN0IGZpZ21lbnRzIG9mIHRoaXMgaGVscGRlc2sncyBpbWFnaW5hdGlvbiwgY3JlYXRlZCB0byBleGlzdCBvbmx5IHRlbXBvcmFyaWx5IGFuZCB0byBkZW1vbnN0cmF0ZSB3aGF0IGEgbGl2ZSBjaGF0IHJlY29yZCBsb29rcyBsaWtlLg==";s:6:"base64";b:1;s:10:"submittype";i:1;s:10:"actiontype";s:7:"message";s:8:"dateline";i:1392880109;}i:7;a:7:{s:4:"type";i:1;s:4:"name";s:11:"Phoebe Todd";s:7:"message";s:72:"WWVhaC4uLiBkbyB5b3UgdGhpbmsgYW55b25lIHdpbGwgYWN0dWFsbHkgcmVhZCB0aGlzPw==";s:6:"base64";b:1;s:10:"submittype";i:2;s:10:"actiontype";s:7:"message";s:8:"dateline";i:1392880116;}i:8;a:7:{s:4:"type";i:2;s:4:"name";s:%d:"%s";s:7:"message";s:108:"SSdtIG5vdCBzdXJlLiBJIGtpbmQgb2YgZ2V0IHRoZSBmZWVsaW5nIHRoYXQgc29tZW9uZSBpcyB3YXRjaGluZyB1cyByaWdodCBub3figKY=";s:6:"base64";b:1;s:10:"submittype";i:1;s:10:"actiontype";s:7:"message";s:8:"dateline";i:1392880127;}i:9;a:7:{s:4:"type";i:1;s:4:"name";s:11:"Phoebe Todd";s:7:"message";s:40:"U2FtZSBoZXJlLiBTbyB3ZWlyZCAtIG1lIHRvbyE=";s:6:"base64";b:1;s:10:"submittype";i:2;s:10:"actiontype";s:7:"message";s:8:"dateline";i:1392880132;}i:10;a:7:{s:4:"type";i:2;s:4:"name";s:%d:"%s";s:7:"message";s:112:"T2ssIHdlbGwgdGhhbmsgeW91IGZvciBoZWxwaW5nIG1lIHRlc3QgbGl2ZSBjaGF0LiBIb3BlZnVsbHkgd2UnbGwgbWVldCBhZ2FpbiBzb29uLg==";s:6:"base64";b:1;s:10:"submittype";i:1;s:10:"actiontype";s:7:"message";s:8:"dateline";i:1392880142;}i:11;a:7:{s:4:"type";i:1;s:4:"name";s:11:"Phoebe Todd";s:7:"message";s:52:"U2FtZSBoZXJlLiBJJ2xsIHNlZSB5b3UgaW4gdGhlIGV0aGVyLg==";s:6:"base64";b:1;s:10:"submittype";i:2;s:10:"actiontype";s:7:"message";s:8:"dateline";i:1392880148;}}', strlen('You are now chatting with ' . $_staffContainer['fullname'] . ' - General'), $_staffContainer['fullname'], strlen($_staffContainer['fullname']), $_staffContainer['fullname'], strlen($_staffContainer['fullname']), $_staffContainer['fullname'], strlen($_staffContainer['fullname']), $_staffContainer['fullname'], strlen($_staffContainer['fullname']), $_staffContainer['fullname'], strlen($_staffContainer['fullname']), $_staffContainer['fullname'])),
            'INSERT');

        // Create a demo tag on a live chat
        SWIFT_Tag::Process(SWIFT_TagLink::TYPE_CHAT, $_chatObjectID, array($this->Language->Get('sample_tag')), $_staffContainer['staffid']);

        // Create a call
        $this->Database->AutoExecute(TABLE_PREFIX . 'calls', array('phonenumber' => $this->Language->Get('sample_phonenumber'), 'callguid' => '', 'userid' => 2,
            'userfullname' => $this->Language->Get('sample_userfullname'), 'useremail' => $this->Language->Get('sample_useremailaddress'),
            'staffid' => (int)($_staffContainer['staffid']), 'stafffullname' => $_staffContainer['fullname'], 'chatobjectid' => 0,
            'departmentid' => (int)($_Department->GetDepartmentID()), 'isclicktocall' => 0, 'callstatus' => SWIFT_Call::STATUS_ENDED,
            'calltype' => SWIFT_Call::TYPE_INBOUND, 'dateline' => (DATENOW - 172800), 'duration' => 162, 'enddateline' => (DATENOW - 172800) + 162),
            'INSERT');
        // Add a message
        $_messageID = $this->Database->AutoExecute(TABLE_PREFIX . 'messages', array('messagemaskid' => GenerateUniqueMask(false), 'dateline' => DATENOW, 'replydateline' => '0',
            'fullname' => $this->Language->Get('sample_userfullname'), 'email' => $this->Language->Get('sample_useremailaddress'),
            'subject' => $this->Language->Get('sample_messagesubject'), 'departmentid' => $_Department->GetDepartmentID(),
            'parentmessageid' => '0', 'messagestatus' => SWIFT_Message::STATUS_NEW, 'messagetype' => SWIFT_MessageManager::MESSAGE_CLIENT,
            'staffid' => '0', 'messagerating' => '5', 'chatobjectid' => 0),
            'INSERT');

        // Add a message data
        $this->Database->AutoExecute(TABLE_PREFIX . 'messagedata', array('messageid' => $this->Database->Insert_ID(), 'contenttype' => SWIFT_MessageManager::MESSAGE_CLIENT,
            'contents' => $this->Language->Get('sample_messagecontents')), 'INSERT');

        // Create a demo tag on an offline message
        SWIFT_Tag::Process(SWIFT_TagLink::TYPE_CHATMESSAGE, $_messageID, array($this->Language->Get('sample_tag')), $_staffContainer['staffid']);

        // Create a canned response category
        $_cannedCategoryID = SWIFT_CannedCategory::Create(SWIFT_CannedCategory::TYPE_PUBLIC, $this->Language->Get('sample_cannedresponsecattitle'), '0', $_staffContainer['staffid']);

        // Create a canned response
        SWIFT_CannedResponse::Create($_cannedCategoryID, $this->Language->Get('sample_cannedresponsetitle'), false, false,
            SWIFT_CannedResponse::TYPE_MESSAGE, $this->Language->Get('sample_cannedmessagecontent'), $_staffContainer['staffid']);

        // Create a visitor rule
        SWIFT_VisitorRule::Insert($this->Language->Get('sample_livechatvisitorruletitle'), true, '1', SWIFT_VisitorRule::RULE_MATCHALL, array(array('searchenginefound', '1', '1')),
            array(array('setgroup', '1')), '2');

        return true;
    }


    /**
     * Uninstalls the App
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Uninstall()
    {
        parent::Uninstall();

        SWIFT_Cron::DeleteOnName(array('livechat'));

        SWIFT_Department::DeleteOnApp(array(APP_LIVECHAT));

        SWIFT_Rating::DeleteOnType(array(SWIFT_Rating::TYPE_CHATHISTORY, SWIFT_Rating::TYPE_CHATSURVEY));

        return true;
    }
}

