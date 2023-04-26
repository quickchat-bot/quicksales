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

namespace Tickets;

use SWIFT_Exception;
use Base\Library\KQL\SWIFT_KQLSchema;
use SWIFT_Loader;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\AuditLog\SWIFT_TicketAuditLog;
use Tickets\Library\Flag\SWIFT_TicketFlag;
use Tickets\Models\Ticket\SWIFT_TicketPost;

/**
 * The Tickets KQL Schema Class
 *
 * @author Varun Shoor
 */
class SWIFT_KQLSchema_tickets extends SWIFT_KQLSchema
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->LoadKQLLabels('kql_tickets', APP_TICKETS);
    }

    /**
     * Retrieve the Tickets Schema
     *
     * @author Varun Shoor
     * @return array The Schema Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSchema()
    {
        if (!$this->GetIsClassLoaded())     {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_schemaContainer = array();

        $_schemaContainer['tickets'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'ticketid',
            self::SCHEMA_TABLELABEL => 'tickets',
            self::SCHEMA_POSTCOMPILER => 'Schema_PostCompileTickets',
            self::SCHEMA_AUTOJOIN => array('users'),

            self::SCHEMA_RELATEDTABLES => array('users' => 'tickets.userid = users.userid',
                        'taglinks' => 'tickets.ticketid = taglinks.linkid AND taglinks.linktype = \'' . SWIFT_TagLink::TYPE_TICKET . '\''),

            self::SCHEMA_FIELDS => array(
                'ticketid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                    self::FIELD_WRITER => 'Field_WriteTicketID',
                ),

                'ticketmaskid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                    self::FIELD_AUXILIARY => array('ticketid' => 'tickets.ticketid'),
                    self::FIELD_WRITER => 'Field_WriteTicketMaskID',
                ),

                'departmentid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('departments.departmentid', 'departments.title', "departments.departmentapp = 'tickets'"),
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),

                'ticketstatusid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('ticketstatus.ticketstatusid', 'ticketstatus.title'),
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),

                'priorityid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('ticketpriorities.priorityid', 'ticketpriorities.title'),
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),

                'ownerstaffid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('staff.staffid', 'staff.fullname'),
                    self::FIELD_WIDTH => 200,
                    self::FIELD_ALIGN => 'center',
                ),

                'tickettypeid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('tickettypes.tickettypeid', 'tickettypes.title'),
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),

                'emailqueueid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('emailqueues.emailqueueid', 'emailqueues.email'),
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),

                'userid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('users.userid', 'users.fullname'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'staffid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('staff.staffid', 'staff.fullname'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'fullname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'email' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'subject' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 350,
                    self::FIELD_ALIGN => 'center',
                ),

                'phoneno' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'lastactivity' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'laststaffreplytime' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'lastuserreplytime' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'slaplanid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('slaplans.slaplanid', 'slaplans.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'totalreplies' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'ipaddress' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 120,
                    self::FIELD_ALIGN => 'center',
                ),

                'flagtype' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_TicketFlag::FLAG_BLUE => 'custom_blue', SWIFT_TicketFlag::FLAG_GREEN => 'custom_green', SWIFT_TicketFlag::FLAG_ORANGE => 'custom_orange', SWIFT_TicketFlag::FLAG_PURPLE => 'custom_purple', SWIFT_TicketFlag::FLAG_RED => 'custom_red', SWIFT_TicketFlag::FLAG_YELLOW => 'custom_yellow'),
                    self::FIELD_WIDTH => 120,
                    self::FIELD_ALIGN => 'center',
                ),

                'hasnotes' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'hasattachments' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'isemailed' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'isresolved' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'creator' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_Ticket::CREATOR_STAFF => 'custom_creatortpstaff', SWIFT_Ticket::CREATOR_USER => 'custom_creatortpuser'),
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'creationmode' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_Ticket::CREATIONMODE_SUPPORTCENTER => 'custom_cmsupportcenter', SWIFT_Ticket::CREATIONMODE_STAFFCP => 'custom_cmstaffcp', SWIFT_Ticket::CREATIONMODE_EMAIL => 'custom_cmemail', SWIFT_Ticket::CREATIONMODE_API => 'custom_cmapi', SWIFT_Ticket::CREATIONMODE_SITEBADGE => 'custom_cmsitebadge', SWIFT_Ticket::CREATIONMODE_MOBILE => 'custom_cmmobile', SWIFT_Ticket::CREATIONMODE_STAFFAPI => 'custom_cmstaffapi'),
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'timeworked' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 120,
                    self::FIELD_ALIGN => 'center',
                ),

                'timebilled' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 120,
                    self::FIELD_ALIGN => 'center',
                ),

                'isautoclosed' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'autoclosetimeline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'isescalated' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'escalatedtime' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'averageresponsetime' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                /**
                 * BUG FIX - Ashish Kataria
                 *
                 * SWIFT-2210 Response times in reports are not calculated in accordance with SLA-defined working hours
                 *
                 * Comments: Added field for calculating average SLA response time
                 */
                'averageslaresponsetime' => array(
                    self::FIELD_TYPE  => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'isfirstcontactresolved' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'wasreopened' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'reopendateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'resolutiondateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'escalationlevelcount' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'resolutionseconds' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'resolutionlevel' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'repliestoresolution' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'duetime' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'resolutionduedateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'firstresponsetime' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 120,
                    self::FIELD_ALIGN => 'center',
                ),

                'lastpostid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('ticketposts.ticketpostid', 'ticketposts.contents'),
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),

                'hasbilling' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),
            ),
        );



        /**
         * ---------------------------------------------
         * Ticket Posts
         * ---------------------------------------------
         */
        $_schemaContainer['ticketposts'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'ticketpostid',
            self::SCHEMA_TABLELABEL => 'ticketposts',
            self::SCHEMA_AUTOJOIN => array('tickets'),

            self::SCHEMA_RELATEDTABLES => array('tickets' => 'ticketposts.ticketid = tickets.ticketid'),

            self::SCHEMA_FIELDS => array(
                'ticketpostid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'subject' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 300,
                    self::FIELD_ALIGN => 'center',
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'fullname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'email' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 250,
                    self::FIELD_ALIGN => 'center',
                ),

                'contents' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 400,
                    self::FIELD_ALIGN => 'left',
                    self::FIELD_AUXILIARY => array('ishtml' => 'ticketposts.ishtml'),
                    self::FIELD_PROCESSOR => 'Field_ProcessHTMLContents',
                ),

                'ipaddress' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'isthirdparty' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'isemailed' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'creator' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_TicketPost::CREATOR_STAFF => 'custom_creatortpstaff', SWIFT_TicketPost::CREATOR_USER => 'custom_creatortpuser', SWIFT_TicketPost::CREATOR_THIRDPARTY => 'custom_creatortpthirdparty', SWIFT_TicketPost::CREATOR_CC => 'custom_creatortpcc', SWIFT_TicketPost::CREATOR_BCC => 'custom_creatortpbcc'),
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'creationmode' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_Ticket::CREATIONMODE_SUPPORTCENTER => 'custom_cmsupportcenter', SWIFT_Ticket::CREATIONMODE_STAFFCP => 'custom_cmstaffcp', SWIFT_Ticket::CREATIONMODE_EMAIL => 'custom_cmemail', SWIFT_Ticket::CREATIONMODE_API => 'custom_cmapi', SWIFT_Ticket::CREATIONMODE_SITEBADGE => 'custom_cmsitebadge', SWIFT_Ticket::CREATIONMODE_MOBILE => 'custom_cmmobile', SWIFT_Ticket::CREATIONMODE_STAFFAPI => 'custom_cmstaffapi'),
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'responsetime' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 120,
                    self::FIELD_ALIGN => 'center',
                ),

                'firstresponsetime' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 120,
                    self::FIELD_ALIGN => 'center',
                ),

                /**
                 * BUG FIX - Ashish Kataria
                 *
                 * SWIFT-2210 Response times in reports are not calculated in accordance with SLA-defined working hours
                 *
                 * Comments: Added field for calculating SLA response time
                 */
                'slaresponsetime' => array(
                    self::FIELD_TYPE  => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 120,
                    self::FIELD_ALIGN => 'center',
                ),

                'isprivate' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'issurveycomment' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

            ),
        );


        /**
         * ---------------------------------------------
         * EscalationPaths
         * ---------------------------------------------
         */
        $_schemaContainer['escalationpaths'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'escalationpathid',
            self::SCHEMA_TABLELABEL => 'escalationpaths',
            self::SCHEMA_AUTOJOIN => array('tickets', 'slaplans', 'escalationrules', 'staff', 'departments', 'ticketstatus', 'ticketpriorities', 'tickettypes'),

            self::SCHEMA_RELATEDTABLES => array('tickets' => 'escalationpaths.ticketid = tickets.ticketid',
                'slaplans' => 'escalationpaths.slaplanid = slaplans.slaplanid',
                'escalationrules' => 'escalationpaths.escalationruleid = escalationrules.escalationruleid',
                'staff' => 'escalationpaths.ownerstaffid = staff.staffid',
                'departments' => 'escalationpaths.departmentid = departments.departmentid',
                'ticketstatus' => 'escalationpaths.ticketstatusid = ticketstatus.ticketstatusid',
                'ticketpriorities' => 'escalationpaths.priorityid = ticketpriorities.priorityid',
                'tickettypes' => 'escalationpaths.tickettypeid = tickettypes.tickettypeid'),

            self::SCHEMA_FIELDS => array(
                'escalationpathid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'ticketid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('tickets.ticketid', 'tickets.ticketid'),
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'slaplanid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('slaplans.slaplanid', 'slaplans.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'escalationruleid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('escalationrules.escalationruleid', 'escalationrules.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'ownerstaffid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('staff.staffid', 'staff.fullname'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'departmentid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('departments.departmentid', 'departments.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'ticketstatusid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('ticketstatus.ticketstatusid', 'ticketstatus.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'priorityid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('ticketpriorities.priorityid', 'ticketpriorities.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'tickettypeid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('tickettypes.tickettypeid', 'tickettypes.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),
            ),
        );


        /**
         * ---------------------------------------------
         * TicketTypes
         * ---------------------------------------------
         */
        $_schemaContainer['tickettypes'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'tickettypeid',
            self::SCHEMA_TABLELABEL => 'tickettype',

            self::SCHEMA_FIELDS => array(
                'tickettypeid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'title' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),
            ),
        );


        /**
         * ---------------------------------------------
         * TicketStatus
         * ---------------------------------------------
         */
        $_schemaContainer['ticketstatus'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'ticketstatusid',
            self::SCHEMA_TABLELABEL => 'ticketstatus',

            self::SCHEMA_FIELDS => array(
                'ticketstatusid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'title' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),
            ),
        );


        /**
         * ---------------------------------------------
         * TicketPriorities
         * ---------------------------------------------
         */
        $_schemaContainer['ticketpriorities'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'priorityid',
            self::SCHEMA_TABLELABEL => 'ticketpriorities',

            self::SCHEMA_FIELDS => array(
                'priorityid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'title' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),
            ),
        );


        /**
         * ---------------------------------------------
         * SLAPlans
         * ---------------------------------------------
         */
        $_schemaContainer['slaplans'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'slaplanid',
            self::SCHEMA_TABLELABEL => 'slaplans',

            self::SCHEMA_FIELDS => array(
                'slaplanid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'title' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),
            ),
        );


        /**
         * ---------------------------------------------
         * EscalationRules
         * ---------------------------------------------
         */
        $_schemaContainer['escalationrules'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'escalationruleid',
            self::SCHEMA_TABLELABEL => 'escalationrules',

            self::SCHEMA_FIELDS => array(
                'escalationruleid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'title' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),
            ),
        );


        /**
         * ---------------------------------------------
         * TicketAuditLogs
         * ---------------------------------------------
         */
        SWIFT_Loader::LoadModel('AuditLog:TicketAuditLog', APP_TICKETS);
        $_schemaContainer['ticketauditlogs'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'ticketauditlogid',
            self::SCHEMA_TABLELABEL => 'ticketauditlogs',
            self::SCHEMA_POSTCOMPILER => 'Schema_PostCompileTickets',
            self::SCHEMA_AUTOJOIN => array('tickets'),

            self::SCHEMA_RELATEDTABLES => array('tickets' => 'ticketauditlogs.ticketid = tickets.ticketid',
                'staff' => 'ticketauditlogs.creatortype = \'' . SWIFT_TicketAuditLog::CREATOR_STAFF . '\' AND ticketauditlogs.creatorid = staff.staffid',
                'users' => 'ticketauditlogs.creatortype = \'' . SWIFT_TicketAuditLog::CREATOR_USER . '\' AND ticketauditlogs.creatorid = users.userid'),

            self::SCHEMA_FIELDS => array(
                'ticketauditlogid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'creatortype' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_TicketAuditLog::CREATOR_STAFF => 'custom_creatorstaff', SWIFT_TicketAuditLog::CREATOR_USER => 'custom_creatoruser', SWIFT_TicketAuditLog::CREATOR_SYSTEM => 'custom_creatorsystem', SWIFT_TicketAuditLog::CREATOR_PARSER => 'custom_creatorparser'),
                    self::FIELD_WIDTH => 100,
                ),

                'creatorfullname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'actiontype' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_TicketAuditLog::ACTION_NEWTICKET => 'custom_actionnewticket',
                        SWIFT_TicketAuditLog::ACTION_NEWTICKETPOST => 'custom_actionnewticketpost',
                        SWIFT_TicketAuditLog::ACTION_UPDATEOWNER => 'custom_actionupdateowner',
                        SWIFT_TicketAuditLog::ACTION_UPDATESTATUS => 'custom_actionupdatestatus',
                        SWIFT_TicketAuditLog::ACTION_UPDATEPRIORITY => 'custom_actionupdatepriority',
                        SWIFT_TicketAuditLog::ACTION_UPDATETYPE => 'custom_actionupdatetype',
                        SWIFT_TicketAuditLog::ACTION_UPDATEDEPARTMENT => 'custom_actionupdatedepartment',
                        SWIFT_TicketAuditLog::ACTION_UPDATETICKETPOST => 'custom_actionupdateticketpost',
                        SWIFT_TicketAuditLog::ACTION_DELETETICKETPOST => 'custom_actiondeleteticketpost',
                        SWIFT_TicketAuditLog::ACTION_DELETETICKET => 'custom_actiondeleteticket',
                        SWIFT_TicketAuditLog::ACTION_UPDATEFLAG => 'custom_actionupdateflag',
                        SWIFT_TicketAuditLog::ACTION_WATCH => 'custom_actionwatch',
                        SWIFT_TicketAuditLog::ACTION_TRASHTICKET => 'custom_actiontrashticket',
                        SWIFT_TicketAuditLog::ACTION_UPDATETAGS => 'custom_actionupdatetags',
                        SWIFT_TicketAuditLog::ACTION_LINKTICKET => 'custom_actionlinkticket',
                        SWIFT_TicketAuditLog::ACTION_MERGETICKET => 'custom_actionmergeticket',
                        SWIFT_TicketAuditLog::ACTION_BAN => 'custom_actionban',
                        SWIFT_TicketAuditLog::ACTION_UPDATETICKET => 'custom_actionupdateticket',
                        SWIFT_TicketAuditLog::ACTION_UPDATEUSER => 'custom_actionupdateuser',
                        SWIFT_TicketAuditLog::ACTION_UPDATESLA => 'custom_actionupdatesla',
                        SWIFT_TicketAuditLog::ACTION_NEWNOTE => 'custom_actionnoteadded',
                        SWIFT_TicketAuditLog::ACTION_DELETENOTE => 'custom_actionnotedeleted',
                        ),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'actionmsg' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 700,
                    self::FIELD_ALIGN => 'left',
                ),

                'departmentid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('departments.departmentid', 'departments.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),
            ),
        );


        /**
         * ---------------------------------------------
         * TicketTimeTracks
         * ---------------------------------------------
         */
        $_schemaContainer['tickettimetracks'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'tickettimetrackid',
            self::SCHEMA_TABLELABEL => 'tickettimetracks',
            self::SCHEMA_AUTOJOIN => array('tickets', 'staff', 'tickettimetracknotes'),

            self::SCHEMA_RELATEDTABLES => array('tickets' => 'tickettimetracks.ticketid = tickets.ticketid',
                'staff' => 'tickettimetracks.workerstaffid = staff.staffid',
                'users' => 'tickets.userid = users.userid',
                'tickettimetracknotes' => 'tickettimetracks.tickettimetrackid = tickettimetracknotes.tickettimetrackid'),

            self::SCHEMA_FIELDS => array(
                'tickettimetrackid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'workdateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'creatorstaffname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'timespent' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),

                'timebillable' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 160,
                    self::FIELD_ALIGN => 'center',
                ),

                'workerstaffid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('staff.staffid', 'staff.fullname'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * TicketTimeTrackNotes
         * ---------------------------------------------
         */
        $_schemaContainer['tickettimetracknotes'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'tickettimetracknoteid',
            self::SCHEMA_TABLELABEL => 'tickettimetracknotes',

            self::SCHEMA_FIELDS => array(
                'tickettimetracknoteid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'notes' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 400,
                    self::FIELD_ALIGN => 'center',
                ),

            ),
        );


        return $_schemaContainer;
    }

}
