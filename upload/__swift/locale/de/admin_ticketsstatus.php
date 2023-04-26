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

$__LANG = array (
    // ======= BEGIN v4 LOCALES =======
    'tickets'                               => 'Tickets',
    'tabgeneral'                            => 'Allgemeines',
    'tabpermissions'                        => 'Berechtigungen',
    'staffvisibilitycustom'                 => 'Wechsel zu diesem Ticket-Status einschränken',
    'desc_staffvisibilitycustom'            => 'Ist dies aktiviert, können nur die unten genannten Teams den Ticket-Status auf diesen wechseln.',
    'staffgroups'                           => 'Personalteams',
    'triggersurvey'                         => 'Zufriedenheitsumfrage-E-Mail senden',
    'desc_triggersurvey'                    => 'Wird ein Ticket-Status zu diesem gewechselt, wird eine E-Mail mit der Bitte, Feedback und Bewertung abzugeben, an den Benutzer geschickt.',
    'statustitle'                           => 'Statusname',
    'desc_statustitle'                      => 'Geben Sie einen Namen für diesen Status an.',
    'statuscolor'                           => 'Statusfarbe',
    'desc_statuscolor'                      => 'Wir empfehlen Grün für einen offenen Status und dunkle Farben für einen erledigten Status.',
    'statusbgcolor'                         => 'Statushintergrundfarbe',
    'desc_statusbgcolor'                    => 'In der Personal-Systemsteuerung wird die ganze Ticket-Eigenschaftsleiste in dieser Farbe (abhängig vom Ticket-Status) angezeigt. Lassen Sie es sich nicht entgehen!',
    'displayorder'                          => 'Anzeigereihenfolge',
    'desc_displayorder'                     => 'Ticket-Status werden entsprechend der Anzeigereihenfolge angezeigt, die kleinsten zuerst.',
    'displayiconstatus'                     => 'Symbol',
    'desc_displayiconstatus'                => 'Verlinken Sie oder laden Sie ein Symbol für diesen Status hoch. {$themepath} können Sie benutzen, um das Helpdesk-Bilderverzeichnis anzuzeigen, zum Beispiel: <em>{$themepath}icon_statusopen.gif</em>.',
    'statusdep'                             => 'Abteilung Status zuordnen',
    'desc_statusdep'                        => 'Ordnen Sie diesen Status einer bestimmten Abteilung zu. Tun Sie dies nicht, ist dieser Status für alle Ticket-Abteilungen verfügbar.',
    'statusalldep'                          => '--Nicht verlinkt--',
    'markasresolved'                        => 'Ist dies ein <em>erledigter</em> Ticket-Status?',
    'desc_markasresolved'                   => 'Sollen Tickets auf diesem Status erledigt oder geschlossen sein (wie behoben, erledigt, beendet), aktivieren Sie diese Option. So werden sie von der Liste der offenen Tickets ausgeblendet.',
    'displayinmainlist'                     => 'In Ticket-Hauptliste anzeigen',
    'desc_displayinmainlist'                => 'Tickets mit diesem Status werden in der Ticket-Hauptliste angezeigt.',
    'displaycount'                          => 'Ticket-Anzahl in der Ticket-Navigationsstruktur anzeigen',
    'desc_displaycount'                     => 'Ist dies aktiviert, wird die Anzahl der Tickets in diesem Status in der Personal-Systemsteuerung in der Navigationsstruktur angezeigt. Wir empfehlen Ihnen, diese Einsteillung für jeglichen erledigten Status zu vermeiden.',
    'statustype2'                           => 'Statustyp',
    'desc_statustype2'                      => '<strong>Öffentliche</strong> Status sind überall und auch für Ihre Endnutzer sichtbar.<br /><strong>Private</strong> Status sind nur für Ihr Personal sichtbar.',
    'resetduetime'                          => 'Ticket-Antwortfrist löschen, wenn auf diesen Status gewechselt wird',
    'desc_resetduetime'                     => 'Wenn Tickets auf diesen Status gewechselt werden, wird die Ticket-Frist automatisch gelöscht.',
    'dispatchnotification'                  => 'Benachrichtigung an Benutzer versenden',
    'desc_dispatchnotification'             => 'Ist dies aktiviert, versendet das System eine Benachrichtigung an den Benutzer/Verfasser, wenn dieser Status für ein Ticket aktiviert wird.',
    'insertstatus'                          => 'Status eingeben',
    'desc_insertstatus'                     => '',
    'invalidticketstatus'                   => 'Ticket-Status konnte nicht geladen werden.',
    'managestatus'                          => 'Status',
    'desc_managestatus'                     => '',
    'wineditticketstatus'                   => 'Status: %s bearbeiten',
    'editstatus'                            => 'Status bearbeiten',
    'desc_editstatus'                       => '',
    'taboptions'                            => 'Optionen',
    'titledelticketstatus'                  => 'Ticket-Status gelöscht (%d)',
    'msgdelticketstatus'                    => 'Folgende Ticket-Status wurden gelöscht:',
    'titleticketstatusinsert'               => 'Ticket Status %s erstellt',
    'msgticketstatusinsert'                 => 'Ticket-Status %s wurde erfolgreich erstellt.',
    'titleticketstatusupdate'               => 'Ticket Status %s aktualisiert',
    'msgticketstatusupdate'                 => 'Ticket-Status %s wurde erfolgreich aktualisiert.',
    'titlemasterstatusdelete'               => 'Ticket-Status (%d) konnte nicht gelöscht werden',
    'msgmasterstatusdelete'                 => 'Folgender Master-Ticket-Status konnte nicht gelöscht werden:',
    'tablanguages'                          => 'Sprachen: Übersetzung',
    'title_statusinuse'                     => 'Status wird genutzt',
    'message_statusinuse_editassociation'   => 'Es sind Tickets mit diesem Status vorhanden, die zur verlinkten Abteilung gehören. Sie müssen den Status der Tickets ändern, um die verlinkten Abteilungen aktualisieren zu können.',
    'message_statusinuse_createassociation' => 'Es sind Tickets mit diesem Status vorhanden, die zur verlinkten Abteilung gehören. Sie müssen den Status der Tickets ändern, um die verlinkten Abteilungen aktualisieren zu können.',
    // ======= END v4 LOCALES =======
);


return $__LANG;
