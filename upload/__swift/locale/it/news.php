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
    'news'                 => 'Notizie',
    'managenews'           => 'Gestisci Notizie',
    'managesubscribers'    => 'Gestisci gli Abbonati',
    'insertnews'           => 'Inserisci Notizie',
    'importexport'         => 'Importa/Esporta',

    // Manage Subscribers
    'subscribers'          => 'Abbonati',
    'dlsubscriberconfirm'  => 'Abbonato eliminato',
    'subemail'             => 'Indirizzo email',
    'desc_subemail'        => 'Inserisci l\'indirizzo email dell\'abbonato.',
    'substatus'            => 'Stato di Convalida',
    'subdate'              => 'Data',
    'sublist'              => 'Elenco Abbonati',
    'legend'               => 'Legenda: ',
    'subinsertconfirm'     => '%s è stato registrato',

    // Insert Subscriber
    'insertsubscriber'     => 'Inserisci Abbonato',

    // Edit Subscriber
    'invalidsubscriber'    => 'Abbonato non valido',
    'editsubscriber'       => 'Modifica Abbonato',
    'subscriberdetails'    => 'Dettagli dell\'Abbonato',
    'subupdateconfirm'     => 'Aggiornato l\'abbonato %s',

    // Manage News
    'newssubject'          => 'Oggetto delle Notizie',
    'newsdescription'      => 'Descrizione',
    'desc_newsdescription' => 'Inserisci una breve descrizione dell\'articolo delle notizie. Questo campo è limitato a <strong>255</strong> caratteri.',
    'newscontents'         => 'Contenuti',
    'newstype'             => 'Tipo di Notizia',
    'desc_newstype'        => '<strong>Pubblico</strong> Verrà pubblicato solo nel centro di supporto rivolto ai clienti.<br /><strong>Privato</strong> Verrà pubblicato solo nel pannello di controllo del personale e non agli utenti finali.',
    'newsexpiry'           => 'Scadenza',
    'newsformexpiry'       => 'Scadenza (Opzionale)',
    'desc_newsformexpiry'  => 'Una volta superata la data di scadenza, l\'articolo non verrà più visualizzato (ma può essere riattivato in qualsiasi momento).',
    'manage'               => 'Gestisci',
    'publicnews'           => 'Notizie Pubbliche',
    'privatenews'          => 'Notizie Private',
    'newsdelconfirm'       => 'Eliminato articolo delle notizie',

    // Insert News
    'contents'             => 'Contenuti',
    'newsdetails'          => 'Dettagli delle Notizie',
    'newsoptions'          => 'Opzioni delle Notizie',
    'sendemail'            => 'Invia email agli abbonati',
    'desc_sendemail'       => 'L\'articolo dovrebbe essere inviato via email a tutti gli abbonati?',
    'emailsubject'         => 'Oggetto Email Personalizzato',
    'desc_emailsubject'    => 'Se qui non viene specificato un oggetto, verrà utilizzato il titolo dell\'articolo.',
    'newstgroup'           => 'Gruppo di Modelli',
    'desc_newstgroup'      => 'I gruppi di modelli in cui verrà pubblicata la notizia.',
    'newsinsertconfirm'    => 'Articolo di notizie (%s) creato con successo',
    'unsubscribe'          => 'Annulla iscrizione: ',
    'clickhere'            => 'Clicca Qui',
    'fromname'             => 'Nome da',
    'desc_fromname'        => 'Il valore <em>Nome Da</em> da utilizzare per le mail inviate agli abbonati, se abilitato.',
    'fromemail'            => 'Dall\'indirizzo email',
    'desc_fromemail'       => 'Il valore <em>Email Da</em> da utilizzare per le mail inviate agli abbonati, se abilitato.',
    'updateprefix'         => 'AGGIORNAMENTO: ',

    // Edit News
    'updatenews'           => 'Aggiorna notizie',
    'invalidnewsitem'      => 'Articoli di notizie non validi',
    'newsupdateconfirm'    => 'Articolo di notizie (%s) aggiornato con successo',
    'exportpdf'            => 'Esporta PDF',
    'deletenews'           => 'Elimina le Notizie',
    'newsoptions'          => 'Opzioni delle Notizie',
    'nwpostedby'           => 'Pubblicato da ',
    'nwposted'             => 'Pubblicato ',
    'nwpostedon'           => ' il ',
    'nwread'               => 'Letto',

    // Comments
    'comments'             => 'Commenti',

    // Potentialy unused phrases in news.php
    'subisvalidated'       => 'Is Validated?',
    'desc_subisvalidated'  => 'If set to <i>No</i>, the provided email address will be sent an activation link.',
    'validated'            => 'Validated',
    'notvalidated'         => 'Not Validated',
    'exportfilename'       => 'File name',
    'desc_exportfilename'  => 'Enter the filename for the export of data.',
    'export'               => 'Export',
    'exportsubscriberdata' => 'Export Subscriber Data',
    'importsubscribers'    => 'Import Subscribers',
    'import'               => 'Import',
    'importdesc'           => 'You can import all news subscribers by entering their email addresses separated by new lines below.',
    'importconfirm'        => 'Subscriber import process completed!<BR />Total: %s<BR />Imported: %s<BR />Failed: %s<BR />Addresses that failed to be imported: %s',
    'none'                 => 'None',
    'desc_newssubject'     => 'Enter a subject for the news item.',
);

return $__LANG;
