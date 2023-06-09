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
    // ======= BEGIN v4 LOCALES =======
    'tickets'                    => 'Ticket',
    'insertworkflow'             => 'Inserisci Flusso di lavoro',
    'desc_insertworkflow'        => '',
    'tabgeneral'                 => 'Generale',
    'tabactions'                 => 'Azioni',
    'workflowtitle'              => 'Etichetta della regola del flusso di lavoro',
    'desc_workflowtitle'         => 'L\'etichetta di una regola del flusso di lavoro viene visualizzata (dove applicabile) nel pannello di controllo del personale. Cliccando sull\'etichetta si esegue il flusso.',
    'isenabled'                  => 'Flusso di lavoro attivato',
    'desc_isenabled'             => 'Se una regola del flusso di lavoro è disabilitata non apparirà mai.',
    'insertcriteria'             => 'Inserisci Criteri',
    'sortorder'                  => 'Ordine di visualizzazione',
    'desc_sortorder'             => 'Le regole del flusso di lavoro vengono visualizzate secondo il numero di ordine visualizzato, dal più piccolo al più grande.',
    'editworkflow'               => 'Modifica Flusso di lavoro',
    'manageworkflows'            => 'Flussi di lavoro',
    'creationdate'               => 'Data di Creazione',
    'titleworkflowdel'           => 'Regole del flusso di lavoro eliminate (%d)',
    'msgworkflowdel'             => 'Le seguenti regole del flusso di lavoro sono state eliminate:',
    'tabnotifications'           => 'Notifiche',
    'insertnotification'         => 'Inserisci Notifica',

    'tabpermissions'             => 'Autorizzazioni del Personale',
    'staffvisibilitycustom'      => 'Limita il flusso di lavoro a specifiche squadre di personale',
    'desc_staffvisibilitycustom' => 'Se abilitata, questa regola del flusso di lavoro apparirà soltanto alle squadre qui sotto (supponendo che siano soddisfatti gli altri criteri).',
    'uservisibility'             => 'Abilita per gli utenti',
    'desc_uservisibility'        => 'Se abilitata, questa regola del flusso di lavoro verrà visualizzata per gli utenti (supponendo che gli altri criteri siano soddisfatti)',
    'staffgroups'                => 'Squadre del Personale',

    // Actions
    'nochange'                   => '-- Nessun Cambiamento --',
    'actionstaff'                => 'Assegna il ticket',
    'desc_actionstaff'           => 'Il ticket verrà assegnato a questo utente del personale.',
    'actionpriority'             => 'Modifica la priorità del ticket',
    'desc_actionpriority'        => 'Il ticket sarà modificato a questa priorità.',
    'actionticketstatus'         => 'Modifica lo stato del ticket',
    'desc_actionticketstatus'    => 'Il ticket sarà modificato a questo stato.',
    'actiondepartment'           => 'Sposta il ticket al dipartimento',
    'desc_actiondepartment'      => 'Il ticket verrà spostato a questo reparto.',
    'actionslaplan'              => 'Cambia il piano di SLA del ticket',
    'desc_actionslaplan'         => 'Il ticket verrà assegnato a questo piano di SLA.',
    'actionflagtype'             => 'Segnala ticket',
    'desc_actionflagtype'        => 'Il ticket verrà segnato con questo colore.',
    'noplanavailable'            => '-- Non Disponibile --',
    'actionnotes'                => 'Aggiungi una nota al ticket',
    'desc_actionnotes'           => 'Verrà aggiunta una nota al ticket.',
    'titlenoaction'              => 'Nessuna azione specificata',
    'msgnoaction'                => 'Almeno un\'azione deve essere specificata per una regola del flusso di lavoro.',
    'actionaddtags'              => 'Aggiungi tag al ticket',
    'desc_actionaddtags'         => 'Questi tag verranno aggiunti al ticket.',
    'actionremovetags'           => 'Rimuovi tag dal ticket',
    'desc_actionremovetags'      => 'Se il ticket ha uno di questi tag, verranno rimossi.',
    'actiontickettype'           => 'Cambia il tipo di ticket',
    'desc_actiontickettype'      => 'Il tipo di ticket verrà cambiato a quello qui specificato.',
    'actiontrainbayesian'        => 'Addestra algoritmo Bayesiano',
    'desc_actiontrainbayesian'   => 'Assegna una categoria Bayesiana a questo ticket (addestrerà pure il filtro Bayesiano per la futura classificazione automatica).',
    'actiontrash'                => 'Sposta nel cestino',
    'desc_actiontrash'           => 'Sposta il ticket nel cestino.',

    'titleinsertworkflow'        => 'Creata regola del flusso di lavoro (%s)',
    'msginsertworkflow'          => 'La regola del flusso di lavoro (%s) è stata creata con successo.',
    'titleupdateworkflow'        => 'Aggiornata regola del flusso di lavoro (%s)',
    'msgupdateworkflow'          => 'La regola del flusso di lavoro (%s) è stata aggiornata con successo.',
    'titleworkflowenable'        => 'Regole del flusso di lavoro ticket  abilitate (%d)',
    'msgworkflowenable'          => 'Le seguenti regole del flusso di lavoro sono state abilitate:',
    'titleworkflowdisable'       => 'Regole del flusso di lavoro ticket  disabilitate (%d)',
    'msgworkflowdisable'         => 'Le seguenti regole del flusso di lavoro sono state disabilitate:',

    // ?
    'wfaticketstatus'            => 'Modifica lo stato del ticket',
    'wfatickettype'              => 'Cambia il tipo di ticket',
    'wfaaddtags'                 => 'Aggiungi tag',
    'wfaremovetags'              => 'Rimuovi tag',
    'wfadepartment'              => 'Sposta il ticket al dipartimento',
    'wfaflag'                    => 'Segnala ticket',
    'wfaddnotes'                 => 'Aggiuni note',
    'wfapriority'                => 'Modifica la priorità del ticket',
    'wfastaff'                   => 'Cambia il proprietario del ticket',
    'wfaslaplan'                 => 'Cambia il piano di SLA',
    'wfabayesian'                => 'Assegna categoria Bayesiana',
    'wfatrash'                   => 'Sposta nel cestino',

    // Criteria
    'wftickettype'               => 'Tipo di ticket',
    'desc_wftickettype'          => '',
    'wfbayesian'                 => 'Categoria Bayesiana',
    'desc_wfbayesian'            => 'Ticket che sono stati abbinati a una categoria Bayesiana specifica.',
    'wfsubject'                  => 'Oggetto del ticket',
    'desc_wfsubject'             => 'Cerca l\'oggetto di un ticket.',
    'notapplicable'              => '-- Non Disponibile --',
    'wfticketstatus'             => 'Stato del ticket',
    'desc_wfticketstatus'        => '',
    'wfticketpriority'           => 'Priorità del ticket',
    'desc_wfticketpriority'      => '',
    'wfusergroup'                => 'Gruppo di utenti',
    'desc_wfusergroup'           => '',
    'wfdepartment'               => 'Dipartimento del ticket',
    'desc_wfdepartment'          => 'Ticket che appartengono ad un dipartimento.',
    'wfowner'                    => 'Proprietario del ticket',
    'desc_wfowner'               => 'Ticket assegnati ad un determinato utente del personale.',
    'wfunassigned'               => '-- Non assegnato/i --',
    'wfactivestaff'              => '-- Personale Attivo --',
    'wfemailqueue'               => 'Coda email',
    'desc_wfemailqueue'          => 'Ticket che sono stati creati o risposti via email da una coda email specifica.',
    'wfflagtype'                 => 'Contrassegno ticket',
    'desc_wfflagtype'            => '',
    'wfcreator'                  => 'Ticket creato da',
    'desc_wfcreator'             => '',
    'creatorstaff'               => 'Personale',
    'creatorclient'              => 'Cliente',
    'wffullname'                 => 'Nome del destinatario',
    'desc_wffullname'            => 'Cerca i nomi dei destinatari che partecipano a un ticket.',
    'wfemail'                    => 'Indirizzo email del destinatario',
    'desc_wfemail'               => 'Cerca i ticket dagli indirizzi email del destinatario.',
    'wflastreplier'              => 'Il nome dell\'ultimo che ha risposto',
    'desc_wflastreplier'         => 'Cerca i ticket dal nome dell\'ultimo che ha risposto.',
    'wfcharset'                  => 'Set di caratteri',
    'desc_wfcharset'             => 'Ticket di un determinato set di caratteri.',
    'wfslaplan'                  => 'Piano di SLA',
    'desc_wfslaplan'             => 'Ticket che sono attualmente assegnati ad un determinato piano di SLA.',
    'wfdate'                     => 'Data di creazione del ticket',
    'desc_wfdate'                => 'Ticket che sono stati creati prima o dopo questo orario.',
    'wfdaterange'                => 'Data di creazione del ticket <range>',
    'desc_wfdaterange'           => 'Ticket che sono stati creati entro questo lasso di tempo.',
    'wflastactivity'             => 'Ultimo aggiornamento ticket',
    'desc_wflastactivity'        => 'Ticket che sono stati aggiornati (es. risposti da qualcuno o qualsiasi altro evento) prima o dopo questo orario.',
    'wflastactivityrange'        => 'Ultimo aggiornamento ticket <range>',
    'desc_wflastactivityrange'   => 'Ticket che sono stati aggiornati (es. risposti da qualcuno o qualsiasi altro evento) entro questo lasso di tempo.',
    'wflaststaffreply'           => 'Ultima risposta da parte del personale',
    'desc_wflaststaffreply'      => 'Ticket che hanno ricevuto una risposta da un utente del personale prima o dopo questo orario.',
    'wflaststaffreplyrange'      => 'Ultima risposta da parte del personale <range>',
    'desc_wflaststaffreplyrange' => 'Ticket che hanno ricevuto una risposta da un utente del personale entro questo lasso di tempo.',
    'wflastuserreply'            => 'Ultima risposta dall\'utente',
    'desc_wflastuserreply'       => 'Ticket che hanno ricevuto una risposta da un utente prima o dopo questo orario.',
    'wflastuserreplyrange'       => 'Ultima risposta dall\'utente <range>',
    'desc_wflastuserreplyrange'  => 'Ticket che hanno ricevuto una risposta da un utente entro questo lasso di tempo.',
    'wfdue'                      => 'Scadenza della risposta',
    'desc_wfdue'                 => 'Ticket che hanno una scadenza della risposta prima o dopo questo tempo.',
    'wfduerange'                 => 'Scadenza della risposta <range>',
    'desc_wfduerange'            => 'Ticket che hanno una scadenza della risposta entro questo lasso di tempo.',
    'wfisoverdue'                => 'Scaduto: Risposta in ritardo',
    'desc_wfisoverdue'           => 'Ticket che sono in ritardo (perché non hanno avuto una risposta prima della scadenza della risposta).',
    'wfresolutiondue'            => 'Scadenza risoluzione',
    'desc_wfresolutiondue'       => 'Ticket che hanno una scadenza di risoluzione prima o dopo di questo tempo.',
    'wfresolutionduerange'       => 'Scadenza risoluzione <range>',
    'desc_wfresolutionduerange'  => 'Ticket che hanno una scadenza di risoluzione entro questo lasso di tempo.',
    'wfisresolutionoverdue'      => 'Scaduto: Risoluzione in ritardo',
    'desc_wfisresolutionoverdue' => 'Ticket che sono in ritardo (perché non sono stati risolti prima della scadenza di risoluzione).',
    'wftemplategroup'            => 'Gruppo di modelli',
    'desc_wftemplategroup'       => 'Ticket che appartengono ad un particolare gruppo di modelli.',
    'wftimeworked'               => 'Tempo lavorato (minuti)',
    'desc_wftimeworked'          => 'Ticket che corrispondono al valore <strong>tempo lavorato</strong> (come parte delle voci di fatturazione ticket e rilevamento del tempo).',
    'wftotalreplies'             => 'Totale risposte',
    'desc_wftotalreplies'        => 'Ticket che hanno questo numero di risposte.',
    'wfpendingfollowups'         => 'Follow-up in sospeso',
    'desc_wfpendingfollowups'    => 'Ticket che hanno follow-up pianificati.',
    'wfipaddress'                => 'Indirizzo IP',
    'desc_wfipaddress'           => 'Se la risposta ad un ticket è stata fatta dal <strong>centro di supporto</strong>, l\'helpdesk potrebbe aver registrato l\'indirizzo IP dell\'utente.',
    'wfisemailed'                => 'Creato da email',
    'desc_wfisemailed'           => 'Ticket che sono stati creati via email.',
    'wfisedited'                 => 'È stato modificato',
    'desc_wfisedited'            => 'Ticket che sono stati modificati.',
    'wfhasnotes'                 => 'Ha note',
    'desc_wfhasnotes'            => 'Ticket che hanno note ticket.',
    'wfhasattachments'           => 'Contiene allegati',
    'desc_wfhasattachments'      => 'Ticket che hanno file allegati.',
    'wfisescalated'              => 'È andato in escalation',
    'desc_wfisescalated'         => 'Ticket che sono stati riassegnati da una o più regole di escalation.',
    'wfhasdraft'                 => 'Ha una bozza',
    'desc_wfhasdraft'            => 'Ticket nel quale è salvata una risposta in bozza.',
    'wfhasbilling'               => 'Ha voci di fatturazione e rilevamento del tempo',
    'desc_wfhasbilling'          => 'Ticket che hanno voci di fatturazione e rilevamento del tempo.',
    'wfisphonecall'              => 'È un ticket telefonico',
    'desc_wfisphonecall'         => 'Ticket che sono stati contrassegnati come ticket di tipo <em>telefonata</em>.',

    // Potentialy unused phrases in admin_workflows.php
    'triggerevent'               => 'Trigger Event',
    'desc_triggerevent'          => 'Select the event that triggers this Workflow rule',
    'smatchtype'                 => 'Match Type',
    'matchtype'                  => 'Criteria Options',
    'desc_matchtype'             => 'Select the grouping method for the criteria fields.',
    'smatchall'                  => 'Match All (AND)',
    'smatchany'                  => 'Match Any (OR)',
    'desc_editworkflow'          => '',
    'invalidworkflowrule'        => 'Invalid Workflow Rule. Please make sure the record exists in the database.',
    'desc_manageworkflows'       => '',
    'desc_tickettype'            => '',
    'desc_trainbayesian'         => '',
    'tecron'                     => 'Scheduled Task (Recurring automatic execution)',
    'desc_tecron'                => 'This event is triggered whenever a scheduled task is executed. Usually every 3-5 minutes depending upon pseudo/manual cron execution.',
    'teticketcreation'           => 'Ticket Creation',
    'desc_teticketcreation'      => 'This event is triggered whenever a new ticket is created',
    'teslaplan'                  => 'SLA Plan Change',
    'desc_teslaplan'             => 'This event is triggered whenever a SLA Plan is changed for a ticket',
    'teflag'                     => 'Flag Change',
    'desc_teflag'                => 'This event is triggered on change of the flag type on a ticket',
    'temarkdue'                  => 'Ticket Marked Due',
    'desc_temarkdue'             => 'This event is triggered whenever a ticket is marked as due',
    'teaddrecipients'            => 'Recipients Addition',
    'desc_teaddrecipients'       => 'This event is triggered whener a recipient is added for a ticket',
    'teupdateproperties'         => 'Update Properties',
    'desc_teupdateproperties'    => 'This event is triggered on update of ticket properties',
    'testaffreply'               => 'Staff Reply',
    'desc_testaffreply'          => 'This event is triggered whenever a staff replies to a ticket',
    'teuserreply'                => 'User Reply',
    'desc_teuserreply'           => 'This event is triggered whenever a user replies to a ticket',
    'teticketnote'               => 'Ticket Note',
    'desc_teticketnote'          => 'This event is triggered whenever a note is added to a ticket',
    'techangepriority'           => 'Priority Change',
    'desc_techangepriority'      => 'This event is triggered whenever a priority is changed for a ticket',
    'techangestatus'             => 'Status Change',
    'desc_techangestatus'        => 'This event is triggered whenever a status is changed for a ticket',
    'teassignticket'             => 'Assign Ticket',
    'desc_teassignticket'        => 'This event is triggered whenever a ticket is assigned to a staff',
    'temoveticket'               => 'Move Ticket',
    'desc_temoveticket'          => 'This event is triggered whenever a ticket is moved to a new department',
    'teforwardticket'            => 'Ticket Forward',
    'desc_teforwardticket'       => 'This event is triggered whenever a ticket is forwarded to a third party',
    'tesavedraft'                => 'Save as Draft',
    'desc_tesavedraft'           => 'This event is triggered whenever a ticket is saved as draft',
    'teescalateticket'           => 'Ticket Escalated',
    'desc_teescalateticket'      => 'This event is triggered whenever a ticket is escalated',
    'tetimeworked'               => 'Time Worked',
    'desc_timeworked'            => 'This event is triggered whenever a time worked entry is added for a ticket',
    'teticketpost'               => 'Ticket Post Updated',
    'desc_teticketpost'          => 'This event is triggered whenever a ticket post is updated by a staff',
    'teaddlabel'                 => 'Label',
    'desc_teaddlabel'            => 'This event is triggered whenever a label is added for a ticket',
    'wfislabeled'                => 'Is Labeled',
    'desc_wfislabeled'           => '',
);

return $__LANG;
