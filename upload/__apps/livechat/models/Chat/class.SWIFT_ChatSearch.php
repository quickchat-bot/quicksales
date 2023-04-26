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

namespace LiveChat\Models\Chat;

use Base\Library\Rules\SWIFT_Rules;
use Base\Models\Department\SWIFT_Department;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT;

/**
 * The Chat Search Management Lib
 *
 * @author Varun Shoor
 */
class SWIFT_ChatSearch extends SWIFT_Rules
{
    // Criteria
    const CHATSEARCH_USERFULLNAME = 'userfullname';
    const CHATSEARCH_USEREMAIL = 'useremail';
    const CHATSEARCH_SUBJECT = 'subject';
    const CHATSEARCH_CONVERSATIONSQL = 'conversation';
    const CHATSEARCH_CONVERSATIONNGRAM = 'conversationngram';
    const CHATSEARCH_STAFFID = 'staffid';
    const CHATSEARCH_DATE = 'dateline';
    const CHATSEARCH_DATERANGE = 'datelinerange';
    const CHATSEARCH_DEPARTMENT = 'department';
    const CHATSEARCH_WAITTIME = 'waittime';
    const CHATSEARCH_TRANSFERSTATUS = 'transferstatus';
    const CHATSEARCH_TRANSFERFROM = 'transferfrom';
    const CHATSEARCH_TRANSFERTO = 'transferto';
    const CHATSEARCH_TRANSFERDATE = 'transferdate';
    const CHATSEARCH_TRANSFERDATERANGE = 'transferdaterange';
    const CHATSEARCH_CHATTYPE = 'chattype';
    const CHATSEARCH_CHATSTATUS = 'chatstatus';
    const CHATSEARCH_ROUNDROBINHITS = 'roundrobinhits';
    const CHATSEARCH_SKILL = 'chatskill';
    const CHATSEARCH_IPADDRESS = 'ipaddress';
    const CHATSEARCH_ISPROACTIVE = 'isproactive';


    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct(null, null);
    }

    /**
     * Extends the $_criteria array with custom field data (like departments etc.)
     *
     * @author Varun Shoor
     * @param array $_criteriaPointer The Criteria Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public static function ExtendCustomCriteria(&$_criteriaPointer)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = $_SWIFT->Cache->Get('staffcache');

        // ======= STAFF =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY fullname ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_field[] = array('title' => $_SWIFT->Database->Record['fullname'], 'contents' => $_SWIFT->Database->Record['staffid']);
        }

        if (!count($_field)) {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CHATSEARCH_STAFFID]['fieldcontents'] = $_field;

        // ======= DEPARTMENTS =======
        $_field = array();

        $_departmentMap = SWIFT_Department::GetDepartmentMap(APP_LIVECHAT);
        if (_is_array($_departmentMap)) {
            foreach ($_departmentMap as $_key => $_val) {
                $_field[] = array('title' => $_val['title'], 'contents' => $_val['departmentid']);

                if (isset($_val['subdepartments']) && _is_array($_val['subdepartments'])) {
                    foreach ($_val['subdepartments'] as $_subKey => $_subVal) {
                        $_field[] = array('title' => ' |- ' . $_subVal['title'], 'contents' => $_subVal['departmentid']);
                    }
                }
            }
        }

        if (!count($_field)) {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CHATSEARCH_DEPARTMENT]['fieldcontents'] = $_field;

        // ======= TRANSFER STATUS =======
        $_field = array();

        $_field[] = array('title' => $_SWIFT->Language->Get('chattransferpending'), 'contents' => SWIFT_Chat::TRANSFER_PENDING);
        $_field[] = array('title' => $_SWIFT->Language->Get('chattransferaccepted'), 'contents' => SWIFT_Chat::TRANSFER_ACCEPTED);
        $_field[] = array('title' => $_SWIFT->Language->Get('chattransferrejected'), 'contents' => SWIFT_Chat::TRANSFER_REJECTED);

        $_criteriaPointer[self::CHATSEARCH_TRANSFERSTATUS]["fieldcontents"] = $_field;

        // ======= TRANSFERRED FROM =======
        $_field = array();

        if (_is_array($_staffCache)) {
            foreach ($_staffCache as $_key => $_val) {
                $_field[] = array('title' => $_val['fullname'], 'contents' => $_val['staffid']);
            }
        }

        if (!count($_field)) {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CHATSEARCH_TRANSFERFROM]['fieldcontents'] = $_field;

        // ======= TRANSFERRED TO =======
        $_criteriaPointer[self::CHATSEARCH_TRANSFERTO]['fieldcontents'] = $_field;

        // ======= CHAT SKILLS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "chatskills ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord()) {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['chatskillid']);
        }

        if (!count($_field)) {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::CHATSEARCH_SKILL]['fieldcontents'] = $_field;

        // ======= CHAT STATUS =======
        $_field = array();

        $_field[] = array('title' => $_SWIFT->Language->Get('chatstatusincoming'), 'contents' => SWIFT_Chat::CHAT_INCOMING);
        $_field[] = array('title' => $_SWIFT->Language->Get('chatstatusinchat'), 'contents' => SWIFT_Chat::CHAT_INCHAT);
        $_field[] = array('title' => $_SWIFT->Language->Get('chatstatusended'), 'contents' => SWIFT_Chat::CHAT_ENDED);
        $_field[] = array('title' => $_SWIFT->Language->Get('chatstatusnoanswer'), 'contents' => SWIFT_Chat::CHAT_NOANSWER);
        $_field[] = array('title' => $_SWIFT->Language->Get('chatstatustimeout'), 'contents' => SWIFT_Chat::CHAT_TIMEOUT);

        $_criteriaPointer[self::CHATSEARCH_CHATSTATUS]["fieldcontents"] = $_field;

        // ======= CHAT TYPE =======
        $_field = array();

        $_field[] = array('title' => $_SWIFT->Language->Get('chattypeclient'), 'contents' => SWIFT_Chat::CHATTYPE_CLIENT);
        $_field[] = array('title' => $_SWIFT->Language->Get('chattypestaff'), 'contents' => SWIFT_Chat::CHATTYPE_STAFF);

        $_criteriaPointer[self::CHATSEARCH_CHATTYPE]["fieldcontents"] = $_field;

        return true;
    }

    /**
     * Return the Criteria for the User Search
     *
     * @author Varun Shoor
     * @return mixed "_criteriaPointer" (ARRAY) on Success, "false" otherwise
     */
    public static function GetCriteriaPointer()
    {
        $_SWIFT = SWIFT::GetInstance();

        $_criteriaPointer = array();

        $_criteriaPointer[self::CHATSEARCH_USERFULLNAME]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_USERFULLNAME);
        $_criteriaPointer[self::CHATSEARCH_USERFULLNAME]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_USERFULLNAME);
        $_criteriaPointer[self::CHATSEARCH_USERFULLNAME]['op'] = 'string';
        $_criteriaPointer[self::CHATSEARCH_USERFULLNAME]['field'] = 'text';

        $_criteriaPointer[self::CHATSEARCH_USEREMAIL]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_USEREMAIL);
        $_criteriaPointer[self::CHATSEARCH_USEREMAIL]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_USEREMAIL);
        $_criteriaPointer[self::CHATSEARCH_USEREMAIL]['op'] = 'string';
        $_criteriaPointer[self::CHATSEARCH_USEREMAIL]['field'] = 'text';

        $_criteriaPointer[self::CHATSEARCH_SUBJECT]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_SUBJECT);
        $_criteriaPointer[self::CHATSEARCH_SUBJECT]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_SUBJECT);
        $_criteriaPointer[self::CHATSEARCH_SUBJECT]['op'] = 'string';
        $_criteriaPointer[self::CHATSEARCH_SUBJECT]['field'] = 'text';

        $_criteriaPointer[self::CHATSEARCH_CONVERSATIONSQL]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_CONVERSATIONSQL);
        $_criteriaPointer[self::CHATSEARCH_CONVERSATIONSQL]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_CONVERSATIONSQL);
        $_criteriaPointer[self::CHATSEARCH_CONVERSATIONSQL]['op'] = 'resstring';
        $_criteriaPointer[self::CHATSEARCH_CONVERSATIONSQL]['field'] = 'text';

        $_criteriaPointer[self::CHATSEARCH_CONVERSATIONNGRAM]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_CONVERSATIONNGRAM);
        $_criteriaPointer[self::CHATSEARCH_CONVERSATIONNGRAM]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_CONVERSATIONNGRAM);
        $_criteriaPointer[self::CHATSEARCH_CONVERSATIONNGRAM]['op'] = 'resstring';
        $_criteriaPointer[self::CHATSEARCH_CONVERSATIONNGRAM]['field'] = 'text';

        $_criteriaPointer[self::CHATSEARCH_STAFFID]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_STAFFID);
        $_criteriaPointer[self::CHATSEARCH_STAFFID]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_STAFFID);
        $_criteriaPointer[self::CHATSEARCH_STAFFID]['op'] = 'bool';
        $_criteriaPointer[self::CHATSEARCH_STAFFID]['field'] = 'custom';

        $_criteriaPointer[self::CHATSEARCH_DEPARTMENT]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_DEPARTMENT);
        $_criteriaPointer[self::CHATSEARCH_DEPARTMENT]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_DEPARTMENT);
        $_criteriaPointer[self::CHATSEARCH_DEPARTMENT]['op'] = 'bool';
        $_criteriaPointer[self::CHATSEARCH_DEPARTMENT]['field'] = 'custom';

        $_criteriaPointer[self::CHATSEARCH_DATE]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_DATE);
        $_criteriaPointer[self::CHATSEARCH_DATE]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_DATE);
        $_criteriaPointer[self::CHATSEARCH_DATE]['op'] = 'int';
        $_criteriaPointer[self::CHATSEARCH_DATE]['field'] = 'cal';

        $_criteriaPointer[self::CHATSEARCH_DATERANGE]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_DATERANGE);
        $_criteriaPointer[self::CHATSEARCH_DATERANGE]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_DATERANGE);
        $_criteriaPointer[self::CHATSEARCH_DATERANGE]['op'] = 'resbool';
        $_criteriaPointer[self::CHATSEARCH_DATERANGE]['field'] = 'daterange';

        $_criteriaPointer[self::CHATSEARCH_WAITTIME]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_WAITTIME);
        $_criteriaPointer[self::CHATSEARCH_WAITTIME]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_WAITTIME);
        $_criteriaPointer[self::CHATSEARCH_WAITTIME]['op'] = 'int';
        $_criteriaPointer[self::CHATSEARCH_WAITTIME]['field'] = 'int';

        $_criteriaPointer[self::CHATSEARCH_TRANSFERSTATUS]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_TRANSFERSTATUS);
        $_criteriaPointer[self::CHATSEARCH_TRANSFERSTATUS]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_TRANSFERSTATUS);
        $_criteriaPointer[self::CHATSEARCH_TRANSFERSTATUS]['op'] = 'bool';
        $_criteriaPointer[self::CHATSEARCH_TRANSFERSTATUS]['field'] = 'custom';

        $_criteriaPointer[self::CHATSEARCH_TRANSFERFROM]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_TRANSFERFROM);
        $_criteriaPointer[self::CHATSEARCH_TRANSFERFROM]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_TRANSFERFROM);
        $_criteriaPointer[self::CHATSEARCH_TRANSFERFROM]['op'] = 'bool';
        $_criteriaPointer[self::CHATSEARCH_TRANSFERFROM]['field'] = 'custom';

        $_criteriaPointer[self::CHATSEARCH_TRANSFERTO]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_TRANSFERTO);
        $_criteriaPointer[self::CHATSEARCH_TRANSFERTO]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_TRANSFERTO);
        $_criteriaPointer[self::CHATSEARCH_TRANSFERTO]['op'] = 'bool';
        $_criteriaPointer[self::CHATSEARCH_TRANSFERTO]['field'] = 'custom';

        $_criteriaPointer[self::CHATSEARCH_TRANSFERDATE]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_TRANSFERDATE);
        $_criteriaPointer[self::CHATSEARCH_TRANSFERDATE]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_TRANSFERDATE);
        $_criteriaPointer[self::CHATSEARCH_TRANSFERDATE]['op'] = 'int';
        $_criteriaPointer[self::CHATSEARCH_TRANSFERDATE]['field'] = 'cal';

        $_criteriaPointer[self::CHATSEARCH_TRANSFERDATERANGE]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_TRANSFERDATERANGE);
        $_criteriaPointer[self::CHATSEARCH_TRANSFERDATERANGE]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_TRANSFERDATERANGE);
        $_criteriaPointer[self::CHATSEARCH_TRANSFERDATERANGE]['op'] = 'resbool';
        $_criteriaPointer[self::CHATSEARCH_TRANSFERDATERANGE]['field'] = 'daterange';

        $_criteriaPointer[self::CHATSEARCH_CHATTYPE]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_CHATTYPE);
        $_criteriaPointer[self::CHATSEARCH_CHATTYPE]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_CHATTYPE);
        $_criteriaPointer[self::CHATSEARCH_CHATTYPE]['op'] = 'bool';
        $_criteriaPointer[self::CHATSEARCH_CHATTYPE]['field'] = 'custom';

        $_criteriaPointer[self::CHATSEARCH_CHATSTATUS]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_CHATSTATUS);
        $_criteriaPointer[self::CHATSEARCH_CHATSTATUS]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_CHATSTATUS);
        $_criteriaPointer[self::CHATSEARCH_CHATSTATUS]['op'] = 'bool';
        $_criteriaPointer[self::CHATSEARCH_CHATSTATUS]['field'] = 'custom';

        $_criteriaPointer[self::CHATSEARCH_SKILL]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_SKILL);
        $_criteriaPointer[self::CHATSEARCH_SKILL]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_SKILL);
        $_criteriaPointer[self::CHATSEARCH_SKILL]['op'] = 'bool';
        $_criteriaPointer[self::CHATSEARCH_SKILL]['field'] = 'custom';

        $_criteriaPointer[self::CHATSEARCH_ROUNDROBINHITS]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_ROUNDROBINHITS);
        $_criteriaPointer[self::CHATSEARCH_ROUNDROBINHITS]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_ROUNDROBINHITS);
        $_criteriaPointer[self::CHATSEARCH_ROUNDROBINHITS]['op'] = 'int';
        $_criteriaPointer[self::CHATSEARCH_ROUNDROBINHITS]['field'] = 'int';

        $_criteriaPointer[self::CHATSEARCH_IPADDRESS]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_IPADDRESS);
        $_criteriaPointer[self::CHATSEARCH_IPADDRESS]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_IPADDRESS);
        $_criteriaPointer[self::CHATSEARCH_IPADDRESS]['op'] = 'string';
        $_criteriaPointer[self::CHATSEARCH_IPADDRESS]['field'] = 'text';

        $_criteriaPointer[self::CHATSEARCH_ISPROACTIVE]['title'] = $_SWIFT->Language->Get('cs' . self::CHATSEARCH_ISPROACTIVE);
        $_criteriaPointer[self::CHATSEARCH_ISPROACTIVE]['desc'] = $_SWIFT->Language->Get('desc_cs' . self::CHATSEARCH_ISPROACTIVE);
        $_criteriaPointer[self::CHATSEARCH_ISPROACTIVE]['op'] = 'bool';
        $_criteriaPointer[self::CHATSEARCH_ISPROACTIVE]['field'] = 'bool';

        return $_criteriaPointer;
    }

    /**
     * Returns the field pointer
     *
     * @author Varun Shoor
     * @return array
     */
    public static function GetFieldPointer()
    {
        $_fieldPointer = array();
        $_fieldPointer[self::CHATSEARCH_USERFULLNAME] = 'chatobjects.userfullname';
        $_fieldPointer[self::CHATSEARCH_USEREMAIL] = 'chatobjects.useremail';
        $_fieldPointer[self::CHATSEARCH_SUBJECT] = 'chatobjects.subject';
        $_fieldPointer[self::CHATSEARCH_STAFFID] = 'chatobjects.staffid';
        $_fieldPointer[self::CHATSEARCH_DATE] = 'chatobjects.dateline';
        $_fieldPointer[self::CHATSEARCH_DATERANGE] = 'chatobjects.dateline';
        $_fieldPointer[self::CHATSEARCH_DEPARTMENT] = 'chatobjects.departmentid';
        $_fieldPointer[self::CHATSEARCH_WAITTIME] = 'chatobjects.waittime';
        $_fieldPointer[self::CHATSEARCH_TRANSFERSTATUS] = 'chatobjects.transferstatus';
        $_fieldPointer[self::CHATSEARCH_TRANSFERFROM] = 'chatobjects.transferfromid';
        $_fieldPointer[self::CHATSEARCH_TRANSFERTO] = 'chatobjects.transfertoid';
        $_fieldPointer[self::CHATSEARCH_TRANSFERDATE] = 'chatobjects.transferdateline';
        $_fieldPointer[self::CHATSEARCH_TRANSFERDATERANGE] = 'chatobjects.transferdateline';
        $_fieldPointer[self::CHATSEARCH_CHATTYPE] = 'chatobjects.chattype';
        $_fieldPointer[self::CHATSEARCH_CHATSTATUS] = 'chatobjects.chatstatus';
        $_fieldPointer[self::CHATSEARCH_ROUNDROBINHITS] = 'chatobjects.roundrobinhits';
        $_fieldPointer[self::CHATSEARCH_SKILL] = 'chatobjects.chatskillid';
        $_fieldPointer[self::CHATSEARCH_IPADDRESS] = 'chatobjects.ipaddress';
        $_fieldPointer[self::CHATSEARCH_ISPROACTIVE] = 'chatobjects.isproactive';

        return $_fieldPointer;
    }
}
