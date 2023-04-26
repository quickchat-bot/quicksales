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
    'tsticketid'                    => 'ID Ticket',
    'desc_tsticketid'               => 'L\'ID numerico di un ticket (questo viene utilizzato internamente dall\'helpdesk - può darsi che invece cerchi <strong>ID maschera Ticket</strong>).',
    'tsticketmaskid'                => 'ID maschera ticket',
    'desc_tsticketmaskid'           => 'Questo è l\'ID esterno del normale ticket (ABC-123-4567).',
    'tsfullname'                    => 'Nome del destinatario',
    'desc_tsfullname'               => 'Cerca i nomi dei destinatari che partecipano ad un ticket (cerca nei ticket e nelle risposte individuali dei ticket).',
    'tsemail'                       => 'Indirizzo email del destinatario',
    'desc_tsemail'                  => 'Cerca ticket dagli indirizzi email del destinatario.',
    'tslastreplier'                 => 'Nome dell\'ultimo risponditore',
    'desc_tslastreplier'            => 'Cerca il nome dell\'ultima persona che ha risposto ad un ticket.',
    'tsreplyto'                     => 'Mittente',
    'desc_tsreplyto'                => 'L\'indirizzo mittente specificato nell\'intestazione email. Questo è valido solo per i ticket creati tramite email.',
    'tssubject'                     => 'Oggetto del ticket',
    'desc_tssubject'                => 'Cerca l\'oggetto di un ticket.',
    'tsmessage'                     => 'Contenuto dei ticket',
    'desc_tsmessage'                => 'Cerca il contenuto delle risposte dei ticket.',
    'tsmessagelike'                 => 'Contenuto della risposta dei ticket (utilizzando SQL LIKE)',
    'desc_tsmessagelike'            => 'Cerca nel contenuto del messaggio utilizzando il metodo SQL LIKE.',
    'tsuser'                        => 'Nome account o indirizzi email dell\'utente',
    'desc_tsuser'                   => 'Cerca ticket creati da utenti (con nomi o indirizzi email che soddisfano questi criteri).',
    'tsuserorganization'            => 'Organizzazione dell\'utente',
    'desc_tsuserorganization'       => 'Cerca il nome d\'organizzazione degli utenti e dei destinatari che partecipano nei ticket.',
    'tsipaddress'                   => 'Indirizzo IP',
    'desc_tsipaddress'              => 'Se la risposta ad un ticket è stata fatta dal <strong>centro di supporto</strong>, l\'helpdesk potrebbe aver registrato l\'indirizzo IP dell\'utente.',
    'tscharset'                     => 'Set di caratteri',
    'desc_tscharset'                => 'Ticket di un determinato set di caratteri.',
    'tsphone'                       => 'Numero di telefono',
    'desc_tsphone'                  => 'Cerca il numero di telefono degli utenti e dei destinatari che partecipano nei ticket.',
    'tstimeworked'                  => 'Tempo lavorato',
    'desc_tstimeworked'             => 'Cerca ticket in base al tempo lavorato in secondi (nelle voci di fatturazione ticket e nel rilevamento del tempo).',
    'tstimebilled'                  => 'Tempo fatturabile',
    'desc_tstimebilled'             => 'Cerca ticket in base al tempo fatturabile in secondi (nelle voci di fatturazione ticket e nel rilevamento del tempo).',
    'tsdepartment'                  => 'Dipartimento',
    'desc_tsdepartment'             => 'Ticket che appartengono ad un dipartimento.',
    'tsowner'                       => 'Proprietario',
    'desc_tsowner'                  => 'Ticket assegnati ad un determinato utente del personale.',
    'tstype'                        => 'Tipo',
    'desc_tstype'                   => '',
    'tsstatus'                      => 'Stato',
    'desc_tsstatus'                 => '',
    'tspriority'                    => 'Priorità',
    'desc_tspriority'               => '',
    'tsemailqueue'                  => 'Coda email',
    'desc_tsemailqueue'             => 'Ticket che sono stati creati o risposti via email da una coda email specifica.',
    'tsslaplan'                     => 'Piano di SLA',
    'desc_tsslaplan'                => 'Ticket che sono attualmente assegnati ad un determinato piano di SLA.',
    'tsflag'                        => 'Contrassegno',
    'desc_tsflag'                   => '',
    'tstemplategroup'               => 'Gruppo di modelli',
    'desc_tstemplategroup'          => 'Ticket che appartengono ad un particolare gruppo di modelli.',
    'tsescalation'                  => 'Riassegnato dalla regola di escalation',
    'desc_tsescalation'             => 'Cerca ticket che sono stati riassegnati da una regola di escalation specifica.',
    'tsbayesian'                    => 'Categoria Bayesiana',
    'desc_tsbayesian'               => 'Ticket che sono stati abbinati a una categoria Bayesiana specifica.',
    'tsusergroup'                   => 'Gruppo di utenti',
    'desc_tsusergroup'              => 'Cerca ticket che hanno destinatari che appartengono ad un determinato gruppo di utenti.',
    'tscreator'                     => 'Ticket creato da',
    'desc_tscreator'                => '',
    'tscreationmode'                => 'Modalità creazione',
    'desc_tscreationmode'           => 'Cerca ticket in base a come sono stati creati.',
    'tsdue'                         => 'Scadenza della risposta',
    'desc_tsdue'                    => 'Ticket che hanno una scadenza della risposta prima o dopo questo tempo.',
    'tsduerange'                    => 'Scadenza della risposta <range>',
    'desc_tsduerange'               => 'Ticket che hanno una scadenza della risposta entro questo lasso di tempo.',
    'tsresolutiondue'               => 'Scadenza risoluzione',
    'desc_tsresolutiondue'          => 'Ticket che hanno una scadenza di risoluzione prima o dopo di questo tempo.',
    'tsresolutionduerange'          => 'Scadenza risoluzione <range>',
    'desc_tsresolutionduerange'     => 'Ticket che hanno una scadenza di risoluzione entro questo lasso di tempo.',
    'tscreationdate'                => 'Data di creazione',
    'desc_tscreationdate'           => 'Ticket che sono stati creati prima o dopo questo orario.',
    'tscreationdaterange'           => 'Data di creazione <range>',
    'desc_tscreationdaterange'      => 'Ticket che sono stati creati entro questo lasso di tempo.',
    'tslastactivity'                => 'Ultimo aggiornamento',
    'desc_tslastactivity'           => 'Ticket che sono stati aggiornati (es. risposto da qualcuno o qualsiasi altro evento) prima o dopo questo orario.',
    'tslastactivityrange'           => 'Ultimo aggiornamento <range>',
    'desc_tslastactivityrange'      => 'Ticket che sono stati aggiornati (es. risposto da qualcuno o qualsiasi altro evento) entro questo lasso di tempo.',
    'tslaststaffreply'              => 'Ultima risposta da parte del personale',
    'desc_tslaststaffreply'         => 'Ticket che hanno ricevuto una risposta da un utente del personale entro questo lasso di tempo.',
    'tslaststaffreplyrange'         => 'Ultima risposta da parte del personale <range>',
    'desc_tslaststaffreplyrange'    => 'Ticket che hanno ricevuto una risposta da un utente del personale entro questo lasso di tempo.',
    'tslastuserreply'               => 'Ultima risposta dall\'utente',
    'desc_tslastuserreply'          => 'Ticket che hanno ricevuto una risposta da un utente prima o dopo questo orario.',
    'tslastuserreplyrange'          => 'Ultima risposta dall\'utente <range>',
    'desc_tslastuserreplyrange'     => 'Ticket che hanno ricevuto una risposta da un utente entro questo lasso di tempo.',
    'tsescalateddate'               => 'Data di escalation',
    'desc_tsescalateddate'          => 'Ticket che sono andati in escalation ( in ritardo) prima o dopo questo periodo di tempo.',
    'tsescalateddaterange'          => 'Data di escalation <range>',
    'desc_tsescalateddaterange'     => 'Ticket che sono andati in escalation (in ritardo) entro questo lasso di tempo.',
    'tsresolutiondate'              => 'Scadenza risoluzione',
    'desc_tsresolutiondate'         => 'Ticket che hanno una scadenza di risoluzione prima o dopo di questo tempo.',
    'tsresolutiondaterange'         => 'Scadenza risoluzione <range>',
    'desc_tsresolutiondaterange'    => 'Ticket che hanno una scadenza di risoluzione entro questo lasso di tempo.',
    'tsreopendate'                  => 'Data di riapertura',
    'desc_tsreopendate'             => 'Cerca ticket dall\'orario che sono stati riaperti (con lo stato cambiato da <strong>risolto</strong> ad <strong>aperto</strong>) prima o dopo questo periodo di tempo.',
    'tsreopendaterange'             => 'Data di riapertura <range>',
    'desc_tsreopendaterange'        => 'Cerca ticket dall\'orario che sono stati riaperti (con lo stato cambiato da <strong>risolto</strong> ad <strong>aperto</strong>) entro questo lasso di tempo.',
    'tsedited'                      => 'È stato modificato',
    'desc_tsedited'                 => 'Ticket che sono stati modificati.',
    'tseditedby'                    => 'Modificato da',
    'desc_tseditedby'               => 'Cerca ticket modificati da un determinato utente del personale.',
    'tsediteddate'                  => 'Data modificato',
    'desc_tsediteddate'             => 'Ticket che sono stati modificati prima o dopo questo periodo di tempo.',
    'tsediteddaterange'             => 'Data modificato <range>',
    'desc_tsediteddaterange'        => 'Ticket che sono stati modificati entro questo lasso di tempo.',
    'tstotalreplies'                => 'Totale risposte',
    'desc_tstotalreplies'           => 'Ticket che hanno questo numero di risposte.',
    'tshasnotes'                    => 'Il ticket ha note',
    'desc_tshasnotes'               => '',
    'tshasattachments'              => 'Il ticket contiene allegati',
    'desc_tshasattachments'         => '',
    'tsisemailed'                   => 'Creato da email',
    'desc_tsisemailed'              => 'Ticket che sono stati creati via email.',
    'tshasdraft'                    => 'Il ticket ha una bozza',
    'desc_tshasdraft'               => 'Ticket nel quale è salvata una risposta in bozza.',
    'tshasfollowup'                 => 'Follow-up in sospeso',
    'desc_tshasfollowup'            => 'Ticket che hanno follow-up pianificati.',
    'tsislinked'                    => 'Il ticket è collegato ad un altro',
    'desc_tsislinked'               => 'Cerca ticket che sono stati collegati l\'uno all\'altro',
    'tsisfirstcontactresolved'      => 'Risolto al primo contatto',
    'desc_tsisfirstcontactresolved' => 'Cerca ticket che sono stati risolti alla prima risposta (da un utente del personale).',
    'tsaverageresponsetime'         => 'Tempo medio di risposta',
    'desc_tsaverageresponsetime'    => 'Cerca ticket con un determinato tempo medio di risposta (tra le risposte degli utenti e del personale).',
    'tsescalationlevelcount'        => 'Numero di escalation',
    'desc_tsescalationlevelcount'   => 'Cerca ticket in base a quante volte hanno subito un escalation (sono andati in ritardo).',
    'tswasreopened'                 => 'Il ticket è stato riaperto',
    'desc_tswasreopened'            => 'Cerca ticket che sono stati riaperti (modificati dallo stato <strong>risolto</strong> allo stato<strong>aperto</strong>).',
    'tsisresolved'                  => 'Il ticket è risolto',
    'desc_tsisresolved'             => 'Cerca ticket che sono stati risolti (impostati sullo stato <strong>risolto</strong>).',
    'tsresolutionlevel'             => 'Numero di proprietari ticket prima della risoluzione',
    'desc_tsresolutionlevel'        => 'Cerca un ticket secondo quanti proprietari ha avuto prima che fosse risolto.',
    'tsticketnotes'                 => 'Contenuti delle note dei ticket',
    'desc_tsticketnotes'            => 'Cerca nei contenuti delle note dei ticket.',
    'tsgeneraloptions'              => 'Criteri generali dei ticket',
    'tsdateoptions'                 => 'Criteri di data',
    'tsmiscellaneous'               => 'Criteri vari',
    'tstag' => 'Tag',
    'desc_tstag' => 'Tickets that belong to a particular tag.',

    /**
     * ---------------------------------------------
     * OTHER LOCALES
     * ---------------------------------------------
     */
    'notapplicable'                 => '-- Non Applicabile --',
    'lookup'                        => 'Ricerca',
);


return $__LANG;
