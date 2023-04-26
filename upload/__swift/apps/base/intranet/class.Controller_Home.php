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

namespace Base\Intranet;

use Controller_intranet;
use SWIFT_Exception;

/**
 * The Dashboard Controller
 *
 * @author Varun Shoor
 *
 * @property View_Home $View
 */
class Controller_Home extends Controller_intranet
{
    private $_tabContainer = array();

    // Core Constants
    const TAB_NAME = 0;
    const TAB_ICON = 1;
    const TAB_TITLE = 2;
    const TAB_CONTENTS = 3;

    const TAB_WELCOME = 'welcome';

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
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_BuildWelcomeTab();

        $this->UserInterface->Header($this->Language->Get('Dashboard'), 1);
        $this->View->RenderDashboard();
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

            return false;
        }

        $_tabContents = '';

        $this->_AddTab(self::TAB_WELCOME, 'icon_dashboardwelcome.gif', $this->Language->Get('tabwelcome'), $_tabContents);

        return true;
    }

    /**
     * Adds a tab to the tab container
     *
     * @author Varun Shoor
     * @param string $_tabName The Tab Name
     * @param string $_tabIcon The Tab Icon
     * @param string $_tabTitle The Tab Title
     * @param string $_tabContents The Tab Contents
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function _AddTab($_tabName, $_tabIcon, $_tabTitle, $_tabContents)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_tabContainer[] = array(self::TAB_NAME => $_tabName, self::TAB_ICON => $_tabIcon, self::TAB_TITLE => $_tabTitle, self::TAB_CONTENTS => $_tabContents);

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

            return false;
        }

        return $this->_tabContainer;
    }
}

?>
