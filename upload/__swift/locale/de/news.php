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
    'news'                 => 'News',
    'managenews'           => 'News verwalten',
    'managesubscribers'    => 'Abonnenten verwalten',
    'insertnews'           => 'News einfügen',
    'importexport'         => 'Importieren/Exportieren',

    // Manage Subscribers
    'subscribers'          => 'Abonnenten',
    'dlsubscriberconfirm'  => 'Abonnent gelöscht',
    'subemail'             => 'E-Mail-Adresse',
    'desc_subemail'        => 'Geben Sie die E-Mail-Adresse des Abonnenten ein.',
    'substatus'            => 'Bestätigungsstatus',
    'subdate'              => 'Datum',
    'sublist'              => 'Abonnentenliste',
    'legend'               => 'Legende: ',
    'subinsertconfirm'     => '%s wurde registriert',

    // Insert Subscriber
    'insertsubscriber'     => 'Abonnent einfügen',

    // Edit Subscriber
    'invalidsubscriber'    => 'Ungültiger Abonnent',
    'editsubscriber'       => 'Abonnent bearbeiten',
    'subscriberdetails'    => 'Abonnenten-Details',
    'subupdateconfirm'     => 'Abonnent %s aktualisiert',

    // Manage News
    'newssubject'          => 'News-Thema',
    'newsdescription'      => 'Beschreibung',
    'desc_newsdescription' => 'Geben Sie eine kurze Beschreibung der Nachricht ein. Dieses Feld ist auf <strong>255</strong> Zeichen begrenzt.',
    'newscontents'         => 'Inhalte',
    'newstype'             => 'News-Typ',
    'desc_newstype'        => '<strong>Öffentlich</strong> Wird nur im Kunden-Support-Center veröffentlicht.<br /><strong>Privat</strong> Wird nur in der Personal-Systemsteuerung und nicht für Endbenutzer veröffentlicht.',
    'newsexpiry'           => 'Ablaufdatum',
    'newsformexpiry'       => 'Ablaufdatum (Optional)',
    'desc_newsformexpiry'  => 'Nach dem Ablaufdatum wird der Artikel nicht mehr angezeigt (kann aber jederzeit erneut aktiviert werden).',
    'manage'               => 'Verwalten',
    'publicnews'           => 'Öffentliche News',
    'privatenews'          => 'Private News',
    'newsdelconfirm'       => 'News-Artikel gelöscht',

    // Insert News
    'contents'             => 'Inhalte',
    'newsdetails'          => 'News-Details',
    'newsoptions'          => 'News-Optionen',
    'sendemail'            => 'E-Mail an Abonnenten senden',
    'desc_sendemail'       => 'Soll der News-Artikel in einer E-Mail an alle Abonnenten gesendet werden?',
    'emailsubject'         => 'Benutzerdefinierter E-Mail-Betreff',
    'desc_emailsubject'    => 'Wird hier kein Betreff angegeben, wird der Titel des News-Artikels benutzt.',
    'newstgroup'           => 'Vorlagengruppen',
    'desc_newstgroup'      => 'Die Vorlagengruppen, unter denen News veröffentlicht werden.',
    'newsinsertconfirm'    => 'News-Artikel (%s) wurde erfolgreich erstellt',
    'unsubscribe'          => 'Abonnement kündigen: ',
    'clickhere'            => 'Klicken Sie hier',
    'fromname'             => 'Von-Namen',
    'desc_fromname'        => 'Der <em>Von-Name</em>, der für E-Mails an Abonnenten verwendet wird, wenn aktiviert.',
    'fromemail'            => 'Von-E-Mail-Adresse',
    'desc_fromemail'       => 'Die <em>Von-E-Mail</em>, die für E-Mails an Abonnenten verwendet wird, wenn aktiviert.',
    'updateprefix'         => 'UPDATE: ',

    // Edit News
    'updatenews'           => 'Update-News',
    'invalidnewsitem'      => 'Ungültige News-Artikel',
    'newsupdateconfirm'    => 'News-Artikel (%s) wurde erfolgreich aktualisiert',
    'exportpdf'            => 'PDF exportieren',
    'deletenews'           => 'News löschen',
    'nwpostedby'           => 'Gepostet von ',
    'nwposted'             => 'Gepostet ',
    'nwpostedon'           => ' über ',
    'nwread'               => 'Lesen',

    // Comments
    'comments'             => 'Kommentare',

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
