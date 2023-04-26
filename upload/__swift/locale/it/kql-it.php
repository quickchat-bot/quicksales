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
    'kqlparseerror'                        => 'Errore di analisi: %s',

    /**
     * ---------------------------------------------
     * Table Labels
     * ---------------------------------------------
     */
    'departments'                          => 'Dipartimenti',
    'staff'                                => 'Personale',
    'teams'                                => 'Squadre',
    'userorganizations'                    => 'Organizzazioni Utente',
    'usergroups'                           => 'Gruppi di Utenti',
    'users'                                => 'Utenti',
    'useremails'                           => 'Email Utente',
    'ratings'                              => 'Valutazioni',
    'ratingresults'                        => 'Risultati delle Valutazioni',
    'taglinks'                             => 'Collegamenti Tag',
    'tags'                                 => 'Tag',

    /**
     * ---------------------------------------------
     * Table Aliases
     * ---------------------------------------------
     */
    'staffgroup'                           => 'Gruppo del Personale',

    /**
     * ---------------------------------------------
     * Custom Labels
     * ---------------------------------------------
     */
    'custom_tickets'                       => 'Ticket',
    'custom_livechat'                      => 'Chat dal Vivo',
    'custom_public'                        => 'Pubblico',
    'custom_private'                       => 'Privato',
    'custom_shared'                        => 'Condiviso',
    'custom_restricted'                    => 'Limitato',
    'custom_guest'                         => 'Guest',
    'custom_registered'                    => 'Registrato',
    'custom_user'                          => 'Utente',
    'custom_manager'                       => 'Gestore',
    'custom_nosalutation'                  => '',
    'custom_mr'                            => 'Sig.',
    'custom_ms'                            => 'Sig.na.',
    'custom_mrs'                           => 'Sig.ra.',
    'custom_dr'                            => 'Dott.',

    /**
     * ---------------------------------------------
     * Field Labels
     * ---------------------------------------------
     */

    // Departments
    'departments_departmentid'             => 'ID Dipartimento',
    'departments_title'                    => 'Dipartimento',
    'departments_departmentapp'            => 'App',
    'departments_displayorder'             => 'Ordine di Visualizzazione',
    'departments_parentdepartmentid'       => 'Dipartimento Genitore',
    'departments_departmenttype'           => 'Tipo',

    // Staff
    'staff_staffid'                        => 'ID Personale',
    'staff_firstname'                      => 'Nome',
    'staff_lastname'                       => 'Cognome',
    'staff_fullname'                       => 'Nome e Cognome',
    'staff_username'                       => 'Nome Utente',
    'staff_designation'                    => 'Designation',
    'staff_email'                          => 'Email',
    'staff_lastvisit'                      => 'Ultima Visita',
    'staff_isenabled'                      => 'È Abilitato',
    'staff_staffgroup'                     => 'Squadra',

    // StaffGroup/Team
    'staffgroup_staffgroupid'              => 'ID Squadra',
    'staffgroup_title'                     => 'Squadra',
    'staffgroup_isadmin'                   => 'È Admin',

    // UserOrganizations
    'userorganizations_userorganizationid' => 'ID Organizzazione Utente',
    'userorganizations_organizationname'   => 'Nome',
    'userorganizations_organizationtype'   => 'Tipo',
    'userorganizations_address'            => 'Indirizzo',
    'userorganizations_city'               => 'Città',
    'userorganizations_state'              => 'Provincia',
    'userorganizations_postalcode'         => 'Codice postale',
    'userorganizations_country'            => 'Nazione',
    'userorganizations_phone'              => 'Telefono',
    'userorganizations_fax'                => 'Fax',
    'userorganizations_website'            => 'Sito web',
    'userorganizations_dateline'           => 'Data di Creazione',
    'userorganizations_lastupdate'         => 'Ultimo Aggiornamento',
    'userorganizations_slaplanid'          => 'Piano di SLA',
    'userorganizations_slaexpirytimeline'  => 'Scadenza di SLA',
    'userorganizations_usergroupid'        => 'Gruppo di Utenti',

    // UserGroups
    'usergroups_usergroupid'               => 'ID Gruppo di Utenti',
    'usergroups_title'                     => 'Gruppo di Utenti',
    'usergroups_grouptype'                 => 'Tipo di Gruppo di Utenti',

    // Users
    'users_userid'                         => 'ID Utente',
    'users_usergroupid'                    => 'Gruppo di Utenti',
    'users_userorganizationid'             => 'Organizzazione dell\'Utente',
    'users_userrole'                       => 'Ruolo',
    'users_salutation'                     => 'Appellativo',
    'users_fullname'                       => 'Nome e Cognome',
    'users_userdesignation'                => 'Designazione',
    'users_phone'                          => 'Telefono',
    'users_dateline'                       => 'Data di Creazione',
    'users_lastupdate'                     => 'Ultimo Aggiornamento',
    'users_lastvisit'                      => 'Ultima Visita',
    'users_slaplanid'                      => 'Piano di SLA',
    'users_slaexpirytimeline'              => 'Scadenza di SLA',
    'users_userexpirytimeline'             => 'Scadenza',
    'users_isvalidated'                    => 'È Convalidato',

    // UserEmails
    'useremails_useremailid'               => 'ID Email Utente',
    'useremails_linktypeid'                => 'Utente',
    'useremails_email'                     => 'Email',

    // Ratings
    'custom_benchtickets'                  => 'Ticket',
    'custom_benchticketposts'              => 'Messaggi Ticket',
    'custom_benchchatsurvey'               => 'Sondaggio Chat',
    'custom_benchchathistory'              => 'Cronologia Chat',
    'ratings_ratingid'                     => 'ID Valutazione',
    'ratings_ratingtitle'                  => 'Titolo',
    'ratings_ratingtype'                   => 'Tipo',
    'ratings_ratingvisibility'             => 'Visibilità',

    // RatingResults
    'ratingresults_ratingresultid'         => 'ID Risultato della Valutazione',
    'ratingresults_dateline'               => 'Data Valutazione',
    'ratingresults_ratingresult'           => 'Punteggio',
    'ratingresults_ratingid'               => 'Valutazione',

    // TagLinks
    'taglinks_taglinkid'                   => 'ID Collegamento Tag',
    'taglinks_tagid'                       => 'Tag',

    // Tags
    'tags_tagid'                           => 'ID Tag',
    'tags_tagname'                         => 'Nome',

);

return $__LANG;
