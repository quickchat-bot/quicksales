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
 * @copyright      Copyright (c) 2001-2014, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

$__LANG = array(
    'kqlparseerror'                        => 'Parsefout: %s',

    /**
     * ---------------------------------------------
     * Table Labels
     * ---------------------------------------------
     */
    'departments'                          => 'Afdelingen',
    'staff'                                => 'Medewerker',
    'teams'                                => 'Teams',
    'userorganizations'                    => 'Gebruikersorganisaties',
    'usergroups'                           => 'Gebruikersgroepen',
    'users'                                => 'Gebruikers',
    'useremails'                           => 'Gebruikeremails',
    'ratings'                              => 'Beoordelingen',
    'ratingresults'                        => 'Beoordelingsresultaten',
    'taglinks'                             => 'Taglinks',
    'tags'                                 => 'Tags',

    /**
     * ---------------------------------------------
     * Table Aliases
     * ---------------------------------------------
     */
    'staffgroup'                           => 'Medewerkergroep',

    /**
     * ---------------------------------------------
     * Custom Labels
     * ---------------------------------------------
     */
    'custom_tickets'                       => 'Tickets',
    'custom_livechat'                      => 'Live Chat',
    'custom_public'                        => 'Publiek',
    'custom_private'                       => 'Privé',
    'custom_shared'                        => 'Gedeeld',
    'custom_restricted'                    => 'Beperkt',
    'custom_guest'                         => 'Gast',
    'custom_registered'                    => 'Geregistreerd',
    'custom_user'                          => 'Gebruiker',
    'custom_manager'                       => 'Manager',
    'custom_nosalutation'                  => '',
    'custom_mr'                            => 'Deheer',
    'custom_ms'                            => 'Mevrouw',
    'custom_mrs'                           => 'Mevrouw',
    'custom_dr'                            => 'Dr.',

    /**
     * ---------------------------------------------
     * Field Labels
     * ---------------------------------------------
     */

    // Departments
    'departments_departmentid'             => 'Afdelings-ID',
    'departments_title'                    => 'Afdeling',
    'departments_departmentapp'            => 'App',
    'departments_displayorder'             => 'Weergavevolgorde',
    'departments_parentdepartmentid'       => 'Bovenliggende afdeling',
    'departments_departmenttype'           => 'Type',

    // Staff
    'staff_staffid'                        => 'Medewerker-ID',
    'staff_firstname'                      => 'Voornaam',
    'staff_lastname'                       => 'Achternaam',
    'staff_fullname'                       => 'Volledige naam',
    'staff_username'                       => 'Gebruikersnaam',
    'staff_designation'                    => 'Designation',
    'staff_email'                          => 'Email',
    'staff_lastvisit'                      => 'Laatste bezoek',
    'staff_isenabled'                      => 'Is ingeschakeld',
    'staff_staffgroup'                     => 'Team',

    // StaffGroup/Team
    'staffgroup_staffgroupid'              => 'Team-ID',
    'staffgroup_title'                     => 'Team',
    'staffgroup_isadmin'                   => 'Is admin',

    // UserOrganizations
    'userorganizations_userorganizationid' => 'Gebruikersorganisatie-ID',
    'userorganizations_organizationname'   => 'Naam',
    'userorganizations_organizationtype'   => 'Type',
    'userorganizations_address'            => 'Adres',
    'userorganizations_city'               => 'Plaats',
    'userorganizations_state'              => 'Staat/provincie',
    'userorganizations_postalcode'         => 'Postcode',
    'userorganizations_country'            => 'Land',
    'userorganizations_phone'              => 'Telefoon',
    'userorganizations_fax'                => 'Fax',
    'userorganizations_website'            => 'Website',
    'userorganizations_dateline'           => 'Aanmaakdatum',
    'userorganizations_lastupdate'         => 'Laatste update',
    'userorganizations_slaplanid'          => 'SLA-plan',
    'userorganizations_slaexpirytimeline'  => 'SLA-verloopdatum',
    'userorganizations_usergroupid'        => 'Gebruikersgroep',

    // UserGroups
    'usergroups_usergroupid'               => 'Gebruikersgroep-ID',
    'usergroups_title'                     => 'Gebruikersgroep',
    'usergroups_grouptype'                 => 'Gebruikersgroeptype',

    // Users
    'users_userid'                         => 'Gebruikers-ID',
    'users_usergroupid'                    => 'Gebruikersgroep',
    'users_userorganizationid'             => 'Gebruikersorganisatie',
    'users_userrole'                       => 'Rol',
    'users_salutation'                     => 'Aanhef',
    'users_fullname'                       => 'Volledige naam',
    'users_userdesignation'                => 'Aanduiding',
    'users_phone'                          => 'Telefoon',
    'users_dateline'                       => 'Aanmaakdatum',
    'users_lastupdate'                     => 'Laatste update',
    'users_lastvisit'                      => 'Laatste bezoek',
    'users_slaplanid'                      => 'SLA-plan',
    'users_slaexpirytimeline'              => 'SLA-verloopdatum',
    'users_userexpirytimeline'             => 'Verloopdatum',
    'users_isvalidated'                    => 'Is gevalideerd',

    // UserEmails
    'useremails_useremailid'               => 'Gebruikersemail-ID',
    'useremails_linktypeid'                => 'Gebruiker',
    'useremails_email'                     => 'Email',

    // Ratings
    'custom_benchtickets'                  => 'Tickets',
    'custom_benchticketposts'              => 'Ticketberichten',
    'custom_benchchatsurvey'               => 'Chatonderzoek',
    'custom_benchchathistory'              => 'Chatgeschiedenis',
    'ratings_ratingid'                     => 'Beoordelings-ID',
    'ratings_ratingtitle'                  => 'Titel',
    'ratings_ratingtype'                   => 'Type',
    'ratings_ratingvisibility'             => 'Zichtbaarheid',

    // RatingResults
    'ratingresults_ratingresultid'         => 'Beoordelingsresultaat-ID',
    'ratingresults_dateline'               => 'Beoordelingsdatum',
    'ratingresults_ratingresult'           => 'Score',
    'ratingresults_ratingid'               => 'Beoordeling',

    // TagLinks
    'taglinks_taglinkid'                   => 'Taglink-ID',
    'taglinks_tagid'                       => 'Tag',

    // Tags
    'tags_tagid'                           => 'Tag-ID',
    'tags_tagname'                         => 'Naam',

);

return $__LANG;
