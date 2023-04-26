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

namespace Base\Library\Attachment;

use Base\Library\Attachment\SWIFT_Attachment_Exception;
use Base\Models\Attachment\SWIFT_Attachment;
use Base\Library\Rules\SWIFT_Rules;

/**
 * The Purge Attachments Rule Handling Class
 *
 * @author Varun Shoor
 */
class SWIFT_AttachmentRule extends SWIFT_Rules
{
    // Criteria
    const ATTACHMENT_FILENAME = 'filename';
    const ATTACHMENT_SIZE = 'size';
    const ATTACHMENT_DATE = 'dateline';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct(array(), SWIFT_Rules::RULE_MATCHALL);
    }

    /**
     * Retrieve the Criteria Pointer
     *
     * @author Varun Shoor
     * @return array The Criteria Pointer
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetCriteriaPointer()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_criteriaPointer = array();

        $_criteriaPointer[self::ATTACHMENT_FILENAME]['title'] = $this->Language->Get('pa' . self::ATTACHMENT_FILENAME);
        $_criteriaPointer[self::ATTACHMENT_FILENAME]['desc'] = $this->Language->Get('desc_pa' . self::ATTACHMENT_FILENAME);
        $_criteriaPointer[self::ATTACHMENT_FILENAME]['op'] = 'string';
        $_criteriaPointer[self::ATTACHMENT_FILENAME]['field'] = 'text';

        $_criteriaPointer[self::ATTACHMENT_SIZE]['title'] = $this->Language->Get('pa' . self::ATTACHMENT_SIZE);
        $_criteriaPointer[self::ATTACHMENT_SIZE]['desc'] = $this->Language->Get('desc_pa' . self::ATTACHMENT_SIZE);
        $_criteriaPointer[self::ATTACHMENT_SIZE]['op'] = 'int';
        $_criteriaPointer[self::ATTACHMENT_SIZE]['field'] = 'int';

        $_criteriaPointer[self::ATTACHMENT_DATE]['title'] = $this->Language->Get('pa' . self::ATTACHMENT_DATE);
        $_criteriaPointer[self::ATTACHMENT_DATE]['desc'] = $this->Language->Get('desc_pa' . self::ATTACHMENT_DATE);
        $_criteriaPointer[self::ATTACHMENT_DATE]['op'] = 'cal';
        $_criteriaPointer[self::ATTACHMENT_DATE]['field'] = 'cal';

        return $_criteriaPointer;;
    }

    /**
     * Retrieve the Attachment ID List Based on the Specific Criteria's
     *
     * @author Varun Shoor
     * @param int $_linkType
     * @param array $_criteriaContainer The Criteria Container
     * @param mixed $_ruleMatch The Rule Match Type
     * @return array
     * @throws SWIFT_Attachment_Exception If the Class is not Loaded
     */
    public function GetAttachmentIDList($_linkType, $_criteriaContainer, $_ruleMatch)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Attachment_Exception(SWIFT_CLASSNOTLOADED);
        } elseif ($_linkType != 0 && !SWIFT_Attachment::IsValidLinkType($_linkType)) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        } elseif (!_is_array($_criteriaContainer) || empty($_ruleMatch) || ($_ruleMatch != SWIFT_Rules::RULE_MATCHALL &&
                $_ruleMatch != SWIFT_Rules::RULE_MATCHANY)) {
            throw new SWIFT_Attachment_Exception(SWIFT_INVALIDDATA);
        }

        $_oper = " AND ";
        if ($_ruleMatch == SWIFT_Rules::RULE_MATCHALL) {
            $_oper = " AND ";
        } elseif ($_ruleMatch == SWIFT_Rules::RULE_MATCHANY) {
            $_oper = " OR ";
        }

        $_sqlContainer = array();
        $_linkTypeSQLClause = null;

        if (!empty($_linkType)) {
            $_linkTypeSQLClause = self::BuildSQL('linktype', '=', $_linkType) . ' AND ';
        }

        foreach ($_criteriaContainer as $_val) {
            if (!isset($_val[0]) || !isset($_val[1]) || !isset($_val[2])) {
                continue;
            }

            switch ($_val[0]) {
                case self::ATTACHMENT_FILENAME:
                    $_sqlContainer[] = self::BuildSQL('filename', $_val[1], $_val[2]);

                    break;

                case self::ATTACHMENT_SIZE;
                    $_sqlContainer[] = self::BuildSQL('filesize', $_val[1], ((int)($_val[2]) * 1024));

                    break;

                case self::ATTACHMENT_DATE:
                    $_sqlContainer[] = self::BuildSQL('dateline', $_val[1], IIF(!empty($_val[2]), GetCalendarDateline($_val[2]), DATENOW));

                    break;
            }
        }

        $_attachmentIDList = array();

        if (count($_sqlContainer)) {
            $this->Database->Query('SELECT attachmentid FROM ' . TABLE_PREFIX . 'attachments WHERE ' . $_linkTypeSQLClause . ' (' . implode($_oper, $_sqlContainer) . ')');
            while ($this->Database->NextRecord()) {
                $_attachmentIDList[] = $this->Database->Record['attachmentid'];
            }

        }

        return $_attachmentIDList;
    }
}

?>
