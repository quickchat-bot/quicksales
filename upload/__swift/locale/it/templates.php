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

    'tabsettings'                   => 'Impostazioni',
    'templates'                     => 'Modelli',

    // Header Logos (nee Personalize)
    'personalizationerrmsg' => 'Devi fornire almeno un logo di intestazione',
    'titlepersonalization'          => 'Aggiornato logo d\'intestazione',
    'msgpersonalization'            => 'I tuoi logo d\'intestazione sono stati salvati. Se un logo viene modificato, dovrai aggiornare la pagina per visualizzare le modifiche.',
    'tabpersonalize'                => 'Logo d\'Intestazione',
    'generalinformation'            => 'Informazioni Generali',
    'companyname'                   => 'Nome Azienda',
    'desc_companyname'              => 'Il nome qui inserito viene utilizzato per brandizzare l\'interfaccia del supporto clienti e nelle email in uscita.',
    'defaultreturnemail'            => 'Indirizzo Email Mittente Predefinito',
    'desc_defaultreturnemail'       => 'Questo indirizzo viene utilizzato come predefinito nel campo "Da" per le email in uscita. Questo indirizzo deve corrispondere a una coda email attiva in modo che possa accettare le risposte dei clienti.',
    'logoimages'                    => 'Immagini di Intestazione',
    'supportcenterlogo'             => 'Logo di intestazione del centro di supporto',
    'desc_supportcenterlogo'        => 'Questo logo verrà visualizzato nel centro di supporto rivolto ai clienti. È consigliabile che il logo rientri nelle dimensioni di 150 (larghezza) per 40 (altezza) pixel',
    'stafflogo'                     => 'Logo d\'intestazione del pannello di controllo',
    'desc_stafflogo'                => 'Questo logo è visualizzato nell\'intestazione dei pannelli di controllo (in alto a sinistra). Il logo <em>deve rientrare nelle dimenzioni</em> <strong>150</strong> (larghezza) per <strong>24</strong> (altezza) pixel.',
    'personalize'                   => 'Logo d\'Intestazione',

    // Import and export
    'tabexport'                     => 'Esporta',
    'export'                        => 'Esporta',
    'tabimport'                     => 'Importa',
    'import'                        => 'Importa',
    'result'                        => 'Risultato',
    'exporthistory'                 => 'Esporta anche la cronologia dei modelli',
    'desc_exporthistory'            => 'Così come le versioni più recenti dei modelli anche le versioni precedenti verranno esportate.',
    'mergeoptions'                  => 'Opzioni per l\'Unione',
    'addtohistory'                  => 'Mantieni la cronologia delle revisioni del modello',
    'desc_addtohistory'             => 'Se l\'unione del gruppo di modelli sovrascrive tutti i modelli esistenti, i modelli sovrascritti verranno mantenuti nella cronologia modelli.',
    'titleversioncheckfail'         => 'Questo gruppo di modelli è datato',
    'msgversioncheckfail'           => 'Non è stato possibile importare questo gruppo di modelli perché è stato generato con una versione precedente di Kayako e quindi potrebbero mancare modelli. Se desideri ignorare il controllo di versione, attiva <em>Ignora la versione gruppo di modelli</em>.',
    'importexport'                  => 'Importa/Esporta',
    'exporttgroup'                  => 'Gruppo di modelli da esportare',
    'desc_exporttgroup'             => 'Il gruppo di modelli da esportare come file XML.',
    'exportoptions'                 => 'Tipo di esportazione',
    'desc_exportoptions'            => 'Il tipo di modelli da esportare.',
    'exportalltemplates'            => 'Esporta tutti i modelli',
    'exportmodifications'           => 'Esporta solo i modelli modificati',
    'templatefile'                  => 'File XML del gruppo di modelli',
    'desc_templatefile'             => 'Seleziona un file XML del gruppo di modelli dal computer.',
    'createnewgroup'                => '-- Crea un nuovo gruppo di modelli --',
    'mergewith'                     => 'Unisci i modelli importati con',
    'desc_mergewith'                => 'Scegli se creare un nuovo gruppo di modelli utilizzando il contenuto del file o unire solamente i modelli modificati con un gruppo di modelli esistente.',
    'ignoreversion'                 => 'Ignora la versione gruppo di modelli',
    'desc_ignoreversion'            => 'Se selezionato, la versione del file di importazione verrà ignorata. Si consiglia di non abilitare questa opzione in quanto può causare problemi al Centro di Supporto Clienti.',
    'titletemplateimportfailed'     => 'C\'era un problema con il file del gruppo di modelli',
    'msgtemplateimportfailed'       => 'Non è stato possibile elaborare il file del gruppo di modelli. Potrebbe contenere dati non validi.',
    'titletgroupmerge'              => 'È stato unito il file del gruppo di modelli con %s',
    'msgtgroupmerge'                => 'Il file del gruppo di modelli %s è stato importato e unito al gruppo di modelli %s con successo.',
    'titletgroupimport'             => 'Importato gruppo di modelli %s',
    'msgtgroupimport'               => 'Il file del gruppo di modelli %s è stato importato e il gruppo di modelli %s  è stato creato con successo.',

    // Templates
    'changegroup'                   => 'Cambia il Gruppo di Modelli',
    'restoretemplates'              => 'Ripristina Modelli',
    'desc_restoretemplates'         => '',
    'moditgroup'                    => 'Cerca nel gruppo di modelli',
    'desc_moditgroup'               => 'I modelli di questo gruppo di modelli verranno controllati per errori.',
    'tabgeneral'                    => 'Generale',
    'restoretgroup'                 => 'Ripristina i modelli alle versioni originali più recenti: %s',
    'tabrestore'                    => 'Ripristina Modelli',
    'findtemplates'                 => 'Trova Modelli',
    'titlerestoretemplates'         => 'Modelli ripristinati (%d)',
    'msgrestoretemplates'           => 'Sono stati ripristinati i seguenti modelli:',
    'tabdiagnostics'                => 'Diagnostica',
    'tabsearch'                     => 'Modelli di Ricerca',
    'titletgrouprestorecat'         => 'Categoria del gruppo di modelli ripristinata',
    'msgtgrouprestorecat'           => 'I modelli nella categoria %s di %s (%s) sono stati ripristinati con successo.',
    'expandcontract'                => 'Espandi/Contrai',
    'tabhistory'                    => 'Cronologia',
    'templateversion'               => 'Numero di versione del modello',
    'saveasnewversion'              => 'Salva una nuova versione del modello',
    'titletemplaterestore'          => '%s ripristinato',
    'msgtemplaterestore'            => 'Il modello %s è stato ripristinato allo stato originale.',
    'titletemplateupdate'           => 'aggiornato %s',
    'msgtemplateupdate'             => 'Le modifiche al modello %s sono state salvate correttamente.',
    'tabedittemplate'               => 'Modello: %s (%s)',
    'titlenohistory'                => 'Nessuna cronologia dei modelli',
    'msgnohistory'                  => 'Non ci sono revisioni precedenti di questo modello, quindi non c\'è nulla da visualizzare.',
    'historydescription'            => 'Modifiche',
    'historyitemlist'               => '%s: %s (%s) Note: <em>%s</em>',
    'system'                        => '(Sistema)',
    'historyitemcurrent'            => '%s: <em><strong>Attuale</strong></em> (%s)',
    'compare'                       => 'Confronta',
    'current'                       => 'Attuale',
    'notcurrenttemp'                => 'Versione Vecchia',
    'exportdiff'                    => 'Esporta file in formato diff',
    'tabcomparison'                 => 'Confronta le Versioni',
    'changelognotes'                => 'Descrivi le modifiche',
    'desc_changelognotes'           => 'Se fai delle modifiche a questo modello, aggiungi qui una breve nota in modo che sia possibile tracciare le modifiche nella scheda <strong>Cronologia</strong>.',
    'none'                          => 'Nessuno',
    'inserttemplate'                => 'Inserisci Modello',
    'inserttgroup'                  => 'Gruppo di Modelli',
    'desc_inserttgroup'             => 'Seleziona il Gruppo di Modelli per questo modello.',
    'templateeditingguideline'      => 'Consigli sulle modifiche dei modelli',
    'desc_templateeditingguideline' => 'Utilizzando l\'editor di modelli puoi personalizzare l\'aspetto del centro di supporto. Se un aggiornamento futuro di Kayako include modifiche allo stesso modello, verrà chiesto di ripristinare il modello all\'ultima versione originale. Questo annullerà le modifiche apportate al modello e sarà necessario riapplicarle.<br><br>Per ridurre potenziali mal di testa dai un\'occhiata alla <a href="https://go.gfi.com/?pageid=GFIHelpDeskTemplates" target="_blank" rel="noopener noreferrer">guida sulla modifica dei modelli</a> prima di personalizzare il centro di supporto.',
    'restoreconfirmaskcat'          => 'Sei sicuro di voler ripristinare i modelli in questa categoria?\\nL\'azione non è reversibile; con il ripristino dei modelli c\'è il rischio di perdere tutte le modifiche dell\'interfaccia utente fatte nei modelli esistenti!',
    'inserttemplatetgroup'          => 'Gruppo di Modelli',
    'inserttemplatetcategory'       => 'Categoria Modello',
    'inserttemplatename'            => 'Nome modello',
    'desc_inserttemplatename'       => 'Immetti un nome per il modello utilizzando solo caratteri alfanumerici. Per esempio, <em>interstazione</em> o <em>benvenutocentrodisupporto</em>.',
    'titleinserttemplatedupe'       => 'Il nome del modello è già in uso',
    'msginserttemplatedupe'         => 'Questo gruppo di modelli ha già un modello con questo nome; si prega di sceglierne un altro.',
    'titleinserttemplatechar'       => 'Il nome del modello contiene caratteri non validi',
    'msginserttemplatechar'         => 'Il nome del modello può contenere solo caratteri alfanumerici (lettere e numeri).',
    'titleinserttemplate'           => 'Creato modello %s',
    'msginserttemplate'             => 'Il modello %s è stato creato nel gruppo di modelli %s.',
    'titletemplatedel'              => 'Modello eliminato',
    'msgtemplatedel'                => 'Il modello %s è stato eliminato.',

    // Template group
    'titleisenabledprob'            => 'Non è possibile disabilitare il gruppo di modelli predefinito',
    'msgisenabledprob'              => 'Questo gruppo di modelli è impostato come predefinito per l\'helpdesk; non può essere disabilitato.',
    'useloginshare'                 => 'Utilizza LoginShare per autenticare gli utenti',
    'desc_useloginshare'            => 'Gli utenti che accedono all\'helpdesk mentre questo gruppo di modelli è attivo verranno autenticati con l\'API LoginShare.',
    'groupusername'                 => 'Nome utente',
    'desc_groupusername'            => 'Immetti un nome utente per attivare la protezione tramite password per questo gruppo di modelli.',
    'passwordprotection'            => 'Protezione con Password',
    'enablepassword'                => 'Abilita la protezione con password',
    'desc_enablepassword'           => 'Per aprire il centro di supporto gli utenti finali dovranno immettere un nome utente e la password.',
    'password'                      => 'Password',
    'desc_password'                 => 'Immetti una password per attivare la protezione tramite password per questo gruppo di modelli.',
    'passwordconfirm'               => 'Reinserisci password',
    'desc_passwordconfirm'          => 'Conferma la password, per evitare errori di battitura.',
    'tabsettings_tickets'           => 'Impostazioni: Ticket',
    'tabsettings_livechat'          => 'Impostazioni: Chat dal Vivo',
    'isenabled'                     => 'Il gruppo di modelli è abilitato',
    'desc_isenabled'                => 'Se un gruppo di modelli è disabilitato, non sarà attivo e non sarà accessibile agli utenti finali.',
    'titlepwnomatch'                => 'Le password non corrispondono',
    'msgpwnomatch'                  => 'The passwords entered do not match. Please try again.',
    'titleinvalidgrouptitle'        => 'Il nome del gruppo di modelli contiene caratteri non validi',
    'msginvalidgrouptitle'          => 'Il nome di un gruppo di modelli può contenere solo caratteri alfanumerici.',
    'titlegrouptitleexists'         => 'Il nome del gruppo di modelli è già in uso',
    'msggrouptitleexists'           => 'Un altro gruppo di modelli sta usando questo titolo. Scegline uno diverso.',
    'winedittemplategroup'          => 'Modifica Gruppo di Modelli: %s',
    'tabpermissions'                => 'Autorizzazioni',
    'titletgroupupdate'             => 'Aggiornato gruppo di modelli %s',
    'msgtgroupupdate'               => 'Il gruppo di modelli %s è stato aggiornato con successo.',
    'titletgroupinsert'             => 'Creato gruppo di modelli %s',
    'msgtgroupinsert'               => 'Il gruppo di modelli %s è stato creato con successo.',
    'titletgroupnodel'              => 'Non è stato possibile eliminare il gruppo di modelli',
    'msgtgroupnodel'                => 'Non è stato possibile eliminare questo gruppo master di modelli:',
    'titletgroupdel'                => 'Gruppi di modelli eliminati (%d)',
    'msgtgroupdel'                  => 'Sono stati eliminati i seguenti gruppi di modelli:',
    'titletgrouprestore'            => 'Gruppi di modelli ripristinati (%d)',
    'msgtgrouprestore'              => 'I seguenti gruppi di modelli e i modelli contenuti sono stati ripristinati allo stato originale:',
    'insertemplategroup'            => 'Inserisci Gruppo di Modelli',
    'tgrouptitle'                   => 'Nome del gruppo di modelli',
    'desc_tgrouptitle'              => 'Il nome di un gruppo di modelli può contenere solo caratteri alfanumerici.',
    'gridtitle_companyname'         => 'Nome dell\'Organizzazione',
    'companyname'                   => 'Nome Azienda',
    'desc_companyname'              => 'Il nome qui inserito viene utilizzato per brandizzare l\'interfaccia del supporto clienti e nelle email in uscita.',
    'generaloptions'                => 'Opzioni Generali',
    'defaultlanguage'               => 'Lingua predefinita',
    'desc_defaultlanguage'          => 'La lingua che l\'helpdesk selezionerà automaticamente per questo gruppo di modelli.',
    'usergroups'                    => 'Ruoli del Gruppo di Utenti',
    'guestusergroup'                => 'Gruppo di utenti Guest (non collegato)',
    'desc_guestusergroup'           => 'Questo gruppo di utenti determinerà le autorizzazioni e le impostazioni per chiunque visita il centro di supporto e <strong>non è connesso</strong>.',
    'regusergroup'                  => 'Gruppo di utenti registrato (connesso)',
    'desc_regusergroup'             => 'Questo gruppo di utenti determinerà le autorizzazioni e le impostazioni per chi visita il centro di supporto ed è <strong>connesso</strong>.',
    'restrictgroups'                => 'Limita al gruppo di utenti registrato',
    'desc_restrictgroups'           => 'Con questo gruppo di modelli solo gli utenti appartenenti al gruppo utenti sopra specificati saranno in grado di accedere al centro di supporto.',
    'copyfrom'                      => 'Copia i modelli dal gruppo di modelli',
    'desc_copyfrom'                 => 'I modelli del gruppo di modelli qui selezionato verranno copiati in questo nuovo gruppo di modelli.',
    'promptticketpriority'          => 'L\'utente può selezionare una priorità ticket',
    'desc_promptticketpriority'     => 'Quando si crea un ticket, l\'utente può selezionare la priorità del ticket. In caso contrario verrà utilizzata la priorità predefinita.',
    'prompttickettype'              => 'L\'Utente può selezionare il tipo di ticket',
    'desc_prompttickettype'         => 'Quando crea un ticket, l\'utente può selezionare il tipo di ticket. In caso contrario, verrà utilizzato il tipo predefinito.',
    'tickettype'                    => 'Tipo di ticket predefinito',
    'desc_tickettype'               => 'I ticket creati da questo gruppo di modelli utilizzeranno come predefinito questo tipo.',
    'ticketstatus'                  => 'Stato predefinito ticket',
    'desc_ticketstatus'             => 'I ticket creati o con risposta da questo gruppo di modelli verranno impostati su questo stato. Se un utente risponde a un ticket associato a questo gruppo di modelli il ticket verrà modificato a questo stato.',
    'ticketpriority'                => 'Priorità ticket predefinita',
    'desc_ticketpriority'           => 'I ticket creati da questo gruppo di modelli verranno impostati su questa priorità come predefinito.',
    'ticketdep'                     => 'Dipartimento predefinito',
    'desc_ticketdep'                => 'Questo dipartimento verrà selezionato come predefinito sulla pagina <em>invia ticket</em> nel centro di supporto di questo gruppo di modelli.',
    'livechatdep'                   => 'Dipartimento predefinito',
    'desc_livechatdep'              => 'Questo dipartimento verrà selezionato come predefinito nel modulo di richiesta di chat dal vivo di questo gruppo di modelli.',
    'ticketsdeptitle'               => '%s (Ticket)',
    'livesupportdeptitle'           => '%s (Supporto Live)',
    'isdefault'                     => 'Questo gruppo di modelli è l\'impostazione predefinita dell\'helpdesk',
    'desc_isdefault'                => 'Il gruppo di modelli predefinito per un helpdesk verrà utilizzato sempre a meno che ne venga specificato un altro.',
    'loginshare'                    => 'LoginShare',

    // Manage template groups
    'grouptitle'                    => 'Titolo del Gruppo',
    'glanguage'                     => 'Lingua',
    'managegroups'                  => 'Gestisci Gruppi',
    'templategroups'                => 'Gruppi di Modelli',
    'desc_templategroups'           => '',
    'grouplist'                     => 'Elenco dei Gruppi',
    'restore'                       => 'Ripristina',
    'export'                        => 'Esporta',
    'restoreconfirmask'             => 'Sei sicuro di voler ripristinare i modelli in questo gruppo allo stato originale? Le modifiche apportate ai modelli andranno perse.',
    'restoreconfirm'                => 'I modelli nel gruppo %s sono stati ripristinati al loro stato originale',
    'inserttemplategroup'           => 'Inserisci Gruppo',
    'edittemplategroup'             => 'Modifica Gruppo',

    // ======= MANAGE TEMPLATES =======
    'desc_templates'                => '',
    'managetemplates'               => 'Gestisci Modelli',
    'templatetitle'                 => 'Modelli: %s',
    'expand'                        => 'Espandi',
    'notmodified'                   => 'Originale',
    'modified'                      => 'Modificato',
    'upgrade'                       => 'Scaduto',
    'expandall'                     => 'Espandi Tutto',
    'jump'                          => 'Salta',
    'templategroup'                 => 'Gruppo di Modelli',
    'desc_templategroup'            => '',
    'edittemplate'                  => 'Modifica Modello',
    'edittemplatetitle'             => 'Modello: %s (Gruppo: %s)',
    'templatedata'                  => 'Contenuti del Modello',
    'savetemplate'                  => 'Salva',
    'saveandreload'                 => 'Salva e Aggiorna',
    'restore'                       => 'Ripristina',
    'templatestatus'                => 'Stato del modello',
    'desc_templatestatus'           => '',
    'tstatus'                       => '<img src="%s" align="absmiddle" border="0" /> %s', // Switch position for RTL language
    'dateadded'                     => 'Ultima modifica',
    'desc_dateadded'                => '',
    'contents'                      => '',
    'desc_contents'                 => '',


    // Diagnostics
    'diagnostics'                   => 'Diagnostica',
    'moditgroup'                    => 'Cerca nel gruppo di modelli',
    'desc_moditgroup'               => 'I modelli di questo gruppo di modelli verranno controllati per errori.',
    'list'                          => 'Elenco',
    'diagtgroup'                    => 'Gruppi di Modelli',
    'desc_diagtgroup'               => '',
    'diagnose'                      => 'Diagnostica',
    'templatename'                  => 'Nome Modello',
    'status'                        => 'Stato',
    'compiletime'                   => 'Tempo di Compilazione',
    'diagnosetemplategroup'         => 'Modelli da diagnosticare: %s',

    // Search
    'search'                        => 'Cerca',
    'searchtemplates'               => 'Modelli di Ricerca',
    'query'                         => 'Cerca',
    'desc_query'                    => 'Testo da cercare nei modelli.',
    'searchtgroup'                  => 'Cerca nel gruppo di modelli',
    'desc_searchtgroup'             => 'I modelli in questo gruppo di modelli saranno inclusi nella ricerca.',
    'searchtemplategroup'           => 'Ricerca nei modelli: %s',

    // Template categories
    'template_general'              => 'Generale',
    'template_chat'                 => 'Supporto live',
    'template_troubleshooter'       => 'Risolutore problematiche',
    'template_news'                 => 'Notizie',
    'template_knowledgebase'        => 'Knowledgebase',
    'template_tickets'              => 'Ticket',
    'template_reports'              => 'Report',

    // Potentialy unused phrases in templates.php
    'desc_importexport'             => '',
    'restoretemplatestatus'         => 'Template Status',
    'restoresubmitquestion'         => 'Are you sure you wish to restore the selected templates?\\nThis action cannot be reversed, you will loose all modifications carried out in the selected templates.',
    'desc_diagnostics'              => '',
    'desc_search'                   => '',
    'tabplugins'                    => 'Plugins',
    'ls_app'                        => 'LoginShare Plugin',
    'wineditls'                     => 'Edit LoginShare Plugin: %s',
    'invalidloginshareplugin'       => 'Invalid LoginShare Plugin, Please make sure the LoginShare plugin exists in the database.',
    'lsnotitle'                     => 'No Settings Available',
    'lsnomsg'                       => 'There are no settings available for the LoginShare plugin <b>"%s"</b>.',
    'loginsharefile'                => 'LoginShare XML File',
    'desc_loginsharefile'           => 'Upload the LoginShare XML File',
    'titlenoelevatedls'             => 'Unable to Import LoginShare XML',
    'msgnoelevatedls'               => 'Kayako is unable to import the LoginShare XML file as it is required that you login with a staff user that has elevated rights. You can add your user to elevated right list in config/config.php file of the package.',
    'titlelsversioncheckfail'       => 'Version Check Failed',
    'msglsversioncheckfail'         => 'Kayako is unable to import the LoginShare Plugin as the plugin was created for an older version of Kayako',
    'titlelsinvaliduniqueid'        => 'Duplicate Unique ID Error',
    'msglsinvaliduniqueid'          => 'Kayako is unable to import the LoginShare Plugin due to a conflict in Unique ID. This usually means that the plugin has already been imported into the database.',
    'titlelsinvalidxml'             => 'Invalid XML File',
    'msglsinvalidxml'               => 'Kayako is unable to import the LoginShare Plugin as the XML file corrupt or contains invalid data.',
    'titlelsimported'               => 'Imported LoginShare Plugin',
    'msglsimported'                 => 'Kayako has successfully imported the %s LoginShare Plugin.',
    'titlelsdeleted'                => 'Deleted LoginShare Plugin',
    'msglsdeleted'                  => 'Successfully deleted the "%s" LoginShare Plugin from the database.',
    'tgroupjump'                    => 'Template Group: %s',
    'desc_templateversion'          => '',
    'desc_changelognotes'           => 'Se fai delle modifiche a questo modello, aggiungi qui una breve nota in modo che sia possibile tracciare le modifiche nella scheda <strong>Cronologia</strong>.',
    'desc_inserttgroup'             => 'Seleziona il Gruppo di Modelli per questo modello.',
    'titlelsupdate'                 => 'LoginShare Update',
    'msglsupdate'                   => 'Successfully updated "%s" LoginShare settings',
    'exporttemplates'               => 'Export Templates',
    'exportxml'                     => 'Export XML',
    'filename'                      => 'Filename',
    'desc_filename'                 => 'Specify the Export Filename.',
    'importtemplates'               => 'Import Templates',
    'importxml'                     => 'Import XML',
    'tgroupmergeconfirm'            => 'Template Group "%s" merged with import file',
    'versioncheckfailed'            => 'Version Check Failed: The uploaded template pack was created using older version of Kayako',
    'tgroupnewimportconfirm'        => 'Template Group "%s" imported successfully',
    'templategroupdetails'          => 'Template Group Details',
    'passworddontmatch'             => 'ERROR: Passwords don\'t match',
    'invalidgrouptitle'             => 'ERROR: Only alphanumeric characters can be used in the Template Group Title',
    'grouptitleexists'              => 'ERROR: Invalid Group Title. There is another Template Group with the same title; please choose a different title.',
    'desc_loginshare'               => 'Specify the LoginShare App to use to authenticate the visitors under this Template Group. Make sure you have updated the settings for this app under Templates &gt; LoginShare.',
    'groupinsertconfirm'            => 'Template Group "%s" inserted successfully',
    'groupdelconfirm'               => 'Template Group "%s" deleted successfully',
    'invalidgroup'                  => 'Invalid Template Group',
    'groupupdateconfirm'            => 'Template Group "%s" updated successfully',
    'templatecategories'            => 'Template Categories',
    'groupjump'                     => 'Group Jump',
    'legend'                        => 'Legend: ',
    'invalidtemplate'               => 'Invalid Template',
    'generalinfo'                   => 'General Information',
    'preview'                       => 'Preview',
    'copyclipboard'                 => 'Copy to Clipboard',
    'templateupdateconfirm'         => 'Template "%s" updated successfully',
    'templaterestoreconfirm'        => 'Templates "%s" restored to original contents',
    'templatesrestoreconfirm'       => '%s Templates restored to original contents',
    'clipboardconfirm'              => 'The Template contents have been copied to your clipboard. You can now paste the contents in your favorite HTML editor.',
    'clipboardconfirmmoz'           => 'The text to be copied has been selected. Press Ctrl+C to copy the text to the clipboard.',
    'listmodified'                  => 'List Modified Templates',
    'listtorestore'                 => 'List Templates to Restore',
    'diagnosesmarty'                => 'Diagnose Smarty Template Engine Errors',
    'modifiedtemplates'             => 'Modified Templates (Group: %s)',
    'listtemplates'                 => 'List of Templates (Group: %s)',
    'diagnoseerrors'                => 'Diagnose Errors (Group: %s)',
    'searchqueryd'                  => 'Search Query: %s',
    'pluginlist'                    => 'Plugin List',
    'hostname'                      => 'Hostname',
    'dbname'                        => 'DB Name',
    'dbuser'                        => 'DB User',
    'dbpass'                        => 'DB Password',
    'tableprefix'                   => 'Tabe Prefix',
    'ldaphostname'                  => 'Active Directory Host',
    'ldapport'                      => 'Port (Default: 389)',
    'ldapbasedn'                    => 'Base DN',
    'ldaprdn'                       => 'RDN',
    'ldappassword'                  => 'Password',
    'hsphostserver'                 => 'Server Hostname',
    'hspport'                       => 'Server Port',
    'hspurl'                        => 'XML API URL',
    'hspconnectfail'                => 'Could not connect to server. Try again later.',
    'template_parser'               => 'Email Parser',
    'loginapi_modernbill'           => 'ModernBill',
    'loginapi_ipb'                  => 'Invision Power Board',
    'loginapi_vb'                   => 'vBulletin',
    'loginapi_osc'                  => 'osCommerce',
    'loginapi_iono'                 => 'IONO License Manager',
    'loginapi_plexum'               => 'Plexum',
    'loginapi_awbs'                 => 'AWBS',
    'loginapi_phpaudit'             => 'PHPAudit v2',
    'loginapi_whmautopilot'         => 'WHMAP v3',
    'loginapi_activedirectory'      => 'Active Directory/LDAP',
    'loginapi_activedirectoryssl'   => 'Active Directory/LDAP (SSL)',
    'loginapi_ticketpurchaser'      => 'Ticker Purchaser',
    'loginapi_xcart'                => 'X-Cart',
    'loginapi_phpbb'                => 'PHPBB',
    'loginapi_smf'                  => 'Simple Machines Forum',
    'loginapi_mybb'                 => 'MyBB',
    'loginapi_xmb'                  => 'XMB',
    'loginapi_clientexec'           => 'Clientexec',
    'loginapi_joomla'               => 'Joomla CMS',
    'loginapi_hsphere'              => 'H-Sphere XML-API',
    'loginapi_phpprobid'            => 'PHPProBid',
    'loginapi_cubecart'             => 'CubeCart',
    'loginapi_modernbillv5'         => 'ModernBill v5',
    'loginapi_cscart'               => 'CS-Cart',
    'loginapi_fsr'                  => 'FSRevolution',
    'loginapi_viper'                => 'Viper Cart',
    'loginapi_xoops'                => 'XOOPS',
    'loginapi_whmcsintegration'     => 'WHMCS - Integration Placeholder Only (Not for direct logins)',
);


return $__LANG;