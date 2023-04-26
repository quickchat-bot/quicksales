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

namespace Tickets\Library\API;

use SWIFT;
use SWIFT_DataStore;
use SWIFT_Exception;
use SWIFT_Library;
use Base\Models\Tag\SWIFT_TagLink;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Ticket\SWIFT_TicketLinkedTable;
use Tickets\Models\Note\SWIFT_TicketNote;
use Base\Models\User\SWIFT_UserNoteManager;
use SWIFT_XML;

/**
 * The Ticket API Manager Class
 *
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class SWIFT_TicketAPIManager extends SWIFT_Library
{
    /**
     * @var SWIFT_XML
     */
    protected $XML = null;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_XML $_SWIFT_XMLObject
     * @throws SWIFT_Exception
     */
    public function __construct(SWIFT_XML $_SWIFT_XMLObject)
    {
        parent::__construct();

        if (!$_SWIFT_XMLObject instanceof SWIFT_XML || !$_SWIFT_XMLObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception('Invalid XML Object');
        }

        $this->XML = $_SWIFT_XMLObject;
    }

    /**
     * Render the Tickets
     *
     * @author Varun Shoor
     * @param array $_ticketIDList
     * @param bool $_renderTicketPosts (OPTIONAL)
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderTickets($_ticketIDList, $_renderTicketPosts = false)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_ticketStatusCache = $_SWIFT->Cache->Get('statuscache');
        $_ticketTypeCache = $_SWIFT->Cache->Get('tickettypecache');
        $_ticketPriorityCache = $_SWIFT->Cache->Get('prioritycache');
        $_departmentCache = $_SWIFT->Cache->Get('departmentcache');
        $_staffCache = $_SWIFT->Cache->Get('staffcache');
        $_ticketFilterCache = $_SWIFT->Cache->Get('ticketfiltercache');
        $_ticketWorkflowCache = $_SWIFT->Cache->Get('ticketworkflowcache');

        $_ticketsContainer = $_userIDList = $_userOrganizationMap = array();
        /* Bug Fix : Saloni Dhall
         *
         * SWIFT-4072 : Implement sorting parameters for content in the REST API
         *
         * Comments : FIELD clause added in SELECT, so that displays the results in the same order that the $_ticketIDList are put into the IN() clause.
         */
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tickets WHERE ticketid IN (" . BuildIN($_ticketIDList, true) . ") ORDER BY FIELD(ticketid, " . BuildIN($_ticketIDList, true) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_ticketsContainer[$_SWIFT->Database->Record['ticketid']] = $_SWIFT->Database->Record;

            if (!in_array($_SWIFT->Database->Record['userid'], $_userIDList)) {
                $_userIDList[] = $_SWIFT->Database->Record['userid'];
            }
        }

        // Process Ticket Posts?
        $_ticketPostContainer = array();

        if ($_renderTicketPosts == true) {
            $_ticketPostOrder = 'ASC';
            if ($_SWIFT->Settings->Get('t_postorder') === 'desc') {
                $_ticketPostOrder = 'DESC';
            }

            $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketposts WHERE ticketid IN (" . BuildIN($_ticketIDList) . ") ORDER BY ticketpostid " . $_ticketPostOrder);
            while ($_SWIFT->Database->NextRecord())
            {
                $_ticketPostContainer[$_SWIFT->Database->Record['ticketid']][$_SWIFT->Database->Record['ticketpostid']] = $_SWIFT->Database->Record;
            }
        }

        // Process Users & Organizations
        $_userOrganizationIDList = array();
        $_SWIFT->Database->Query("SELECT users.userid AS userid, users.userorganizationid AS userorganizationid, userorganizations.organizationname AS organizationname FROM " . TABLE_PREFIX . "users AS users
            LEFT JOIN " . TABLE_PREFIX . "userorganizations AS userorganizations ON (users.userorganizationid = userorganizations.userorganizationid)
            WHERE users.userid IN (" . BuildIN($_userIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_userOrganizationMap[$_SWIFT->Database->Record['userid']] = array((int) ($_SWIFT->Database->Record['userorganizationid']), $_SWIFT->Database->Record['organizationname']);

            $_userOrganizationIDList[] = $_SWIFT->Database->Record['userorganizationid'];
        }

        // Process Tags
        $_tagContainer = $_tagLinks = $_tagMap = $_tagIDList = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "taglinks
            WHERE linktype = '" . SWIFT_TagLink::TYPE_TICKET . "' AND linkid IN (" . BuildIN($_ticketIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!in_array($_SWIFT->Database->Record['tagid'], $_tagIDList)) {
                $_tagIDList[] = $_SWIFT->Database->Record['tagid'];
            }

            if (!isset($_tagMap[$_SWIFT->Database->Record['linkid']])) {
                $_tagMap[$_SWIFT->Database->Record['linkid']] = array();
            }

            $_tagMap[$_SWIFT->Database->Record['linkid']][] = $_SWIFT->Database->Record['tagid'];
        }

        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "tags
            WHERE tagid IN (" . BuildIN($_tagIDList) . ")");
        while ($_SWIFT->Database->NextRecord())
        {
            $_tagContainer[$_SWIFT->Database->Record['tagid']] = $_SWIFT->Database->Record['tagname'];
        }

        // Process Ticket Watchers
        $_ticketWatcherMap = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketwatchers WHERE ticketid IN (" . BuildIN($_ticketIDList) . ")
            ORDER BY ticketwatcherid ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!isset($_ticketWatcherMap[$_SWIFT->Database->Record['ticketid']])) {
                $_ticketWatcherMap[$_SWIFT->Database->Record['ticketid']] = array();
            }

            $_ticketWatcherMap[$_SWIFT->Database->Record['ticketid']][] = $_SWIFT->Database->Record['staffid'];
        }

        // Process Workflows
        $_ticketWorkflowMap = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketlinkedtables
            WHERE ticketid IN (" . BuildIN($_ticketIDList) . ") AND linktype = '" . SWIFT_TicketLinkedTable::LINKTYPE_WORKFLOW . "'");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!isset($_ticketWorkflowMap[$_SWIFT->Database->Record['ticketid']])) {
                $_ticketWorkflowMap[$_SWIFT->Database->Record['ticketid']] = array();
            }

            $_ticketWorkflowMap[$_SWIFT->Database->Record['ticketid']][] = $_SWIFT->Database->Record['linktypeid'];
        }

        // Process Ticket Notes
        $_ticketNotesContainer = array();
        $_SWIFT->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketnotes
            WHERE linktype = '" . SWIFT_TicketNote::LINKTYPE_TICKET . "' AND linktypeid IN (" . BuildIN($_ticketIDList) . ")
            ORDER BY dateline ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!isset($_ticketNotesContainer[$_SWIFT->Database->Record['linktypeid']])) {
                $_ticketNotesContainer[$_SWIFT->Database->Record['linktypeid']] = array();
            }

            $_ticketNotesContainer[$_SWIFT->Database->Record['linktypeid']][] = $_SWIFT->Database->Record;
        }

        // Process User Notes
        $_userNotesContainer = $_userOrganizationNotesContainer = array();
        $_SWIFT->Database->Query("SELECT usernotes.*, usernotedata.notecontents AS note FROM " . TABLE_PREFIX . "usernotes AS usernotes
            LEFT JOIN " . TABLE_PREFIX . "usernotedata AS usernotedata ON (usernotes.usernoteid = usernotedata.usernoteid)
            WHERE linktype = '" . SWIFT_UserNoteManager::LINKTYPE_USER . "' AND linktypeid IN (" . BuildIN($_userIDList) . ")
            ORDER BY usernotes.dateline ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!isset($_userNotesContainer[$_SWIFT->Database->Record['linktypeid']])) {
                $_userNotesContainer[$_SWIFT->Database->Record['linktypeid']] = array();
            }

            $_userNotesContainer[$_SWIFT->Database->Record['linktypeid']][] = $_SWIFT->Database->Record;
        }

        // Process User Organization Notes
        $_SWIFT->Database->Query("SELECT usernotes.*, usernotedata.notecontents AS note FROM " . TABLE_PREFIX . "usernotes AS usernotes
            LEFT JOIN " . TABLE_PREFIX . "usernotedata AS usernotedata ON (usernotes.usernoteid = usernotedata.usernoteid)
            WHERE linktype = '" . SWIFT_UserNoteManager::LINKTYPE_ORGANIZATION . "' AND linktypeid IN (" . BuildIN($_userOrganizationIDList) . ")
            ORDER BY usernotes.dateline ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!isset($_userOrganizationNotesContainer[$_SWIFT->Database->Record['linktypeid']])) {
                $_userOrganizationNotesContainer[$_SWIFT->Database->Record['linktypeid']] = array();
            }

            $_userOrganizationNotesContainer[$_SWIFT->Database->Record['linktypeid']][] = $_SWIFT->Database->Record;
        }

        // Process Ticket Time Tracks
        $_ticketTimeTrackContainer = array();
        $_SWIFT->Database->Query("SELECT tickettimetracks.*, tickettimetracknotes.notes AS note FROM " . TABLE_PREFIX . "tickettimetracks AS tickettimetracks
            LEFT JOIN " . TABLE_PREFIX . "tickettimetracknotes AS tickettimetracknotes ON (tickettimetracks.tickettimetrackid = tickettimetracknotes.tickettimetrackid)
            WHERE tickettimetracks.ticketid IN (" . BuildIN($_ticketIDList) . ")
            ORDER BY tickettimetracks.dateline ASC");
        while ($_SWIFT->Database->NextRecord())
        {
            if (!isset($_ticketTimeTrackContainer[$_SWIFT->Database->Record['ticketid']])) {
                $_ticketTimeTrackContainer[$_SWIFT->Database->Record['ticketid']] = array();
            }

            $_ticketTimeTrackContainer[$_SWIFT->Database->Record['ticketid']][] = $_SWIFT->Database->Record;
        }

        /**
         * ---------------------------------------------
         * Begin XML Rendering
         * ---------------------------------------------
         */

        $this->XML->AddParentTag('tickets');

        foreach ($_ticketsContainer as $_ticketID => $_ticket) {
            /**
             * @var SWIFT_Ticket
             */
            $_SWIFT_TicketObject = new SWIFT_Ticket(new SWIFT_DataStore($_ticket));

            $_userOrganizationName = '';
            $_userOrganizationID = 0;

            if (isset($_userOrganizationMap[$_ticket['userid']])) {
                $_userOrganizationID = $_userOrganizationMap[$_ticket['userid']][0];
                $_userOrganizationName = $_userOrganizationMap[$_ticket['userid']][1];
            }

            $_ticketTags = '';
            if (isset($_tagMap[$_ticketID])) {
                foreach ($_tagMap[$_ticketID] as $_tagID) {
                    if (isset($_tagContainer[$_tagID])) {
                        $_ticketTags .= ' ' . $_tagContainer[$_tagID];
                    }
                }
            }

            $_ticketTags = trim($_ticketTags);

            $this->XML->AddParentTag('ticket', array('id' => $_ticket['ticketid'], 'flagtype' => $_ticket['flagtype']));

            $this->XML->AddTag('displayid', $_SWIFT_TicketObject->GetTicketDisplayID());
            $this->XML->AddTag('departmentid', $_ticket['departmentid']);
            $this->XML->AddTag('statusid', $_ticket['ticketstatusid']);
            $this->XML->AddTag('priorityid', $_ticket['priorityid']);
            $this->XML->AddTag('typeid', $_ticket['tickettypeid']);
            $this->XML->AddTag('userid', $_ticket['userid']);
            $this->XML->AddTag('userorganization', $_userOrganizationName);
            $this->XML->AddTag('userorganizationid', $_userOrganizationID);
            $this->XML->AddTag('ownerstaffid', $_ticket['ownerstaffid']);
            $this->XML->AddTag('ownerstaffname', $_ticket['ownerstaffname']);
            $this->XML->AddTag('fullname', $_ticket['fullname']);
            $this->XML->AddTag('email', $_ticket['email']);
            $this->XML->AddTag('lastreplier', $_ticket['lastreplier']);
            $this->XML->AddTag('subject', $_ticket['subject']);
            $this->XML->AddTag('creationtime', $_ticket['dateline']);
            $this->XML->AddTag('lastactivity', $_ticket['lastactivity']);
            $this->XML->AddTag('laststaffreply', $_ticket['laststaffreplytime']);
            $this->XML->AddTag('lastuserreply', $_ticket['lastuserreplytime']);
            $this->XML->AddTag('slaplanid', $_ticket['slaplanid']);
            $this->XML->AddTag('nextreplydue', $_ticket['duetime']);
            $this->XML->AddTag('resolutiondue', $_ticket['resolutionduedateline']);
            $this->XML->AddTag('replies', $_ticket['totalreplies']);
            $this->XML->AddTag('ipaddress', $_ticket['ipaddress']);
            $this->XML->AddTag('creator', $_ticket['creator']);
            $this->XML->AddTag('creationmode', $_ticket['creationmode']);
            $this->XML->AddTag('creationtype', $_ticket['tickettype']);
            $this->XML->AddTag('isescalated', $_ticket['isescalated']);
            $this->XML->AddTag('escalationruleid', $_ticket['escalationruleid']);
            $this->XML->AddTag('templategroupid', $_ticket['tgroupid']);
            $this->XML->AddTag('tags', $_ticketTags);

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-1987 Ticket API result should also returns template group name
             *
             */
            $_templateGroupCache = $_SWIFT->Cache->Get('templategroupcache');
            if (!empty($_ticket['tgroupid']) && isset($_templateGroupCache[$_ticket['tgroupid']])) {
                $this->XML->AddTag('templategroupname', $_templateGroupCache[$_ticket['tgroupid']]['title']);
            }

            if (isset($_ticketWatcherMap[$_ticketID])) {
                foreach ($_ticketWatcherMap[$_ticketID] as $_staffID) {
                    if (isset($_staffCache[$_staffID])) {
                        $this->XML->AddTag('watcher', '', array('staffid' => (int) ($_staffID), 'name' => $_staffCache[$_staffID]['fullname']));
                    }
                }
            }

            if (isset($_ticketWorkflowMap[$_ticketID])) {
                foreach ($_ticketWorkflowMap[$_ticketID] as $_ticketWorkflowID) {
                    if (isset($_ticketWorkflowCache[$_ticketWorkflowID])) {
                        $this->XML->AddTag('workflow', '', array('id' => $_ticketWorkflowID, 'title' => $_ticketWorkflowCache[$_ticketWorkflowID]['title']));
                    }
                }
            }

            if (isset($_ticketNotesContainer[$_ticketID])) {
                foreach ($_ticketNotesContainer[$_ticketID] as $_ticketNote) {
                    $this->XML->AddTag('note', $_ticketNote['note'], array('type' => 'ticket', 'id' => $_ticketNote['ticketnoteid'], 'ticketid' => $_ticketNote['linktypeid'], 'notecolor' => $_ticketNote['notecolor'],
                        'creatorstaffid' => $_ticketNote['staffid'], 'forstaffid' => $_ticketNote['forstaffid'], 'creatorstaffname' => $_ticketNote['staffname'], 'creationdate' => $_ticketNote['dateline']));
                }
            }

            if (isset($_userNotesContainer[$_ticket['userid']])) {
                foreach ($_userNotesContainer[$_ticket['userid']] as $_ticketNote) {
                    $this->XML->AddTag('note', $_ticketNote['note'], array('type' => 'user', 'id' => $_ticketNote['usernoteid'], 'userid' => $_ticketNote['linktypeid'], 'notecolor' => $_ticketNote['notecolor'],
                        'creatorstaffid' => $_ticketNote['staffid'], 'forstaffid' => '0', 'creatorstaffname' => $_ticketNote['staffname'], 'creationdate' => $_ticketNote['dateline']));
                }
            }

            if (isset($_userOrganizationNotesContainer[$_userOrganizationID])) {
                foreach ($_userOrganizationNotesContainer[$_userOrganizationID] as $_ticketNote) {
                    $this->XML->AddTag('note', $_ticketNote['note'], array('type' => 'userorganization', 'id' => $_ticketNote['usernoteid'], 'userorganizationid' => $_ticketNote['linktypeid'], 'notecolor' => $_ticketNote['notecolor'],
                        'creatorstaffid' => $_ticketNote['staffid'], 'forstaffid' => '0', 'creatorstaffname' => $_ticketNote['staffname'], 'creationdate' => $_ticketNote['dateline']));
                }
            }

            if (isset($_ticketTimeTrackContainer[$_ticketID])) {
                foreach ($_ticketTimeTrackContainer[$_ticketID] as $_ticketTimeTrack) {
                    $this->XML->AddTag('note', $_ticketTimeTrack['note'],
                    array('type' => 'timetrack', 'id' => $_ticketTimeTrack['tickettimetrackid'], 'ticketid' => $_ticketTimeTrack['ticketid'], 'timeworked' => $_ticketTimeTrack['timespent'], 'timebillable' => $_ticketTimeTrack['timebillable'],
                    'billdate' => $_ticketTimeTrack['dateline'], 'workdate' => $_ticketTimeTrack['workdateline'],
                    'workerstaffid' => $_ticketTimeTrack['workerstaffid'], 'workerstaffname' => $_ticketTimeTrack['workerstaffname'],
                    'creatorstaffid' => $_ticketTimeTrack['creatorstaffid'], 'creatorstaffname' => $_ticketTimeTrack['creatorstaffname'],
                    'notecolor' => $_ticketTimeTrack['notecolor']));
                }
            }

            if ($_renderTicketPosts == true) {
                $this->XML->AddParentTag('posts');

                if (isset($_ticketPostContainer[$_ticketID])) {
                    foreach ($_ticketPostContainer[$_ticketID] as $_ticketPostID => $_ticketPost) {
                        $this->XML->AddParentTag('post');

                            $this->XML->AddTag('id', $_ticketPostID);
                            $this->XML->AddTag('ticketpostid', $_ticketPostID);
                            $this->XML->AddTag('ticketid', $_ticketPost['ticketid']);
                            $this->XML->AddTag('dateline', $_ticketPost['dateline']);
                            $this->XML->AddTag('userid', $_ticketPost['userid']);
                            $this->XML->AddTag('fullname', $_ticketPost['fullname']);
                            $this->XML->AddTag('email', $_ticketPost['email']);
                            $this->XML->AddTag('emailto', $_ticketPost['emailto']);
                            $this->XML->AddTag('ipaddress', $_ticketPost['ipaddress']);
                            $this->XML->AddTag('hasattachments', $_ticketPost['hasattachments']);
                            $this->XML->AddTag('creator', $_ticketPost['creator']);
                            $this->XML->AddTag('isthirdparty', $_ticketPost['isthirdparty']);
                            $this->XML->AddTag('ishtml', $_ticketPost['ishtml']);
                            $this->XML->AddTag('isemailed', $_ticketPost['isemailed']);
                            $this->XML->AddTag('staffid', $_ticketPost['staffid']);
                            $this->XML->AddTag('issurveycomment', $_ticketPost['issurveycomment']);
                            $this->XML->AddTag('contents', $_ticketPost['contents']);
                            $this->XML->AddTag('isprivate', $_ticketPost['isprivate']);

                        $this->XML->EndParentTag('post');
                    }
                }

                $this->XML->EndParentTag('posts');
            }

            $this->XML->EndParentTag('ticket');
        }
        $this->XML->EndParentTag('tickets');

        $this->XML->EchoXML();

        return true;
    }

}
