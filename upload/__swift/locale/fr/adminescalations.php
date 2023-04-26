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
    // ======= BEGIN V4 LOCALES =======
    'escalations'                 => 'Escalades',
    'insertrule'                  => 'Ajouter une règle',
    'ruletitle'                   => 'Titre de la règle',
    'desc_ruletitle'              => 'Entrez un titre pour la règle d\'escalade, par exemple : <em>Tickets en retard</em>.',
    'escalationruledetails'       => 'Informations de la règle escalade',
    'nochange'                    => '--Aucun changement--',
    'escalationplan'              => 'Plan de SLA',
    'desc_escalationplan'         => 'Cette règle d\'escalade se déclenche si un billet est en retard selon le plan de SLA spécifié ici.',
    'escalationaction'            => 'Actions',
    'escalationstaff'             => 'Attribuer des tickets à un utilisateur du personnel',
    'desc_escalationstaff'        => '',
    'escalationpriority'          => 'Modifier la priorité du ticket',
    'desc_escalationpriority'     => '',
    'escalationticketstatus'      => 'Modifier l\'état du ticket',
    'desc_escalationticketstatus' => '',
    'escalationdepartment'        => 'Déplacer le ticket vers le service',
    'desc_escalationdepartment'   => '',
    'escalationslaplan'           => 'Changer le plan de SLA du ticket',
    'desc_escalationslaplan'      => '',
    'escalationflagtype'          => 'Signaler le ticket',
    'desc_escalationflagtype'     => '',
    'noplanavailable'             => '-- Non disponible --',
    'sendnotification'            => 'Envoyer une notification par email à l\'utilisateur',
    'desc_sendnotification'       => '',
    'titlenoplanavail'            => 'Aucun plans de SLA trouvé',
    'msgnoplanavail'              => 'Il doit exister au moins un plan de SLA avant que vous puissiez créer une règle d\'escalade, parce qu\'une règle d\'escalade détermine ce qui se arrive à un ticket qui ne satisfait pas les exigences d\'un plan de SLA.',
    'creationdate'                => 'Date de création',
    'wineditescalationrule'       => 'Modifier la règle d\'escalade : %s',
    'invalidescalationrule'       => 'Un problème a été détecté (règle d\'escalade non valide – s\'assurer que la règle existe dans la base de données)',
    'editrule'                    => 'Modifier la règle',
    'desc_editrule'               => '',
    'titledelescalationrule'      => 'Règles d\'escalade supprimées (%d)',
    'msgdelescalationrule'        => 'Les règles d\'escalade suivantes ont été supprimés :',
    'titleinsertescalationrule'   => 'Règle d\'escalade créé',
    'msginsertescalationrule'     => 'La règle d\'escalade (%s) a été créée avec succès.',
    'titleupdateescalationrule'   => 'Règle d\'escalade mise à jour',
    'msgupdateescalationrule'     => 'La règle d\'escalade (%s) a été mise à jour avec succès.',
    'manageescalations'           => 'Gérer',
    'desc_manageescalations'      => '',
    'tabnotifications'            => 'Notifications',
    'insertnotification'          => 'Ajouter une notification',
    'escalationtickettype'        => 'Modifier le type de ticket',
    'desc_escalationtickettype'   => '',
    'escalationaddtags'           => 'Baliser le ticket',
    'desc_escalationaddtags'      => '',
    'escalationremovetags'        => 'Supprimer les balises du ticket (si défini)',
    'desc_escalationremovetags'   => '',
    'eruletype'                   => 'Type d\'escalade',
    'desc_eruletype'              => 'Cette règle d\'escalade se déclenche quand un ticket est en retard par rapport au plan de SLA défini ci-dessus.<br /><br /><strong>Réponse en retard</strong> Cette règle d\'escalade se déclenche quand une date limite de réponse est dépassée.<br /><strong>Résolution en retard</strong> Se déclenche quand une date limite de résolution est dépassée.<br /><strong>Et/ou</strong> Se déclenche quand une date limite de réponse ou de résolution est dépassée.',
    'eruletype_due'               => 'Réponse en retard',
    'eruletype_resolutiondue'     => 'Résolution en retard',
    'eruletype_both'              => 'Soit la réponse, soit la résolution est en retard',
    // ======= END V4 LOCALES =======
);

return $__LANG;
