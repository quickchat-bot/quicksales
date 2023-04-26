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

namespace Tickets\Client;

use Base\Models\User\SWIFT_UserOrganizationLink;
use Controller_client;
use SWIFT;
use SWIFT_App;
use SWIFT_Date;
use Base\Models\Department\SWIFT_Department;
use SWIFT_Exception;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterfaceGrid;
use Base\Models\User\SWIFT_UserSetting;
use Base\Models\Widget\SWIFT_Widget;

/**
 * The Ticket View List Controller
 *
 * @author Varun Shoor
 */
class Controller_ViewList extends Controller_client
{
    static protected $_sortContainer = array('lastactivity' => 'lastactivity',
                                        'lastreplier' => 'lastreplier',
                                        'department' => 'departmentid',
                                        'type' => 'tickettypeid',
                                        'status' => 'ticketstatusid',
                                        'priority' => 'priorityid');

    static protected $_sortOrderContainer = array('asc' => 'ASC', 'desc' => 'DESC');

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2528: Widget particular pages shows up using direct URIs irrespective of whether the widget's visibility is restricted.
         *
         * Comments: None
         */
        if (!SWIFT_App::IsInstalled(APP_TICKETS) || !SWIFT_Widget::IsWidgetVisible(APP_TICKETS, 'viewtickets'))
        {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();

            // Exit and stop processing so other error messages are not displayed
            log_error_and_exit($this->Language->Get('nopermission'));
        }

        $this->Load->Library('CustomField:CustomFieldRendererClient', [], true, false, 'base');
        $this->Load->Library('CustomField:CustomFieldManager', [], true, false, 'base');

        $this->Language->Load('tickets');

        SWIFT_Ticket::LoadLanguageTable();

        $_SWIFT = SWIFT::GetInstance();
        if (!$_SWIFT->Session->IsLoggedIn() || !$_SWIFT->User instanceof SWIFT_User || !$_SWIFT->User->GetIsClassLoaded())
        {
            $this->UserInterface->Error(true, $this->Language->Get('logintocontinue'));

            $this->Load->Controller('Default', 'Base')->Load->Index();

            // Exit and stop processing so other error messages are not displayed
            log_error_and_exit($this->Language->Get('logintocontinue'));
        }
    }

    /**
     * View a List of Tickets
     *
     * @author Varun Shoor
     * @param int|bool $_showResolved (OPTIONAL) Whether to show resolved tickets
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_showResolved = -1, $_ticketLimitOffset = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT->User instanceof SWIFT_User || !$_SWIFT->User->GetIsClassLoaded())
        {
            return false;
        }

        if (is_numeric($_ticketLimitOffset)) {
            $_ticketLimitOffset = (int) ($_ticketLimitOffset);
        } else {
            $_ticketLimitOffset = 0;
        }

        $_userSettingContainer = SWIFT_UserSetting::RetrieveOnUser($_SWIFT->User);

        /**
         * ---------------------------------------------
         * PROPERTY LOADING
         * ---------------------------------------------
         */
        $_userSortBy = 'lastactivity';
        if (isset($_userSettingContainer['sortby']))
        {
            $_userSortBy = $_userSettingContainer['sortby'];
        }

        $_userSortOrder = 'DESC';
        if (isset($_userSettingContainer['sortorder']))
        {
            $_userSortOrder = $_userSettingContainer['sortorder'];
        }

        $_userSortOrderFlip = '';
        if ($_userSortOrder === 'DESC')
        {
            $_userSortOrderFlip = 'asc';
        } else {
            $_userSortOrderFlip = 'desc';
        }

        $this->Template->Assign('_sortBy', $_userSortBy);
        $this->Template->Assign('_sortOrder', strtolower($_userSortOrder));
        $this->Template->Assign('_sortOrderFlip', strtolower($_userSortOrderFlip));

        $_showResolvedFinal = (int) ($_showResolved);
        $_showResolvedTickets = false;
        $_excludeResolved = true;
        if ($_showResolvedFinal == 1)
        {
            $_showResolvedTickets = true;

            $_excludeResolved = false;

            SWIFT_UserSetting::Replace($_SWIFT->User->GetUserID(), 'showresolved', '1');
        } else if ($_showResolvedFinal == 0) {
            $_showResolvedTickets = false;

            SWIFT_UserSetting::Replace($_SWIFT->User->GetUserID(), 'showresolved', '0');
        } else {
            if (isset($_userSettingContainer['showresolved']))
            {
                $_showResolvedTickets = (int) ($_userSettingContainer['showresolved']);
                if ($_showResolvedTickets) {
                    $_excludeResolved = false;
                }
            }
        }

        $this->Template->Assign('_showResolvedTickets', $_showResolvedTickets);

        $_ticketListCount = SWIFT_Ticket::RetrieveSCTicketsCountOnUser($_SWIFT->User, $_excludeResolved);

        $_ticketListOffset = ($_ticketLimitOffset);
        $_ticketListLimitCount = 15;    // Default Value of Tickets to Display Per Page

        if ($_ticketListOffset < 0) {
            $_ticketListOffset = 0;

            $_ticketListLimitCount = $_ticketListCount;
        }

        $_ticketListPaginationLimit = $_ticketListLimitCount;
        if ($_ticketListLimitCount == false) {
            $_ticketListPaginationLimit = $_ticketListCount;
        }
        $_ticketURL = SWIFT::Get('basename') . '/Tickets/ViewList/Index/' .  ($_showResolvedFinal);

        $_paginationHTML = SWIFT_UserInterfaceGrid::RenderPagination('javascript:Redirect("' . $_ticketURL . '/', $_ticketListCount, $_ticketListPaginationLimit, $_ticketListOffset, '5', 'pageoftotal', TRUE, FALSE, TRUE, TRUE, true);

        $this->Template->Assign('_paginationHTML', $_paginationHTML);

        $_departmentCache = $this->Cache->Get('departmentcache');
        $_ticketStatusCache = $this->Cache->Get('statuscache');
        $_ticketPriorityCache = $this->Cache->Get('prioritycache');
        $_ticketTypeCache = $this->Cache->Get('tickettypecache');

        $_ticketObjectContainer = SWIFT_Ticket::RetrieveSCTicketsOnUser($_SWIFT->User, $_userSortBy, $_userSortOrder, $_ticketListOffset, $_ticketListLimitCount, $_excludeResolved);
        $_ticketContainer = array();

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-5060 Unicode characters like emojis not working in the subject
         */
        /**
         * @var int $_ticketID
         * @var SWIFT_Ticket $_SWIFT_TicketObject
         */
        foreach ($_ticketObjectContainer as $_ticketID => $_SWIFT_TicketObject)
        {
            $_ticketContainer[$_ticketID] = $_SWIFT_TicketObject->GetDataStore();
            $_ticketContainer[$_ticketID]['isresolved'] = false;
            $_ticketContainer[$_ticketID]['displayticketid'] = $_SWIFT_TicketObject->GetTicketDisplayID();
            //Any subject containing HTML will be rendered as plain text.
            $_ticketContainer[$_ticketID]['subject'] = $this->Input->SanitizeForXSS($this->Emoji->Decode($_SWIFT_TicketObject->GetProperty('subject')));
            $_ticketContainer[$_ticketID]['lastactivity'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_SWIFT_TicketObject->GetProperty('lastactivity'));

            if ($_SWIFT->Settings->Get('t_cstaffname') == '1' && (int) ($_SWIFT_TicketObject->GetProperty('laststaffreplytime')) > (int) ($_SWIFT_TicketObject->GetProperty('lastuserreplytime'))) {
                $_ticketContainer[$_ticketID]['lastreplier'] = htmlspecialchars($_SWIFT->Settings->Get('t_cdisplayname'));
            } else {
                $_ticketContainer[$_ticketID]['lastreplier'] = htmlspecialchars($_SWIFT_TicketObject->GetProperty('lastreplier'));
            }

            $_ticketContainer[$_ticketID]['department'] = $this->Language->Get('na');
            $_ticketContainer[$_ticketID]['type'] = $this->Language->Get('na');
            $_ticketContainer[$_ticketID]['status'] = $this->Language->Get('na');
            $_ticketContainer[$_ticketID]['priority'] = $this->Language->Get('na');

            $_ticketContainer[$_ticketID]['statusbgcolor'] = '';
            $_ticketContainer[$_ticketID]['prioritybgcolor'] = '';

            if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]) && $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['markasresolved'] == '1')
            {
                $_ticketContainer[$_ticketID]['isresolved'] = true;
            }

            /*
             * BUG FIX - Varun Shoor
             *
             * SWIFT-697 Language translation for ticket status is not working correctly
             *
             * Comments:
             */

            // Department
            $deptId = $_SWIFT_TicketObject->GetProperty('departmentid');
            if (isset($_departmentCache[$deptId]))
            {
                if (isset($_departmentCache[$deptId]) && $_departmentCache[$deptId]['departmenttype'] == SWIFT_Department::DEPARTMENT_PUBLIC)
                {
                    $_ticketContainer[$_ticketID]['department'] = StripName(text_to_html_entities($_departmentCache[$deptId]['title']), 16);
                    $_ticketDepartmentTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_DEPARTMENT,
                        $deptId);
                    if (!empty($_ticketDepartmentTitleLanguage)) {
                        $_ticketContainer[$_ticketID]['department'] = StripName(text_to_html_entities($_ticketDepartmentTitleLanguage), 16);
                    }
                } else {
                    $_ticketContainer[$_ticketID]['department'] = $this->Language->Get('private');
                }
            }

            // Ticket Type
            if (isset($_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')]))
            {
                if (isset($_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')]) && $_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')]['type'] == SWIFT_PUBLIC)
                {
                    $_ticketContainer[$_ticketID]['type'] = StripName(htmlspecialchars($_ticketTypeCache[$_SWIFT_TicketObject->GetProperty('tickettypeid')]['title']), 16);
                    $_ticketTypeTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETTYPE, $_SWIFT_TicketObject->GetProperty('tickettypeid'));
                    if (!empty($_ticketTypeTitleLanguage)) {
                        $_ticketContainer[$_ticketID]['type'] = StripName(htmlspecialchars($_ticketTypeTitleLanguage), 16);
                    }
                } else {
                    $_ticketContainer[$_ticketID]['type'] = $this->Language->Get('private');
                }
            }

            // Ticket Status
            if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]))
            {
                if (isset($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]) && $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['statustype'] == SWIFT_PUBLIC)
                {
                    $_ticketContainer[$_ticketID]['status'] = StripName(htmlspecialchars($_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['title']), 16);
                    $_ticketStatusTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETSTATUS, $_SWIFT_TicketObject->GetProperty('ticketstatusid'));
                    if (!empty($_ticketStatusTitleLanguage)) {
                        $_ticketContainer[$_ticketID]['status'] = StripName(htmlspecialchars($_ticketStatusTitleLanguage), 16);
                    }
                } else {
                    $_ticketContainer[$_ticketID]['status'] = $this->Language->Get('private');
                }

                $_ticketContainer[$_ticketID]['statusbgcolor'] = $_ticketStatusCache[$_SWIFT_TicketObject->GetProperty('ticketstatusid')]['statusbgcolor'];
            }

            // Ticket Priorities
            if (isset($_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')]))
            {
                if (isset($_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')]) && $_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')]['type'] == SWIFT_PUBLIC)
                {
                    $_ticketContainer[$_ticketID]['priority'] = StripName(htmlspecialchars($_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')]['title']), 16);
                    $_ticketPriorityTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_TICKETPRIORITY, $_SWIFT_TicketObject->GetProperty('priorityid'));
                    if (!empty($_ticketPriorityTitleLanguage)) {
                        $_ticketContainer[$_ticketID]['priority'] = StripName(htmlspecialchars($_ticketPriorityTitleLanguage), 16);
                    }
                } else {
                    $_ticketContainer[$_ticketID]['priority'] = $this->Language->Get('private');
                }

                $_ticketContainer[$_ticketID]['prioritybgcolor'] = $_ticketPriorityCache[$_SWIFT_TicketObject->GetProperty('priorityid')]['bgcolorcode'];
            }
        }

        // get resolved tickets count
        $_resolvedTicketCount = SWIFT_Ticket::RetrieveSCTicketsCountOnUser($_SWIFT->User, false, true);

        $this->Template->Assign('_ticketContainer', $_ticketContainer);
        $this->Template->Assign('_resolvedTicketCount', $_resolvedTicketCount);

        $this->Template->Assign('_pageTitle', htmlspecialchars($this->Language->Get('ticketviewtickettitle')));
        $this->UserInterface->Header('viewtickets');

        $this->Template->Render('viewtickets_list');

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Sort the Ticket List Results
     *
     * @author Varun Shoor
     * @param string $_sortBy The Sort By Field
     * @param string $_sortOrder The Sort Order
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Sort($_sortBy, $_sortOrder)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!isset(static::$_sortContainer[$_sortBy]) || !isset(static::$_sortOrderContainer[$_sortOrder])) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        SWIFT_UserSetting::Replace($_SWIFT->User->GetUserID(), 'sortby', static::$_sortContainer[$_sortBy]);
        SWIFT_UserSetting::Replace($_SWIFT->User->GetUserID(), 'sortorder', static::$_sortOrderContainer[$_sortOrder]);

        $this->Load->Index();

        return true;
    }
}
