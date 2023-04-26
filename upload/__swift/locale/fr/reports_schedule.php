<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Andriy Lesyuk
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2014, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'schedules'                  => 'Programmes',

    'exportformat'               => 'Format de rapport',
    'desc_exportformat'          => 'Le rapport sera généré (et vous sera envoyé par email) dans ce format.',
    'recurrence'                 => 'Exécutez le rapport chaque',
    'desc_recurrence'            => 'Spécifiez avec quelle fréquence le rapport doit être exécuté.',
    'firstexecutiondate'         => 'Date de première exécution',
    'nextexecutiondate'          => 'Prochaine date d\'exécution',
    'lastexecutiondate'          => 'Dernière date d\'exécution',
    'desc_executiondate'         => 'Spécifiez la première exécution de ce rapport. L\'exécution des rapports ultérieurs sera calculée automatiquement.',
    'schedulecc'                 => 'Envoyer à d\'autres adresses email',
    'desc_schedulecc'            => 'Le rapport sera envoyé à votre adresse e-mail. Vous pouvez éventuellement spécifier d\'autres adresses e-mail.',

    'recurrence_once'            => 'Une fois',
    'recurrence_hourly'          => 'Toutes les heures',
    'recurrence_daily'           => 'Quotidien',
    'recurrence_weekly'          => 'Hebdomadaire',
    'recurrence_bimonthly'       => 'Bi-hebdomadaire',
    'recurrence_monthly'         => 'Mensuel',
    'recurrence_quarterly'       => 'Trimestriel',
    'recurrence_yearly'          => 'Annuel',

    'titleinvalidformat'         => 'Format d\'exportation non valide',
    'msginvalidformat'           => 'Veuillez sélectionner un format d\'exportation valide.',
    'titleinvalidrecurrence'     => 'Récurrence non valide',
    'msginvalidrecurrence'       => 'Veuillez sélectionner un type de récurrence valide.',
    'titleinvalidexecutiondate'  => 'Date de première exécution non valide',
    'msginvalidexecutiondate'    => 'Veuillez spécifier des valeurs valides pour les propriétés de date et d\'heure de la première date d\'exécution.',
    'titlepastexecutiondate'     => 'La première date d\'exécution est dans le passé',
    'msgpastexecutiondate'       => 'Veuillez sélectionner une date dans le futur pour la première exécution.',
    'titleinvalidcc'             => 'Adresse email non valide',
    'msginvalidcc'               => 'Veuillez vérifier la syntaxe des adresses email fournies.',

    'notifyreportscheduleinsert' => 'Rapport planifié avec succès',
    'notifyreportscheduleupdate' => 'Calendrier de rapport mis à jour',

    'ccemailaddresses'           => 'Adresses email copiées dans',

    'reportsendoncesub'          => 'Rapport : %s',
    'reportsendhourlysub'        => 'Rapport horaire : %s',
    'reportsenddailysub'         => 'Rapport quotidien : %s',
    'reportsendweeklysub'        => 'Rapport hebdomadaire : %s',
    'reportsendbimonthlysub'     => 'Rapport bi-hebdomadaire : %s',
    'reportsendmonthlysub'       => 'Rapport mensuel : %s',
    'reportsendquarterlysub'     => 'Rapport trimestriel : %s',
    'reportsendyearlysub'        => 'Rapport annuel : %s',

    'reportnoattachment'         => 'aucun attachement',
    'reportsendintro'            => 'Vous avez reçu cet email parce que vous ou un tiers agissant en votre nom (%s) à planifié l\'exécution et l\'envoi automatique d\'un rapport de helpdesk vers votre boîte de réception.',
    'reporterrorintro'           => 'Le rapport a retourné l\'erreur suivante',
    'reporterrorccnote'          => 'En raison de l\'erreur ci-dessus cet email n\'a pas été envoyé aux autres adresses email spécifiées pour recevoir une copie du rapport.',

    'noexportresultfound'        => 'Ce rapport n\'a produit aucun résultat, il n\'y avait donc rien à exporter.',
);

return $__LANG;
