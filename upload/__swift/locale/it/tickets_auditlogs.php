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
    'duetimecleared'      => '- CANCELLATO -',

    // Activity Log
    'log_newreply'        => 'Risposto al ticket: n. %s',
    'log_forward'         => 'Inoltrato il ticket: n. %s a: %s',
    'log_newticket'       => 'Creato ticket n. %s: %s',

    // Audit Log
    'al_newticket'        => 'Nuovo ticket creato da %s (%s): oggetto: %s', // DONE
    'al_newticket_queue'  => 'Nuovo ticket creato da %s (%s): oggetto: %s, coda email: %s', // DONE
    'al_newreply'         => 'Nuova risposta creata da %s (%s)', // DONE
    'al_watch'            => 'Osservazione ticket attiavata: n. %s da: %s', // DONE
    'al_owner'            => 'Proprietario del ticket modificato da: %s a: %s', // DONE
    'al_priority'         => 'Priorità del ticket modificata da: %s a: %s', // DONE
    'al_status'           => 'Stato del ticket modificato da: %s a: %s', // DONE
    'al_statusautoclose'  => 'Stato del ticket modificato da: %s a: %s dalla chiusura automatica', // DONE
    'al_type'             => 'Tipo di ticket modificato da: %s a: %s', // DONE
    'al_department'       => 'Dipartimento del ticket modificato da: %s a: %s', // DONE
    'al_due'              => 'Imposto la scadenza a: %s', // DONE
    'al_resolutiondue'    => 'Imposto la scadenza della risoluzione a: %s', // DONE
    'al_sla'              => 'Piano di SLA impostato a: %s', // DONE
    'al_slaclear'         => 'Eliminato il piano di SLA associato', // DONE
    'al_flag'             => 'Ticket contrassegnato: %s', // DONE
    'al_flagclear'        => 'Contrassegno ticket cancellato', // DONE
    'al_duestaffoverdue'  => 'Ticket contrassegnato come in ritardo', // DONE
    'al_duestaffclear'    => 'Scadenza ticket azzerata', // DONE
    'al_resduestaffclear' => 'Azzerata scadenza della risoluzione del ticket', // DONE
    'al_ban'              => 'Bloccato autore ticket: ID ticket: n. %s, indirizzo email: %s', // DONE
    'al_merge'            => 'Ticket unito: ID ticket: n. %s, oggetto: %s, nome: %s, indirizzo email: %s', // DONE
    'al_untrashticket'    => 'Ticket recuperato dal cestino: ID ticket: n. %s, oggetto: %s, nome: %s, indirizzo email: %s', // DONE
    'al_deleteticket'     => 'Ticket eliminato: ID ticket: n. %s, oggetto: %s, nome: %s, indirizzo email: %s', // DONE
    'al_trashticket'      => 'Ticket spostato nel cestino: ID ticket: n. %s, oggetto: %s, nome: %s, indirizzo email: %s', // DONE
    'al_updateticketpost' => 'Aggiornato messaggio ticket: autore: %s (%s)', // DONE
    'al_deleteticketpost' => 'Eliminato messaggio ticket: autore: %s (%s)', // DONE
    'al_ticketnote'       => 'Aggiunta nota ticket al ticket', // DONE
    'al_usernote'         => 'Aggiunta nota utente al ticket', // DONE
    'al_deletenote'       => 'Eliminata nota ticket', // DONE
    'al_updatenote'       => 'Aggiornata nota ticket', // DONE
    'al_delbilling'       => 'Eliminata voce di fatturazione del ticket', // DONE
    'al_ticketbilling'    => 'Aggiunta voce di fatturazione al ticket', // DONE
    'al_updticketbilling' => 'Aggiornata voce di fatturazione del ticket', // DONE
    'al_delfollowup'      => 'Cancellato il follow-up del ticket', // DONE
    'al_createfollowup'   => 'Creato follow-up al ticket', // DONE
    'al_updateproperties' => 'Aggiornate le proprietà del ticket: oggetto: %s > %s, nome: %s > %s, indirizzo email: %s > %s',

    'al_newforward'       => 'Nuovo messaggio di inoltro creato da %s (%s)',
    'al_escalated'        => 'Escalation del ticket secondo la regola: %s',
    'al_print'            => 'Ticket stampato da: %s',
    'al_cleardraft'       => 'Bozza del ticket cancellata da: %s',
    'al_savedraft'        => 'Bozza del ticket salvata da: %s',
    'al_forward'          => 'Ticket inoltrato a: %s da: %s',
    'al_recipientdel'     => 'Destinatario ticket: %s è stato eliminato da: %s',
    'al_timetrackdel'     => 'La voce di rilevamento del tempo n. %s è stata eliminata da: %s',
    'al_timetrack'        => 'È stata aggiunta una voce per il rilevamento del tempo per: %s da: %s (tempo lavorato: %s fatturabile: %s)',
    'al_prule'            => 'Elaborati secondo la regola email: %s',
    'al_statusac'         => 'Stato del ticket modificato da: %s a: %s dalla chiusura automatica',
    'al_xmlexport'        => 'Ticket esportato in formato XML da: %s',
    'al_pdfexport'        => 'Ticket esportato in formato PDF da: %s',
);

return $__LANG;
