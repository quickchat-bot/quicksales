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
    'troubleshooter'             => 'Fehlersuche',
    'categories'                 => 'Kategorien',
    'titletrcatmismatch'         => 'Eine Kategorie namens %s ist bereits vorhanden',
    'msgtrcatmismatch'           => 'Eine Fehlersuchen-Kategorie mit dem Namen %s ist bereits vorhanden. Bitte wählen Sie einen anderen aus.',
    'wininsertcategory'          => 'Kategorie einfügen',
    'wineditcategory'            => 'Kategorie bearbeiten: %s',
    'insertcategory'             => 'Kategorie einfügen',
    'editcategory'               => 'Kategorie bearbeiten',

    'titletrcategoryinsert'      => 'Fehlersuchen-Kategorie (%s) erstellt',
    'msgtrcategoryinsert'        => 'Fehlersuchen-Kategorie (%s) erfolgreich erstellt.',
    'titletrcategoryupdate'      => 'Fehlersuchen-Kategorie (%s) aktualisiert',
    'msgtrcategoryupdate'        => 'Fehlersuchen-Kategorie (%s) erfolgreich aktualisiert.',

    'trcategory'                 => 'Kategorienamen',
    'desc_trcategory'            => 'Beispielsweise <em>Mein Gerät lässt sich nicht einschalten</em>.',
    'next'                       => 'Weiter',

    'tabgeneral'                 => 'Allgemeines',
    'taboptions'                 => 'Optionen',
    'categorytype'               => 'Sichtbarkeit',
    'desc_categorytype'          => '<strong>Global</strong> Für alle verfügbar, Personal und Endbenutzer.<br /><strong>Öffentlich</strong> Nur für Endbenutzer im Support-Center verfügbar.<br /><strong>Privat</strong> Nur für Ihre Personalbenutzer in der Personal-Systemsteuerung verfügbar.',
    'displayorder'               => 'Anzeigereihenfolge',
    'desc_displayorder'          => 'Fehlersuchen-Kategorien werden entsprechend ihrer Nummer der Anzeigereihenfolge, von der kleinsten zur größten, aufgelistet.',
    'tabpermissionsuser'         => 'Berechtigungen: Benutzer',
    'tabpermissionsstaff'        => 'Berechtigungen: Personal',
    'uservisibilitycustom'       => 'Für bestimmte Benutzergruppen beschränken',
    'desc_uservisibilitycustom'  => 'Ist dies aktiviert, ist diese Kategorie nur für folgende Benutzergruppen sichtbar.',
    'staffvisibilitycustom'      => 'Auf bestimmte Personalteams beschränken',
    'desc_staffvisibilitycustom' => 'Ist dies aktiviert, ist diese Kategorie nur für folgende Teams sichtbar.',
    'usergroups'                 => 'Benutzergruppen',
    'staffteams'                 => 'Personalteams',

    'tablist'                    => 'Liste',
    'tabtree'                    => 'Struktur',
    'steptitle'                  => 'Schritt-Titel',
    'categorytitle'              => 'Kategorie',
    'author'                     => 'Verfasser',
    'stepstatus'                 => 'Status',

    /**
     * ---------------------------------------------
     * INSERT TROUBLESHOOTER
     * ---------------------------------------------
     */
    'titletrstepinsert'          => 'Schritt der Fehlersuche (%s) erstellt',
    'msgtrstepinsert'            => 'Schritt der Fehlersuche (%s) wurde erfolgreich erstellt.',
    'titletrstepupdate'          => 'Schritt der Fehlersuche (%s) aktualisiert',
    'msgtrstepupdate'            => 'Schritt der Fehlersuche (%s) wurde erfolgreich aktualisiert.',
    'insertstep'                 => 'Schritt eingeben',
    'recursionsuffix'            => ' (Mögliche Wiederholung)',
    'parentcategoryitem'         => 'VERFASSER',
    'desc_parentsteps'           => '',
    'tabgeneral'                 => 'Allgemeines',
    'tabattachments'             => 'Anhänge',
    'taboptions'                 => 'Optionen',
    'publish'                    => 'Veröffentlichen',
    'published'                  => 'Veröffentlicht',
    'draft'                      => 'Entwurf',
    'infobauthor'                => 'VERFASSER',
    'infobcreationdate'          => 'ERSTELLT AM',
    'infobeditedby'              => 'BEARBEITET VON',
    'infobeditedon'              => 'BEARBEITET AM',
    'ftarticletype'              => 'Typ',
    'ftcategories'               => 'Kategorien',
    'ftdate'                     => 'Datum',
    'informationbox'             => 'Information',
    'quickfilter'                => 'Schnellfilter',
    'markasdraft'                => 'Als Entwurf markieren',
    'saveasdraft'                => 'Als Entwurf speichern',
    'addfile'                    => 'Datei hinzufügen',
    'steptitle'                  => 'Schritt-Titel',
    'desc_steptitle'             => '',
    'parentsteps'                => 'Übergeordnete Schritte',
    'desc_parentsteps'           => '',
    'generalsettings'            => 'Allgemeine Einstellungen',
    'allowcomments'              => 'Kommentare zulassen',
    'desc_allowcommentsstep'     => 'Ob Kommentare zu diesem Schritt zugelassen werden.',
    'noticketrediractive'        => '-- Keine Abteilung ausgewählt --',
    'ticketredirectionsettings'  => 'Ticket Umleitungseinstellungen',
    'redirecttickets'            => 'An diesem Schritt zu Ticket leiten',
    'desc_redirecttickets'       => 'Aktivieren Sie diese Einstellung, um Benutzer einzuladen, ein Ticket zu erstellen, wenn sie diesen Schritt (d.h. eine Grenze) erreicht haben.',
    'redirectdepartment'         => 'Ticket-Abteilung',
    'desc_redirectdepartment'    => 'Wird keine Abteilung ausgewählt, werden die Benutzer aufgefordert, eine eigene auszuwählen.',
    'ticketsubject'              => 'Ticket-Betreff',
    'desc_ticketsubject'         => 'Wird kein Ticket-Betreff angegeben, kann der Benutzer einen eigenen eingeben.',
    'tickettype'                 => 'Ticket-Typ',
    'desc_tickettype'            => 'Geben Sie einen Typ für das Ticket an, den ein Benutzer, der diesen Schritt erreicht, erstellen kann nachdem er dazu eingeladen wurde.',
    'ticketpriority'             => 'Ticket-Priorität',
    'desc_ticketpriority'        => 'Wenn keine Priorität angegeben wird, kann der Benutzer seine eigene Ticket-Priorität (falls zutreffend) auswählen.',
    'nochange'                   => '-- Keine Änderung --',

    /**
     * ---------------------------------------------
     * VIEW ALL
     * ---------------------------------------------
     */
    'tabviewall'                 => 'Fehlersuche',
    'troubleshooterdesc'         => 'Die Assistenten zur Fehlersuche, führen Sie durch eine Reihe von Schritten zur Diagnose und Behebung häufiger Probleme. Wählen Sie zunächst eine Fehlersuchen-Kategorie.',
    'trattachments'              => 'Anhänge',
    'trnextsteps'                => 'Nächste Schritte',
    'trnext'                     => 'Weiter',
    'trback'                     => 'Zurück',
    'steps'                      => 'Schritte',
    'trcategoryviews'            => ' (%d Aufrufe)',

    // Potentialy unused phrases in staff_troubleshooter.php
    'desc_categorytitle'         => 'Enter the category title',

);

return $__LANG;
