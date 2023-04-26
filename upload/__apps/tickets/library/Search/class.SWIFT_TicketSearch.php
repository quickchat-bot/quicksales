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

namespace Tickets\Library\Search;

use SWIFT;
use SWIFT_App;
use Base\Models\CustomField\SWIFT_CustomField;
use Base\Models\Department\SWIFT_Department;
use Base\Library\Rules\SWIFT_Rules;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Library\Flag\SWIFT_TicketFlag;

/**
 * The Ticket Search Management Lib
 *
 * @author Varun Shoor
 */
class SWIFT_TicketSearch extends SWIFT_Rules
{
    // Criteria
    const TICKETSEARCH_TICKETID = 'ticketid';
    const TICKETSEARCH_TICKETMASKID = 'ticketmaskid';
    const TICKETSEARCH_FULLNAME = 'fullname'; // Include Ticket Post Data
    const TICKETSEARCH_EMAIL = 'email'; // Include Ticket Post Data
    const TICKETSEARCH_LASTREPLIER = 'lastreplier';
    const TICKETSEARCH_REPLYTO = 'replyto';
    const TICKETSEARCH_SUBJECT = 'subject';
    const TICKETSEARCH_MESSAGE = 'message'; // Custom
    const TICKETSEARCH_TICKETNOTES = 'ticketnotes'; // Custom
    const TICKETSEARCH_MESSAGELIKE = 'messagelike'; // Custom
    const TICKETSEARCH_USER = 'user'; // Custom
    const TICKETSEARCH_USERORGANIZATION = 'userorganization'; // Custom
    const TICKETSEARCH_IPADDRESS = 'ipaddress';
    const TICKETSEARCH_CHARSET = 'charset';
    const TICKETSEARCH_PHONE = 'phone';

    const TICKETSEARCH_TIMEWORKED = 'timeworked';
    const TICKETSEARCH_TIMEBILLED = 'timebilled';

    const TICKETSEARCH_DEPARTMENT = 'department';
    const TICKETSEARCH_OWNER = 'owner';
    const TICKETSEARCH_TYPE = 'type';
    const TICKETSEARCH_STATUS = 'status';
    const TICKETSEARCH_PRIORITY = 'priority';
    const TICKETSEARCH_EMAILQUEUE = 'emailqueue';
    const TICKETSEARCH_SLAPLAN = 'slaplan';
    const TICKETSEARCH_FLAG = 'flag';
    const TICKETSEARCH_TEMPLATEGROUP = 'templategroup';
    const TICKETSEARCH_ESCALATION = 'escalation';
    const TICKETSEARCH_BAYESIAN = 'bayesian';
    const TICKETSEARCH_USERGROUP = 'usergroup'; // Custom
    const TICKETSEARCH_CREATOR = 'creator';
    const TICKETSEARCH_CREATIONMODE = 'creationmode';

    const TICKETSEARCH_DUE = 'due';
    const TICKETSEARCH_DUERANGE = 'duerange';
    const TICKETSEARCH_RESOLUTIONDUE = 'resolutiondue';
    const TICKETSEARCH_RESOLUTIONDUERANGE = 'resolutionduerange';

    const TICKETSEARCH_CREATIONDATE = 'creationdate';
    const TICKETSEARCH_CREATIONDATERANGE = 'creationdaterange';
    const TICKETSEARCH_LASTACTIVITY = 'lastactivity';
    const TICKETSEARCH_LASTACTIVITYRANGE = 'lastactivityrange';
    const TICKETSEARCH_LASTSTAFFREPLY = 'laststaffreply';
    const TICKETSEARCH_LASTSTAFFREPLYRANGE = 'laststaffreplyrange';
    const TICKETSEARCH_LASTUSERREPLY = 'lastuserreply';
    const TICKETSEARCH_LASTUSERREPLYRANGE = 'lastuserreplyrange';
    const TICKETSEARCH_ESCALATEDDATE = 'escalateddate';
    const TICKETSEARCH_ESCALATEDDATERANGE = 'escalateddaterange';
    const TICKETSEARCH_RESOLUTIONDATE = 'resolutiondate';
    const TICKETSEARCH_RESOLUTIONDATERANGE = 'resolutiondaterange';
    const TICKETSEARCH_REOPENDATE = 'reopendate';
    const TICKETSEARCH_REOPENDATERANGE = 'reopendaterange';


    const TICKETSEARCH_EDITED = 'edited';
    const TICKETSEARCH_EDITEDBY = 'editedby';
    const TICKETSEARCH_EDITEDDATE = 'editeddate';
    const TICKETSEARCH_EDITEDDATERANGE = 'editeddaterange';


    const TICKETSEARCH_TOTALREPLIES = 'totalreplies';
    const TICKETSEARCH_HASNOTES = 'hasnotes';
    const TICKETSEARCH_HASATTACHMENTS = 'hasattachments';
    const TICKETSEARCH_ISEMAILED = 'isemailed';
    const TICKETSEARCH_HASDRAFT = 'hasdraft';
    const TICKETSEARCH_HASFOLLOWUP = 'hasfollowup';
    const TICKETSEARCH_ISLINKED = 'islinked';


    const TICKETSEARCH_ISFIRSTCONTACTRESOLVED = 'isfirstcontactresolved';
    const TICKETSEARCH_AVERAGERESPONSETIME = 'averageresponsetime';
    const TICKETSEARCH_ESCALATIONLEVELCOUNT = 'escalationlevelcount';
    const TICKETSEARCH_WASREOPENED = 'wasreopened';
    const TICKETSEARCH_ISRESOLVED = 'isresolved';
    const TICKETSEARCH_RESOLUTIONLEVEL = 'resolutionlevel';

    const TICKETSEARCH_CUSTOMFIELDS = 'customfield__';
    const TICKETSEARCH_CUSTOMFIELDGROUP = 'customfieldgroup__';

    const TICKETSEARCHGROUP_MISCELLANEOUS = 'miscellaneous';
    const TICKETSEARCHGROUP_GENERAL = 'generaloptions';
    const TICKETSEARCHGROUP_DATE = 'dateoptions';
    const TICKETSEARCH_TAG = 'tag';

    /**
    * Extends the $_criteria array with custom field data (like departments etc.)
    *
    * @author Varun Shoor
    * @param array $_criteriaPointer The Criteria Pointer
    * @return bool "true" on Success, "false" otherwise
    */
    public static function ExtendCustomCriteria(&$_criteriaPointer) {
        $_SWIFT = SWIFT::GetInstance();

        // ======= DEPARTMENTS =======
        $_field = array();
        $_departmentMapContainer =  SWIFT_Department::GetDepartmentMap(APP_TICKETS);
        foreach ($_departmentMapContainer as $_departmentID => $_departmentContainer)
        {
            $_field[] = array('title' => $_departmentContainer['title'], 'contents' => $_departmentID);

            $subdepartments = (array) $_departmentContainer['subdepartments'];
            if (_is_array($subdepartments))
            {
                /**
                 * @var int $_subDepartmentID
                 * @var array $_subDepartmentContainer
                 */
                foreach ($subdepartments as $_subDepartmentID => $_subDepartmentContainer)
                {
                    $_field[] = array('title' => '|--' . $_subDepartmentContainer['title'], 'contents' => $_subDepartmentID);
                }
            }
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_DEPARTMENT]['fieldcontents'] = $_field;

        // ======= FLAG =======
        $_field = array();
        $_SWIFT_TicketFlagObject = new SWIFT_TicketFlag();
        foreach ($_SWIFT_TicketFlagObject->GetFlagContainer() as $_flagType => $_flagContainer)
        {
            $_field[] = array('title' => $_flagContainer[0], 'contents' => $_flagType);
        }

        if (!count($_field))
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }
        // @codeCoverageIgnoreEnd

        $_criteriaPointer[self::TICKETSEARCH_FLAG]['fieldcontents'] = $_field;


        // ======= OWNER =======
        $_field = array();
        $_field[] = array('title' => $_SWIFT->Language->Get('sactivestaff'), 'contents' => '-1');
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "staff ORDER BY fullname ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['fullname'], 'contents' => $_SWIFT->Database->Record['staffid']);
        }

        if (!count($_field))
        {
            // @codeCoverageIgnoreStart
            // this code will never be executed
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }
        // @codeCoverageIgnoreEnd

        $_criteriaPointer[self::TICKETSEARCH_OWNER]['fieldcontents'] = $_field;
        $_criteriaPointer[self::TICKETSEARCH_EDITEDBY]['fieldcontents'] = $_field;

        // ======= TICKET TYPE =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickettypes ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['tickettypeid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_TYPE]['fieldcontents'] = $_field;

        // ======= TICKET STATUS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketstatus ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['ticketstatusid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_STATUS]['fieldcontents'] = $_field;

        // ======= EMAIL QUEUE =======
        $_field = array();

        if (SWIFT_App::IsInstalled(APP_PARSER))
        {
            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "emailqueues ORDER BY email ASC");
            while ($_SWIFT->Database->NextRecord())
            {
                $_field[] = array('title' => $_SWIFT->Database->Record['email'], 'contents' => $_SWIFT->Database->Record['emailqueueid']);
            }
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_EMAILQUEUE]['fieldcontents'] = $_field;

        // ======= TICKET PRIORITIES =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketpriorities ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['priorityid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_PRIORITY]['fieldcontents'] = $_field;

        // ======= SLA PLAN =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "slaplans ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['slaplanid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_SLAPLAN]['fieldcontents'] = $_field;

        // ======= TEMPLATE GROUPS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "templategroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['tgroupid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_TEMPLATEGROUP]['fieldcontents'] = $_field;

        // ======= ESCALATION RULES =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "escalationrules ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['escalationruleid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_ESCALATION]['fieldcontents'] = $_field;

        // ======= BAYESIAN CATEGORIES =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayescategories ORDER BY category ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['category'], 'contents' => $_SWIFT->Database->Record['bayescategoryid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_BAYESIAN]['fieldcontents'] = $_field;

        // ======= USER GROUPS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER BY title ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            $_field[] = array('title' => $_SWIFT->Database->Record['title'], 'contents' => $_SWIFT->Database->Record['usergroupid']);
        }

        if (!count($_field))
        {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_USERGROUP]['fieldcontents'] = $_field;

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-1061 Ability to filter and search according to ticket tag
         */
        // ======= TAGS =======
        $_field = array();
        $_SWIFT->Database->Query("SELECT tagid, tagname FROM " . TABLE_PREFIX . "tags ORDER BY tagname ASC");

        while ($_SWIFT->Database->NextRecord()) {
            $_field[] = array('title' => $_SWIFT->Database->Record['tagname'], 'contents' => $_SWIFT->Database->Record['tagid']);
        }

        if (!count($_field)) {
            $_field[] = array('title' => $_SWIFT->Language->Get('notapplicable'), 'contents' => '0');
        }

        $_criteriaPointer[self::TICKETSEARCH_TAG]['fieldcontents'] = $_field;

        // ======= CREATOR =======
        $_field = array();
        $_field[] = array('title' => $_SWIFT->Language->Get('badgestaff'), 'contents' => SWIFT_Ticket::CREATOR_STAFF);
        $_field[] = array('title' => $_SWIFT->Language->Get('badgeuser'), 'contents' => SWIFT_Ticket::CREATOR_USER);

        $_criteriaPointer[self::TICKETSEARCH_CREATOR]['fieldcontents'] = $_field;

        // ======= CREATION MODE =======
        $_field = array();
        $_field[] = array('title' => $_SWIFT->Language->Get('cm_api'), 'contents' => SWIFT_Ticket::CREATIONMODE_API);
        $_field[] = array('title' => $_SWIFT->Language->Get('cm_email'), 'contents' => SWIFT_Ticket::CREATIONMODE_EMAIL);
        $_field[] = array('title' => $_SWIFT->Language->Get('cm_sitebadge'), 'contents' => SWIFT_Ticket::CREATIONMODE_SITEBADGE);
        $_field[] = array('title' => $_SWIFT->Language->Get('cm_staffcp'), 'contents' => SWIFT_Ticket::CREATIONMODE_STAFFCP);
        $_field[] = array('title' => $_SWIFT->Language->Get('cm_supportcenter'), 'contents' => SWIFT_Ticket::CREATIONMODE_SUPPORTCENTER);

        $_criteriaPointer[self::TICKETSEARCH_CREATIONMODE]['fieldcontents'] = $_field;

        // ======= CUSTOMFIELDS =======
        /*
         * BUG FIX - Simaranjit Singh
         *
         * SWIFT-2059  Linked custom fields are not displayed in correct order under advanced search.
         */
        $_parentContainer = $_childContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "customfieldoptions ORDER BY displayorder ASC");
        while ($_SWIFT->Database->NextRecord()) {
            if ($_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['field'] == 'custom') {
                if ($_SWIFT->Database->Record['parentcustomfieldoptionid'] != '0') {
                    $_childContainer[$_SWIFT->Database->Record['parentcustomfieldoptionid']][$_SWIFT->Database->Record['customfieldoptionid']] = $_SWIFT->Database->Record;
                } else {
                    $_parentContainer[$_SWIFT->Database->Record['customfieldoptionid']] = $_SWIFT->Database->Record;
                }
            }
        }

        foreach ($_parentContainer as $_parentID => $_parentData) {
            $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_parentData['customfieldid']]['fieldcontents'][] = array(
                'title' => $_parentData['optionvalue'], 'contents' => $_parentData['customfieldoptionid'],
            );
            if (isset($_childContainer[$_parentID])) {
                foreach ($_childContainer[$_parentID] as $_childData) {
                    $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_childData['customfieldid']]['fieldcontents'][] = array(
                        'title' => '|--' . $_childData['optionvalue'], 'contents' => $_childData['customfieldoptionid'],
                    );
                }
            }
        }

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

        // General Options Group
        $_criteriaPointer[self::TICKETSEARCHGROUP_GENERAL]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCHGROUP_GENERAL);
        $_criteriaPointer[self::TICKETSEARCHGROUP_GENERAL]['optgroup'] = true;

        $_criteriaPointer[self::TICKETSEARCH_TICKETID]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_TICKETID);
        $_criteriaPointer[self::TICKETSEARCH_TICKETID]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_TICKETID);
        $_criteriaPointer[self::TICKETSEARCH_TICKETID]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_TICKETID]['field'] = 'int';

        $_criteriaPointer[self::TICKETSEARCH_TICKETMASKID]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_TICKETMASKID);
        $_criteriaPointer[self::TICKETSEARCH_TICKETMASKID]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_TICKETMASKID);
        $_criteriaPointer[self::TICKETSEARCH_TICKETMASKID]['op'] = 'string';
        $_criteriaPointer[self::TICKETSEARCH_TICKETMASKID]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_FULLNAME]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_FULLNAME);
        $_criteriaPointer[self::TICKETSEARCH_FULLNAME]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_FULLNAME);
        $_criteriaPointer[self::TICKETSEARCH_FULLNAME]['op'] = 'string';
        $_criteriaPointer[self::TICKETSEARCH_FULLNAME]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_EMAIL]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_EMAIL);
        $_criteriaPointer[self::TICKETSEARCH_EMAIL]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_EMAIL);
        $_criteriaPointer[self::TICKETSEARCH_EMAIL]['op'] = 'string';
        $_criteriaPointer[self::TICKETSEARCH_EMAIL]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_LASTREPLIER]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_LASTREPLIER);
        $_criteriaPointer[self::TICKETSEARCH_LASTREPLIER]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_LASTREPLIER);
        $_criteriaPointer[self::TICKETSEARCH_LASTREPLIER]['op'] = 'string';
        $_criteriaPointer[self::TICKETSEARCH_LASTREPLIER]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_REPLYTO]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_REPLYTO);
        $_criteriaPointer[self::TICKETSEARCH_REPLYTO]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_REPLYTO);
        $_criteriaPointer[self::TICKETSEARCH_REPLYTO]['op'] = 'string';
        $_criteriaPointer[self::TICKETSEARCH_REPLYTO]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_SUBJECT]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_SUBJECT);
        $_criteriaPointer[self::TICKETSEARCH_SUBJECT]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_SUBJECT);
        $_criteriaPointer[self::TICKETSEARCH_SUBJECT]['op'] = 'string';
        $_criteriaPointer[self::TICKETSEARCH_SUBJECT]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_MESSAGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_MESSAGE);
        $_criteriaPointer[self::TICKETSEARCH_MESSAGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_MESSAGE);
        $_criteriaPointer[self::TICKETSEARCH_MESSAGE]['op'] = 'resstring';
        $_criteriaPointer[self::TICKETSEARCH_MESSAGE]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_MESSAGELIKE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_MESSAGELIKE);
        $_criteriaPointer[self::TICKETSEARCH_MESSAGELIKE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_MESSAGELIKE);
        $_criteriaPointer[self::TICKETSEARCH_MESSAGELIKE]['op'] = 'resstring';
        $_criteriaPointer[self::TICKETSEARCH_MESSAGELIKE]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_TICKETNOTES]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_TICKETNOTES);
        $_criteriaPointer[self::TICKETSEARCH_TICKETNOTES]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_TICKETNOTES);
        $_criteriaPointer[self::TICKETSEARCH_TICKETNOTES]['op'] = 'resstring';
        $_criteriaPointer[self::TICKETSEARCH_TICKETNOTES]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_USER]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_USER);
        $_criteriaPointer[self::TICKETSEARCH_USER]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_USER);
        $_criteriaPointer[self::TICKETSEARCH_USER]['op'] = 'resstring';
        $_criteriaPointer[self::TICKETSEARCH_USER]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_USERORGANIZATION]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_USERORGANIZATION);
        $_criteriaPointer[self::TICKETSEARCH_USERORGANIZATION]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_USERORGANIZATION);
        $_criteriaPointer[self::TICKETSEARCH_USERORGANIZATION]['op'] = 'resstring';
        $_criteriaPointer[self::TICKETSEARCH_USERORGANIZATION]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_PHONE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_PHONE);
        $_criteriaPointer[self::TICKETSEARCH_PHONE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_PHONE);
        $_criteriaPointer[self::TICKETSEARCH_PHONE]['op'] = 'string';
        $_criteriaPointer[self::TICKETSEARCH_PHONE]['field'] = 'text';


        $_criteriaPointer[self::TICKETSEARCH_TIMEWORKED]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_TIMEWORKED);
        $_criteriaPointer[self::TICKETSEARCH_TIMEWORKED]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_TIMEWORKED);
        $_criteriaPointer[self::TICKETSEARCH_TIMEWORKED]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_TIMEWORKED]['field'] = 'int';

        $_criteriaPointer[self::TICKETSEARCH_TIMEBILLED]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_TIMEBILLED);
        $_criteriaPointer[self::TICKETSEARCH_TIMEBILLED]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_TIMEBILLED);
        $_criteriaPointer[self::TICKETSEARCH_TIMEBILLED]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_TIMEBILLED]['field'] = 'int';


        $_criteriaPointer[self::TICKETSEARCH_DEPARTMENT]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_DEPARTMENT);
        $_criteriaPointer[self::TICKETSEARCH_DEPARTMENT]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_DEPARTMENT);
        $_criteriaPointer[self::TICKETSEARCH_DEPARTMENT]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_DEPARTMENT]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_OWNER]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_OWNER);
        $_criteriaPointer[self::TICKETSEARCH_OWNER]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_OWNER);
        $_criteriaPointer[self::TICKETSEARCH_OWNER]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_OWNER]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_TYPE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_TYPE);
        $_criteriaPointer[self::TICKETSEARCH_TYPE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_TYPE);
        $_criteriaPointer[self::TICKETSEARCH_TYPE]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_TYPE]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_STATUS]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_STATUS);
        $_criteriaPointer[self::TICKETSEARCH_STATUS]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_STATUS);
        $_criteriaPointer[self::TICKETSEARCH_STATUS]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_STATUS]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_PRIORITY]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_PRIORITY);
        $_criteriaPointer[self::TICKETSEARCH_PRIORITY]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_PRIORITY);
        $_criteriaPointer[self::TICKETSEARCH_PRIORITY]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_PRIORITY]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_EMAILQUEUE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_EMAILQUEUE);
        $_criteriaPointer[self::TICKETSEARCH_EMAILQUEUE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_EMAILQUEUE);
        $_criteriaPointer[self::TICKETSEARCH_EMAILQUEUE]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_EMAILQUEUE]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_SLAPLAN]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_SLAPLAN);
        $_criteriaPointer[self::TICKETSEARCH_SLAPLAN]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_SLAPLAN);
        $_criteriaPointer[self::TICKETSEARCH_SLAPLAN]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_SLAPLAN]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_FLAG]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_FLAG);
        $_criteriaPointer[self::TICKETSEARCH_FLAG]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_FLAG);
        $_criteriaPointer[self::TICKETSEARCH_FLAG]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_FLAG]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_TEMPLATEGROUP]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_TEMPLATEGROUP);
        $_criteriaPointer[self::TICKETSEARCH_TEMPLATEGROUP]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_TEMPLATEGROUP);
        $_criteriaPointer[self::TICKETSEARCH_TEMPLATEGROUP]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_TEMPLATEGROUP]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_ESCALATION]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_ESCALATION);
        $_criteriaPointer[self::TICKETSEARCH_ESCALATION]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_ESCALATION);
        $_criteriaPointer[self::TICKETSEARCH_ESCALATION]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_ESCALATION]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_BAYESIAN]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_BAYESIAN);
        $_criteriaPointer[self::TICKETSEARCH_BAYESIAN]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_BAYESIAN);
        $_criteriaPointer[self::TICKETSEARCH_BAYESIAN]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_BAYESIAN]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_USERGROUP]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_USERGROUP);
        $_criteriaPointer[self::TICKETSEARCH_USERGROUP]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_USERGROUP);
        $_criteriaPointer[self::TICKETSEARCH_USERGROUP]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_USERGROUP]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_CREATOR]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_CREATOR);
        $_criteriaPointer[self::TICKETSEARCH_CREATOR]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_CREATOR);
        $_criteriaPointer[self::TICKETSEARCH_CREATOR]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_CREATOR]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_CREATIONMODE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_CREATIONMODE);
        $_criteriaPointer[self::TICKETSEARCH_CREATIONMODE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_CREATIONMODE);
        $_criteriaPointer[self::TICKETSEARCH_CREATIONMODE]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_CREATIONMODE]['field'] = 'custom';


        // Date Options Group
        $_criteriaPointer[self::TICKETSEARCHGROUP_DATE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCHGROUP_DATE);
        $_criteriaPointer[self::TICKETSEARCHGROUP_DATE]['optgroup'] = true;

        $_criteriaPointer[self::TICKETSEARCH_DUE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_DUE);
        $_criteriaPointer[self::TICKETSEARCH_DUE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_DUE);
        $_criteriaPointer[self::TICKETSEARCH_DUE]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_DUE]['field'] = 'cal';

        $_criteriaPointer[self::TICKETSEARCH_DUERANGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_DUERANGE);
        $_criteriaPointer[self::TICKETSEARCH_DUERANGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_DUERANGE);
        $_criteriaPointer[self::TICKETSEARCH_DUERANGE]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_DUERANGE]['field'] = 'daterangeforward';

        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDUE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_RESOLUTIONDUE);
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDUE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_RESOLUTIONDUE);
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDUE]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDUE]['field'] = 'cal';

        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDUERANGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_RESOLUTIONDUERANGE);
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDUERANGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_RESOLUTIONDUERANGE);
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDUERANGE]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDUERANGE]['field'] = 'daterangeforward';

        $_criteriaPointer[self::TICKETSEARCH_CREATIONDATE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_CREATIONDATE);
        $_criteriaPointer[self::TICKETSEARCH_CREATIONDATE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_CREATIONDATE);
        $_criteriaPointer[self::TICKETSEARCH_CREATIONDATE]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_CREATIONDATE]['field'] = 'cal';

        $_criteriaPointer[self::TICKETSEARCH_CREATIONDATERANGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_CREATIONDATERANGE);
        $_criteriaPointer[self::TICKETSEARCH_CREATIONDATERANGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_CREATIONDATERANGE);
        $_criteriaPointer[self::TICKETSEARCH_CREATIONDATERANGE]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_CREATIONDATERANGE]['field'] = 'daterange';

        $_criteriaPointer[self::TICKETSEARCH_LASTACTIVITY]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_LASTACTIVITY);
        $_criteriaPointer[self::TICKETSEARCH_LASTACTIVITY]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_LASTACTIVITY);
        $_criteriaPointer[self::TICKETSEARCH_LASTACTIVITY]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_LASTACTIVITY]['field'] = 'cal';

        $_criteriaPointer[self::TICKETSEARCH_LASTACTIVITYRANGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_LASTACTIVITYRANGE);
        $_criteriaPointer[self::TICKETSEARCH_LASTACTIVITYRANGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_LASTACTIVITYRANGE);
        $_criteriaPointer[self::TICKETSEARCH_LASTACTIVITYRANGE]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_LASTACTIVITYRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::TICKETSEARCH_LASTSTAFFREPLY]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_LASTSTAFFREPLY);
        $_criteriaPointer[self::TICKETSEARCH_LASTSTAFFREPLY]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_LASTSTAFFREPLY);
        $_criteriaPointer[self::TICKETSEARCH_LASTSTAFFREPLY]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_LASTSTAFFREPLY]['field'] = 'cal';

        $_criteriaPointer[self::TICKETSEARCH_LASTSTAFFREPLYRANGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_LASTSTAFFREPLYRANGE);
        $_criteriaPointer[self::TICKETSEARCH_LASTSTAFFREPLYRANGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_LASTSTAFFREPLYRANGE);
        $_criteriaPointer[self::TICKETSEARCH_LASTSTAFFREPLYRANGE]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_LASTSTAFFREPLYRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::TICKETSEARCH_LASTUSERREPLY]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_LASTUSERREPLY);
        $_criteriaPointer[self::TICKETSEARCH_LASTUSERREPLY]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_LASTUSERREPLY);
        $_criteriaPointer[self::TICKETSEARCH_LASTUSERREPLY]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_LASTUSERREPLY]['field'] = 'cal';

        $_criteriaPointer[self::TICKETSEARCH_LASTUSERREPLYRANGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_LASTUSERREPLYRANGE);
        $_criteriaPointer[self::TICKETSEARCH_LASTUSERREPLYRANGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_LASTUSERREPLYRANGE);
        $_criteriaPointer[self::TICKETSEARCH_LASTUSERREPLYRANGE]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_LASTUSERREPLYRANGE]['field'] = 'daterange';

        $_criteriaPointer[self::TICKETSEARCH_ESCALATEDDATE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_ESCALATEDDATE);
        $_criteriaPointer[self::TICKETSEARCH_ESCALATEDDATE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_ESCALATEDDATE);
        $_criteriaPointer[self::TICKETSEARCH_ESCALATEDDATE]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_ESCALATEDDATE]['field'] = 'cal';

        $_criteriaPointer[self::TICKETSEARCH_ESCALATEDDATERANGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_ESCALATEDDATERANGE);
        $_criteriaPointer[self::TICKETSEARCH_ESCALATEDDATERANGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_ESCALATEDDATERANGE);
        $_criteriaPointer[self::TICKETSEARCH_ESCALATEDDATERANGE]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_ESCALATEDDATERANGE]['field'] = 'daterange';

        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDATE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_RESOLUTIONDATE);
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDATE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_RESOLUTIONDATE);
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDATE]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDATE]['field'] = 'cal';

        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDATERANGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_RESOLUTIONDATERANGE);
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDATERANGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_RESOLUTIONDATERANGE);
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDATERANGE]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONDATERANGE]['field'] = 'daterange';

        $_criteriaPointer[self::TICKETSEARCH_REOPENDATE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_REOPENDATE);
        $_criteriaPointer[self::TICKETSEARCH_REOPENDATE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_REOPENDATE);
        $_criteriaPointer[self::TICKETSEARCH_REOPENDATE]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_REOPENDATE]['field'] = 'cal';

        $_criteriaPointer[self::TICKETSEARCH_REOPENDATERANGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_REOPENDATERANGE);
        $_criteriaPointer[self::TICKETSEARCH_REOPENDATERANGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_REOPENDATERANGE);
        $_criteriaPointer[self::TICKETSEARCH_REOPENDATERANGE]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_REOPENDATERANGE]['field'] = 'daterange';

        $_criteriaPointer[self::TICKETSEARCH_EDITEDDATE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_EDITEDDATE);
        $_criteriaPointer[self::TICKETSEARCH_EDITEDDATE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_EDITEDDATE);
        $_criteriaPointer[self::TICKETSEARCH_EDITEDDATE]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_EDITEDDATE]['field'] = 'cal';

        $_criteriaPointer[self::TICKETSEARCH_EDITEDDATERANGE]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_EDITEDDATERANGE);
        $_criteriaPointer[self::TICKETSEARCH_EDITEDDATERANGE]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_EDITEDDATERANGE);
        $_criteriaPointer[self::TICKETSEARCH_EDITEDDATERANGE]['op'] = 'resbool';
        $_criteriaPointer[self::TICKETSEARCH_EDITEDDATERANGE]['field'] = 'daterange';


        // Miscellaneous Group
        $_criteriaPointer[self::TICKETSEARCHGROUP_MISCELLANEOUS]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCHGROUP_MISCELLANEOUS);
        $_criteriaPointer[self::TICKETSEARCHGROUP_MISCELLANEOUS]['optgroup'] = true;

        $_criteriaPointer[self::TICKETSEARCH_EDITED]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_EDITED);
        $_criteriaPointer[self::TICKETSEARCH_EDITED]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_EDITED);
        $_criteriaPointer[self::TICKETSEARCH_EDITED]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_EDITED]['field'] = 'bool';

        $_criteriaPointer[self::TICKETSEARCH_EDITEDBY]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_EDITEDBY);
        $_criteriaPointer[self::TICKETSEARCH_EDITEDBY]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_EDITEDBY);
        $_criteriaPointer[self::TICKETSEARCH_EDITEDBY]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_EDITEDBY]['field'] = 'custom';

        $_criteriaPointer[self::TICKETSEARCH_TOTALREPLIES]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_TOTALREPLIES);
        $_criteriaPointer[self::TICKETSEARCH_TOTALREPLIES]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_TOTALREPLIES);
        $_criteriaPointer[self::TICKETSEARCH_TOTALREPLIES]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_TOTALREPLIES]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_HASNOTES]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_HASNOTES);
        $_criteriaPointer[self::TICKETSEARCH_HASNOTES]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_HASNOTES);
        $_criteriaPointer[self::TICKETSEARCH_HASNOTES]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_HASNOTES]['field'] = 'bool';

        $_criteriaPointer[self::TICKETSEARCH_HASATTACHMENTS]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_HASATTACHMENTS);
        $_criteriaPointer[self::TICKETSEARCH_HASATTACHMENTS]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_HASATTACHMENTS);
        $_criteriaPointer[self::TICKETSEARCH_HASATTACHMENTS]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_HASATTACHMENTS]['field'] = 'bool';

        $_criteriaPointer[self::TICKETSEARCH_ISEMAILED]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_ISEMAILED);
        $_criteriaPointer[self::TICKETSEARCH_ISEMAILED]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_ISEMAILED);
        $_criteriaPointer[self::TICKETSEARCH_ISEMAILED]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_ISEMAILED]['field'] = 'bool';

        $_criteriaPointer[self::TICKETSEARCH_HASDRAFT]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_HASDRAFT);
        $_criteriaPointer[self::TICKETSEARCH_HASDRAFT]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_HASDRAFT);
        $_criteriaPointer[self::TICKETSEARCH_HASDRAFT]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_HASDRAFT]['field'] = 'bool';

        $_criteriaPointer[self::TICKETSEARCH_HASFOLLOWUP]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_HASFOLLOWUP);
        $_criteriaPointer[self::TICKETSEARCH_HASFOLLOWUP]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_HASFOLLOWUP);
        $_criteriaPointer[self::TICKETSEARCH_HASFOLLOWUP]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_HASFOLLOWUP]['field'] = 'bool';

        $_criteriaPointer[self::TICKETSEARCH_ISLINKED]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_ISLINKED);
        $_criteriaPointer[self::TICKETSEARCH_ISLINKED]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_ISLINKED);
        $_criteriaPointer[self::TICKETSEARCH_ISLINKED]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_ISLINKED]['field'] = 'bool';


        $_criteriaPointer[self::TICKETSEARCH_ISFIRSTCONTACTRESOLVED]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_ISFIRSTCONTACTRESOLVED);
        $_criteriaPointer[self::TICKETSEARCH_ISFIRSTCONTACTRESOLVED]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_ISFIRSTCONTACTRESOLVED);
        $_criteriaPointer[self::TICKETSEARCH_ISFIRSTCONTACTRESOLVED]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_ISFIRSTCONTACTRESOLVED]['field'] = 'bool';

        $_criteriaPointer[self::TICKETSEARCH_AVERAGERESPONSETIME]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_AVERAGERESPONSETIME);
        $_criteriaPointer[self::TICKETSEARCH_AVERAGERESPONSETIME]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_AVERAGERESPONSETIME);
        $_criteriaPointer[self::TICKETSEARCH_AVERAGERESPONSETIME]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_AVERAGERESPONSETIME]['field'] = 'int';

        $_criteriaPointer[self::TICKETSEARCH_ESCALATIONLEVELCOUNT]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_ESCALATIONLEVELCOUNT);
        $_criteriaPointer[self::TICKETSEARCH_ESCALATIONLEVELCOUNT]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_ESCALATIONLEVELCOUNT);
        $_criteriaPointer[self::TICKETSEARCH_ESCALATIONLEVELCOUNT]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_ESCALATIONLEVELCOUNT]['field'] = 'int';

        $_criteriaPointer[self::TICKETSEARCH_WASREOPENED]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_WASREOPENED);
        $_criteriaPointer[self::TICKETSEARCH_WASREOPENED]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_WASREOPENED);
        $_criteriaPointer[self::TICKETSEARCH_WASREOPENED]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_WASREOPENED]['field'] = 'bool';

        $_criteriaPointer[self::TICKETSEARCH_ISRESOLVED]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_ISRESOLVED);
        $_criteriaPointer[self::TICKETSEARCH_ISRESOLVED]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_ISRESOLVED);
        $_criteriaPointer[self::TICKETSEARCH_ISRESOLVED]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_ISRESOLVED]['field'] = 'bool';

        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONLEVEL]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_RESOLUTIONLEVEL);
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONLEVEL]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_RESOLUTIONLEVEL);
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONLEVEL]['op'] = 'int';
        $_criteriaPointer[self::TICKETSEARCH_RESOLUTIONLEVEL]['field'] = 'int';

        $_criteriaPointer[self::TICKETSEARCH_IPADDRESS]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_IPADDRESS);
        $_criteriaPointer[self::TICKETSEARCH_IPADDRESS]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_IPADDRESS);
        $_criteriaPointer[self::TICKETSEARCH_IPADDRESS]['op'] = 'string';
        $_criteriaPointer[self::TICKETSEARCH_IPADDRESS]['field'] = 'text';

        $_criteriaPointer[self::TICKETSEARCH_CHARSET]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_CHARSET);
        $_criteriaPointer[self::TICKETSEARCH_CHARSET]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_CHARSET);
        $_criteriaPointer[self::TICKETSEARCH_CHARSET]['op'] = 'string';
        $_criteriaPointer[self::TICKETSEARCH_CHARSET]['field'] = 'text';

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-1061 Ability to filter and search according to ticket tag
         */
        $_criteriaPointer[self::TICKETSEARCH_TAG]['title'] = $_SWIFT->Language->Get('ts' . self::TICKETSEARCH_TAG);
        $_criteriaPointer[self::TICKETSEARCH_TAG]['desc'] = $_SWIFT->Language->Get('desc_ts' . self::TICKETSEARCH_TAG);
        $_criteriaPointer[self::TICKETSEARCH_TAG]['op'] = 'bool';
        $_criteriaPointer[self::TICKETSEARCH_TAG]['field'] = 'custom';

        // Csutom Field Options
        $_SWIFT->Database->Query("SELECT customfields.*, customfieldgroups.title AS customfieldgrouptitle FROM " . TABLE_PREFIX . "customfields AS customfields
                LEFT JOIN " . TABLE_PREFIX . "customfieldgroups AS customfieldgroups ON (customfieldgroups.customfieldgroupid = customfields.customfieldgroupid)
                ORDER BY customfieldgroupid ASC");
        while ($_SWIFT->Database->NextRecord()) {
            if (isset($_SWIFT->Database->Record['customfieldgrouptitle'])) {
                $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDGROUP . $_SWIFT->Database->Record['customfieldgroupid']]['title'] = $_SWIFT->Database->Record['customfieldgrouptitle'];
                $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDGROUP . $_SWIFT->Database->Record['customfieldgroupid']]['optgroup'] = true;
            }
            $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['title'] = $_SWIFT->Database->Record['title'];
            $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['desc'] = $_SWIFT->Database->Record['description'];

            switch ($_SWIFT->Database->Record['fieldtype']) {
                case SWIFT_CustomField::TYPE_RADIO:
                case SWIFT_CustomField::TYPE_SELECT:
                case SWIFT_CustomField::TYPE_CHECKBOX:
                case SWIFT_CustomField::TYPE_SELECTLINKED:
                case SWIFT_CustomField::TYPE_SELECTMULTIPLE:
                    $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['op'] = 'bool';
                    $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['field'] = 'custom';

                    break;

                case SWIFT_CustomField::TYPE_DATE:
                    $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['op'] = 'int';
                    $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['field'] = 'cal';

                    break;

                case SWIFT_CustomField::TYPE_PASSWORD:
                    $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['op'] = 'resstring';
                    $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['field'] = 'text';

                    break;

                default:
                    $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['op'] = 'string';
                    $_criteriaPointer[self::TICKETSEARCH_CUSTOMFIELDS . $_SWIFT->Database->Record['customfieldid']]['field'] = 'text';
                    break;
            }

        }

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

        $_fieldPointer[self::TICKETSEARCH_TICKETID] = 'tickets.ticketid';
        $_fieldPointer[self::TICKETSEARCH_TICKETMASKID] = 'tickets.ticketmaskid';
        $_fieldPointer[self::TICKETSEARCH_FULLNAME] = 'tickets.fullname';
        $_fieldPointer[self::TICKETSEARCH_EMAIL] = 'tickets.email';
        $_fieldPointer[self::TICKETSEARCH_LASTREPLIER] = 'tickets.lastreplier';
        $_fieldPointer[self::TICKETSEARCH_REPLYTO] = 'tickets.replyto';
        $_fieldPointer[self::TICKETSEARCH_SUBJECT] = 'tickets.subject';
        $_fieldPointer[self::TICKETSEARCH_IPADDRESS] = 'tickets.ipaddress';
        $_fieldPointer[self::TICKETSEARCH_CHARSET] = 'tickets.charset';
        $_fieldPointer[self::TICKETSEARCH_PHONE] = 'tickets.phoneno';


        $_fieldPointer[self::TICKETSEARCH_TIMEWORKED] = 'tickets.timeworked';
        $_fieldPointer[self::TICKETSEARCH_TIMEBILLED] = 'tickets.timebilled';


        $_fieldPointer[self::TICKETSEARCH_DEPARTMENT] = 'tickets.departmentid';
        $_fieldPointer[self::TICKETSEARCH_OWNER] = 'tickets.ownerstaffid';
        $_fieldPointer[self::TICKETSEARCH_TYPE] = 'tickets.tickettypeid';
        $_fieldPointer[self::TICKETSEARCH_STATUS] = 'tickets.ticketstatusid';
        $_fieldPointer[self::TICKETSEARCH_PRIORITY] = 'tickets.priorityid';
        $_fieldPointer[self::TICKETSEARCH_EMAILQUEUE] = 'tickets.emailqueueid';
        $_fieldPointer[self::TICKETSEARCH_SLAPLAN] = 'tickets.slaplanid';
        $_fieldPointer[self::TICKETSEARCH_FLAG] = 'tickets.flagtype';
        $_fieldPointer[self::TICKETSEARCH_TEMPLATEGROUP] = 'tickets.tgroupid';
        $_fieldPointer[self::TICKETSEARCH_ESCALATION] = 'tickets.escalationruleid';
        $_fieldPointer[self::TICKETSEARCH_BAYESIAN] = 'tickets.bayescategoryid';
        $_fieldPointer[self::TICKETSEARCH_CREATOR] = 'tickets.creator';
        $_fieldPointer[self::TICKETSEARCH_CREATIONMODE] = 'tickets.creationmode';


        $_fieldPointer[self::TICKETSEARCH_DUE] = 'tickets.duetime';
        $_fieldPointer[self::TICKETSEARCH_DUERANGE] = 'tickets.duetime';
        $_fieldPointer[self::TICKETSEARCH_RESOLUTIONDUE] = 'tickets.resolutionduedateline';
        $_fieldPointer[self::TICKETSEARCH_RESOLUTIONDUERANGE] = 'tickets.resolutionduedateline';


        $_fieldPointer[self::TICKETSEARCH_CREATIONDATE] = 'tickets.dateline';
        $_fieldPointer[self::TICKETSEARCH_CREATIONDATERANGE] = 'tickets.dateline';
        $_fieldPointer[self::TICKETSEARCH_LASTACTIVITY] = 'tickets.lastactivity';
        $_fieldPointer[self::TICKETSEARCH_LASTACTIVITYRANGE] = 'tickets.lastactivity';
        $_fieldPointer[self::TICKETSEARCH_LASTSTAFFREPLY] = 'tickets.laststaffreplytime';
        $_fieldPointer[self::TICKETSEARCH_LASTSTAFFREPLYRANGE] = 'tickets.laststaffreplytime';
        $_fieldPointer[self::TICKETSEARCH_LASTUSERREPLY] = 'tickets.lastuserreplytime';
        $_fieldPointer[self::TICKETSEARCH_LASTUSERREPLYRANGE] = 'tickets.lastuserreplytime';
        $_fieldPointer[self::TICKETSEARCH_ESCALATEDDATE] = 'tickets.escalatedtime';
        $_fieldPointer[self::TICKETSEARCH_ESCALATEDDATERANGE] = 'tickets.escalatedtime';
        $_fieldPointer[self::TICKETSEARCH_RESOLUTIONDATE] = 'tickets.resolutiondateline';
        $_fieldPointer[self::TICKETSEARCH_RESOLUTIONDATERANGE] = 'tickets.resolutiondateline';
        $_fieldPointer[self::TICKETSEARCH_REOPENDATE] = 'tickets.reopendateline';
        $_fieldPointer[self::TICKETSEARCH_REOPENDATERANGE] = 'tickets.reopendateline';


        $_fieldPointer[self::TICKETSEARCH_EDITED] = 'tickets.edited';
        $_fieldPointer[self::TICKETSEARCH_EDITEDBY] = 'tickets.editedbystaffid';
        $_fieldPointer[self::TICKETSEARCH_EDITEDDATE] = 'tickets.editeddateline';
        $_fieldPointer[self::TICKETSEARCH_EDITEDDATERANGE] = 'tickets.editeddateline';


        $_fieldPointer[self::TICKETSEARCH_TOTALREPLIES] = 'tickets.totalreplies';
        $_fieldPointer[self::TICKETSEARCH_HASNOTES] = 'tickets.hasnotes';
        $_fieldPointer[self::TICKETSEARCH_HASATTACHMENTS] = 'tickets.hasattachments';
        $_fieldPointer[self::TICKETSEARCH_ISEMAILED] = 'tickets.isemailed';
        $_fieldPointer[self::TICKETSEARCH_HASDRAFT] = 'tickets.hasdraft';
        $_fieldPointer[self::TICKETSEARCH_HASFOLLOWUP] = 'tickets.hasfollowup';
        $_fieldPointer[self::TICKETSEARCH_ISLINKED] = 'tickets.islinked';


        $_fieldPointer[self::TICKETSEARCH_ISFIRSTCONTACTRESOLVED] = 'tickets.isfirstcontactresolved';
        $_fieldPointer[self::TICKETSEARCH_AVERAGERESPONSETIME] = 'tickets.averageresponsetime';
        $_fieldPointer[self::TICKETSEARCH_ESCALATIONLEVELCOUNT] = 'tickets.escalationlevelcount';
        $_fieldPointer[self::TICKETSEARCH_WASREOPENED] = 'tickets.wasreopened';
        $_fieldPointer[self::TICKETSEARCH_ISRESOLVED] = 'tickets.isresolved';
        $_fieldPointer[self::TICKETSEARCH_RESOLUTIONLEVEL] = 'tickets.resolutionlevel';

        return $_fieldPointer;
    }
}
?>
