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
    'escalations'                 => 'Escalation',
    'insertrule'                  => 'Inserisci Regola',
    'ruletitle'                   => 'Titolo regola',
    'desc_ruletitle'              => 'Immetti un titolo per la regola di escalation, ad esempio: <em>Ticket in ritardo</em>.',
    'escalationruledetails'       => 'Dettagli della Regola di Escalation',
    'nochange'                    => '-- Nessun cambiamento --',
    'escalationplan'              => 'Piano di SLA',
    'desc_escalationplan'         => 'Questa regola di escalation si attiverà se un ticket va in ritardo secondo il piano di SLA qui specificato.',
    'escalationaction'            => 'Azioni',
    'escalationstaff'             => 'Assegna il ticket ad un utente del personale',
    'desc_escalationstaff'        => '',
    'escalationpriority'          => 'Modifica la priorità del ticket',
    'desc_escalationpriority'     => '',
    'escalationticketstatus'      => 'Modifica lo stato del ticket',
    'desc_escalationticketstatus' => '',
    'escalationdepartment'        => 'Sposta il ticket al dipartimento',
    'desc_escalationdepartment'   => '',
    'escalationslaplan'           => 'Cambia il piano di SLA del ticket',
    'desc_escalationslaplan'      => '',
    'escalationflagtype'          => 'Contrassegna il ticket',
    'desc_escalationflagtype'     => '',
    'noplanavailable'             => '-- Non Disponibile --',
    'sendnotification'            => 'Invia una notifica email all\'utente',
    'desc_sendnotification'       => '',
    'titlenoplanavail'            => 'Nessun piano di SLA trovato',
    'msgnoplanavail'              => 'È necessario che ci sia almeno un piano di SLA prima che si possa creare una regola di escalation perché la regola di escalation determina ciò che accade ad un ticket in violazione di un piano di SLA.',
    'creationdate'                => 'Data di Creazione',
    'wineditescalationrule'       => 'Modifica Regola di Escalation: %s',
    'invalidescalationrule'       => 'È stato riscontrato un problema (regola di escalation non valida - assicurati che la regola esista nel database)',
    'editrule'                    => 'Modifica Regola',
    'desc_editrule'               => '',
    'titledelescalationrule'      => 'Regole di escalation eliminate (%d)',
    'msgdelescalationrule'        => 'Sono state eliminate le seguenti regole di escalation:',
    'titleinsertescalationrule'   => 'Creata regola di escalation',
    'msginsertescalationrule'     => 'La regola di escalation (%s) è stata creata con successo.',
    'titleupdateescalationrule'   => 'Regola di escalation aggiornata',
    'msgupdateescalationrule'     => 'La regola di escalation (%s) è stata aggiornata con successo.',
    'manageescalations'           => 'Gestisci',
    'desc_manageescalations'      => '',
    'tabnotifications'            => 'Notifiche',
    'insertnotification'          => 'Inserisci Notifica',
    'escalationtickettype'        => 'Cambia il tipo di ticket',
    'desc_escalationtickettype'   => '',
    'escalationaddtags'           => 'Assegna un tag al ticket',
    'desc_escalationaddtags'      => '',
    'escalationremovetags'        => 'Rimuovi i tag dal ticket (se impostati)',
    'desc_escalationremovetags'   => '',
    'eruletype'                   => 'Tipo di escalation',
    'desc_eruletype'              => 'Questa regola di escalation si attiverà quando un ticket è in ritardo secondo il piano di SLA specificato sopra. <br /><br /><strong>Risposta in ritardo</strong> Questa regola di escalation si attiverà quando si supera la scadenza della risposta.<br /><strong>Risoluzione in ritardo</strong> Si attiva quando si supera la scadenza della risoluzione.<br /><strong>Entrambi</strong> Si attiva quando si supera la scadenza della risposta o della risoluzione.',
    'eruletype_due'               => 'Risposta in ritardo',
    'eruletype_resolutiondue'     => 'Risoluzione in ritardo',
    'eruletype_both'              => 'Risposta o risoluzione in ritardo',
    // ======= END V4 LOCALES =======
);

return $__LANG;
