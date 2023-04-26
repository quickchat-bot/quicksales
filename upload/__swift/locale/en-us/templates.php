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

$__LANG = array (

    'tabsettings' => 'Settings',
    'templates' => 'Templates',

    // Header Logos (nee Personalize)
    'personalizationerrmsg' => 'You must provide at least one header logo',
    'titlepersonalization' => 'Header logos updated',
    'msgpersonalization' => 'Your header logos have been saved. If one has been changed, you will need to refresh your page to see the changes.',
    'tabpersonalize' => 'Header Logos',
    'generalinformation' => 'General Information',
    'companyname' => 'Company Name',
    'desc_companyname' => 'The name here is used to brand the client support interface and the outgoing emails.',
    'defaultreturnemail' => 'Default Return Email Address',
    'desc_defaultreturnemail' => 'This address is used as the default "From" address in outgoing email. This address must correspond to an active email queue in order to accept customer replies.',
    'logoimages' => 'Header Images',
    'supportcenterlogo' => 'Support center header logo',
    'desc_supportcenterlogo' => 'This logo is displayed in the end-user facing support center. We recommend that the logo fit inside 150px (width) by 40px (height)',
    'stafflogo' => 'Control panel header logo',
    'desc_stafflogo' => 'This logo is displayed in the header of the control panels (top-left). The logo <em>must fit inside</em> <strong>150px</strong> (width) by <strong>24px</strong> (height).',
    'personalize' => 'Header Logos',

    // Import and export
    'tabexport' => 'Export',
    'export' => 'Export',
    'tabimport' => 'Import',
    'import' => 'Import',
    'result' => 'Result',
    'exporthistory' => 'Export template history too',
    'desc_exporthistory' => 'As well as the most recent versions of the templates, previous versions will also be exported.',
    'mergeoptions' => 'Merge Options',
    'addtohistory' => 'Maintain template revision history',
    'desc_addtohistory' => 'If the template group merge overwrites any existing templates, the overwritten templates will be preserved in the template history.',
    'titleversioncheckfail' => 'This template group is out of date',
    'msgversioncheckfail' => 'This template group could not be imported because it was generated using an older version of QuickSupport, and may be missing templates. If you would like to override the version check, enable <em>Ignore template group version</em>.',
    'importexport' => 'Import/Export',
    'exporttgroup' => 'Template group to export',
    'desc_exporttgroup' => 'The template group to be exported as an XML file.',
    'exportoptions' => 'Export type',
    'desc_exportoptions' => 'The type of templates to export.',
    'exportalltemplates' => 'Export all templates',
    'exportmodifications' => 'Export only modified templates',
    'templatefile' => 'Template group XML file',
    'desc_templatefile' => 'Select a template group XML file from your computer.',
    'createnewgroup' => '-- Create a new template group --',
    'mergewith' => 'Merge imported templates with',
    'desc_mergewith' => 'Choose whether to create a new template group using the contents of the file, or merge only the modified templates with an existing template group.',
    'ignoreversion' => 'Ignore template group version',
    'desc_ignoreversion' => 'If selected, the import file version will be ignored. It is recommended that you do not enable this option as it can result in problems in the Client Support Center.',
    'titletemplateimportfailed' => 'There was a problem with the template group file',
    'msgtemplateimportfailed' => 'The template group file uploaded could not be processed. It may contain bad data.',
    'titletgroupmerge' => 'Merged the imported template group file with %s',
    'msgtgroupmerge' => 'The template group file %s was imported and merged into template group %s successfully.',
    'titletgroupimport' => 'Template group %s imported',
    'msgtgroupimport' => 'The template group file %s was imported and template group %s created successfully.',

    // Templates
    'changegroup' => 'Switch Template Group',
    'restoretemplates' => 'Restore Templates',
    'desc_restoretemplates' => '',
    'moditgroup' => 'Template group to search',
    'desc_moditgroup' => 'Templates of the statuses selected below will and which belong to this template group be searched.',
    'tabgeneral' => 'General',
    'restoretgroup' => 'Restore templates to latest original versions: %s',
    'tabrestore' => 'Restore Templates',
    'findtemplates' => 'Find Templates',
    'titlerestoretemplates' => 'Templates restored (%d)',
    'msgrestoretemplates' => 'The following templates were restored:',
    'tabdiagnostics' => 'Diagnostics',
    'tabsearch' => 'Search Templates',
    'titletgrouprestorecat' => 'Template group category restored',
    'msgtgrouprestorecat' => 'The templates in the %s category of %s (%s) were restored successfully.',
    'expandcontract' => 'Expand/Contract',
    'tabhistory' => 'History',
    'templateversion' => 'Template version number',
    'saveasnewversion' => 'Save a new template version',
    'titletemplaterestore' => '%s restored',
    'msgtemplaterestore' => 'The template %s was restored to its original state.',
    'titletemplateupdate' => '%s updated',
    'msgtemplateupdate' => 'Changes were saved to the template %s successfully.',
    'tabedittemplate' => 'Template: %s (%s)',
    'titlenohistory' => 'No template history',
    'msgnohistory' => 'There are no previous revisions of this template, so there is nothing to display.',
    'historydescription' => 'Changes',
    'historyitemlist' => '%s: %s (%s) Notes: <em>%s</em>',
    'system' => '(System)',
    'historyitemcurrent' => '%s: <em><strong>Current</strong></em> (%s)',
    'compare' => 'Compare',
    'current' => 'Current',
    'notcurrenttemp' => 'Old Version',
    'exportdiff' => 'Export diff File',
    'tabcomparison' => 'Compare Versions',
    'changelognotes' => 'Describe your changes',
    'desc_changelognotes' => 'If you are making changes to this template, add a short note here so that you can track your changes under the <strong>History</strong> tab.',
    'none' => 'None',
    'inserttemplate' => 'Insert Template',
    'inserttgroup' => 'Template Group',
    'desc_inserttgroup' => 'Please select the Template Group for this Template.',
    'templateeditingguideline' => 'Template editing best practices',
    'desc_templateeditingguideline' => 'Using the template editor you can customize the look and feel of the support center. If a future QuickSupport update also includes changes to the same template, you will be asked to restore the template to the latest original version. This will undo your template changes and you will need to reapply them.<br><br>To minimize potential headaches, check out the <a href="https://go.opencart.com.vn/?pageid=GFIHelpDeskTemplates" target="_blank" rel="noopener noreferrer">template editing best practices guide</a> before customizing your support center.',
    'restoreconfirmaskcat' => 'Are you sure you wish to restore the Templates in this Category?\nYou cannot reverse this action; restoring the Templates might result in the loss of all UI changes you have made to existing Templates!',
    'inserttemplatetgroup' => 'Template Group',
    'inserttemplatetcategory' => 'Template Category',
    'inserttemplatename' => 'Template name',
    'desc_inserttemplatename' => 'Enter a name for the template using alphanumeric characters only. For example, <em>headertext</em> or <em>supportcenterwelcome</em>.',
    'titleinserttemplatedupe' => 'Template name already in use',
    'msginserttemplatedupe' => 'This template group already has a template with this name; please choose another.',
    'titleinserttemplatechar' => 'Template name contains invalid characters',
    'msginserttemplatechar' => 'The template name can only contain alphanumeric characters (letters and numbers).',
    'titleinserttemplate' => 'Template %s created',
    'msginserttemplate' => 'The template %s was created in the %s template group.',
    'titletemplatedel' => 'Template deleted',
    'msgtemplatedel' => 'The template %s was deleted.',

    // Template group
    'titleisenabledprob' => 'Cannot disable the default template group',
    'msgisenabledprob' => 'This template group is set as the default for the helpdesk; it cannot be disabled.',
    'useloginshare' => 'Use LoginShare to authenticate users',
    'desc_useloginshare' => 'Users who log into the helpdesk while this template group is active will be authenticated using the LoginShare API.',
    'groupusername' => 'Username',
    'desc_groupusername' => 'Enter a username to enable password protection for this template group.',
    'passwordprotection' => 'Password Protection',
    'enablepassword' => 'Enable password protection',
    'desc_enablepassword' => 'End users will be asked to enter a username and password to open the support center.',
    'password' => 'Password',
    'desc_password' => 'Enter a password to enable password protection for this template group.',
    'passwordconfirm' => 'Retype password',
    'desc_passwordconfirm' => 'Confirm the password, to avoid typos.',
    'tabsettings_tickets' => 'Settings: Tickets',
    'tabsettings_livechat' => 'Settings: Live Chat',
    'isenabled' => 'Template group is enabled',
    'desc_isenabled' => 'If a template group is disabled, it will not be active and cannot be accessed by end users.',
    'titlepwnomatch' => 'Passwords do not match',
    'msgpwnomatch' => 'The passwords entered do not match. Please try again.',
    'titleinvalidgrouptitle' => 'Template group name contains invalid characters',
    'msginvalidgrouptitle' => 'A template group name can only contain alphanumeric characters.',
    'titlegrouptitleexists' => 'Template group name already in use',
    'msggrouptitleexists' => 'Another template group is using this title. Please choose another.',
    'winedittemplategroup' => 'Edit Template Group: %s',
    'tabpermissions' => 'Permissions',
    'titletgroupupdate' => 'Template group %s updated',
    'msgtgroupupdate' => 'The template group %s was updated successfully.',
    'titletgroupinsert' => 'Template group %s created',
    'msgtgroupinsert' => 'The template group %s was created successfully.',
    'titletgroupnodel' => 'Template group could not be deleted',
    'msgtgroupnodel' => 'This master template group could not be deleted:',
    'titletgroupdel' => 'Template groups deleted (%d)',
    'msgtgroupdel' => 'The following template groups were deleted:',
    'titletgrouprestore' => 'Template groups restored (%d)',
    'msgtgrouprestore' => 'The following template groups and their templates were restored to their original states:',
    'insertemplategroup' => 'Insert Template Group',
    'tgrouptitle' => 'Template group name',
    'desc_tgrouptitle' => 'A template group name can only contain alphanumeric characters.',
    'gridtitle_companyname' => 'Organization Name',
    'companyname' => 'Organization name',
    'desc_companyname' => 'The organization name specified here will override the helpdesk default organization name.',
    'generaloptions' => 'General Options',
    'defaultlanguage' => 'Default language',
    'desc_defaultlanguage' => 'The language which the helpdesk will select by default for this template group.',
    'usergroups' => 'User Group Roles',
    'guestusergroup' => 'Guest (not logged in) user group',
    'desc_guestusergroup' => 'This user group will determine the permissions and settings for anyone visiting the support center and <strong>is not logged in</strong>.',
    'regusergroup' => 'Registered (logged in) user group',
    'desc_regusergroup' => 'This user group will determine the permissions and settings for anyone visiting the support center and <strong>is logged in</strong>.',
    'restrictgroups' => 'Restrict to registered user group',
    'desc_restrictgroups' => 'Only users belonging to the user group specified above will be able to log into the support center under this template group.',
    'copyfrom' => 'Copy templates from template group',
    'desc_copyfrom' => 'Templates from the template group selected here will be copied into this new template group.',
    'promptticketpriority' => 'User can select a ticket priority',
    'desc_promptticketpriority' => 'When creating a ticket, a user can select a ticket priority. If not, the default priority will be used.',
    'prompttickettype' => 'User can select a ticket type',
    'desc_prompttickettype' => 'When creating a ticket, a user can select a ticket type. If not, the default type will be used.',
    'tickettype' => 'Default ticket type',
    'desc_tickettype' => 'Tickets created from this template group will use this type by default.',
    'ticketstatus' => 'Default ticket status',
    'desc_ticketstatus' => 'Tickets created from or replied to from this template group will be set to this status. If a user replies to a ticket that is associated with this template group, the ticket will be changed to this status.',
    'ticketpriority' => 'Default ticket priority',
    'desc_ticketpriority' => 'Tickets created from this template group will be set to this priority by default.',
    'ticketdep' => 'Default department',
    'desc_ticketdep' => 'This department will be selected by default on the <em>submit ticket</em> page in the support center of this template group.',
    'livechatdep' => 'Default department',
    'desc_livechatdep' => 'This department will be selected by default on the live chat request form of this template group.',
    'ticketsdeptitle' => '%s (Tickets)',
    'livesupportdeptitle' => '%s (Live Support)',
    'isdefault' => 'This template group is the helpdesk default',
    'desc_isdefault' => 'The default template group for a helpdesk will always be used unless another is specified.',
    'loginshare' => 'LoginShare',

    // Manage template groups
    'grouptitle' => 'Group Title',
    'glanguage' => 'Language',
    'managegroups' => 'Manage Groups',
    'templategroups' => 'Template Groups',
    'desc_templategroups' => '',
    'grouplist' => 'Group List',
    'restore' => 'Restore',
    'export' => 'Export XML',
    'restoreconfirmask' => 'Are you sure you wish to restore the templates in this group to their original state? Any modifications made to the templates will be lost.',
    'restoreconfirm' => 'The templates in group %s were restored to their original state',
    'inserttemplategroup' => 'Insert Group',
    'edittemplategroup' => 'Edit Group',

    // ======= MANAGE TEMPLATES =======
    'desc_templates' => '',
    'managetemplates' => 'Manage Templates',
    'templatetitle' => 'Templates: %s',
    'expand' => 'Expand',
    'notmodified' => 'Original',
    'modified' => 'Modified',
    'upgrade' => 'Out of date',
    'expandall' => 'Expand All',
    'jump' => 'Jump',
    'templategroup' => 'Template Group',
    'desc_templategroup' => '',
    'edittemplate' => 'Edit Template',
    'edittemplatetitle' => 'Template: %s (Group: %s)',
    'templatedata' => 'Template Contents',
    'savetemplate' => 'Save',
    'saveandreload' => 'Save &amp; Reload',
    'restore' => 'Restore',
    'templatestatus' => 'Template status',
    'desc_templatestatus' => '',
    'tstatus' => '<img src="%s" align="absmiddle" border="0" /> %s', // Switch position for RTL language
    'dateadded' => 'Last modified',
    'desc_dateadded' => '',
    'contents' => '',
    'desc_contents' => '',


    // Diagnostics
    'diagnostics' => 'Diagnostics',
    'moditgroup' => 'Template group',
    'desc_moditgroup' => 'The templates of this template group will be checked for errors.',
    'list' => 'List',
    'diagtgroup' => 'Template Group',
    'desc_diagtgroup' => '',
    'diagnose' => 'Diagnose',
    'templatename' => 'Template Name',
    'status' => 'Status',
    'compiletime' => 'Compile Time',
    'diagnosetemplategroup' => 'Diagnose templates: %s',

    // Search
    'search' => 'Search',
    'searchtemplates' => 'Search Templates',
    'query' => 'Search for',
    'desc_query' => 'Text to search the templates for.',
    'searchtgroup' => 'Search in template group',
    'desc_searchtgroup' => 'The templates in this template group will be searched.',
    'searchtemplategroup' => 'Search templates: %s',

    // Template categories
    'template_general' => 'General',
    'template_chat' => 'Live support',
    'template_troubleshooter' => 'Troubleshooter',
    'template_news' => 'News',
    'template_knowledgebase' => 'Knowledgebase',
    'template_tickets' => 'Tickets',
    'template_reports' => 'Reports',

    // Potentialy unused phrases in templates.php
    'desc_importexport' => '',
    'restoretemplatestatus' => 'Template Status',
    'restoresubmitquestion' => 'Are you sure you wish to restore the selected templates?\nThis action cannot be reversed, you will loose all modifications carried out in the selected templates.',
    'desc_diagnostics' => '',
    'desc_search' => '',
    'tabplugins' => 'Plugins',
    'ls_app' => 'LoginShare Plugin',
    'wineditls' => 'Edit LoginShare Plugin: %s',
    'invalidloginshareplugin' => 'Invalid LoginShare Plugin, Please make sure the LoginShare plugin exists in the database.',
    'lsnotitle' => 'No Settings Available',
    'lsnomsg' => 'There are no settings available for the LoginShare plugin <b>"%s"</b>.',
    'loginsharefile' => 'LoginShare XML File',
    'desc_loginsharefile' => 'Upload the LoginShare XML File',
    'titlenoelevatedls' => 'Unable to Import LoginShare XML',
    'msgnoelevatedls' => 'QuickSupport is unable to import the LoginShare XML file as it is required that you login with a staff user that has elevated rights. You can add your user to elevated right list in config/config.php file of the package.',
    'titlelsversioncheckfail' => 'Version Check Failed',
    'msglsversioncheckfail' => 'QuickSupport is unable to import the LoginShare Plugin as the plugin was created for an older version of QuickSupport',
    'titlelsinvaliduniqueid' => 'Duplicate Unique ID Error',
    'msglsinvaliduniqueid' => 'QuickSupport is unable to import the LoginShare Plugin due to a conflict in Unique ID. This usually means that the plugin has already been imported into the database.',
    'titlelsinvalidxml' => 'Invalid XML File',
    'msglsinvalidxml' => 'QuickSupport is unable to import the LoginShare Plugin as the XML file corrupt or contains invalid data.',
    'titlelsimported' => 'Imported LoginShare Plugin',
    'msglsimported' => 'QuickSupport has successfully imported the %s LoginShare Plugin.',
    'titlelsdeleted' => 'Deleted LoginShare Plugin',
    'msglsdeleted' => 'Successfully deleted the "%s" LoginShare Plugin from the database.',
    'tgroupjump' => 'Template Group: %s',
    'desc_templateversion' => '',
    'desc_changelognotes' => '',
    'desc_inserttgroup' => 'Please select the Template Group for this Template.',
    'titlelsupdate' => 'LoginShare Update',
    'msglsupdate' => 'Successfully updated "%s" LoginShare settings',
    'exporttemplates' => 'Export Templates',
    'exportxml' => 'Export XML',
    'filename' => 'Filename',
    'desc_filename' => 'Specify the Export Filename.',
    'importtemplates' => 'Import Templates',
    'importxml' => 'Import XML',
    'tgroupmergeconfirm' => 'Template Group "%s" merged with import file',
    'versioncheckfailed' => 'Version Check Failed: The uploaded template pack was created using older version of QuickSupport',
    'tgroupnewimportconfirm' => 'Template Group "%s" imported successfully',
    'templategroupdetails' => 'Template Group Details',
    'passworddontmatch' => 'ERROR: Passwords don\'t match',
    'invalidgrouptitle' => 'ERROR: Only alphanumeric characters can be used in the Template Group Title',
    'grouptitleexists' => 'ERROR: Invalid Group Title. There is another Template Group with the same title; please choose a different title.',
    'desc_loginshare' => 'Specify the LoginShare App to use to authenticate the visitors under this Template Group. Make sure you have updated the settings for this app under Templates &gt; LoginShare.',
    'groupinsertconfirm' => 'Template Group "%s" inserted successfully',
    'groupdelconfirm' => 'Template Group "%s" deleted successfully',
    'invalidgroup' => 'Invalid Template Group',
    'groupupdateconfirm' => 'Template Group "%s" updated successfully',
    'templatecategories' => 'Template Categories',
    'groupjump' => 'Group Jump',
    'legend' => 'Legend: ',
    'invalidtemplate' => 'Invalid Template',
    'generalinfo' => 'General Information',
    'preview' => 'Preview',
    'copyclipboard' => 'Copy to Clipboard',
    'templateupdateconfirm' => 'Template "%s" updated successfully',
    'templaterestoreconfirm' => 'Templates "%s" restored to original contents',
    'templatesrestoreconfirm' => '%s Templates restored to original contents',
    'clipboardconfirm' => 'The Template contents have been copied to your clipboard. You can now paste the contents in your favorite HTML editor.',
    'clipboardconfirmmoz' => 'The text to be copied has been selected. Press Ctrl+C to copy the text to the clipboard.',
    'listmodified' => 'List Modified Templates',
    'listtorestore' => 'List Templates to Restore',
    'diagnosesmarty' => 'Diagnose Smarty Template Engine Errors',
    'modifiedtemplates' => 'Modified Templates (Group: %s)',
    'listtemplates' => 'List of Templates (Group: %s)',
    'diagnoseerrors' => 'Diagnose Errors (Group: %s)',
    'searchqueryd' => 'Search Query: %s',
    'pluginlist' => 'Plugin List',
    'hostname' => 'Hostname',
    'dbname' => 'DB Name',
    'dbuser' => 'DB User',
    'dbpass' => 'DB Password',
    'tableprefix' => 'Tabe Prefix',
    'ldaphostname' => 'Active Directory Host',
    'ldapport' => 'Port (Default: 389)',
    'ldapbasedn' => 'Base DN',
    'ldaprdn' => 'RDN',
    'ldappassword' => 'Password',
    'hsphostserver' => 'Server Hostname',
    'hspport' => 'Server Port',
    'hspurl' => 'XML API URL',
    'hspconnectfail' => 'Could not connect to server. Try again later.',
    'template_parser' => 'Email Parser',
    'loginapi_modernbill' => 'ModernBill',
    'loginapi_ipb' => 'Invision Power Board',
    'loginapi_vb' => 'vBulletin',
    'loginapi_osc' => 'osCommerce',
    'loginapi_iono' => 'IONO License Manager',
    'loginapi_plexum' => 'Plexum',
    'loginapi_awbs' => 'AWBS',
    'loginapi_phpaudit' => 'PHPAudit v2',
    'loginapi_whmautopilot' => 'WHMAP v3',
    'loginapi_activedirectory' => 'Active Directory/LDAP',
    'loginapi_activedirectoryssl' => 'Active Directory/LDAP (SSL)',
    'loginapi_ticketpurchaser' => 'Ticker Purchaser',
    'loginapi_xcart' => 'X-Cart',
    'loginapi_phpbb' => 'PHPBB',
    'loginapi_smf' => 'Simple Machines Forum',
    'loginapi_mybb' => 'MyBB',
    'loginapi_xmb' => 'XMB',
    'loginapi_clientexec' => 'Clientexec',
    'loginapi_joomla' => 'Joomla CMS',
    'loginapi_hsphere' => 'H-Sphere XML-API',
    'loginapi_phpprobid' => 'PHPProBid',
    'loginapi_cubecart' => 'CubeCart',
    'loginapi_modernbillv5' => 'ModernBill v5',
    'loginapi_cscart' => 'CS-Cart',
    'loginapi_fsr' => 'FSRevolution',
    'loginapi_viper' => 'Viper Cart',
    'loginapi_xoops' => 'XOOPS',
    'loginapi_whmcsintegration' => 'WHMCS - Integration Placeholder Only (Not for direct logins)',
);


return $__LANG;
