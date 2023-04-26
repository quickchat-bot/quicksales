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
    'ratings'                     => 'Classements',
    'tabgeneral'                  => 'Général',
    'tabpermissionsstaff'         => 'Autorisations (Personnel)',
    'tabpermissionsuser'          => 'Autorisations (Utilisateur)',
    'staffgroups'                 => 'Équipes d\'opérateurs',
    'usergroups'                  => 'Groupes d\'utilisateurs',
    'ratingticket'                => 'Ticket',
    'ratingticketpost'            => 'Post de ticket',
    'ratingchatsurvey'            => 'Sondage chat',
    'ratingchathistory'           => 'Historique du chat',

    'staffvisibilitycustom'       => 'Restreindre la modification de l\'évaluation à certaines équipes du personnel',
    'desc_staffvisibilitycustom'  => 'Si activé, seules les équipes ci-dessous seront en mesure de modifier et de définir les valeurs de cette évaluatuion.',

    'buservisibilitycustom'       => 'Restreindre l\'évaluation à des groupes d\'utilisateurs spécifiques',
    'desc_buservisibilitycustom'  => 'Si activé, seules les groupes d\'utilisateurs ci-dessous seront en mesure de modifier et de définir des valeurs pour cette évaluatuion.',

    'bstaffvisibilitycustom'      => 'Restreindre l\'évaluation à des équipes de personnel spécifiques',
    'desc_bstaffvisibilitycustom' => 'Si activée, seules les équipes ci-dessous sera en mesure d\'afficher cette évaluatuion.',

    'isclientonly'                => 'Empêcher le personnel de définir des valeurs pour cette évaluatuion',
    'desc_isclientonly'           => 'Seuls les utilisateurs finaux seront en mesure de définir des valeurs pour cette évaluatuion si activé.',

    'ratingscale'                 => 'Échelle d\'évaluation',
    'desc_ratingscale'            => 'L\'évaluation la plus élevée qui peut être attribuée. Par exemple, si <em>5</em> est sélectionnée, la note la plus basse possible sera 1 et 10 la plus haute.',

    'ratingtype'                  => 'Zone de helpdesk',
    'desc_ratingtype'             => 'La zone du helpdesk à laquelle cette classification s\'applique. < br / > < br / > <strong>Ticket :</strong> Permet d\'évaluer des tickets entiers.<br /><strong>Post de ticket :</strong> Permet d\'évaluer chaque réponse à un ticket.<br /><strong>Sondage chat :</strong> Les évaluations seront ajoutés au sondage post chat.<br /><strong>Historique de chat :</strong> Permet de classer les enregistrements de chat dans le tableaux de bord du personnel (à des fins d\'évaluation et d\'audit interne).',

    'ratingtitle'                 => 'Indicateur d\'évaluation',
    'desc_ratingtitle'            => 'Par exemple, la <em>Satisfaction globale</em> ou la <em>Vitesse de résolution</em>.',

    'displayorder'                => 'Ordre d\'affichage',
    'desc_displayorder'           => 'Plusieurs évaluations peuvent apparaître au même endroit. Elles sont affichées selon leur numéro de position d\'affichage, du plus petit au plus grand.',

    'ratingdep'                   => 'Restreindre l\'évaluation à un service',
    'desc_ratingdep'              => 'Sélectionnez un service pour limiter cette évaluatuion aux tickets ou aux chats au sein d\'un service spécifique. Si aucun service n\'est sélectionnée, l\'évaluation s\'affiche pour tous les services.',
    'ratingalldep'                => '--Tous les services--',

    'iseditable'                  => 'Permettre la modification après la première soumission',
    'desc_iseditable'             => 'La valeur définie pour cette évaluatuion peut être modifié, si activé.',

    'ratingvisibility'            => 'Qui peut soumettre une évaluation ?',
    'desc_ratingvisibility'       => '<strong>Publique</strong> Les évaluations sont disponibles pour tous les utilisateurs. Sélectionnez cette option si vous souhaitez recueillir les commentaires de vos utilisateurs finaux et rendre les évaluations visibles au personnel. < br /> <strong>Privé</strong> Les évaluations sont disponibles uniquement aux personnel, à des fins de d\'évaluation interne.',

    'ratingvis'                   => 'Visibilité',
    'insertrating'                => 'une évaluation',
    'desc_insertrating'           => '',
    'manageratings'               => 'Gérer',
    'desc_manageratings'          => '',
    'wineditrating'               => 'Modifier l\'évaluation : %s',
    'editrating'                  => 'Modifier l\'évaluation',
    'desc_editrating'             => '',
    'titledelrating'              => 'Classements supprimés (%d)',
    'msgdelrating'                => 'Les évaluations suivants et toutes leurs données ont été supprimés :',
    'titleratinginsert'           => 'Classement créé',
    'msgratinginsert'             => 'Ce évaluation (%s) à été créé avec succès.',
    'titleratingupdate'           => 'Classement mis à jour',
    'msgratingupdate'             => 'L\'évaluation (%s) a été mise à jour avec succès.',

    // Potentialy unused phrases in admin_ratings.php
    'ratingknowledgebase'         => 'Base de connaissances',
    'ratingtroubleshooter'        => 'Dépannage',
    'ratingnews'                  => 'Actualités',
);


return $__LANG;
