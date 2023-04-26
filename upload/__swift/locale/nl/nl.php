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
    'html_encoding'                => '8bit',
    'text_encoding'                => '8bit',
    'html_charset'                 => 'UTF-8',
    'text_charset'                 => 'UTF-8',
    'base_charset'                 => '',
    'yes'                          => 'Ja',
    'no'                           => 'Nee',
    'menusupportcenter'            => 'Support Center',
    'menustaffcp'                  => 'Medewerkerbeheerscherm',
    'menuadmincp'                  => 'Adminbeheerscherm',
    'app_notreg'                   => 'De app %s is niet geregistreerd',
    'event_notreg'                 => 'De gebeurtenis %s is niet geregistreerd',
    'unable_to_execute'            => 'Kan %s niet uitvoeren',
    'action_notreg'                => 'De actie %s is niet geregistreerd',
    'username'                     => 'Gebruikersnaam',
    'password'                     => 'Wachtwoord',
    'rememberme'                   => 'Onthouden',
    'defaulttitle'                 => '%s - Powered by QuickSupport',
    'login'                        => 'Aanmelden',
    'global'                       => 'Algemeen',
    'first'                        => 'Eerste',
    'last'                         => 'Laatste',
    'pagination'                   => 'Pagina %s van %s',
    'submit'                       => 'Verzenden',
    'reset'                        => 'Herstellen',
    'poweredby'                    => 'Helpdesk software powered by QuickSupport',
    'copyright'                    => 'Copyright &copy; 2001-%s QuickSupport',
    'notifycsrfhash'               => 'QuickSupport kon dit verzoek niet valideren',
    'titlecsrfhash'                => 'QuickSupport kon dit verzoek niet valideren',
    'msgcsrfhash'                  => 'QuickSupport valideert verzoeken ter bescherming tegen vervalsingen van cross-site verzoeken en kon dit verzoek niet valideren. Probeer het nogmaals.',
    'invaliduser'                  => 'Gebruikersnaam of wachtwoord is onjuist',
    'invaliduseripres'             => 'Niet-toegestaan IP-adres (poging: %d van %d)',
    'invaliduserdisabled'          => 'Dit account is uitgeschakeld (poging: %d van %d)',
    'invalid_sessionid'            => 'Je versie is verslopen, meld je aan',
    'staff_not_admin'              => 'Gebruiker heeft geen adminrechten',
    'sort_desc'                    => 'Aflopend sorteren',
    'sort_asc'                     => 'Oplopend sorteren',
    'options'                      => 'Opties',
    'delete'                       => 'Verwijderen',
    'settings'                     => 'Instellingen',
    'search'                       => 'Zoeken',
    'searchbutton'                 => 'Zoeken',
    'actionconfirm'                => 'Weet je zeker dat je wilt doorgaan?',
    'loggedout'                    => 'Succesvol afgemeld',
    'view'                         => 'Weergeven',
    'dashboard'                    => 'Start',
    'help'                         => 'Help',
    'size'                         => 'Grootte',
    'home'                         => 'Start',
    'logout'                       => 'Afmelden',
    'staffcp'                      => 'Medewerkerbeheerscherm',
    'admincp'                      => 'Adminbeheerscherm',
    'winapp'                       => 'QuickSupport Desktop',
    'staffapi'                     => 'Staff API',
    'bytes'                        => 'Bytes',
    'kb'                           => 'KB',
    'mb'                           => 'MB',
    'gb'                           => 'GB',
    'noitemstodisplay'             => 'Geen items om weer te geven',
    'manage'                       => 'Beheren',
    'title'                        => 'Titel',
    'disable'                      => 'Uitschakelen',
    'enable'                       => 'Inschakelen',
    'edit'                         => 'Bewerken',
    'back'                         => 'Terug',
    'forward'                      => 'Volgende',
    'insert'                       => 'Toevoegen',
    'edit'                         => 'Bewerken',
    'update'                       => 'Bijwerken',
    'public'                       => 'Publiek',
    'private'                      => 'Privé',
    'requiredfieldempty'           => 'Een van de verplichte velden is leeg',
    'clifatalerror'                => 'FATALE FOUT',
    'clienterchoice'               => 'Voer je keuze in: ',
    'clinotvalidchoice'            => '"%s" is geen geldige keuze; probeer het nogmaals.: ',
    'description'                  => 'Omschrijving',
    'success'                      => 'Succes',
    'failure'                      => 'Mislukt',
    'status'                       => 'Status',
    'date'                         => 'Datum',
    'seconds'                      => 'Seconden',
    'order'                        => 'Volgorde',
    'email'                        => 'Email',
    'subject'                      => 'Onderwerp',
    'contents'                     => 'Inhoud',
    'sunday'                       => 'Zondag',
    'monday'                       => 'Maandag',
    'tuesday'                      => 'Dinsdag',
    'wednesday'                    => 'Woensdag',
    'thursday'                     => 'Donderdag',
    'friday'                       => 'Vrijdag',
    'saturday'                     => 'Zaterdag',
    'am'                           => 'AM',
    'pm'                           => 'PM',
    'pfieldreveal'                 => '[Weergeven]',
    'pfieldhide'                   => '[Verbergen]',
    'loadingwindow'                => 'Laden...',
    'customfields'                 => 'Aangepaste velden',
    'nopermission'                 => 'Helaas heb je niet de rechten voor het uitvoeren van dit verzoek.',
    'nopermissiontext'             => 'Helaas heb je geen toestemming voor het uitvoeren van dit verzoek.',
    'generationdate'               => 'XML gegenereerd: %s',
    'approve'                      => 'Goedkeuren',
    'paginall'                     => 'Alles',
    'fullname'                     => 'Volledige naam',
    'onlineusers'                  => 'Online medewerker',
    'vardate1'                     => '%dd %dh %dm',
    'vardate2'                     => '%dh %dm %ds',
    'vardate3'                     => '%dm %ds',
    'vardate4'                     => '%ds',
    'reports'                      => 'Rapporten',
    'demomode'                     => 'Deze actie kan niet worden uitgevoerd in demomodus',
    'unmodifiedreport'             => 'Running the report unmodified as user does not have permission to modify report.',
    'titledemomode'                => 'Kan niet doorgaan',
    'msgdemomode'                  => 'Deze actie kan niet worden uitgevoerd in demomodus',
    'filter'                       => 'Filter',
    'editor'                       => 'Editor',
    'images'                       => 'Afbeeldingen',
    'tabedit'                      => 'Bewerken',
    'notifyfieldempty'             => 'Een van de verplichte velden is leeg',
    'notifykqlfieldempty'          => 'KQL-query is leeg',
    'titlefieldempty'              => 'Ontbrekende velden',
    'msgfieldempty'                => 'Een van de verplichte velden is leeg of bevat ongeldige gegevens; controleer je invoer.',
    'msgpastdate'                   => 'Datum kan niet in het verleden zijn',
    'titlefieldinvalid'            => 'Ongeldige waarde',
    'msgfieldinvalid'              => 'Een van de velden bevat ongeldige gegevens; controleer uw invoer.',
    'titleinvalidemail'            => 'Invalid Email',
    'msginvalidemail'              => 'The email you entered is same as your customer email; please check your input.',
    'msginvalidadditionalemail'    => 'The email address entered is already used in the desk; Please enter the valid email address.',
    'save'                         => 'Opslaan',
    'viewall'                      => 'Alles weergeven',
    'cancel'                       => 'Annuleren',
    'save'                         => 'Opslaan',
    'tabgeneral'                   => 'Algemeen',
    'language'                     => 'Taal',
    'loginshare'                   => 'LoginShare',
    'licenselimit_unabletocreate'  => 'Er kon geen nieuwe medewerkergebruiker worden gemaakt, omdat je licentielimiet is bereikt',
    'help'                         => 'Help',
    'name'                         => 'Naam',
    'value'                        => 'Waarde',
    'engagevisitor'                => 'Bezoeker contacteren',
    'inlinechat'                   => 'Inline chat',
    'url'                          => 'URL',
    'hexcode'                      => 'Hexcode',
    'vactionvariables'             => 'Actie: Variabelen',
    'vactionvexp'                  => 'Actie: Bezoekerservaring',
    'vactionsalerts'               => 'Actie: Medewerkermeldingen',
    'vactionsetdepartment'         => 'Actie: Afdeling instellen',
    'vactionsetskill'              => 'Actie: Vaardigheid instellen',
    'vactionsetgroup'              => 'Actie: Bezoekersgroep instellen',
    'vactionsetcolor'              => 'Actie: Kleur instellen',
    'vactionbanvisitor'            => 'Actie: Bezoeker bannen',
    'customengagevisitor'          => 'Bezoeker aangepast contacteren',
    'managerules'                  => 'Regels beheren',
    'open'                         => 'Openen',
    'close'                        => 'Sluiten',
    'titleupdatedswiftsettings'    => 'Instellingen voor %s bijgewerkt',
    'updatedsettingspartially'     => 'De volgende instellingen zijn niet bijgewerkt',
    'msgupdatedswiftsettings'      => 'De instellingen voor %s zijn succesvol bijgewerkt.',
    'geoipprocessrunning'          => 'Opbouwen van GeoIP-database wordt reeds uitgevoerd',
    'continueprocessquestion'      => 'Er wordt nog een taak uitgevoerd. Als je deze pagina verlaat, wordt deze gestopt. Wil je doorgaan?',
    'titleupdsettings'             => 'Instellingen voor %s bijgewerkt',
    'type'                         => 'Type',
    'banip'                        => 'IP (255.255.255.255)',
    'banclassc'                    => 'Klasse C (255.255.255.*)',
    'banclassb'                    => 'Klasse B (255.255.*.*)',
    'banclassa'                    => 'Klasse A (255.*.*.*)',
    'if'                           => 'Als',
    'loginlogerror'                => 'Aanmelding geblokkeerd voor %d minuten (poging: %d van %d)',
    'loginlogerrorsecs'            => 'Aanmelding geblokkeerd voor %d seconden (poging: %d van %d)',
    'loginlogwarning'              => 'Gebruikersnaam of wachtwoord ongeldig (poging: %d of %d)',
    'na'                           => '- NVT -',
    'redirectloading'              => 'Laden...',
    'noinfoinview'                 => 'Er is niets om weer te geven',
    'nochange'                     => '-- Geen wijziging --',
    'activestaff'                  => '-- Actieve medewerker --',
    'notificationuser'             => 'Gebruiker',
    'notificationuserorganization' => 'Gebruikersorganisatie',
    'notificationstaff'            => 'Medewerker (Eigenaar)',
    'notificationteam'             => 'Medewerkerteam',
    'notificationdepartment'       => 'Afdeling',
    'notificationsubject'          => 'Onderwerp: ',
    'lastupdate'                   => 'Laatste update',
    'interface_admin'              => 'Adminbeheerscherm',
    'interface_staff'              => 'Medewerkerbeheerscherm',
    'interface_intranet'           => 'Intranet',
    'interface_api'                => 'API',
    'interface_winapp'             => 'QuickSupport Desktop/Medewerker-API',
    'interface_syncworks'          => 'SyncWorks',
    'interface_instaalert'         => 'InstaAlert',
    'interface_pda'                => 'PDA',
    'interface_rss'                => 'RSS',
    'error_database'               => 'Database',
    'error_php'                    => 'PHP',
    'error_exception'              => 'Uitzondering',
    'error_mail'                   => 'Email',
    'error_general'                => 'Algemeen',
    'error_loginshare'             => 'LoginShare',
    'loading'                      => 'Laden...',
    'pwtooshort'                   => 'Te kort',
    'pwveryweak'                   => 'Zeer zwak',
    'pwweak'                       => 'Zwak',
    'pwmedium'                     => 'Gemiddeld',
    'pwstrong'                     => 'Sterk',
    'pwverystrong'                 => 'Zeer sterk',
    'pwunsafeword'                 => 'Mogelijk onveilig wachtwoord',
    'staffpasswordpolicy'          => '<strong>Wachtwoordeisen:</strong> Minimale lengte: %d tekens, minimaal aantal cijfers: %d, minimaal aantal leestekens: %d, Minimaal aantal hoofdletters: %d',
    'userpasswordpolicy'           => '<strong>Wachtwoordeisen:</strong> Minimale lengte: %d tekens, minimaal aantal cijfers: %d, minimaal aantal leestekens: %d, Minimaal aantal hoofdletters: %d',
    'titlepwpolicy'                => 'Wachtwoord voldoet niet aan de eisen',
    'passwordexpired'              => 'Wachtwoord is verlopen',
    'newpassword'                  => 'Nieuw wachtwoord',
    'passwordagain'                => 'Wachtwoord (herhalen)',
    'passworddontmatch'            => 'Wachtwoorden komen niet overeen',
    'defaulttimezone'              => '-- Standaard tijdzone --',
    'tagcloud'                     => 'Tagwolk',
    'searchmodeactive'             => 'Resultaten zijn gefilterd - klik om te herstellen',
    'notifysearchfailed'           => '"0" resultaten gevonden',
    'titlesearchfailed'            => '"0" resultaten gevonden',
    'msgsearchfailed'              => 'QuickSupport kon geen vermeldingen vinden die overeenkomen met de opgegeven criteria.',
    'quickfilter'                  => 'Snelfilter',
    'fuenterurl'                   => 'URL invoeren:',
    'fuorupload'                   => 'of uploaden:',
    'errorsmtpconnect'             => 'Kon geen verbinding maken met de SMTP-server',
    'starttypingtags'              => 'Begin met typen om tags in te voegen...',
    'unsupportedtagchars'          => 'One or more unsupported characters were stripped from the tag.',
    'titleinvalidfileext'          => 'Niet-ondersteund afbeeldingstype',
    'msginvalidfileext'            => 'Ondersteunde afbeeldingstypen zijn: gif, jpeg, jpg, png.',
    'notset'                       => '-- Niet ingesteld --',
    'ratings'                      => 'Beoordelingen',
    'system'                       => 'Systeem',
    'schatid'                      => 'Chat-ID',
    'supportcenterfield'           => 'Support Center:',
    'smessagesurvey'               => 'Berichten/onderzoeken',
    'nosubject'                    => '(geen onderwerp)',
    'nolocale'                     => '(geen locatie)',
    'markdefault'                   => 'Markeren als standaard',
    'policyurlupdatetitle'           => 'Beleids-URL bijgewerkt',
    'policyurlupdatemessage'       => 'De beleids-URL is succesvol bijgewerkt.',

    // Easy Dates
    'edoneyear'                    => 'één jaar',
    'edxyear'                      => '%d jaar',
    'edonemonth'                   => 'één maand',
    'edxmonth'                     => '%d maanden',
    'edoneday'                     => 'één dag',
    'edxday'                       => '%d dagen',
    'edonehour'                    => 'één uur',
    'edxhour'                      => '%d uur',
    'edoneminute'                  => 'één minuut',
    'edxminute'                    => '%d minuten',
    'edjustnow'                    => 'zojuist',
    'edxseconds'                   => '%d seconden',
    'ago'                          => 'geleden',

    // Operators
    'opcontains'                   => 'Bevat',
    'opnotcontains'                => 'Bevat geen',
    'opequal'                      => 'Gelijk aan',
    'opnotequal'                   => 'Niet gelijk aan',
    'opgreater'                    => 'Groter dan',
    'opless'                       => 'Kleiner dan',
    'opregexp'                     => 'Reguliere expressie',
    'opchanged'                    => 'Gewijzigd',
    'opnotchanged'                 => 'Niet gewijzigd',
    'opchangedfrom'                => 'Gewijzigd van',
    'opchangedto'                  => 'Gewijzigd naar',
    'opnotchangedfrom'             => 'Niet gewijzigd van',
    'opnotchangedto'               => 'Niet gewijzigd naar',
    'matchand'                     => 'AND',
    'matchor'                      => 'OR',
    'strue'                        => 'Waar',
    'sfalse'                       => 'Niet waar',
    'notifynoperm'                 => 'Helaas heb je niet de rechten voor het uitvoeren van dit verzoek.',
    'titlenoperm'                  => 'Onvoldoende rechten',
    'msgnoperm'                    => 'Helaas heb je niet de rechten voor het uitvoeren van dit verzoek.',
    'msgnoperm1'                   => 'The ticket has been created but you do not have the permission to carry out other operations.',
    'cyesterday'                   => 'Gisteren',
    'ctoday'                       => 'Vandaag',
    'ccurrentwtd'                  => 'Deze week tot vandaag',
    'ccurrentmtd'                  => 'Deze maand tot vandaag',
    'ccurrentytd'                  => 'Dit jaar tot vandaag',
    'cl7days'                      => 'Laatste 7 dagen',
    'cl30days'                     => 'Laatste 30 dagen',
    'cl90days'                     => 'Laatste 90 dagen',
    'cl180days'                    => 'Laatste 180 dagen',
    'cl365days'                    => 'Laatste 365 dagen',
    'ctomorrow'                    => 'Morgen',
    'cnextwfd'                     => 'Deze week vanaf vandaag',
    'cnextmfd'                     => 'Deze maand vanaf vandaag',
    'cnextyfd'                     => 'Dit jaar vanaf vandaag',
    'cn7days'                      => 'Komende 7 dagen',
    'cn30days'                     => 'Komende 30 dagen',
    'cn90days'                     => 'Komende 90 dagen',
    'cn180days'                    => 'Komende 180 dagen',
    'cn365days'                    => 'Komende 365 dagen',
    'new'                          => 'Nieuw',
    'phoneext'                     => 'Telefoon: %s',
    'snewtickets'                  => 'Nieuwe tickets',
    'sadvancedsearch'              => 'Geavanceerd zoeken',
    'squicksearch'                 => 'Snel zoeken:',
    'sticketidlookup'              => 'Ticket-ID zoeken:',
    'screatorreplier'              => 'Auteur/antwoorder:',
    'smanage'                      => 'Beheren',
    'clear'                        => 'Wissen',
    'never'                        => 'Nooit',
    'seuser'                       => 'Gebruikers',
    'seuserorg'                    => 'Gebruikersorganisaties',
    'manage'                       => 'Beheren',
    'import'                       => 'Importeren',
    'export'                       => 'Exporteren',
    'comments'                     => 'Reacties',
    'commentdata'                  => 'Reacties:',
    'postnewcomment'               => 'Nieuwe reactie plaatsen',
    'replytocomment'               => 'Reageren op reactie',
    'buttonsubmit'                 => 'Verzenden',
    'reply'                        => 'Antwoorden',

    // Flags
    'purpleflag'                   => 'Paarse vlag',
    'redflag'                      => 'Rode vlag',
    'orangeflag'                   => 'Oranje vlag',
    'yellowflag'                   => 'Gele vlag',
    'blueflag'                     => 'Blauwe vlag',
    'greenflag'                    => 'Groene vlag',

    'calendar'                     => 'Kalender',
    'cal_january'                  => 'Januari',
    'cal_february'                 => 'Februari',
    'cal_march'                    => 'Maart',
    'cal_april'                    => 'April',
    'cal_may'                      => 'Mei',
    'cal_june'                     => 'Juni',
    'cal_july'                     => 'Juli',
    'cal_august'                   => 'Augustus',
    'cal_september'                => 'September',
    'cal_october'                  => 'Oktober',
    'cal_november'                 => 'November',
    'cal_december'                 => 'December',

    /**
     * ###############################################
     * APP LIST
     * ###############################################
     */
    'app_base'                     => 'Basis',
    'app_tickets'                  => 'Tickets',
    'app_knowledgebase'            => 'Kennisbank',
    'app_parser'                   => 'Emailparser',
    'app_livechat'                 => 'Live Support',
    'app_troubleshooter'           => 'Probleemoplosser',
    'app_news'                     => 'Nieuws',
    'app_core'                     => 'Core',
    'app_backend'                  => 'Backend',
    'app_reports'                  => 'Rapporten',

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
    'wrong_profile_image'          => 'De profielafbeelding is niet bijgewerkt. Verkeerd formaat.',
    'wrong_image_size'             => 'De afbeeldingsgrootte is groter dan de toegestane uploadgrootte.',
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

        14 => array('Instellingen', 'bar_settings.gif', APP_CORE, '/Base/Settings/Index'),
        26 => array('REST API', 'bar_restapi.gif', APP_BASE),
        27 => array('Taggenerator', 'bar_tag.gif', APP_LIVECHAT, '/Base/TagGenerator/Index'),
        0  => array('Sjablonen', 'bar_templates.gif', APP_BASE),
        1  => array('Talen', 'bar_languages.gif', APP_CORE),
        2  => array('Aangepaste velden', 'bar_customfields.gif', APP_CORE),
        25 => array('GeoIP', 'bar_geoip.gif', APP_CORE, '/Base/GeoIP/Manage'),
        13 => array('Live Support', 'bar_livesupport.gif', APP_LIVECHAT),
        4  => array('Emailparser', 'bar_mailparser.gif', APP_PARSER),
        5  => array('Tickets', 'bar_tickets.gif', APP_TICKETS),
        35 => array ('Toestemming van de gebruiker', 'bar_maintenance.gif', APP_BASE, '/Base/Consent/Index'),
        29 => array('Workflow', 'bar_workflow.gif', APP_TICKETS, '/Tickets/Workflow/Manage'),
        30 => array('Beoordelingen', 'bar_ratings.gif', APP_TICKETS, '/Base/Rating/Manage'),
        6  => array('SLA', 'bar_sla.gif', APP_TICKETS),
        7  => array('Escalaties', 'bar_escalations.gif', APP_TICKETS, '/Tickets/Escalation/Manage'),
        20 => array('Bayesiaans', 'bar_bayesian.gif', APP_TICKETS),
        21 => array('Kennisbank', 'bar_knowledgebase.gif', APP_KNOWLEDGEBASE),
        23 => array('Nieuws', 'bar_news.gif', APP_NEWS),
        24 => array('Probleemoplosser', 'bar_troubleshooter.gif', APP_TROUBLESHOOTER),
        31 => array('Widgets', 'bar_widgets.gif', APP_BASE, '/Base/Widget/Manage'),
        32 => array('Apps', 'bar_apps.gif', APP_BASE, '/Base/App/Manage'),
        9  => array('Logs', 'bar_logs.gif', APP_BASE),
        10 => array('Geplande taken', 'bar_cron.gif', APP_BASE),
        11 => array('Database', 'bar_database.gif', APP_BASE),
        33 => array('Importeren', 'bar_import.gif', APP_BASE),
        12 => array('Diagnostiek', 'bar_diagnostics.gif', APP_BASE),
        34 => array('Onderhoud', 'bar_maintenance.gif', APP_BASE),
    );

    SWIFT::Set('adminbar', $_adminBarContainer);

    $_adminBarItemContainer = array(
        0  => array(
            0 => array('Groepen', '/Base/TemplateGroup/Manage'),
            1 => array('Templates', '/Base/Template/Manage'),
            2 => array('Zoeken', '/Base/TemplateSearch/Index'),
            3 => array('Importeren/exporteren', '/Base/TemplateManager/ImpEx'),
            4 => array('Herstellen', '/Base/TemplateRestore/Index'),
            5 => array('Diagnostiek', '/Base/TemplateDiagnostics/Index'),
            6 => array('Koplogo\'s', '/Base/TemplateManager/Personalize'),
        ),

        1  => array(
            0 => array('Talen', '/Base/Language/Manage'),
            1 => array('Zinnen', '/Base/LanguagePhrase/Manage'),
            2 => array('Zoeken', '/Base/LanguagePhrase/Search'),
            3 => array('Importeren/exporteren', '/Base/LanguageManager/ImpEx'),
            4 => array('Herstellen', '/Base/LanguageManager/Restore'),
            5 => array('Diagnostiek', '/Base/LanguageManager/Diagnostics'),
        ),

        2  => array(
            0 => array('Groepen', '/Base/CustomFieldGroup/Manage'),
            1 => array('Velden', '/Base/CustomField/Manage'),
        ),

        4  => array(
            0 => array('Instellingen', '/Parser/SettingsManager/Index'),
            1 => array('Emailwachtrij', '/Parser/EmailQueue/Manage'),
            2 => array('Regels', '/Parser/Rule/Manage'),
            3 => array('Breaklines', '/Parser/Breakline/Manage'),
            4 => array('Bans', '/Parser/Ban/Manage'),
            5 => array('Catch-allregels', '/Parser/CatchAll/Manage'),
            6 => array('Lusblokkeringen', '/Parser/LoopBlock/Manage'),
            7 => array('Lusblokkeringsregels', '/Parser/LoopRule/Manage'),
            9 => array('Parserlog', '/Parser/ParserLog/Manage'),
        ),

        5  => array(
            0 => array('Instellingen', '/Tickets/SettingsManager/Index'),
            1 => array('Types', '/Tickets/Type/Manage'),
            2 => array('Statussen', '/Tickets/Status/Manage'),
            3 => array('Prioriteiten', '/Tickets/Priority/Manage'),
            4 => array('Bestandstypes', '/Tickets/FileType/Manage'),
            5 => array('Links', '/Tickets/Link/Manage'),
            8 => array('Automatisch sluiten', '/Tickets/AutoClose/Manage'),
            7 => array('Onderhoud', '/Tickets/Maintenance/Index'),
        ),

        6  => array(
            0 => array('Instellingen', '/Tickets/SettingsManager/SLA'),
            1 => array('Plannen', '/Tickets/SLA/Manage'),
            2 => array('Schema\'s', '/Tickets/Schedule/Manage'),
            3 => array('Vakantiedagen', '/Tickets/Holiday/Manage'),
            4 => array('Importeren/exporteren', '/Tickets/HolidayManager/Index'),
        ),

        20 => array(
            0 => array('Instellingen', '/Tickets/SettingsManager/Bayesian'),
            1 => array('Categorieën', '/Tickets/BayesianCategory/Manage'),
            2 => array('Diagnostiek', '/Tickets/BayesianDiagnostics/Index'),
        ),

        9  => array(
            0 => array('Foutenlog', '/Base/ErrorLog/Manage'),
            1 => array('Takenlog', '/Base/ScheduledTasks/TaskLog'),
            3 => array('Activiteitenlog', '/Base/ActivityLog/Manage'),
            4 => array('Aanmeldlog', '/Base/LoginLog/Manage'),
        ),

        10 => array(
            0 => array('Beheren', '/Base/ScheduledTasks/Manage'),
            1 => array('Takenlog', '/Base/ScheduledTasks/TaskLog'),
        ),

        11 => array(
            0 => array('Tabelgegevens', '/Base/Database/TableInfo'),
        ),

        12 => array(
            0 => array('Actieve sessies', '/Base/Diagnostics/ActiveSessions'),
            1 => array('Cachegegevens', '/Base/Diagnostics/CacheInformation'),
            2 => array('Cache opnieuw opbouwen', '/Base/Diagnostics/RebuildCache'),
            3 => array('PHP-info', '/Base/Diagnostics/PHPInfo'),
            4 => array('Probleem melden', '/Base/Diagnostics/ReportBug'),
            5 => array('Licentiegegevens', '/Base/Diagnostics/LicenseInformation'),
        ),

        13 => array(
            0 => array('Instellingen', '/LiveChat/SettingsManager/Index'),
            1 => array('Bezoekersregels', '/LiveChat/Rule/Manage'),
            2 => array('Bezoekersgroepen', '/LiveChat/Group/Manage'),
            3 => array('Medewerkervaardigheden', '/LiveChat/Skill/Manage'),
            4 => array('Bezoekersbans', '/LiveChat/Ban/Manage'),
            5 => array('Berichtroutering', '/LiveChat/MessageRouting/Index'),
            6 => array('Medewerkerstatus', '/LiveChat/OnlineStatus/Index'),
        ),

        19 => array(
            0 => array('Instellingen', '/Manuals/SettingsManager/Index'),
        ),

        21 => array(
            0 => array('Instellingen', '/KnowledgeBase/SettingsManager/Index'),
            1 => array('Onderhoud', '/KnowledgeBase/Maintenance/Index'),
        ),

        22 => array(
            0 => array('Instellingen', '/Downloads/SettingsManager/Index'),
        ),

        23 => array(
            0 => array('Instellingen', '/News/SettingsManager/Index'),
            1 => array('Importeren/exporteren', '/News/ImpEx/Manage'),
        ),

        24 => array(
            0 => array('Instellingen', '/Troubleshooter/SettingsManager/Index'),
        ),

        25 => array(
            0 => array('Onderhoud', '/Base/GeoIP/Manage'),
        ),

        26 => array(
            0 => array('Instellingen', '/Base/Settings/RESTAPI'),
            1 => array('API Information', '/Base/RESTAPI/Index'),
        ),

        33 => array(
            0 => array('Beheren', '/Base/Import/Manage'),
            1 => array('Importlog', '/Base/ImportLog/Manage'),
        ),

        34 => array(
            0 => array('Bijlagen opschonen', '/Base/PurgeAttachments/Index'),
            1 => array('Bijlagen verplaatsen', '/Base/MoveAttachments/Index'),
        ),

    );

    // Log stuff
    if (SWIFT_PRODUCT == 'Fusion' || SWIFT_PRODUCT == 'Resolve' || SWIFT_PRODUCT == 'Case') {
        $_adminBarItemContainer[9][2] = array('Parser Log', '/Parser/ParserLog/Manage');
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

        1 => array('Start', 80, APP_CORE),
        2 => array('Medewerker', 100, APP_BASE),
        3 => array('Afdelingen', 120, APP_BASE),
        4 => array('Gebruikers', 100, APP_BASE),
    );

    SWIFT::Set('adminmenu', $_adminMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_adminLinkContainer = array(

        1 => array(
            0 => array('Start', '/Base/Home/Index'),
            1 => array('Instellingen', '/Base/Settings/Index'),
        ),

        2 => array(
            0 => array('Medewerkers beheren', '/Base/Staff/Manage'),
            1 => array('Teams beheren', '/Base/StaffGroup/Manage'),
            2 => array('Medewerker toevoegen', '/Base/Staff/Insert'),
            3 => array('Teams toevoegen', '/Base/StaffGroup/Insert'),
            4 => array('LoginShare', '/Base/Settings/StaffLoginShare'),
            5 => array('Instellingen', '/Base/Settings/Staff'),
        ),

        3 => array(
            0 => array('Afdelingen beheren', '/Base/Department/Manage'),
            1 => array('Afdeling toevoegen', '/Base/Department/Insert'),
            2 => array('Toegangsoverzicht', '/Base/Department/AccessOverview'),
        ),

        4 => array(
            0 => array('Gebruikersgroepen beheren', '/Base/UserGroup/Manage'),
            1 => array('Gebruikersgroepen toevoegen', '/Base/UserGroup/Insert'),
            2 => array('LoginShare', '/Base/Settings/UserLoginShare'),
            3 => array('Instellingen', '/Base/Settings/User'),
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
        1 => array('Start', 80, APP_CORE),
        2 => array('Tickets', 100, APP_TICKETS, 't_entab'),
        3 => array('Live Support', 120, APP_LIVECHAT, 'ls_entab'),
        4 => array('Kennisbank', 140, APP_KNOWLEDGEBASE, 'kb_entab'),
        6 => array('Probleemoplosser', 140, APP_TROUBLESHOOTER, 'tr_entab'),
        7 => array('Nieuws', 90, APP_NEWS, 'nw_entab'),
        8 => array('Gebruikers', 90, APP_CORE, 'cu_entab'),
        9 => array('Rapporten', 90, APP_REPORTS, 'rp_entab'),
    );

    SWIFT::Set('staffmenu', $_staffMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_staffLinkContainer = array(

        1 => array(
            0 => array('Start', '/Base/Home/Index'),
            1 => array('Mijn voorkeuren', '/Base/Preferences/ViewPreferences'),
            2 => array('Notificaties', '/Base/Notification/Manage', 'staff_canviewnotifications'),
            3 => array('Reacties', '/Base/Comment/Manage', 'staff_canviewcomments'),
        ),

        2 => array(
            0 => array('Tickets beheren', '/Tickets/Manage/Index', 'staff_tcanviewtickets'),
            1 => array('Zoeken', ':UIDropDown(\'ticketsearchmenu\', event, \'linkmenu2_1\', \'linksdiv\'); LinkTicketSearchForms();'),
            2 => array('Nieuwe ticket', ':UICreateWindow(\'/Tickets/Ticket/NewTicket/\', \'newticket\', \'New Ticket\', \'Loading..\', 500, 350, true);', 'staff_tcaninsertticket'),
            3 => array('Macros', '/Tickets/MacroCategory/Manage', 'staff_tcanviewmacro'),
            4 => array('Weergaven', '/Tickets/View/Manage', 'staff_tcanview_views'),
            5 => array('Filters', ':UIDropDown(\'ticketfiltermenu\', event, \'linkmenu2_5\', \'linksdiv\');'),
        ),

        3 => array(
            0 => array('Chatgeschiedenis', '/LiveChat/ChatHistory/Manage', 'staff_lscanviewchat'),
            1 => array('Berichten/onderzoeken', '/LiveChat/Message/Manage', 'staff_lscanviewmessages'),
            2 => array('Oproeplogs', '/LiveChat/Call/Manage', 'staff_lscanviewcalls'),
            3 => array('Standaard antwoorden', '/LiveChat/CannedCategory/Manage', 'admin_lscanviewcanned'),
            4 => array('Zoeken', ':UIDropDown(\'chatsearchmenu\', event, \'linkmenu3_4\', \'linksdiv\'); LinkChatSearchForms();'),
        ),

        4 => array(
            0 => array('Kennisbank weergeven', '/Knowledgebase/ViewKnowledgebase/Index'),
            1 => array('Kennisbank beheren', '/Knowledgebase/Article/Manage'),
            2 => array('Categorieën', '/Knowledgebase/Category/Manage'),
            3 => array('Nieuw artikel', '/Knowledgebase/Article/Insert'),
        ),

        5 => array(
            0 => array('View Downloads', '/Downloads/Downloads/Manage'),
            1 => array('Manage Downloads', '/Downloads/Downloads/Manage'),
            2 => array('Categorieën', '/Downloads/Category/Insert'),
            3 => array('New File', '/Downloads/File/Insert'),
        ),

        6 => array(
            0 => array('Probleemoplosser weergeven', '/Troubleshooter/Category/ViewAll'),
            1 => array('Probleemoplosser beheren', '/Troubleshooter/Step/Manage'),
            2 => array('Categorieën', '/Troubleshooter/Category/Manage'),
            3 => array('Nieuwe stap', ':UICreateWindow(\'/Troubleshooter/Step/InsertDialog/\', \'newstep\', \'Insert Step\', \'Loading..\', 400, 200, true);'),
        ),

        7 => array(
            0 => array('Nieuws weergeven', '/News/NewsItem/ViewAll', 'staff_nwcanviewitems'),
            1 => array('Nieuws beheren', '/News/NewsItem/Manage', 'staff_nwcanmanageitems'),
            2 => array('Categorieën', '/News/Category/Manage', 'staff_nwcanviewcategories'),
            3 => array('Abonnees', '/News/Subscriber/Manage', 'staff_nwcanviewsubscribers'),
            4 => array('Nieuws toevoegen', ':UICreateWindow(\'/News/NewsItem/InsertDialog/\', \'newnews\', \'Insert News\', \'Loading..\', 600, 420, true);'),
        ),

        8 => array(
            0 => array('Gebruikers beheren', '/Base/User/Manage', 'staff_canviewusers'),
            1 => array('Organisaties beheren', '/Base/UserOrganization/Manage', 'staff_canviewuserorganizations'),
            2 => array('Zoeken', ':UIDropDown(\'usersearchmenu\', event, \'linkmenu8_2\', \'linksdiv\'); LinkUserSearchForms();'),
            3 => array('Gebruiker toevoegen', '/Base/User/Insert', 'staff_caninsertuser'),
            4 => array('Organisaties toevoegen', '/Base/UserOrganization/Insert', 'staff_caninsertuserorganization'),
            5 => array ('Import Users', '/Base/User/ImportCSV', 'staff_caninsertuser'),
        ),

        9 => array(
            0 => array('Rapporten beheren', '/Reports/Report/Manage'),
            1 => array('Categorieën', '/Reports/Category/Manage'),
            2 => array('Nieuw rapport', ':UICreateWindow(\'/Reports/Report/InsertDialog/\', \'newreport\', \'New Report\', \'Loading..\', 400, 280, true);'),
        ),
    );

    $_staffLinkContainer[2][1][15] = true;
    $_staffLinkContainer[2][5][15] = true;
    $_staffLinkContainer[8][2][15] = true;
    $_staffLinkContainer[3][4][15] = true;

    SWIFT::Set('stafflinks', $_staffLinkContainer);
}




return $__LANG;
