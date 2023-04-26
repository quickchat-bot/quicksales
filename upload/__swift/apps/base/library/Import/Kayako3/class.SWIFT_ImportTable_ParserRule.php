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

namespace Base\Library\Import\QuickSupport3;

use SWIFT_Exception;
use Base\Library\Import\SWIFT_ImportManager;
use Base\Library\Import\SWIFT_ImportTable;
use SWIFT_Loader;
use Parser\Models\Rule\SWIFT_ParserRule;
use Base\Library\Rules\SWIFT_Rules;

/**
 * Import Table: ParserRule
 *
 * @author Varun Shoor
 */
class SWIFT_ImportTable_ParserRule extends SWIFT_ImportTable
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
        parent::__construct($_SWIFT_ImportManagerObject, 'ParserRule');

        if (!$this->TableExists(TABLE_PREFIX . 'parserrules')) {
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
            $this->Database->Query("DELETE FROM " . TABLE_PREFIX . "parserrules");
        }

        $_count = 0;

        $this->DatabaseImport->QueryLimit("SELECT * FROM " . TABLE_PREFIX . "parserrules ORDER BY parserruleid ASC", $this->GetItemsPerPass(), $this->GetOffset());
        while ($this->DatabaseImport->NextRecord()) {
            $_count++;

            $_ruleType = SWIFT_ParserRule::TYPE_PREPARSE;
            if ($this->DatabaseImport->Record['ruletype'] == '2') {
                $_ruleType = SWIFT_ParserRule::TYPE_POSTPARSE;
            }

            $_matchType = SWIFT_Rules::RULE_MATCHEXTENDED;

            $_ruleMatchType = SWIFT_Rules::RULE_MATCHALL;
            if ($this->DatabaseImport->Record['matchtype'] == '2') {
                $_ruleMatchType = SWIFT_Rules::RULE_MATCHANY;
            }

            $this->Database->AutoExecute(TABLE_PREFIX . 'parserrules',
                array('title' => $this->DatabaseImport->Record['title'], 'dateline' => (int)($this->DatabaseImport->Record['dateline']),
                    'sortorder' => (int)($this->DatabaseImport->Record['sortorder']), 'isenabled' => '1',
                    'stopprocessing' => (int)($this->DatabaseImport->Record['stopprocessing']), 'ruletype' => ($_ruleType),
                    'matchtype' => $_matchType), 'INSERT');
            $_parserRuleID = $this->Database->InsertID();

            $this->ImportManager->GetImportRegistry()->UpdateKey('parserrule', $this->DatabaseImport->Record['parserruleid'], $_parserRuleID);
            $this->ImportManager->GetImportRegistry()->UpdateKey('parserrule', 'rulematchtype' . $_parserRuleID, $_ruleMatchType);
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

        $_countContainer = $this->DatabaseImport->QueryFetch("SELECT COUNT(*) AS totalitems FROM " . TABLE_PREFIX . "parserrules");
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
