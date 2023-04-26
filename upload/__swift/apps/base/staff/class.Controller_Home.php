<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Staff;

use Base\Admin\Controller_Staff;
use Base\Library\CallHomeData\SWIFT_CallHomeData;
use Base\Models\Comment\SWIFT_Comment;
use News\Library\Render\SWIFT_NewsRenderManager;
use SWIFT_App;
use LiveChat\Models\Chat\SWIFT_Chat;
use LiveChat\Library\Chat\SWIFT_ChatRenderManager;
use SWIFT_Exception;
use SWIFT_Hook;
use SWIFT_Loader;
use LiveChat\Models\Message\SWIFT_MessageManager;
use Tickets\Library\View\SWIFT_TicketViewRenderer;

/**
 * The Dashboard Controller
 *
 * @author Varun Shoor
 *
 * @property View_Home $View
 */
class Controller_Home extends Controller_staff
{
    private $_tabContainer = array();

    // Core Constants
    const TAB_NAME = 0;
    const TAB_ICON = 1;
    const TAB_TITLE = 2;
    const TAB_CONTENTS = 3;
    const TAB_COUNTER = 4;

    const TAB_WELCOME = 'welcome';
    const TAB_RECENTCHATS = 'recentchats';
    const TAB_RECENTMESSAGES = 'recentmessages';
    const TAB_OVERDUETICKETS = 'overduetickets';

    protected $_counterContainer = array();

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();
        $this->Template->Assign('_isDashboard', true);

        $this->Language->Load('dashboard');
    }

    /**
     * The Main Dashboard Renderer
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        //Call home Data
        SWIFT_Loader::LoadLibrary('CallHomeData:CallHomeData', APP_BASE);

        $_CallHome = new SWIFT_CallHomeData();
        $_CallHome->CallHomeData();

        // Begin Hook: staff_dashboard_init
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_dashboard_init')) ? eval($_hookCode) : false;
        // End Hook

        $this->_BuildWelcomeTab();
        $this->_BuildRecentChatTab();
        $this->_BuildRecentMessagesTab();

        // Begin Hook: staff_dashboard_end
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_dashboard_end')) ? eval($_hookCode) : false;
        // End Hook


        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            SWIFT_Loader::LoadLibrary('View:TicketViewRenderer', APP_TICKETS);
            SWIFT_Loader::LoadModel('Ticket:Ticket', APP_TICKETS);

            $this->Language->Load('staff_ticketsmain');
            $this->Language->Load('staff_ticketsmanage');

            $this->UserInterface->AddNavigationBox($this->Language->Get('app_tickets'), SWIFT_TicketViewRenderer::RenderTree());

            // Counters
            $this->_counterContainer[] = SWIFT_TicketViewRenderer::GetMyTicketsCounter();
            $this->_counterContainer[] = SWIFT_TicketViewRenderer::GetUnassignedCounter();

            $this->_BuildOverdueTicketsTab();
        } else if (SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            SWIFT_Loader::LoadLibrary('Chat:ChatRenderManager', APP_LIVECHAT);

            $this->Language->Load('staff_livechat');

            $this->UserInterface->AddNavigationBox($this->Language->Get('app_livechat'), SWIFT_ChatRenderManager::RenderTree());
        }

        // Core Counters
        $this->_counterContainer[] = SWIFT_Comment::GetCommentCounter();

        // Begin Hook: staff_dashboard_counter_end
        unset($_hookCode);
        ($_hookCode = SWIFT_Hook::Execute('staff_dashboard_counter_end')) ? eval($_hookCode) : false;
        // End Hook

        $this->UserInterface->Header($this->Language->Get('dashboard'), 1, 0);
        $this->View->RenderDashboard($this->_counterContainer);
        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Build the Welcome Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _BuildWelcomeTab()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_tabContents = '';

        if (SWIFT_App::IsInstalled(APP_NEWS)) {
            SWIFT_Loader::LoadLibrary('Render:NewsRenderManager', APP_NEWS);
            $_SWIFT_NewsRenderManagerObject = new SWIFT_NewsRenderManager();
            if ($_SWIFT_NewsRenderManagerObject->GetIsClassLoaded()) {
                $_tabContents .= $_SWIFT_NewsRenderManagerObject->RenderWelcomeTab();
            }
        }

        $this->_AddTab(self::TAB_WELCOME, 'icon_dashboardwelcome.gif', $this->Language->Get('tabwelcome'), $_tabContents);

        return true;
    }

    /**
     * Build the Recent Chat Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _BuildRecentChatTab()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            return false;
        }

        $this->Load->LoadModel('Chat:Chat', APP_LIVECHAT);

        $_chatObjectContainer = SWIFT_Chat::RetrieveDashboardContainer();

        $_tabContents = $this->View->RenderRecentChatTabView($_chatObjectContainer);

        $this->_AddTab(self::TAB_RECENTCHATS, 'icon_chatblue.gif', $this->Language->Get('tabrecentchats'), $_tabContents, $_chatObjectContainer[0]);

        return true;
    }

    /**
     * Build the Recent Messages Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _BuildRecentMessagesTab()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_App::IsInstalled(APP_LIVECHAT)) {
            return false;
        }

        $_messageContainer = array();

        $this->Load->LoadModel('Message:MessageManager', APP_LIVECHAT);

        $_messageContainer = SWIFT_MessageManager::RetrieveDashboardContainer();

        $_tabContents = $this->View->RenderRecentMessageTabView($_messageContainer);

        $this->_AddTab(self::TAB_RECENTMESSAGES, 'icon_email.gif', $this->Language->Get('tabrecentmessages'), $_tabContents, $_messageContainer[0]);

        return true;
    }

    /**
     * Build the Overdue Tickets Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function _BuildOverdueTicketsTab()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!SWIFT_App::IsInstalled(APP_TICKETS)) {
            return false;
        }

        if (class_exists('\Tickets\Library\View\SWIFT_TicketViewRenderer')) {
            $_ticketsContainer = SWIFT_TicketViewRenderer::RetrieveOverdueContainer();

            $_tabContents = $this->View->RenderOverdueTicketsTabView($_ticketsContainer);

            $this->_AddTab(self::TAB_OVERDUETICKETS, 'icon_ticketunassigned.png', $this->Language->Get('taboverduetickets'), $_tabContents, $_ticketsContainer[0]);
        }

        return true;
    }

    /**
     * Adds a tab to the tab container
     *
     * @author Varun Shoor
     *
     * @param string $_tabName The Tab Name
     * @param string $_tabIcon The Tab Icon
     * @param string $_tabTitle The Tab Title
     * @param string $_tabContents The Tab Contents
     * @param int $_tabCounter The Tab Counter
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _AddTab($_tabName, $_tabIcon, $_tabTitle, $_tabContents, $_tabCounter = 0)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_tabContainer[] = array(self::TAB_NAME => $_tabName, self::TAB_ICON => $_tabIcon, self::TAB_TITLE => $_tabTitle, self::TAB_CONTENTS => $_tabContents,
            self::TAB_COUNTER => number_format($_tabCounter, 0));

        return true;
    }

    /**
     * Retrieve the Tab Container
     *
     * @author Varun Shoor
     * @return mixed "_tabContainer" (ARRAY) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _GetTabContainer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_tabContainer;
    }
}
