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
    'duetimecleared'      => '- EFFACÉ -',

    // Activity Log
    'log_newreply'        => 'A répondu au ticket : #%s',
    'log_forward'         => 'A transféré le ticket : #%s à: %s',
    'log_newticket'       => 'Ticket créé : #%s: %s',

    // Audit Log
    'al_newticket'        => 'Nouveau ticket créé par %s (%s) : objet : %s', // DONE
    'al_newticket_queue'  => 'Nouveau ticket créé par %s (%s) : objet : %s, file d\'attente : %s', // DONE
    'al_newreply'         => 'Nouvelle réponse créée par %s (%s)', // DONE
    'al_watch'            => 'Suivi du ticket activé : #%s par : %s', // DONE
    'al_owner'            => 'La propriété du ticket est passée de : %s à : %s', // DONE
    'al_priority'         => 'La priorité du ticket est passée de : %s à : %s', // DONE
    'al_status'           => 'L\'état du ticket est passé de : %s à : %s', // DONE
    'al_statusautoclose'  => 'L\'état du ticket est passé de : %s : à %s par fermeture automatique', // DONE
    'al_type'             => 'Le type du ticket type est passé de : %s à : %s', // DONE
    'al_department'       => 'Le ticket est passé du service : %s au service : %s', // DONE
    'al_due'              => 'Définir l\'heure limite sur : %s', // DONE
    'al_resolutiondue'    => 'Définir l\'heure limite de résolution sur : %s', // DONE
    'al_sla'              => 'Plan de SLA défini sur : %s', // DONE
    'al_slaclear'         => 'Plan de SLA lié supprimé', // DONE
    'al_flag'             => 'Ticket signalé : %s', // DONE
    'al_flagclear'        => 'Drapeau de ticket effacé', // DONE
    'al_duestaffoverdue'  => 'Ticket marqué comme en retard', // DONE
    'al_duestaffclear'    => 'Heure limite du ticket effacée', // DONE
    'al_resduestaffclear' => 'Heure limite de résolution du ticket effacée', // DONE
    'al_ban'              => 'Créateur du ticket banni : ID de ticket : #%s, adresse email : %s', // DONE
    'al_merge'            => 'Ticket fusionné : ID du ticket : #%s, objet: %s, nom: %s, adresse email : %s', // DONE
    'al_untrashticket'    => 'Ticket récupéré de la corbeille: ID du ticket : #%s, objet: %s, nom: %s, adresse email : %s', // DONE
    'al_deleteticket'     => 'Ticket supprimé : ID de ticket : #%s, objet: %s, nom: %s, adresse email : %s', // DONE
    'al_trashticket'      => 'Ticket envoyé à la corbeille: ID du ticket : #%s, objet: %s, nom : %s, adresse email : %s', // DONE
    'al_updateticketpost' => 'Message de ticket  mis à jour : créateur : %s (%s)', // DONE
    'al_deleteticketpost' => 'Message de ticket supprimé : créateur : %s (%s)', // DONE
    'al_ticketnote'       => 'Remarque de ticket ajoutée au ticket', // DONE
    'al_usernote'         => 'Remarque d\'utilisateur ajoutée au billet', // DONE
    'al_deletenote'       => 'Remarque de ticket supprimée', // DONE
    'al_updatenote'       => 'Remarque de ticket mise à jour', // DONE
    'al_delbilling'       => 'Entrée de facturation de ticket supprimée', // DONE
    'al_ticketbilling'    => 'Entrée de facturation de ticket ajoutée', // DONE
    'al_updticketbilling' => 'Entrée de facturation de ticket mise à jour', // DONE
    'al_delfollowup'      => 'Suivi de ticket supprimé', // DONE
    'al_createfollowup'   => 'Suivi de ticket créé', // DONE
    'al_updateproperties' => 'Mise à jour des propriétés de ticket : objet : %s > %s, nom : %s > %s, adresse email : %s > %s',

    'al_newforward'       => 'Nouveau message de transfert créé par %s (%s)',
    'al_escalated'        => 'Ticket escaladé sous la règle : %s',
    'al_print'            => 'Ticket imprimé par : %s',
    'al_cleardraft'       => 'Projet de ticket effacé par : %s',
    'al_savedraft'        => 'Projet de ticket enregistré par : %s',
    'al_forward'          => 'Ticket envoyé à : %s par : %s',
    'al_recipientdel'     => 'Destinataire du ticket : %s supprimé par : %s',
    'al_timetrackdel'     => 'Entrée de suivi du temps #%s supprimée par : %s',
    'al_timetrack'        => 'Entrée de suivi du temps ajoutée pour : %s par : %s (temps travaillé : %s temps facturable %s)',
    'al_prule'            => 'Traitées par la règle d\'email : %s',
    'al_statusac'         => 'L\'état du ticket est passé de : %s : à %s par fermeture automatique',
    'al_xmlexport'        => 'Ticket exporté comme XML par : %s',
    'al_pdfexport'        => 'Ticket exporté comme PDF par : %s',
);

return $__LANG;
