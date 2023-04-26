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
    'mpsettings'             => 'Einstellungen',
    'desc_mpsettings'        => '',
    'tabsettings'            => 'Einstellungen',
    'mailparser'             => 'E-Mail-Parser',


    'titleinsertban'         => 'E-Mail-Adresse gesperrt',
    'msginsertban'           => 'Die E-Mail-Adresse %s wurde gesperrt, so dass der E-Mail-Parser keine weiteren E-Mails von dieser Adresse verarbeiten wird.',
    'titleupdateban'         => 'Sperrung der E-Mail-Adresse aktualisiert',
    'msgupdateban'           => 'Die Sperrung der E-Mail-Adresse für %s wurde erfolgreich aktualisiert.',
    'wineditban'             => 'Parser-Sperre bearbeiten',
    'titledelbans'           => 'Sperren der E-Mail-Adresse aufgehoben (%d)',
    'msgdelbans'             => 'Folgende Sperren von E-Mail-Adressen wurden gelöscht:',

    // Email Bans
    'emailbans'              => 'E-Mail-Sperrungen',
    'emailbanlist'           => 'Liste der E-Mail-Sperrungen',
    'bannedemail'            => 'E-Mail-Adresse',
    'bannedby'               => 'Gesperrt von',
    'insertban'              => 'Sperre einfügen',
    'insertnewban'           => 'Neue Sperre einfügen',
    'desc_bannedemail'       => 'Geben Sie eine E-Mail-Adresse zum Sperren ein. Jegliche eingehende E-Mails von dieser Adresse werden vom Helpdesk ignoriert. Um eine ganze Domain zu sperren, geben Sie <em>@domainname.com</em> ein.',
    'parserbaninsertconfirm' => 'E-Mail-Sperre "%s" erfolgreich eingefügt',
    'parserbansdelconfirm'   => 'E-Mail-Sperre(n) erfolgreich gelöscht',
    'invalidemailban'        => 'Ungültige Sperrungs-ID für E-Mail-Adressen',
    'editban'                => 'Sperre bearbeiten',
    'bandelconfirmmsg'       => 'Sind Sie sicher, dass Sie diesen Eintrag zur E-Mail-Sperrung löschen möchten?  Diese Aktion ist unwiderruflich!',
    'parserbanupdconfirm'    => 'Sperrung der E-Mail-Adresse %s aktualisiert',
    'desc_mailbans'          => 'Sie können verhindern, dass Problembenutzer Tickets senden, indem Sie deren E-Mail-Adressen sperren. Wenn QuickSupport eine E-Mail-Nachricht empfängt, vergleicht es die Adresse des Verfassers mit der Sperrungsliste. Ist diese Adresse gesperrt, wird die Nachricht ohne Meldung ignoriert.',

    // Catch-All Rules
    'insertcatchallrule'     => 'Catch-All-Regel einfügen',
    'titlenqcatchall'        => 'Konfigurieren Sie die E-Mail-Warteschlangen vor dem Erstellen der Catch-All-Regeln',
    'msgnqcatchall'          => 'Catch-All-Regeln legen fest, was mit in das Helpdesk eintreffende E-Mails geschieht. Ohne konfigurierte E-Mail-Warteschlangen können keine E-Mails das Helpdesk erreichen.',
    'wineditcatchall'        => 'Catch-All-Regel bearbeiten',
    'titledelcatchall'       => 'Catch-All-Regeln gelöscht (%d)',
    'msgdelcatchall'         => 'Folgende Catch-All-Regeln wurden gelöscht: <br>',
    'titleupdatecatchall'    => 'Catch-All-Regel (%s) aktualisiert',
    'msgupdatecatchall'      => 'Die Catch-All-Regel (%s) wurde erfolgreich aktualisiert.',
    'titleinsertcatchall'    => 'Catch-All-Regel erstellt',
    'msginsertcatchall'      => 'Die Catch-All-Regel (%s) wurde erfolgreich erstellt.',
    'catchallrules'          => 'Catch-All-Regeln',
    'desc_catchallrules'     => 'Catch-All-Regeln verwenden reguläre Ausdrücke, um die Adressen der eingehenden E-Mail-Nachrichten abzugleichen. Die Nachrichten können dann an die E-Mail-Warteschlange Ihrer Wahl geleitet werden. Weitere Informationen zu regulären Ausdrücken finden Sie unter <a href="http://www.php.net/preg_match" target="_blank" rel="noopener noreferrer" id="red"> preg_match()</a>.',
    'insertrule'             => 'Regel einfügen',
    'insertnewrule'          => 'Neue Regel einfügen',
    'editrule'               => 'Regel bearbeiten',
    'rregexp'                => 'Musterbasierte Suche',
    'desc_rregexp'           => 'Geben Sie einen regulären Ausdruck ein, der mit der E-Mail-Adresse des Absenders abgeglichen wird. Stimmen diese überein, wird diese Catch-All-Regel ausgelöst. Zum Beispiel:<br /><br /><strong>/^(.*)(\\@ihredomain\\.com)$/i</strong> Stimmt für jede E-Mail-Adresse der Domäne ihredomain.com (*@ihredomain.com)<br /><strong>/^(support\\@)(.*)$/i</strong> Stimmte mit @* überein',
    'sortorder'              => 'Ausführungsreihenfolge',
    'desc_sortorder'         => 'Catch-All-Regeln werden entsprechend der Nummer ihrer Ausführungsreihenfolge, kleinste zur größten, ausgeführt (1 wird vor 5 ausgeführt).',
    'emailqueue'             => 'Zu E-Mail-Warteschlange leiten',
    'desc_emailqueue'        => 'Eingehende E-Mails, die von dieser Catch-All-Regel abgefangen werden, werden an diese E-Mail-Warteschlange geleitet.',
    'ruletitle'              => 'Regeltitel',
    'desc_ruletitle'         => 'Zum Beispiel, <em>Regel zum Auffangen aller @acme.com</em>.',
    'noemailqueueadd'        => '-- Keine E-Mail-Warteschlangen konfiguriert --',
    'editcatchallrule'       => 'Catch-All-Regel bearbeiten',

    // Potentialy unused phrases in mailparser_misc.php
    'invalidemailaddress'    => 'Invalid email address specified',

    'errnoqueue'             => 'ERROR: You do not have any email queues in the database. You need to have at least one email queue to utilize catch-all rules.',
    'catchruleinsertconfirm' => 'Catch-all rule "%s" inserted successfully',
    'catchruleupdconfirm'    => 'Catch-all rule "%s" updated successfully',
    'catchallruledelconfirm' => 'Catch-all rule(s) deleted successfully',
    'invalidcatchalrule'     => 'Invalid catch all rule specified',
    'cruledelconfirmmsg'     => 'Are you sure you wish to delete this catch-all rule?  This action is irreversible!',
);

return $__LANG;
