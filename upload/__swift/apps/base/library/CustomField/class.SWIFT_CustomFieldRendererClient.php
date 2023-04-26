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

use Base\Library\CustomField\SWIFT_CustomFieldManager;
use Base\Library\CustomField\SWIFT_CustomFieldRenderer;
use Base\Models\CustomField\SWIFT_CustomField;
use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use SWIFT_FileManager;
use Base\Library\Language\SWIFT_LanguagePhraseLinked;
use Base\Library\UserInterface\SWIFT_UserInterface;

/**
 * The Staff Custom Field Rendering Class
 *
 * @author Varun Shoor
 */
class SWIFT_CustomFieldRendererClient extends SWIFT_CustomFieldRenderer
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('Language:LanguagePhraseLinked', [], true, false, 'base');
    }

    /**
     * Render the Custom Fields
     *
     * @author Varun Shoor
     * @param mixed $_renderType The Render Type
     * @param mixed $_mode The INSERT/EDIT Mode
     * @param array $_groupTypeList The Group Type List
     * @param int $_typeID (OPTIONAL) The Type ID to Load Values From
     * @param int $_departmentID (OPTIONAL) Filter by Department ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function Render($_renderType, $_mode, $_groupTypeList, $_typeID = 0, $_departmentID = 0)
    {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        } elseif (!self::IsValidRenderType($_renderType) || !_is_array($_groupTypeList)) {
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

        $_customFieldGroupContainer = SWIFT_CustomFieldManager::Retrieve($_groupTypeList, $_typeID, $_departmentID, $_filterByLinks);

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

        $_templateDataContainer = array();

        foreach ($_customFieldGroupContainer as $_customFieldGroupID => $_customFieldGroup) {
            /*
             * BUG FIX - Werner Garcia <werner.garcia@crossover.com>
             *
             * KAYAKOC-3230: Extra spacing in 'Submit a Ticket' page in HelpCentre
             *
             */
            // check if there are fields, so empty groups are not rendered
            if (count($_customFieldGroup['_fields']) > 0) {
                $_templateDataContainer[$_customFieldGroupID] =
                    $this->RenderCustomFieldGroup($_mode, $_renderType, $_customFieldGroup,
                        $_customFieldValueContainer, $_rawCustomFieldValueContainer);
                if (count($_templateDataContainer[$_customFieldGroupID]) === 0) {
                    // do not render an empty group
                    unset($_templateDataContainer[$_customFieldGroupID]);
                }
            }
        }

        $this->Template->Assign('_customFields', $_templateDataContainer);

        return true;
    }

    /**
     * Render the Custom Field Group (FIELD MODE)
     *
     * @author Varun Shoor
     * @param mixed $_mode The INSERT/EDIT Mode
     * @param mixed $_renderType The Render Type
     * @param array $_customFieldGroup
     * @param array $_customFieldValueContainer The Value Container
     * @param array $_rawCustomFieldValueContainer The Raw Value Container
     * @return array|bool The Data Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function RenderCustomFieldGroup($_mode, $_renderType, $_customFieldGroup, $_customFieldValueContainer, $_rawCustomFieldValueContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!count($_customFieldGroup['_fields'])) {
            return false;
        }

        $_customFieldGroupTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_CUSTOMFIELDGROUP, $_customFieldGroup['customfieldgroupid']);
        if (!empty($_customFieldGroupTitleLanguage)) {
            $_customFieldGroup['title'] = $_customFieldGroupTitleLanguage;
        }

        $_dataContainer = array();
        $_dataContainer['title'] = htmlspecialchars($_customFieldGroup['title']);
        $_dataContainer['_fields'] = array();

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

            // If its an edit mode and user editable == 0 and no value set then reset it..
            if ($_mode == SWIFT_UserInterface::MODE_EDIT && !isset($_customFieldValueContainer[$_customFieldID]) && $_customField['usereditable'] == '0') {
                $_customFieldValue = '';
            }

            $_dataContainer['_fields'][$_customFieldID] = $_customField;
            $_dataContainer['_fields'][$_customFieldID]['valuetype'] = 'static';
            $_dataContainer['_fields'][$_customFieldID]['fieldvalue'] = '';
            $_dataContainer['_fields'][$_customFieldID]['fieldvaluelinked'] = false;
            $_dataContainer['_fields'][$_customFieldID]['fieldtype'] = 'hidden';

            $_customFieldTitleLanguage = $this->Language->GetLinked(SWIFT_LanguagePhraseLinked::TYPE_CUSTOMFIELD, $_customFieldID);
            if (!empty($_customFieldTitleLanguage)) {
                $_dataContainer['_fields'][$_customFieldID]['title'] = $_customFieldTitleLanguage;
            }

            $_data = &$_dataContainer['_fields'][$_customFieldID];

            switch ($_customField['fieldtype']) {
                case SWIFT_CustomField::TYPE_TEXT:
                    $_data['fieldtype'] = 'text';

                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        $_data['fieldvalue'] = htmlspecialchars($_customFieldValue);
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = htmlspecialchars($_customFieldValue);
                    }

                    break;

                case SWIFT_CustomField::TYPE_TEXTAREA:
                    $_data['fieldtype'] = 'textarea';

                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        $_data['fieldvalue'] = nl2br(htmlspecialchars($_customFieldValue));
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = htmlspecialchars($_customFieldValue);
                    }

                    break;

                case SWIFT_CustomField::TYPE_PASSWORD:
                    $_data['fieldtype'] = 'password';

                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        $_data['fieldvalue'] = htmlspecialchars($_customFieldValue);
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = htmlspecialchars($_customFieldValue);
                    }

                    break;

                case SWIFT_CustomField::TYPE_CHECKBOX:
                    $_data['fieldtype'] = 'checkbox';

                    $_checkBoxContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        $_data['fieldvalue'] = $this->GetStaticOptions($_customField, $_checkBoxContainer,
                            (bool)$_customField['usereditable']);
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = $this->EscapeOptions($_checkBoxContainer);
                    }

                    break;

                case SWIFT_CustomField::TYPE_RADIO:
                    $_data['fieldtype'] = 'radio';

                    $_radioContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        $_data['fieldvalue'] = $this->GetStaticOptions($_customField, $_radioContainer,
                            (bool)$_customField['usereditable']);
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = $this->EscapeOptions($_radioContainer);
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECT:
                    $_data['fieldtype'] = 'select';

                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        $_data['fieldvalue'] = $this->GetStaticOptions($_customField, $_optionsContainer,
                            (bool)$_customField['usereditable']);
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = $this->EscapeOptions($_optionsContainer);
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECTLINKED:
                    $_data['fieldtype'] = 'selectlinked';

                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);

                    // First we build up a database of child options
                    $_parentOptionContainer = array();
                    foreach ($_optionsContainer as $_optionIndex => $_option) {
                        if (!empty($_option['parent'])) {
                            if (!isset($_parentOptionContainer[$_option['parent']])) {
                                $_parentOptionContainer[$_option['parent']] = array();
                            }

                            $_parentOptionContainer[$_option['parent']]['_options'][$_optionIndex] = $_option;
                            $_parentOptionContainer[$_option['parent']]['display'] = false;
                        }
                    }

                    // Itterate again and see if parent is selected
                    $_finalFieldValue = array();
                    foreach ($_optionsContainer as $_optionIndex => $_option) {
                        if (empty($_option['parent'])) {
                            $_finalFieldValue[$_optionIndex] = $_option;
                        }

                        if (empty($_option['parent']) && isset($_parentOptionContainer[$_option['value']]) && isset($_option['selected']) && $_option['selected'] == true) {
                            $_parentOptionContainer[$_option['value']]['display'] = true;
                        }
                    }

                    $_data['fieldvaluelinked'] = $_parentOptionContainer;

                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        $_data['fieldvalue'] = $this->GetStaticOptions($_customField, $_optionsContainer, (bool)$_customField['usereditable']);
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = $_finalFieldValue;
                    }

                    break;

                case SWIFT_CustomField::TYPE_SELECTMULTIPLE:
                    $_data['fieldtype'] = 'selectmultiple';

                    $_optionsContainer = $this->GetOptions($_customField, $_customFieldValueContainer);
                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        $_data['fieldvalue'] = $this->GetStaticOptions($_customField, $_optionsContainer,
                            (bool)$_customField['usereditable']);
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = $this->EscapeOptions($_optionsContainer);
                    }

                    break;

                case SWIFT_CustomField::TYPE_DATE:
                    $_data['fieldtype'] = 'date';

                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        if (!empty($_customFieldValue)) {
                            $_data['fieldvalue'] = SWIFT_Date::Get(SWIFT_Date::TYPE_DATE, (int)($_customFieldValue), false, true);
                        }
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = '';
                        if (!empty($_customFieldValue)) {
                            $_data['fieldvalue'] = gmdate(SWIFT_Date::GetCalendarDateFormat(), (int)($_customFieldValue));
                        }
                    }

                    break;

                case SWIFT_CustomField::TYPE_FILE:
                    $_data['fieldtype'] = 'file';

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

                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        if (empty($_fileLink)) {
                            $_fileLink = $this->Language->Get('na');
                        }

                        $_data['fieldvalue'] = $_fileLink;
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = $_fileLink;
                    }

                    break;

                case SWIFT_CustomField::TYPE_CUSTOM:
                    $_data['fieldtype'] = 'custom';

                    if ($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) {
                        $_data['fieldvalue'] = htmlspecialchars($_customFieldValue);
                    } else {
                        $_data['valuetype'] = 'field';
                        $_data['fieldvalue'] = htmlspecialchars($_customFieldValue);
                    }

                    break;


                default:
                    break;
            }

            if (($_renderType == self::TYPE_STATIC || ($_customField['usereditable'] == '0' && $_mode == SWIFT_UserInterface::MODE_EDIT)) && empty($_data['fieldvalue'])) {
                // if the field does not have value(s), remove it
                unset($_dataContainer['_fields'][$_customFieldID]);
            }
        }

        if (count($_dataContainer['_fields']) === 0) {
            // if the group does not have fields, send an empty one
            return [];
        }

        return $_dataContainer;
    }

    /**
     * Escape the Option title and values
     *
     * @author Varun Shoor
     * @param array $_optionsContainer The Options Container
     * @return array The Processed Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function EscapeOptions($_optionsContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        foreach ($_optionsContainer as $_key => $_val) {
            $_optionsContainer[$_key]['title'] = htmlspecialchars($_val['title']);
            $_optionsContainer[$_key]['value'] = htmlspecialchars($_val['value']);
        }

        return $_optionsContainer;
    }
}

?>
