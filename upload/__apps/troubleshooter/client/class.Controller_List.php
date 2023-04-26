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

namespace Troubleshooter\Client;

use Base\Library\UserInterface\SWIFT_UserInterfaceClient;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use Base\Models\Widget\SWIFT_Widget;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;

/**
 * The Troubleshooter List Controller
 *
 * @property SWIFT_UserInterfaceClient $UserInterface
 * @author Varun Shoor
 */
class Controller_List extends \Controller_client
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

        /*
         * BUG FIX - Parminder Singh
         *
         * SWIFT-2528: Widget particular pages shows up using direct URIs irrespective of whether the widget's visibility is restricted.
         *
         * Comments: None
         */
        if (!SWIFT_App::IsInstalled(APP_TROUBLESHOOTER) || !SWIFT_Widget::IsWidgetVisible(APP_TROUBLESHOOTER))
        {
            $this->UserInterface->Error(true, $this->Language->Get('nopermission'));
            $this->Load->Controller('Default', 'Core')->Load->Index();

            return;
        }

        $this->Language->Load('troubleshooter');
    }

    /**
     * The Troubleshooter Category Rendering Function
     *
     * @author Varun Shoor
     * @param int $_troubleshooterCategoryID (OPTIONAL) The Troubleshooter Category ID Preselected
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index($_troubleshooterCategoryID = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_troubleshooterCategoryContainer = SWIFT_TroubleshooterCategory::Retrieve(array(SWIFT_TroubleshooterCategory::TYPE_GLOBAL, SWIFT_TroubleshooterCategory::TYPE_PUBLIC),
                0, SWIFT::Get('usergroupid'));

        foreach ($_troubleshooterCategoryContainer as $_loopTroubleshooterCategoryID => $_troubleshooterCategory) {
            $_troubleshooterCategoryContainer[$_loopTroubleshooterCategoryID]['title'] = htmlspecialchars($_troubleshooterCategory['title']);
            $_troubleshooterCategoryContainer[$_loopTroubleshooterCategoryID]['description'] = nl2br(htmlspecialchars($_troubleshooterCategory['description']));

            if ($this->Settings->Get('tr_displayviews') == '1')
            {
                $_troubleshooterCategoryContainer[$_loopTroubleshooterCategoryID]['views'] = IIF($_troubleshooterCategory['views'] > 0, sprintf($this->Language->Get('trcategoryviews'), $_troubleshooterCategory['views']), '');
            } else {
                $_troubleshooterCategoryContainer[$_loopTroubleshooterCategoryID]['views'] = '';
            }
        }

        $this->Template->Assign('_troubleshooterCategoryContainer', $_troubleshooterCategoryContainer);
        $this->Template->Assign('_troubleshooterCategoryCount', count($_troubleshooterCategoryContainer));

        $this->Template->Assign('_pageTitle', htmlspecialchars($this->Language->Get('troubleshooter')));
        $this->UserInterface->Header('troubleshooter');
        $this->Template->Render('troubleshooterlist');
        $this->UserInterface->Footer();

        return true;
    }
}
