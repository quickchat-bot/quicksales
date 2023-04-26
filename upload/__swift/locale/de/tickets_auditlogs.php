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
    'duetimecleared'      => '-GELÖSCHT-',

    // Activity Log
    'log_newreply'        => 'Geantwortet auf Ticket: #%s',
    'log_forward'         => 'Weitergeleitetes Ticket: #%s an: %s',
    'log_newticket'       => 'Erstelltes Ticket: #%s: %s',

    // Audit Log
    'al_newticket'        => 'Neues Ticket erstellt von %s (%s): Betreff: %s', // DONE
    'al_newticket_queue'  => 'Neues Ticket erstellt von %s (%s): Betreff: %s, E-Mail-Warteschlange: %s', // DONE
    'al_newreply'         => 'Neue Antwort erstellt von %s (%s)', // DONE
    'al_watch'            => 'Ticket-Verfolgung aktiviert: #%s von: %s', // DONE
    'al_owner'            => 'Ticket-Eigentümer geändert von: %s auf: %s', // DONE
    'al_priority'         => 'Ticket-Priorität geändert von: %s auf: %s', // DONE
    'al_status'           => 'Ticket Status gändert von: %s auf: %s', // DONE
    'al_statusautoclose'  => 'Ticket Status gändert von: %s auf: %s durch automatische Schließung', // DONE
    'al_type'             => 'Ticket-Typ geändert von: %s auf: %s', // DONE
    'al_department'       => 'Ticket-Abteilung geändert von: %s auf: %s', // DONE
    'al_due'              => 'Fälligkeit eingestellt auf: %s', // DONE
    'al_resolutiondue'    => 'Auflösungszeit eingestellt auf: %s', // DONE
    'al_sla'              => 'DLV-Plan eigestellt auf: %s', // DONE
    'al_slaclear'         => 'Assoziierter DLV-Plan gelöscht', // DONE
    'al_flag'             => 'Ticket markiert: %s', // DONE
    'al_flagclear'        => 'Ticket-Markierung aufgehoben', // DONE
    'al_duestaffoverdue'  => 'Ticket als überfällig markiert', // DONE
    'al_duestaffclear'    => 'Ticket Fälligkeit gelöscht', // DONE
    'al_resduestaffclear' => 'Ticket-Auflösungszeit gelöscht', // DONE
    'al_ban'              => 'Ticket-Verfasser gesperrt: Ticket-ID: #%s, E-Mail-Adresse: %s', // DONE
    'al_merge'            => 'Zusammengeführtes Ticket: Ticket-ID: #%s, Betreff: %s, Name: %s, E-Mail-Adresse: %s', // DONE
    'al_untrashticket'    => 'Aus dem Papierkorb wiederhergestelltes Ticket: Ticket-ID: #%s, Betreff: %s, Name: %s, E-Mail-Adresse: %s', // DONE
    'al_deleteticket'     => 'Gelöschtes Ticket: Ticket-ID: #%s, Betreff: %s, Name: %s, E-Mail-Adresse: %s', // DONE
    'al_trashticket'      => 'In den Papierkorb verschobenes Ticket: Ticket-ID: #%s, Betreff: %s, Name: %s, E-Mail-Adresse: %s', // DONE
    'al_updateticketpost' => 'Aktualisierter Ticket-Beitrag: Verfasser: %s (%s)', // DONE
    'al_deleteticketpost' => 'Gelöschter Ticket-Beitrag: Verfasser: %s (%s)', // DONE
    'al_ticketnote'       => 'Ticket-Anmerkung zu Ticket hinzugefügt', // DONE
    'al_usernote'         => 'Benutzer-Anmerkung zu Ticket hinzugefügt', // DONE
    'al_deletenote'       => 'Ticket-Abrechnung gelöscht', // DONE
    'al_updatenote'       => 'Ticket-Anmerkung zu aktualisiert', // DONE
    'al_delbilling'       => 'Eintrag zut Ticket-Abrechnung gelöscht', // DONE
    'al_ticketbilling'    => 'Eintrag zur Ticket-Abrechnung zu Ticket hinzugefügt', // DONE
    'al_updticketbilling' => 'Eintrag zur Ticket-Abrechnung aktualisiert', // DONE
    'al_delfollowup'      => 'Ticket-Follow-Up gelöscht', // DONE
    'al_createfollowup'   => 'Ticket-Follow-Up erstellt', // DONE
    'al_updateproperties' => 'Ticket-Eigenschaften aktualisiert: Betreff: %s > %s, Name: %s > %s, E-Mail-Adresse: %s > %s',

    'al_newforward'       => 'Neue weitergeleitete Nachricht erstellt von %s (%s)',
    'al_escalated'        => 'Ticket eskaliert nach Regel: %s',
    'al_print'            => 'Ticket ausgedruckt von: %s',
    'al_cleardraft'       => 'Ticket-Entwurf gelöscht von: %s',
    'al_savedraft'        => 'Ticket-Entwurf gespeichert von: %s',
    'al_forward'          => 'Ticket weitergeleitet an: %s von: %s',
    'al_recipientdel'     => 'Ticket-Empfänger: %s gelöscht von: %s',
    'al_timetrackdel'     => 'Zeiterfassungs-Eintrag #%s gelöscht von: %s',
    'al_timetrack'        => 'Zeiterfassungs-Eintrag hinzugefügt: %s von: %s (Arbeitszeit: %s abzurechnende Zeit: %s)',
    'al_prule'            => 'Verarbeitet unter E-Mail-Regel: %s',
    'al_statusac'         => 'Ticket-Status gändert von: %s auf: %s durch automatische Schließung',
    'al_xmlexport'        => 'Als XML exportiertes Ticket: %s',
    'al_pdfexport'        => 'Als PDF exportiertes Ticket: %s',
);

return $__LANG;
