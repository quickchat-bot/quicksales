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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use Controller_admin;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Session;
use Base\Models\Staff\SWIFT_StaffActivityLog;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Tickets\Library\Bayesian\SWIFT_Bayesian;
use Tickets\Models\Bayesian\SWIFT_BayesianCategory;

/**
 * The Bayesian Diagnostics Controller
 *
 * @author Varun Shoor
 *
 * @method Library($_libraryName, $_arguments, $_initiateInstance, $_customAppName, $_appName)
 * @property SWIFT_Bayesian $Bayesian
 * @property Controller_BayesianDiagnostics $Load
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_BayesianDiagnostics $View
 */
class Controller_BayesianDiagnostics extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 20;

    const MODE_TRAINING = 1;
    const MODE_PROBABILITY = 2;

    private $_probabilityHTML = '';

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Bayesian:Bayesian', [], true, false, 'tickets');

        $this->Language->Load('tickets');
    }

    /**
     * Runs the Checks for Training/Probability
     *
     * @author Varun Shoor
     * @param int $_mode The Check Mode
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function RunChecks($_mode)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // BEGIN CSRF HASH CHECK

        if (!SWIFT_Session::CheckCSRFHash($_POST['csrfhash']))
        {
            SWIFT::Error($_SWIFT->Language->Get('titlecsrfhash'), $_SWIFT->Language->Get('msgcsrfhash'));

            return false;
        }

        // END CSRF HASH CHECK

        if ($_mode == self::MODE_TRAINING)
        {
            if (empty($_POST['bayescategoryid']) || empty($_POST['type']) || trim($_POST['bayesiantext']) == '')
            {
                $this->UserInterface->CheckFields('bayescategoryid', 'type', 'bayesiantext');

                $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                return false;
            }
        } else if ($_mode == self::MODE_PROBABILITY) {
            if (trim($_POST['probabilitytext']) == '')
            {
                $this->UserInterface->CheckFields('probabilitytext');

                $this->UserInterface->Error($this->Language->Get('titlefieldempty'), $this->Language->Get('msgfieldempty'));

                return false;
            }
        }

        if (SWIFT::Get('isdemo') == true) {
            $this->UserInterface->Error($this->Language->Get('titledemomode'), $this->Language->Get('msgdemomode'));

            return false;
        }

        if ($_SWIFT->Staff->GetPermission('admin_tcanrunbayesdiagnostics') == '0') {
            $this->UserInterface->Error($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));

            return false;
        }

        return true;
    }

    /**
     * Render the UI Confirmation
     *
     * @author Varun Shoor
     * @param mixed $_mode The User Interface Mode
     * @param int $_wordCount The Word Count for the Category being Rendered
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    private function _RenderConfirmation($_mode, $_wordCount)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalText = $this->Language->Get('bayescattitle') . ': ' . htmlspecialchars($_POST['category']) . '<br />';
        $_finalText .= $this->Language->Get('categoryweight') . ': ' . (float)($_POST['categoryweight']) . '<br />';
        $_finalText .= $this->Language->Get('wordcount') . ': ' . number_format($_wordCount, 0).'<br />';

        if ($_mode == SWIFT_UserInterface::MODE_INSERT)
        {
            SWIFT::Info(sprintf($this->Language->Get('titlebayesinsert'), htmlspecialchars($_POST['category'])), sprintf($this->Language->Get('msgbayesinsert'), htmlspecialchars($_POST['category'])) . '<br />' . $_finalText);
        } else if ($_mode == SWIFT_UserInterface::MODE_EDIT) {
            SWIFT::Info(sprintf($this->Language->Get('titlebayesupdate'), htmlspecialchars($_POST['category'])), sprintf($this->Language->Get('msgbayesupdate'), htmlspecialchars($_POST['category'])) . '<br />' . $_finalText);
        }

        return true;
    }

    /**
     * Render the Bayesian Diagnostics Form
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header($this->Language->Get('bayesian') . ' > ' . $this->Language->Get('diagnostics'), self::MENU_ID, self::NAVIGATION_ID);

        if ($_SWIFT->Staff->GetPermission('admin_tcanrunbayesdiagnostics') == '0')
        {
            $this->UserInterface->DisplayError($this->Language->Get('titlenoperm'), $this->Language->Get('msgnoperm'));
        } else {
            $this->View->Render($this->_probabilityHTML);
        }

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Train/Untrain Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ProcessData()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(self::MODE_TRAINING))
        {
            $_SWIFT_BayesianCategoryObject = new SWIFT_BayesianCategory($_POST['bayescategoryid']);
            if (!$_SWIFT_BayesianCategoryObject instanceof SWIFT_BayesianCategory || !$_SWIFT_BayesianCategoryObject->GetIsClassLoaded())
            {
                // @codeCoverageIgnoreStart
                // this code will never be executed
                throw new SWIFT_Exception(SWIFT_INVALIDDATA);
            }

            if ($_POST['type'] == SWIFT_Bayesian::BAYES_TRAIN)
            {
                $this->Bayesian->Train(0, $_POST['bayescategoryid'], $_POST['bayesiantext']);

                SWIFT::Info($this->Language->Get('titlebtrain'), $this->Language->Get('msgbtrain'));

                SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activitybayestrain'), htmlspecialchars($_SWIFT_BayesianCategoryObject->GetProperty('category'))), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            } else if ($_POST['type'] == SWIFT_Bayesian::BAYES_UNTRAIN) {

                $this->Bayesian->Untrain(0, $_POST['bayescategoryid'], $_POST['bayesiantext']);

                SWIFT::Info($this->Language->Get('titlebuntrain'), $this->Language->Get('msgbuntrain'));

                SWIFT_StaffActivityLog::AddToLog(sprintf($this->Language->Get('activitybayesuntrain'), htmlspecialchars($_SWIFT_BayesianCategoryObject->GetProperty('category'))), SWIFT_StaffActivityLog::ACTION_UPDATE, SWIFT_StaffActivityLog::SECTION_TICKETS, SWIFT_StaffActivityLog::INTERFACE_ADMIN);
            }

            $this->Load->Index();

            return true;
        }

        $this->Load->Index();

        return false;
    }

    /**
     * Probability Processor Processor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function CheckProbability()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->RunChecks(self::MODE_PROBABILITY))
        {
            $_probabilityResult = $this->Bayesian->Get($_POST['probabilitytext']);

            $_bayesCategoryContainer = $this->Bayesian->GetCategories(false);

            $this->_probabilityHTML = $this->View->RenderProbabilityResult($_probabilityResult, $_bayesCategoryContainer);

            $this->Load->Index();

            return true;
        }

        $this->Load->Index();

        return false;
    }
}
