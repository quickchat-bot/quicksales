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
    'kqlparseerror'                        => 'Erreur d\'analyse : %s',

    /**
     * ---------------------------------------------
     * Table Labels
     * ---------------------------------------------
     */
    'departments'                          => 'Service',
    'staff'                                => 'Personnel',
    'teams'                                => 'Équipes',
    'userorganizations'                    => 'Organisations d\'utilisateurs',
    'usergroups'                           => 'Groupes d\'utilisateurs',
    'users'                                => 'Utilisateurs',
    'useremails'                           => 'Emails de l\'utilisateur',
    'ratings'                              => 'Classements',
    'ratingresults'                        => 'Résultats de l\'évaluation',
    'taglinks'                             => 'Liens des balises',
    'tags'                                 => 'Balises',

    /**
     * ---------------------------------------------
     * Table Aliases
     * ---------------------------------------------
     */
    'staffgroup'                           => 'Groupe d\'opérateur',

    /**
     * ---------------------------------------------
     * Custom Labels
     * ---------------------------------------------
     */
    'custom_tickets'                       => 'Tickets',
    'custom_livechat'                      => 'Chat en direct',
    'custom_public'                        => 'Publique',
    'custom_private'                       => 'Privé',
    'custom_shared'                        => 'Partagé',
    'custom_restricted'                    => 'Restreint',
    'custom_guest'                         => 'Invité',
    'custom_registered'                    => 'Enregistré',
    'custom_user'                          => 'Utilisateur',
    'custom_manager'                       => 'Gestionnaire',
    'custom_nosalutation'                  => '',
    'custom_mr'                            => 'M.',
    'custom_ms'                            => 'Mme',
    'custom_mrs'                           => 'Mmes',
    'custom_dr'                            => 'Dr',

    /**
     * ---------------------------------------------
     * Field Labels
     * ---------------------------------------------
     */

    // Departments
    'departments_departmentid'             => 'ID de service',
    'departments_title'                    => 'Service',
    'departments_departmentapp'            => 'Applications',
    'departments_displayorder'             => 'Ordre d\'affichage',
    'departments_parentdepartmentid'       => 'Service parent',
    'departments_departmenttype'           => 'Type',

    // Staff
    'staff_staffid'                        => 'ID d\'opérateur',
    'staff_firstname'                      => 'Prénom',
    'staff_lastname'                       => 'Nom de famille',
    'staff_fullname'                       => 'Nom et prénom',
    'staff_username'                       => 'Nom d\'utilisateur',
    'staff_designation'                    => 'Designation',
    'staff_email'                          => 'Email',
    'staff_lastvisit'                      => 'Dernière visite',
    'staff_isenabled'                      => 'Est activé',
    'staff_staffgroup'                     => 'Équipe',

    // StaffGroup/Team
    'staffgroup_staffgroupid'              => 'ID d\'équipe',
    'staffgroup_title'                     => 'Équipe',
    'staffgroup_isadmin'                   => 'Est administrateur',

    // UserOrganizations
    'userorganizations_userorganizationid' => 'ID de l\'organisation de l\'utilisateur',
    'userorganizations_organizationname'   => 'Nom',
    'userorganizations_organizationtype'   => 'Type',
    'userorganizations_address'            => 'Adresse',
    'userorganizations_city'               => 'Ville',
    'userorganizations_state'              => 'État',
    'userorganizations_postalcode'         => 'Code postal/zip',
    'userorganizations_country'            => 'Pays',
    'userorganizations_phone'              => 'Téléphone',
    'userorganizations_fax'                => 'Télécopie',
    'userorganizations_website'            => 'Site Web',
    'userorganizations_dateline'           => 'Date de création',
    'userorganizations_lastupdate'         => 'Dernière mise à jour',
    'userorganizations_slaplanid'          => 'Plan de SLA',
    'userorganizations_slaexpirytimeline'  => 'Expiration de la SLA',
    'userorganizations_usergroupid'        => 'Groupe d\'utilisateurs',

    // UserGroups
    'usergroups_usergroupid'               => 'ID de groupe d\'utilisateurs',
    'usergroups_title'                     => 'Groupe d\'utilisateurs',
    'usergroups_grouptype'                 => 'Type de groupe d\'utilisateurs',

    // Users
    'users_userid'                         => 'ID d\'utilisateur',
    'users_usergroupid'                    => 'Groupe d\'utilisateurs',
    'users_userorganizationid'             => 'Organisation de l\'utilisateur',
    'users_userrole'                       => 'Rôle',
    'users_salutation'                     => 'Formule de politesse',
    'users_fullname'                       => 'Nom et prénom',
    'users_userdesignation'                => 'Désignation',
    'users_phone'                          => 'Téléphone',
    'users_dateline'                       => 'Date de création',
    'users_lastupdate'                     => 'Dernière mise à jour',
    'users_lastvisit'                      => 'Dernière visite',
    'users_slaplanid'                      => 'Plan de SLA',
    'users_slaexpirytimeline'              => 'Expiration de la SLA',
    'users_userexpirytimeline'             => 'Expiration',
    'users_isvalidated'                    => 'Est validé',

    // UserEmails
    'useremails_useremailid'               => 'ID d\'email d\'utilisateur',
    'useremails_linktypeid'                => 'Utilisateur',
    'useremails_email'                     => 'Email',

    // Ratings
    'custom_benchtickets'                  => 'Tickets',
    'custom_benchticketposts'              => 'Messages de ticket',
    'custom_benchchatsurvey'               => 'Sondage chat',
    'custom_benchchathistory'              => 'Historique du chat',
    'ratings_ratingid'                     => 'ID d\'évaluation',
    'ratings_ratingtitle'                  => 'Titre',
    'ratings_ratingtype'                   => 'Type',
    'ratings_ratingvisibility'             => 'Visibilité',

    // RatingResults
    'ratingresults_ratingresultid'         => 'ID de résultat de classification',
    'ratingresults_dateline'               => 'Date d\'évaluation',
    'ratingresults_ratingresult'           => 'Score',
    'ratingresults_ratingid'               => 'Classement',

    // TagLinks
    'taglinks_taglinkid'                   => 'ID de liend de balise',
    'taglinks_tagid'                       => 'Balise',

    // Tags
    'tags_tagid'                           => 'ID de balise',
    'tags_tagname'                         => 'Nom',

);

return $__LANG;
