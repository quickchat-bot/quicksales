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

// Initial pass done
// Usage script run

$__LANG = array(
    'manage'                          => 'Gestisci',

    'tabgeneral'                      => 'Generale',
    'tabmassaction'                   => 'Azione di Massa',
    'tabreply'                        => 'Rispondi',
    'tabforward'                      => 'Inoltra',

    /**
     * ---------------------------------------------
     * Mass actions
     * ---------------------------------------------
     */
    'manochange'                      => '-- Nessun Cambiamento --',
    'maticketstatus'                  => 'Cambia lo stato',
    'maticketpriority'                => 'Cambia priorità',
    'matickettype'                    => 'Cambia il tipo di ticket',
    'madepartment'                    => 'Sposta al dipartimento',
    'maowner'                         => 'Assegna',
    'maticketlink'                    => 'Collega',
    'maaddtags'                       => 'Aggiungi tag',
    'maremovetags'                    => 'Rimuovi tag',
    'maticketflag'                    => 'Segnala',
    'manoflag'                        => '-- Nessun Contrassegno --',
    'mabayescategory'                 => 'Addestra algoritmo Bayesiano',
    'sactivestaff'                    => '-- Personale Attivo --',
    'tnavnextticket'                  => 'Prossimo Ticket',
    'tnavprevticket'                  => 'Ticket Precedente',
    'tagaddtorecp'                    => 'Aggiungi ai destinatari',
    'massreply'                       => 'Risposta di Massa',

    /**
     * ---------------------------------------------
     * Trash view
     * ---------------------------------------------
     */
    'emptytrash'                      => 'Svuota il Cestino',
    'emptytrashconfirm'               => 'I ticket nel cestino verranno eliminati definitivamente. Desideri continuare?',
    'putback'                         => 'Rimetti',

    /**
     * ---------------------------------------------
     * Viewing a Ticket
     * ---------------------------------------------
     */
    'mergeoptions'                    => 'Unisci Ticket',
    'mergeparentticket'               => 'ID Ticket al quale unire',
    'desc_mergeparentticket'          => 'L\'ID del ticket al quale verrà unito.',
    'titleeditmergefailed'            => 'Non è stato possibile unire i ticket',
    'msgeditmergefailed'              => 'Non è stato possibile unire il ticket perché non si trovava l\'ID del ticket specificato. Controlla l\'ID del ticket e riprova.',
    'titleeditmergesuccess'           => 'Ticket uniti',
    'msgeditmergesuccess'             => 'I ticket sono stati uniti con successo.',
    'tabcalls'                        => 'Chiamate',
    'dispatchasuser'                  => 'Rispondi come utente',
    'viewticket'                      => 'Visualizza Ticket',
    'viewticketext'                   => '[n. %s]: %s',
    'proptitledepartment'             => 'DIPARTIMENTO',
    'proptitleowner'                  => 'PROPRIETARIO',
    'proptitlestatus'                 => 'STATO',
    'proptitlepriority'               => 'PRIORITÀ',
    'proptitletype'                   => 'TIPO',
    'tinforesolutiondue'              => 'Scadenza risoluzione: ',
    'tinfosla'                        => 'Piano di SLA: ',
    'tinfoticketid'                   => 'ID: ',
    'tinfodue'                        => 'Scadenza risposta: ',
    'tinfocreated'                    => 'Data di creazione: ',
    'tinfoupdated'                    => 'Aggiornato: ',
    'ticketlinkinfo'                  => 'Dipartimento: %s, Stato: %s',
    'ticketunlink'                    => '- scollega',
    'tppostedon'                      => 'Pubblicato il: %s',
    'tppostedonsurvey'                => 'Commento del sondaggio inserito il: %s',
    'tpemail'                         => 'Email: %s',
    'tpipaddress'                     => 'Indirizzo IP: %s',
    'participantbox'                  => 'Partecipanti',
    'badgethirdparty'                 => 'Terze Parti',
    'badgeuser'                       => 'Utente',
    'badgestaff'                      => 'Personale',
    'badgecc'                         => 'Destinatario',
    'badgebcc'                        => 'CCN',
    'wineditticketpost'               => 'Modifica Messaggio Ticket',
    'editpost'                        => 'Modifica Messaggio',
    'lastedited'                      => 'Ultima modifica di: %s Il: %s',
    'dialogduetimeline'               => 'Scadenza risposta',
    'dialogresolutionduetimeline'     => 'Scadenza risoluzione',
    'stopwatching'                    => 'Smetti di Osservare',
    'surrender'                       => 'Cedi',
    'print'                           => 'Stampa',
    'print_with_notes'                => 'Stampa + note',
    'take'                            => 'Prendi',
    'tabauditlog'                     => 'Registro di Controllo',
    'aldescription'                   => 'Descrizione',
    'alentrytype'                     => 'Tipo di Voce',
    'alstaff'                         => ' (Personale)',
    'aluser'                          => ' (Utente)',
    'alsystem'                        => ' (Sistema)',
    'alparser'                        => ' (Analizzatore)',
    'editnote'                        => 'Modifica Nota',
    'notetitle'                       => '%s su %s',
    'noteeditedtitle'                 => ' <em>(Modificato da %s il %s)</em>',
    'ticketnotedelconfirm'            => 'Sei sicuro di voler eliminare questa nota del ticket?',
    'tabaddnote'                      => 'Aggiungi Nota',
    'tabeditnote'                     => 'Modifica Nota',
    'addnotes'                        => 'Note',
    'notetype'                        => 'Tipo di nota',
    'addnote'                         => 'Aggiungi Nota',
    'notes_ticket'                    => 'Ticket',
    'notes_user'                      => 'Utente',
    'notes_userorganization'          => 'Organizzazione dell\'Utente',
    'tabrelease'                      => 'Rilascia',
    'tabedit'                         => 'Modifica',
    'tabhistory'                      => 'Cronologia',
    'tabchats'                        => 'Chat',
    'notesvisibleall'                 => '-- Tutti --',
    'notevisibleto'                   => 'Visibilità',
    'desc_notevisibleto'              => 'Limita la visibilità di questa nota.',
    'edit_subject'                    => 'Oggetto del ticket',
    'edit_fullname'                   => 'Nome dell\'autore',
    'edit_email'                      => 'Indirizzo email dell\'autore',
    'edit_overridesla'                => 'Sostituisci SLA',
    'desc_edit_overridesla'           => 'Specifica un piano di SLA da allegare a questo ticket, sostituirà qualsiasi piano di SLA assegnato automaticamente.',
    'editslausedef'                   => '-- Usa SLA Predefinito --',
    'edittproperties'                 => 'Proprietà del Ticket',
    'edittrecipients'                 => 'Destinatari del Ticket',
    'editrecipientsdesc'              => 'Additional recipients can be included in a ticket. Recipients will receive email copies of ticket replies made by staff, but will not have access to view the ticket online.<br /><br />The option to add ticket recipients is available when CCing, BCCing, or forwarding a ticket. There are three types of recipients:<br /><br /><strong>Third party:</strong> Copied (CCd) in on all ticket replies made by staff. They are also able to reply to these by email, and these replies will be added to the ticket.<br /><br /><strong>CC:</strong> Copied (CCd) in on all ticket replies made by staff.  Can contribute to a ticket via email only.<br /><br /><strong>BCC:</strong> Blind copied (BCCd) in on all ticket replies made by staff. Can contribute to a ticket via email only.',
    'editthirdparty'                  => 'Destinatari di terze parti',
    'editcc'                          => 'Destinatari CC',
    'editbcc'                         => 'Destinatari CCN',
    'history_ticketid'                => 'ID Ticket',
    'history_subject'                 => 'Oggetto',
    'history_date'                    => 'Data',
    'history_department'              => 'Dipartimento',
    'history_type'                    => 'Tipo',
    'history_status'                  => 'Stato',
    'history_priority'                => 'Priorità',
    'workflowbox'                     => 'Flusso di lavoro',
    'informationbox'                  => 'Informazioni',
    'tinfobticketid'                  => 'ID TICKET',
    'tinfobuser'                      => 'UTENTE',
    'tinfobuserorganization'          => 'ORGANIZZAZIONE',
    'tinfobusergroup'                 => 'GRUPPO',
    'dispatchfrom'                    => 'Da',
    'dispatchto'                      => 'A',
    'dispatchcc'                      => 'CC',
    'dispatchbcc'                     => 'CCN',
    'dispatchcontents'                => 'Contenuto della Risposta',
    'dispatchaddmacro'                => 'Macro',
    'dispatchaddkb'                   => 'Knowledgebase',
    'dispatchsendmail'                => 'Invia email',
    'dispatchrawhtmlxml'              => 'Invia come HTML / XML raw',
    'dispatchprivate'                 => 'Risposta privata',
    'dispatchwatch'                   => 'Osserva il ticket',
    'dispatchaddfile'                 => 'Aggiungi un Altro File',
    'dispatchsend'                    => 'Invia',
    'dispatchattachfile'              => 'Allega',
    'dispatchsaveasdraft'             => 'Salva come Bozza',
    'dispatchto'                      => 'A',
    'dispatchsubject'                 => 'Oggetto',
    'dispatchsend'                    => 'Invia',
    'dispatchsendar'                  => 'Invia autorisponditore',
    'dispatchuser'                    => 'Utente',
    'dispatchnewuser'                 => 'Crea nuovo account utente',
    'dispatchisphone'                 => 'Ticket telefonico',
    'winuserquickinsert'              => 'Crea un Nuovo Account Utente',
    'dispatch'                        => 'Assegna',
    'tabdispatch'                     => 'Assegna',
    'dispatchticket'                  => 'Assegna Ticket',
    'assign'                          => 'Assegna',
    'newticket'                       => 'Nuovo Ticket',
    'newticket_department'            => 'Crea ticket nel dipartimento',
    'desc_newticket_department'       => '',
    'nt_sendmail'                     => 'Invia un\'email',
    'nt_asuser'                       => 'Come utente',
    'newticket_type'                  => 'Crea tipo di ticket',
    'desc_newticket_type'             => '<strong>Invia un\'email</strong> Un ticket verrà creato dove sei l\'autore - è come inviare una email a qualcuno.<br /><strong>Come utente</strong> Crea un ticket per conto di un utente, sotto il loro nome. Utile per segnare nel registro le telefonate.',
    'nt_next'                         => 'Avanti',
    'dispatchcreate'                  => 'Crea',
    'tabnewticket2'                   => 'Nuovo Ticket: %s',
    'tpemailto'                       => 'Invia email a: %s',
    'tpemailforwardedto'              => 'Inoltrato a: %s',
    'tlockinfo'                       => 'Anche %s sta visualizzando il ticket (ultimo aggiornamento: %s)',
    'tpostlockinfo'                   => '%s sta attualmente rispondendo a questo ticket (ultimo aggiornamento: %s)',
    'tabfollowup'                     => 'Follow-up',
    'notes'                           => 'Note',

    'tabbilling'                      => 'Fatturazione',
    'billworker'                      => 'Operaio',
    'billdate'                        => 'Data di fatturazione',
    'billworkdate'                    => 'Data di lavoro',
    'billtimespent'                   => 'Tempo trascorso (hh: mm)',
    'billworked'                      => 'Tempo lavorato:',
    'billtotalworked'                 => 'Totale tempo lavorato:',
    'billbillable'                    => 'Tempo fatturabile:',
    'billtotalbillable'               => 'Totale tempo fatturabile:',
    'editbilling'                     => 'Modifica Record di Fatturazione',
    'tabeditbilling'                  => 'Modifica Fatturazione',
    'billingtitle'                    => 'Voce di fatturazione per: %s il %s',
    'billingtitlework'                => ' (ha lavorato il: %s)',
    'billingeditedtitle'              => ' <em>(Modificato da %s il %s)</em>',
    'billingeditedtitle2'             => ' <em>(Modificato il %s)</em>',
    'editbilling'                     => 'Modifica Record di Fatturazione',
    'ticketbillingdelconfirm'         => 'Sei sicuro di voler eliminare questo record di fatturazione?',
    'titleinvalidbilldate'            => 'C\'è un problema con il valore del tempo trascorso',
    'msginvalidbilldate'              => 'Si prega di fornire valori validi (ore: minuti) per il tempo lavorato e fatturabile.',

    'fugeneral'                       => 'Generale',
    'fuaddnote'                       => 'Aggiungi Nota',
    'fupostreply'                     => 'Inserisci Risposta',
    'fuforward'                       => 'Inoltra',

    'followupmins'                    => 'In minuti...',
    'followuphours'                   => 'In ore...',
    'followupdays'                    => 'In giorni...',
    'followupweeks'                   => 'In settimane ...',
    'followupmonths'                  => 'In mesi...',
    'followupcustom'                  => 'Personalizza',
    'followup'                        => 'Follow-up',
    'followup_willrunattime'          => 'Eseguirà un follow-up il %s creato da %s (%s)',
    'followup_willchangeownerto'      => 'Assegnerà il ticket a %s',
    'followup_willchangedepartmentto' => 'Sposterà il ticket a %s',
    'followup_willchangestatusto'     => 'Cambierà lo stato a %s',
    'followup_willchangepriorityto'   => 'Cambierà la priorità a %s',
    'followup_willchangetypeto'       => 'Cambierà il tipo di ticket a %s',
    'followup_willaddstaffnotes'      => 'Aggiungerà una nota ticket',
    'followup_willaddusernotes'       => 'Aggiungerà una nota utente',
    'followup_willaddareply'          => 'Aggiungerà una risposta già scritta a questo ticket',
    'followup_willforwardto'          => 'Inoltrerà questo ticket a %s',
    'followup_removeowner'            => 'Azzererà il proprietario di questo ticket',

    'flag'                            => 'Segnala',
    'tescalationhistory'              => 'Visualizza cronologia escalation (%d)',
    'tepdate'                         => 'Data di escalation: ',
    'tepslaplan'                      => 'Piano di SLA: ',
    'tepescalationrule'               => 'Regola di escalation eseguita: ',
    'ntchatid'                        => 'ID Chat: ',
    'ntchatuserfullname'              => 'Utente: ',
    'ntchatuseremail'                 => 'Email: ',
    'ntchatstafffullname'             => 'Personale: ',
    'ntchatdepartment'                => 'Dipartimento: ',
    'titleticketdeleted'              => 'Il ticket non poteva essere caricato',
    'msgticketdeleted'                => 'Questo ticket è stato eliminato e non è stato possibile caricarlo.',

    /**
     * ---------------------------------------------
     * MACRO
     * ---------------------------------------------
     */
    'macro'                           => 'Macro',
    'macros'                          => 'Macro',
    'insertmacro'                     => 'Inserisci Macro',
    'editmacro'                       => 'Modifica Macro',
    'tabcategories'                   => 'Categorie',
    'tabmacros'                       => 'Macro',
    'macrotitle'                      => 'Titolo macro',
    'insertcategory'                  => 'Inserisci Categoria',
    'parentcategoryitem'              => '- Categoria Genitore -',
    'macrocategorytitle'              => 'Nome categoria',
    'desc_macrocategorytitle'         => 'Per esempio, <em>Risposte di fatturazione</em> o <em>Triage di supporto</em>.',
    'parentcategory'                  => 'Categoria genitore',
    'desc_parentcategory'             => 'La categoria dove metterci dentro questa categoria.',
    'categorytype'                    => 'Disponibilità',
    'desc_categorytype'               => '<strong>Pubblico</strong> Le categorie macro sono disponibili a tutto il personale (salvo che siano specificate delle squadre di seguito).<br /><strong>Privato</strong> Le categorie macro sono disponibili solo a te (l\'autore). Perché non le condividi con tutti?',
    'titlemacrocategoryinsert'        => 'Categoria macro (%s) creata',
    'msgmacrocategoryinsert'          => 'La categoria macro (%s) è stata creata con successo.',
    'titlemacrocategoryupdate'        => 'Categoria macro (%s) aggiornata',
    'msgmacrocategoryupdate'          => 'La categoria macro (%s) è stata aggiornata con successo.',
    'titleinvalidparentcat'           => 'Un problema con la categoria genitore',
    'msginvalidparentcat'             => 'La categoria genitore specificata sembra non essere valida. Si prega di rivedere l\'albero di categorie macro per verificare che la categoria genitore sia effettivamente un genitore e non un figlio.',
    'titledelmacrocat'                => 'Categorie macro eliminate (%d)',
    'msgdelmacrocat'                  => 'Sono state eliminate le seguenti categorie macro:',
    'filterreplies'                   => 'Filtra Risposte',
    'rootcategory'                    => 'Categoria principale',
    'macroreplytitle'                 => 'Titolo macro',
    'desc_macroreplytitle'            => 'Ad esempio, <em>Messaggio di primo contatto</em> o <em>Innalza ticket al 2° livello di escalation</em>.',
    'parentcategoryreply'             => 'Categoria',
    'desc_parentcategoryreply'        => 'La categoria dove creare questa macro.',
    'reststaffgroupall'               => '-- Tutte le Squadre del Personale --',
    'restrictstaffgroup'              => 'Limita ad una squadra specifica',
    'desc_restrictstaffgroup'         => 'Rendi disponibile questa categoria alla squadra qui specificata (sopra, la disponibilità della categoria deve essere <em>pubblica</em>).',
    'macroreplycontents'              => 'Contenuto della Risposta',
    'tabproperties'                   => 'Proprietà',
    'ticketfields'                    => 'Proprietà del Ticket',
    'macrodepartment'                 => 'Imposta dipartimento',
    'desc_macrodepartment'            => '',
    'macroticketstatus'               => 'Imposta stato del ticket',
    'desc_macroticketstatus'          => '',
    'macrotickettype'                 => 'Imposta tipo di ticket',
    'desc_macrotickettype'            => '',
    'macroticketpriority'             => 'Imposta priorità ticket',
    'desc_macroticketpriority'        => '',
    'desc_macroaddtags'               => '',
    'macroaddtags'                    => 'Aggiungi tag al ticket',
    'macroownerstaff'                 => 'Imposta proprietario ticket',
    'desc_macroownerstaff'            => '',
    'insertmacro'                     => 'Inserisci Macro',
    'editmacro'                       => 'Modifica Macro',
    'titlemacroreplyinsert'           => 'Creata nuova macro (%s)',
    'msgmacroreplyinsert'             => 'Una nuova macro ticket (%s) è stata creata con successo.',
    'titlemacroreplyupdate'           => 'Aggiornata macro (%s)',
    'msgmacroreplyupdate'             => 'Una nuova macro ticket (%s) è stata aggiornata con successo.',
    'titledelmacroreply'              => 'Macro eliminate (%d)',
    'msgdelmacroreply'                => 'Le seguenti macro sono state eliminate:',
    'quickinsert'                     => 'Inserimento Rapido',
    'qimacro'                         => 'Macro',
    'qiknowledgebase'                 => 'Knowledgebase',
    'replytotalhits'                  => 'Riscontri',
    'replylastused'                   => 'Ultimo utilizzo',
    'invalidattachments'              => 'Purtroppo, non siamo riusciti ad accettare i tuoi allegati (troppo grandi o a causa di restrizioni del tipo di file): %s',


    /**
     * ---------------------------------------------
     * SEARCH
     * ---------------------------------------------
     */
    'search'                          => 'Cerca',
    'tabsearch'                       => 'Ricerca Avanzata',
    'matchtype'                       => 'Tipo di criteri di confronto',
    'desc_matchtype'                  => 'Il modo in cui la ricerca gestirà i seguenti criteri.',
    'smatchall'                       => 'Confronta tutti i criteri (AND)',
    'smatchany'                       => 'Confronta qualsiasi criterio (OR)',
    'insertcriteria'                  => 'Inserisci Criteri',

    /**
     * ---------------------------------------------
     * NEW TICKET
     * ---------------------------------------------
     */
    'tabrecurrence'                   => 'Ricorrenza',
    'recurrence_none'                 => 'Non ripetere',
    'recurrence_daily'                => 'Giornaliera',
    'recurrence_weekly'               => 'Settimanale',
    'recurrence_monthly'              => 'Mensile',
    'recurrence_yearly'               => 'Annuo',
    'rec_every'                       => 'Ogni',
    'rec_days'                        => 'giorni',
    'rec_everyweekday'                => 'Ogni giorno feriale',
    'rec_weeks'                       => 'settimane su',
    'rec_monday'                      => 'Lunedì',
    'rec_tuesday'                     => 'Martedì',
    'rec_wednesday'                   => 'Mercoledì',
    'rec_thursday'                    => 'Giovedì',
    'rec_friday'                      => 'Venerdì',
    'rec_saturday'                    => 'Sabato',
    'rec_sunday'                      => 'Domenica',
    'rec_day'                         => 'Giorno',
    'rec_ofevery'                     => 'di ogni',
    'rec_months'                      => 'mesi',
    'rec_the'                         => 'Il',
    'rec_first'                       => 'Primo',
    'rec_second'                      => 'Secondo',
    'rec_third'                       => 'Terzo',
    'rec_fourth'                      => 'Quarto',
    'rec_fifth'                       => 'Quinto',
    'rec_of'                          => 'di',
    'recurnotactivated'               => 'Questo ticket non si ripeterà.',
    'recurrencerange'                 => 'Si ripete fino a',
    'recur_starts'                    => 'La ricorrenza inizia',
    'recur_utc'                       => '<i>Date is in UTC</i>',
    'recur_ends'                      => 'La ricorrenza finisce',
    'rec_noeenddate'                  => 'Nessuna data finale - continua a ripetere',
    'rec_endafter'                    => 'Finisce dopo:',
    'rec_endby'                       => 'Finisce entro la data:',
    'rec_occurrences'                 => 'occorrenze',
    'pause'                           => 'Pausa',
    'resume'                          => 'Riprendi',
    'stop'                            => 'Interrompi',

    // Potentialy unused phrases in staff_ticketsmanage.php
    'proptitleticketid'               => 'ID TICKET',
    'wineditdue'                      => 'Modifica Scadenza e Termine della Risoluzione nella Sequenza temporale',
    'aldate'                          => 'Data',
    'altimeline'                      => 'Sequenza temporale',
    'tabuser'                         => 'Utente',
    'desc_newticketdepartment'        => 'Seleziona il dipartimento in cui desideri creare il ticket.',
    'tabnewticket'                    => 'Nuovo Ticket',
    'currentfollowups'                => 'Attuali follow-up per questo ticket:',
    'tabreplies'                      => 'Risposte',
    'replytitle'                      => 'Titolo della Risposta',
    'insertreply'                     => 'Inserisci Risposta',
    'wininsertmacrocat'               => 'Inserisci Categoria Macro',
);



return $__LANG;
