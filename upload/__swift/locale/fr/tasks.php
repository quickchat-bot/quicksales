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
    // Scheduled tasks
    'scheduledtasks'       => 'Tâches planifiées',
    'desc_scheduledtasks'  => 'Ces tâches seront exécutées aux intervalles indiqués ci-dessous pour effectuer plusieurs opérations de helpdesk. Si vous êtes un client de téléchargement et que votre centre d\'assistance est rarement utilisé, il est recommandé de mettre en place un service cron côté serveur pour déclencher le système de tâches planifiées.  <a href="https://go.gfi.com/?pageid=GFIHelpDeskTasks" target="_blank" rel="noopener noreferrer">Pour en savoir plus sur les tâches planifiées</a>.',
    'tasklist'             => 'Liste des tâches',
    'nextrun'              => 'Prochaine exécution',
    'lastrun'              => 'Dernière exécution',
    'runsevery'            => 'S\'exécute tous les',
    'xminute'              => '%s minutes',
    'xhour'                => '%s heures',
    'xday'                 => '%s jours',
    'runnow'               => 'Exécuter',
    'scheduledexecute'     => 'La tâche (%s) sera exécutée la prochaine fois que le système de tâches planifiées sera déclenché',
    'taskdisabled'         => 'Tâche %s désactivée',
    'taskenabled'          => 'Tâche %s activée ',
    'tasklog'              => 'Journal de la tâche',
    'securl'               => 'URL : ',
    'tasklogs'             => 'Journaux de tâche',
    'executiontime'        => 'Heure d\'exécution',
    'cronlogdeleteconfirm' => 'Entrée (%s) du journal de la tâche supprimée',
    'execute'              => 'Exécuter',
    'titledisablecron'     => 'Tâche planifiée désactivée (%s)',
    'msgdisablecron'       => 'Les tâches planifiées suivantes ont été désactivées et ne s\'exécuteront plus :',
    'titleenablecron'      => 'Tâches planifiées activées (%d)',
    'msgenablecron'        => 'Les tâches planifiées suivantes ont été activées :',
    'titleexecutecron'     => 'Les %d tâches planifiées ont étés exécutées',
    'msgexecutecron'       => 'Les tâches planifiées suivantes ont été exécutées :',

    // ======= TASK ENTRIES =======
    'cronhourlycleanup'    => 'Nettoyage du système toutes les heures',
    'crondailycleanup'     => 'Nettoyage quotidien du système',
    'cronsearchengine'     => 'Ré-indexation de l\'engin de recherche',
    'tickets'              => 'Tâches de tickets (escalades de la SLA, nettoyage, etc.)',
    'parser'               => 'Récupération d\'emails (POP3/IMAP)',
    'parsercleanup'        => 'Nettoyage de l\'analyseur d\'email',
    'livechat'             => 'Nettoyage du chat en direct',
    'newssync'             => 'Synchroniser les flux RSS d\'actualités',
    'ticketautoclose'      => 'Tickets à fermeture automatique',
    'defaultcoreminute'    => 'Synchronisation à la minute du cœur',
    'defaultcorehourly'    => 'Synchronization horaire du cœur',
    'defaultcoredaily'     => 'Synchronisation quotidienne du cœur',
    'defaultcoreweekly'    => 'Synchronisation hebdomadaire du cœur',
    'defaultcoremonthly'   => 'Synchronisation mensuelle du cœur',
    'reportemailing'       => 'Exécuter les rapports planifiés',
    'ticketfollowup'       => 'Exécuter les tâches de suivi de tickets',
);


return $__LANG;
