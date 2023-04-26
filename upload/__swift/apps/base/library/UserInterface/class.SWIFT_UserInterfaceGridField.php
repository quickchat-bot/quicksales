<?php
//=======================================
//###################################
// QuickSupport Singapore Pte. Ltd. - SWIFT Framework
//
// Source Copyright 2001QuickSupport Singapore Pte. Ltd.h Ltd.
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//                          www.kayako.com
//###################################
//=======================================

namespace Base\Library\UserInterface;

use SWIFT_Base;
use SWIFT_Exception;

/**
 * The Grid Field Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_UserInterfaceGridField extends SWIFT_Base
{
    private $_fieldName = false;
    private $_fieldType;
    private $_fieldWidth = 0;
    private $_fieldAlignment;
    private $_fieldTitle = '';
    private $_fieldSortOrder = 'asc';

    // Type
    const TYPE_DB = 1;
    const TYPE_CUSTOM = 2;
    const TYPE_ID = 3;

    // Alignment
    const ALIGN_LEFT = 1;
    const ALIGN_CENTER = 2;
    const ALIGN_RIGHT = 3;

    // Sort Order
    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_fieldName The Field Name
     * @param string $_fieldTitle The Field Title
     * @param int $_fieldType The Field Type
     * @param int $_fieldWidth The Field Width
     * @param int $_fieldAlignment The Field Alignment
     * @param string $_fieldSortOrder The Field Sort Order
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct($_fieldName, $_fieldTitle = null, $_fieldType = 0, $_fieldWidth = 0, $_fieldAlignment = 0, $_fieldSortOrder = '')
    {
        parent::__construct();

        if (!$this->SetName($_fieldName)) {
            throw new SWIFT_Exception(SWIFT_CREATEFAILED);
        }

        if (!empty($_fieldTitle)) {
            $this->SetTitle($_fieldTitle);
        } else {
            $this->SetTitle($_fieldName);
        }

        if (self::IsValidType($_fieldType)) {
            $this->SetType($_fieldType);
        } else {
            $this->SetType(self::TYPE_CUSTOM);
        }

        if ($_fieldWidth) {
            $this->SetWidth($_fieldWidth);
        }

        if (self::IsValidAlignment($_fieldAlignment)) {
            $this->SetAlignment($_fieldAlignment);
        } else {
            $this->SetAlignment(self::ALIGN_LEFT);
        }

        if (self::IsValidSortOrder($_fieldSortOrder)) {
            $this->SetSortOrder($_fieldSortOrder);
        } else {
            $this->SetSortOrder(self::SORT_ASC);
        }

        $this->SetIsClassLoaded(true);
    }

    /**
     * Set the Field Name (Should match db field name if type is set to DB)
     *
     * @author Varun Shoor
     * @param string $_fieldName The Field Name
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetName($_fieldName)
    {
        if (empty($_fieldName) || trim($_fieldName) == '') {
            return false;
        }

        $this->_fieldName = $_fieldName;

        return true;
    }

    /**
     * Retrieve the Field Name
     *
     * @author Varun Shoor
     * @return mixed "_fieldName" (STRING) on Success, "false" otherwise
     */
    public function GetName()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_fieldName;
    }

    /**
     * Checks to see if it is a valid field type
     *
     * @author Varun Shoor
     * @param int $_fieldType The Field Type
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidType($_fieldType)
    {
        if ($_fieldType == self::TYPE_CUSTOM || $_fieldType == self::TYPE_DB || $_fieldType == self::TYPE_ID) {
            return true;
        }

        return false;
    }

    /**
     * Set the Field Type
     *
     * @author Varun Shoor
     * @param int $_fieldType The Field Type
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetType($_fieldType)
    {
        if (!self::IsValidType($_fieldType)) {
            return false;
        }

        $this->_fieldType = $_fieldType;

        return true;
    }

    /**
     * Retrieve the Field Type
     *
     * @author Varun Shoor
     * @return mixed "_fieldType" (INT) on Success, "false" otherwise
     */
    public function GetType()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_fieldType;
    }

    /**
     * Set the Field Width
     *
     * @author Varun Shoor
     * @param int $_fieldWidth The Field Width
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetWidth($_fieldWidth)
    {

        if (empty($_fieldWidth)) {
            return false;
        }

        $this->_fieldWidth = $_fieldWidth;

        return true;
    }

    /**
     * Retrieve the Field Width
     *
     * @author Varun Shoor
     * @return mixed "_fieldWidth" (INT) on Success, "false" otherwise
     */
    public function GetWidth()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_fieldWidth;
    }

    /**
     * Checks to see if its a valid field alignment
     *
     * @author Varun Shoor
     * @param int $_fieldAlignment The Field Alignment
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidAlignment($_fieldAlignment)
    {
        if (!$_fieldAlignment == self::ALIGN_LEFT || $_fieldAlignment == self::ALIGN_CENTER || $_fieldAlignment == self::ALIGN_RIGHT) {
            return true;
        }

        return false;
    }

    /**
     * Set the Field Alignment
     *
     * @author Varun Shoor
     * @param int $_fieldAlignment The Field Alignment
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetAlignment($_fieldAlignment)
    {
        if (!self::IsValidAlignment($_fieldAlignment)) {
            return false;
        }

        $this->_fieldAlignment = $_fieldAlignment;

        return true;
    }

    /**
     * Retrieve the Field Alignment
     *
     * @author Varun Shoor
     * @return mixed "_fieldAlignment" (INT) on Success, "false" otherwise
     */
    public function GetAlignment()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_fieldAlignment;
    }

    /**
     * Retrieve the textual representation of the alignment
     *
     * @author Varun Shoor
     * @return mixed "_fieldAlignment" (STRING) on Success, "false" otherwise
     */
    public function GetAlignmentText()
    {
        switch ($this->GetAlignment()) {
            case self::ALIGN_LEFT:
                return 'left';
                break;

            case self::ALIGN_CENTER:
                return 'center';
                break;

            case self::ALIGN_RIGHT:
                return 'right';
                break;

            default:
                break;
        }

        return 'left';
    }

    /**
     * Set the Field Title
     *
     * @author Varun Shoor
     * @param string $_fieldTitle The Field Title
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetTitle($_fieldTitle)
    {
        if (empty($_fieldTitle) || trim($_fieldTitle) == '') {
            return false;
        }

        $this->_fieldTitle = $_fieldTitle;

        return true;
    }

    /**
     * Retrieve the Field Title
     *
     * @author Varun Shoor
     * @return mixed "_fieldTitle" (STRING) on Success, "false" otherwise
     */
    public function GetTitle()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_fieldTitle;
    }

    /**
     * Checks to see if its a valid field sort order
     *
     * @author Varun Shoor
     * @param string $_fieldSortOrder The Field Sort Order
     * @return bool "true" on Success, "false" otherwise
     */
    public static function IsValidSortOrder($_fieldSortOrder)
    {
        if ($_fieldSortOrder == self::SORT_ASC || $_fieldSortOrder == self::SORT_DESC) {
            return true;
        }

        return false;
    }

    /**
     * Set the Field Sort Order
     *
     * @author Varun Shoor
     * @param string $_fieldSortOrder The Field Sort Order
     * @return bool "true" on Success, "false" otherwise
     */
    public function SetSortOrder($_fieldSortOrder)
    {
        if (!self::IsValidSortOrder($_fieldSortOrder)) {
            return false;
        }

        $this->_fieldSortOrder = $_fieldSortOrder;

        return true;
    }

    /**
     * Get the Field Sort Order
     *
     * @author Varun Shoor
     * @return mixed "_fieldSortOrder" (STRING) on Success, "false" otherwise
     */
    public function GetSortOrder()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        return $this->_fieldSortOrder;
    }
}

?>
