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

$__LANG = array(
    'tsticketid'                    => 'Ticket-ID',
    'desc_tsticketid'               => 'Die numerische ID eines Tickets (wird intern vom Helpdesk bernutzt - Sie könnten aber auch nach der <strong>Ticket-Maske-ID</strong> suchen).',
    'tsticketmaskid'                => 'Ticket-Maske-ID',
    'desc_tsticketmaskid'           => 'Dies ist eine gewöhnliche externe Ticket-ID (ABC-123-4567).',
    'tsfullname'                    => 'Name des Empfängers',
    'desc_tsfullname'               => 'Suchen Sie die Namen der an einem Ticket beteiligten Empfänger (Tickets und individuelle Ticket-Antworten durchsuchen).',
    'tsemail'                       => 'E-Mail-Adresse des Empfängers',
    'desc_tsemail'                  => 'Suchen Sie Tickets nach den E-Mail-Adressen der Empfänger.',
    'tslastreplier'                 => 'Name des Verfassers der letzten Antwort',
    'desc_tslastreplier'            => 'Sucht den Namen der letzten Person, die auf ein Ticket geantwortet hat.',
    'tsreplyto'                     => 'Antworten an',
    'desc_tsreplyto'                => 'Die Antworten-An E-Mail, die im E-Mail-Header angegeben wird. Dies gilt nur für gültige per E-Mail erstellte Tickets.',
    'tssubject'                     => 'Ticket-Betreff',
    'desc_tssubject'                => 'Sucht den Betreff eines Tickets.',
    'tsmessage'                     => 'Ticket-Inhalt',
    'desc_tsmessage'                => 'Durchsucht den Inhalt der Ticket-Antworten.',
    'tsmessagelike'                 => 'Inhalt der Ticket-Antworten (mit SQL LIKE)',
    'desc_tsmessagelike'            => 'Durchsucht den Inhalt der Nachrichten unter Verwendung der SQL LIKE-Suchmethode.',
    'tsuser'                        => 'Benutzer-Konto-Name oder Email-Adressen',
    'desc_tsuser'                   => 'Nach von Benutzern erstellte Tickets suchen (mit Namen oder E-Mail-Adressen, die diesen Kriterien entsprechen).',
    'tsuserorganization'            => 'Benutzerorganisation',
    'desc_tsuserorganization'       => 'Den Name der Organisation von Benutzern und Empfänger, die an einem Ticket teilnehmen, suchen.',
    'tsipaddress'                   => 'IP-Adresse',
    'desc_tsipaddress'              => 'Wurde die Antwort auf ein Ticket vom <strong>Support-Center</strong> aus getätigt, hat das Helpdesk womöglich eine IP-Adresse für den Benutzer registriert.',
    'tscharset'                     => 'Zeichensatz',
    'desc_tscharset'                => 'Tickets eines bestimmten Zeichensatzes.',
    'tsphone'                       => 'Telefonnummer',
    'desc_tsphone'                  => 'Sucht die Telefonnummer von Benutzern und Empfänger, die ein einem Ticket teilnehmen.',
    'tstimeworked'                  => 'Arbeitszeit',
    'desc_tstimeworked'             => 'Tickets nach der geleisteten Arbeitszeit in Sekunden durchsuchen (in den Einträgen für Ticket-Abrechnung und Zeiterfassung).',
    'tstimebilled'                  => 'Abzurechnende Zeit',
    'desc_tstimebilled'             => 'Tickets nach der abzurechnenden Arbeitszeit in Sekunden durchsuchen (in Einträgen für Ticket-Abrechnung und Zeiterfassung).',
    'tsdepartment'                  => 'Abteilung',
    'desc_tsdepartment'             => 'Tickets, die zu einer Abteilung gehören.',
    'tsowner'                       => 'Eigentümer',
    'desc_tsowner'                  => 'An einen bestimmten Personalbenutzer zugewiesene Tickets.',
    'tstype'                        => 'Typ',
    'desc_tstype'                   => '',
    'tsstatus'                      => 'Status',
    'desc_tsstatus'                 => '',
    'tspriority'                    => 'Priorität',
    'desc_tspriority'               => '',
    'tsemailqueue'                  => 'E-Mail-Warteschlange',
    'desc_tsemailqueue'             => 'Tickets, die in einer bestimmten E-Mail-Warteschlange erstellt oder beantwortet wurden.',
    'tsslaplan'                     => 'DLV-Plan',
    'desc_tsslaplan'                => 'Tickets, die aktuell einem bestimmten DLV-Plan zugewiesen sind.',
    'tsflag'                        => 'Markieren',
    'desc_tsflag'                   => '',
    'tstemplategroup'               => 'Vorlagengruppe',
    'desc_tstemplategroup'          => 'Tickets, die zu einer bestimmten Vorlagengruppe gehören.',
    'tsescalation'                  => 'Eskaliert von Regel',
    'desc_tsescalation'             => 'Sucht die von einer bestimmten Eskalationsregel eskalierten Tickets.',
    'tsbayesian'                    => 'Bayes-Kategorie',
    'desc_tsbayesian'               => 'Tickets, die mit einer bestimmten Bayes-Kategorie übereinstimmen.',
    'tsusergroup'                   => 'Benutzergruppe',
    'desc_tsusergroup'              => 'Sucht nach Tickets, deren Empfängers einer bestimmten Benutzergruppe gehören.',
    'tscreator'                     => 'Ticket erstellt von',
    'desc_tscreator'                => '',
    'tscreationmode'                => 'Erstellungsmodus',
    'desc_tscreationmode'           => 'Durchsucht Tickets, nach ihrem Erstellungsmodus.',
    'tsdue'                         => 'Antwortfrist',
    'desc_tsdue'                    => 'Tickets, die eine Antwort vor oder nach dieser Frist erhalten haben.',
    'tsduerange'                    => 'Antwortfrist <range>',
    'desc_tsduerange'               => 'Tickets, die eine Antwort innerhalb dieser Frist erhalten haben.',
    'tsresolutiondue'               => 'Auflösungsfrist',
    'desc_tsresolutiondue'          => 'Tickets, die vor oder nach dieser Auflösungsfrist erledigt wurden.',
    'tsresolutionduerange'          => 'Auflösungsfrist <range>',
    'desc_tsresolutionduerange'     => 'Tickets, die innerhalb der Auflösungsfrist erledigt wurden.',
    'tscreationdate'                => 'Erstellungsdatum',
    'desc_tscreationdate'           => 'Tickets, die vor oder nach diesem Zeitpunkt erstellt wurden.',
    'tscreationdaterange'           => 'Erstellungsdatum <range>',
    'desc_tscreationdaterange'      => 'Tickets, die innerhalb dieses Zeitraums erstellt wurden.',
    'tslastactivity'                => 'Zuletzt aktualisiert',
    'desc_tslastactivity'           => 'Tickets, die vor oder nach diesem Zeitpunkt aktualisiert wurden (z.B. von jemanden beantwortet, oder weitere Aktualisierungsmaßnahmen).',
    'tslastactivityrange'           => 'Zuletzt aktualisiert <range>',
    'desc_tslastactivityrange'      => 'Tickets, die in diesem Zeitraum aktualisiert wurden (z.B. von jemanden beantwortet, oder weitere Aktualisierungsmaßnahmen).',
    'tslaststaffreply'              => 'Letzte Antwort vom Personal',
    'desc_tslaststaffreply'         => 'Tickets, die eine Antwort von einem Personalbenutzer in diesem Zeitraum erhalten haben.',
    'tslaststaffreplyrange'         => 'Letzte Antwort vom Personal <range>',
    'desc_tslaststaffreplyrange'    => 'Tickets, die eine Antwort von einem Personalbenutzer in diesem Zeitraum erhalten haben.',
    'tslastuserreply'               => 'Letzte Antwort vom Benutzer',
    'desc_tslastuserreply'          => 'Tickets, die eine Antwort von einem Benutzer vor oder nach diesem Zeitpunkt erhalten haben.',
    'tslastuserreplyrange'          => 'Letzte Antwort vom Benutzer <range>',
    'desc_tslastuserreplyrange'     => 'Tickets, die eine Antwort von einem Benutzer in diesem Zeitraum erhalten haben.',
    'tsescalateddate'               => 'Eskalierungsdatum',
    'desc_tsescalateddate'          => 'Tickets, die vor oder nach dieser Zeit eskaliert wurden (überfällig wurden).',
    'tsescalateddaterange'          => 'Eskalierungsdatum <range>',
    'desc_tsescalateddaterange'     => 'Tickets, die innerhalb dieses Zeitrahmens eskaliert wurden (überfällig wurden).',
    'tsresolutiondate'              => 'Auflösungsfrist',
    'desc_tsresolutiondate'         => 'Tickets, deren Auflösungsfrist vor oder nach dieser Zeit liegt.',
    'tsresolutiondaterange'         => 'Auflösungsfrist <range>',
    'desc_tsresolutiondaterange'    => 'Tickets, deren Auflösungsfrist innerhalb dieses Zeitrahmens liegt.',
    'tsreopendate'                  => 'Wiedereröffnungsdatum',
    'desc_tsreopendate'             => 'Sucht nach Tickets, deren Wiedereröffnungsdatum (von einem <strong>erledigten</strong> auf einen <strong>offenen</strong> Status geändert) vor oder nach dieser Zeit liegt.',
    'tsreopendaterange'             => 'Wiedereröffnungsdatum <range>',
    'desc_tsreopendaterange'        => 'Sucht nach Tickets, deren Wiedereröffnungsdatum (von einem <strong>erledigten</strong> auf einen <strong>offenen</strong> Status geändert) innerhalb dieses Zeitrahmens liegt.',
    'tsedited'                      => 'Wurde bearbeitet',
    'desc_tsedited'                 => 'Tickets, die bearbeitet wurden.',
    'tseditedby'                    => 'Bearbeitet von',
    'desc_tseditedby'               => 'Sucht nach Tickets, die von einem bestimmten Personalbenutzer bearbeitet wurden.',
    'tsediteddate'                  => 'Bearbeitetungsdatum',
    'desc_tsediteddate'             => 'Tickets, die vor oder nach dieser Zeit bearbeitet wurden.',
    'tsediteddaterange'             => 'Bearbeitetungsdatum <range>',
    'desc_tsediteddaterange'        => 'Tickets, die in diesem Zeitfenster bearbeitet wurden.',
    'tstotalreplies'                => 'Gesamtzahl der Antworten',
    'desc_tstotalreplies'           => 'Tickets, die so viele Antworten haben.',
    'tshasnotes'                    => 'Ticket hat Anmerkungen',
    'desc_tshasnotes'               => '',
    'tshasattachments'              => 'Ticket hat Anhänge',
    'desc_tshasattachments'         => '',
    'tsisemailed'                   => 'Über E-Mail erstellt',
    'desc_tsisemailed'              => 'Über E-Mail erstellte Tickets.',
    'tshasdraft'                    => 'Ticket hat einen Entwurf',
    'desc_tshasdraft'               => 'Tickets, an die der Entwurf einer Antwort gespeichert ist.',
    'tshasfollowup'                 => 'Ausstehende Follow-ups',
    'desc_tshasfollowup'            => 'Tickets, die Ticket-Follow-ups geplant haben.',
    'tsislinked'                    => 'Ticket ist mit einem anderen verknüpft',
    'desc_tsislinked'               => 'Sucht nach Tickets, die mit einem anderen verknüpft wurden',
    'tsisfirstcontactresolved'      => 'Im ersten Kontakt erledigt',
    'desc_tsisfirstcontactresolved' => 'Sucht nach Tickets, die mit der ersten Antwort (von einem Personalbenutzer) erledigt wurden.',
    'tsaverageresponsetime'         => 'Durchschnittliche Antwortzeit',
    'desc_tsaverageresponsetime'    => 'Sucht nach Tickets, die eine bestimmte durchschnittliche Antwortzeit (zwischen Benutzer und Personal-Antworten) haben.',
    'tsescalationlevelcount'        => 'Anzahl der Eskalationen',
    'desc_tsescalationlevelcount'   => 'Durchsucht Tickets nach der Anzahl an Eskalationen (überfällig geworden).',
    'tswasreopened'                 => 'Ticket wurde wiedereröffnet',
    'desc_tswasreopened'            => 'Sucht nach wiedereröffneten Tickets (von einem <strong>erledigten</strong> auf einen <strong>offenen</strong> Status geändert).',
    'tsisresolved'                  => 'Ticket ist erledigt',
    'desc_tsisresolved'             => 'Sucht nach Tickets, die erledigt wurden (Status auf <strong>erledigt</strong> gesetzt).',
    'tsresolutionlevel'             => 'Anzahl der Ticket-Eigentümer vor Auflösung',
    'desc_tsresolutionlevel'        => 'Durchsucht Tickets nach ihrer Anzahl an Eigentümern, bevor sie gelöst wurden.',
    'tsticketnotes'                 => 'Inhalt der Ticket-Anmerkungen',
    'desc_tsticketnotes'            => 'Durchsucht den Inhalt der Ticket-Anmerkungen.',
    'tsgeneraloptions'              => 'Allgemeine Ticket-Kriterien',
    'tsdateoptions'                 => 'Datumskriterien',
    'tsmiscellaneous'               => 'Sonstige Kriterien',
    'tstag' => 'Tag',
    'desc_tstag' => 'Tickets that belong to a particular tag.',

    /**
     * ---------------------------------------------
     * OTHER LOCALES
     * ---------------------------------------------
     */
    'notapplicable'                 => '- Nicht zutreffend -',
    'lookup'                        => 'Nachschlagen',
);


return $__LANG;
