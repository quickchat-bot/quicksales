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

namespace Base\Library\Import\QuickSupport3;

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Parser\Models\Rule\SWIFT_ParserRule;
use Base\Library\Rules\SWIFT_Rules;

/**
 * Import Table: ParserRuleCriteria
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_ParserRuleCriteria extends SWIFT_ImportTable
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param SWIFT_ImportManager $_SWIFT_ImportManagerObject The Import Manager Object
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct(SWIFT_ImportManager $_SWIFT_ImportManagerObject)
    {
        parent::__construct($_SWIFT_ImportManagerObject, 'ParserRuleCriteria');

        if (!$this->TableExists(TABLE_PREFIX . 'rulecriteria')) {
            $this->SetByPass(true);
        }

        SWIFT_Loader::LoadModel('Rule:ParserRule', APP_PARSER);
    }

    /**
     * Import the data based on offset in the table
     *
     * @author Varun Shoor
     * @return int The number of records on success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Import()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if ($this->GetOffset() == 0) {
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserrulecriteria");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "rulecriteria ORDER BY rulecriteriaid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_newParserRuleID = $this->ImportManager->GetImportRegistry()->GetKey('parserrule', $this->DatabaseImport->Record['parserruleid']);
            if ($_newParserRuleID == false) {
                $this->GetImportManager()->AddToLog('Parser rule criteria import failed due to non existant parser rule id (incomplete old deletion)', SWIFT_ImportManager::LOG_WARNING);

                continue;
            }

            $_ruleMatch = $this->DatabaseImport->Record['rulematch'];
            if ($this->DatabaseImport->Record['name'] == SWIFT_ParserRule::PARSER_TICKETSTATUS) {
                $_ruleMatch = $this->ImportManager->GetImportRegistry()->GetKey('ticketstatus', $_ruleMatch);
            }

            $_ruleMatchType = $this->ImportManager->GetImportRegistry()->GetKey('parserrule', 'rulematchtype' . $_newParserRuleID);

            /*
            define("OP_EQUAL", 1);
            define("OP_NOTEQUAL", 2);
            define("OP_REGEXP", 3);
            define("OP_CONTAINS", 4);
            define("OP_NOTCONTAINS", 5);
            define("OP_GREATER", 6);
            define("OP_LESS", 7);
            */

            $_ruleOP = SWIFT_Rules::OP_EQUAL;
            if ($this->DatabaseImport->Record['ruleop'] == '2') {
                $_ruleOP = SWIFT_Rules::OP_NOTEQUAL;
            } elseif ($this->DatabaseImport->Record['ruleop'] == '3') {
                $_ruleOP = SWIFT_Rules::OP_REGEXP;
            } elseif ($this->DatabaseImport->Record['ruleop'] == '4') {
                $_ruleOP = SWIFT_Rules::OP_CONTAINS;
            } elseif ($this->DatabaseImport->Record['ruleop'] == '5') {
                $_ruleOP = SWIFT_Rules::OP_NOTCONTAINS;
            } elseif ($this->DatabaseImport->Record['ruleop'] == '6') {
                $_ruleOP = SWIFT_Rules::OP_GREATER;
            } elseif ($this->DatabaseImport->Record['ruleop'] == '7') {
                $_ruleOP = SWIFT_Rules::OP_LESS;
            }

            $this->GetImportManager()->AddToLog('Importing Parser Rule Criteria ID: ' . htmlspecialchars($this->DatabaseImport->Record['rulecriteriaid']), SWIFT_ImportManager::LOG_SUCCESS);

            $this->Database->AutoExecute(TABLE_PREFIX . 'parserrulecriteria',
                array('parserruleid' => $_newParserRuleID, 'rulematch' => $_ruleMatch,
                    'ruleop' => $_ruleOP, 'name' => $this->DatabaseImport->Record['name'], 'rulematchtype' => $_ruleMatchType), 'INSERT');
            $_parserRuleCriteriaID = $this->Database->InsertID();
        }

        SWIFT_ParserRule::RebuildCache();

        return $_count;
    }

    /**
     * Retrieve the total number of records in a table
     *
     * @author Varun Shoor
     * @return int The Record Count
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function GetTotal()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "rulecriteria");
        if (isset($_countContainer['totalitems'])) {
            return $_countContainer['totalitems'];
        }

        return 0;
    }

    /**
     * Retrieve the number of items to process in a pass
     *
     * @author Varun Shoor
     * @return int The Number of Items
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetItemsPerPass()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return 500;
    }
}

?>
