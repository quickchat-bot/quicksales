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

namespace Base\Library\Rules;

use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_Model;

/**
 * The Global Rules Manager Class
 *
 * @author Varun Shoor
 */
class SWIFT_Rules extends SWIFT_Model
{
    private $_criteriaStore = array();
    private $_matchType = false;

    // Core Constants
    const RULE_MATCHALL = 1;
    const RULE_MATCHANY = 2;
    const RULE_MATCHEXTENDED = 3;

    const OP_EQUAL = 1;
    const OP_NOTEQUAL = 2;
    const OP_REGEXP = 3;
    const OP_CONTAINS = 4;
    const OP_NOTCONTAINS = 5;
    const OP_GREATER = 6;
    const OP_LESS = 7;
    const OP_CHANGED = 8;
    const OP_CHANGEDTO = 9;
    const OP_CHANGEDFROM = 10;
    const OP_NOTCHANGED = 11;
    const OP_NOTCHANGEDTO = 12;
    const OP_NOTCHANGEDFROM = 13;

    const DATERANGE_YESTERDAY = 'yesterday';
    const DATERANGE_TODAY = 'today';
    const DATERANGE_TOMORROW = 'tomorrow';
    const DATERANGE_CURRENTWEEKTODATE = 'cwtd';
    const DATERANGE_CURRENTMONTHTODATE = 'cmtd';
    const DATERANGE_CURRENTYEARTODATE = 'cytd';
    const DATERANGE_NEXTWEEKFROMDATE = 'nwfd';
    const DATERANGE_NEXTMONTHFROMDATE = 'nmfd';
    const DATERANGE_NEXTYEARFROMDATE = 'nyfd';
    const DATERANGE_LAST7DAYS = 'l7d';
    const DATERANGE_LAST30DAYS = 'l30d';
    const DATERANGE_LAST90DAYS = 'l90d';
    const DATERANGE_LAST180DAYS = 'l180d';
    const DATERANGE_LAST365DAYS = 'l365d';
    const DATERANGE_NEXT7DAYS = 'n7d';
    const DATERANGE_NEXT30DAYS = 'n30d';
    const DATERANGE_NEXT90DAYS = 'n90d';
    const DATERANGE_NEXT180DAYS = 'n180d';
    const DATERANGE_NEXT365DAYS = 'n365d';

    const CRITERIA_NAME = 'name';
    const CRITERIA_OP = 'ruleop';
    const CRITERIA_VALUE = 'rulematch';
    const CRITERIA_MATCHTYPEEXT = 'rulematchtype';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param array $_criteria The Criteria Container
     * @param int $_matchType RULE_MATCHALL/RULE_MATCHANY
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_criteria, $_matchType)
    {
        if (!$this->SetCriteria($_criteria) || !$this->SetMatchType($_matchType)) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        parent::__construct();
    }

    /**
     * Used to set the Criteria Container (which will in turn be used by Execute)
     *
     * @author Varun Shoor
     * @param array $_criteria The Criteria Container
     * @return bool "true" on Success, "false" otherwise
     */
    protected function SetCriteria($_criteria)
    {
        $this->_criteriaStore = $_criteria;

        return true;
    }

    /**
     * Retrieves the criteria from $_criteriaStore
     *
     * @author Varun Shoor
     * @return array
     */
    protected function GetCriteria()
    {
        return $this->_criteriaStore;
    }

    /**
     * Sets the Match Type Criteria
     *
     * @author Varun Shoor
     * @param int $_matchType RULE_MATCHALL/RULE_MATCHANY
     * @return bool "true" on Success, "false" otherwise
     */
    protected function SetMatchType($_matchType)
    {
        if ($_matchType != self::RULE_MATCHALL && $_matchType != self::RULE_MATCHANY && $_matchType != self::RULE_MATCHEXTENDED) {
            return false;
        }

        $this->_matchType = $_matchType;

        return true;
    }

    /**
     * Retrieve the Match Type Flag Value
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function GetMatchType()
    {
        return $this->_matchType;
    }

    /**
     * Gets the associated value with a criteria
     *
     * @author Varun Shoor
     * @param string $_criteriaName The Criteria Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function GetCriteriaValue($_criteriaName)
    {
        return false;
    }

    /**
     * Gets the associated change container value with a criteria
     *
     * @author Varun Shoor
     * @param string $_criteriaName The Criteria Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function GetChangeContainer($_criteriaName)
    {
        return false;
    }

    /**
     * Executes the Rule
     *
     * @author Varun Shoor
     * @param array $_criteriaContainer (OPTIONAL) The Criteria Container Array
     * @return bool "true" on Success, "false" otherwise
     */
    public function Execute($_criteriaContainer = array())
    {
        if (!_is_array($this->GetCriteria())) {
            return false;
        }

        $_criteriaAll = $_criteriaAny = $_ruleMatched = false;
        $_previousMatchResult = true;


        $_extendedCriteriaAND = $_extendedCriteriaOR = false;
        $_extendedANDCount = $_extendedORCount = 0;

        // Itterate through criteria
        foreach ($this->GetCriteria() as $_criteriaKey => $_criteriaValue) {
            $_matchResult = false;

            $_fieldType = false;
            if (isset($_criteriaContainer[$_criteriaValue[0]], $_criteriaContainer[$_criteriaValue[0]]['field'])) {
                $_fieldType = $_criteriaContainer[$_criteriaValue[0]]['field'];
            } elseif (isset($_criteriaContainer[$_criteriaValue[0]], $_criteriaContainer[$_criteriaValue[0]][3])) {
                $_fieldType = $_criteriaContainer[$_criteriaValue[0]][3];
            }

            $_extendedMatch = false;
            if (isset($_criteriaValue[3])) {
                $_extendedMatch = (int)($_criteriaValue[3]);
            }

            $_matchResult = self::CheckCriteria($_criteriaValue[1], $_criteriaValue[2], $this->GetCriteriaValue($_criteriaValue[0]), $_fieldType, $this->GetChangeContainer($_criteriaValue[0]));
            //echo $_criteriaValue[0] . ' <b>=</b> '. $this->GetCriteriaValue($_criteriaValue[0]) . ' <b>X</b> ' .$_criteriaValue[2] . ' <b>=</b> ' . $_matchResult . "<BR />\n";

            // If this is an extended match and we cannot match the AND criteria then break
            //echo 'EXTENDED MATCH: ' . $_extendedMatch . '<br/><br/>';
            if ($this->GetMatchType() == self::RULE_MATCHEXTENDED && $_extendedMatch == self::RULE_MATCHALL) {
                $_extendedANDCount++;

                if ($_matchResult == false) {
                    $_extendedCriteriaAND = false;
                    break;
                } elseif ($_matchResult == true) {
                    $_extendedCriteriaAND = true;
                }

                // Or if this is an extended match and the match type is OR and we match it successfully, then mark the or property holder as true
            } elseif ($this->GetMatchType() == self::RULE_MATCHEXTENDED && $_extendedMatch == self::RULE_MATCHANY) {
                $_extendedORCount++;

                if ($_matchResult == true) {
                    $_extendedCriteriaOR = true;
                }
            }

            if ($_matchResult == true) {
                $_criteriaAny = true;
            }

            // Otherwise we check all criterias
            if ($_matchResult == true && $_previousMatchResult == true) {
                $_criteriaAll = true;
            } else {
                $_criteriaAll = false;
            }

            // break right here if our criteria is to match just one rule
            // break right here if our criteria is to match all and match result is false
            if (($_criteriaAny == true && $this->GetMatchType() == self::RULE_MATCHANY) ||
                ($_criteriaAll == false && $this->GetMatchType() == self::RULE_MATCHALL)) {
                break;
            }

            $_previousMatchResult = $_criteriaAll;
        }

        // We always set extended or to true if there were no OR criteria fields
        if ($_extendedORCount == 0) {
            $_extendedCriteriaOR = true;
        }

        // We also set extended and to true if there were no AND criteria fields
        if ($_extendedANDCount == 0) {
            $_extendedCriteriaAND = true;
        }

        //echo 'EXTENDAND: ' . $_extendedCriteriaAND . '<br/>';
        //echo 'EXTENDOR: ' . $_extendedCriteriaOR . '<br/>';

        if (($this->GetMatchType() == self::RULE_MATCHANY && $_criteriaAny == true) || ($this->GetMatchType() == self::RULE_MATCHALL && $_criteriaAll == true) ||
            ($this->GetMatchType() == self::RULE_MATCHEXTENDED && $_extendedCriteriaAND == true && $_extendedCriteriaOR == true)) {
            return true;
        }

        return false;
    }

    /**
     * Converts the Criteria Array to Javascript Representation
     *
     * @author Varun Shoor
     * @param array $_criteria The Criteria Pointer
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CriteriaPointerToJavaScriptArray($_criteria)
    {
        if (!_is_array($_criteria)) {
            return false;
        }

        $_jsData = '<script language="Javascript" type="text/javascript">criteriaStore = new Object();';

        $_index = 0;
        foreach ($_criteria as $key => $val) {
            if (isset($val['optgroup']) && $val['optgroup'] == true) {
                $_jsData .= 'criteriaStore["' . addslashes($key) . '"] = {"key": "' . addslashes($key) . '", "title": "' . htmlspecialchars(addslashes(StripName($val['title'], 40))) . '", "optgroup": true};';
            } else {
                $_jsData .= 'criteriaStore["' . addslashes($key) . '"] = {"key": "' . addslashes($key) . '", "title": "' . htmlspecialchars(addslashes(StripName($val['title'], 40))) . '", "desc": "' . addslashes($val['desc']) . '", "op": "' . addslashes($val['op']) . '", "field": "' . htmlspecialchars($val['field']) . '", "fieldcontents": {';
                if (isset($val['fieldcontents']) && _is_array($val['fieldcontents'])) {
                    $_subData = array();
                    foreach ($val['fieldcontents'] as $fieldKey => $fieldVal) {
                        $_subData[] = $fieldKey . ': {"title": "' . addslashes(StripName($fieldVal['title'], 40)) . '", "contents": "' . $fieldVal['contents'] . '"}';
                    }

                    if (count($_subData)) {
                        $_jsData .= implode(', ', $_subData);
                    }
                }
                $_jsData .= '}};';
            }

            $_index++;
        }
        $_jsData .= '</script>';

        echo $_jsData;

        return true;
    }

    /**
     * Converts the Rule Criteria & Action Pointer to JavaScript representation
     *
     * @author Varun Shoor
     * @param array $_ruleCriteria The Criteria Pointer
     * @param string|bool $_extendedValue The Extended Action JS Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CriteriaActionsPointerToJavaScript($_ruleCriteria, $_extendedValue = false)
    {
        $_returnValue = '<script language="Javascript" type="text/javascript">QueueFunction(function(){';
        if (_is_array($_ruleCriteria)) {
            foreach ($_ruleCriteria as $_key => $_val) {
                if (!isset($_val[2])) {
                    $_val[2] = '';
                }

                $_hasExtendedMatch = '0';
                $_extendedMatch = self::RULE_MATCHALL;
                if (isset($_val[3]) && ($_val[3] == self::RULE_MATCHALL || $_val[3] == self::RULE_MATCHANY)) {
                    $_hasExtendedMatch = '1';
                    $_extendedMatch = $_val[3];
                }

                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-2304 If we are specifying backslashes with bracket expression([,]) while creating regex in the help desk, backslashes are getting removed
                 *
                 */
                $_returnValue .= 'newGlobalRuleCriteria("' . $_val[0] . '", "' . $_val[1] . '", "' . htmlspecialchars(addslashes($_val[2])) . '", "' . $_hasExtendedMatch . '", "' . $_extendedMatch . '");';
            }
        }

        if ($_extendedValue) {
            $_returnValue .= $_extendedValue;
        }

        $_returnValue .= '});</script>';

        echo $_returnValue;
    }

    /**
     * Gets the textual representation of comparison operator
     *
     * @author Varun Shoor
     * @param string $_op Comparison Operator
     * @return bool "true" on Success, "false" otherwise
     */
    public static function GetOperText($_op)
    {
        $_SWIFT = SWIFT::GetInstance();

        if ($_op == self::OP_EQUAL) {
            return $_SWIFT->Language->Get('opequal');
        } elseif ($_op == self::OP_NOTEQUAL) {
            return $_SWIFT->Language->Get('opnotequal');
        } elseif ($_op == self::OP_REGEXP) {
            return $_SWIFT->Language->Get('opregexp');
        } elseif ($_op == self::OP_CONTAINS) {
            return $_SWIFT->Language->Get('opcontains');
        } elseif ($_op == self::OP_NOTCONTAINS) {
            return $_SWIFT->Language->Get('opnotcontains');
        } elseif ($_op == self::OP_GREATER) {
            return $_SWIFT->Language->Get('opgreater');
        } elseif ($_op == self::OP_LESS) {
            return $_SWIFT->Language->Get('opless');
        } elseif ($_op == self::OP_CHANGED) {
            return $_SWIFT->Language->Get('opchanged');
        } elseif ($_op == self::OP_CHANGEDFROM) {
            return $_SWIFT->Language->Get('opchangedfrom');
        } elseif ($_op == self::OP_CHANGEDTO) {
            return $_SWIFT->Language->Get('opchangedto');
        } elseif ($_op == self::OP_NOTCHANGED) {
            return $_SWIFT->Language->Get('opnotchanged');
        } elseif ($_op == self::OP_NOTCHANGEDFROM) {
            return $_SWIFT->Language->Get('opnotchangedfrom');
        } elseif ($_op == self::OP_NOTCHANGEDTO) {
            return $_SWIFT->Language->Get('opnotchangedto');
        }
    }

    /**
     * Checks the criteria using oper and value and returns a bool value
     *
     * @author Varun Shoor
     * @param int $_oper The Comparison Operator
     * @param string $_value The Value
     * @param mixed $_result Array/String Value Container
     * @param string|false $_fieldType (OPTIONAL) The Criteria Type
     * @param array|bool $_changeContainer (OPTIONAL) The Change Container
     * @return bool "true" on Success, "false" otherwise
     */
    public static function CheckCriteria($_oper, $_value, $_result, $_fieldType = false, $_changeContainer = false)
    {
        $_dateRangeContainer = $_dateRangeEnabled = $_calendarEnabled = false;
        if ($_fieldType == 'daterange' || $_fieldType == 'daterangeforward') {
            $_dateRangeContainer = self::GetDateRange($_value);

            $_dateRangeEnabled = true;
        } elseif ($_fieldType == 'cal') {
            $_calendarEnabled = true;

            $_value = GetCalendarDateline($_value);
        }

        switch ($_oper) {
            case self::OP_EQUAL:
                if (is_array($_result)) {
                    foreach ($_result as $key => $val) {
                        if ($_dateRangeEnabled) {
                            // Greater than minimum range value and less than maximum range value
                            if ($val >= $_dateRangeContainer[0] && $val <= $_dateRangeContainer[1]) {
                                return true;
                            }
                        } else {
                            if ($_value == $val) {
                                return true;
                            }
                        }
                    }

                    return false;
                } else {
                    if ($_dateRangeEnabled) {
                        // Greater than minimum range value and less than maximum range value
                        if ($_result >= $_dateRangeContainer[0] && $_result <= $_dateRangeContainer[1]) {
                            return true;
                        }
                    } elseif ($_value == $_result) {
                        return true;
                    }
                }

                return false;
                break;

            case self::OP_NOTEQUAL:
                if (is_array($_result)) {
                    $_checkResult = false;

                    foreach ($_result as $key => $val) {
                        if ($_dateRangeEnabled) {
                            // Greater than minimum range value and less than maximum range value
                            if ($val >= $_dateRangeContainer[0] && $val <= $_dateRangeContainer[1]) {
                                $_checkResult = false;
                            } else {
                                $_checkResult = true;
                            }
                        } else {
                            if ($_value != $val) {
                                $_checkResult = true;
                            } else {
                                $_checkResult = false;
                            }
                        }
                    }

                    return $_checkResult;
                } else {
                    if ($_dateRangeEnabled) {
                        // Greater than minimum range value and less than maximum range value
                        if ($_result >= $_dateRangeContainer[0] && $_result <= $_dateRangeContainer[1]) {
                            return false;
                        } else {
                            return true;
                        }
                    } elseif ($_value != $_result) {
                        return true;
                    }
                }

                return false;
                break;

            case self::OP_REGEXP:
                if (is_array($_result)) {
                    foreach ($_result as $key => $val) {
                        if (@preg_match($_value, $val)) {
                            return true;
                        }
                    }
                } elseif (@preg_match($_value, $_result)) {
                    return true;
                }

                return false;
                break;

            case self::OP_CONTAINS:
                if (_is_array($_result)) {
                    foreach ($_result as $key => $val) {
                        if (@stristr($val, $_value)) {
                            return true;
                        }
                    }
                } elseif (@stristr($_result, $_value)) {
                    return true;
                }

                return false;
                break;

            case self::OP_NOTCONTAINS:
                /*
                 * BUG FIX - Varun Shoor
                 *
                 * SWIFT-1899 [Warning]: stristr() [<a href='function.stristr'>function.stristr</a>]: Empty delimiter (Rules/class.SWIFT_Rules.php:523)
                 *
                 */
                if (empty($_value)) {
                    return false;
                    break;
                }

                if (is_array($_result)) {
                    $_checkResult = false;
                    foreach ($_result as $key => $val) {
                        if (!stristr($val, $_value)) {
                            $_checkResult = true;
                        } else {
                            $_checkResult = false;
                        }
                    }

                    return $_checkResult;
                } elseif (!stristr($_result, $_value)) {
                    return true;
                }

                return false;
                break;

            case self::OP_GREATER:
                if (is_array($_result)) {
                    foreach ($_result as $key => $val) {
                        if ((int)($val) > (int)($_value)) {
                            return true;
                        }
                    }

                    return false;
                } elseif ((int)($_result) > (int)($_value)) {
                    return true;
                }

                return false;
                break;

            case self::OP_LESS:
                if (is_array($_result)) {
                    $_checkResult = false;
                    foreach ($_result as $key => $val) {
                        if ((int)($val) < (int)($_value)) {
                            $_checkResult = true;
                        } else {
                            $_checkResult = false;
                        }
                    }

                    return $_checkResult;
                } elseif ((int)($_result) < (int)($_value)) {
                    return true;
                }

                return false;
                break;

            case self::OP_CHANGED:
                if (_is_array($_changeContainer)) {
                    return true;
                }

                return false;
                break;

            case self::OP_NOTCHANGED:
                if (!_is_array($_changeContainer)) {
                    return true;
                }

                return false;
                break;

            case self::OP_CHANGEDFROM:
                if (_is_array($_changeContainer) && $_changeContainer[0] == $_value) {
                    return true;
                }

                return false;
                break;

            case self::OP_NOTCHANGEDFROM:
                if (!_is_array($_changeContainer) || (_is_array($_changeContainer) && $_changeContainer[0] != $_value)) {
                    return true;
                }

                return false;
                break;

            case self::OP_CHANGEDTO:
                if (_is_array($_changeContainer) && $_changeContainer[1] == $_value) {
                    return true;
                }

                return false;
                break;

            case self::OP_NOTCHANGEDTO:
                if (!_is_array($_changeContainer) || (_is_array($_changeContainer) && $_changeContainer[1] != $_value)) {
                    return true;
                }

                return false;
                break;
        }
    }

    /**
     * Build the SQL Search String
     *
     * @author Varun Shoor
     * @param string $_fieldName The Field Name
     * @param int|string $_oper The Operator
     * @param int|string $_value The Value
     * @return mixed "_sql" (STRING) on Success, "false" otherwise
     */
    public static function BuildSQL($_fieldName, $_oper, $_value)
    {
        $_SWIFT = SWIFT::GetInstance();

        $_prefix = '';
        $_suffix = '';

        if ($_oper == self::OP_NOTEQUAL) {
            // Not Equal
            $_prefix = " != '";
            $_suffix = "'";
        } elseif ($_oper == self::OP_CONTAINS) {
            $_prefix = " LIKE '%";
            $_suffix = "%'";
        } elseif ($_oper == self::OP_NOTCONTAINS) {
            $_prefix = " NOT LIKE '%";
            $_suffix = "%'";
        } elseif ($_oper == self::OP_GREATER) {
            $_prefix = " > '";
            $_suffix = "'";
        } elseif ($_oper == self::OP_LESS) {
            $_prefix = " < '";
            $_suffix = "'";
        } else {
            // Equal
            $_prefix = " = '";
            $_suffix = "'";
        }

        $_sql = $_fieldName . $_prefix . $_SWIFT->Database->Escape($_value) . $_suffix;

        return $_sql;
    }

    /**
     * Builds the relevant SQL for the date range
     *
     * @author Varun Shoor
     * @param mixed $_value The Date Range Value
     * @return array array(minrange, maxrange)
     */
    public static function GetDateRange($_value)
    {
        $_minRange = $_maxRange = 0;

        switch ($_value) {
            case self::DATERANGE_TODAY:
                $_minRange = SWIFT_Date::FloorDate(DATENOW);
                $_maxRange = SWIFT_Date::CeilDate(DATENOW);

                break;

            case self::DATERANGE_YESTERDAY:
                $_minRange = SWIFT_Date::FloorDate(strtotime('-1 day'));
                $_maxRange = SWIFT_Date::CeilDate($_minRange);

                break;

            case self::DATERANGE_TOMORROW:
                $_minRange = SWIFT_Date::FloorDate(strtotime('+1 day'));
                $_maxRange = SWIFT_Date::CeilDate($_minRange);

                break;

            case self::DATERANGE_CURRENTWEEKTODATE:
                // date(w) = current day offset from Sunday, the first day of the week.
                $_minRange = SWIFT_Date::FloorDate(DATENOW - (SWIFT_Date::DATE_ONE_DAY * (int)date('w')));
                $_maxRange = DATENOW;

                break;

            case self::DATERANGE_NEXTWEEKFROMDATE:
                // date(w) = current day offset from Sunday, the first day of the week.
                $_minRange = DATENOW;
                $_maxRange = SWIFT_Date::FloorDate(DATENOW + (SWIFT_Date::DATE_ONE_DAY * (7 - (int)date('w'))));

                break;

            case self::DATERANGE_LAST7DAYS:
                $_minRange = SWIFT_Date::FloorDate(strtotime('-1 week'));
                $_maxRange = DATENOW;

                break;

            case self::DATERANGE_NEXT7DAYS:
                $_minRange = DATENOW;
                $_maxRange = SWIFT_Date::FloorDate(strtotime('+1 week'));

                break;

            case self::DATERANGE_CURRENTMONTHTODATE:
                $_minRange = SWIFT_Date::FirstOfTheMonth(DATENOW);
                $_maxRange = DATENOW;

                break;

            case self::DATERANGE_NEXTMONTHFROMDATE:
                $_minRange = DATENOW;
                $_maxRange = mktime(24, 0, 0, (int)date('m', $_minRange), (int)date('t', $_minRange), (int)date('Y', $_minRange));

                break;

            case self::DATERANGE_LAST30DAYS:
                $_minRange = SWIFT_Date::FloorDate(strtotime('-1 month'));
                $_maxRange = DATENOW;

                break;

            case self::DATERANGE_NEXT30DAYS:
                $_minRange = DATENOW;
                $_maxRange = SWIFT_Date::FloorDate(strtotime('+1 month'));

                break;

            case self::DATERANGE_LAST90DAYS:
                $_minRange = SWIFT_Date::FloorDate(strtotime('-3 month'));
                $_maxRange = DATENOW;

                break;

            case self::DATERANGE_NEXT90DAYS:
                $_minRange = DATENOW;
                $_maxRange = SWIFT_Date::FloorDate(strtotime('+3 month'));

                break;

            case self::DATERANGE_LAST180DAYS:
                $_minRange = SWIFT_Date::FloorDate(strtotime('-6 month'));
                $_maxRange = DATENOW;

                break;

            case self::DATERANGE_NEXT180DAYS:
                $_minRange = DATENOW;
                $_maxRange = SWIFT_Date::FloorDate(strtotime('+6 month'));

                break;

            case self::DATERANGE_CURRENTYEARTODATE:
                $_minRange = mktime(0, 0, 0, 1, 1, (int)date('Y'));
                $_maxRange = DATENOW;

                break;

            case self::DATERANGE_NEXTYEARFROMDATE:
                $_minRange = DATENOW;
                $_maxRange = mktime(24, 0, 0, 12, 31, (int)date('Y'));

                break;

            case self::DATERANGE_LAST365DAYS:
                $_minRange = SWIFT_Date::FloorDate(strtotime('-1 year'));
                $_maxRange = DATENOW;

                break;

            case self::DATERANGE_NEXT365DAYS:
                $_minRange = DATENOW;
                $_maxRange = SWIFT_Date::FloorDate(strtotime('+1 year'));

                break;

            default:
                break;
        }

        return array($_minRange, $_maxRange);
    }

    /**
     * Builds the relevant SQL for the date range
     *
     * @author Varun Shoor
     * @param string $_fieldName The Field Name
     * @param mixed $_value The Date Range Value
     * @return mixed "_finalSQL" (STRING) on Success, "false" otherwise
     */
    public static function BuildSQLDateRange($_fieldName, $_value)
    {
        $_minRange = $_maxRange = 0;

        $_rangeContainer = self::GetDateRange($_value);
        $_minRange = $_rangeContainer[0];
        $_maxRange = $_rangeContainer[1];

        $_finalSQL = "(" . $_fieldName . " > '" . (int)($_minRange) . "' AND " . $_fieldName . " < '" . (int)($_maxRange) . "')";

        return $_finalSQL;
    }

    /**
     * Check to see if its a valid oper type
     *
     * @author Varun Shoor
     * @param mixed $_operType The Oper Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidOper($_operType)
    {
        if ($_operType == self::OP_EQUAL || $_operType == self::OP_NOTEQUAL || $_operType == self::OP_REGEXP || $_operType == self::OP_CONTAINS || $_operType == self::OP_NOTCONTAINS ||
            $_operType == self::OP_GREATER || $_operType == self::OP_LESS || $_operType == self::OP_CHANGED || $_operType == self::OP_CHANGEDFROM || $_operType == self::OP_CHANGEDTO ||
            $_operType == self::OP_NOTCHANGED || $_operType == self::OP_NOTCHANGEDFROM || $_operType == self::OP_NOTCHANGEDTO) {
            return true;
        }

        return false;
    }

    /**
     * Build one day date range (Fix for one day date check insead of equal to)
     *
     * @author Mahesh Salaria
     * @param string $_fieldName The Field Name
     * @param string $_oper The Operator
     * @param mixed $_value The Date Range Value
     * @return mixed "_finalSQL" (STRING) on Success, "false" otherwise
     */
    public static function BuildOneDayDateRange($_fieldName, $_oper, $_value)
    {
        $_minRange = $_maxRange = 0;

        $_minRange = SWIFT_Date::FloorDate($_value);
        $_maxRange = SWIFT_Date::CeilDate($_value);
        $_prefix = $_suffix = $_statementPrefix = '';

        /*
         * BUG FIX - Ashish Kataria
         *
         * SWIFT-2578 Creation Date ('not equal') criteria in Advanced search and Filter does not give correct results
         *
         */
        if ($_oper == self::OP_NOTEQUAL) {
            $_statementPrefix = '!';
        } elseif ($_oper == self::OP_GREATER) {
            $_prefix = " > '";
            $_suffix = "'";
        } elseif ($_oper == self::OP_LESS) {
            $_prefix = " < '";
            $_suffix = "'";
        }

        $_finalSQL = '';
        if (empty($_prefix) && empty($_suffix)) {
            $_finalSQL = $_statementPrefix . "(" . $_fieldName . " > '" . $_minRange . "' AND " . $_fieldName . " < '" . $_maxRange . "')";
        } else {
            $_finalSQL = $_fieldName . $_prefix . (int)($_value) . $_suffix;
        }

        return $_finalSQL;
    }
}

?>
