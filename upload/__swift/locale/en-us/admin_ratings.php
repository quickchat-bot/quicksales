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
    'ratings' => 'Ratings',
    'tabgeneral' => 'General',
    'tabpermissionsstaff' => 'Permissions (Staff)',
    'tabpermissionsuser' => 'Permissions (User)',
    'staffgroups' => 'Staff Teams',
    'usergroups' => 'User Groups',
    'ratingticket' => 'Ticket',
    'ratingticketpost' => 'Ticket posts',
    'ratingchatsurvey' => 'Chat survey',
    'ratingchathistory' => 'Chat history',

    'staffvisibilitycustom' => 'Restrict rating modification to certain staff teams',
    'desc_staffvisibilitycustom' => 'If enabled, only the teams below will be able to modify and set values for this rating.',

    'buservisibilitycustom' => 'Restrict rating to specific user groups',
    'desc_buservisibilitycustom' => 'If enabled, only the user groups below will be able to modify and set values for this rating.',

    'bstaffvisibilitycustom' => 'Restrict rating to specific staff teams',
    'desc_bstaffvisibilitycustom' =>  'If enabled, only the teams below will be able to view this rating.',

    'isclientonly' => 'Prevent staff from setting values for this rating',
    'desc_isclientonly' => 'Only end users will be able to set values for this rating if enabled.',

    'ratingscale' => 'Rating scale',
    'desc_ratingscale' => 'The maximum rating that can be given. For example, if <em>5</em> is selected, the lowest possible rating will be 1 and the highest 10.',

    'ratingtype' => 'Helpdesk area',
    'desc_ratingtype' => 'The area of the helpdesk this rating will apply to. <br /><br /><strong>Ticket:</strong> Used to rate entire tickets.<br /><strong>Ticket post:</strong> Used to rate individual ticket responses.<br /><strong>Chat survey:</strong> Ratings will be added to the post-chat survey.<br /><strong>Chat history:</strong> Used to rate chat records in the staff control panel (intended for internal rating and auditing).',

    'ratingtitle' => 'Rating label',
    'desc_ratingtitle' => 'For example, <em>Overall satisfaction</em> or <em>Speed of resolution</em>.',

    'displayorder' => 'Display order',
    'desc_displayorder' => 'Multiple ratings can appear in the same location. They will be displayed according to their display order number, smallest to largest.',

    'ratingdep' => 'Restrict rating to a department',
    'desc_ratingdep' => 'Select a department to restrict this rating to tickets or chats within a specific department. If no department is selected, the rating will appear across all departments.',
    'ratingalldep' => '-- All departments --',

    'iseditable' => 'Allow modification after first submission',
    'desc_iseditable' => 'The value set for this rating can be modified, if enabled.',

    'ratingvisibility' => 'Who can submit a rating?',
    'desc_ratingvisibility' => '<strong>Public</strong> Ratings are available to all users. Select this option if you want to collect feedback from your end users and have the ratings visible to staff too.<br /><strong>Private</strong> Ratings are available only to your staff users, for internal rating purposes.',

    'ratingvis' => 'Visibility',
    'insertrating' => 'Insert Rating',
    'desc_insertrating' => '',
    'manageratings' => 'Manage',
    'desc_manageratings' => '',
    'wineditrating' => 'Edit rating: %s',
    'editrating' => 'Edit rating',
    'desc_editrating' => '',
    'titledelrating' => 'Ratings deleted (%d)',
    'msgdelrating' => 'The following ratings and all their data were deleted:',
    'titleratinginsert' => 'Rating created',
    'msgratinginsert' => 'The rating (%s) was created successfully.',
    'titleratingupdate' => 'Rating updated',
    'msgratingupdate' => 'The rating (%s) was updated successfully.',

    // Potentialy unused phrases in admin_ratings.php
    'ratingknowledgebase' => 'Knowledgebase',
    'ratingtroubleshooter' => 'Troubleshooter',
    'ratingnews' => 'News',
);


return $__LANG;
