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

namespace Troubleshooter\Client;

use Base\Library\Comment\SWIFT_CommentManager;
use SWIFT;
use SWIFT_App;
use Base\Models\Comment\SWIFT_Comment;
use SWIFT_DataID;
use SWIFT_Exception;
use Troubleshooter\Models\Category\SWIFT_TroubleshooterCategory;
use Troubleshooter\Models\Step\SWIFT_TroubleshooterStep;
use Base\Models\Widget\SWIFT_Widget;

/**
 * Comments Controller: Troubleshooter Step
 *
 * @author Varun Shoor
 * @property SWIFT_CommentManager $CommentManager
 */
class Controller_Comments extends \Controller_client
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

        $this->Load->Library('Comment:CommentManager', [], true, false, 'base');
    }

    /**
     * Submit a new Comment
     *
     * @author Varun Shoor
     * @param int $_troubleshooterStepID The Troubleshooter Step ID
     * @param string $_troubleshooterStepHistory (OPTIONAL) The Troubleshooter Step History
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Submit($_troubleshooterStepID, $_troubleshooterStepHistory = '')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_SWIFT_TroubleshooterStepObject = false;

        try {
            $_SWIFT_TroubleshooterStepObject = new SWIFT_TroubleshooterStep(new SWIFT_DataID($_troubleshooterStepID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        $_troubleshooterCategoryID = $_SWIFT_TroubleshooterStepObject->GetProperty('troubleshootercategoryid');

        $_SWIFT_TroubleshooterCategoryObject = false;

        try {
            $_SWIFT_TroubleshooterCategoryObject = new SWIFT_TroubleshooterCategory(new SWIFT_DataID($_troubleshooterCategoryID));
        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
            return false;
        }

        if (!$_SWIFT_TroubleshooterCategoryObject instanceof SWIFT_TroubleshooterCategory || !$_SWIFT_TroubleshooterCategoryObject->GetIsClassLoaded())
        {
            // @codeCoverageIgnoreStart
            // This code will never be reached
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            // @codeCoverageIgnoreEnd
        }

        if (!$_SWIFT_TroubleshooterCategoryObject->CanAccess(array(SWIFT_TroubleshooterCategory::TYPE_GLOBAL, SWIFT_TroubleshooterCategory::TYPE_PUBLIC), 0, SWIFT::Get('usergroupid')))
        {
            throw new SWIFT_Exception('Access Denied');
        }

        if ($_SWIFT_TroubleshooterStepObject->GetProperty('troubleshootercategoryid') != $_troubleshooterCategoryID)
        {
            // @codeCoverageIgnoreStart
            // This code will never be reached
            throw new SWIFT_Exception('Invalid Step Category');
            // @codeCoverageIgnoreEnd
        }

        $_commentResult = $this->CommentManager->ProcessPOSTUser(SWIFT_Comment::TYPE_TROUBLESHOOTER, $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID(),
                SWIFT::Get('basename') . '/Troubleshooter/Step/View/' . $_troubleshooterCategoryID . '/' . $_SWIFT_TroubleshooterStepObject->GetTroubleshooterStepID() . '/' . $_troubleshooterStepHistory);

        if ($_commentResult) {
            unset($_POST['fullname']); unset($_POST['email']); unset($_POST['comments']);
        }

        $this->Load->Controller('Step', 'Troubleshooter')->Load->Method('View', $_troubleshooterCategoryID, $_troubleshooterStepID, $_troubleshooterStepHistory);

        return true;
    }
}
?>
