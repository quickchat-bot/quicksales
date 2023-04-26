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

$_SWIFT = SWIFT::GetInstance();

$__LANG = array(
    'charset'                      => 'UTF-8',
    'html_encoding'                => '8-Bit',
    'text_encoding'                => '8-Bit',
    'html_charset'                 => 'UTF-8',
    'text_charset'                 => 'UTF-8',
    'base_charset'                 => '',
    'yes'                          => 'Ja',
    'no'                           => 'Nein',
    'menusupportcenter'            => 'Support-Center',
    'menustaffcp'                  => 'Personal-Systemsteuerung',
    'menuadmincp'                  => 'Admin-Systemsteuerung',
    'app_notreg'                   => 'Die %s Anwendung ist nicht registriert',
    'event_notreg'                 => 'Das Ereignis %s ist nicht registriert',
    'unable_to_execute'            => 'Konnte %s nicht ausführen',
    'action_notreg'                => 'Die Aktion %s ist nicht registriert',
    'username'                     => 'Benutzername',
    'password'                     => 'Passwort',
    'rememberme'                   => 'Merken',
    'defaulttitle'                 => '%s - Powered by QuickSupport',
    'login'                        => 'Anmelden',
    'global'                       => 'Global',
    'first'                        => 'Erstes',
    'last'                         => 'Letztes',
    'pagination'                   => 'Seite %s von %s',
    'submit'                       => 'Senden',
    'reset'                        => 'Zurücksetzen',
    'poweredby'                    => 'Helpdesk-Software Powered by QuickSupport',
    'copyright'                    => 'Copyright &copy; 2001-%s QuickSupport',
    'notifycsrfhash'               => 'QuickSupport hatte ein Problem beim Bestätigen dieser Anfrage',
    'titlecsrfhash'                => 'QuickSupport hatte ein Problem beim Bestätigen dieser Anfrage',
    'msgcsrfhash'                  => 'QuickSupport überprüft Anfragen zum Schutz vor Cross-Site Request Forgeries und konnte diese Anfrage nicht bestätigen. Bitte versuchen Sie es erneut.',
    'invaliduser'                  => 'Benutzername oder Passwort ist falsch',
    'invaliduseripres'             => 'Nicht autorisierte IP-Adresse (Versuch: %d von %d)',
    'invaliduserdisabled'          => 'Dieses Konto ist deaktiviert (Versuch: %d von %d)',
    'invalid_sessionid'            => 'Ihre Sitzung ist abgelaufen, bitte melden Sie sich an',
    'staff_not_admin'              => 'Benutzer verfügt über keine Administratorenrechte',
    'sort_desc'                    => 'Absteigend sortieren',
    'sort_asc'                     => 'Aufsteigend sortieren',
    'options'                      => 'Einstellungen',
    'delete'                       => 'Entfernen',
    'settings'                     => 'Einstellungen',
    'search'                       => 'Suchen',
    'searchbutton'                 => 'Suchen',
    'actionconfirm'                => 'Sind Sie sicher, dass Sie fortfahren möchten?',
    'loggedout'                    => 'Erfolgreich abgemeldet',
    'view'                         => 'Anzeigen',
    'dashboard'                    => 'Startseite',
    'help'                         => 'Hilfe',
    'size'                         => 'Größe',
    'home'                         => 'Startseite',
    'logout'                       => 'Abmelden',
    'staffcp'                      => 'Personal-Systemsteuerung',
    'admincp'                      => 'Admin-Systemsteuerung',
    'winapp'                       => 'QuickSupport Desktop',
    'staffapi'                     => 'Staff API',
    'bytes'                        => 'Bytes',
    'kb'                           => 'KB',
    'mb'                           => 'MB',
    'gb'                           => 'GB',
    'noitemstodisplay'             => 'Keine anzuzeigende Elemente',
    'manage'                       => 'Verwalten',
    'title'                        => 'Titel',
    'disable'                      => 'Deaktivieren',
    'enable'                       => 'Aktivieren',
    'edit'                         => 'Bearbeiten',
    'back'                         => 'Zurück',
    'forward'                      => 'Weiter',
    'insert'                       => 'Einfügen',
    'update'                       => 'Aktualisieren',
    'public'                       => 'Öffentlich',
    'private'                      => 'Privat',
    'requiredfieldempty'           => 'Eines der erforderlichen Felder ist leer',
    'clifatalerror'                => 'SCHWERER FEHLER',
    'clienterchoice'               => 'Bitte geben Sie Ihre Wahl ein: ',
    'clinotvalidchoice'            => '"%s" ist keine gültige Auswahl; Bitte versuchen Sie es erneut.: ',
    'description'                  => 'Beschreibung',
    'success'                      => 'Erfolgreich',
    'failure'                      => 'Fehler',
    'status'                       => 'Status',
    'date'                         => 'Datum',
    'seconds'                      => 'Sekunden',
    'order'                        => 'Reihenfolge',
    'email'                        => 'E-Mail',
    'subject'                      => 'Betreff',
    'contents'                     => 'Inhalte',
    'sunday'                       => 'Sonntag',
    'monday'                       => 'Montag',
    'tuesday'                      => 'Dienstag',
    'wednesday'                    => 'Mittwoch',
    'thursday'                     => 'Donnerstag',
    'friday'                       => 'Freitag',
    'saturday'                     => 'Samstag',
    'am'                           => 'AM',
    'pm'                           => 'PM',
    'pfieldreveal'                 => '[Einblenden]',
    'pfieldhide'                   => '[Ausblenden]',
    'loadingwindow'                => 'Wird geladen...',
    'customfields'                 => 'Benutzerdefinierte Felder',
    'nopermission'                 => 'Leider verfügen Sie nicht über die Berechtigung, diese Anfrage auszuführen.',
    'nopermissiontext'             => 'Leider verfügen Sie nicht über die Berechtigung, diese Anfrage auszuführen.',
    'generationdate'               => 'Generiertes XML: %s',
    'approve'                      => 'Genehmigen',
    'paginall'                     => 'Alle',
    'fullname'                     => 'Vollständiger Name',
    'onlineusers'                  => 'Online Personal',
    'vardate1'                     => '%dd %dh %dmin',
    'vardate2'                     => '%dh %dmin %ds',
    'vardate3'                     => '%dmin %ds',
    'vardate4'                     => '%ds',
    'reports'                      => 'Berichte',
    'demomode'                     => 'Diese Aktion kann im Demo-Modus nicht ausgeführt werden',
    'unmodifiedreport'             => 'Running the report unmodified as user does not have permission to modify report.',
    'titledemomode'                => 'Konnte nicht fortfahren',
    'msgdemomode'                  => 'Diese Aktion kann im Demo-Modus nicht ausgeführt werden',
    'filter'                       => 'Filter',
    'editor'                       => 'Editor',
    'images'                       => 'Bilder',
    'tabedit'                      => 'Bearbeiten',
    'notifyfieldempty'             => 'Eines der erforderlichen Felder ist leer',
    'notifykqlfieldempty'          => 'Die KQL-Abfrage ist leer',
    'titlefieldempty'              => 'Fehlende Felder',
    'msgfieldempty'                => 'Eines der erforderlichen Felder ist leer oder enthält ungültige Daten; Bitte überprüfen Sie Ihre Eingabe.',
    'msgpastdate'                   => 'Datum kann nicht in der Vergangenheit liegen',
    'titlefieldinvalid'            => 'Ungültiger Wert',
    'msgfieldinvalid'              => 'Eines der Felder enthält ungültige Daten; Bitte überprüfen Sie Ihre Eingabe.',
    'titleinvalidemail'            => 'Invalid Email',
    'msginvalidemail'              => 'The email you entered is same as your customer email; please check your input.',
    'msginvalidadditionalemail'    => 'The email address entered is already used in the desk; Please enter the valid email address.',
    'save'                         => 'Speichern',
    'viewall'                      => 'Alle anzeigen',
    'cancel'                       => 'Abbrechen',
    'tabgeneral'                   => 'Allgemeines',
    'language'                     => 'Sprache',
    'loginshare'                   => 'LoginShare',
    'licenselimit_unabletocreate'  => 'Ein neuer Personalbenutzer konnte nicht erstellt werden, weil Ihr Lizenz-Limit erreicht wurde',
    'name'                         => 'Name',
    'value'                        => 'Wert',
    'engagevisitor'                => 'Besucher kontaktieren',
    'inlinechat'                   => 'Eingebunderer Chat',
    'url'                          => 'URL',
    'hexcode'                      => 'Hex-Code',
    'vactionvariables'             => 'Aktion: Variablen',
    'vactionvexp'                  => 'Aktion: Besuchererfahrung',
    'vactionsalerts'               => 'Aktion: Personal-Benachrichtigungen',
    'vactionsetdepartment'         => 'Aktion: Abteilung einstellen',
    'vactionsetskill'              => 'Aktion: Kenntnisse einstellen',
    'vactionsetgroup'              => 'Aktion: Besuchergruppe einstellen',
    'vactionsetcolor'              => 'Aktion: Farbe einstellen',
    'vactionbanvisitor'            => 'Aktion: Besucher sperren',
    'customengagevisitor'          => 'Benutzerdefinierte Kontaktierung eines Besuchers',
    'managerules'                  => 'Regeln Verwalten',
    'open'                         => 'Öffnen',
    'close'                        => 'Schließen',
    'titleupdatedswiftsettings'    => '%s Einstellungen aktualisiert',
    'updatedsettingspartially'     => 'Die folgenden Einstellungen wurden nicht aktualisiert',
    'msgupdatedswiftsettings'      => 'Die %s Einstellungen wurden erfolgreich aktualisiert.',
    'geoipprocessrunning'          => 'Erstellung der GeoIP-Datenbank wird ausgeführt',
    'continueprocessquestion'      => 'Ein Vorgang wird noch ausgeführt. Wenn Sie diese Seite verlassen, wird es abgebrochen. Möchten Sie fortfahren?',
    'titleupdsettings'             => '%s Einstellungen aktualisiert',
    'type'                         => 'Typ',
    'banip'                        => 'IP (255.255.255.255)',
    'banclassc'                    => 'Klasse C (255.255.255.*)',
    'banclassb'                    => 'Klasse B (255.255.*.*)',
    'banclassa'                    => 'Klasse A (255.*.*.*)',
    'if'                           => 'Wenn',
    'loginlogerror'                => 'Anmeldung für %d Minuten gesperrt (Versuch: %d von %d)',
    'loginlogerrorsecs'            => 'Anmeldung für %d Sekunden gesperrt (Versuch: %d von %d)',
    'loginlogwarning'              => 'Ungültiger Benutzername oder Kennwort (Versuchen: %d von %d)',
    'na'                           => '-NA-',
    'redirectloading'              => 'Wird geladen...',
    'noinfoinview'                 => 'Nicht zum hier Anzeigen',
    'nochange'                     => '--Keine Änderung--',
    'activestaff'                  => '--Aktives Personal--',
    'notificationuser'             => 'Benutzer',
    'notificationuserorganization' => 'Benutzerorganisation',
    'notificationstaff'            => 'Personal (Besitzer)',
    'notificationteam'             => 'Personalteams',
    'notificationdepartment'       => 'Abteilung',
    'notificationsubject'          => 'Betreff: ',
    'lastupdate'                   => 'Letzte Aktualisierung',
    'interface_admin'              => 'Admin-Systemsteuerung',
    'interface_staff'              => 'Personal-Systemsteuerung',
    'interface_intranet'           => 'Intranet',
    'interface_api'                => 'API',
    'interface_winapp'             => 'QuickSupport Desktop/Personal API',
    'interface_syncworks'          => 'SyncWorks',
    'interface_instaalert'         => 'InstaAlert',
    'interface_pda'                => 'PDA',
    'interface_rss'                => 'RSS',
    'error_database'               => 'Datenbank',
    'error_php'                    => 'PHP',
    'error_exception'              => 'Ausnahme',
    'error_mail'                   => 'E-Mail',
    'error_general'                => 'Allgemeines',
    'error_loginshare'             => 'LoginShare',
    'loading'                      => 'Wird geladen...',
    'pwtooshort'                   => 'Zu kurz',
    'pwveryweak'                   => 'Sehr schwach',
    'pwweak'                       => 'Schwach',
    'pwmedium'                     => 'Medium',
    'pwstrong'                     => 'Stark',
    'pwverystrong'                 => 'Sehr stark',
    'pwunsafeword'                 => 'Potenziell unsicheres Passwort',
    'staffpasswordpolicy'          => '<strong>Passwortanforderungen:</strong> Mindestlänge: %d Zeichen, mindestens: %d Ziffern, mindestens: %d Sonderzeichen, mindestens: %d Großbuchstaben',
    'userpasswordpolicy'           => '<strong>Passwortanforderungen:</strong> Mindestlänge: %d Zeichen, mindestens: %d Ziffern, mindestens: %d Sonderzeichen, mindestens: %d Großbuchstaben',
    'titlepwpolicy'                => 'Passwort erfüllt nicht die Anforderungen',
    'passwordexpired'              => 'Das Passwort ist abgelaufen',
    'newpassword'                  => 'Neues Passwort',
    'passwordagain'                => 'Passwort (Wiederholen)',
    'passworddontmatch'            => 'Die Passwörter stimmen nicht überein',
    'defaulttimezone'              => '--Standard-Zeitzone--',
    'tagcloud'                     => 'Tag-Cloud',
    'searchmodeactive'             => 'Ergebnisse werden gefiltert - klicken Sie Zurücksetzen',
    'notifysearchfailed'           => '"0" Treffer',
    'titlesearchfailed'            => '"0" Treffer',
    'msgsearchfailed'              => 'QuickSupport konnte keine Daten zu den eingegebenen Kriterien finden.',
    'quickfilter'                  => 'Schnellfilter',
    'fuenterurl'                   => 'URL eingeben:',
    'fuorupload'                   => 'oder hochladen:',
    'errorsmtpconnect'             => 'Verbindung zum SMTP-Server konnte nicht hergestellt werden',
    'starttypingtags'              => 'Tippen Sie zum Einfügen von Tags...',
    'unsupportedtagchars'          => 'One or more unsupported characters were stripped from the tag.',
    'titleinvalidfileext'          => 'Nicht unterstützte Bildart',
    'msginvalidfileext'            => 'Unterstützte Bildarten sind: gif, jpeg, jpg, png.',
    'notset'                       => '--Nicht festgelegt--',
    'ratings'                      => 'Bewertungen',
    'system'                       => 'System',
    'schatid'                      => 'Chat-ID',
    'supportcenterfield'           => 'Support-Center:',
    'smessagesurvey'               => 'Nachrichten/Umfragen',
    'nosubject'                    => '(Kein Betreff)',
    'nolocale'                     => '(nicht lokal)',
    'markdefault'                   => 'Als Standard markieren',
    'policyurlupdatetitle'           => 'Richtlinien-URL wurde aktualisiert',
    'policyurlupdatemessage'       => 'Die Richtlinien-URL wurde erfolgreich aktualisiert.',

    // Easy Dates
    'edoneyear'                    => 'ein Jahr',
    'edxyear'                      => '%d Jahre',
    'edonemonth'                   => 'ein Monat',
    'edxmonth'                     => '%d Monate',
    'edoneday'                     => 'ein Tag',
    'edxday'                       => '%d Tag',
    'edonehour'                    => 'eine Stunde',
    'edxhour'                      => '%d Stunden',
    'edoneminute'                  => 'Eine Minute',
    'edxminute'                    => '%d Minuten',
    'edjustnow'                    => 'Gerade jetzt',
    'edxseconds'                   => '%d Sekunden',
    'ago'                          => 'vor',

    // Operators
    'opcontains'                   => 'Enthält',
    'opnotcontains'                => 'Enthält nicht',
    'opequal'                      => 'Gleich',
    'opnotequal'                   => 'Entspricht nicht',
    'opgreater'                    => 'Größer als',
    'opless'                       => 'Weniger als',
    'opregexp'                     => 'Regulärer Ausdruck',
    'opchanged'                    => 'Geändert',
    'opnotchanged'                 => 'Nicht geändert',
    'opchangedfrom'                => 'Geändert von',
    'opchangedto'                  => 'Geändert auf',
    'opnotchangedfrom'             => 'Nicht geändert von',
    'opnotchangedto'               => 'Nicht geändert auf',
    'matchand'                     => 'UND',
    'matchor'                      => 'ODER',
    'strue'                        => 'Wahr',
    'sfalse'                       => 'Falsch',
    'notifynoperm'                 => 'Leider verfügen Sie nicht über die Berechtigung, diese Anfrage auszuführen.',
    'titlenoperm'                  => 'Ungenügende Berechtigungen',
    'msgnoperm'                    => 'Leider verfügen Sie nicht über die Berechtigung, diese Anfrage auszuführen.',
    'msgnoperm1'                   => 'The ticket has been created but you do not have the permission to carry out other operations.',
    'cyesterday'                   => 'Gestern',
    'ctoday'                       => 'Heute',
    'ccurrentwtd'                  => 'Aktuelle Woche bis heute',
    'ccurrentmtd'                  => 'Aktueller Monat bis heute',
    'ccurrentytd'                  => 'Laufendes Jahr bis dato',
    'cl7days'                      => 'Letzte 7 Tage',
    'cl30days'                     => 'Letzte 30 Tage',
    'cl90days'                     => 'Letzte 90 Tage',
    'cl180days'                    => 'Letzte 180 Tage',
    'cl365days'                    => 'Letzte 365 Tage',
    'ctomorrow'                    => 'Morgen',
    'cnextwfd'                     => 'Aktuelle Woche ab heute',
    'cnextmfd'                     => 'Aktueller Monat ab heute',
    'cnextyfd'                     => 'Aktuelles Jahr ab heute',
    'cn7days'                      => 'Nächste 7 Tage',
    'cn30days'                     => 'Nächste 30 Tage',
    'cn90days'                     => 'Nächste 90 Tage',
    'cn180days'                    => 'Nächste 180 Tage',
    'cn365days'                    => 'Nächste 365 Tage',
    'new'                          => 'Neu',
    'phoneext'                     => 'Telefon: %s',
    'snewtickets'                  => 'Neue Tickets',
    'sadvancedsearch'              => 'Erweiterte Suche',
    'squicksearch'                 => 'Schnellsuche:',
    'sticketidlookup'              => 'Ticket-ID Suche:',
    'screatorreplier'              => 'Ersteller/Beantworter:',
    'smanage'                      => 'Verwalten',
    'clear'                        => 'Löschen',
    'never'                        => 'Nie',
    'seuser'                       => 'Benutzer',
    'seuserorg'                    => 'Benutzer-Organisationen',
    'import'                       => 'Importieren',
    'export'                       => 'Exportieren',
    'comments'                     => 'Kommentare',
    'commentdata'                  => 'Kommentare:',
    'postnewcomment'               => 'Neues Kommentar posten',
    'replytocomment'               => 'Auf Kommentar antworten',
    'buttonsubmit'                 => 'Senden',
    'reply'                        => 'Antworten',

    // Flags
    'purpleflag'                   => 'Lila Markierung',
    'redflag'                      => 'Rote Markierung',
    'orangeflag'                   => 'Orange Markierung',
    'yellowflag'                   => 'Gelbe Markierung',
    'blueflag'                     => 'Blaue Markierung',
    'greenflag'                    => 'Grüne Markierung',

    'calendar'                     => 'Kalender',
    'cal_january'                  => 'Januar',
    'cal_february'                 => 'Februar',
    'cal_march'                    => 'März',
    'cal_april'                    => 'April',
    'cal_may'                      => 'Mai',
    'cal_june'                     => 'Juni',
    'cal_july'                     => 'Juli',
    'cal_august'                   => 'August',
    'cal_september'                => 'September',
    'cal_october'                  => 'Oktober',
    'cal_november'                 => 'November',
    'cal_december'                 => 'Dezember',

    /**
     * ###############################################
     * APP LIST
     * ###############################################
     */
    'app_base'                     => 'Basis',
    'app_tickets'                  => 'Tickets',
    'app_knowledgebase'            => 'Wissensdatenbank',
    'app_parser'                   => 'E-Mail-Parser',
    'app_livechat'                 => 'Live-Support',
    'app_troubleshooter'           => 'Fehlersuche',
    'app_news'                     => 'News',
    'app_core'                     => 'Kern',
    'app_backend'                  => 'Backend',
    'app_reports'                  => 'Berichte',

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
    'wrong_profile_image'          => 'Das Profilbild wurde nicht aktualisiert. Falsches Format.',
    'wrong_image_size'             => 'Die Bildgröße ist größer als die zulässige Uploadgröße.',
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

        14 => array('Einstellungen', 'bar_settings.gif', APP_CORE, '/Base/Settings/Index'),
        26 => array('REST-API', 'bar_restapi.gif', APP_BASE),
        27 => array('Tag-Generator', 'bar_tag.gif', APP_LIVECHAT, '/Base/TagGenerator/Index'),
        0  => array('Vorlagen', 'bar_templates.gif', APP_BASE),
        1  => array('Sprachen', 'bar_languages.gif', APP_CORE),
        2  => array('Benutzerdefinierte Felder', 'bar_customfields.gif', APP_CORE),
        25 => array('GeoIP', 'bar_geoip.gif', APP_CORE, '/Base/GeoIP/Manage'),
        13 => array('Live-Support', 'bar_livesupport.gif', APP_LIVECHAT),
        4  => array('E-Mail-Parser', 'bar_mailparser.gif', APP_PARSER),
        5  => array('Tickets', 'bar_tickets.gif', APP_TICKETS),
        35 => array ('Benutzerzustimmung', 'bar_maintenance.gif', APP_BASE, '/Base/Consent/Index'),
        29 => array('Arbeitsablauf', 'bar_workflow.gif', APP_TICKETS, '/Tickets/Workflow/Manage'),
        30 => array('Bewertungen', 'bar_ratings.gif', APP_TICKETS, '/Base/Rating/Manage'),
        6  => array('DLV', 'bar_sla.gif', APP_TICKETS),
        7  => array('Eskalation', 'bar_escalations.gif', APP_TICKETS, '/Tickets/Escalation/Manage'),
        20 => array('Bayesian', 'bar_bayesian.gif', APP_TICKETS),
        21 => array('Wissensdatenbank', 'bar_knowledgebase.gif', APP_KNOWLEDGEBASE),
        23 => array('News', 'bar_news.gif', APP_NEWS),
        24 => array('Fehlersuche', 'bar_troubleshooter.gif', APP_TROUBLESHOOTER),
        31 => array('Widgets', 'bar_widgets.gif', APP_BASE, '/Base/Widget/Manage'),
        32 => array('Apps', 'bar_apps.gif', APP_BASE, '/Base/App/Manage'),
        9  => array('Protokoll', 'bar_logs.gif', APP_BASE),
        10 => array('Geplante Tasks', 'bar_cron.gif', APP_BASE),
        11 => array('Datenbank', 'bar_database.gif', APP_BASE),
        33 => array('Importieren', 'bar_import.gif', APP_BASE),
        12 => array('Diagnostik', 'bar_diagnostics.gif', APP_BASE),
        34 => array('Wartung', 'bar_maintenance.gif', APP_BASE),
    );

    SWIFT::Set('adminbar', $_adminBarContainer);

    $_adminBarItemContainer = array(
        0  => array(
            0 => array('Gruppen', '/Base/TemplateGroup/Manage'),
            1 => array('Templates', '/Base/Template/Manage'),
            2 => array('Suchen', '/Base/TemplateSearch/Index'),
            3 => array('Importieren/Exportieren', '/Base/TemplateManager/ImpEx'),
            4 => array('Wiederherstellen', '/Base/TemplateRestore/Index'),
            5 => array('Diagnostik', '/Base/TemplateDiagnostics/Index'),
            6 => array('Header-Logos', '/Base/TemplateManager/Personalize'),
        ),

        1  => array(
            0 => array('Languages', '/Base/Language/Manage'),
            1 => array('Ausdrücke', '/Base/LanguagePhrase/Manage'),
            2 => array('Search', '/Base/LanguagePhrase/Search'),
            3 => array('Importieren/Exportieren', '/Base/LanguageManager/ImpEx'),
            4 => array('Wiederherstellen', '/Base/LanguageManager/Restore'),
            5 => array('Diagnostik', '/Base/LanguageManager/Diagnostics'),
        ),

        2  => array(
            0 => array('Gruppen', '/Base/CustomFieldGroup/Manage'),
            1 => array('Felder', '/Base/CustomField/Manage'),
        ),

        4  => array(
            0 => array('Einstellungen', '/Parser/SettingsManager/Index'),
            1 => array('E-Mail-Warteschlangen', '/Parser/EmailQueue/Manage'),
            2 => array('Regeln', '/Parser/Rule/Manage'),
            3 => array('Breaklines', '/Parser/Breakline/Manage'),
            4 => array('Bans', '/Parser/Ban/Manage'),
            5 => array('Catch-All-Regeln', '/Parser/CatchAll/Manage'),
            6 => array('Schleifenblockierungen', '/Parser/LoopBlock/Manage'),
            7 => array('Regeln zur Schleifenblockierung', '/Parser/LoopRule/Manage'),
            9 => array('Parser-Protokoll', '/Parser/ParserLog/Manage'),
        ),

        5  => array(
            0 => array('Einstellungen', '/Tickets/SettingsManager/Index'),
            1 => array('Typen', '/Tickets/Type/Manage'),
            2 => array('Status', '/Tickets/Status/Manage'),
            3 => array('Prioritäten', '/Tickets/Priority/Manage'),
            4 => array('Dateitypen', '/Tickets/FileType/Manage'),
            5 => array('Links', '/Tickets/Link/Manage'),
            8 => array('Automatische Schließung', '/Tickets/AutoClose/Manage'),
            7 => array('Wartung', '/Tickets/Maintenance/Index'),
        ),

        6  => array(
            0 => array('Einstellungen', '/Tickets/SettingsManager/SLA'),
            1 => array('Pläne', '/Tickets/SLA/Manage'),
            2 => array('Zeitpläne', '/Tickets/Schedule/Manage'),
            3 => array('Feiertage', '/Tickets/Holiday/Manage'),
            4 => array('Importieren/Exportieren', '/Tickets/HolidayManager/Index'),
        ),

        20 => array(
            0 => array('Einstellungen', '/Tickets/SettingsManager/Bayesian'),
            1 => array('Kategorien', '/Tickets/BayesianCategory/Manage'),
            2 => array('Diagnostik', '/Tickets/BayesianDiagnostics/Index'),
        ),

        9  => array(
            0 => array('Fehlerprotokoll', '/Base/ErrorLog/Manage'),
            1 => array('Aufgabenprotokoll', '/Base/ScheduledTasks/TaskLog'),
            3 => array('Aktivitätsprotokoll', '/Base/ActivityLog/Manage'),
            4 => array('Anmeldeprotokoll', '/Base/LoginLog/Manage'),
        ),

        10 => array(
            0 => array('Verwalten', '/Base/ScheduledTasks/Manage'),
            1 => array('Aufgabenprotokoll', '/Base/ScheduledTasks/TaskLog'),
        ),

        11 => array(
            0 => array('Tabelleninformation', '/Base/Database/TableInfo'),
        ),

        12 => array(
            0 => array('Aktive Sitzung', '/Base/Diagnostics/ActiveSessions'),
            1 => array('Cache-Infos', '/Base/Diagnostics/CacheInformation'),
            2 => array('Cache neuerstellen', '/Base/Diagnostics/RebuildCache'),
            3 => array('PHP-Info', '/Base/Diagnostics/PHPInfo'),
            4 => array('Fehler melden', '/Base/Diagnostics/ReportBug'),
            5 => array('Lizenzinfo', '/Base/Diagnostics/LicenseInformation'),
        ),

        13 => array(
            0 => array('Einstellungen', '/LiveChat/SettingsManager/Index'),
            1 => array('Besucherregeln', '/LiveChat/Rule/Manage'),
            2 => array('Besuchergruppen', '/LiveChat/Group/Manage'),
            3 => array('Kenntnisse des Personals', '/LiveChat/Skill/Manage'),
            4 => array('Besuchersperrungen', '/LiveChat/Ban/Manage'),
            5 => array('Nachrichtenverteilung', '/LiveChat/MessageRouting/Index'),
            6 => array('Personalstatus', '/LiveChat/OnlineStatus/Index'),
        ),

        19 => array(
            0 => array('Einstellungen', '/Manuals/SettingsManager/Index'),
        ),

        21 => array(
            0 => array('Einstellungen', '/KnowledgeBase/SettingsManager/Index'),
            1 => array('Wartung', '/KnowledgeBase/Maintenance/Index'),
        ),

        22 => array(
            0 => array('Einstellungen', '/Downloads/SettingsManager/Index'),
        ),

        23 => array(
            0 => array('Einstellungen', '/News/SettingsManager/Index'),
            1 => array('Importieren/Exportieren', '/News/ImpEx/Manage'),
        ),

        24 => array(
            0 => array('Einstellungen', '/Troubleshooter/SettingsManager/Index'),
        ),

        25 => array(
            0 => array('Wartung', '/Base/GeoIP/Manage'),
        ),

        26 => array(
            0 => array('Einstellungen', '/Base/Settings/RESTAPI'),
            1 => array('API-Informationen', '/Base/RESTAPI/Index'),
        ),

        33 => array(
            0 => array('Verwalten', '/Base/Import/Manage'),
            1 => array('Importprotokoll', '/Base/ImportLog/Manage'),
        ),

        34 => array(
            0 => array('Anhänge löschen', '/Base/PurgeAttachments/Index'),
            1 => array('Anhänge verschieben', '/Base/MoveAttachments/Index'),
        ),

    );

    // Log stuff
    if (SWIFT_PRODUCT == 'Fusion' || SWIFT_PRODUCT == 'Resolve' || SWIFT_PRODUCT == 'Case') {
        $_adminBarItemContainer[9][2] = array('Parser-Protokoll', '/Parser/ParserLog/Manage');
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

        1 => array('Startseite', 80, APP_CORE),
        2 => array('Personal', 100, APP_BASE),
        3 => array('Abteilungen', 120, APP_BASE),
        4 => array('Benutzer', 100, APP_BASE),
    );

    SWIFT::Set('adminmenu', $_adminMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_adminLinkContainer = array(

        1 => array(
            0 => array('Startseite', '/Base/Home/Index'),
            1 => array('Einstellungen', '/Base/Settings/Index'),
        ),

        2 => array(
            0 => array('Personal verwalten', '/Base/Staff/Manage'),
            1 => array('Teams verwalten', '/Base/StaffGroup/Manage'),
            2 => array('Personal einfügen', '/Base/Staff/Insert'),
            3 => array('Team einfügen', '/Base/StaffGroup/Insert'),
            4 => array('LoginShare', '/Base/Settings/StaffLoginShare'),
            5 => array('Einstellungen', '/Base/Settings/Staff'),
        ),

        3 => array(
            0 => array('Abteilungen verwalten', '/Base/Department/Manage'),
            1 => array('Abteilung eingeben', '/Base/Department/Insert'),
            2 => array('Zugangsübersicht', '/Base/Department/AccessOverview'),
        ),

        4 => array(
            0 => array('Benutzergruppen verwalten', '/Base/UserGroup/Manage'),
            1 => array('Benutzergruppen einfügen', '/Base/UserGroup/Insert'),
            2 => array('LoginShare', '/Base/Settings/UserLoginShare'),
            3 => array('Einstellungen', '/Base/Settings/User'),
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
        1 => array('Startseite', 80, APP_CORE),
        2 => array('Tickets', 100, APP_TICKETS, 't_entab'),
        3 => array('Live-Support', 120, APP_LIVECHAT, 'ls_entab'),
        4 => array('Wissensdatenbank', 140, APP_KNOWLEDGEBASE, 'kb_entab'),
        6 => array('Fehlersuche', 140, APP_TROUBLESHOOTER, 'tr_entab'),
        7 => array('Nachrichten', 90, APP_NEWS, 'nw_entab'),
        8 => array('Benutzer', 90, APP_CORE, 'cu_entab'),
        9 => array('Berichte', 90, APP_REPORTS, 'rp_entab'),
    );

    SWIFT::Set('staffmenu', $_staffMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_staffLinkContainer = array(

        1 => array(
            0 => array('Startseite', '/Base/Home/Index'),
            1 => array('Meine Einstellungen', '/Base/Preferences/ViewPreferences'),
            2 => array('Benachrichtigungen', '/Base/Notification/Manage', 'staff_canviewnotifications'),
            3 => array('Kommentare', '/Base/Comment/Manage', 'staff_canviewcomments'),
        ),

        2 => array(
            0 => array('Tickets verwalten', '/Tickets/Manage/Index', 'staff_tcanviewtickets'),
            1 => array('Suchen', ':UIDropDown(\'ticketsearchmenu\', event, \'linkmenu2_1\', \'linksdiv\'); LinkTicketSearchForms();'),
            2 => array('Neues Ticket', ':UICreateWindow(\'/Tickets/Ticket/NewTicket/\', \'newticket\', \'New Ticket\', \'Loading..\', 500, 350, true);', 'staff_tcaninsertticket'),
            3 => array('Makros', '/Tickets/MacroCategory/Manage', 'staff_tcanviewmacro'),
            4 => array('Ansichten', '/Tickets/View/Manage', 'staff_tcanview_views'),
            5 => array('Filter', ':UIDropDown(\'ticketfiltermenu\', event, \'linkmenu2_5\', \'linksdiv\');'),
        ),

        3 => array(
            0 => array('Chat-Verlauf', '/LiveChat/ChatHistory/Manage', 'staff_lscanviewchat'),
            1 => array('Nachrichten/Umfragen', '/LiveChat/Message/Manage', 'staff_lscanviewmessages'),
            2 => array('Anrufsprotokolle', '/LiveChat/Call/Manage', 'staff_lscanviewcalls'),
            3 => array('Vorgefertigte Antworten', '/LiveChat/CannedCategory/Manage', 'admin_lscanviewcanned'),
            4 => array('Suchen', ':UIDropDown(\'chatsearchmenu\', event, \'linkmenu3_4\', \'linksdiv\'); LinkChatSearchForms();'),
        ),

        4 => array(
            0 => array('Wissensdatenbank anzeigen', '/Knowledgebase/ViewKnowledgebase/Index'),
            1 => array('Wissensdatenbank verwalten', '/Knowledgebase/Article/Manage'),
            2 => array('Categories', '/Knowledgebase/Category/Manage'),
            3 => array('Neuer Artikel', '/Knowledgebase/Article/Insert'),
        ),

        5 => array(
            0 => array('View Downloads', '/Downloads/Downloads/Manage'),
            1 => array('Manage Downloads', '/Downloads/Downloads/Manage'),
            2 => array('Categories', '/Downloads/Category/Insert'),
            3 => array('New File', '/Downloads/File/Insert'),
        ),

        6 => array(
            0 => array('Fehlersuche anzeigen', '/Troubleshooter/Category/ViewAll'),
            1 => array('Fehlersuche verwalten', '/Troubleshooter/Step/Manage'),
            2 => array('Categories', '/Troubleshooter/Category/Manage'),
            3 => array('Neuer Schritt', ':UICreateWindow(\'/Troubleshooter/Step/InsertDialog/\', \'newstep\', \'Insert Step\', \'Loading..\', 400, 200, true);'),
        ),

        7 => array(
            0 => array('News anzeigen', '/News/NewsItem/ViewAll', 'staff_nwcanviewitems'),
            1 => array('News verwalten', '/News/NewsItem/Manage', 'staff_nwcanmanageitems'),
            2 => array('Categories', '/News/Category/Manage', 'staff_nwcanviewcategories'),
            3 => array('Abonnenten', '/News/Subscriber/Manage', 'staff_nwcanviewsubscribers'),
            4 => array('News einfügen', ':UICreateWindow(\'/News/NewsItem/InsertDialog/\', \'newnews\', \'Insert News\', \'Loading..\', 600, 420, true);'),
        ),

        8 => array(
            0 => array('Benutzer verwalten', '/Base/User/Manage', 'staff_canviewusers'),
            1 => array('Organisationen verwalten', '/Base/UserOrganization/Manage', 'staff_canviewuserorganizations'),
            2 => array('Search', ':UIDropDown(\'usersearchmenu\', event, \'linkmenu8_2\', \'linksdiv\'); LinkUserSearchForms();'),
            3 => array('Benutzer einfügen', '/Base/User/Insert', 'staff_caninsertuser'),
            4 => array('Organisation einfügen', '/Base/UserOrganization/Insert', 'staff_caninsertuserorganization'),
            5 => array ('Import Users', '/Base/User/ImportCSV', 'staff_caninsertuser'),
        ),

        9 => array(
            0 => array('Berichte verwalten', '/Reports/Report/Manage'),
            1 => array('Categories', '/Reports/Category/Manage'),
            2 => array('Neuer Bericht', ':UICreateWindow(\'/Reports/Report/InsertDialog/\', \'newreport\', \'New Report\', \'Loading..\', 400, 280, true);'),
        ),
    );

    $_staffLinkContainer[2][1][15] = true;
    $_staffLinkContainer[2][5][15] = true;
    $_staffLinkContainer[8][2][15] = true;
    $_staffLinkContainer[3][4][15] = true;

    SWIFT::Set('stafflinks', $_staffLinkContainer);
}




return $__LANG;
