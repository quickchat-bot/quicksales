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
    'notifications'                  => 'Benachrichtigungen',
    'manage'                         => 'Verwalten',
    'ruletitle'                      => 'Regeltitel',
    'desc_ruletitle'                 => 'Geben Sie einen Titel für die Benachrichtigungsregel ein.',
    'isenabled'                      => 'Benachrichtigungsregel aktiviert',
    'desc_isenabled'                 => 'Bestimmen Sie, ob diese Regel aktiviert oder deaktiviert ist.',
    'ruletype'                       => 'Typ',
    'desc_ruletype'                  => 'Die Benachrichtigungsregel kann eines der folgenden Elemente zugeordnet werden.',
    'next'                           => 'Weiter',
    'creator'                        => 'Verfasser',
    'wininsertnotification'          => 'Benachrichtigung einfügen',

    'unassigned'                     => '-- Nicht zugewiesen --',

    'tabgeneral'                     => 'Allgemeines',
    'tabactions'                     => 'Aktionen',
    'insertcriteria'                 => 'Kriterien einfügen',

    'na_email'                       => 'E-Mail',
    'na_sms'                         => 'SMS',
    'na_pool'                        => 'Desktop-Anwendung',
    'na_staff'                       => 'Eigenes Personalbenutzer-Konto',
    'na_staffgroup'                  => 'Team',
    'na_department'                  => 'Abteilung',
    'na_user'                        => 'Benutzer',
    'nactionemaildispatch'           => 'E-Mail an ... des Ticket-Eigentümers',
    'desc_nactionemaildispatch'      => '',
    'nadispatchemail'                => 'E-Mail an andere E-Mail-Adressen senden',
    'desc_nadispatchemail'           => '',
    'nadispatchemailstaff'           => 'E-Mail an anderen Mitarbeiter senden',
    'desc_nadispatchemailstaff'      => '',
    'nactionpooldispatch'            => 'Benachrichtigung an ... des aktiven Ticket-Eigentümers senden',
    'desc_nactionpooldispatch'       => '',
    'nactionpoolcustomdispatch'      => 'Personal',
    'desc_nactionpoolcustomdispatch' => '',
    'notificationruledesc'           => 'Benachrichtigungsregeltyp auswählen.',
    'titlenocriteria'                => 'Es wurden keine Kriterien angegeben',
    'msgnocriteria'                  => 'Mindestens ein Kriterium ist erforderlich, um eine Benachrichtigungsregel zu erstellen.',
    'titlenotificationinsert'        => 'Benachrichtigungsregel (%s) erstellt',
    'msgnotificationinsert'          => 'Die Benachrichtigungsregel (%s) wurde erfolgreich erstellt.',
    'titlenotificationupdate'        => 'Benachrichtigungsregel (%s) aktualisiert',
    'msgnotificationupdate'          => 'Die Benachrichtigungsregel (%s) wurde erfolgreich aktualisiert.',
    'tabnotifyapp'                   => 'Notify App',
    'tabemail'                       => 'E-Mail',
    'titleenablenotification'        => 'Benachrichtigungsregeln aktiviert (%d)',
    'msgenablenotification'          => 'Folgende Benachrichtigungsregeln wurden aktiviert:<br />%s',
    'titledisablenotification'       => 'Benachrichtigungsregeln deaktiviert (%d)',
    'msgdisablenotification'         => 'Folgende Benachrichtigungsregeln wurden deaktiviert:<br />%s',

    'emailprefix'                    => 'E-Mail-Betreff-Präfix',
    'desc_emailprefix'               => 'Geben Sie ein E-Mail-Betreff-Präfix ein, um die von dieser Benachrichtigungsregel versandten E-Mails  leicht zu erkennen.',

    /**
     * ---------------------------------------------
     * RULE TYPES
     * ---------------------------------------------
     */
    'nrule_chat'                     => 'Chat',
    'nrule_message'                  => 'Offline-Nachricht',
    'nrule_survey'                   => 'Chat-Umfrage',
    'nrule_ticket'                   => 'Ticket',
    'nrule_user'                     => 'Benutzer',

    /**
     * ---------------------------------------------
     * RULE CRITERIA: TICKETS
     * ---------------------------------------------
     */

    'ntickettype'                    => 'Ticket-Typ',
    'desc_ntickettype'               => '',
    'nbayesian'                      => 'Bayes-Kategorie',
    'desc_nbayesian'                 => 'Tickets, die mit einer bestimmten Bayes-Kategorie übereinstimmen.',
    'notapplicable'                  => '-- Nicht verfügbar --',
    'nticketstatus'                  => 'Ticket-Status',
    'desc_nticketstatus'             => '',
    'nticketpriority'                => 'Ticket-Priorität',
    'desc_nticketpriority'           => '',
    'nusergroup'                     => 'Benutzergruppe',
    'desc_nusergroup'                => 'Sucht nach Tickets, deren Empfängers einer bestimmten Benutzergruppe gehören.',
    'ndepartment'                    => 'Ticket-Abteilung',
    'desc_ndepartment'               => 'Tickets, die zu einer Abteilung gehören.',
    'nowner'                         => 'Ticket-Besitzer',
    'desc_nowner'                    => 'An einen bestimmten Personalbenutzer zugewiesene Tickets.',
    'nunassigned'                    => '-- Nicht zugewiesen --',
    'nactivestaff'                   => '-- Aktives Personal --',
    'nemailqueue'                    => 'E-Mail-Warteschlange',
    'desc_nemailqueue'               => 'Tickets, die in einer bestimmten E-Mail-Warteschlange erstellt oder beantwortet wurden.',
    'nflagtype'                      => 'Ticket-Markierung',
    'desc_nflagtype'                 => '',
    'ncreator'                       => 'Ticket erstellt von',
    'desc_ncreator'                  => '',
    'creatorstaff'                   => 'Personal',
    'creatorclient'                  => 'Benutzer',
    'nslaplan'                       => 'DLV-Plan',
    'desc_nslaplan'                  => 'Tickets, die aktuell einem bestimmten DLV-Plan zugewiesen sind.',
    'ndaterange'                     => 'Ticket-Erstellungsdatum <range>',
    'desc_ndaterange'                => 'Tickets, die innerhalb dieses Zeitraums erstellt wurden.',
    'nlastactivityrange'             => 'Ticket zuletzt aktualisiert <range>',
    'desc_nlastactivityrange'        => 'Tickets, die in diesem Zeitraum aktualisiert wurden (z.B. von jemanden beantwortet, oder weitere Aktualisierungsmaßnahmen).',
    'nlaststaffreplyrange'           => 'Letzte Antwort vom Personal <range>',
    'desc_nlaststaffreplyrange'      => 'Tickets, die eine Antwort von einem Personalbenutzer in diesem Zeitraum erhalten haben.',
    'nlastuserreplyrange'            => 'Letzte Antwort vom Benutzer <range>',
    'desc_nlastuserreplyrange'       => 'Tickets, die eine Antwort von einem Benutzer in diesem Zeitraum erhalten haben.',
    'nduerange'                      => 'Antwortfrist <range>',
    'desc_nduerange'                 => 'Tickets, die eine Antwort innerhalb dieser Frist erhalten haben.',
    'nisoverdue'                     => 'Überfällig: Antwort überfällig',
    'desc_nisoverdue'                => 'Tickets, die überfällig sind (weil sie nicht vor der Antwortfrist beantwortet wurden).',
    'nresolutionduerange'            => 'Auflösungsfrist <range>',
    'desc_nresolutionduerange'       => 'Tickets, die innerhalb der Auflösungsfrist erledigt wurden.',
    'nisresolutionoverdue'           => 'Überfällig: Auflösung überfällig',
    'desc_nisresolutionoverdue'      => 'Tickets, die überfällig sind (weil sie nicht vor der Auflösungsfrist erledigt wurden).',
    'ntemplategroup'                 => 'Vorlagengruppe',
    'desc_ntemplategroup'            => 'Tickets, die zu einer bestimmten Vorlagengruppe gehören.',
    'ntimeworked'                    => 'Arbeitszeit (in Minuten)',
    'desc_ntimeworked'               => 'Tickets, die diesem Wert für <strong>Arbeitszeit </strong> entsprechen (als Teil der Einträge zur Ticket-Abrechnung und Zeiterfassung).',
    'ntotalreplies'                  => 'Gesamtzahl der Antworten',
    'desc_ntotalreplies'             => 'Tickets, die so viele Antworten haben.',
    'npendingfollowups'              => 'Ausstehende Follow-ups',
    'desc_npendingfollowups'         => 'Tickets, die Ticket-Follow-ups geplant haben.',
    'nipaddress'                     => 'IP-Adresse',
    'desc_nipaddress'                => 'Wurde die Antwort auf ein Ticket vom <strong>Support-Center</strong> aus getätigt, hat das Helpdesk womöglich eine IP-Adresse für den Benutzer registriert.',
    'nisemailed'                     => 'Über E-Mail erstellt',
    'desc_nisemailed'                => 'Über E-Mail erstellte Tickets.',
    'nisedited'                      => 'Wurde bearbeitet',
    'desc_nisedited'                 => 'Tickets, die bearbeitet wurden.',
    'nhasnotes'                      => 'Hat Anmerkungen',
    'desc_nhasnotes'                 => 'Tickets, die Ticket-Anmerkungen haben.',
    'nhasattachments'                => 'Hat Anhänge',
    'desc_nhasattachments'           => 'Tickets, die Dateianhänge haben.',
    'nisescalated'                   => 'Wurde eskaliert',
    'desc_nisescalated'              => 'Tickets, die durch mindestens eine Eskalationsregel eskaliert wurden.',
    'nhasdraft'                      => 'Hat einen Entwurf',
    'desc_nhasdraft'                 => 'Tickets, an die der Entwurf einer Antwort gespeichert ist.',
    'nhasbilling'                    => 'Hat Einträge zu Abrechnung und Zeiterfassung',
    'desc_nhasbilling'               => 'Tickets, die Einträge zu Abrechnung und Zeiterfassung haben.',
    'nisphonecall'                   => 'Ist ein Telefon-Ticket',
    'desc_nisphonecall'              => 'Tickets, die als <em>Telefonanruf</em>Ticket-Typ markiert wurden.',
    'nislabeled'                     => 'Ist getaggt',
    'desc_nislabeled'                => 'Tickets, die getaggt wurden.',

    'nticketevent'                   => 'Ereignis',
    'desc_nticketevent'              => '',
    'tevent_newticket'               => 'Neues Ticket erstellt',
    'tevent_newticketnotes'          => 'Neue Ticket-Anmerkung hinzugefügt',
    'tevent_newclientreply'          => 'Neue Antwort vom Endbenutzer',
    'tevent_newclientsurvey'         => 'Neuer Versand von Umfrage',
    'tevent_newstaffreply'           => 'Neue Personal-Antwort',
    'tevent_ticketassigned'          => 'Ticket zugeordnet',
    'tevent_newuser'                 => 'Neuen Benutzerregistrierung',

    'nuserevent'                     => 'Ereignis',
    'desc_nuserevent'                => 'Diese Regel wird von diesem Ereignis ausgelöst.',
);

return $__LANG;
