<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2014, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'tabgeneral'                => 'General',
    'visibilitytype'            => 'Group visibility',
    'desc_visibilitytype'       => '<strong>Public</strong> Custom field groups are available to both staff and and end users (where applicable).<br /><strong>Private</strong> Groups are available only to staff users.',
    'insertcfgrouptitle'        => 'Custom field group (%s) created',
    'insertcfgroupmsg'          => 'The custom field group (%s) was created successfully.<br /><strong>Title:</strong> %s<br /><strong>Type:</strong> %s<br /><strong>Display order:</strong> %s',
    'updatecfgrouptitle'        => 'Custom field group (%s) updated',
    'updatecfgroupmsg'          => 'The custom field group (%s) was updated successfully.<br /><strong>Title:</strong> %s<br /><strong>Type:</strong> %s<br /><strong>Display order:</strong> %s',
    'wineditcfgroup'            => 'Edit Custom Field Group: %s',
    'titledelcfgroup'           => 'Custom field groups deleted (%d)',
    'msgdelcfgroup'             => 'The following custom field groups were deleted:<br />%s',
    'buttonnext'                => 'Next',
    'encryptindb'               => 'Encrypt field value in database',
    'desc_encryptindb'          => 'Any data inputted to this custom field will be encrypted in the database. Password fields are automatically encrypted.',
    'tabdepartments'            => 'Departments',
    'assigneddepartments'       => 'Assigned Departments',
    'titleunproc'               => 'Unable to create custom field',
    'msgunproc'                 => 'You need to create at least one custom field group to add a new custom field (custom fields belong to custom field groups).',
    'taboptions'                => 'Options',
    'tablanguages'              => 'Languages: Translation',
    'tabpermissions'            => 'Permissions',
    'cfteampermissions'         => 'Permissions: Team',
    'cfstaffpermissions'        => 'Permissions: Staff (Overrides Team Permissions)',
    'notset'                    => 'Not Set',
    'insertcfieldtitle'         => 'Custom field created (%s)',
    'insertcfieldmsg'           => 'The custom field (%s) was created successfully.<br />',
    'updatecfieldtitle'         => 'Custom field updated (%s)',
    'updatecfieldmsg'           => 'The custom field (%s) was created successfully.<br />',
    'wineditcfield'             => 'Edit Custom Field: %s',
    'titledelcfields'           => 'Custom fields deleted (%d)',
    'msgdelcfields'             => 'The following custom fields were deleted:<br />%s',

    'grouptitle'                => 'Group title',
    'desc_grouptitle'           => 'The custom field group title is displayed above any custom fields which you add to this group. For example, <em>Order information</em>.',
    'grouptype'                 => 'Custom field group location',
    'desc_grouptype'            => 'Custom field groups are bound to an area of your helpdesk - such as ticket creation, or before a live chat. Once set for a group, this cannot be changed.',
    'displayorder'              => 'Display order',
    'desc_displayorder'         => 'If there are multiple custom field groups in the same location, they will be displayed according to their display order, smallest to largest.',
    'customfields'              => 'Custom Fields',
    'managegroups'              => 'Manage Groups',
    'desc_customfieldgroups'    => '',
    'grouplist'                 => 'Custom Field Groups',
    'insertgroup'               => 'Insert Group',
    'grouptypeuser'             => 'User profile',
    'grouptypeuserorganization' => 'User organisation profile',
    'grouptypestaffticket'      => 'Staff ticket creation',
    'grouptypeuserticket'       => 'User ticket submission',
    'grouptypestaffuserticket'  => 'Staff and end user ticket creation',
    'grouptypetimetrack'        => 'Ticket time tracking/billing entry',
    'grouptypelivesupportpre'   => 'Live chat (before chat)',
    'grouptypelivesupportpost'  => 'Live chat (after chat)',
    'insertgroup'               => 'Insert Group',
    'updategroup'               => 'Update Group',
    'cfgroupinsertconfirm'      => 'Custom field group (%s) created',
    'addfield'                  => 'Add Field',
    'cfgroupdelconfirm'         => 'Custom field groups deleted',
    'cfgroupactconfirm'         => 'Are you sure you wish to delete this custom field group? Deleting a group will also delete all custom fields belonging to the group and all of their respective stored values.',
    'editgroup'                 => 'Edit Group',
    'invalidcfgroup'            => 'A problem was encountered (invalid custom field group)',
    'cfgroupupdateconfirm'      => 'Custom field group (%s) updated',
    'insertfield'               => 'Insert Field',
    'desc_customfields'         => '',
    'next'                      => 'Next &raquo;',
    'customfieldgroup'          => 'Custom field group',
    'desc_customfieldgroup'     => 'The custom field group that the this new field will belong to.',
    'fieldtype'                 => 'Field type',
    'erroraddcfgroup'           => 'ERROR: You need to create at least one custom field group to add a custom field',
    'nogroupadded'              => '-- No Custom Field Group Available --',
    'fieldtitle'                => 'Field title',
    'desc_fieldtitle'           => 'A title for the field (displayed next to the field wherever the field is present).',
    'fieldname'                 => 'Field name',
    'desc_fieldname'            => '',
    'isrequired'                => 'Required field',
    'desc_isrequired'           => 'The user will be required to complete this field if this setting is enabled.',
    'usereditable'              => 'Field editable by end users',
    'desc_usereditable'         => 'Whether a user can modify the data in this field, once entered.',
    'staffeditable'             => 'Field editable by staff',
    'desc_staffeditable'        => 'Whether staff users can modify data in this field, once entered.',
    'regexpvalidate'            => 'Pattern match',
    'desc_regexpvalidate'       => 'A regular expression pattern entered here will be used to validate data entered into this custom field.',
    'defaultvalue'              => 'Default field value',
    'desc_defaultvalue'         => 'If specified, this field will have a default value (which users can then overwrite).',
    'description'               => 'Field description',
    'desc_description'          => 'A field description can be used to provide additional information to users about what they should enter.',
    'fielddisplayorder'         => 'Display order',
    'desc_fielddisplayorder'    => 'If there are multiple fields within a field group, they will be displayed according to their display order, smallest to largest.',
    'optionvalues'              => 'Field Options',
    'optiondisplayorder'        => 'Display Order',
    'optionisselected'          => 'Is Selected?',
    'cfieldinsertconfirm'       => 'Custom field (%s) created',
    'cfgroupd'                  => 'Custom field group',
    'desc_cfgroupd'             => 'The custom field group that the this new field will belong to.',

    // Manage Fields
    'managefields'              => 'Manage Fields',

    // Field List
    'field_text'                => 'Text',
    'desc_field_text'           => 'Standard text input field.',
    'field_textarea'            => 'Text area',
    'desc_field_textarea'       => 'Multi-line text input area.',
    'field_password'            => 'Password',
    'desc_field_password'       => 'A password input field (a standard text field, but masked).',
    'field_checkbox'            => 'Checkbox',
    'desc_field_checkbox'       => 'Check boxes (multiple selection).',
    'field_radio'               => 'Radio',
    'desc_field_radio'          => 'Radio buttons (single selection).',
    'field_select'              => 'Drop-down select',
    'desc_field_select'         => 'Drop-down list field (single selection).',
    'field_file'                => 'File',
    'desc_field_file'           => 'File upload field (single file upload).',
    'field_selectmultiple'      => 'Multiple select',
    'desc_field_selectmultiple' => 'Multi-selection list field (multiple selection).',
    'field_custom'              => 'Custom',
    'desc_field_custom'         => 'Select this field type to create a generic entry for custom fields. This type of custom field only provides a storage area for the data - you must add the corresponding HTML for a user to input the data.',
    'field_linkedselect'        => 'Linked select',
    'desc_field_linkedselect'   => 'Linked selection fields (multiple nested selection).',
    'linkedselect_usage' => 'Use the plus sign (+) to add linked sub-options for each field option. The sub-options will be displayed in a separate select field whenever a parent option is selected.',
    'field_date'                => 'Date',
    'desc_field_date'           => 'A text box with a pop-up date picker for collecting date value.',

    // Potentialy unused phrases in customfields.php
    'cfgroupdetails'            => 'Custom field group details',
    'grouptypeknowledgebase'    => 'Knowledgebase Articles',
    'grouptypenews'             => 'News Articles',
    'grouptypetroubleshooter'   => 'Troubleshooter Steps',
    'usergroups'                => 'User Groups',
    'desc_usergroups'           => 'Select the user groups that the custom field group should be available to. For example, you can have two different user groups and you may wish to have this custom field group available to only one of them.',
    'selectoneusergroup'        => 'ERROR: You need to select at least one user group',
    'selectcftype'              => 'Select Custom Field Type',
    'insertfieldstep1'          => 'Insert Field: Step 1',
    'insertfieldstep2'          => 'Insert Field: Step 2',
    'fielddetails'              => 'Field Details',
    'fieldoptions'              => 'Field Options',
    'cfdepartments'             => 'Departments',
    'desc_cfdepartments'        => 'Select the department(s) that the custom field is bound to. You can use this feature to display different custom fields for different departments.',
    'fieldlist'                 => 'Field List',
    'cfdelconfirm'              => 'Custom field deleted successfully',
    'cfdelconfirmmsg'           => 'Are you sure you wish to delete this custom field? Deleting a custom field will result in the permanent deletion of the field and all its respective stored values.  This action is irreversible!',
    'invalidcustomfield'        => 'Invalid Custom Field',
    'editcfield'                => 'Edit Custom Field',
    'updatefield'               => 'Update Field',
    'cfieldupdateconfirm'       => 'Custom field "%s" updated successfully',
);

return $__LANG;
