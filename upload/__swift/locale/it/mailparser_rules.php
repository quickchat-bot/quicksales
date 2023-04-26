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
    // ======= BEGIN v4 LOCALES =======
    'isenabled'                 => 'Regola dell\'analizzatore email abilitata',
    'desc_isenabled'            => 'Le regole email verranno ignorate se disabilitato.',
    'insertcriteria'            => 'Inserisci Criteri',
    'tabactions'                => 'Azioni',
    'titlenoaction'             => 'Nessuna azione specificata',
    'msgnoaction'               => 'Deve essere specificata almeno un\'azione che la regola email eseguirà (se corrisponde).',
    'if'                        => 'Se',
    'titleinsertrule'           => 'Creata regola email (%s)',
    'msginsertrule'             => 'Regola email (%s) creata con successo.',
    'titleupdaterule'           => 'Aggiornata regola email (%s)',
    'msgupdaterule'             => 'Regola dell\'analizzatore email "%s" aggiornata con successo.',
    'titledelprules'            => 'Regole email eliminate (%d)',
    'msgdelprules'              => 'Sono state eliminate le seguenti regole email:',
    'titleenableprules'         => 'Regole email abilitate (%d)',
    'msgenableprules'           => 'Sono state abilitate le seguenti regole email:',
    'titledisableprules'        => 'Regole email disabilitate (%d)',
    'msgdisableprules'          => 'Sono state disabilitate le seguenti regole email:',
    // ======= END v4 LOCALES =======

    'mailparser'                => 'Analizzatore Email',

    'desc_parserrules'          => '',

    // Operators
    'opcontains'                => 'Contiene',
    'opnotcontains'             => 'Non contiene',
    'opequal'                   => 'Uguale',
    'opnotequal'                => 'Non uguale',
    'opgreater'                 => 'Maggiore di',
    'opless'                    => 'Minore di',
    'opregexp'                  => 'Espressione regolare',
    'strue'                     => 'Vero',
    'sfalse'                    => 'Falso',

    // Criteria
    'psendername'               => 'Nome mittente',
    'desc_psendername'          => 'Il nome del mittente viene analizzato dall\'intestazione "da:" del messaggio email.',

    'psenderemail'              => 'Indirizzo email del mittente',
    'desc_psenderemail'         => 'L\'indirizzo del mittente viene analizzato dall\'intestazione "da:" del messaggio email. Esempio: <em>Da: <span class="tabletitle">Nome del Mittente</span><emailmittente@domain.com></em>',

    'pdestinationname'          => 'Nome di destinazione (destinatario)',
    'desc_pdestinationname'     => 'Il nome di destinazione viene analizzato dall\'intestazione "A:" dell\'email. Il nome di destinazione è solitamente il nome dell\'utente del personale che ha risposto al ticket (a meno che non sia configurato diversamente).',

    'pdestinationemail'         => 'Indirizzo email di destinazione (destinatario)',
    'desc_pdestinationemail'    => 'L\'indirizzo email di destinazione è analizzato dall\'intestazione "a:" del messaggio email. Solitamente questo è l\'indirizzo appartenente alla coda email al quale viene inviata la risposta.',

    'preplytoname'              => 'Nome mittente',
    'desc_preplytoname'         => 'Il nome del mittente è analizzato dall\'intestazione "rispondi a:" del messaggio email.',

    'preplytoemail'             => 'Indirizzo email mittente',
    'desc_preplytoemail'        => 'L\'indirizzo email del mittente è analizzato dall\'intestazione "rispondi a:" del messaggio email.',

    'psubject'                  => 'Oggetto',
    'desc_psubject'             => 'Corrisponde all\'oggetto della email.',

    'precipients'               => 'Destinatari',
    'desc_precipients'          => 'Confronta con i destinatari dell\'email. Possono esserci più destinatari a cui è stata inviata un\'email in arrivo, tra cui nelle intestazioni "A:" e "CC:" del messaggio email.',

    'pbody'                     => 'Corpo',
    'desc_pbody'                => 'Confronta il resto del contenuto dell\'email dopo che vengono rimosse l\'HTML (tranne che sia configurato diversamente) e le interruzioni di riga.',

    'pbodysize'                 => 'Dimensione corpo',
    'desc_pbodysize'            => 'La dimensione totale del contenuto del corpo dell\'email in byte.',

    'ptextbody'                 => 'Corpo testo normale',
    'desc_ptextbody'            => 'Un\'email può avere due tipi di contenuto; <em>testo normale</em>, <em>HTML</em> o <em>entrambi</em>. Questa regola cerca solamente nella versione <em>testo</em> del corpo. Si noti che non tutte le email vengono inviate in <em>entrambi</em> i formati.',

    'phtmlbody'                 => 'Corpo HTML',
    'desc_phtmlbody'            => 'Un\'email può avere due tipi di contenuto; <em>testo normale</em>, <em>HTML</em> o <em>entrambi</em>. Questa regola cerca solamente nella versione <em>HTML</em> del corpo. Si noti che non tutte le email vengono inviate in <em>entrambi</em> i formati.',

    'ptextbodysize'             => 'Dimensioni corpo testo',
    'desc_ptextbodysize'        => 'Un\'email può avere due tipi di contenuto; <em>testo normale</em>, <em>HTML</em> o <em>entrambi</em>. Questa regola confronta le dimensioni totali del contenuto del corpo <em>testo normale</em> dell\'email in byte.',

    'phtmlbodysize'             => 'Dimensioni corpo HTML',
    'desc_phtmlbodysize'        => 'Un\'email può avere due tipi di contenuto; <em>testo normale</em>, <em>HTML</em> o <em>entrambi</em>. Questa regola confronta le dimensioni totali del contenuto del corpo <em>HTML</em> dell\'email in byte.',

    'pattachmentname'           => 'Nomi degli allegati',
    'desc_pattachmentname'      => 'Confronta i nome degli allegati che sono stati accettati nell\'helpdesk (come definito nel pannello di controllo admin).',

    'pattachmentsize'           => 'Dimensione degli allegati',
    'desc_pattachmentsize'      => 'Confronta le dimensioni degli allegati che sono stati accettati nell\'helpdesk (come definito nel pannello di controllo admin).',

    'ptotalattachmentsize'      => 'Dimensione totale degli allegati',
    'desc_ptotalattachmentsize' => 'Confronta la <em>dimensione totale</em> (in byte) degli allegati che sono stati accettati nell\'helpdesk (come definito nel pannello di controllo admin).',

    'pisreply'                  => 'È una risposta dell\'utente',
    'desc_pisreply'             => 'Se l\'email in arrivo è una risposta ad un ticket esistente da un utente finale questa regola corrisponderà.',

    'pisthirdparty'             => 'È una risposta al destinatario di terza parte',
    'desc_pisthirdparty'        => 'Se l\'email in arrivo è una risposta ad un ticket esistente da un indirizzo che è stato aggiunto al ticket come destinatario di terze parti questa regola corrisponderà.',

    'pfloodprotection'          => 'Attivata protezione da flood',
    'desc_pfloodprotection'     => 'Questa regola corrisponderà se è stata attivata la protezione da flood dell\'helpdesk.',

    'pisstaffreply'             => 'È una risposta del personale',
    'desc_pisstaffreply'        => 'Se l\'email in arrivo è una risposta ad un ticket esistente dall\'indirizzo di un utente del personale questa regola corrisponderà.',

    'pticketstatus'             => 'Stato ticket (post-analisi)',
    'desc_pticketstatus'        => 'Se l\'email in arrivo è una risposta ad un ticket esistente lo stato del ticket verrà confrontato con questa regola.',

    'pticketemailqueue'         => 'Coda email',
    'desc_pticketemailqueue'    => 'Se l\'email in arrivo è prelevata dalla coda specificata questa regola corrisponderà.',

    'ptickettype'               => 'Tipo di ticket (post-analisi)',
    'desc_ptickettype'          => 'Se l\'email in arrivo è una risposta ad un ticket esistente il tipo di ticket verrà confrontato con questa regola.',

    'pticketpriority'           => 'Priorità ticket (post-analisi)',
    'desc_pticketpriority'      => 'Se l\'email in arrivo è una risposta ad un ticket esistente la priorità del ticket verrà confrontata con questa regola.',

    'pticketusergroup'          => 'Gruppo di utenti del ticket (post-analisi)',
    'desc_pticketusergroup'     => 'Se l\'email in arrivo è una risposta ad un ticket esistente il gruppo di utenti del ticket verrà confrontato con questa regola.',

    'pticketdepartment'         => 'Dipartimento del ticket (post-analisi)',
    'desc_pticketdepartment'    => 'Se l\'email in arrivo è una risposta ad un ticket esistente il dipartimento del ticket verrà confrontato con questa regola.',

    'pticketowner'              => 'Proprietario del ticket (post-analisi)',
    'desc_pticketowner'         => 'Se l\'email in arrivo è una risposta ad un ticket esistente il proprietario del ticket verrà confrontato con questa regola.',
    'prunassigned'              => '-- Non assegnato/i --',

    'pticketflagtype'           => 'Contrassegno ticket (post-analisi)',
    'desc_pticketflagtype'      => 'Se l\'email in arrivo è una risposta ad un ticket esistente il contrassegno del ticket verrà confrontato con questa regola.',

    'pbayescategory'            => 'Categoria Bayesiana',
    'desc_pbayescategory'       => 'L\'helpdesk passa tutte le email in arrivo attraverso il filtro Bayesiano e assegna al ticket una categoria Bayesiana con il punteggio più alto. Questo si può utilizzare per filtrare lo spam o per addestrare il sistema ad indirizzare automaticamente i ticket in base al contenuto, con l\'uso di categorie Bayesiane personalizzate.',

    // Insert Rule
    'insertrule'                => 'Inserisci Regola',
    'iruletype'                 => 'Tipo di regola email: ',
    'ipreparse'                 => 'Prima che venga elaborata l\'email (pre-analisi)',
    'ipostparse'                => 'Dopo che viene elaborata l\'email (post-analisi)',

    'paignore'                  => 'Ignora email',
    'desc_paignore'             => 'L\'email verrà ignorata dall\'helpdesk e non verrà elaborata in una risposta o un ticket.',
    'panoautoresp'              => 'Non inviare autorisponditore',
    'desc_panoautoresp'         => 'Un autorisponditore che conferma la ricezione di nuove email (esempio un nuovo ticket o una risposta) non verrà inviato.',
    'panoalerts'                => 'Non elaborare regole di notifica',
    'desc_panoalerts'           => 'Le regole di notifica dell\'helpdesk non verranno elaborate se abilitato.',
    'pnochange'                 => '-- Nessun Cambiamento --',
    'pcstaff'                   => 'Assegna il ticket',
    'desc_pcstaff'              => 'Il ticket (se è uno nuovo o se è una risposta ad uno esistente) verrà assegnato a questo utente del personale.',
    'pcstatus'                  => 'Modifica lo stato del ticket',
    'desc_pcstatus'             => 'Il ticket sarà modificato a questo stato.',
    'pcpriority'                => 'Modifica la priorità del ticket',
    'desc_pcpriority'           => 'Il ticket sarà modificato a questa priorità.',
    'pcdepartment'              => 'Sposta il ticket al dipartimento',
    'desc_pcdepartment'         => 'Il ticket verrà spostato a questo dipartimento.',
    'pcslaplan'                 => 'Cambia il piano di SLA del ticket',
    'desc_pcslaplan'            => 'Questo piano di SLA verrà assegnato al ticket.',
    'pcmovetotrash'             => 'Sposta nel cestino',
    'desc_pcmovetotrash'        => 'Il ticket verrà spostato nel cestino',
    'pcflag'                    => 'Contrassegna ticket',
    'desc_pcflag'               => 'Il ticket verrà contrassegnato con questo colore.',
    'paddnotes'                 => 'Aggiungi una nota al ticket',
    'desc_paddnotes'            => 'Verrà aggiunta una nota al ticket.',
    'pcforward'                 => 'Inoltra l\'email',
    'desc_pcforward'            => 'L\'email verrà inoltrata a questo indirizzo.',
    'preply'                    => 'Rispondi all\'email',
    'desc_preply'               => 'L\'email verrà risposta automaticamente con questo messaggio.',
    'panoticket'                => 'Non elaborare come una risposta di ticket',
    'desc_panoticket'           => 'Anche se l\'email è una risposta ad un ticket esistente, l\'email non verrà aggiunta al ticket esistente e verrà creato un ticket nuovo.',
    'pctickettype'              => 'Cambia il tipo di ticket',
    'desc_pctickettype'         => 'Il tipo di ticket verrà cambiato a quello qui specificato.',
    'pcaddtags'                 => 'Aggiungi tag al ticket',
    'desc_pcaddtags'            => 'Questi tag verranno aggiunti al ticket.',
    'pcremovetags'              => 'Rimuovi tag dal ticket',
    'desc_pcremovetags'         => 'Se il ticket ha qualunque di questi tag verranno rimossi.',
    'pcprivate'                 => 'Rendi la risposta al ticket privato',
    'desc_pcprivate'            => 'Questa risposta al ticket verrà contrassegnata come privato ed è visibile solo agli altri utenti del personale (questo vale per risposte ai ticket solo dal personale).',

    // Edit Rules
    'editrule'                  => 'Modifica regola',
    'ptitle'                    => 'Titolo regola email',
    'desc_ptitle'               => 'Ad esempio, <em>ricevute di PayPal</em>.',
    'pstop'                     => 'Interrompi l\'elaborazione di altre regole',
    'desc_pstop'                => 'Se questa regola corrisponde per prima, le altre regole email non verranno elaborate dopo di questa.',

    // Manage Rules
    'managerules'               => 'Regole',
    'ruletitle'                 => 'Titolo regola',
    'sortorder'                 => 'Ordine di esecuzione',
    'desc_sortorder'            => 'È possibile che più regole email possano corrispondere allo stesso indirizzo email. L\'helpdesk elaborerà le regole secondo il numero di ordine di esecuzione, dal più piccolo al più grande.',
    'ruletype'                  => 'Tipo di regola',
    'creationdate'              => 'Data di creazione',

    // Potentialy unused phrases in mailparser_rules.php
    'smatchtype'                => 'Match Type',
    'matchtype'                 => 'Criteria Options',
    'desc_matchtype'            => 'Select the grouping method for the criteria fields.',
    'smatchall'                 => 'Match <b>All</b> Criteria (AND)',
    'smatchany'                 => 'Match <b>Any</b> Criteria (OR)',
    'criteria'                  => 'Criteria',
    'newcriteria'               => 'New Criteria',
    'ruleinsertconfirm'         => 'Parser rule "%s" inserted successfully',
    'ruleupdateconfirm'         => 'Parser rule "%s" updated successfully',
    'invalidrule'               => 'ERROR: Invalid email parser rule',
    'ruledelconfirm'            => 'Parser rule deleted successfully',
    'rulelist'                  => 'Rule List',
    'notapplicable'             => '-- NA --',

);

return $__LANG;
