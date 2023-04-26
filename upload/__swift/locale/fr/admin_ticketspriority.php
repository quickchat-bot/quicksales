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
    'tickets'                   => 'Tickets',
    'insertpriority'            => 'Ajouter la priorité',
    'tabgeneral'                => 'Généralités',
    'tabpermissions'            => 'Autorisations',
    'tablanguages'              => 'Langues',
    'prioritytitle'             => 'Titre de la priorité',
    'desc_prioritytitle'        => 'Par exemple, <em>élevé</em> ou <em>niveau 1</em>.',
    'prioritytype'              => 'Type de priorité',
    'desc_prioritytype'         => 'Les priorités <strong>privés</strong> sont visibles uniquement aux utilisateurs du personnel et non pas aux utilisateurs finaux. Les priorités <br /><strong>publiques</strong> sont visibles aux deux types d\'utilisateurs.',
    'forecolor'                 => 'Couleur de premier plan',
    'desc_forecolor'            => 'Eventuellement, spécifiez une couleur de premier plan (texte) pour la priorité.',
    'bgcolor'                   => 'Couleur d\'arrière-plan',
    'desc_bgcolor'              => 'Eventuellement, spécifiez une couleur d\'arrière-plan (ombrage) pour la priorité (assurez-vous que la couleur de premier plan ci-dessus soit visible par dessus la couleur de fond!).',
    'displayorder'              => 'Ordre d\'affichage',
    'desc_displayorder'         => 'Les priorités sont répertoriées selon leur ordre d\'affichage, du premier au dernier.',
    'displayiconpriority'       => 'Icône',
    'desc_displayiconpriority'  => 'Télécharger ou créér un lien vers une icône pour cette priorité. {$themepath} peut être utilisé pour pointer vers le répertoire d\'images du helpdesk, par exemple : <em>{$themepath}icon_prioritymedium.gif</em>.',
    'uservisibilitycustom'      => 'Restreindre la priorité à des groupes d\'utilisateurs spécifiques',
    'desc_uservisibilitycustom' => 'Si activée, seuls les groupes d\'utilisateurs ci-dessous pourront voir (et le cas échéant, sélectionner) cette priorité.',
    'usergroups'                => 'Groupes d\'utilisateurs',
    'invalidticketpriority'     => 'Un problème a été détecté (priorité de ticket non valide - s\'assurer que la priorité existe dans la base de données)',
    'editpriority'              => 'Modifier la priorité',
    'desc_editpriority'         => '',
    'managepriorities'          => 'Priorités',
    'desc_managepriorities'     => '',
    'wineditticketpriority'     => 'Modifier la priorité du ticket : %s',
    'titledelticketpriority'    => 'Priorités duticket supprimées (%d)',
    'msgdelticketpriority'      => 'Les priorités suivantes du ticket ont été supprimées :',
    'titleticketpriorityinsert' => 'Priorité de ticket %s créé',
    'msgticketpriorityinsert'   => 'La priorité (%s) a été créée avec succès.',
    'titleticketpriorityupdate' => 'Priorité de ticket %s mise à jour',
    'msgticketpriorityupdate'   => 'La priorité (%s) a été mise à jour avec succès.',
    'titlemasterprioritydelete' => 'Les priorités du tickets n\'ont pas pu être supprimées',
    'msgmasterprioritydelete'   => 'Impossible de supprimer les priorités suivantes du ticket principal :',

    // Potentialy unused phrases in admin_ticketspriority.php
    'desc_insertpriority'       => '',
    'prioritylist'              => 'Priority List',
    'prioritydetails'           => 'Priority Details',
    'registrationrequired'      => 'Registration Required?',
    'desc_registrationrequired' => 'If enabled, only registered clients will be able to create Tickets with this Priority.',
);

return $__LANG;
