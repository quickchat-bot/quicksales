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
    // ======= BEGIN v4 LOCALES =======
    'isenabled'                 => 'E-Mail-Parser-Regel aktiviert',
    'desc_isenabled'            => 'Is dies deaktiviert, werden E-Mail-Regeln ignoriert.',
    'insertcriteria'            => 'Kriterien einfügen',
    'tabactions'                => 'Aktionen',
    'titlenoaction'             => 'Es wurden keine Aktionen angegeben',
    'msgnoaction'               => 'Es muss mindestens eine Aktion angegeben werden, die (bei Erfüllung) ausgeführt wird.',
    'if'                        => 'Wenn',
    'titleinsertrule'           => 'E-Mail-Regel (%s) erstellt',
    'msginsertrule'             => 'E-Mail-Regel (%s) wurde erfolgreich erstellt.',
    'titleupdaterule'           => 'E-Mail-Regel (%s) aktualisiert',
    'msgupdaterule'             => 'E-Mail-Parser Regel "%s" erfolgreich aktualisiert.',
    'titledelprules'            => 'E-Mail-Regeln gelöscht (%d)',
    'msgdelprules'              => 'Folgenden E-Mail-Warteschlangen wurden gelöscht:',
    'titleenableprules'         => 'Aktivierte E-Mail-Regeln (%d)',
    'msgenableprules'           => 'Folgende E-Mail-Regeln wurden aktiviert:',
    'titledisableprules'        => 'Deaktivierte E-Mail-Regeln (%d)',
    'msgdisableprules'          => 'Folgende E-Mail-Regeln wurden deaktiviert:',
    // ======= END v4 LOCALES =======

    'mailparser'                => 'E-Mail-Parser',

    'desc_parserrules'          => '',

    // Operators
    'opcontains'                => 'Enthält',
    'opnotcontains'             => 'Enthält nicht',
    'opequal'                   => 'Gleich',
    'opnotequal'                => 'Ungleich',
    'opgreater'                 => 'Größer als',
    'opless'                    => 'Weniger als',
    'opregexp'                  => 'Regulärer Ausdruck',
    'strue'                     => 'Richtig',
    'sfalse'                    => 'Falsch',

    // Criteria
    'psendername'               => 'Name des Absenders',
    'desc_psendername'          => 'Der Name des Absenders wird aus dem "von:" E-Mail-Kopf geparst.',

    'psenderemail'              => 'E-Mail-Adresse des Absenders',
    'desc_psenderemail'         => 'Die E-Mail-Adresse des Absenders wird aus dem "von:" Teil des E-Mail-Kopfes geparst. Zum Beispiel: <em>Von: <span class="tabletitle">Name des Absenders</span> <senderemail@domain.com></em>',

    'pdestinationname'          => 'Zielname (Empfänger)',
    'desc_pdestinationname'     => 'Der Zielname wird aus dem "An:" Teil des E-Mail-Kopfes geparst. Der Zielname ist in der Regel der Name des Personalbenutzers, der auf das Ticket geantwortet hat (sofern nicht anders konfiguriert).',

    'pdestinationemail'         => 'Ziel-E-Mail-Adresse (Empfänger)',
    'desc_pdestinationemail'    => 'Der Ziel-E-Mail-Adresse wird aus dem "An:" Teil des E-Mail-Kopfes geparst. Der Zielname ist in der Regel der Name der E-Mail-Warteschlange, an die die Antwort geschickt wird.',

    'preplytoname'              => 'Antworten-an-Name',
    'desc_preplytoname'         => 'Der Antworten-an-Name wird aus dem "Antworten an:" Teil des E-Mail-Kopfes geparst.',

    'preplytoemail'             => 'Antworten-an-E-Mail-Adresse',
    'desc_preplytoemail'        => 'Der Antworten-an-E-Mail-Adresse wird aus dem "Antworten an:" Teil des E-Mail-Kopfes geparst.',

    'psubject'                  => 'Betreff',
    'desc_psubject'             => 'Entspricht dem Betreff der E-Mail.',

    'precipients'               => 'Empfänger',
    'desc_precipients'          => 'Gleicht die E-Mail-Empfänger ab. Es kann mehrere Empfänger geben, an die eine eingehende E-Mail gesendet wurde, auch in den "An:" und "CC:" Kopfzeilen einer E-Mail-Nachricht.',

    'pbody'                     => 'Körper',
    'desc_pbody'                => 'Gleicht den restlichen Inhalt des E-Mail-Körpers nach HTML ab (falls nicht anders konfiguriert), wo Trennlinien enfernt worden sind.',

    'pbodysize'                 => 'Größe des Körpers',
    'desc_pbodysize'            => 'Die Gesamtgröße des Textinhalts des E-Mail-Körpers in Bytes.',

    'ptextbody'                 => 'Reiner-Text Körper',
    'desc_ptextbody'            => 'Eine E-Mail kann zwei Inhaltstypen haben; <em>Reiner-Text</em>, <em>HTML</em> oder <em>beide</em>. Diese Regel durchsucht nur die <em>Klartext-</em> Version des Körpers. Beachten Sie, dass nicht alles E-Mails in <em>beiden</em> Formaten versandt werden.',

    'phtmlbody'                 => 'HTML-Körper',
    'desc_phtmlbody'            => 'Eine E-Mail kann zwei Inhaltstypen haben; <em>Reiner-Text</em>, <em>HTML</em> oder <em>beide</em>. Diese Regel gleicht nur die <em>HTML-</em> Version des Körpers ab. Beachten Sie, dass nicht alles E-Mails in <em>beiden</em> Formaten versandt werden.',

    'ptextbodysize'             => 'Textkörpergröße',
    'desc_ptextbodysize'        => 'Eine E-Mail kann zwei Inhaltstypen haben; <em>Reiner-Text</em>, <em>HTML</em> oder <em>beide</em>. Diese Regel gleicht die Gesamtgröße des <em>Klartext-</em> Inhaltes der E-Mail in Bytes ab.',

    'phtmlbodysize'             => 'HTML-Körpergröße',
    'desc_phtmlbodysize'        => 'Eine E-Mail kann zwei Inhaltstypen haben; <em>Reiner-Text</em>, <em>HTML</em> oder <em>beide</em>. Diese Regel gleicht die Gesamtgröße des <em>HTML-</em> Inhaltes der E-Mail in Bytes ab.',

    'pattachmentname'           => 'Name der Anhänge',
    'desc_pattachmentname'      => 'Gleicht die Namen der Anhangsdateien, die in das Helpdesk aufgenommen wurden ab (wie in der Admin-Systemsteuerung festgelegt).',

    'pattachmentsize'           => 'Anhangsgröße',
    'desc_pattachmentsize'      => 'Gleicht die Größe der Anhangsdateien, die in das Helpdesk aufgenommen wurden ab (wie in der Admin-Systemsteuerung festgelegt).',

    'ptotalattachmentsize'      => 'Gesamtgröße des Anhangs',
    'desc_ptotalattachmentsize' => 'Gleicht die <em>Gesamtröße</em> (in Bytes) der Anhangsdateien, die in das Helpdesk aufgenommen wurden ab (wie in der Admin-Systemsteuerung festgelegt).',

    'pisreply'                  => 'Ist eine Benutzer-Antwort',
    'desc_pisreply'             => 'Ist die eingehende E-Mail eine Antwort auf ein bestehendes Ticket eines Endnutzers, trifft diese Regel zu.',

    'pisthirdparty'             => 'Ist Antwort eines Dritten Empfängers',
    'desc_pisthirdparty'        => 'Ist die eingehen E-Mail eine Antwort auf ein bestehendes Ticket von einer Adresse, die als Dritter Empfänger an das Ticket hinzugefügt wurde, trifft diese Regel zu.',

    'pfloodprotection'          => 'Flut-Schutz ausgelöst',
    'desc_pfloodprotection'     => 'Diese Regel trifft zu, wenn der Helpdesk-Flut-Schutz ausgelöst wird.',

    'pisstaffreply'             => 'Ist eine Personal-Antwort',
    'desc_pisstaffreply'        => 'Ist die eingehende E-Mail eine Antwort auf ein bestehendes Ticket von der E-Mail-Adresse eines Personalbenutzers, trifft diese Regel zu.',

    'pticketstatus'             => 'Ticket-Status (nach parsing)',
    'desc_pticketstatus'        => 'Ist die eingehende E-Mail eine Antwort auf ein bestehendes Ticket, wird der Ticket-Status von dieser Regel abgeglichen.',

    'pticketemailqueue'         => 'E-Mail-Warteschlange',
    'desc_pticketemailqueue'    => 'Wird die eingehende E-Mail von einer bestimmten Warteschlange abgerufen, trifft diese Regel zu.',

    'ptickettype'               => 'Ticket-Typ (nach parsing)',
    'desc_ptickettype'          => 'Ist die eingehende E-Mail eine Antwort auf ein bestehendes Ticket, wird der Ticket-Typ von dieser Regel abgeglichen.',

    'pticketpriority'           => 'Ticket-Priorität (nach parsing)',
    'desc_pticketpriority'      => 'Ist die eingehende E-Mail eine Antwort auf ein bestehendes Ticket, wird die Ticket-Priorität von dieser Regel abgeglichen.',

    'pticketusergroup'          => 'Ticket-Benutzergruppe (nach parsing)',
    'desc_pticketusergroup'     => 'Ist die eingehende E-Mail eine Antwort auf ein bestehendes Ticket, wird die Ticket-Benutzergruppe von dieser Regel abgeglichen.',

    'pticketdepartment'         => 'Ticket-Abteilung (nach parsing)',
    'desc_pticketdepartment'    => 'Ist die eingehende E-Mail eine Antwort auf ein bestehendes Ticket, wird die Ticket-Abteilung von dieser Regel abgeglichen.',

    'pticketowner'              => 'Ticket-Besitzer (nach parsing)',
    'desc_pticketowner'         => 'Ist die eingehende E-Mail eine Antwort auf ein bestehendes Ticket, wird der Ticket-Besitzer von dieser Regel abgeglichen.',
    'prunassigned'              => '-- Nicht zugewiesen --',

    'pticketflagtype'           => 'Ticket-Markierung (nach Analyse)',
    'desc_pticketflagtype'      => 'Ist die eingehende E-Mail eine Antwort auf ein bestehendes Ticket, wird die Ticket-Markierung von dieser Regel abgeglichen.',

    'pbayescategory'            => 'Bayes-Kategorie',
    'desc_pbayescategory'       => 'Das Helpdesk lässt alle eingehende E-Mails durch den Bayes-Filter durchlaufen und ordnet dem Ticket eine Bayes-Kategorie mit der höchsten Punktzahl zu. Sie können dies benutzen, um Spam-E-Mails zu filtern oder das System zu trainieren, automatisch Tickets nach ihrem Inhalt zu verteilen, indem es benutzerdefinierte Bayes-Kategorien nutzt.',

    // Insert Rule
    'insertrule'                => 'Regel einfügen',
    'iruletype'                 => 'E-Mail-Regel-Typ: ',
    'ipreparse'                 => 'Vor der Verarbeitung der E-Mail (Pre-parsing)',
    'ipostparse'                => 'Nach der Verarbeitung der E-Mail (post-parsing)',

    'paignore'                  => 'E-Mail ignorieren',
    'desc_paignore'             => 'Die E-Mail wird vom Helpdesk ignoriert und weder in ein Ticket oder eine Antwort verarbeitet.',
    'panoautoresp'              => 'Keine automatische Antwort senden',
    'desc_panoautoresp'         => 'Es wird keine automatische Antwort mit einer Empfangsbestätigung geschickt (wie bei neuen Tickets oder Antworten).',
    'panoalerts'                => 'Benachrichtigungsregeln nicht verarbeiten',
    'desc_panoalerts'           => 'Helpdesk Benachrichtigungsregeln werden nicht verarbeitet werden, wenn aktiviert.',
    'pnochange'                 => '-- Keine Änderung --',
    'pcstaff'                   => 'Ticket zuweisen',
    'desc_pcstaff'              => 'Das Ticket (neues oder Antwort auf eine ein bestehendes) wird diesem Personalbenutzer zugewiesen.',
    'pcstatus'                  => 'Ticket-Status ändern',
    'desc_pcstatus'             => 'Das Ticket wird zu diesem Status geändert.',
    'pcpriority'                => 'Ticket-Priorität ändern',
    'desc_pcpriority'           => 'Das Ticket wird auf diese Priorität geändert.',
    'pcdepartment'              => 'Ticket in Abteilung verschieben',
    'desc_pcdepartment'         => 'Das Ticket wird in diese Abteilung verschoben.',
    'pcslaplan'                 => 'Ticket DLV-Plan ändern',
    'desc_pcslaplan'            => 'Dem Ticket wird dieser DLV-Plan zugewiesen.',
    'pcmovetotrash'             => 'In den Papierkorb verschieben',
    'desc_pcmovetotrash'        => 'Das Ticket wird in den Papierkorb verschoben',
    'pcflag'                    => 'Ticket markieren',
    'desc_pcflag'               => 'Das Ticket wird mit dieser Farbe markiert.',
    'paddnotes'                 => 'Anmerkung zum Ticket hinzufügen',
    'desc_paddnotes'            => 'Eine Anmerkung wird dem Ticket hinzugefügt.',
    'pcforward'                 => 'Die E-Mail weiterleiten',
    'desc_pcforward'            => 'Die E-Mail wird an diese Adresse weitergeleitet.',
    'preply'                    => 'Auf die E-Mail antworten',
    'desc_preply'               => 'Die E-Mail erhält automatisch folgende Nachricht als Antwort.',
    'panoticket'                => 'Nicht als Ticket-Antwort verarbeiten',
    'desc_panoticket'           => 'Selbst wenn die E-Mail eine Antwort auf ein bestehendes Ticket ist, wird sie nicht den bestehenden Tickets hinzugefügt und ein neues Ticket wird erstellt.',
    'pctickettype'              => 'Ticket-Typ ändern',
    'desc_pctickettype'         => 'Der Ticket-Typ wird zu dem hier festgelegten geändert.',
    'pcaddtags'                 => 'Tags an Ticket hinzufügen',
    'desc_pcaddtags'            => 'Diese Tags werden dem Ticket hinzugefügt.',
    'pcremovetags'              => 'Tags vom Ticket entfernen',
    'desc_pcremovetags'         => 'Sollte das Ticket eines dieser Tags haben, werden diese entfernt.',
    'pcprivate'                 => 'Ticket-Antwort als privat festlegen',
    'desc_pcprivate'            => 'Diese Ticket-Antwort wird als privat gekennzeichnet und nur für andere Personal-Benutzer sichtbar sein (dies gilt nur für Ticket-Antworten vom Personal).',

    // Edit Rules
    'editrule'                  => 'Regel bearbeiten',
    'ptitle'                    => 'E-Mail-Regel-Titel',
    'desc_ptitle'               => 'Z. B. <em>PayPal Quittungen</em>.',
    'pstop'                     => 'Keine weiteren Regeln ausführen',
    'desc_pstop'                => 'Trifft diese Regel zuerst zu, werden keine anderen E-Mail-Regeln nach dieser verarbeitet.',

    // Manage Rules
    'managerules'               => 'Regeln',
    'ruletitle'                 => 'Regeltitel',
    'sortorder'                 => 'Ausführungsreihenfolge',
    'desc_sortorder'            => 'Es ist möglich, dass mehrere E-Mail-Regeln für die gleiche E-Mail zutreffen. Das Helpdesk führt die Regeln nach ihrer Ausführungsreihenfolge aus, kleinste zu größten.',
    'ruletype'                  => 'Regeltyp',
    'creationdate'              => 'Erstellt',

    // Potentialy unused phrases in mailparser_rules.php
    'smatchtype'                => 'Match Type',
    'matchtype'                 => 'Criteria Options',
    'desc_matchtype'            => 'Select the grouping method for the criteria fields.',
    'smatchall'                 => 'Match <b>All</b> Criteria (AND)',
    'smatchany'                 => 'Match <b>Any</b> Criteria (OR)',
    'criteria'                  => 'Criteria',
    'newcriteria'               => 'New Criteria',
    'ruleinsertconfirm'         => 'Parser rule "%s" inserted successfully',
    'ruleupdateconfirm'         => 'Parser rule "%s" updated successfully',
    'invalidrule'               => 'ERROR: Invalid email parser rule',
    'ruledelconfirm'            => 'Parser rule deleted successfully',
    'rulelist'                  => 'Rule List',
    'notapplicable'             => '-- NA --',

);

return $__LANG;
