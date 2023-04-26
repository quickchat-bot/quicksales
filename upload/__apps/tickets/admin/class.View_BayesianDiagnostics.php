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

namespace Tickets\Admin;

use Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;
use Tickets\Library\Bayesian\SWIFT_Bayesian;

/**
 * The Bayesian Diagnostics View
 *
 * @property SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Varun Shoor
 */
class View_BayesianDiagnostics extends SWIFT_View
{
    /**
     * Render the Bayesian Diagnostics Form
     * 
     * @author Varun Shoor
     * @param string $_probabilityHTML The Probability HTML
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Render($_probabilityHTML = '')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Calculate the URL
        $this->UserInterface->Start(get_short_class($this),'/Tickets/BayesianDiagnostics/ProcessData', SWIFT_UserInterface::MODE_EDIT, false);

        $_probabilityTabSelected = false;
        $_trainingTabSelected = true;
        if (!empty($_probabilityHTML))
        {
            $_probabilityTabSelected = true;
            $_trainingTabSelected = false;
        }

        /*
         * ###############################################
         * BEGIN TRAINING TAB
         * ###############################################
         */
        $_TrainingTabObject = $this->UserInterface->AddTab($this->Language->Get('tabtraining'), 'icon_form.gif', 'training', $_trainingTabSelected);

        $_TrainingTabObject->LoadToolbar();
        $_TrainingTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', '/Tickets/BayesianDiagnostics/ProcessData',
                SWIFT_UserInterfaceToolbar::LINK_FORM);
        $_TrainingTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('bayesiandiagnostics'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_optionsContainer = array();

        $_index = 0;
        $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "bayescategories ORDER BY category ASC");
        while ($this->Database->NextRecord())
        {
            $_optionsContainer[$_index]['title'] = $this->Database->Record['category'];
            $_optionsContainer[$_index]['value'] = $this->Database->Record['bayescategoryid'];

            $_index++;
        }
        $_TrainingTabObject->Select('bayescategoryid', $this->Language->Get('bayescategory'), $this->Language->Get('desc_bayescategory'),
                $_optionsContainer);


        $_optionsContainer = array();
        $_optionsContainer[0]['title'] = $this->Language->Get('bayestrain');
        $_optionsContainer[0]['value'] = SWIFT_Bayesian::BAYES_TRAIN;
        $_optionsContainer[1]['title'] = $this->Language->Get('bayesuntrain');
        $_optionsContainer[1]['value'] = SWIFT_Bayesian::BAYES_UNTRAIN;

        $_TrainingTabObject->Select('type', $this->Language->Get('bayesactiontype'), $this->Language->Get('desc_bayesactiontype'),
                $_optionsContainer);

        $_TrainingTabObject->Title($this->Language->Get('bayestext'), 'icon_doublearrows.gif');

        $_TrainingTabObject->TextArea('bayesiantext', '', '', '', '100', '25');

        /*
         * ###############################################
         * END TRAINING TAB
         * ###############################################
         */



        /*
         * ###############################################
         * BEGIN PROBABILITY TAB
         * ###############################################
         */

        $_ProbabilityTabObject = $this->UserInterface->AddTab($this->Language->Get('tabprobability'), 'icon_settings2.gif', 'probability',
                $_probabilityTabSelected);

        if (!empty($_probabilityHTML))
        {
            $_ProbabilityTabObject->PrependHTML($_probabilityHTML);
        }

        $_ProbabilityTabObject->LoadToolbar();
        $_ProbabilityTabObject->Toolbar->AddButton($this->Language->Get('check'), 'fa-check-circle', '/Tickets/BayesianDiagnostics/CheckProbability',
                SWIFT_UserInterfaceToolbar::LINK_FORM);
        $_ProbabilityTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('bayesiandiagnostics'),
                SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_ProbabilityTabObject->Title($this->Language->Get('bayestext'), 'icon_doublearrows.gif');

        $_ProbabilityTabObject->TextArea('probabilitytext', '', '', '', '100', '25');

        /*
         * ###############################################
         * END PROBABILITY TAB
         * ###############################################
         */

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the Probability Result
     * 
     * @author Varun Shoor
     * @param array $_probabilityResult The Probability Result Container
     * @param array $_bayesianCategoryContainer The Bayesian Category Container
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function RenderProbabilityResult($_probabilityResult, $_bayesianCategoryContainer)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_finalRowHTML = '';

        $_reportHTML = '<table width="100%" border="0" cellspacing="1" cellpadding="4">';
        $_reportHTML .= '<tr>';
        $_reportHTML .= '<td align="left" valign="top" class="settabletitlerowmain2">' . $this->Language->Get('word') . '</td>';
        foreach ($_bayesianCategoryContainer as $_key => $_val)
        {
            $_reportHTML .= '<td align="left" valign="top" class="settabletitlerowmain2" width="100" nowrap>' .
            htmlspecialchars(StripName($_val['category'], 10)) . '</td>';

            if (isset($_probabilityResult[0][$_key]))
            {
                $_finalRowHTML .= '<td align="left" valign="top" class="settabletitlerowmain2"><b>' .
                htmlspecialchars(number_format($_probabilityResult[0][$_key]['combined'], 4)) . '</b></td>';
            }
        }
        $_reportHTML .= '</tr>';

        // Word Rows
        if (_is_array($_probabilityResult[1]))
        {
            foreach ($_probabilityResult[1] as $_key => $_val)
            {
                $_reportHTML .= '<tr>';
                $_reportHTML .= '<td align="left" valign="top">' . htmlspecialchars($_key) . '</td>';
                foreach ($_bayesianCategoryContainer as $_categoryKey => $_categoryKey)
                {
                    if (!isset($_val[$_categoryKey]))
                    {
                        continue;
                    }

                    $_reportHTML .= '<td align="left" valign="top">' . htmlspecialchars(number_format($_val[$_categoryKey], 4)) . '</td>';
                }

                $_reportHTML .= '</tr>';
            }
        }

        // Create the final row
        $_reportHTML .= '<tr>';
        $_reportHTML .= '<td align="left" valign="top" class="settabletitlerowmain2"><b>' . $this->Language->Get('combinedprobability') . '</b></td>';
        $_reportHTML .= $_finalRowHTML;
        $_reportHTML .= '</tr></table>';

        return $_reportHTML;
    }
}
