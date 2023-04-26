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

// Initial pass done
// Usage script run

$_SWIFT = SWIFT::GetInstance();

$__LANG = array(
    'charset'                      => 'UTF-8',
    'html_encoding'                => '8bit',
    'text_encoding'                => '8bit',
    'html_charset'                 => 'UTF-8',
    'text_charset'                 => 'UTF-8',
    'base_charset'                 => '',
    'yes'                          => 'Sì',
    'no'                           => 'No',
    'menusupportcenter'            => 'Centro di Supporto',
    'menustaffcp'                  => 'CP Personale',
    'menuadmincp'                  => 'CP Admin',
    'app_notreg'                   => 'L\'app %s non è registrata',
    'event_notreg'                 => 'L\'evento %s non è registrato',
    'unable_to_execute'            => 'Impossibile eseguire %s',
    'action_notreg'                => 'L\'azione %s non è registrata',
    'username'                     => 'Nome utente',
    'password'                     => 'Password',
    'rememberme'                   => 'Ricordami',
    'defaulttitle'                 => '%s - Powered by QuickSupport',
    'login'                        => 'Accedi',
    'global'                       => 'Globale',
    'first'                        => 'Primo',
    'last'                         => 'Ultimo',
    'pagination'                   => 'Pagina %s di %s',
    'submit'                       => 'Invia',
    'reset'                        => 'Reimposta',
    'poweredby'                    => 'Software di Helpdesk Powered by QuickSupport',
    'copyright'                    => 'Copyright &copy; 2001-%s QuickSupport',
    'notifycsrfhash'               => 'QuickSupport ha avuto un problema nel convalidare questa richiesta',
    'titlecsrfhash'                => 'QuickSupport ha avuto un problema nel convalidare questa richiesta',
    'msgcsrfhash'                  => 'QuickSupport convalida le richieste per proteggere contro le richieste intersito contraffatte e non è stato possibile convalidare questa richiesta. Riprova per favore.',
    'invaliduser'                  => 'Il nome utente o la password è errata',
    'invaliduseripres'             => 'Indirizzo IP non autorizzato (tentativo: %d di %d)',
    'invaliduserdisabled'          => 'Questo account è disabilitato (tentativo: %d di %d)',
    'invalid_sessionid'            => 'Sessione scaduta, riaccedi',
    'staff_not_admin'              => 'L\'utente non dispone dei privilegi di admin',
    'sort_desc'                    => 'Ordine Decrescente',
    'sort_asc'                     => 'Ordine Crescente',
    'options'                      => 'Opzioni',
    'delete'                       => 'Elimina',
    'settings'                     => 'Impostazioni',
    'search'                       => 'Cerca',
    'searchbutton'                 => 'Cerca',
    'actionconfirm'                => 'Sei sicuro di voler continuare?',
    'loggedout'                    => 'Disconnesso correttamente',
    'view'                         => 'Visualizza',
    'dashboard'                    => 'Pagina iniziale',
    'help'                         => 'Guida',
    'size'                         => 'Dimensione',
    'home'                         => 'Pagina iniziale',
    'logout'                       => 'Disconnetti',
    'staffcp'                      => 'CP Personale',
    'admincp'                      => 'CP Admin',
    'winapp'                       => 'QuickSupport Desktop',
    'staffapi'                     => 'Staff API',
    'bytes'                        => 'Byte',
    'kb'                           => 'KB',
    'mb'                           => 'MB',
    'gb'                           => 'GB',
    'noitemstodisplay'             => 'Nessun elemento da visualizzare',
    'manage'                       => 'Gestisci',
    'title'                        => 'Titolo',
    'disable'                      => 'Disattiva',
    'enable'                       => 'Attiva',
    'edit'                         => 'Modifica',
    'back'                         => 'Indietro',
    'forward'                      => 'Avanti',
    'insert'                       => 'Inserisci',
    'edit'                         => 'Modifica',
    'update'                       => 'Aggiorna',
    'public'                       => 'Pubblico',
    'private'                      => 'Privato',
    'requiredfieldempty'           => 'Uno dei campi obbligatori è vuoto',
    'clifatalerror'                => 'ERRORE FATALE',
    'clienterchoice'               => 'Inserisci la tua scelta: ',
    'clinotvalidchoice'            => '"%s" non è una scelta valida; riprova per favore.: ',
    'description'                  => 'Descrizione',
    'success'                      => 'Successo',
    'failure'                      => 'Fallimento',
    'status'                       => 'Stato',
    'date'                         => 'Data',
    'seconds'                      => 'Secondi',
    'order'                        => 'Ordine',
    'email'                        => 'Email',
    'subject'                      => 'Oggetto',
    'contents'                     => 'Contenuto',
    'sunday'                       => 'Domenica',
    'monday'                       => 'Lunedì',
    'tuesday'                      => 'Martedì',
    'wednesday'                    => 'Mercoledì',
    'thursday'                     => 'Giovedì',
    'friday'                       => 'Venerdì',
    'saturday'                     => 'Sabato',
    'am'                           => 'AM',
    'pm'                           => 'PM',
    'pfieldreveal'                 => '[Rivela]',
    'pfieldhide'                   => '[Nascondi]',
    'loadingwindow'                => 'Caricamento in corso...',
    'customfields'                 => 'Campi Personalizzati',
    'nopermission'                 => 'Purtroppo, non hai il permesso per effettuare questa richiesta.',
    'nopermissiontext'             => 'Purtroppo, non hai il permesso per effettuare questa richiesta.',
    'generationdate'               => 'Generato XML: %s',
    'approve'                      => 'Approva',
    'paginall'                     => 'Tutti',
    'fullname'                     => 'Nome e Cognome',
    'onlineusers'                  => 'Personale Online',
    'vardate1'                     => '%dg %dh %dm',
    'vardate2'                     => '%dh %dm %ds',
    'vardate3'                     => '%dm %ds',
    'vardate4'                     => '%ds',
    'reports'                      => 'Report',
    'demomode'                     => 'Questa azione non può essere eseguita in modalità demo',
    'titledemomode'                => 'Impossibile procedere',
    'unmodifiedreport'             => 'Running the report unmodified as user does not have permission to modify report.',
    'msgdemomode'                  => 'Questa azione non può essere eseguita in modalità demo',
    'filter'                       => 'Filtro',
    'editor'                       => 'Editor',
    'images'                       => 'Immagini',
    'tabedit'                      => 'Modifica',
    'notifyfieldempty'             => 'Uno dei campi obbligatori è vuoto',
    'notifykqlfieldempty'          => 'Query KQL è vuota',
    'titlefieldempty'              => 'Campi mancanti',
    'msgfieldempty'                => 'Uno dei camp obbligatori è vuoto o contiene dati non validi; si prega di controllare ciò che è stato inserito.',
    'msgpastdate'                   => 'La data non può essere nel passato',
    'titlefieldinvalid'            => 'Valore non valido',
    'msgfieldinvalid'              => 'Uno dei campi contiene dati non validi; per favore controlla il tuo input.',
    'titleinvalidemail'            => 'Invalid Email',
    'msginvalidemail'              => 'The email you entered is same as your customer email; please check your input.',
    'msginvalidadditionalemail'    => 'The email address entered is already used in the desk; Please enter the valid email address.',
    'save'                         => 'Salva',
    'viewall'                      => 'Visualizza Tutto',
    'cancel'                       => 'Annulla',
    'tabgeneral'                   => 'Generale',
    'language'                     => 'Lingua',
    'loginshare'                   => 'LoginShare',
    'licenselimit_unabletocreate'  => 'Non è stato possibile creare un nuovo utente del personale perché è stato raggiunto il limite di licenze',
    'help'                         => 'Guida',
    'name'                         => 'Nome',
    'value'                        => 'Valore',
    'engagevisitor'                => 'Contatta il Visitatore',
    'inlinechat'                   => 'Chat in linea',
    'url'                          => 'URL',
    'hexcode'                      => 'Codice Esadecimale',
    'vactionvariables'             => 'Azione: Variabili',
    'vactionvexp'                  => 'Azione: Esperienza Visitatore',
    'vactionsalerts'               => 'Azione: Notifiche al Personale',
    'vactionsetdepartment'         => 'Azione: Imposta Dipartimento',
    'vactionsetskill'              => 'Azione: Imposta competenza',
    'vactionsetgroup'              => 'Azione: Imposta gruppo visitatori',
    'vactionsetcolor'              => 'Azione: Imposta colore',
    'vactionbanvisitor'            => 'Azione: Blocca Visitatore',
    'customengagevisitor'          => 'Contatto Personalizzato Visitatore',
    'managerules'                  => 'Gestisci Regole',
    'open'                         => 'Apri',
    'close'                        => 'Chiudi',
    'titleupdatedswiftsettings'    => 'Impostazioni %s aggiornate',
    'updatedsettingspartially'     => 'Le seguenti impostazioni non sono state aggiornate',
    'msgupdatedswiftsettings'      => 'Le impostazioni di %s sono state aggiornate con successo.',
    'geoipprocessrunning'          => 'La costruzione del database GeoIP è già in corso',
    'continueprocessquestion'      => 'Un\'attività è ancora in esecuzione. Se lasci questa pagina, verrà annullata. Desideri continuare?',
    'titleupdsettings'             => 'Impostazioni %s aggiornate',
    'type'                         => 'Tipo',
    'banip'                        => 'IP (255.255.255.255)',
    'banclassc'                    => 'Classe C (255.255.255.*)',
    'banclassb'                    => 'Classe B (255.255. *. *)',
    'banclassa'                    => 'Classe A (255.*. *. *)',
    'if'                           => 'Se',
    'loginlogerror'                => 'Accesso bloccato per %d minuti (tentativo: %d di %d)',
    'loginlogerrorsecs'            => 'Accesso bloccato per %d secondi (tentativo: %d di %d)',
    'loginlogwarning'              => 'Password o nome utente non valido (tentativo: %d di %d)',
    'na'                           => '- N/D -',
    'redirectloading'              => 'Caricamento in corso...',
    'noinfoinview'                 => 'Non c\'è nulla da visualizzare qui',
    'nochange'                     => '-- Nessun Cambiamento --',
    'activestaff'                  => '-- Personale Attivo --',
    'notificationuser'             => 'Utente',
    'notificationuserorganization' => 'Organizzazione dell\'Utente',
    'notificationstaff'            => 'Personale (Proprietario)',
    'notificationteam'             => 'Squadra del Personale',
    'notificationdepartment'       => 'Dipartimento',
    'notificationsubject'          => 'Oggetto: ',
    'lastupdate'                   => 'Ultimo Aggiornamento',
    'interface_admin'              => 'CP Admin',
    'interface_staff'              => 'CP Personale',
    'interface_intranet'           => 'Intranet',
    'interface_api'                => 'API',
    'interface_winapp'             => 'QuickSupport Desktop/API del Personale',
    'interface_syncworks'          => 'SyncWorks',
    'interface_instaalert'         => 'InstaAlert',
    'interface_pda'                => 'PDA',
    'interface_rss'                => 'RSS',
    'error_database'               => 'Database',
    'error_php'                    => 'PHP',
    'error_exception'              => 'Eccezione',
    'error_mail'                   => 'Email',
    'error_general'                => 'Generale',
    'error_loginshare'             => 'LoginShare',
    'loading'                      => 'Caricamento in corso...',
    'pwtooshort'                   => 'Troppo breve',
    'pwveryweak'                   => 'Molto debole',
    'pwweak'                       => 'Debole',
    'pwmedium'                     => 'Media',
    'pwstrong'                     => 'Sicura',
    'pwverystrong'                 => 'Molto sicura',
    'pwunsafeword'                 => 'Password potenzialmente insicura',
    'staffpasswordpolicy'          => '<strong>Requisiti di password:</strong> Lunghezza minima: %d caratteri, Minimo cifre: %d, Minimo simboli: %d, Minimo lettere maiuscole: %d',
    'userpasswordpolicy'           => '<strong>Requisiti di password:</strong> Lunghezza minima: %d caratteri, Minimo cifre: %d, Minimo simboli: %d, Minimo lettere maiuscole: %d',
    'titlepwpolicy'                => 'La password non soddisfa i requisiti',
    'passwordexpired'              => 'La password è scaduta',
    'newpassword'                  => 'Nuova Password',
    'passwordagain'                => 'Password (ripeti)',
    'passworddontmatch'            => 'Le password non corrispondono',
    'defaulttimezone'              => '-- Fuso Orario Predefinito --',
    'tagcloud'                     => 'Cloud Tag',
    'searchmodeactive'             => 'I risultati sono filtrati - clicca per resettare',
    'notifysearchfailed'           => '"0" Risultati Trovati',
    'titlesearchfailed'            => '"0" Risultati Trovati',
    'msgsearchfailed'              => 'QuickSupport non è stato in grado di individuare record corrispondenti ai criteri specificati.',
    'quickfilter'                  => 'Filtro Rapido',
    'fuenterurl'                   => 'Immetti l\'URL:',
    'fuorupload'                   => 'o Carica:',
    'errorsmtpconnect'             => 'Impossibile connettersi al server SMTP',
    'starttypingtags'              => 'Inizia a digitare per inserire tag...',
    'unsupportedtagchars'          => 'One or more unsupported characters were stripped from the tag.',
    'titleinvalidfileext'          => 'Tipo di immagine non supportato',
    'msginvalidfileext'            => 'Tipi di file immagine supportati sono: gif, jpeg, jpg, png.',
    'notset'                       => '-- Non Impostato --',
    'ratings'                      => 'Valutazioni',
    'system'                       => 'Sistema',
    'schatid'                      => 'ID Chat',
    'supportcenterfield'           => 'Centro di Supporto:',
    'smessagesurvey'               => 'Messaggi/Sondaggi',
    'nosubject'                    => '(Nessun oggetto)',
    'nolocale'                     => '(nessun locale)',
    'markdefault'                   => 'Contrassegna come predefinito',
    'policyurlupdatetitle'           => 'URL della politica aggiornato',
    'policyurlupdatemessage'       => 'L\'URL della politica è stato aggiornato correttamente.',

    // Easy Dates
    'edoneyear'                    => 'un anno',
    'edxyear'                      => '%d anni',
    'edonemonth'                   => 'un mese',
    'edxmonth'                     => '%d mesi',
    'edoneday'                     => 'un giorno',
    'edxday'                       => '%d giorni',
    'edonehour'                    => 'un\'ora',
    'edxhour'                      => '%d ore',
    'edoneminute'                  => 'Un minuto',
    'edxminute'                    => '%d minuti',
    'edjustnow'                    => 'Adesso',
    'edxseconds'                   => '%d secondi',
    'ago'                          => 'fa',

    // Operators
    'opcontains'                   => 'Contiene',
    'opnotcontains'                => 'Non contiene',
    'opequal'                      => 'È uguale a',
    'opnotequal'                   => 'Non è uguale a',
    'opgreater'                    => 'Maggiore di',
    'opless'                       => 'Meno di',
    'opregexp'                     => 'Espressione regolare',
    'opchanged'                    => 'Cambiato',
    'opnotchanged'                 => 'Non è cambiato',
    'opchangedfrom'                => 'Modificato da',
    'opchangedto'                  => 'Modificato in',
    'opnotchangedfrom'             => 'Non è cambiato da',
    'opnotchangedto'               => 'Non è cambiato in',
    'matchand'                     => 'AND',
    'matchor'                      => 'OR',
    'strue'                        => 'Vero',
    'sfalse'                       => 'Falso',
    'notifynoperm'                 => 'Purtroppo, non hai il permesso di effettuare questa richiesta.',
    'titlenoperm'                  => 'Autorizzazioni insufficienti',
    'msgnoperm'                    => 'Purtroppo, non hai il permesso per effettuare questa richiesta.',
    'msgnoperm1'                   => 'The ticket has been created but you do not have the permission to carry out other operations.',
    'cyesterday'                   => 'Ieri',
    'ctoday'                       => 'Oggi',
    'ccurrentwtd'                  => 'Settimana corrente alla data',
    'ccurrentmtd'                  => 'Mese corrente alla data',
    'ccurrentytd'                  => 'Anno corrente alla data',
    'cl7days'                      => 'Ultimi 7 giorni',
    'cl30days'                     => 'Ultimi 30 giorni',
    'cl90days'                     => 'Ultimi 90 giorni',
    'cl180days'                    => 'Ultimi 180 giorni',
    'cl365days'                    => 'Ultimi 365 giorni',
    'ctomorrow'                    => 'Domani',
    'cnextwfd'                     => 'Settimana corrente dalla data',
    'cnextmfd'                     => 'Mese corrente dalla data',
    'cnextyfd'                     => 'Anno corrente dalla data',
    'cn7days'                      => 'Prossimi 7 giorni',
    'cn30days'                     => 'Prossimi 30 giorni',
    'cn90days'                     => 'Prossimi 90 giorni',
    'cn180days'                    => 'Prossimi 180 giorni',
    'cn365days'                    => 'Prossimi 365 giorni',
    'new'                          => 'Nuovo',
    'phoneext'                     => 'Telefono: %s',
    'snewtickets'                  => 'Nuovi ticket',
    'sadvancedsearch'              => 'Ricerca avanzata',
    'squicksearch'                 => 'Ricerca rapida:',
    'sticketidlookup'              => 'Ricerca ID ticket:',
    'screatorreplier'              => 'Autore/Risposto da:',
    'smanage'                      => 'Gestisci',
    'clear'                        => 'Cancella',
    'never'                        => 'Mai',
    'seuser'                       => 'Utenti',
    'seuserorg'                    => 'Organizzazioni Utente',
    'manage'                       => 'Gestisci',
    'import'                       => 'Importa',
    'export'                       => 'Esporta',
    'comments'                     => 'Commenti',
    'commentdata'                  => 'Commenti:',
    'postnewcomment'               => 'Inserisci un nuovo commento',
    'replytocomment'               => 'Rispondi al commento',
    'buttonsubmit'                 => 'Invia',
    'reply'                        => 'Rispondi',

    // Flags
    'purpleflag'                   => 'Bandiera Viola',
    'redflag'                      => 'Bandiera Rossa',
    'orangeflag'                   => 'Bandiera Arancione',
    'yellowflag'                   => 'Bandiera Gialla',
    'blueflag'                     => 'Bandiera Blu',
    'greenflag'                    => 'Bandiera verde',

    'calendar'                     => 'Calendario',
    'cal_january'                  => 'Gennaio',
    'cal_february'                 => 'Febbraio',
    'cal_march'                    => 'Marzo',
    'cal_april'                    => 'Aprile',
    'cal_may'                      => 'Maggio',
    'cal_june'                     => 'Giugno',
    'cal_july'                     => 'Luglio',
    'cal_august'                   => 'Agosto',
    'cal_september'                => 'Settembre',
    'cal_october'                  => 'Ottobre',
    'cal_november'                 => 'Novembre',
    'cal_december'                 => 'Dicembre',

    /**
     * ###############################################
     * APP LIST
     * ###############################################
     */
    'app_base'                     => 'Base',
    'app_tickets'                  => 'Ticket',
    'app_knowledgebase'            => 'Knowledgebase',
    'app_parser'                   => 'Analizzatore Email',
    'app_livechat'                 => 'Supporto Live',
    'app_troubleshooter'           => 'Risolutore problematiche',
    'app_news'                     => 'Notizie',
    'app_core'                     => 'Core',
    'app_backend'                  => 'Backend',
    'app_reports'                  => 'Report',

    // Potentialy unused phrases in en-us.php
    'defaultloginapi'              => 'QuickSupport Login Routine',
    'redirect_login'               => 'Processing Login...',
    'redirect_dashboard'           => 'Redirecting to Home...',
    'no_wait'                      => 'Please click here if your browser does not automatically redirect you',
    'select_un_all'                => 'Select/Unselect All Items',
    'quicksearch'                  => 'Quick Search',
    'mass_action'                  => 'Mass Action',
    'massfieldaction'              => 'Action: ',
    'advanced_search'              => 'Advanced Search',
    'searchfieldquery'             => 'Query: ',
    'searchfieldfield'             => 'Field: ',
    'settingsfieldresultsperpage'  => 'Results Per Page: ',
    'clidefault'                   => '%s v%s\\nCopyright (c) 2001-%s QuickSupport\\n',
    'firstselect'                  => '- Select -',
    'exportasxml'                  => 'XML',
    'exportascsv'                  => 'CSV',
    'exportassql'                  => 'SQL',
    'exportaspdf'                  => 'PDF',
    'clientarea'                   => 'Support Center',
    'pdainterface'                 => 'PDA Interface',
    'kayakomobile'                 => 'QuickSupport Mobile',
    'thousandsseperator'           => ',',
    'clierror'                     => '[ERROR]: ',
    'cliwarning'                   => '[WARNING]: ',
    'cliok'                        => '[OK]: ',
    'cliinfo'                      => '[INFO]: ',
    'sections'                     => 'Sections',
    'twodesc'                      => '%s (%s)',
    'hourrenderus'                 => '%s:%s %s',
    'hourrendereu'                 => '%s:%s',
    'jump'                         => 'Jump',
    'newprvmsgconfirm'             => 'You have a new private message\\nClick OK to open the private message list in a new window.',
    'commentdelconfirm'            => 'Comment deleted successfully',
    'commentstatusconfirm'         => 'Comment status change completed successfully',
    'commentupdconfirm'            => 'Comment by "%s" updated successfully',
    'unapprove'                    => 'Unapprove',
    'approvedcomments'             => 'Approved Comments',
    'unapprovedcomments'           => 'Unapproved Comments',
    'editcomment'                  => 'Edit Comment',
    'quickjump'                    => 'Quick Jump',
    'choiceadd'                    => 'Add >',
    'choicerem'                    => '< Remove',
    'choicemup'                    => 'Move Up',
    'choicemdn'                    => 'Move Down',
    'ticketsubjectformat'          => '[%s#%s]: %s',
    'forwardticketsubjectformat'   => '[%s~%s]: %s',
    'loggedinas'                   => 'Logged In: ',
    'tcustomize'                   => 'Customize...',
    'notifydemomode'               => 'Permission denied. Product is in demo mode.',
    'uploadfile'                   => 'Upload File: ',
    'uploadedimages'               => 'Uploaded Images',
    'tabinsert'                    => 'Insert',
    'allpages'                     => 'All Pages',
    'maddimage'                    => 'Image',
    'maddlinktoimage'              => 'Link to Image',
    'maddthumbnail'                => 'Thumbnail',
    'maddthumbnailwithlink'        => 'Thumbnail with Link',
    'checkuncheckall'              => 'Check/Uncheck All',
    'defaultloginshare'            => 'QuickSupport LoginShare',
    'invalidusernoapiaccess'       => 'Invalid Staff. This staff does not have API access, please configure under Settings > General.',
    'msgupdsettings'               => 'Successfully updated all settings for "%s"',
    'msgpwpolicy'                  => 'The password specified does not match the requirements of the Password Policy.',
    'passwordpolicymismatch'       => 'The password specified does not match the requirements of the Password Policy.',
    'short_all_tickets'            => 'All',
    'iprestrictdenial'             => 'Access Denied (%s): IP not allowed (%s), please add the IP in the allowed list under /config/config.php',
    'cal_clear'                    => 'Clear',
    'cal_close'                    => 'Close',
    'cal_prev'                     => 'Prev',
    'cal_next'                     => 'Next',
    'cal_today'                    => 'Today',
    'cal_sunday'                   => 'Su',
    'cal_monday'                   => 'Mo',
    'cal_tuesday'                  => 'Tu',
    'cal_wednesday'                => 'We',
    'cal_thursday'                 => 'Th',
    'cal_friday'                   => 'Fr',
    'cal_saturday'                 => 'Sa',
    'app_bugs'                     => 'Bugs',
    'wrong_profile_image'          => "L'immagine del profilo non è stata aggiornata. Formato errato.",
    'wrong_image_size'             => 'La dimensione dell\'immagine è maggiore della dimensione di caricamento consentita.',
);


/*
 * ###############################################
 * BEGIN INTERFACE RELATED CODE
 * ###############################################
 */


if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN) {
    /**
     * Admin Area Navigation Bar
     */

    $_adminBarContainer = array(

        14 => array('Impostazioni', 'bar_settings.gif', APP_CORE, '/Base/Settings/Index'),
        26 => array('REST API', 'bar_restapi.gif', APP_BASE),
        27 => array('Generatore di Tag', 'bar_tag.gif', APP_LIVECHAT, '/Base/TagGenerator/Index'),
        0  => array('Modelli', 'bar_templates.gif', APP_BASE),
        1  => array('Lingue', 'bar_languages.gif', APP_CORE),
        2  => array('Campi Personalizzati', 'bar_customfields.gif', APP_CORE),
        25 => array('GeoIP', 'bar_geoip.gif', APP_CORE, '/Base/GeoIP/Manage'),
        13 => array('Supporto Live', 'bar_livesupport.gif', APP_LIVECHAT),
        4  => array('Analizzatore Email', 'bar_mailparser.gif', APP_PARSER),
        5  => array('Ticket', 'bar_tickets.gif', APP_TICKETS),
        35 => array ('Consenso dell\'utente', 'bar_maintenance.gif', APP_BASE, '/Base/Consent/Index'),
        29 => array('Flusso di lavoro', 'bar_workflow.gif', APP_TICKETS, '/Tickets/Workflow/Manage'),
        30 => array('Valutazioni', 'bar_ratings.gif', APP_TICKETS, '/Base/Rating/Manage'),
        6  => array('SLA', 'bar_sla.gif', APP_TICKETS),
        7  => array('Escalation', 'bar_escalations.gif', APP_TICKETS, '/Tickets/Escalation/Manage'),
        20 => array('Bayesiano', 'bar_bayesian.gif', APP_TICKETS),
        21 => array('Knowledgebase', 'bar_knowledgebase.gif', APP_KNOWLEDGEBASE),
        23 => array('Notizie', 'bar_news.gif', APP_NEWS),
        24 => array('Risolutore problematiche', 'bar_troubleshooter.gif', APP_TROUBLESHOOTER),
        31 => array('Widget', 'bar_widgets.gif', APP_BASE, '/Base/Widget/Manage'),
        32 => array('App', 'bar_apps.gif', APP_BASE, '/Base/App/Manage'),
        9  => array('Registri', 'bar_logs.gif', APP_BASE),
        10 => array('Operazioni Pianificate', 'bar_cron.gif', APP_BASE),
        11 => array('Database', 'bar_database.gif', APP_BASE),
        33 => array('Importa', 'bar_import.gif', APP_BASE),
        12 => array('Diagnostica', 'bar_diagnostics.gif', APP_BASE),
        34 => array('Manutenzione', 'bar_maintenance.gif', APP_BASE),
    );

    SWIFT::Set('adminbar', $_adminBarContainer);

    $_adminBarItemContainer = array(
        0  => array(
            0 => array('Gruppi', '/Base/TemplateGroup/Manage'),
            1 => array('Modelli', '/Base/Template/Manage'),
            2 => array('Cerca', '/Base/TemplateSearch/Index'),
            3 => array('Importa/Esporta', '/Base/TemplateManager/ImpEx'),
            4 => array('Ripristina', '/Base/TemplateRestore/Index'),
            5 => array('Diagnostica', '/Base/TemplateDiagnostics/Index'),
            6 => array('Logo d\'Intestazione', '/Base/TemplateManager/Personalize'),
        ),

        1  => array(
            0 => array('Lingue', '/Base/Language/Manage'),
            1 => array('Frasi', '/Base/LanguagePhrase/Manage'),
            2 => array('Cerca', '/Base/LanguagePhrase/Search'),
            3 => array('Importa/Esporta', '/Base/LanguageManager/ImpEx'),
            4 => array('Ripristina', '/Base/LanguageManager/Restore'),
            5 => array('Diagnostica', '/Base/LanguageManager/Diagnostics'),
        ),

        2  => array(
            0 => array('Gruppi', '/Base/CustomFieldGroup/Manage'),
            1 => array('Campi', '/Base/CustomField/Manage'),
        ),

        4  => array(
            0 => array('Impostazioni', '/Parser/SettingsManager/Index'),
            1 => array('Code Email', '/Parser/EmailQueue/Manage'),
            2 => array('Regole', '/Parser/Rule/Manage'),
            3 => array('Breaklines', '/Parser/Breakline/Manage'),
            4 => array('Bans', '/Parser/Ban/Manage'),
            5 => array('Regole di Catch-all', '/Parser/CatchAll/Manage'),
            6 => array('Blocchi Loop', '/Parser/LoopBlock/Manage'),
            7 => array('Regole di Blocco Loop', '/Parser/LoopRule/Manage'),
            9 => array('Registro dell\'Analizzatore', '/Parser/ParserLog/Manage'),
        ),

        5  => array(
            0 => array('Impostazioni', '/Tickets/SettingsManager/Index'),
            1 => array('Tipi', '/Tickets/Type/Manage'),
            2 => array('Stati', '/Tickets/Status/Manage'),
            3 => array('Priorità', '/Tickets/Priority/Manage'),
            4 => array('Tipi di File', '/Tickets/FileType/Manage'),
            5 => array('Link', '/Tickets/Link/Manage'),
            8 => array('Chiusura Automatica', '/Tickets/AutoClose/Manage'),
            7 => array('Manutenzione', '/Tickets/Maintenance/Index'),
        ),

        6  => array(
            0 => array('Impostazioni', '/Tickets/SettingsManager/SLA'),
            1 => array('Pianificazioni', '/Tickets/SLA/Manage'),
            2 => array('Orari', '/Tickets/Schedule/Manage'),
            3 => array('Festività', '/Tickets/Holiday/Manage'),
            4 => array('Importa/Esporta', '/Tickets/HolidayManager/Index'),
        ),

        20 => array(
            0 => array('Impostazioni', '/Tickets/SettingsManager/Bayesian'),
            1 => array('Categorie', '/Tickets/BayesianCategory/Manage'),
            2 => array('Diagnostica', '/Tickets/BayesianDiagnostics/Index'),
        ),

        9  => array(
            0 => array('Registro degli Errori', '/Base/ErrorLog/Manage'),
            1 => array('Registro Attività', '/Base/ScheduledTasks/TaskLog'),
            3 => array('Registro Attività', '/Base/ActivityLog/Manage'),
            4 => array('Registro di Accesso', '/Base/LoginLog/Manage'),
        ),

        10 => array(
            0 => array('Gestisci', '/Base/ScheduledTasks/Manage'),
            1 => array('Registro Attività', '/Base/ScheduledTasks/TaskLog'),
        ),

        11 => array(
            0 => array('Informazioni Tabella', '/Base/Database/TableInfo'),
        ),

        12 => array(
            0 => array('Sessioni Attive', '/Base/Diagnostics/ActiveSessions'),
            1 => array('Informazioni Cache', '/Base/Diagnostics/CacheInformation'),
            2 => array('Ricostruisci la Cache', '/Base/Diagnostics/RebuildCache'),
            3 => array('Info PHP', '/Base/Diagnostics/PHPInfo'),
            4 => array('Segnala Problema', '/Base/Diagnostics/ReportBug'),
            5 => array('Informazioni Licenze', '/Base/Diagnostics/LicenseInformation'),
        ),

        13 => array(
            0 => array('Impostazioni', '/LiveChat/SettingsManager/Index'),
            1 => array('Regole Visitatori', '/LiveChat/Rule/Manage'),
            2 => array('Gruppi di Visitatori', '/LiveChat/Group/Manage'),
            3 => array('Competenze del Personale', '/LiveChat/Skill/Manage'),
            4 => array('Blocchi Visitatori', '/LiveChat/Ban/Manage'),
            5 => array('Smistamento Messaggi', '/LiveChat/MessageRouting/Index'),
            6 => array('Stato del Personale', '/LiveChat/OnlineStatus/Index'),
        ),

        19 => array(
            0 => array('Impostazioni', '/Manuals/SettingsManager/Index'),
        ),

        21 => array(
            0 => array('Impostazioni', '/KnowledgeBase/SettingsManager/Index'),
            1 => array('Manutenzione', '/KnowledgeBase/Maintenance/Index'),
        ),

        22 => array(
            0 => array('Impostazioni', '/Downloads/SettingsManager/Index'),
        ),

        23 => array(
            0 => array('Impostazioni', '/News/SettingsManager/Index'),
            1 => array('Importa/Esporta', '/News/ImpEx/Manage'),
        ),

        24 => array(
            0 => array('Impostazioni', '/Troubleshooter/SettingsManager/Index'),
        ),

        25 => array(
            0 => array('Manutenzione', '/Base/GeoIP/Manage'),
        ),

        26 => array(
            0 => array('Impostazioni', '/Base/Settings/RESTAPI'),
            1 => array('Informazioni API', '/Base/RESTAPI/Index'),
        ),

        33 => array(
            0 => array('Gestisci', '/Base/Import/Manage'),
            1 => array('Importa Registro', '/Base/ImportLog/Manage'),
        ),

        34 => array(
            0 => array('Elimina Allegati', '/Base/PurgeAttachments/Index'),
            1 => array('Sposta gli Allegati', '/Base/MoveAttachments/Index'),
        ),

    );

    // Log stuff
    if (SWIFT_PRODUCT == 'Fusion' || SWIFT_PRODUCT == 'Resolve' || SWIFT_PRODUCT == 'Case') {
        $_adminBarItemContainer[9][2] = array('Registro dell\'Analizzatore', '/Parser/ParserLog/Manage');
    }

    if (SWIFT_PRODUCT == 'Fusion' || SWIFT_PRODUCT == 'Engage') {
        unset($_adminBarContainer[27]);
    }

    SWIFT::Set('adminbaritems', $_adminBarItemContainer);


    /**
     * Admin Area Menu Links
     * Translate the Highlighted Text: 0 => array (>>>'Home'<<<, 100, APP_NAME),
     * ! IMPORTANT ! The following array does NOT have a Zero based index
     */

    $_adminMenuContainer = array(

        1 => array('Pagina iniziale', 80, APP_CORE),
        2 => array('Personale', 100, APP_BASE),
        3 => array('Dipartimenti', 120, APP_BASE),
        4 => array('Utenti', 100, APP_BASE),
    );

    SWIFT::Set('adminmenu', $_adminMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_adminLinkContainer = array(

        1 => array(
            0 => array('Pagina iniziale', '/Base/Home/Index'),
            1 => array('Impostazioni', '/Base/Settings/Index'),
        ),

        2 => array(
            0 => array('Gestisci il Personale', '/Base/Staff/Manage'),
            1 => array('Gestisci Squadre', '/Base/StaffGroup/Manage'),
            2 => array('Inserisci Personale', '/Base/Staff/Insert'),
            3 => array('Inserisci Squadre', '/Base/StaffGroup/Insert'),
            4 => array('LoginShare', '/Base/Settings/StaffLoginShare'),
            5 => array('Impostazioni', '/Base/Settings/Staff'),
        ),

        3 => array(
            0 => array('Gestisci Dipartimenti', '/Base/Department/Manage'),
            1 => array('Inserisci Dipartimento', '/Base/Department/Insert'),
            2 => array('Panoramica di Accesso', '/Base/Department/AccessOverview'),
        ),

        4 => array(
            0 => array('Gestisci Gruppi di Utenti', '/Base/UserGroup/Manage'),
            1 => array('Inserisci Gruppi di Utenti', '/Base/UserGroup/Insert'),
            2 => array('LoginShare', '/Base/Settings/UserLoginShare'),
            3 => array('Impostazioni', '/Base/Settings/User'),
        ),
    );

    SWIFT::Set('adminlinks', $_adminLinkContainer);
} else if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFF) {
    /**
     * Staff Area Menu Links
     * Translate the Highlighted Text: 0 => array (>>>'Home'<<<, 100),
     * ! IMPORTANT ! The following array does NOT have a Zero based index
     */

    $_staffMenuContainer = array(
        1 => array('Pagina iniziale', 80, APP_CORE),
        2 => array('Ticket', 100, APP_TICKETS, 't_entab'),
        3 => array('Supporto Live', 120, APP_LIVECHAT, 'ls_entab'),
        4 => array('Knowledgebase', 140, APP_KNOWLEDGEBASE, 'kb_entab'),
        6 => array('Risolutore problematiche', 140, APP_TROUBLESHOOTER, 'tr_entab'),
        7 => array('Notizie', 90, APP_NEWS, 'nw_entab'),
        8 => array('Utenti', 90, APP_CORE, 'cu_entab'),
        9 => array('Report', 90, APP_REPORTS, 'rp_entab'),
    );

    SWIFT::Set('staffmenu', $_staffMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_staffLinkContainer = array(

        1 => array(
            0 => array('Pagina iniziale', '/Base/Home/Index'),
            1 => array('Le Mie Preferenze', '/Base/Preferences/ViewPreferences'),
            2 => array('Notifiche', '/Base/Notification/Manage', 'staff_canviewnotifications'),
            3 => array('Commenti', '/Base/Comment/Manage', 'staff_canviewcomments'),
        ),

        2 => array(
            0 => array('Gestisci Ticket', '/Tickets/Manage/Index', 'staff_tcanviewtickets'),
            1 => array('Cerca', ':UIDropDown(\'ticketsearchmenu\', event, \'linkmenu2_1\', \'linksdiv\'); LinkTicketSearchForms();'),
            2 => array('Nuovo Ticket', ':UICreateWindow(\'/Tickets/Ticket/NewTicket/\', \'newticket\', \'New Ticket\', \'Loading..\', 500, 350, true);', 'staff_tcaninsertticket'),
            3 => array('Macro', '/Tickets/MacroCategory/Manage', 'staff_tcanviewmacro'),
            4 => array('Visualizzazioni', '/Tickets/View/Manage', 'staff_tcanview_views'),
            5 => array('Filtri', ':UIDropDown(\'ticketfiltermenu\', event, \'linkmenu2_5\', \'linksdiv\');'),
        ),

        3 => array(
            0 => array('Cronologia Chat', '/LiveChat/ChatHistory/Manage', 'staff_lscanviewchat'),
            1 => array('Messaggi/Sondaggi', '/LiveChat/Message/Manage', 'staff_lscanviewmessages'),
            2 => array('Registri delle Chiamate', '/LiveChat/Call/Manage', 'staff_lscanviewcalls'),
            3 => array('Risposte Preconfezionate', '/LiveChat/CannedCategory/Manage', 'admin_lscanviewcanned'),
            4 => array('Cerca', ':UIDropDown(\'chatsearchmenu\', event, \'linkmenu3_4\', \'linksdiv\'); LinkChatSearchForms();'),
        ),

        4 => array(
            0 => array('Visualizza Knowledgebase', '/Knowledgebase/ViewKnowledgebase/Index'),
            1 => array('Gestisci Knowledgebase', '/Knowledgebase/Article/Manage'),
            2 => array('Categorie', '/Knowledgebase/Category/Manage'),
            3 => array('Nuovo Articolo', '/Knowledgebase/Article/Insert'),
        ),

        5 => array(
            0 => array('View Downloads', '/Downloads/Downloads/Manage'),
            1 => array('Manage Downloads', '/Downloads/Downloads/Manage'),
            2 => array('Categorie', '/Downloads/Category/Insert'),
            3 => array('New File', '/Downloads/File/Insert'),
        ),

        6 => array(
            0 => array('Visualizza Risolutore Problematiche', '/Troubleshooter/Category/ViewAll'),
            1 => array('Gestisci Risolutore Problematiche', '/Troubleshooter/Step/Manage'),
            2 => array('Categorie', '/Troubleshooter/Category/Manage'),
            3 => array('Nuovo Passaggio', ':UICreateWindow(\'/Troubleshooter/Step/InsertDialog/\', \'newstep\', \'Insert Step\', \'Loading..\', 400, 200, true);'),
        ),

        7 => array(
            0 => array('Visualizza Notizie', '/News/NewsItem/ViewAll', 'staff_nwcanviewitems'),
            1 => array('Gestisci Notizie', '/News/NewsItem/Manage', 'staff_nwcanmanageitems'),
            2 => array('Categorie', '/News/Category/Manage', 'staff_nwcanviewcategories'),
            3 => array('Abbonati', '/News/Subscriber/Manage', 'staff_nwcanviewsubscribers'),
            4 => array('Inserisci Notizie', ':UICreateWindow(\'/News/NewsItem/InsertDialog/\', \'newnews\', \'Insert News\', \'Loading..\', 600, 420, true);'),
        ),

        8 => array(
            0 => array('Gestisci Utenti', '/Base/User/Manage', 'staff_canviewusers'),
            1 => array('Gestisci Organizzazioni', '/Base/UserOrganization/Manage', 'staff_canviewuserorganizations'),
            2 => array('Cerca', ':UIDropDown(\'usersearchmenu\', event, \'linkmenu8_2\', \'linksdiv\'); LinkUserSearchForms();'),
            3 => array('Inserisci Utente', '/Base/User/Insert', 'staff_caninsertuser'),
            4 => array('Inserisci Organizzazioni', '/Base/UserOrganization/Insert', 'staff_caninsertuserorganization'),
            5 => array ('Import Users', '/Base/User/ImportCSV', 'staff_caninsertuser'),
        ),

        9 => array(
            0 => array('Gestisci Report', '/Reports/Report/Manage'),
            1 => array('Categorie', '/Reports/Category/Manage'),
            2 => array('Nuovo Report', ':UICreateWindow(\'/Reports/Report/InsertDialog/\', \'newreport\', \'New Report\', \'Loading..\', 400, 280, true);'),
        ),
    );

    $_staffLinkContainer[2][1][15] = true;
    $_staffLinkContainer[2][5][15] = true;
    $_staffLinkContainer[8][2][15] = true;
    $_staffLinkContainer[3][4][15] = true;

    SWIFT::Set('stafflinks', $_staffLinkContainer);
}




return $__LANG;
