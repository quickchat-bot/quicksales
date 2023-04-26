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
    'notifications'                  => 'Notifiche',
    'manage'                         => 'Gestisci',
    'ruletitle'                      => 'Titolo regola',
    'desc_ruletitle'                 => 'Immetti un titolo per la regola di notifica.',
    'isenabled'                      => 'Regola di notifica abilitata',
    'desc_isenabled'                 => 'Scegli se questa regola è abilitata o disabilitata.',
    'ruletype'                       => 'Tipo',
    'desc_ruletype'                  => 'La regola di notifica può essere associata ad uno dei seguenti elementi.',
    'next'                           => 'Avanti',
    'creator'                        => 'Autore',
    'wininsertnotification'          => 'Inserisci Notifica',

    'unassigned'                     => '-- Non assegnato/i --',

    'tabgeneral'                     => 'Generale',
    'tabactions'                     => 'Azioni',
    'insertcriteria'                 => 'Inserisci Criteri',

    'na_email'                       => 'Email',
    'na_sms'                         => 'SMS',
    'na_pool'                        => 'Applicazione desktop',
    'na_staff'                       => 'Il proprio account utente del personale',
    'na_staffgroup'                  => 'Squadra',
    'na_department'                  => 'Dipartimento',
    'na_user'                        => 'Utente',
    'nactionemaildispatch'           => 'Invia email al proprietario del ticket...',
    'desc_nactionemaildispatch'      => '',
    'nadispatchemail'                => 'Invia email ad altri indirizzi email',
    'desc_nadispatchemail'           => '',
    'nadispatchemailstaff'           => 'Invia email ad altro personale',
    'desc_nadispatchemailstaff'      => '',
    'nactionpooldispatch'            => 'Invia notifica al proprietario del ticket attivo...',
    'desc_nactionpooldispatch'       => '',
    'nactionpoolcustomdispatch'      => 'Personale',
    'desc_nactionpoolcustomdispatch' => '',
    'notificationruledesc'           => 'Seleziona un tipo di regola di notifica.',
    'titlenocriteria'                => 'Nessun criterio specificato',
    'msgnocriteria'                  => 'Almeno un criterio è necessario per creare una regola di notifica.',
    'titlenotificationinsert'        => 'Creata regola di notifica (%s)',
    'msgnotificationinsert'          => 'La regola di notifica (%s) è stata creata con successo.',
    'titlenotificationupdate'        => 'Aggiornata regola di notifica (%s)',
    'msgnotificationupdate'          => 'La regola di notifica (%s) è stata aggiornata con successo.',
    'tabnotifyapp'                   => 'Notify App',
    'tabemail'                       => 'Email',
    'titleenablenotification'        => 'Regole di notifica abilitate (%d)',
    'msgenablenotification'          => 'Le seguenti regole di notifica sono state abilitate:<br />%s',
    'titledisablenotification'       => 'Regole di notifica disabilitate (%d)',
    'msgdisablenotification'         => 'Le seguenti regole di notifica sono state disabilitate:<br />%s',

    'emailprefix'                    => 'Prefisso oggetto email',
    'desc_emailprefix'               => 'Specifica il prefisso dell\'oggetto dell\'email per identificare facilmente le email inviate da questa regola di notifica.',

    /**
     * ---------------------------------------------
     * RULE TYPES
     * ---------------------------------------------
     */
    'nrule_chat'                     => 'Chat',
    'nrule_message'                  => 'Messaggio Non In Linea',
    'nrule_survey'                   => 'Sondaggio Chat',
    'nrule_ticket'                   => 'Ticket',
    'nrule_user'                     => 'Utente',

    /**
     * ---------------------------------------------
     * RULE CRITERIA: TICKETS
     * ---------------------------------------------
     */

    'ntickettype'                    => 'Tipo di ticket',
    'desc_ntickettype'               => '',
    'nbayesian'                      => 'Categoria Bayesiana',
    'desc_nbayesian'                 => 'Ticket che sono stati abbinati ad una categoria Bayesiana specifica.',
    'notapplicable'                  => '-- Non Disponibile --',
    'nticketstatus'                  => 'Stato del ticket',
    'desc_nticketstatus'             => '',
    'nticketpriority'                => 'Priorità del ticket',
    'desc_nticketpriority'           => '',
    'nusergroup'                     => 'Gruppo di utenti',
    'desc_nusergroup'                => 'Cerca ticket che hanno destinatari che appartengono ad un particolare gruppo di utenti.',
    'ndepartment'                    => 'Dipartimento del ticket',
    'desc_ndepartment'               => 'Ticket che appartengono ad un dipartimento.',
    'nowner'                         => 'Proprietario del ticket',
    'desc_nowner'                    => 'Ticket assegnati ad un determinato utente del personale.',
    'nunassigned'                    => '-- Non Assegnato/i --',
    'nactivestaff'                   => '-- Personale Attivo --',
    'nemailqueue'                    => 'Coda email',
    'desc_nemailqueue'               => 'Ticket che sono stati creati o risposti via email da una coda email specifica.',
    'nflagtype'                      => 'Contrassegno ticket',
    'desc_nflagtype'                 => '',
    'ncreator'                       => 'Ticket creato da',
    'desc_ncreator'                  => '',
    'creatorstaff'                   => 'Personale',
    'creatorclient'                  => 'Utente',
    'nslaplan'                       => 'Piano di SLA',
    'desc_nslaplan'                  => 'Ticket che sono assegnati attualmente ad un determinato piano di SLA.',
    'ndaterange'                     => 'Data di creazione ticket <range>',
    'desc_ndaterange'                => 'Ticket che sono stati creati entro questo lasso di tempo.',
    'nlastactivityrange'             => 'Ultimo aggiornamento ticket <range>',
    'desc_nlastactivityrange'        => 'Ticket che sono stati aggiornati (es. risposto da qualcuno o qualsiasi altro evento) entro questo lasso di tempo.',
    'nlaststaffreplyrange'           => 'Ultima risposta da parte del personale <range>',
    'desc_nlaststaffreplyrange'      => 'Ticket che hanno ricevuto una risposta da un utente del personale entro questo lasso di tempo.',
    'nlastuserreplyrange'            => 'Ultima risposta dall\'utente <range>',
    'desc_nlastuserreplyrange'       => 'Ticket che hanno ricevuto una risposta da un utente entro questo lasso di tempo.',
    'nduerange'                      => 'Scadenza della risposta <range>',
    'desc_nduerange'                 => 'Ticket che hanno una scadenza della risposta entro questo lasso di tempo.',
    'nisoverdue'                     => 'Scaduto: Risposta in ritardo',
    'desc_nisoverdue'                => 'Ticket che sono in ritardo (perché non hanno avuto una risposta prima della scadenza della risposta).',
    'nresolutionduerange'            => 'Scadenza risoluzione <range>',
    'desc_nresolutionduerange'       => 'Ticket che hanno una scadenza di risoluzione entro questo lasso di tempo.',
    'nisresolutionoverdue'           => 'Scaduto: Risoluzione in ritardo',
    'desc_nisresolutionoverdue'      => 'Ticket che sono in ritardo (perché non sono stati risolti prima della scadenza di risoluzione).',
    'ntemplategroup'                 => 'Gruppo di modelli',
    'desc_ntemplategroup'            => 'Ticket che appartengono ad un particolare gruppo di modelli.',
    'ntimeworked'                    => 'Tempo lavorato (in minuti)',
    'desc_ntimeworked'               => 'Ticket che corrispondono al valore <strong>tempo lavorato</strong> (come parte delle voci di fatturazione ticket e rilevamento del tempo).',
    'ntotalreplies'                  => 'Totale risposte',
    'desc_ntotalreplies'             => 'Ticket che hanno questo numero di risposte.',
    'npendingfollowups'              => 'Follow-up in sospeso',
    'desc_npendingfollowups'         => 'Ticket che hanno follow-up pianificati.',
    'nipaddress'                     => 'Indirizzo IP',
    'desc_nipaddress'                => 'Se la risposta ad un ticket è stata fatta dal <strong>centro di supporto</strong>, l\'helpdesk potrebbe aver registrato l\'indirizzo IP dell\'utente.',
    'nisemailed'                     => 'Creato da email',
    'desc_nisemailed'                => 'Ticket che sono stati creati via email.',
    'nisedited'                      => 'È stato modificato',
    'desc_nisedited'                 => 'Ticket che sono stati modificati.',
    'nhasnotes'                      => 'Ha note',
    'desc_nhasnotes'                 => 'Ticket che hanno note ticket.',
    'nhasattachments'                => 'Ha allegati',
    'desc_nhasattachments'           => 'Ticket che hanno file allegati.',
    'nisescalated'                   => 'È andato in escalation',
    'desc_nisescalated'              => 'Ticket che sono stati riassegnati da una o più regole di escalation.',
    'nhasdraft'                      => 'Ha una bozza',
    'desc_nhasdraft'                 => 'Ticket nel quale è salvata una risposta in bozza.',
    'nhasbilling'                    => 'Ha voci di fatturazione e rilevamento del tempo',
    'desc_nhasbilling'               => 'Ticket che hanno voci di fatturazione e rilevamento del tempo.',
    'nisphonecall'                   => 'È un ticket telefonico',
    'desc_nisphonecall'              => 'Ticket che è stato contrassegnato come ticket di tipo <em>telefonico</em>.',
    'nislabeled'                     => 'È taggato',
    'desc_nislabeled'                => 'Ticket che sono stati taggati.',

    'nticketevent'                   => 'Evento',
    'desc_nticketevent'              => '',
    'tevent_newticket'               => 'Nuovo ticket creato',
    'tevent_newticketnotes'          => 'Aggiunta nuova nota ticket',
    'tevent_newclientreply'          => 'Nuova risposta dall\'utente finale',
    'tevent_newclientsurvey'         => 'Invio nuovo sondaggio',
    'tevent_newstaffreply'           => 'Nuova risposta dal personale',
    'tevent_ticketassigned'          => 'Ticket assegnato',
    'tevent_newuser'                 => 'Registrazione nuovo utente',

    'nuserevent'                     => 'Evento',
    'desc_nuserevent'                => 'Questa regola si attiverà con questo evento.',
);

return $__LANG;
