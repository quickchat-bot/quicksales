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
    'notifications'                  => 'Notifications',
    'manage'                         => 'Gérer',
    'ruletitle'                      => 'Titre de la règle',
    'desc_ruletitle'                 => 'Entrez un titre pour la règle de notification.',
    'isenabled'                      => 'Règle de notification activée',
    'desc_isenabled'                 => 'Active/désactive cette règle.',
    'ruletype'                       => 'Type',
    'desc_ruletype'                  => 'La règle de notification peut être associée à l\'un des éléments suivants.',
    'next'                           => 'Suivante',
    'creator'                        => 'Créateur',
    'wininsertnotification'          => 'Ajouter une notification',

    'unassigned'                     => '-- Non assigné --',

    'tabgeneral'                     => 'Généralités',
    'tabactions'                     => 'Actions',
    'insertcriteria'                 => 'Ajouter un critère',

    'na_email'                       => 'Email',
    'na_sms'                         => 'SMS',
    'na_pool'                        => 'Application de bureau',
    'na_staff'                       => 'Compte de l\' utilisateur du personnel',
    'na_staffgroup'                  => 'Équipe',
    'na_department'                  => 'Service',
    'na_user'                        => 'Utilisateur',
    'nactionemaildispatch'           => 'Envoyer un email au propriétaire du ticket...',
    'desc_nactionemaildispatch'      => '',
    'nadispatchemail'                => 'Envoyer un email à d\'autres adresses e-mail',
    'desc_nadispatchemail'           => '',
    'nadispatchemailstaff'           => 'Envoyer email aux autres membres du personnel',
    'desc_nadispatchemailstaff'      => '',
    'nactionpooldispatch'            => 'Envoyer une notification au propriétaire du ticket actif...',
    'desc_nactionpooldispatch'       => '',
    'nactionpoolcustomdispatch'      => 'Personnel',
    'desc_nactionpoolcustomdispatch' => '',
    'notificationruledesc'           => 'Sélectionnez un type de règle de notification.',
    'titlenocriteria'                => 'Aucun critère spécifié',
    'msgnocriteria'                  => 'Au moins un critères est nécessaire pour créer une règle de notification.',
    'titlenotificationinsert'        => 'Règle de notification (%s) créée',
    'msgnotificationinsert'          => 'La règle de notification (%s) a été créée avec succès.',
    'titlenotificationupdate'        => 'Règle de notification (%s) mise à jour',
    'msgnotificationupdate'          => 'La règle de notification (%s) a été mise à jour avec succès.',
    'tabnotifyapp'                   => 'Notify App',
    'tabemail'                       => 'Email',
    'titleenablenotification'        => 'Règles de notification activées (%d)',
    'msgenablenotification'          => 'Les règles de notification suivantes ont été activées: :<br />%s',
    'titledisablenotification'       => 'Règles de notification désactivées (%d)',
    'msgdisablenotification'         => 'Les règles de notification suivantes ont été désactivées: :<br />%s',

    'emailprefix'                    => 'Préfixe d\'objet d\'email',
    'desc_emailprefix'               => 'Spécifier un préfixe d\'objet d\'email pour identifier plus facilement les emails envoyés par cette règle de notification.',

    /**
     * ---------------------------------------------
     * RULE TYPES
     * ---------------------------------------------
     */
    'nrule_chat'                     => 'Chat',
    'nrule_message'                  => 'Message hors ligne',
    'nrule_survey'                   => 'Sondage de chat',
    'nrule_ticket'                   => 'Ticket',
    'nrule_user'                     => 'Utilisateur',

    /**
     * ---------------------------------------------
     * RULE CRITERIA: TICKETS
     * ---------------------------------------------
     */

    'ntickettype'                    => 'Type de ticket',
    'desc_ntickettype'               => '',
    'nbayesian'                      => 'Catégorie Bayesienne',
    'desc_nbayesian'                 => 'Les tickets qui ont été associés à une catégorie bayesienne spécifique.',
    'notapplicable'                  => '-- Non disponible --',
    'nticketstatus'                  => 'État du ticket',
    'desc_nticketstatus'             => '',
    'nticketpriority'                => 'Priorité du ticket',
    'desc_nticketpriority'           => '',
    'nusergroup'                     => 'Groupe d\'utilisateurs',
    'desc_nusergroup'                => 'Recherche les tickets dont les destinataires appartiennent à un groupe particulier d\'utilisateurs.',
    'ndepartment'                    => 'Service de ticket',
    'desc_ndepartment'               => 'Tickets qui appartiennent à un service.',
    'nowner'                         => 'Propriétaire du ticket',
    'desc_nowner'                    => 'Tickets attribués à un utilisateur du personnel spécifique.',
    'nunassigned'                    => '-- Non assigné --',
    'nactivestaff'                   => '-- Personnel actif --',
    'nemailqueue'                    => 'File d\'attente de messages',
    'desc_nemailqueue'               => 'Les tickets qui ont été créés ou ou auxquels il a été répondu par email via une file d\'attente spécifique.',
    'nflagtype'                      => 'Indicateur de ticket',
    'desc_nflagtype'                 => '',
    'ncreator'                       => 'Ticket créé par',
    'desc_ncreator'                  => '',
    'creatorstaff'                   => 'Personnel',
    'creatorclient'                  => 'Utilisateur',
    'nslaplan'                       => 'Plan de SLA',
    'desc_nslaplan'                  => 'Tickets qui sont actuellement assignés à un plan particulier de SLA.',
    'ndaterange'                     => 'Date de création du ticket <range>',
    'desc_ndaterange'                => 'Tickets qui ont été créés dans ce laps de temps.',
    'nlastactivityrange'             => 'Ticket mis à jour pour la dernière fois <range>',
    'desc_nlastactivityrange'        => 'Les tickets qui ont été mis à jour (c\'est-à-dire auxquels une réponse à été fournie, ou qui ont étés mis à jour pour un autre événement) dans ce laps de temps.',
    'nlaststaffreplyrange'           => 'Dernière réponse du personnel <range>',
    'desc_nlaststaffreplyrange'      => 'Tickets qui ont reçu une réponse d\'un utilisateur du personnel dans ce laps de temps.',
    'nlastuserreplyrange'            => 'Dernière réponse de l\'utilisateur <range>',
    'desc_nlastuserreplyrange'       => 'Tickets qui ont reçu une réponse d\'un utilisateur dans ce laps de temps.',
    'nduerange'                      => 'Date limite de réponse <range>',
    'desc_nduerange'                 => 'Tickets qui disposent d\'une date limite de réponse dans ce laps de temps.',
    'nisoverdue'                     => 'En retard : Réponse en retard',
    'desc_nisoverdue'                => 'Les tickets qui sont en retard (parce qu\'ils n\'ont pas reçu de réponse avant la date limite de réponse).',
    'nresolutionduerange'            => 'Date limite de résolution <range>',
    'desc_nresolutionduerange'       => 'Tickets dont la date limite de résolution tombe dans ce laps de temps.',
    'nisresolutionoverdue'           => 'En retard : résolution en retard',
    'desc_nisresolutionoverdue'      => 'Tickets en retard (parce qu\'ils n\'ont pas été réglés avant la date limite de résolution).',
    'ntemplategroup'                 => 'Groupes de gabarits',
    'desc_ntemplategroup'            => 'Tickets qui appartiennent à un groupe de gabarits particuliers.',
    'ntimeworked'                    => 'Temps travaillé (en minutes)',
    'desc_ntimeworked'               => 'Tickets qui correspondent à la valeur <strong>temps de travail</strong> (dans le cadre des informations de facturation des tickets et de suivi du temps).',
    'ntotalreplies'                  => 'Total de réponses',
    'desc_ntotalreplies'             => 'Tickets qui ont ce nombre de réponses.',
    'npendingfollowups'              => 'En attente de suivi',
    'desc_npendingfollowups'         => 'Tickets qui ont des activités de suivi planifiées.',
    'nipaddress'                     => 'Adresse IP',
    'desc_nipaddress'                => 'Si une réponse à un ticket a été faite depuis le <strong>centre d\'assistance</strong>, il est possible que le service d\'assistance ait enregistré l\'adresse IP de l\'utilisateur.',
    'nisemailed'                     => 'Créé par email',
    'desc_nisemailed'                => 'Tickets qui ont été créés par email.',
    'nisedited'                      => 'A été modifié',
    'desc_nisedited'                 => 'Tickets qui ont été modifiés.',
    'nhasnotes'                      => 'Contient des remarques',
    'desc_nhasnotes'                 => 'Tickets contenant des remarques de ticket.',
    'nhasattachments'                => 'Contient des pièces jointes',
    'desc_nhasattachments'           => 'Tickets contenant des dossiers en pièce jointe.',
    'nisescalated'                   => 'A été escaladé',
    'desc_nisescalated'              => 'Tickets ayant étés escaladés par au moins une règle d\'escalade.',
    'nhasdraft'                      => 'A un projet',
    'desc_nhasdraft'                 => 'Tickets ayant un projet de réponse enregistré.',
    'nhasbilling'                    => 'A des entrées de facturation et de suivi du temps',
    'desc_nhasbilling'               => 'Tickets ayant des entrées de facturation et de suivi du temps.',
    'nisphonecall'                   => 'Est un ticket téléphonique',
    'desc_nisphonecall'              => 'Les tickets qui ont été marqués comme tickets du type <em>appel téléphonique</em>.',
    'nislabeled'                     => 'Est balisé',
    'desc_nislabeled'                => 'Tickets qui ont été balisés.',

    'nticketevent'                   => 'Evénement',
    'desc_nticketevent'              => '',
    'tevent_newticket'               => 'Nouveau ticket créé',
    'tevent_newticketnotes'          => 'Nouvelle note de ticket ajoutée',
    'tevent_newclientreply'          => 'Nouvelle réponse de l\'utilisateur final',
    'tevent_newclientsurvey'         => 'Soumettre un nouveau sondage',
    'tevent_newstaffreply'           => 'Nouvelle réponse du personnel',
    'tevent_ticketassigned'          => 'Ticket assigné',
    'tevent_newuser'                 => 'Inscription d\'un nouvel utilisateur',

    'nuserevent'                     => 'Evénement',
    'desc_nuserevent'                => 'Cette règle se déclenche sur cet événement.',
);

return $__LANG;
