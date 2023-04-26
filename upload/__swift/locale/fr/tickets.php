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

    // Bayesian
    'titlebayesnodel'           => 'Les catégories bayésiennes n\'ont pas pu être supprimées (%d)',
    'msgbayesnodel'             => 'Les catégories bayésiennes suivantes n\'ont pas pu être supprimées :',
    'check'                     => 'Vérifier',
    'bayesian'                  => 'Bayésien',
    'desc_bayesian'             => '',
    'categories'                => 'Catégories',
    'insertcategory'            => 'Ajouter la catégorie',
    'editcategory'              => 'Modifier la catégorie',
    'diagnostics'               => 'Diagnostics',
    'sla'                       => 'SLA',
    'tabtraining'               => 'Formation',
    'tabprobability'            => 'Probabilité',
    'bayescategory'             => 'Catégorie Bayesienne',
    'desc_bayescategory'        => 'Sélectionnez la catégorie bayésienne à entraîner.',
    'bayesactiontype'           => 'Que faire',
    'desc_bayesactiontype'      => '<strong>Entraîner</strong> L\'engin bayésien apprendra à catégoriser le texte entré ci-dessous dans cette catégorie. <br /> <strong>Désentraîner</strong> L\'engin bayésien apprendra à ne pas catégoriser le texte entré ci-dessous dans cette catégorie.',
    'bayestext'                 => 'Texte',
    'bayestrain'                => 'Entraîner',
    'bayesuntrain'              => 'Désentraîner',
    'titlebtrain'               => 'Entraînement complet',
    'msgbtrain'                 => 'L\'engin bayésien a été entraîné avec succès.',
    'titlebuntrain'             => 'Entrainement complet',
    'msgbuntrain'               => 'L\'engin bayésien a été entraîné avec succès.',
    'word'                      => 'Mot',
    'combinedprobability'       => 'Probabilité combinée',
    'tabsettings'               => 'Paramètres',
    'bayescattitle'             => 'Nom de la catégorie',
    'desc_bayescattitle'        => 'Par exemple : <em>Requêtes en rapport avec les ventes</em> ou <em>Requêtes espagnoles</em>.',
    'categoryweight'            => 'Pondération de la catégorie',
    'desc_categoryweight'       => 'La probabilité qu\'un un mot appartienne à une catégorie particulière est multipliée par le pondération de la catégorie pour obtenir la probabilité finale.',
    'tabgeneral'                => 'Généralités',
    'titlebayesdel'             => 'Catégories bayésienne supprimées (%d)',
    'msgbayesdel'               => 'Les catégories bayésiennes suivantes ont été supprimées :',
    'ismaster'                  => 'Catégorie Master',
    'wordcount'                 => 'Nombre de mots',
    'winbayeseditcategory'      => 'Modifier la catégorie bayésienne : %s',
    'titlebayesinsert'          => 'Catégorie bayésienne %s créé',
    'msgbayesinsert'            => 'La catégorie bayésienne %s a été correctement créée.',
    'titlebayesupdate'          => 'Catégorie bayésienne %s mise à jour',
    'msgbayesupdate'            => 'La catégorie bayésienne %s a été correctement créée.',
    'settings'                  => 'Paramètres',
    'desc_settings'             => '',

    // ?
    'tickets'                   => 'Tickets',
    'manage'                    => 'Gérer',

    // Potentialy unused phrases in tickets.php
    'invalidbayescat'           => 'Catégorie bayésienne non valide',
    'titlebayesmasterdel'       => 'Unable to delete category',
    'msgbayesmasterdel'         => 'You cannot delete the master bayesian categories (Spam/Not Spam). If you wish to disable spam filtering, you can do so under the bayesian settings section.',
    'displayorder'              => 'Display Order',
    'desc_displayorder'         => 'This is the default display order in which the items are displayed. The list is sorted in Ascending Order.',
    'statustitle'               => 'Status Title',
    'statuslist'                => 'Status List',
    'ticketstatus'              => 'Status',
    'desc_ticketstatus'         => 'Ticket Status allows you to group similar Tickets into different Categories. You can create a custom Ticket Status by clicking on the "Insert Status" option in the Navigation Bar.',
    'statusdetails'             => 'Custom Ticket Status Details',
    'insertstatus'              => 'Insert Status',
    'desc_statustitle'          => 'Enter your custom Ticket Status Title',
    'redirect_statusinsert'     => 'Ticket Status inserted successfully',
    'statusinsertconfirmation'  => 'Ticket Status "%s" inserted successfully',
    'statusdeleteconfirm'       => 'Ticket Status "%s" deleted successfully',
    'displayinmainlist'         => 'Display in Main Ticket Listing',
    'desc_displayinmainlist'    => 'If enabled, all Tickets under this Status will be displayed in the main Ticket Listing. It is recommended that this option be disabled for Resolved Ticket Statuses like "Closed", "Fixed", or "Info Required"',
    'updatestatus'              => 'Update Status',
    'editstatus'                => 'Edit Status',
    'invalidticketstatus'       => 'Invalid Ticket Status',
    'statusupdateconfirm'       => 'Ticket Status "%s" updated successfully',
    'displaycount'              => 'Display Ticket Count in Tree',
    'desc_displaycount'         => 'If enabled, the total Ticket count for this Status will be displayed in the Filter Tickets Tree. It is recommended that you disable this option for Statuses which are for Resolved or Fixed Tickets.',
    'statuscolor'               => 'Status Color',
    'desc_statuscolor'          => 'Enter the Color Code you would like to associate with this Status.',
    'statusdep'                 => 'Link Status with Department',
    'desc_statusdep'            => 'This option allows you to link this Status to an existing Department. If a Status is linked to a Department, it will be visible only under the linked Department.',
    'statusalldep'              => '-- Display under all Departments --',
    'statustype'                => 'Status Type',
    'errorstr'                  => 'ERROR',
    'resetduetime'              => 'Reset the Due Time Automatically',
    'desc_resetduetime'         => 'If Enabled, the system will automatically reset the due time for a ticket when the ticket status changes.',
    'statustype2'               => 'Status Type',
    'desc_statustype2'          => 'Select the type of Status, Public Status are visible in the Client Support Center whereas Private status types are visible to only the staff users',
    'ticketpriorities'          => 'Priorities',
    'desc_ticketpriorities'     => 'Priorities allow your clients to specify the level of urgency for a given ticket. You can create custom Ticket Priorities by clicking on the "Insert Priority" option in the Navigation Bar.',
    'prioritytitle'             => 'Title',
    'desc_prioritytitle'        => 'Enter your Ticket Priority Title',
    'prioritylist'              => 'Priority List',
    'prioritydetails'           => 'Priority Details',
    'insertpriority'            => 'Insert Priority',
    'prioritytype'              => 'Type',
    'desc_prioritytype'         => 'Specify the Priority Type. Private Priories are not visible to clients.',
    'registrationrequired'      => 'Registration Required?',
    'desc_registrationrequired' => 'If enabled, only registered clients will be able to create Tickets with this Priority.',
    'forecolor'                 => 'Foreground Listing Color',
    'desc_forecolor'            => 'If specified, the foreground color of a Ticket with this Priority will be set to the one chosen.',
    'bgcolor'                   => 'Background Listing Color',
    'desc_bgcolor'              => 'If specified, the background color of a Ticket with this Priority will be set to the one chosen.',
    'redirect_priorityinsert'   => 'Priority inserted successfully',
    'priorityinsertconfirm'     => 'Priority "%s" inserted successfully',
    'prioritydeleteconfirm'     => 'Ticket Priority "%s" deleted successfully',
    'templategroups'            => 'Template Groups',
    'desc_templategroups'       => 'Select the Template Groups that this Priority will be assigned to. This Priority will only be visible under the specified Template Groups.',
    'updatepriority'            => 'Update Priority',
    'selectonetgroup'           => 'ERROR: You need to select at least one Template Group. If you do not wish to display this Priority in the Support Center, please select Private as the Type.',
    'editpriority'              => 'Edit Priority',
    'invalidpriority'           => 'Invalid Ticket Priority',
    'priorityupdateconfirm'     => 'Ticket Priority "%s" updated successfully',
);


return $__LANG;
