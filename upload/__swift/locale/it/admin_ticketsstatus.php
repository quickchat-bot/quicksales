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
    // ======= BEGIN v4 LOCALES =======
    'tickets'                               => 'Ticket',
    'tabgeneral'                            => 'Generale',
    'tabpermissions'                        => 'Autorizzazioni',
    'staffvisibilitycustom'                 => 'Impedisci la modifica dei ticket a questo stato',
    'desc_staffvisibilitycustom'            => 'Se attivo, solo le squadre qui sotto avranno i ticket impostati a questo stato.',
    'staffgroups'                           => 'Squadre del Personale',
    'triggersurvey'                         => 'Invia email di sondaggio della soddisfazione',
    'desc_triggersurvey'                    => 'Quando viene cambiato un ticket a questo stato, verrà inviata un\'email all\'utente con la richiesta di valutazione e feedback.',
    'statustitle'                           => 'Nome stato',
    'desc_statustitle'                      => 'Immetti un nome per questo stato.',
    'statuscolor'                           => 'Colore stato',
    'desc_statuscolor'                      => 'Consigliamo il verde per stati aperti e colori scuri per stati risolti.',
    'statusbgcolor'                         => 'Colore di fondo dello stato',
    'desc_statusbgcolor'                    => 'Nel pannello di controllo del personale, l\'intera barra di proprietà del ticket è riempita con questo colore (a seconda dello stato di ticket). Non puoi sbagliare!',
    'displayorder'                          => 'Ordine di visualizzazione',
    'desc_displayorder'                     => 'I link nei ticket vengono visualizzati secondo l\'ordine di visualizzazione, dal più piccolo al più grande.',
    'displayiconstatus'                     => 'Icona',
    'desc_displayiconstatus'                => 'Collega o carica un\'icona per questo stato. {$themepath} può essere utilizzato per puntare alla directory immagini dell\'helpdesk, per esempio: <em>{$themepath}icon_statusopen.gif</em>.',
    'statusdep'                             => 'Associa lo stato al dipartimento',
    'desc_statusdep'                        => 'Associa questo stato con un dipartimento specifico. Se non si associa, lo stato sarà disponibile a tutti i dipartimenti dei ticket.',
    'statusalldep'                          => '-- Non collegato --',
    'markasresolved'                        => 'Questo ticket ha lo stato di <em>risolto</em>?',
    'desc_markasresolved'                   => 'Abilita questa opzione se i ticket impostati su questo stato sono risolti o chiusi (cioè sistemati, risolti, finiti). Verranno nascosti dagli elenchi di ticket aperti.',
    'displayinmainlist'                     => 'Visualizzare nell\'elenco principale dei ticket',
    'desc_displayinmainlist'                => 'I ticket impostati su questo stato verranno elencati nell\'elenco principale dei ticket.',
    'displaycount'                          => 'Mostra il conteggio ticket nell\'albero di navigazione dei ticket',
    'desc_displaycount'                     => 'Quando è abilitato, il conteggio dei ticket impostati su questo stato verranno visualizzati nell\'albero di navigazione del pannello di controllo del personale. È consigliato disabilitare questa impostazione per eventuali stati risolti.',
    'statustype2'                           => 'Tipo di stato',
    'desc_statustype2'                      => 'Lo stato <strong>Pubblico</strong> è visibile ovunque e anche agli utenti finali.<br />Lo stato <strong>Privato</strong> è visibile solo agli utenti del personale.',
    'resetduetime'                          => 'Cancella la scadenza di risposta dei ticket quando si imposta questo stato',
    'desc_resetduetime'                     => 'Quando si cambiano i ticket a questo stato, l\'ora di scadenza del ticket verrà azzerata automaticamente.',
    'dispatchnotification'                  => 'Invia Notifica all\'Utente',
    'desc_dispatchnotification'             => 'Se attivato, il sistema invierà una notifica all\'utente/autore ogni volta che questo stato diventa attivo per un ticket.',
    'insertstatus'                          => 'Inserisci Stato',
    'desc_insertstatus'                     => '',
    'invalidticketstatus'                   => 'Non è stato possibile caricare lo stato del ticket.',
    'managestatus'                          => 'Stati',
    'desc_managestatus'                     => '',
    'wineditticketstatus'                   => 'Modifica Stato: %s',
    'editstatus'                            => 'Modifica Stato',
    'desc_editstatus'                       => '',
    'taboptions'                            => 'Opzioni',
    'titledelticketstatus'                  => 'Eliminati stati ticket (%d)',
    'msgdelticketstatus'                    => 'Sono stati eliminati i seguenti stati dei ticket:',
    'titleticketstatusinsert'               => 'Creato stato ticket %s',
    'msgticketstatusinsert'                 => 'Lo stato del ticket %s è stato creato con successo.',
    'titleticketstatusupdate'               => 'Aggiornato stato ticket %s',
    'msgticketstatusupdate'                 => 'Lo stato del ticket %s è stato aggiornato con successo.',
    'titlemasterstatusdelete'               => 'Impossibile eliminare gli stati dei ticket (%d)',
    'msgmasterstatusdelete'                 => 'Non è stato possibile eliminare i seguenti stati master di ticket:',
    'tablanguages'                          => 'Lingue: Traduzione',
    'title_statusinuse'                     => 'Stato in Uso',
    'message_statusinuse_editassociation'   => 'Ci sono ticket impostati su questo stato che appartengono all\'esistente dipartimento collegato. È necessario modificare lo stato dei ticket prima di che si possa aggiornare il dipartimento collegato.',
    'message_statusinuse_createassociation' => 'Ci sono ticket impostati su questo stato che appartengono all\'esistente dipartimento collegato. È necessario modificare lo stato dei ticket prima di che si possa aggiornare il dipartimento collegato.',
    // ======= END v4 LOCALES =======
);


return $__LANG;
