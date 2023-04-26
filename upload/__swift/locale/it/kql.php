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
    'kqlparseerror'                        => 'Parse Error: %s',

    /**
     * ---------------------------------------------
     * Table Labels
     * ---------------------------------------------
     */
    'departments'                          => 'Departments',
    'staff'                                => 'Staff',
    'teams'                                => 'Teams',
    'userorganizations'                    => 'User Organizations',
    'usergroups'                           => 'User Groups',
    'users'                                => 'Users',
    'useremails'                           => 'User Emails',
    'ratings'                              => 'Ratings',
    'ratingresults'                        => 'Rating Results',
    'taglinks'                             => 'Tag Links',
    'tags'                                 => 'Tags',

    /**
     * ---------------------------------------------
     * Table Aliases
     * ---------------------------------------------
     */
    'staffgroup'                           => 'Staff Group',

    /**
     * ---------------------------------------------
     * Custom Labels
     * ---------------------------------------------
     */
    'custom_tickets'                       => 'Tickets',
    'custom_livechat'                      => 'Live Chat',
    'custom_public'                        => 'Public',
    'custom_private'                       => 'Private',
    'custom_shared'                        => 'Shared',
    'custom_restricted'                    => 'Restricted',
    'custom_guest'                         => 'Guest',
    'custom_registered'                    => 'Registered',
    'custom_user'                          => 'User',
    'custom_manager'                       => 'Manager',
    'custom_nosalutation'                  => '',
    'custom_mr'                            => 'Mr.',
    'custom_ms'                            => 'Ms.',
    'custom_mrs'                           => 'Mrs.',
    'custom_dr'                            => 'Dr.',

    /**
     * ---------------------------------------------
     * Field Labels
     * ---------------------------------------------
     */

    // Departments
    'departments_departmentid'             => 'Department ID',
    'departments_title'                    => 'Department',
    'departments_departmentapp'            => 'App',
    'departments_displayorder'             => 'Display Order',
    'departments_parentdepartmentid'       => 'Parent Department',
    'departments_departmenttype'           => 'Type',

    // Staff
    'staff_staffid'                        => 'Staff ID',
    'staff_firstname'                      => 'First Name',
    'staff_lastname'                       => 'Last Name',
    'staff_fullname'                       => 'Full Name',
    'staff_username'                       => 'Username',
    'staff_designation'                    => 'Designation',
    'staff_email'                          => 'Email',
    'staff_lastvisit'                      => 'Last Visit',
    'staff_isenabled'                      => 'Is Enabled',
    'staff_staffgroup'                     => 'Team',

    // StaffGroup/Team
    'staffgroup_staffgroupid'              => 'Team ID',
    'staffgroup_title'                     => 'Team',
    'staffgroup_isadmin'                   => 'Is Admin',

    // UserOrganizations
    'userorganizations_userorganizationid' => 'User Organization ID',
    'userorganizations_organizationname'   => 'Name',
    'userorganizations_organizationtype'   => 'Type',
    'userorganizations_address'            => 'Address',
    'userorganizations_city'               => 'City',
    'userorganizations_state'              => 'State',
    'userorganizations_postalcode'         => 'Postal code/zip',
    'userorganizations_country'            => 'Country',
    'userorganizations_phone'              => 'Phone',
    'userorganizations_fax'                => 'Fax',
    'userorganizations_website'            => 'Website',
    'userorganizations_dateline'           => 'Creation Date',
    'userorganizations_lastupdate'         => 'Last Update',
    'userorganizations_slaplanid'          => 'SLA Plan',
    'userorganizations_slaexpirytimeline'  => 'SLA Expiry',
    'userorganizations_usergroupid'        => 'User Group',

    // UserGroups
    'usergroups_usergroupid'               => 'User Group ID',
    'usergroups_title'                     => 'User Group',
    'usergroups_grouptype'                 => 'User Group Type',

    // Users
    'users_userid'                         => 'User ID',
    'users_usergroupid'                    => 'User Group',
    'users_userorganizationid'             => 'User Organization',
    'users_userrole'                       => 'Role',
    'users_salutation'                     => 'Salutation',
    'users_fullname'                       => 'Full Name',
    'users_userdesignation'                => 'Designation',
    'users_phone'                          => 'Phone',
    'users_dateline'                       => 'Creation Date',
    'users_lastupdate'                     => 'Last Update',
    'users_lastvisit'                      => 'Last Visit',
    'users_slaplanid'                      => 'SLA Plan',
    'users_slaexpirytimeline'              => 'SLA Expiry',
    'users_userexpirytimeline'             => 'Expiry',
    'users_isvalidated'                    => 'Is Validated',

    // UserEmails
    'useremails_useremailid'               => 'User Email ID',
    'useremails_linktypeid'                => 'User',
    'useremails_email'                     => 'Email',

    // Ratings
    'custom_benchtickets'                  => 'Tickets',
    'custom_benchticketposts'              => 'Ticket Posts',
    'custom_benchchatsurvey'               => 'Chat Survey',
    'custom_benchchathistory'              => 'Chat History',
    'ratings_ratingid'                     => 'Rating ID',
    'ratings_ratingtitle'                  => 'Title',
    'ratings_ratingtype'                   => 'Type',
    'ratings_ratingvisibility'             => 'Visibility',

    // RatingResults
    'ratingresults_ratingresultid'         => 'Rating Result ID',
    'ratingresults_dateline'               => 'Rating Date',
    'ratingresults_ratingresult'           => 'Score',
    'ratingresults_ratingid'               => 'Rating',

    // TagLinks
    'taglinks_taglinkid'                   => 'Tag Link ID',
    'taglinks_tagid'                       => 'Tag',

    // Tags
    'tags_tagid'                           => 'Tag ID',
    'tags_tagname'                         => 'Name',

);

return $__LANG;
