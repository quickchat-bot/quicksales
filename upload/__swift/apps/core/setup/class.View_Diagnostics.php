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

try {
    SWIFT_Loader::LoadView('SetupBase', __DIR__);
} catch (SWIFT_Exception $e) {
}

/**
 * The Setup View Management Class
 *
 * @author Varun Shoor
 * @property Controller_Diagnostics $Controller
 */
class View_Diagnostics extends View_SetupBase
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->SetType(self::TYPE_DIAGNOSTICS);

        $this->Template->Assign('_setupTypeString', $this->Language->Get('diagnostics'));
        $this->Template->Assign('_setupType', 'Diagnostics');

        if (isset($_POST['step']))
        {
            $this->SetCurrentStep((int) ($_POST['step']));
        }
    }

    /**
     * Display the Header
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Header()
    {
        $_setupSteps = array(0 => $this->Language->Get('setlicenseagreement'), 1 => $this->Language->Get('setsysreq'), 2 => $this->Language->Get('setcollectinfo'), 3 => $this->Language->Get('diagresult'));

        $this->LoadSteps($_setupSteps);

        parent::Header();
    }

    /**
     * Displays the Database Diagnostic Field
     *
     * @author Varun Shoor
     * @param int $_index The Index
     * @param string $_key The Key
     * @param string $_value (OPTIONAL) The Value
     * @param string|array $_textValue (OPTIONAL) The Text Area Value
     * @return bool "true" on Success, "false" otherwise
     */
    private function DisplayDatabaseDiagnosticField($_index, $_key, $_value = '', $_textValue = '')
    {
        if (!is_array($_textValue))
        {
            $_textValue = array($_textValue);
        }

        echo '<table width="98%" border="0" cellspacing="0" cellpadding="3"><tr><td width="100%" class="row1"><b>&nbsp;'.$_index.'.</b> '. $_key . IIF(!empty($_value), ' => '. $_value) .'</td></tr><tr><td align="left" width="100%"><textarea class="swifttextarea" style="width: 98%;">'. htmlspecialchars(implode("\n", $_textValue)) .'</textarea></td></tr></table>';

        return true;
    }

    /**
     * Display the Header Title
     *
     * @author Varun Shoor
     * @param string $_headerTitle The Header Title
     * @return bool "true" on Success, "false" otherwise
     */
    private function DisplayHeader($_headerTitle)
    {
        echo '<table width="98%"  border="0" cellspacing="0" cellpadding="3"><tr><td width="100%" class="row1"><b>&nbsp;'. $_headerTitle .':</b></td></tr></table>';

        return true;
    }

    /**
     * The Diagnostics Result
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function DatabaseDiagnosticsResult()
    {
        /**
        * ###############################################
        * DATABASE STRUCTURE CHECK
        * ###############################################
        */
        $_tableStructure = $this->Controller->SetupDiagnostics->GetTableStructure();
        $_columnStructure = $this->Controller->SetupDiagnostics->GetColumnStructure();
        $_indexStructure = $this->Controller->SetupDiagnostics->GetIndexStructure();
        $_resultPositive = false;

        // Missing Tables
        if (isset($_tableStructure[SWIFT_SetupDiagnostics::TABLE_MISSING]) && _is_array($_tableStructure[SWIFT_SetupDiagnostics::TABLE_MISSING]))
        {
            $this->DisplayHeader($this->Language->Get('diagmissingtables'));

            $_index = 1;
            foreach ($_tableStructure[SWIFT_SetupDiagnostics::TABLE_MISSING] as $_key=>$_val)
            {
                foreach ($_val as $_subKey=>$_subVal)
                {
                    $_resultPositive = true;

                    $this->DisplayDatabaseDiagnosticField($_index, $_subVal[0], '', $_subVal[1]);

                    $_index++;
                }
            }
        }

        // Missing Indexes
        if (isset($_indexStructure[SWIFT_SetupDiagnostics::INDEX_MISSING]) && _is_array($_indexStructure[SWIFT_SetupDiagnostics::INDEX_MISSING]))
        {
            $this->DisplayHeader($this->Language->Get('diagmissingindexes'));

            $_index = 1;
            foreach ($_indexStructure[SWIFT_SetupDiagnostics::INDEX_MISSING] as $_key=>$_val)
            {
                foreach ($_val as $_subKey=>$_subVal)
                {
                    $_resultPositive = true;

                    $this->DisplayDatabaseDiagnosticField($_index, $_key, $_subVal[0], $_subVal[1]);

                    $_index++;
                }
            }
        }

        // Index Mismatch
        if (isset($_indexStructure[SWIFT_SetupDiagnostics::INDEX_MISMATCH]) && _is_array($_indexStructure[SWIFT_SetupDiagnostics::INDEX_MISMATCH]))
        {
            $this->DisplayHeader($this->Language->Get('diagindexmismatch'));

            $_index = 1;
            foreach ($_indexStructure[SWIFT_SetupDiagnostics::INDEX_MISMATCH] as $_key=>$_val)
            {
                foreach ($_val as $_subKey=>$_subVal)
                {
                    $_resultPositive = true;

                    $this->DisplayDatabaseDiagnosticField($_index, $_key, $_subVal[0], $_subVal[1]);

                    $_index++;
                }
            }
        }

        // Missing Columns
        if (isset($_columnStructure[SWIFT_SetupDiagnostics::COLUMN_MISSING]) && _is_array($_columnStructure[SWIFT_SetupDiagnostics::COLUMN_MISSING]))
        {
            $this->DisplayHeader($this->Language->Get('diagmissingcolumns'));

            $_index = 1;
            foreach ($_columnStructure[SWIFT_SetupDiagnostics::COLUMN_MISSING] as $_key=>$_val)
            {
                foreach ($_val as $_subKey=>$_subVal)
                {
                    $_resultPositive = true;

                    $this->DisplayDatabaseDiagnosticField($_index, $_key, $_subVal[0], $_subVal[1]);

                    $_index++;
                }
            }
        }

        // Column MetaType Mismatch
        if (isset($_columnStructure[SWIFT_SetupDiagnostics::COLUMN_METATYPEMISMATCH]) && _is_array($_columnStructure[SWIFT_SetupDiagnostics::COLUMN_METATYPEMISMATCH]))
        {
            $this->DisplayHeader($this->Language->Get('diagcolumnmismatch'));

            $_index = 1;
            foreach ($_columnStructure[SWIFT_SetupDiagnostics::COLUMN_METATYPEMISMATCH] as $_key=>$_val)
            {
                foreach ($_val as $_subKey=>$_subVal)
                {
                    $_resultPositive = true;

                    $this->DisplayDatabaseDiagnosticField($_index, $_key, $_subVal[0], $_subVal[1]);

                    $_index++;
                }
            }
        }

        // Column Length Mismatch
        if (isset($_columnStructure[SWIFT_SetupDiagnostics::COLUMN_LENGTHMISMATCH]) && _is_array($_columnStructure[SWIFT_SetupDiagnostics::COLUMN_LENGTHMISMATCH]))
        {
            $this->DisplayHeader($this->Language->Get('diagcolumnlengthmismatch'));

            $_index = 1;
            foreach ($_columnStructure[SWIFT_SetupDiagnostics::COLUMN_LENGTHMISMATCH] as $_key=>$_val)
            {
                foreach ($_val as $_subKey=>$_subVal)
                {
                    $_resultPositive = true;

                    $this->DisplayDatabaseDiagnosticField($_index, $_key, $_subVal[0], $_subVal[1]);

                    $_index++;
                }
            }
        }

        if (!$_resultPositive)
        {
            $this->DisplayStatus($this->Language->Get('diagresultok'), true);
        }

        return true;
    }
}
?>
