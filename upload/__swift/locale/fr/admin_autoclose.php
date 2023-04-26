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
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'autoclose'                    => 'Fermeture Automatique',
    'manage'                       => 'Gérer',
    'targetstatus'                 => 'État cible',
    'tabgeneral'                   => 'Général',

    'insertrule'                   => 'Ajouter la règle',
    'editrule'                     => 'Modifier la règle',

    'ruletitle'                    => 'Titre de la règle',
    'desc_ruletitle'               => 'Saisir un titre pour cette règle de fermeture automatique.',
    'targetticketstatus'           => 'Définir à l\'état',
    'desc_targetticketstatus'      => 'Une fois passé le délai d\'inactivité (voir ci-dessous), les tickets correspondant à cette règle passent à cet état (ce doit être un état <strong>résolu</strong>).',
    'inactivitythreshold'          => 'Seuil d\'inactivité',
    'desc_inactivitythreshold'     => 'Si aucune mise à jour n\'est faite à un ticket dans ce nombre d\'heures, le billet sera réputé être inactif. Il s\'agit d\'un premier pas vers la fermeture automatiquement d\'un ticket.',
    'closurethreshold'             => 'Seuil de fermeture',
    'desc_closurethreshold'        => 'Si un ticket a été marqué comme inactif et si aucune mise à jour n\'est reçue dans ce nombre d\'heures, le ticket sera automatiquement passé à l\'état spécifié ci-dessus.',
    'isenabled'                    => 'La règle est activée',
    'desc_isenabled'               => 'Activer/désactiver la règle.',
    'sortorder'                    => 'Ordre d\'exécution',
    'desc_sortorder'               => 'Il est possible de créer automatiquement plusieurs règles de fermeture automatique.  L\'ordre d\'exécution détermine quelles règles seront exécutées en premier, de la première à la dernière',
    'sendpendingnotification'      => 'Envoyer email de notification d\'inactivité',
    'desc_sendpendingnotification' => 'Le service client peut informer l\'utilisateur que son ticket à été marqué comme inactif et que à défaut de réponse il sera fermé.',
    'sendfinalnotification'        => 'Envoyer une notification de fermeture définitive du ticket',
    'desc_sendfinalnotification'   => 'Le helpdesk peut aussi notifier l\'utilisateur que le ticket a été fermé.',
    'suppresssurveyemail'          => 'Bloquer l\'email de sondage client',
    'desc_suppresssurveyemail'     => 'Si vous avez activé l\'envoi d\'invitations à participer au sondage de satisfaction quand un ticket atteint l\'état spécifié ci-dessus, vous préférez peut-être éviter l\'envoi d\'invitations dans les cas de fermeture automatique des tickets.',

    'insertcriteria'               => 'Ajouter un critère',


    'titleautocloseruledel'        => 'Règle de fermeture automatique éliminée (%d)',
    'msgautocloseruledel'          => 'Les règles de fermeture automatique suivantes ont étés éliminées :',
    'titleautocloseruleenable'     => 'Règles de fermeture automatique activées (%d)',
    'msgautocloseruleenable'       => 'Les règles de fermeture automatiques suivantes ont étés activées :',
    'titleautocloseruledisable'    => 'Règles de fermeture automatique désactivées (%d)',
    'msgautocloseruledisable'      => 'Les règles de fermeture automatiquesuivantes ont étés désactivées :',
    'titleautocloseruleinsert'     => 'Règle de fermeture automatique créée',
    'msgautocloseruleinsert'       => 'La règle de fermeture automatique (%s) à été créée.',
    'titleautocloseruleupdate'     => 'Règle de fermeture automatique mise à jour',
    'msgautocloseruleupdate'       => 'La règle de fermeture automatique (%s) à été mise à jour.',

    'titlenocriteriaadded'         => 'Aucun critère spécifié',
    'msgnocriteriaadded'           => 'Vous devez spécifier au moins un critère pour créer une régle de fermeture automatique (sinon le service client ne saura pas quels tickets fermer).',

    /**
     * ---------------------------------------------
     * Rule Criterias
     * ---------------------------------------------
     */
    'notapplicable'                => '-- NA --',
    'articketstatusid'             => 'État',
    'desc_articketstatusid'        => '',
    'arpriorityid'                 => 'Priorité',
    'desc_arpriorityid'            => '',
    'ardepartmentid'               => 'Service',
    'desc_ardepartmentid'          => '',
    'arownerstaffid'               => 'Propriétaire',
    'desc_arownerstaffid'          => '',
    'aremailqueueid'               => 'File d\'attente',
    'arusergroupid'                => 'Groupe d\'utilisateurs',
    'desc_arusergroupid'           => '',
    'desc_aremailqueueid'          => '',
    'arflagtype'                   => 'Drapeaux',
    'desc_arflagtype'              => '',
    'arbayescategoryid'            => 'Catégorie Bayesienne',
    'desc_arbayescategoryid'       => 'Les tickets qui ont correspondent à une catégorie bayesienne spécifique.',
    'arcreator'                    => 'Créateur',
    'desc_arcreator'               => '',
    'creatorstaff'                 => 'Personnel',
    'creatorclient'                => 'Utilisateur',
    'arunassigned'                 => '-- Non assigné --',
    'artemplategroupid'            => 'Groupe de gabarits',
    'desc_artemplategroupid'       => '',
    'artickettypeid'               => 'Type',
    'desc_rtickettypeid'           => '',
);

return $__LANG;
