<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author Werner Garcia
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2018, Kayako
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
use Tickets\Models\Workflow\SWIFT_TicketWorkflow;

/**
 * The Workflow Custom Field Rendering Class
 *
 * @author Werner Garcia
 */
class SWIFT_CustomFieldRendererWorkflow extends SWIFT_CustomFieldRenderer
{
    /**
     * Render the Custom Fields
     *
     * @author Werner Garcia
     * @param mixed $_renderType The Render Type
     * @param mixed $_mode The INSERT/EDIT Mode
     * @param array $_groupTypeList The Group Type List
     * @param SWIFT_UserInterfaceTab $_SWIFT_UserInterfaceTabObject The Tab Object to Render in
     * @param SWIFT_TicketWorkflow|null $_SWIFT_TicketWorkflowObject
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Render($_renderType, $_mode, $_groupTypeList, $_SWIFT_UserInterfaceTabObject, SWIFT_TicketWorkflow $_SWIFT_TicketWorkflowObject = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!self::IsValidRenderType($_renderType) || !_is_array($_groupTypeList)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        $_customFieldGroupContainer = SWIFT_CustomFieldManager::RetrieveOnWorkflow($_groupTypeList);

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
            $_SWIFT_UserInterfaceTabObject->Description('Auto-fill custom fields', '', 'wf_cf_title');
            $_SWIFT_UserInterfaceTabObject->Description('Select custom fields to be auto-filled', '', 'wf_cf_description', 2, false, '</td><td align="center" valign="top" width="100"><span class="tabletitle">Include</span>');

            $_customFieldValueHolder = $this->GetWorkflowValues($_customFieldIDList, $_customFieldContainer, $_SWIFT_TicketWorkflowObject);
            if (_is_array($_customFieldValueHolder) && count($_customFieldValueHolder) == 2) {
                $_customFieldValueContainer = $_customFieldValueHolder[0];
                $_rawCustomFieldValueContainer = $_customFieldValueHolder[1];
            }
        }

        foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
            if ($_renderType == self::TYPE_FIELDS) {
                $this->RenderCustomFieldGroup($_SWIFT_UserInterfaceTabObject, $_mode, $_customFieldGroup, $_customFieldValueContainer, $_rawCustomFieldValueContainer);
            }
        }

        return true;
    }

    /**
     * Render the Custom Field Group (FIELD MODE)
     *
     * @author Werner Garcia
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
                        $_SWIFT_UserInterfaceTabObject->Text($_customField['fieldname'], $_customField['title'], $_customField['description'], $_customFieldValue, 'text', 30, 0, '', '', '</td><td align="center" valign="top" width="50"><input type="checkbox" class="wf_include_check" value="1" '.(isset($_rawCustomFieldValueContainer[$_customFieldID]) && $_rawCustomFieldValueContainer[$_customFieldID]['isincluded']?'checked="checked"':'').' name="inc_'.$_customField['fieldname'].'">');
                    }

                    break;

                case SWIFT_CustomField::TYPE_TEXTAREA:
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], nl2br(htmlspecialchars($_customFieldValue)));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->TextArea($_customField['fieldname'], $_customField['title'], $_customField['description'], $_customFieldValue, 30, 3, false, '', '</td><td align="center" valign="top" width="50"><input type="checkbox" class="wf_include_check" value="1" '.(isset($_rawCustomFieldValueContainer[$_customFieldID]) && $_rawCustomFieldValueContainer[$_customFieldID]['isincluded']?'checked="checked"':'').' name="inc_'.$_customField['fieldname'].'">');
                    }

                    break;

                case SWIFT_CustomField::TYPE_PASSWORD:
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], htmlspecialchars($_customFieldValue));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->Password($_customField['fieldname'], $_customField['title'], $_customField['description'], $_customFieldValue, true, '</td><td align="center" valign="top" width="50"><input type="checkbox" class="wf_include_check" value="1" '.(isset($_rawCustomFieldValueContainer[$_customFieldID]) && $_rawCustomFieldValueContainer[$_customFieldID]['isincluded']?'checked="checked"':'').' name="inc_'.$_customField['fieldname'].'">');
                    }

                    break;

                case SWIFT_CustomField::TYPE_CHECKBOX:
                    $_checkBoxContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $this->GetStaticOptions($_customField, $_checkBoxContainer));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->CheckBoxContainerList($_customField['fieldname'], $_customField['title'], $_customField['description'], $_checkBoxContainer, false, false, '</td><td align="center" valign="top" width="50"><input type="checkbox" class="wf_include_check" value="1" '.(isset($_rawCustomFieldValueContainer[$_customFieldID]) && $_rawCustomFieldValueContainer[$_customFieldID]['isincluded']?'checked="checked"':'').' name="inc_'.$_customField['fieldname'].'">');
                    }

                    break;

                case SWIFT_CustomField::TYPE_RADIO:
                    $_radioContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $this->GetStaticOptions($_customField, $_radioContainer));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->Radio($_customField['fieldname'], $_customField['title'], $_customField['description'], $_radioContainer, true, '', '</td><td align="center" valign="top" width="50"><input type="checkbox" class="wf_include_check" value="1" '.(isset($_rawCustomFieldValueContainer[$_customFieldID]) && $_rawCustomFieldValueContainer[$_customFieldID]['isincluded']?'checked="checked"':'').' name="inc_'.$_customField['fieldname'].'">');
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECT:
                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $this->GetStaticOptions($_customField, $_optionsContainer));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->Select($_customField['fieldname'], $_customField['title'], $_customField['description'], $_optionsContainer, '', '', '', false, '', '</td><td align="center" valign="top" width="50"><input type="checkbox" class="wf_include_check" value="1" '.(isset($_rawCustomFieldValueContainer[$_customFieldID]) && $_rawCustomFieldValueContainer[$_customFieldID]['isincluded']?'checked="checked"':'').' name="inc_'.$_customField['fieldname'].'">');
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECTLINKED:
                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $this->GetStaticOptions($_customField, $_optionsContainer));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->SelectLinked($_customField['fieldname'], $_customField['title'], $_customField['description'], $_optionsContainer, '', false, '</td><td align="center" valign="top" width="50"><input type="checkbox" class="wf_include_check" value="1" '.(isset($_rawCustomFieldValueContainer[$_customFieldID]) && $_rawCustomFieldValueContainer[$_customFieldID]['isincluded']?'checked="checked"':'').' name="inc_'.$_customField['fieldname'].'">');
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECTMULTIPLE:
                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_customField['staffeditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT) {
                        $_SWIFT_UserInterfaceTabObject->DefaultDescriptionRow($_customField['title'], $_customField['description'], $this->GetStaticOptions($_customField, $_optionsContainer));
                    } else {
                        $_SWIFT_UserInterfaceTabObject->SelectMultiple($_customField['fieldname'], $_customField['title'], $_customField['description'], $_optionsContainer, 5, '', '', '', '', '</td><td align="center" valign="top" width="50"><input type="checkbox" class="wf_include_check" value="1" '.(isset($_rawCustomFieldValueContainer[$_customFieldID]) && $_rawCustomFieldValueContainer[$_customFieldID]['isincluded']?'checked="checked"':'').' name="inc_'.$_customField['fieldname'].'">');
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

                        $_SWIFT_UserInterfaceTabObject->Date($_customField['fieldname'], $_customField['title'], $_customField['description'], $_dateValue, 0, false, false, '', '</td><td align="center" valign="top" width="50"><input type="checkbox" class="wf_include_check" value="1" '.(isset($_rawCustomFieldValueContainer[$_customFieldID]) && $_rawCustomFieldValueContainer[$_customFieldID]['isincluded']?'checked="checked"':'').' name="inc_'.$_customField['fieldname'].'">');
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
                        $_SWIFT_UserInterfaceTabObject->File($_customField['fieldname'], $_customField['title'], $_customField['description'], 30, $_fileLink . '</td><td align="center" valign="top" width="50"><input type="checkbox" class="wf_include_check" value="1" '.(isset($_rawCustomFieldValueContainer[$_customFieldID]) && $_rawCustomFieldValueContainer[$_customFieldID]['isincluded']?'checked="checked"':'').' name="inc_'.$_customField['fieldname'].'">');
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
}
