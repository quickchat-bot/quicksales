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

namespace Base\Library\CustomField;

use Base\Models\CustomField\SWIFT_CustomField;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_FileManager;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceTab;

/**
 * The Staff Custom Field Rendering Class
 *
 * @author Varun Shoor
 */
class SWIFT_CustomFieldRendererStaff extends SWIFT_CustomFieldRenderer
{
    /**
     * Render the Custom Fields
     *
     * @author Varun Shoor
     * @param mixed $_renderType The Render Type
     * @param mixed $_mode The INSERT/EDIT Mode
     * @param array $_groupTypeList The Group Type List
     * @param SWIFT_UserInterfaceTab $_SWIFT_UserInterfaceTabObject The Tab Object to Render in
     * @param int $_typeID (OPTIONAL) The Type ID to Load Values From
     * @param int $_departmentID (OPTIONAL) Filter by Department ID
     * @return string|bool
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Render($_renderType, $_mode, $_groupTypeList, $_SWIFT_UserInterfaceTabObject = null, $_typeID = 0, $_departmentID = 0, $_extraWidth = false, $_returnHTML = false, $_colSpan = 2)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!self::IsValidRenderType($_renderType) || !_is_array($_groupTypeList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Static is only displayed on linked fields
        if (empty($_typeID) && $_renderType == self::TYPE_STATIC) {
            return false;
        }

        $_filterByLinks = false;
        if ($_renderType == self::TYPE_STATIC) {
            $_filterByLinks = true;
        }

        $_customFieldGroupContainer = SWIFT_CustomFieldManager::RetrieveOnStaff($_groupTypeList, $_SWIFT->Staff, $_typeID, $_departmentID, $_filterByLinks);

        if (!_is_array($_customFieldGroupContainer)) {
            return false;
        }

        $_customFieldValueContainer = $_rawCustomFieldValueContainer = $_customFieldIDList = $_customFieldContainer = array();
        foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
            foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
                $_customFieldIDList[] = $_customFieldID;
                $_customFieldContainer[$_customFieldID] = $_customField;
            }
        }

        if (count($_customFieldIDList)) {
            $_customFieldValueHolder = $this->GetValues($_customFieldIDList, $_customFieldContainer, $_typeID);
            if (_is_array($_customFieldValueHolder) && count($_customFieldValueHolder) == 2) {
                $_customFieldValueContainer = $_customFieldValueHolder[0];
                $_rawCustomFieldValueContainer = $_customFieldValueHolder[1];
            }
        }

        $_renderHTML = '';
        foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
            if ($_renderType == self::TYPE_STATIC) {
                $_renderHTML .= $this->RenderCustomFieldGroupStatic($_SWIFT_UserInterfaceTabObject, $_customFieldGroup, $_customFieldValueContainer, $_rawCustomFieldValueContainer, $_extraWidth, $_returnHTML, $_colSpan);
            } elseif ($_renderType == self::TYPE_FIELDS) {
                $this->RenderCustomFieldGroup($_SWIFT_UserInterfaceTabObject, $_mode, $_customFieldGroup, $_customFieldValueContainer, $_rawCustomFieldValueContainer);
            }
        }

        /*
        $_staticCustomFieldContainer = array();

        foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup)
        {
            if ($_renderType == self::TYPE_STATIC)
            {
                $_staticCustomFieldContainer = array_merge($_staticCustomFieldContainer, $_customFieldGroup['_fields']);
            } elseif ($_renderType == self::TYPE_FIELDS) {
                $this->RenderCustomFieldGroup($_SWIFT_UserInterfaceTabObject, $_customFieldGroup, $_customFieldValueContainer, $_rawCustomFieldValueContainer);
            }
        }

        if (count($_staticCustomFieldContainer))
        {
            $this->RenderCustomFieldGroupStatic($_SWIFT_UserInterfaceTabObject, $_staticCustomFieldContainer, $_customFieldValueContainer, $_rawCustomFieldValueContainer);
        }

         */

        return $_renderHTML;
    }

    /**
     * Render the Custom Field Group (FIELD MODE)
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceTab $_SWIFT_UserInterfaceTabObject The User Interface Tab Object Pointer
     * @param mixed $_mode The INSERT/EDIT Mode
     * @param array $_customFieldGroup
     * @param array $_customFieldValueContainer The Value Container
     * @param array $_rawCustomFieldValueContainer The Raw Value Container
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderCustomFieldGroup(SWIFT_UserInterfaceTab $_SWIFT_UserInterfaceTabObject, $_mode, $_customFieldGroup, $_customFieldValueContainer,
                                              $_rawCustomFieldValueContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!count($_customFieldGroup['_fields'])) {
            return false;
        }

        $_SWIFT_UserInterfaceTabObject->Title(htmlspecialchars($_customFieldGroup['title']), 'icon_doublearrows.gif');

        foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
            $_customFieldValue = $_customField['defaultvalue'];
            if (isset($_customFieldValueContainer[$_customFieldID]) &&
                ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_TEXT || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_TEXTAREA ||
                    $_customField['fieldtype'] == SWIFT_CustomField::TYPE_PASSWORD || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_DATE ||
                    $_customField['fieldtype'] == SWIFT_CustomField::TYPE_CUSTOM || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_FILE)) {
                if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_DATE) {
                    $_customFieldValue = GetCalendarDateline($_customFieldValueContainer[$_customFieldID]);
                } else {
                    $_customFieldValue = $_customFieldValueContainer[$_customFieldID];
                }
            }

            // If its an edit mode and staff editable == 0 and no value set then reset it..
            if ($_mode == SWIFT_UserInterface::MODE_EDIT && !isset($_customFieldValueContainer[$_customFieldID]) && $_customField['staffeditable'] == '0') {
                $_customFieldValue = '';
            }

            switch ($_customField['fieldtype']) {
                case SWIFT_CustomField::TYPE_TEXT:
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], htmlspecialchars($_customFieldValue));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->Text($_customField['fieldname'], $_customField['title'], $_customField['description'], $_customFieldValue);
                    }

                    break;

                case SWIFT_CustomField::TYPE_TEXTAREA:
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], nl2br(htmlspecialchars($_customFieldValue)));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->TextArea($_customField['fieldname'], $_customField['title'], $_customField['description'], $_customFieldValue);
                    }

                    break;

                case SWIFT_CustomField::TYPE_PASSWORD:
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], htmlspecialchars($_customFieldValue));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->Password($_customField['fieldname'], $_customField['title'], $_customField['description'], $_customFieldValue, true);
                    }

                    break;

                case SWIFT_CustomField::TYPE_CHECKBOX:
                    $_checkBoxContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $this->GetStaticOptions($_customField, $_checkBoxContainer));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->CheckBoxContainerList($_customField['fieldname'], $_customField['title'], $_customField['description'], $_checkBoxContainer);
                    }

                    break;

                case SWIFT_CustomField::TYPE_RADIO:
                    $_radioContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $this->GetStaticOptions($_customField, $_radioContainer));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->Radio($_customField['fieldname'], $_customField['title'], $_customField['description'], $_radioContainer, true);
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECT:
                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $this->GetStaticOptions($_customField, $_optionsContainer));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->Select($_customField['fieldname'], $_customField['title'], $_customField['description'], $_optionsContainer);
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECTLINKED:
                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $this->GetStaticOptions($_customField, $_optionsContainer));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->SelectLinked($_customField['fieldname'], $_customField['title'], $_customField['description'], $_optionsContainer);
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECTMULTIPLE:
                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $this->GetStaticOptions($_customField, $_optionsContainer));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->SelectMultiple($_customField['fieldname'], $_customField['title'], $_customField['description'], $_optionsContainer);
                    }

                    break;

                case SWIFT_CustomField::TYPE_DATE:
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_dateValue = '';
                        if (!empty($_customFieldValue)) {
                            $_dateValue = SWIFT_Date::Get(SWIFT_Date::TYPE_DATE, (int)($_customFieldValue), false, true);
                        }

                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $_dateValue);
                    } else {
                        $_dateValue = '';
                        if (!empty($_customFieldValue)) {
                            $_dateValue = gmdate(SWIFT_Date::GetCalendarDateFormat(), (int)($_customFieldValue));
                        }

                        $_SWIFT_UserInterfaceTabObject->Date($_customField['fieldname'], $_customField['title'], $_customField['description'], $_dateValue);
                    }

                    break;

                case SWIFT_CustomField::TYPE_FILE:
                    $_fileLink = '';

                    if (!empty($_customFieldValue)) {
                        try {
                            $_SWIFT_FileManagerObject = new SWIFT_FileManager($_customFieldValue);
                            /** BUG FIX : Saloni Dhall <saloni.dhall@kayako.com>
                             *
                             * SWIFT-3164 : Custom field attachments are not visible if they have moved to cloud
                             */
                            $_fileSize = $_SWIFT_FileManagerObject->GetFileSize();

                            if ($_fileSize > 0) {
                                $_fileLink = '<div><img src="' . SWIFT::Get('themepathimages') . 'icon_file.gif" align="absmiddle" border="0" /> <a href="' . SWIFT::Get('basename') . '/Base/CustomField/Dispatch/' . $_customFieldID . '/' . $_rawCustomFieldValueContainer[$_customFieldID]['uniquehash'] . '" target="_blank">' . htmlspecialchars($_SWIFT_FileManagerObject->GetProperty('originalfilename')) . ' (' . FormattedSize($_fileSize) . ')</a></div>';
                            }
                        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                        }
                    }

                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        if (empty($_fileLink)) {
                            $_fileLink = $this->Language->Get('na');
                        }

                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $_fileLink);
                    } else {
                        $_SWIFT_UserInterfaceTabObject->File($_customField['fieldname'], $_customField['title'], $_customField['description'], 30, $_fileLink);
                    }

                    break;

                case SWIFT_CustomField::TYPE_CUSTOM:
                    $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], htmlspecialchars($_customFieldValue));

                    break;


                default:
                    break;
            }
        }

        return true;
    }

    /**
     * Render the Custom Field Group (STATIC MODE)
     *
     * @author Varun Shoor
     * @param SWIFT_UserInterfaceTab $_SWIFT_UserInterfaceTabObject The User Interface Tab Object Pointer
     * @param array $_customFieldGroup
     * @param array $_customFieldValueContainer The Value Container
     * @param array $_rawCustomFieldValueContainer The Raw Value Container
     * @return string|bool
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderCustomFieldGroupStatic($_SWIFT_UserInterfaceTabObject, $_customFieldGroup, $_customFieldValueContainer, $_rawCustomFieldValueContainer,
                                                    $_extraWidth = false, $_returnHTML = false, $_colSpan = 2)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!count($_customFieldGroup['_fields'])) {
            return false;
        }

        $_staticValueContainer = array();

        foreach ($_customFieldGroup['_fields'] as $_customFieldID => $_customField) {
            $_customFieldValue = $_customField['defaultvalue'];
            if (isset($_customFieldValueContainer[$_customFieldID]) &&
                ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_TEXT || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_TEXTAREA ||
                    $_customField['fieldtype'] == SWIFT_CustomField::TYPE_PASSWORD || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_DATE ||
                    $_customField['fieldtype'] == SWIFT_CustomField::TYPE_CUSTOM || $_customField['fieldtype'] == SWIFT_CustomField::TYPE_FILE)) {
                if ($_customField['fieldtype'] == SWIFT_CustomField::TYPE_DATE) {
                    $_customFieldValue = GetCalendarDateline($_customFieldValueContainer[$_customFieldID]);
                } else {
                    $_customFieldValue = $_customFieldValueContainer[$_customFieldID];
                }
            }

            if (in_array($_customField['fieldtype'], [
                    SWIFT_CustomField::TYPE_TEXT,
                    SWIFT_CustomField::TYPE_TEXTAREA,
                    SWIFT_CustomField::TYPE_PASSWORD,
                    SWIFT_CustomField::TYPE_DATE,
                    SWIFT_CustomField::TYPE_FILE,
                    SWIFT_CustomField::TYPE_CUSTOM,
                ]) && empty($_customFieldValue)) {
                // if the field does not have value(s), hide it
                continue;
            }

            switch ($_customField['fieldtype']) {
                case SWIFT_CustomField::TYPE_TEXT:
                    $_staticValueContainer[] = array($_customField['title'], htmlspecialchars($_customFieldValue));

                    break;

                case SWIFT_CustomField::TYPE_TEXTAREA:
                    $_staticValueContainer[] = array($_customField['title'], nl2br(htmlspecialchars($_customFieldValue)));

                    break;

                case SWIFT_CustomField::TYPE_PASSWORD:
                    $_staticValueContainer[] = array($_customField['title'], htmlspecialchars($_customFieldValue));

                    break;

                case SWIFT_CustomField::TYPE_CHECKBOX:
                    $_checkBoxContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    $staticOptions = $this->GetStaticOptions($_customField, $_checkBoxContainer);
                    if (!empty($staticOptions)) {
                        $_staticValueContainer[] = [$_customField['title'], $staticOptions];
                    }

                    break;

                case SWIFT_CustomField::TYPE_RADIO:
                    $_radioContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    $staticOptions = $this->GetStaticOptions($_customField, $_radioContainer);
                    if (!empty($staticOptions)) {
                        $_staticValueContainer[] = [$_customField['title'], $staticOptions];
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECT:
                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    $staticOptions = $this->GetStaticOptions($_customField, $_optionsContainer);
                    if (!empty($staticOptions)) {
                        $_staticValueContainer[] = [$_customField['title'], $staticOptions];
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECTLINKED:
                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    $staticOptions = $this->GetStaticOptions($_customField, $_optionsContainer);
                    if (!empty($staticOptions)) {
                        $_staticValueContainer[] = [$_customField['title'], $staticOptions];
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECTMULTIPLE:
                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    $staticOptions = $this->GetStaticOptions($_customField, $_optionsContainer);
                    if (!empty($staticOptions)) {
                        $_staticValueContainer[] = [$_customField['title'], $staticOptions];
                    }

                    break;

                case SWIFT_CustomField::TYPE_DATE:
                    $_dateValue = '';
                    if (!empty($_customFieldValue)) {
                        $_dateValue = SWIFT_Date::Get(SWIFT_Date::TYPE_DATE, (int)($_customFieldValue), false, true);
                    }

                    $_staticValueContainer[] = array($_customField['title'], htmlspecialchars($_dateValue));

                    break;

                case SWIFT_CustomField::TYPE_FILE:
                    $_fileLink = '';

                    if (!empty($_customFieldValue)) {
                        try {
                            $_SWIFT_FileManagerObject = new SWIFT_FileManager($_customFieldValue);
                            /** BUG FIX : Saloni Dhall <saloni.dhall@kayako.com>
                             *
                             * SWIFT-3164 : Custom field attachments are not visible if they have moved to cloud
                             */
                            $_fileSize = $_SWIFT_FileManagerObject->GetFileSize();

                            if ($_fileSize > 0) {
                                $_fileLink = '<div><img src="' . SWIFT::Get('themepathimages') . 'icon_file.gif" align="absmiddle" border="0" /> <a href="' . SWIFT::Get('basename') . '/Base/CustomField/Dispatch/' . $_customFieldID . '/' . $_rawCustomFieldValueContainer[$_customFieldID]['uniquehash'] . '" target="_blank">' . htmlspecialchars($_SWIFT_FileManagerObject->GetProperty('originalfilename')) . ' (' . FormattedSize($_fileSize) . ')</a></div>';
                            }
                        } catch (SWIFT_Exception $_SWIFT_ExceptionObject) {
                        }
                    }

                    $_staticValueContainer[] = array($_customField['title'], $_fileLink);

                    break;

                case SWIFT_CustomField::TYPE_CUSTOM:
                    $_staticValueContainer[] = array($_customField['title'], htmlspecialchars($_customFieldValue));

                    break;


                default:
                    break;
            }
        }

        if (!count($_staticValueContainer)) {
            return false;
        }

        $_classPrefix = '_pink';
        if ($_returnHTML == true) {
            $_classPrefix = '';
        }

        $_renderHTML = '<div class="customfieldstatic' . $_classPrefix . '"><div class="customfieldstaticcontainer' . IIF($_extraWidth == true, '2', '') . '">';
        $_renderHTML .= '<div class="customfieldstatictitle">' . htmlspecialchars($_customFieldGroup['title']) . '</div>';
        $_renderHTML .= '<div class="customfieldstaticrule' . $_classPrefix . '"></div>';
        $_renderHTML .= '<div class="customfieldstaticcontent">';
        $_renderHTML .= '<table width="100%" border="0" cellpadding="6" cellspacing="0"><tbody>';
        $_index = 0;

        foreach ($_staticValueContainer as $_valueContainer) {
            if ($_index == 0) {
                $_renderHTML .= '<tr>';
            }

            $_renderHTML .= '<td class="customfieldcol1' . $_classPrefix . '" valign="top" width="25%" align="left">' . $_valueContainer[0] . '</td>';
            $_renderHTML .= '<td class="customfieldcol2' . $_classPrefix . '" valign="top" width="25%" align="left">' . $_valueContainer[1] . '</td>';

            if ($_index == 1) {
                $_renderHTML .= '</tr>';
                $_index = 0;
            } else {
                $_index++;
            }
        }

        if ($_index == 1) {
            $_renderHTML .= '<td class="customfieldcol1' . $_classPrefix . '" width="25%">&nbsp;</td><td class="customfieldcol1' . $_classPrefix . '" width="25%">&nbsp;</td></tr>';
        }

        $_renderHTML .= '</tbody></table>';
        $_renderHTML .= '</div>';
        $_renderHTML .= '</div></div>';

        if ($_returnHTML == true) {
            return $_renderHTML;
        }

        if ($_SWIFT_UserInterfaceTabObject instanceof SWIFT_UserInterfaceTab) {
            $_rowContainer = array();
            $_rowContainer[0]['value'] = $_renderHTML;
            $_rowContainer[0]['align'] = 'left';
            $_rowContainer[0]['valign'] = 'top';
            $_rowContainer[0]['colspan'] = $_colSpan;
            $_rowContainer[0]['class'] = 'gridrowcf';
            $_SWIFT_UserInterfaceTabObject->Row($_rowContainer, 'gridrowcf');

            return true;
        }

        return $_returnHTML;
    }
}
